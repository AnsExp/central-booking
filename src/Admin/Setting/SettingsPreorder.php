<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;
use CentralTickets\REST\RegisterRoute;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Components\InputComponent;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Services\Actions\DateTrip;

final class SettingsPreorder implements Displayer
{
    private InputComponent $secret_key;

    public function __construct()
    {
        $this->secret_key = new InputComponent('secret_key', 'text');
        $this->secret_key->set_value(git_get_secret_key());
        $this->secret_key->styles->set('width', '300px');
    }

    public function display()
    {
        $route_sample = (new RouteRepository)->find_first();
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <input type="hidden" name="scope" value="preorder">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Llave secreta</th>
                    <td>
                        <?= $this->secret_key->compact() ?>
                        <button type="button" class="button-secondary" id="git-generate-button">Generar</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Body para generar una preorden</th>
                    <td>
<pre id="git_sample_request">{
    "secret_key": "<?= git_get_setting('preorder_secret_key', '') ?>",
    "origin": "<?= $route_sample ? $route_sample->get_origin()->get_zone()->name : 'Pichincha' ?>",
    "destiny": "<?= $route_sample ? $route_sample->get_destiny()->get_zone()->name : 'Guayas' ?>",
    "type": "<?= $route_sample ? $route_sample->type : TransportConstants::MARINE ?>",
    "date_trip": "<?= DateTrip::min_date() ?>",
    "departure_time": "<?= $route_sample ? $route_sample->departure_time : date('H:i:s') ?>",
    "pax": 1
}</pre>
                        <button style="display: inline-block;" type="button" class="button git-copy-button" data-target="#git_sample_request">Copiar</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Endpoint generador de preorden</th>
                    <td>
                        <code>POST</code>
                        <pre style="display: inline-block;"
                            id="git_sample_endpoint"><?= site_url('/wp-json/' . RegisterRoute::prefix . 'preorder') ?></pre>
                        <button style="display: block;" type="button" class="button git-copy-button" data-target="#git_sample_endpoint">Copiar</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Llamada a la API en javascript</th>
                    <td>
<pre style="display: inline-block;" id="git_sample_js">/**
 * Lista de origenes displonibles:
<?php
$zones = git_get_zones();
foreach ($zones as $zone) {
    echo " * - {$zone->name}\n";
}
?>
 */
let origin = '';

/**
 * Lista de destinos disponibles:
<?php
foreach ($zones as $zone) {
    echo " * - {$zone->name}\n";
}
?>
 */
let destiny = '';

/**
 * Formato AAAA-MM-DD
 * Ejemplo: 2024-12-31
 */
let dateTrip = '';

/**
 * Formato HH:MM:SS (24 horas)
 * Ejemplo: 14:30:00
 */
let departureTime = '';

/** Tipo de transporte:
<?php
$type = TransportConstants::all();
foreach ($type as $t) {
    if (in_array($t, [TransportConstants::LAND])) {
        continue;
    }
    $display = git_get_text_by_type($t);
    echo " * - {$t} ({$display})\n";
}
?>
 */
let type = '';

/** Número de pasajeros */
let pax = 0;

/**
 * Función que se ejecuta cuando la pre-reserva se ha creado correctamente
 * @param {Number} idPreorder 
 */
function preorderSuccess(idPreorder) { }

/**
 * Función que se ejecuta cuando la pre-reserva ha fallado
 */
function preorderFailed() { }

fetch('<?= site_url('/wp-json/' . RegisterRoute::prefix . 'preorder') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        secret_key: "<?= git_get_secret_key() ?>",
        origin: origin,
        destiny: destiny,
        type: type,
        date_trip: dateTrip,
        departure_time: departureTime,
        pax: pax
    })
})
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preorderSuccess(data.data.id_preorder);
        } else {
            preorderFailed();
        }
    });</pre>
                        <button style="display: block;" type="button" class="button git-copy-button" data-target="#git_sample_js">Copiar</button>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button-primary" id="git-save-button">
                    Guardar configuraciones
                </button>
            </p>
            <script>
                document.querySelectorAll('.git-copy-button').forEach(button => {
                    button.addEventListener('click', () => {
                        const targetSelector = button.getAttribute('data-target');
                        const targetElement = targetSelector ? document.querySelector(targetSelector) : button.previousElementSibling;
                        if (targetElement) {
                            const textToCopy = targetElement.textContent || targetElement.innerText;
                            navigator.clipboard.writeText(textToCopy).then(() => {
                                button.textContent = '¡Copiado!';
                                setTimeout(() => {
                                    button.textContent = 'Copiar';
                                }, 2000);
                            }).catch(err => {
                                console.error('Error al copiar al portapapeles: ', err);
                            });
                        }
                    });
                });
                function generarClaveSecreta(longitud = 32) {
                    const array = new Uint8Array(longitud);
                    window.crypto.getRandomValues(array);
                    return Array.from(array, b => b.toString(16).padStart(2, '0')).join('');
                }
                document.getElementById('git-generate-button').addEventListener('click', function () {
                    const nuevaClave = generarClaveSecreta(16);
                    document.getElementById('<?= $this->secret_key->id ?>').value = nuevaClave;
                });
            </script>
        </form>
        <?php
    }
}