<?php
namespace CentralTickets\Admin\Form;

use CentralTickets\Components\Displayer;

final class FormQRCode implements Displayer
{
    public function display()
    {
        wp_enqueue_script(
            'central-booking-qr-generator',
            CENTRAL_BOOKING_URL . '/assets/js/admin/qr-generator.js',
            ['jquery'],
            time(),
            true
        );
        wp_enqueue_style(
            'central-booking-qr-generator-style',
            CENTRAL_BOOKING_URL . '/assets/css/admin/qr-generator.css'
        );
        wp_localize_script(
            'central-booking-qr-generator',
            'CentralBookingQRGenerator',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'action'   => 'git_qr_generator',
                'nonce'    => wp_create_nonce('central_booking_qr_generator_nonce'),
            ]
        );
        ob_start();
        ?>
        <form method="get" id="qr-generator-form" style="max-width: 500px;">
            <table class="form-table" role="presentation">
                <tr class="form-field">
                    <td scope="row">
                        <label for="data">Información <span class="required">*</span></label>
                    </td>
                    <td>
                        <input type="text" id="data" name="data" required>
                    </td>
                </tr>
                <tr class="form-field">
                    <td scope="row">
                        <label for="width">Tamaño (px) <span class="required">*</span></label>
                    </td>
                    <td>
                        <input type="number" id="width" name="width" min="100" required>
                    </td>
                </tr>
                <tr class="form-field">
                    <td scope="row">
                        <label for="encode">Tipo de información <span class="required">*</span></label>
                    </td>
                    <td>
                        <select name="type" id="type">
                            <option value="text">Texto plano</option>
                            <option value="url">URL</option>
                            <option value="email">Email</option>
                            <option value="phone">Número de teléfono</option>
                            <!-- <option value="wifi">Red WiFi</option> -->
                        </select>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button button-primary">Generar</button>
        </form>
        <div class="qr-container" id="qr-container" style="display: none;">
        </div>
        <?php
        echo ob_get_clean();
    }
}