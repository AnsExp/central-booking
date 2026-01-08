import { apiAddress } from "../../global.js";

const form = document.getElementById('form_route_operator');
const observables = [];

const selects = {
    time: form.querySelector('select[name="time"]'),
    origin: form.querySelector('select[name="origin"]'),
    destiny: form.querySelector('select[name="destiny"]'),
    transport: form.querySelector('select[name="transport"]'),
}

const inputs = {
    dateEnd: form.querySelector('input[name="date_end"]'),
    dateStart: form.querySelector('input[name="date_start"]'),
}

export const offsetDays = 7;

/**
 * @param {Function} observable 
 */
export function addObservable(observable) {
    observables.push(observable);
}

export function getFormValues() {
    const date = new Date(inputs.dateStart.value);
    date.setDate(date.getDate() + offsetDays);
    const newDate = date.toISOString().split('T')[0];
    return {
        origin: parseInt(selects.origin.value),
        destiny: parseInt(selects.destiny.value),
        time: selects.time.value,
        transport: parseInt(selects.transport.value),
        dateStart: inputs.dateStart.value,
        dateEnd: newDate,
    };
}

inputs.dateStart.addEventListener('change', () => {
    const startDate = new Date(inputs.dateStart.value);
    if (!isNaN(startDate.getTime())) {
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + offsetDays - 1);
        inputs.dateEnd.value = endDate.toISOString().split('T')[0];
    }
});

selects.origin.addEventListener('change', () => {
    for (const option of selects.destiny.options) {
        if (option.classList.contains(`show_if_origin_${selects.origin.value}`))
            option.style.display = '';
        else
            option.style.display = 'none';
    }
    selects.destiny.selectedIndex = 0;
    selects.destiny.dispatchEvent(new Event('change'));
});

selects.destiny.addEventListener('change', () => {
    for (const option of selects.time.options) {
        if (option.classList.contains(`show_if_origin_${selects.origin.value}_destiny_${selects.destiny.value}`)) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    }
    selects.time.selectedIndex = 0;
    selects.time.dispatchEvent(new Event('change'));
});

selects.time.addEventListener('change', () => {
    for (const option of selects.transport.options) {
        if (option.classList.contains(`show_if_origin_${selects.origin.value}_destiny_${selects.destiny.value}_time_${selects.time.value}`))
            option.style.display = '';
        else
            option.style.display = 'none';
    }
    selects.transport.selectedIndex = 0;
});

selects.origin.dispatchEvent(new Event('change'));

function validateForm({
    origin, destiny,
    time, transport,
    dateStart, dateEnd }) {
    if (isNaN(origin))
        return false;
    if (isNaN(destiny))
        return false;
    if (isNaN(transport))
        return false;
    if (time === 'Seleccione...')
        return false;
    if (dateStart === '')
        return false;
    if (dateEnd === '')
        return false;
    return true;
}

form.addEventListener('submit', e => {
    e.preventDefault();
    let formValues = getFormValues();
    if (!validateForm(formValues)) {
        console.log('Form Invalid.');
        return;
    }
    const endpoint = `${apiAddress}passengers?approved=true&served=false&id_origin=${formValues.origin}&id_destiny=${formValues.destiny}&departure_time=${formValues.time}&id_transport=${formValues.transport}&date_trip_from=${formValues.dateStart}&date_trip_to=${formValues.dateEnd}`;
    fetch(endpoint)
        .then(response => response.json())
        .then(data => {
            observables.forEach(observable => {
                observable(groupAndFillDates(data));
            });
        });
});

function groupAndFillDates(objectsArray) {
    const groupedByDate = objectsArray.reduce((groups, obj) => {
        const dateKey = obj.date_trip;
        if (!groups[dateKey]) {
            groups[dateKey] = [];
        }
        groups[dateKey].push(obj);
        return groups;
    }, {});

    const params = getFormValues();

    const start = new Date(params.dateStart);
    const end = new Date(params.dateEnd);
    const allDates = [];
    while (start <= end) {
        allDates.push(start.toISOString().split('T')[0]);
        start.setDate(start.getDate() + 1);
    }

    const result = allDates.map(date => groupedByDate[date] || []);

    return result;
}