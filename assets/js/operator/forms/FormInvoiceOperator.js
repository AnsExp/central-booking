import { statusTicket } from "../../languaje.js";
import { prettyDateTime, prettyCurrency } from "../../utils/formatter.js";
import { createCell } from "../../utils/table.js";
import { apiAddress } from "../../global.js";

const form = document.getElementById('form_invoices_finder');
const table = document.getElementById('table_invoices_operator');
let total = 0;
let subtotal = 0;

const inputs = {
    dateInvoice: form.querySelector('input[name="month_invoice"]'),
    operator: form.querySelector('input[name="operator"]'),
}

const selects = {
    operator: form.querySelector('select[name="operator"]'),
}

form.addEventListener('submit', submitHandler);

function getFormValues() {
    let operator = inputs.operator ? inputs.operator.value : selects.operator.value;
    return {
        operator: parseInt(operator),
        dateInvoice: inputs.dateInvoice.value,
    };
}

function obtenerDiasInicioFin(mes) {
    const [anio, mesNumero] = mes.split('-').map(Number);
    const primerDia = new Date(anio, mesNumero - 1, 1);
    const primerDiaFormato = primerDia.toISOString().split('T')[0];
    const ultimoDia = new Date(anio, mesNumero, 0);
    const ultimoDiaFormato = ultimoDia.toISOString().split('T')[0];
    return { primerDia: primerDiaFormato, ultimoDia: ultimoDiaFormato };
}


function createEndpoint() {
    let url = apiAddress;
    let values = getFormValues();

    let dates = obtenerDiasInicioFin(values.dateInvoice);

    url += `invoices?operator=${values.operator}&date_from=${dates.primerDia}&date_to=${dates.ultimoDia}`;

    console.log(url);

    return url;
}

function submitHandler(e) {
    e.preventDefault();
    fetch(createEndpoint())
        .then(response => response.json())
        .then(data => renderTable(data));
}

function renderTable(data) {
    total = 0;
    subtotal = 0;
    const tbody = table.querySelector('tbody');
    tbody.innerHTML = '';
    data.forEach(ticket => tbody.appendChild(createRow(ticket)));
    tbody.appendChild(createTotal());
}

function createRow(ticket) {
    const row = document.createElement('tr');
    const withoutCoupon = document.createElement('i');
    const cellStatus = createCell('');
    const amount = ticket.proof_payment ? ticket.proof_payment.amount : ticket.total_amount;
    withoutCoupon.textContent = 'online';
    statusTicket(ticket.status, status => cellStatus.textContent = status);
    const cells = [
        createCell(ticket.id),
        createCell(prettyDateTime(ticket.date_creation)),
        createCell(ticket.order_number),
        createCell(ticket.name_buyer),
        createCell(prettyCurrency(ticket.total_amount)),
        createCell(ticket.coupon ? ticket.coupon.code : withoutCoupon),
        createCell(prettyCurrency(amount)),
        cellStatus,
        createCell(prettyCurrency(ticket.total_amount - amount)),
    ];
    if ((ticket.total_amount - amount) !== 0) {
        row.classList.add('table-danger');
    }
    cells.forEach(cell => row.appendChild(cell));
    total += ticket.total_amount;
    subtotal += amount;
    return row;
}

function createTotal() {
    const row = document.createElement('tr');
    const cellLabel = document.createElement('b');
    const cellTotal = document.createElement('b');
    const cellSubtotal = document.createElement('b');
    cellTotal.textContent = prettyCurrency(total);
    cellLabel.textContent = 'Total';
    cellSubtotal.textContent = prettyCurrency(subtotal);
    const cells = [
        createCell(cellLabel),
        createCell(''),
        createCell(''),
        createCell(''),
        createCell(cellTotal),
        createCell(''),
        createCell(cellSubtotal),
        createCell(''),
        createCell(''),
    ];
    cells.forEach(cell => row.appendChild(cell));
    return row;
}
