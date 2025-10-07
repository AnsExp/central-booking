<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;
use CentralTickets\REST\RegisterRoute;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Components\InputComponent;
use CentralTickets\Persistence\RouteRepository;

final class SettingsPreorder implements Displayer
{
    private InputComponent $secret_key;

    public function __construct()
    {
        $this->secret_key = new InputComponent('secret_key', 'text');
        $this->secret_key->set_value(git_get_setting('preorder_secret_key', ''));
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
                    <th scope="row">Ejemplo de Body para generar una preorden</th>
                    <td>
<pre id="git_sample_request">{
    "secret_key": "<?= git_get_setting('preorder_secret_key', '') ?>",
    "origin": {
        "name": "<?= $route_sample ? $route_sample->get_origin()->name : 'Quito' ?>",
        "type": "location"
    },
    "destiny": {
        "name": "<?= $route_sample ? $route_sample->get_destiny()->get_zone()->name : 'Guayas' ?>",
        "type": "zone"
    },
    "type": "<?= $route_sample ? $route_sample->type : TransportConstants::MARINE ?>",
    "date_trip": "<?= date('Y-m-d') ?>",
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