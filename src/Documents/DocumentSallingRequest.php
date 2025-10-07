<?php
namespace CentralTickets\Documents;

use CentralTickets\Route;
use CentralTickets\Transport;

final class DocumentSallingRequest extends Document
{
    private Route $route;
    private Transport $transport;
    private string $date_trip;

    public function __construct(
        Route $route,
        Transport $transport,
        string $date_trip
    ) {
        $this->route = $route;
        $this->transport = $transport;
        $this->date_trip = $date_trip;
    }

    public function get_document()
    {
        $dompdf = parent::get_document();
        $dompdf->setPaper('A4');
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        return $dompdf;
    }

    protected function get_head()
    {
        ob_start();
        ?>
        <title>SolicitudZarpe<?= $this->transport->code . date('Ymd') ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');

            * {
                font-family: 'Roboto', sans-serif;
                font-size: 12px;
            }

            header {
                text-align: center;
            }

            h1 {
                font-size: 18px;
            }

            header p,
            header h1 {
                margin: 0;
            }

            .table-bordered {
                width: 100%;
                border-collapse: collapse;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #000;
                padding: 3px 5px;
                text-align: left;
            }

            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                margin-right: 5px;
                vertical-align: middle;
                background-color: white;
            }

            .checkbox-label {
                display: inline-block;
                vertical-align: middle;
                margin-right: 15px;
            }

            .checkbox-container {
                margin: 10px 0;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    protected function get_body()
    {
        ob_start();
        ?>
        <header>
            <img src="<?= CENTRAL_BOOKING_URL . '/assets/img/shield-ecuador.png' ?>" alt="Escudo del Ecuador" width="100"
                style="margin-bottom: 20px;">
            <p>REPÚBLICA DEL ECUADOR</p>
            <p>CAPITANÍA DEL PUERTO DE <?= $this->route->get_origin()->name ?></p>
            <h1>SOLICITUD DE ZARPE Y ROL DE TRIPULACIÓN</h1>
            <p>Tráfico de Cabotaje</p>
            <p>(Para naves de 10 T.R.B. en adelante)</p>
        </header>
        <main>
            <div class="checkbox-container" style="text-align: right;">
                <div class="checkbox-label">
                    <span class="checkbox"></span> Carga
                </div>
                <div class="checkbox-label">
                    <span class="checkbox"></span> Lastre
                </div>
            </div>
            <p>Señor Capitán del puerto <?= $this->route->get_origin()->name ?> cúmpleme informar a usted que el:</p>
            <table style="width: 100%; margin: 20px 0;">
                <tbody>
                    <tr>
                        <td><b>Buque:</b></td>
                        <td><?= $this->transport->nicename ?></td>
                        <td><b>Matrícula:</b></td>
                        <td><?= $this->transport->code ?></td>
                    </tr>
                    <tr>
                        <td><b>Armador:</b></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td><b>Compañía:</b></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td><b>Zarpa del puerto de:</b></td>
                        <td><?= $this->route->get_origin()->name ?></td>
                        <td><b>Con destino a:</b></td>
                        <td><?= $this->route->get_destiny()->name ?></td>
                    </tr>
                    <tr>
                        <td><b>Fecha y hora de despacho:</b></td>
                        <td><?= git_datetime_format(date('Y-m-d H:i:s')) ?></td>
                        <td><b>Fecha y hora de zarpe:</b></td>
                        <td><?= git_date_format($this->date_trip) . ' ' . git_time_format($this->route->departure_time) ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><b>Fecha y hora de estimada de arribo:</b></td>
                        <td><?= git_date_format($this->date_trip) . ' ' . git_time_format($this->plus_time($this->route->departure_time, $this->route->duration_trip)) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Titulo</th>
                        <th>Plaza</th>
                        <th>Nombre</th>
                        <th>NAC</th>
                        <th>Matrícula</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $index = 1;
                    foreach ($this->transport->get_crew() as $member):
                        ?>
                        <tr>
                            <td><?= $index ?></td>
                            <td><?= $member['role'] ?></td>
                            <td><?= $member['role'] ?></td>
                            <td><?= $member['name'] ?></td>
                            <td>Ecuador</td>
                            <td><?= $member['license'] ?></td>
                        </tr>
                        <?php
                        $index++;
                    endforeach;
                    ?>
                </tbody>
            </table>
            <p style="text-align: center;">Certifico que la información aqui contenida es exacta, veraz y completa.</p>
        </main>
        <footer style="margin-top: 50px;">
            <p style="text-align: center;">
                <span style="border-top: 1px solid #000; display: inline-block; padding: 5px;">
                    EL CAPITÁN DEL PUERTO
                </span>
            </p>
            <p>Fecha de emision: <?= git_datetime_format(date('Y-m-d H:i:s')) ?></p>
        </footer>
        <?php
        return ob_get_clean();
    }

    private function plus_time(string $time_one, string $time_two)
    {
        $time_one = strtotime($time_one);
        $time_two = strtotime($time_two);
        if ($time_one === false || $time_two === false) {
            return '0:00:00';
        }
        $total_seconds = $time_one + $time_two;
        return date('H:i:s', $total_seconds);
    }
}
