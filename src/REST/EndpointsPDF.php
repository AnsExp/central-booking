<?php
namespace CentralBooking\REST;

use CentralBooking\Data\Passenger;
use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;
use CentralBooking\Implementation\Document\DocumentSallingRequest;
use CentralBooking\Implementation\Document\DocumentTrip;
use CentralBooking\PDF\DocumentPdf;
use Dompdf\Dompdf;
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
        if ($id_route === null || (is_numeric($id_route ?? 0) && $id_route <= 0)) {
            return false;
        }
        $id_transport = $request->get_param('transport');
        if ($id_transport === null || (is_numeric($id_transport ?? 0) && $id_transport <= 0)) {
            return false;
        }
        $temp_transport = git_transport_by_id($id_transport);
        if (!$temp_transport) {
            return false;
        }
        $temp_route = git_route_by_id($id_route);
        if (!$temp_route) {
            return false;
        }
        $this->route = $temp_route;
        $this->date_trip = $date_trip;
        $this->transport = $temp_transport;
        if (!$this->transport->takeRoute($this->route)) {
            return false;
        }
        $this->passengers = git_passengers(
            [
                'date_trip' => $date_trip,
                'id_route' => $id_route,
                'id_transport' => $id_transport,
                'approved' => true,
                'served' => false,
                'order_by' => 'name',
                'order' => 'ASC'
            ],
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
            (new DocumentPdf($document))->renderPdf(
                false,
                "Lista Embarque.pdf"
            );
        } else {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($this->get_invalid_notice());
            $dompdf->render();
            $dompdf->stream();
        }
    }

    public function get_salling_request(WP_REST_Request $request)
    {
        if ($this->init_entities($request)) {
            $document = new DocumentSallingRequest(
                $this->route,
                $this->transport,
                $this->date_trip
            );
            (new DocumentPdf($document))->renderPdf(
                false,
                "Solicitud de Zarpe.pdf"
            );
        } else {
            $dompdf = new Dompdf();
            $dompdf->loadHtml($this->get_invalid_notice());
            $dompdf->render();
            $dompdf->stream();
        }
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
