<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;

final class SettingsOperators implements DisplayerInterface
{
    private InputComponent $operator_file_size;
    private InputComponent $operator_file_extensions;
    private array $allowed_extensions;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->operator_file_size = new InputComponent('operator_file_size', 'number');
        $this->operator_file_extensions = new InputComponent('operator_file_extensions');

        $this->operator_file_size->attributes->set('min', '1');
        $this->operator_file_size->attributes->set('max', '1024');
        $this->operator_file_size->attributes->set('step', '1');

        $this->operator_file_size->setValue(git_get_setting('operator_file_size', 10));

        $extensions_array = git_get_setting('operator_file_extensions', []);
        $extensions_string = is_array($extensions_array) ? implode(', ', $extensions_array) : '';
        $this->operator_file_extensions->setValue($extensions_string);
        $this->allowed_extensions = [
            '.jpg',
            '.jpeg',
            '.png',
            '.gif',
            '.webp',
            '.svg',
            '.bmp',
            '.ico',
            '.tiff',
            '.pdf',
            '.doc',
            '.docx',
            '.xls',
            '.xlsx',
            '.ppt',
            '.pptx',
            '.txt',
            '.rtf',
            '.zip',
            '.rar',
            '.7z',
            '.tar',
            '.gz',
            '.mp4',
            '.avi',
            '.mov',
            '.wmv',
            '.flv',
            '.webm',
            '.mp3',
            '.wav',
            '.ogg',
            '.m4a',
            '.flac',
            '.css',
            '.js',
            '.html',
            '.xml',
            '.json'
        ];
    }

    public function render()
    {
        ?>
        <form id="git-settings-form"
            action="<?= esc_url(add_query_arg('action', 'git_settings', admin_url('admin-ajax.php'))) ?>" method="post">
            <input type="hidden" name="nonce" value="<?= wp_create_nonce('git_settings_nonce') ?>" />
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const fileSizeInput = document.querySelector('input[name="operator_file_size"]');
                    const extensionsInput = document.querySelector('input[name="operator_file_extensions"]');
                    const fileSizeError = document.getElementById('file-size-error');
                    const extensionsError = document.getElementById('extensions-error');
                    const extensionsPreview = document.getElementById('extensions-preview');

                    if (fileSizeInput) {
                        fileSizeInput.addEventListener('input', function () {
                            const value = parseInt(this.value);
                            const min = <?= 1 ?>;
                            const max = <?= 1024 ?>;

                            if (isNaN(value) || value < min || value > max) {
                                fileSizeError.textContent = `El tamaño debe estar entre ${min} y ${max} MB.`;
                                fileSizeError.style.display = 'block';
                                this.style.borderColor = '#dc3232';
                            } else {
                                fileSizeError.style.display = 'none';
                                this.style.borderColor = '#7e8993';
                            }
                        });
                    }

                    if (extensionsInput) {
                        extensionsInput.addEventListener('input', function () {
                            const value = this.value.trim();

                            if (!value) {
                                extensionsPreview.innerHTML = '';
                                extensionsError.style.display = 'none';
                                return;
                            }

                            const extensions = value.split(',').map(ext => ext.trim().toLowerCase());
                            const allowedExtensions = <?= git_serialize($this->allowed_extensions) ?>;
                            let previewHtml = '<strong>Vista previa:</strong> ';
                            let hasInvalid = false;

                            extensions.forEach(ext => {
                                if (!ext) return;

                                const normalizedExt = ext.startsWith('.') ? ext.substring(1) : ext;
                                const withDot = '.' + normalizedExt;

                                const isValid = allowedExtensions.includes(withDot);
                                const tagClass = isValid ? 'extension-tag' : 'extension-tag invalid';

                                previewHtml += `<span class="${tagClass}">${withDot}</span>`;

                                if (!isValid) {
                                    hasInvalid = true;
                                }
                            });

                            extensionsPreview.innerHTML = previewHtml;

                            if (hasInvalid) {
                                extensionsError.textContent = 'Algunas extensiones no están permitidas (marcadas en rojo).';
                                extensionsError.style.display = 'block';
                            } else {
                                extensionsError.style.display = 'none';
                            }
                        });

                        extensionsInput.dispatchEvent(new Event('input'));
                    }
                });
            </script>
            <input type="hidden" name="scope" value="operators">
            <?php wp_nonce_field('git_settings_nonce', 'nonce'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->operator_file_size->getLabel('Tamaño máximo del archivo (MB)')->render() ?>
                    </th>
                    <td>
                        <?php $this->operator_file_size->render() ?>
                        <p class="description">
                            Ingrese el tamaño máximo del archivo en megabytes (MB).
                            Valor entre 1 y 1024 MB.
                        </p>
                        <div id="file-size-error" class="error-message"
                            style="display: none; color: #dc3232; font-weight: bold;">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->operator_file_extensions->getLabel('Extensiones de archivo permitidas')->render() ?>
                    </th>
                    <td>
                        <?php $this->operator_file_extensions->render() ?>
                        <p class="description">
                            Ingrese las extensiones de archivo permitidas, separadas por comas.<br>
                            <strong>Ejemplo:</strong> jpg, png, pdf, doc, zip<br>
                            <em>Nota: Los puntos se agregarán automáticamente si no los incluye.</em>
                        </p>
                        <div id="extensions-preview" class="extensions-preview" style="margin-top: 10px;"></div>
                        <div id="extensions-error" class="error-message"
                            style="display: none; color: #dc3232; font-weight: bold;">
                        </div>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button-primary" id="git-save-button">
                    Guardar configuraciones
                </button>
            </p>
            <style>
                .extensions-preview {
                    padding: 10px;
                    background: #f1f1f1;
                    border-left: 4px solid #0073aa;
                    border-radius: 3px;
                    font-family: monospace;
                    font-size: 12px;
                }

                .extensions-preview:empty {
                    display: none;
                }

                .extension-tag {
                    display: inline-block;
                    background: #0073aa;
                    color: white;
                    padding: 2px 6px;
                    border-radius: 3px;
                    margin: 2px;
                    font-size: 11px;
                }

                .extension-tag.invalid {
                    background: #dc3232;
                }
            </style>
        </form>
        <?php
    }
}