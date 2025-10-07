<?php
namespace CentralTickets\REST;

use Dompdf\Dompdf;
use CentralTickets\Route;
use CentralTickets\Transport;
use CentralTickets\Passenger;
use CentralTickets\Documents\DocumentTrip;
use CentralTickets\Documents\DocumentSallingRequest;
use CentralTickets\Persistence\RouteRepository;
use CentralTickets\Persistence\PassengerRepository;
use CentralTickets\Persistence\TransportRepository;
use WP_REST_Request;

class EndpointsPDF
{
    private string $date_trip;
    private Route $route;
    private Transport $transport;
    /**
     * @var array<Passenger>
     */
    private array $passengers;
    private string $color_header = '#def7ffff';

    public function init_endpoints()
    {
        RegisterRoute::register(
            'pdf_trip',
            'GET',
            [$this, 'get_trip']
        );
        RegisterRoute::register(
            'pdf_salling_request',
            'GET',
            [$this, 'get_salling_request']
        );
    }

    private function init_entities(WP_REST_Request $request)
    {
        $date_trip = $request->get_param('date');
        if ($date_trip === null || $date_trip === '') {
            return false;
        }
        $date_trip = date('Y-m-d', strtotime($date_trip));
        if (!$date_trip) {
            return false;
        }
        $id_route = $request->get_param('route');
        if (!is_numeric($id_route) || $id_route <= 0) {
            return false;
        }
        $id_transport = $request->get_param('transport');
        if (!is_numeric($id_transport) || $id_transport <= 0) {
            return false;
        }
        $temp_transport = (new TransportRepository)->find($id_transport);
        if (!$temp_transport) {
            return false;
        }
        $temp_route = (new RouteRepository)->find($id_route);
        if (!$temp_route) {
            return false;
        }
        $this->route = $temp_route;
        $this->date_trip = $date_trip;
        $this->transport = $temp_transport;
        if (!$this->transport->use_route($this->route)) {
            return false;
        }
        $passengers_repository = new PassengerRepository();
        $this->passengers = $passengers_repository->find_by(
            [
                'date_trip' => $date_trip,
                'id_route' => $id_route,
                'id_transport' => $id_transport,
                'approved' => true,
                'served' => false,
            ],
            'name',
            'ASC'
        );
        return true;
    }

    public function get_trip(WP_REST_Request $request)
    {
        if ($this->init_entities($request)) {
            $document = new DocumentTrip(
                $this->route,
                $this->transport,
                $this->passengers,
                $this->date_trip
            );
            $dompdf = $document->get_document();
        } else {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($this->get_invalid_notice());
            $dompdf->setPaper('A4');
        }


        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        $dompdf->render();

        $dompdf->stream(
            "Lista Embarque.pdf",
            ['Attachment' => false]
        );
    }

    public function get_salling_request(WP_REST_Request $request)
    {
        if ($this->init_entities($request)) {
            $document = new DocumentSallingRequest(
                $this->route,
                $this->transport,
                $this->date_trip
            );
            $dompdf = $document->get_document();
        } else {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($this->get_invalid_notice());
            $dompdf->setPaper('A4');
        }

        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        
        $dompdf->render();

        $dompdf->stream(
            "Solicitud de Zarpe.pdf",
            ['Attachment' => false]
        );
    }

    private function get_invalid_notice()
    {
        ob_start();
        ?>
        <p>No se ha encontrado información válida para generar el PDF.</p>
        <ul>
            <li>Fecha con formato inválido.</li>
            <li>Ruta o transporte no válido.</li>
            <li>Ruta no asignada al transporte.</li>
        </ul>
        <?php
        return ob_get_clean();
    }
}
