const buttonSubmit = document.getElementById(dataPassenger.elements.submitButton);
const buttonPrevPassenger = document.getElementById(dataPassenger.elements.prevButton);

async function getFormPassengersInfo(passengersCount) {
    try {
        const response = await fetch(`${dataPassenger.ajaxUrl}?action=${dataPassenger.hook}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                passengers_count: passengersCount,
            }),
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const html = await response.json();
        return html.data.output;
    } catch (error) {
        console.error('Error fetching passenger form:', error);
        throw error;
    }
}

async function initPanePassenger() {
    const count = window.CentralTickets.formProduct.passengersCount();
    const html = await getFormPassengersInfo(count);
    document.getElementById(dataPassenger.elements.passengersFormContainer).innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => {
    window.CentralTickets.formProduct.initPanePassenger = initPanePassenger;

    buttonPrevPassenger.addEventListener('click', () => {
        document.getElementById('git-form-product-passengers').style.display = 'none';
        document.getElementById('git-form-product-transport').style.display = 'block';
    });
})