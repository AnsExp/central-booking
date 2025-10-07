import { createCell } from "../../utils/table.js";
import { prettyCurrency, prettyDateTime } from "../../utils/formatter.js";
import { setValuesForm, clearTicketsStack } from "../forms/FormCouponOperator.js";
import { statusTicket } from "../../languaje.js";
import { apiAddress } from "../../global.js";

const elements = {
    form: document.getElementById('form_coupon_operator'),
    tableBody: document.getElementById('table_coupon_operator').querySelector('tbody'),
    buttonModal: document.getElementById('launch_modal_coupon_pane'),
    modal: document.getElementById('launch_modal_coupon_pane'),
}

elements.form.addEventListener('submit', e => {
    e.preventDefault();
    queryTable();
});

async function fetchData() {
    clearTicketsStack()

    let dateEndInput = elements.form.querySelector('input[name="date_end"]');
    let dateStartInput = elements.form.querySelector('input[name="date_start"]');
    let couponSelect = elements.form.querySelector('select[name="select-coupon"]');

    const date = new Date(dateEndInput.value);
    date.setDate(date.getDate() + 1);
    const newDate = date.toISOString().split('T')[0];

    let endpoint = `${apiAddress}tickets?id_coupon=${couponSelect.value}&date_creation_from=${dateStartInput.value}&date_creation_to=${newDate}`;

    let response = await fetch(endpoint);

    return response.json();
}

function formatTable(data) {
    elements.tableBody.innerHTML = '';
    for (const coupon of data) {
        let row = createRow(coupon);
        elements.tableBody.appendChild(row);
    }
}

function createRow(ticket) {
    let row = document.createElement('tr');
    let button = cloneButtonModal();

    button.addEventListener('click', () => {
        let amount = 0;
        if (ticket.proof_payment && ticket.proof_payment.amount > 0) {
            amount = ticket.proof_payment.amount / 100;
        }
        setValuesForm({
            id: ticket.id,
            amount: amount,
            status: ticket.status,
            code: ticket.coupon.code,
            date: ticket.date_creation,
            price: ticket.total_amount,
            passengers: ticket.passengers,
            code_proof_payment: ticket.proof_payment ? ticket.proof_payment.code : '',
            path_proof_payment: ticket.proof_payment ? ticket.proof_payment.path : '',
            name_proof_payment: ticket.proof_payment ? ticket.proof_payment.name : 'Seleccione un archivo...',
        });
    });

    switch (ticket.status) {
        case 'pending':
            row.classList.add('table-light');
            break;
        case 'payment':
            row.classList.add('table-success');
            break;
        case 'cancel':
            row.classList.add('table-danger');
            break;
        case 'partial':
            row.classList.add('table-warning');
            break;
    }

    const statusCell = createCell('');
    statusTicket(ticket.status, status => statusCell.textContent = status);

    const cells = [
        createCell(button),
        createCell(ticket.id),
        createCell(ticket.order_number),
        createCell(prettyDateTime(ticket.date_creation)),
        statusCell,
        createCell(ticket.proof_payment ? prettyCurrency(ticket.proof_payment.amount) : prettyCurrency(0)),
        createCell(prettyCurrency(ticket.total_amount)),
    ];

    cells.forEach(cell => row.appendChild(cell));

    return row;
}

function cloneButtonModal() {
    let control = elements.buttonModal.cloneNode(true);
    control.style.display = '';
    control.classList.add('w-100');
    return control;
}

async function queryTable() {
    let data = await fetchData();
    formatTable(data);
}