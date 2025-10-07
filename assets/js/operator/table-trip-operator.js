const buttonPrintTrip = document.getElementById('button-print-trip');
const buttonFinishTrip = document.getElementById('button-finish-trip');
const buttonLaunchModalInfo = document.querySelectorAll('.button-launch-modal-info');
const buttonPrintSallingRequest = document.getElementById('button-print-salling-request');

let linkPdfTrip = '#';
let linkPdfSallingRequest = '#';
let infoFinishTrip = {
    route: 0,
    transport: 0,
    dateTrip: '',
};

buttonLaunchModalInfo.forEach(button => {
    button.addEventListener('click', function () {
        linkPdfTrip = this.dataset.pathPdfTrip;
        linkPdfSallingRequest = this.dataset.pathPdfSallingRequest;
        infoFinishTrip.route = this.dataset.route;
        infoFinishTrip.transport = this.dataset.transport;
        infoFinishTrip.dateTrip = this.dataset.dateTrip;

        document.getElementById('cell-date-trip').textContent = this.dataset.dateTripDisplay;
        document.getElementById('cell-passengers-count').textContent = this.dataset.passengerCounter;
    });
});

buttonPrintTrip.addEventListener('click', function () {
    console.log('linkPdfTrip:', linkPdfTrip);
    
    if (linkPdfTrip === '#') {
        alert('No hay PDF disponible para imprimir.');
        return;
    }
    window.open(linkPdfTrip, '_blank');
});

buttonPrintSallingRequest.addEventListener('click', function () {
    console.log('linkPdfSallingRequest:', linkPdfSallingRequest);
    if (linkPdfSallingRequest === '#') {
        alert('No hay PDF disponible para imprimir.');
        return;
    }
    window.open(linkPdfSallingRequest, '_blank');
});

buttonFinishTrip.addEventListener('click', function () {

    if (!confirm('¿Estás seguro de que deseas finalizar el trayecto?')) {
        return;
    }

    const form = new FormData();

    form.append('nonce', gitTripOperator.nonce);
    form.append('route', infoFinishTrip.route);
    form.append('transport', infoFinishTrip.transport);
    form.append('date_trip', infoFinishTrip.dateTrip);

    fetch(gitTripOperator.url + '?action=' + gitTripOperator.hook, {
        method: 'POST',
        body: form,
    }).then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Error al finalizar el trayecto. Por favor, inténtalo de nuevo.');
        }
    });
});
