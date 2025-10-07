import { statusTicket } from "../../languaje.js";
import { prettyCurrency, prettyDateTime } from "../../utils/formatter.js";
import { apiAddress, uriAddress } from "../../global.js";

const form = document.getElementById('form_coupon_status');
const buttonSubmit = document.getElementById('button_form_edit_coupon_operator_submit');
let tickets = [];

const inputs = {
    id: form.querySelector('input[name="id"]'),
    code: form.querySelector('input[name="code"]'),
    partialAmount: form.querySelector('input[name="partial_amount"]'),
    filePath: '',
    fileName: '',
    file: form.querySelector('input[name="file"]'),
};
console.log(123);

const selects = {
    status: form.querySelector('select[name="status"]'),
};

const display = {
    date: form.querySelector('div[target="coupon_date"]'),
    code: form.querySelector('div[target="coupon_code"]'),
    price: form.querySelector('div[target="coupon_price"]'),
}

for (const option of selects.status.options) {
    statusTicket(option.textContent, status => option.textContent = status)
}

export function addTicket(ticket) {
    tickets.push(parseInt(ticket));
}

export function removeTicket(ticket) {
    tickets = tickets.filter(x => x !== parseInt(ticket));
}

export function clearTicketsStack() {
    tickets = [];
}

export function setValuesForm({
    id = 0,
    date = '',
    code = '',
    price = 0,
    status = '',
    amount = 0,
    passengers = [],
    name_proof_payment = '',
    code_proof_payment = '',
    path_proof_payment = '' }) {
    if (status !== 'payment' && status !== 'partial') {
        path_proof_payment = '';
        code_proof_payment = '';
        name_proof_payment = 'Seleccione...';
    }
    inputs.id.value = id;
    selects.status.value = status;
    display.code.textContent = code;
    inputs.partialAmount.setAttribute('max', price / 100);
    inputs.partialAmount.value = amount;
    inputs.fileName = name_proof_payment;
    inputs.filePath = path_proof_payment;
    inputs.code.value = code_proof_payment;
    display.date.textContent = prettyDateTime(date);
    display.price.textContent = prettyCurrency(price);
    selects.status.dispatchEvent(new Event('change'));
    form.querySelector('span[target="name_file_display"]').textContent = name_proof_payment;
    form.querySelector('button[target="view_file"]').toggleAttribute('disabled', path_proof_payment === '');
    form.querySelector('button[target="download_file"]').toggleAttribute('disabled', path_proof_payment === '');
    form.querySelector('button[target="view_file"]').addEventListener('click', () => window.open(path_proof_payment));
    const partialContent = form.querySelector('div[target="passengers_container"]');
    const passengersChecks = partialContent.querySelectorAll('.passenger_aproved_container');
    passengersChecks.forEach(check => check.remove());
    passengers.forEach(passenger => {
        const container = document.createElement('div');
        container.classList.add('passenger_aproved_container', 'p-2');
        const check = document.createElement('input');
        const label = document.createElement('label');
        label.textContent = passenger.name;
        check.type = 'checkbox';
        check.classList.add('me-1', 'passenger_aproved_check');
        const idTemp = Math.ceil(Math.random() * 10000);
        check.id = `passenger-${passenger.type_document}-${passenger.data_document}-${idTemp}`;
        check.checked = passenger.approved;
        check.disabled = roles.includes('operator') && status !== 'pending';
        label.setAttribute('for', `passenger-${passenger.type_document}-${passenger.data_document}-${idTemp}`);
        check.value = passenger.id;
        container.appendChild(check);
        container.appendChild(label);
        partialContent.appendChild(container);
    });

    if (roles.includes('operator')) {
        inputs.code.disabled = status !== 'pending';
        buttonSubmit.disabled = status !== 'pending';
        selects.status.disabled = status !== 'pending';
        inputs.partialAmount.disabled = status !== 'pending';
        form.querySelector('button[target="upload_file"]').disabled = status !== 'pending';
    }
}

inputs.partialAmount.addEventListener('input', (e) => {
    const max = parseInt(inputs.partialAmount.getAttribute('max'));
    const value = parseInt(inputs.partialAmount.value, 10);
    if (value > max) {
        inputs.partialAmount.value = max;
    }
});

selects.status.addEventListener('change', () => {
    const stateValue = selects.status.value;
    form.querySelector('div[target="file_data_container"]').style.display = stateValue === 'payment' || stateValue === 'partial' ? '' : 'none';
    form.querySelector('div[target="partial_amount_container"]').style.display = stateValue === 'partial' ? '' : 'none';
    inputs.code.toggleAttribute('required', stateValue == 'payment');
    inputs.file.toggleAttribute('required', stateValue == 'payment' && inputs.filePath === '');
});

function getValuesForm() {
    const passengers = [];
    const status = selects.status.value;
    const partialContent = form.querySelector('div[target="passengers_container"]');
    const passengersChecks = partialContent.querySelectorAll('.passenger_aproved_container');
    passengersChecks.forEach(check => {
        const checkbox = check.querySelector('.passenger_aproved_check');
        if (checkbox.checked) {
            passengers.push(parseInt(checkbox.value));
        }
    });
    return {
        status: status,
        tickets: tickets.length > 0 ? tickets : [parseInt(inputs.id.value)],
        code: inputs.code.value,
        passengers: passengers,
        amount: inputs.partialAmount.value > 0 ? parseFloat(inputs.partialAmount.value) : 0,
        file: inputs.file.files && (status === 'payment' || status === 'partial') ? inputs.file.files[0] : undefined,
    };
}

form.addEventListener('submit', async e => {
    const formValues = getValuesForm();
    try {
        formValues.tickets.forEach(async ticket => {
            const formData = new FormData();
            formData.append('id', ticket);
            formData.append('code', formValues.code);
            formData.append('amount', formValues.amount);
            formData.append('status', formValues.status);
            formData.append('passengers', JSON.stringify(formValues.passengers));
            if (formValues.file !== undefined)
                formData.append('payment_file', formValues.file);
            let endpoint = `${apiAddress}tickets/proof_payment`;
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
            });
            const json = await response.json();
            console.log(json);
        });
    } catch (error) {
        console.error(error.message);
    }
});

fetch(`${uriAddress}/wp-content/plugins/central_tickets/assets/data/settings.json`)
    .then(response => response.json())
    .then(data => {
        inputs.file.addEventListener('change', () => {
            const limit = 1 * 1024 * 1024;
            if (inputs.file.files[0].size > limit) {
                inputs.file.value = null;
                alert('El archivo tiene un peso mayor de lo permitido.\nDe necesitarlo, pongase en contacto con la administración.');
            }
        });
    });

inputs.file.addEventListener('change', () => {
    form.querySelector('span[target="name_file_display"]').textContent = inputs.file.files[0].name;
    inputs.filePath = '';
    form.querySelector('button[target="view_file"]').toggleAttribute('disabled', true);
    form.querySelector('button[target="download_file"]').toggleAttribute('disabled', true);
});
form.querySelector('button[target="upload_file"]').addEventListener('click', () => inputs.file.click());
form.querySelector('button[target="download_file"]').addEventListener("click", () => {
    fetch(inputs.filePath)
        .then(response => {
            if (!response.ok) {
                throw new Error("Error al descargar el archivo");
            }
            return response.blob();
        })
        .then(blob => {
            const enlace = document.createElement("a");
            enlace.href = URL.createObjectURL(blob);
            enlace.download = inputs.fileName;
            document.body.appendChild(enlace);
            enlace.click();
            document.body.removeChild(enlace);
        })
        .catch(error => console.error("Hubo un problema al descargar el archivo:", error));
});
