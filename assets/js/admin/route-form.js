jQuery(document).ready(function ($) {
    const form = $('#form-route');
    if (form) {
        form.on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: formRoute.url + '?action=' + formRoute.hook,
                method: 'post',
                data: {
                    id: $('input[name="id"]').val(),
                    type: $('select[name="type"]').val(),
                    origin: $('select[name="origin"]').val(),
                    destiny: $('select[name="destiny"]').val(),
                    distance: $('input[name="distance"]').val(),
                    duration_trip: $('input[name="duration_trip"]').val(),
                    departure_time: $('input[name="departure_time"]').val(),
                    transports : window.MultiselectAPI.getSelected($('select[name="transports"]')[0]),
                },
                success: function (response) {
                    location.replace(formRoute.successRedirect);
                    console.log(response);
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });
    } else {
        console.error('Form not found');
    }
});