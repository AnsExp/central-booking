jQuery(document).ready(function ($) {
    $('#qr-generator-form').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $('#qr-generator-form button').prop('disabled', true).text('Generando...');
        $.ajax({
            url: CentralBookingQRGenerator.ajax_url,
            method: 'POST',
            data: formData + '&action=git_qr_generator',
            success: function (response) {
                console.log(response.data);
                $('#qr-generator-form button').prop('disabled', false).text('Generar');
                $('#qr-container').html('<img src="' + response.data + '" alt="QR Code">');
                $('#qr-container').show();
            },
            error: function () {
                $('#qr-generator-form button').prop('disabled', false).text('Generar');
                alert('Error generating QR code. Please try again.');
            }
        });
    });
});
