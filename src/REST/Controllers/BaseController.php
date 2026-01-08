<?php
namespace CentralBooking\REST\Controllers;

use CentralTickets\Services\PackageData\PackageData;
use CentralTickets\Services\ArrayParser\ArrayParser;
use CentralTickets\Services\BaseService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @template T
 */
abstract class BaseController
{
    /**
     * @var BaseService<T>
     */
    protected $service;
    /**
     * @var ArrayParser<T>
     */
    protected $response_creator;
    public array $issues = [];

    /**
     * @param ArrayParser<T> $name
     * @param BaseService<T> $service
     */
    protected function __construct($service, $response_creator)
    {
        $this->service = $service;
        $this->response_creator = $response_creator;
    }

    public function get_all(WP_REST_Request $request)
    {
        return new WP_REST_Response(array_map(
            [$this->response_creator, 'get_array'],
            $this->service->list($request->get_query_params())
        ));
    }

    public function get(WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        if (!is_numeric($id) || $id < 0) {
            return new WP_REST_Response(['message' => 'ID inválido.'], 400);
        }
        $entity = $this->service->find($id);
        if ($entity === null) {
            return new WP_REST_Response([
                'message' => 'Registro no encontrado.'
            ], 400);
        }
        return new WP_REST_Response($this->response_creator->get_array($entity));
    }

    public function post(WP_REST_Request $request)
    {
        $request->set_param('id', -1);
        return $this->put($request);
    }

    public function put(WP_REST_Request $request)
    {
        $data = $this->parse_payload($request->get_json_params());
        if (!$this->validate($data)) {
            return new WP_REST_Response([
                'message' => 'Error en la capa de formato.',
                'stack' => $this->issues
            ], 400);
        }
        $result = $this->service->save($data, $request->get_param('id'));
        if ($result === null) {
            return new WP_REST_Response([
                'message' => 'Error en la capa de negocio.',
                'stack' => $this->service->error_stack
            ], 400);
        }
        return new WP_REST_Response($this->response_creator->get_array($result));
    }

    public function delete(WP_REST_Request $request)
    {
        $id = intval($request->get_param('id'));
        if ($id <= 0) {
            return new WP_REST_Response([
                'message' => 'ID inválido.',
                'stack' => $this->issues
            ], 400);
        }
        $deleted = $this->service->remove($id);
        if (!$deleted) {
            return new WP_REST_Response([
                'message' => "Registro con el ID {$id} no existente.",
                'stack' => $this->service->error_stack
            ], 400);
        }
        return new WP_REST_Response([
            'message' => 'Registro eliminado correctamente.',
            'stack' => $this->service->error_stack
        ]);
    }

    public function page(WP_REST_Request $request)
    {
        $pagination = $this->service->paginated(
            $request->get_params(),
            $request->get_params()['order_by'] ?? 'id',
            $request->get_params()['order'] ?? 'ASC',
            $request->get_params()['page_number'] ?? 1,
            $request->get_params()['page_size'] ?? 10,
        );
        return new WP_REST_Response([
            'pagination' => $pagination['pagination'],
            'data' => array_map(
                fn($entity) =>
                $this->response_creator->get_array($entity),
                $pagination['data']
            )
        ]);
    }

    /**
     * @param array $payload
     * @return PackageData<T>
     */
    abstract protected function parse_payload(array $payload);

    /**
     * @param PackageData<T> $data
     * @return bool
     */
    abstract protected function validate($data);
}
