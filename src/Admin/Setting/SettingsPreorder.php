<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Data\Constants\TransportConstants;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\REST\RegisterRoute;

final class SettingsPreorder implements DisplayerInterface
{
    private InputComponent $secret_key;

    public function __construct()
    {
        $this->secret_key = new InputComponent('secret_key', 'text');
        $this->secret_key->setValue(git_get_secret_key());
        $this->secret_key->styles->set('width', '300px');
    }

    public function render()
    {
        $result = (new RouteService)->find();
        $result = $result->hasItems() ? $result->getItems()[0] : null;
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Body para generar una preorden</th>
                <td>
<pre id="git_sample_request">{
    "secret_key": "<?= git_get_secret_key() ?>",
    "origin": "<?= $result ? $result->getOrigin()->getZone()->name : 'Pichincha' ?>",
    "destiny": "<?= $result ? $result->getDestiny()->getZone()->name : 'Guayas' ?>",
    "type": "<?= $result ? $result->type->value : TransportConstants::MARINE->value ?>",
    "date_trip": "<?= git_date_trip_min()->format('Y-m-d') ?>",
    "departure_time": "<?= $result ? $result->getDepartureTime()->format() : date('H:i:s') ?>",
    "pax": 1
}</pre>
                    <button style="display: inline-block;" type="button" class="button git-copy-button"
                        data-target="#git_sample_request">Copiar</button>
                </td>
            </tr>
            <tr>
                <th scope="row">Endpoint generador de preorden</th>
                <td>
                    <code>POST</code>
                    <pre style="display: inline-block;"
                        id="git_sample_endpoint"><?= site_url('/wp-json/' . RegisterRoute::prefix . 'preorder') ?></pre>
                    <button style="display: block;" type="button" class="button git-copy-button"
                        data-target="#git_sample_endpoint">Copiar</button>
                </td>
            </tr>
            <tr>
                <th scope="row">Llamada a la API en javascript</th>
                <td>
                    <pre style="display: inline-block;" id="git_sample_js">
/**
 * Lista de origenes displonibles:
<?php
$zones = git_zones();
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
$types = TransportConstants::cases();
foreach ($types as $type) {
    if (in_array($type, [TransportConstants::LAND])) {
        continue;
    }
    $display = $type->label();
    echo " * - {$type->value} ({$display})\n";
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
            <button style="display: block;" type="button" class="button git-copy-button"
                data-target="#git_sample_js">Copiar</button>
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
        </script>
        <?php
    }
}