const form = document.getElementById('product_form');
const formOverlay = document.getElementById('overlay_loading');

function toggleOverlay(visible) {
    formOverlay.style.display = visible ? '' : 'none';
}

function handleMessageModal(message) {
    document.getElementById('message_form_modal').innerHTML = message;
    document.getElementById('button_launch_modal_form').click();
}

function createInputHidden(name, value) {
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
}

function removeInput(name) {
    let input = form.querySelector(`input[name="${name}"]`);
    if (input) {
        input.remove();
    }
    input = form.querySelector(`select[name="${name}"]`);
    if (input) {
        input.remove();
    }
}

function renameInput(name, newName) {
    let input = form.querySelector(`input[name="${name}"]`);
    if (input) {
        input.name = newName;
    }
    input = form.querySelector(`select[name="${name}"]`);
    if (input) {
        input.name = newName;
    }
}

form.addEventListener('submit', () => {
    removeInput('schedule_goes');
    removeInput('schedule_returns');
    removeInput('terms_conditions');
    form.appendChild(createInputHidden('pax[kid]', window.CentralTickets.formProduct.getPax().kid));
    form.appendChild(createInputHidden('pax[rpm]', window.CentralTickets.formProduct.getPax().rpm));
    form.appendChild(createInputHidden('pax[extra]', window.CentralTickets.formProduct.getPax().extra));
    form.appendChild(createInputHidden('pax[standard]', window.CentralTickets.formProduct.getPax().standard));

    renameInput('date_trip_goes', 'trip[goes][date]');
    form.appendChild(createInputHidden('trip[goes][route]', window.CentralTickets.formProduct.getRoutes().goes.id));
    form.appendChild(createInputHidden('trip[goes][transport]', window.CentralTickets.formProduct.getTransports().goes.id));

    if (() => {
        document.querySelectorAll('input[name="type_way"]').forEach((input) => {
            if (input.value === 'double_way' && input.checked) {
                return true;
            }
        });
        return false;
    }) {
        renameInput('date_trip_returns', 'trip[returns][date]');
        form.appendChild(createInputHidden('trip[returns][route]', window.CentralTickets.formProduct.getRoutes().returns.id));
        form.appendChild(createInputHidden('trip[returns][transport]', window.CentralTickets.formProduct.getTransports().returns.id));
    }
});

window.CentralTickets.formProduct = {
    toggleOverlay: toggleOverlay,
    handleMessageModal: handleMessageModal
}

document.querySelector('.woocommerce-product-gallery').remove();
document.querySelector('.summary.entry-summary').classList.add('w-100');
