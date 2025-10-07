const form = document.getElementById('form-operator');

const couponCounter = form.querySelector('input[name="coupons_counter"]');
const couponLimit = form.querySelector('input[name="coupons_limit"]');

couponLimit.addEventListener('input', function () {
    const limit = parseInt(couponLimit.value, 10);
    couponCounter.setAttribute('max', limit);
});

form.addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData();
    formData.set('nonce', gitOperatorForm.nonce);
    formData.set('id', form.querySelector('input[name="id"]').value);
    formData.set('phone', form.querySelector('input[name="phone"]').value);
    formData.set('lastname', form.querySelector('input[name="lastname"]').value);
    formData.set('firstname', form.querySelector('input[name="firstname"]').value);
    formData.set('coupons_limit', form.querySelector('input[name="coupons_limit"]').value);
    formData.set('coupons_counter', form.querySelector('input[name="coupons_counter"]').value);
    formData.set('coupons', form.querySelector('select[name="coupons"]').dataset.selected);
    if (form.querySelector('input[name="logo_sale"]').checked) {
        formData.set('logo_sale', 'on');
    }
    const messageContainer = document.getElementById('form-operator-message-container');
    fetch(gitOperatorForm.url + '?action=' + gitOperatorForm.action, {
        method: 'POST',
        body: formData,
    }).then(response => {
        const json = response.json();
        if (!response.ok) {
            throw new Error(json.message);
        }
        return json;
    }).then(data => {
        messageContainer.innerHTML += `<div class="notice notice-success is-dismissible"><p>${data.message}</p></div>`;
        
        location.replace(gitOperatorForm.successRedirect);
    }).catch(error => {
        messageContainer.innerHTML += `<div class="notice notice-error is-dismissible"><p>${error}</p></div>`;
    });
});

