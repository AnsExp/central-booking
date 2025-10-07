const fileInput = document.getElementById(formCouponStatus.fileInputId);
const codeInput = document.getElementById(formCouponStatus.codeInputId);
const amountInput = document.getElementById(formCouponStatus.amountInputId);
const statusSelect = document.getElementById(formCouponStatus.statusSelectId);
const checkPassengerClass = document.getElementsByClassName(formCouponStatus.checkPassengerClass);

console.log(formCouponStatus);


for (const option of statusSelect.options) {
    if (formCouponStatus.statusToRemove.includes(option.value)) {
        option.remove();
    }
}

fileInput.addEventListener('change', function () {
    const fileNameDisplay = document.getElementById('proof_payment_name_display');
    if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = fileInput.files[0].name;
    } else {
        fileNameDisplay.textContent = 'No file selected';
    }
});

statusSelect.addEventListener('change', function () {
    const partialOptionsContainer = document.getElementById('partial-options-container');
    if (statusSelect.value == 'partial') {
        partialOptionsContainer.style.display = 'block';
    } else {
        partialOptionsContainer.style.display = 'none';
    }
});

function validateFile() {
    if (!formCouponStatus.fileRequiredIn.includes(statusSelect.value)) {
        return true;
    }
    const input = document.getElementById('form-coupon-status').querySelector('input[name="has_previous"]');
    if (input.value === 'true') {
        return true;
    }
    if (fileInput.files.length === 0) {
        return false;
    }
    return true;
}

function validatePartial() {
    if (statusSelect.value === 'partial') {
        for (const check of checkPassengerClass) {
            if (check.checked) {
                return true;
            }
        }
        return false;
    }
    return true;
}

jQuery('#form-coupon-status').on('submit', function (e) {
    e.preventDefault();

    if (!validatePartial()) {
        const messageDangerContainer = jQuery('#message-danger-container');
        messageDangerContainer.html('De escojer al menos un pasajero aprobado.');
        messageDangerContainer.show();
        return;
    }

    if (!validateFile()) {
        const messageDangerContainer = jQuery('#message-danger-container');
        messageDangerContainer.html('Debe subir un comprobante de pago.');
        messageDangerContainer.show();
        return;
    }

    const formData = new FormData(this);

    jQuery.ajax({
        url: this.getAttribute('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            jQuery('#button-submit-form-coupon-status').text('Guardando...');
            jQuery('#button-submit-form-coupon-status').prop('disabled', true);
        },
        success: function (response) {
            const messageSuccessContainer = jQuery('#message-success-container');
            messageSuccessContainer.show();
            messageSuccessContainer.html(`Se ha actualizado el estado del cupón correctamente.`);

            jQuery('html, body').animate({
                scrollTop: messageSuccessContainer.offset().top - 50
            }, 200);
            setTimeout(() => {
                location.replace(jQuery('#link_to_search_pane').attr('href'));
            }, 2000);
        },
        error: function (response) {
            errorMessage = response.responseJSON.data.message;
            const messageDangerContainer = jQuery('#message-danger-container');
            messageDangerContainer.show();
            messageDangerContainer.html(errorMessage);
            jQuery('html, body').animate({
                scrollTop: messageDangerContainer.offset().top - 50
            }, 200);
        },
        complete: function () {
            jQuery('#button-submit-form-coupon-status').text('Guardar');
            jQuery('#button-submit-form-coupon-status').prop('disabled', false);
        }
    });
});
