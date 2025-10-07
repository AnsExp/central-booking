import { addObservable, getFormValues, offsetDays } from '../forms/FormRouteOperator.js';
import { createCell } from '../../utils/table.js';
import { prettyDate, prettyTime } from '../../utils/formatter.js';
import { apiAddress } from "../../global.js";

const elements = {
    form: document.getElementById('form_route_operator'),
    tableRoute: document.getElementById('table_routes_selected'),
    button_modal: document.getElementById('button_modal_info_trip'),
}

addObservable(data => createTableData(data));

function createTableData(data) {
    elements.tableRoute.innerHTML = '';
    const table = document.createElement('table');
    const tableHead = document.createElement('thead');
    const tableBody = document.createElement('tbody');
    table.classList.add('table', 'table-hover', 'table-striped', 'table-bordered');
    let rowData = document.createElement('tr');
    let rowDate = document.createElement('tr');
    let currentDate = new Date(getFormValues().dateStart);
    for (let i = 0; i < offsetDays; i++) {
        let dateCell = createCell(prettyDate(currentDate.toISOString().split('T')[0]));
        let dataButton = createButton(data[i]);
        dateCell.classList.add('fw-bold', 'align-middle');
        rowDate.appendChild(dateCell);
        rowData.appendChild(createCell(dataButton));
        currentDate.setDate(currentDate.getDate() + 1);
    }
    tableHead.appendChild(rowDate);
    tableBody.appendChild(rowData);
    table.appendChild(tableHead);
    table.appendChild(tableBody);
    elements.tableRoute.appendChild(table);
}

function createButton(data) {
    let button = elements.button_modal.cloneNode(true);
    button.classList.remove('btn-primary');
    button.style.display = '';
    button.textContent = data.length;

    button.style.fontSize = '20pt';
    button.style.fontWeight = '600';

    if (data.length > 0) {

        if (data[0].transport.capacity === data.length)
            button.style.color = '#6FF86A';
        else if (data[0].transport.capacity < data.length)
            button.style.color = '#F86A6A';
        else if (data[0].transport.capacity > data.length)
            button.style.color = '#6A9CF8';


        button.addEventListener('click', () => {

            document.querySelector('span[target="control_route_origin"]').textContent = data[0].route.origin.name;
            document.querySelector('span[target="control_route_destiny"]').textContent = data[0].route.destiny.name;
            document.querySelector('span[target="control_route_time"]').textContent = prettyTime(data[0].route.departure_time);
            document.querySelector('span[target="control_route_date_trip"]').textContent = prettyDate(data[0].date_trip);
            document.querySelector('span[target="control_route_transport"]').textContent = data[0].transport.nicename;
            document.querySelector('span[target="control_route_passengers"]').textContent = data.length;
            document.getElementById('button_route_modal_print_list_passengers')
                .addEventListener('click', () => {
                    const params = getFormValues();
                    const endpoint = `${apiAddress}pdf_generator?origin=${params.origin}&destiny=${params.destiny}&time=${params.time}&transport=${params.transport}&date=${data[0].date_trip}`;
                    window.open(endpoint);
                    console.log(endpoint);
                });
            document.getElementById('button_modal_finish_trip').addEventListener('click', () => {

                data.forEach(ticket => ticket.completed = true)

                updateTicketsDetails(data);
            });
        });
        button.removeAttribute('id');
    } else {
        button.disabled = true;
        button.style.border = 'none';
        button.classList.add('btn', 'w-100');
    }
    return button;
}

function updateTicketsDetails(data) {
    const ids = [];
    data.forEach(passenger => ids.push(passenger.id));

    ids.forEach(id => {
        fetch(`${apiAddress}passengers/served/${id}`, {
            method: 'PUT',
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ served: true })
        });
    });

    location.reload();
}
