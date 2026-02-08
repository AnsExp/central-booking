<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

class FormTrip implements DisplayerInterface
{
    private SelectComponent $origin_select;
    private SelectComponent $destiny_select;
    private SelectComponent $schedule_select;
    private SelectComponent $transport_select;
    private InputComponent $date_from_input;
    private InputComponent $date_to_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $route_selector = new SelectorRouteCombine();

        $this->date_to_input = new InputComponent('date_to', 'date');
        $this->date_from_input = new InputComponent('date_from', 'date');

        $this->origin_select = $route_selector->get_origin_select('id_origin');
        $this->destiny_select = $route_selector->get_destiny_select('id_destiny');
        $this->schedule_select = $route_selector->get_time_select('time');
        $this->transport_select = $route_selector->get_transport_select('id_transport');

        $this->origin_select->setValue($_GET['id_origin'] ?? '');
        $this->date_to_input->setValue($_GET['date_to'] ?? '');
        $this->destiny_select->setValue($_GET['id_destiny'] ?? '');
        $this->schedule_select->setValue($_GET['time'] ?? '');
        $this->date_from_input->setValue($_GET['date_from'] ?? '');
        $this->transport_select->setValue($_GET['id_transport'] ?? '');

        $this->date_to_input->setRequired(true);
        $this->date_from_input->setRequired(true);

        $this->date_to_input->attributes->set('readonly', '');

        wp_enqueue_script_module(
            'central-tickets-operator-form-trip',
            CENTRAL_BOOKING_URL . '/assets/js/operator/form-trip-operator.js',
            [],
            time(),
        );
    }

    public function render()
    {
        $this->showMessage();
        ?>
        <div class="git-profile-section">
            <div class="git-profile-card">
                <div class="git-profile-card-header">
                    <h3 class="git-card-title">
                        <i class="dashicons dashicons-location"></i>
                        Buscar viajes
                    </h3>
                </div>
                <div class="git-profile-card-body">
                    <form method="get">
                        <input type="hidden" name="tab" value="<?= $_GET['tab'] ?? '' ?>">
                        
                        <!-- Ruta -->
                        <div class="git-form-section">
                            <h4 class="git-form-section-title">
                                <i class="dashicons dashicons-route"></i>
                                Ruta del viaje
                            </h4>
                            <div class="git-form-row">
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->origin_select->getLabel('Origen')->compact(); ?>
                                    <?= $this->origin_select->compact(); ?>
                                </div>
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->destiny_select->getLabel('Destino')->compact(); ?>
                                    <?= $this->destiny_select->compact(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del viaje -->
                        <div class="git-form-section">
                            <h4 class="git-form-section-title">
                                <i class="dashicons dashicons-clock"></i>
                                Detalles del viaje
                            </h4>
                            <div class="git-form-row">
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->schedule_select->getLabel('Horario')->compact(); ?>
                                    <?= $this->schedule_select->compact(); ?>
                                </div>
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->transport_select->getLabel('Transporte')->compact(); ?>
                                    <?= $this->transport_select->compact(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Rango de fechas -->
                        <div class="git-form-section">
                            <h4 class="git-form-section-title">
                                <i class="dashicons dashicons-calendar-alt"></i>
                                Período de búsqueda
                            </h4>
                            <div class="git-form-row">
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->date_from_input->getLabel('Fecha desde')->compact(); ?>
                                    <?= $this->date_from_input->compact(); ?>
                                </div>
                                <div class="git-form-group git-form-group-half">
                                    <?= $this->date_to_input->getLabel('Fecha hasta')->compact(); ?>
                                    <?= $this->date_to_input->compact(); ?>
                                </div>
                            </div>
                        </div>

                        <div class="git-form-actions">
                            <button type="submit" class="git-btn git-btn-primary">
                                Buscar viajes
                            </button>
                            <button type="reset" class="git-btn git-btn-secondary" onclick="this.form.reset(); window.location.href=window.location.pathname+'?page=git_operator_panel&tab=trips';">
                                Limpiar filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}
