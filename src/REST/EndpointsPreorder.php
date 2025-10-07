<?php
namespace CentralTickets\REST;

use CentralTickets\Preorder\PreorderCreator;
use WP_REST_Request;
use WP_REST_Response;

class EndpointsPreorder
{
    public function init_endpoints()
    {
        RegisterRoute::register(
            'preorder',
            'POST',
            fn(WP_REST_Request $request) =>
            $this->create_preorder($request)
        );
    }

    private function create_preorder(WP_REST_Request $request)
    {
        $secret_key = git_get_setting('preorder_secret_key', null);
        if ($secret_key === null) {
            return new WP_REST_Response(['error' => 'Secret key not found'], 404);
        }
        $data = $request->get_json_params();
        if (
            !isset(
            $data['pax'],
            $data['type'],
            $data['origin'],
            $data['destiny'],
            $data['date_trip'],
            $data['secret_key'],
            $data['departure_time'],
        )
        ) {
            return new WP_REST_Response(['error' => 'Request format invalid'], 400);
        }
        if ($data['secret_key'] !== $secret_key) {
            return new WP_REST_Response(['error' => 'Invalid secret key'], 403);
        }
        $preorder = PreorderCreator::create(
            $data['pax'],
            $data['transport_id'],
            $data['origin'],
            $data['destiny'],
            $data['date_trip'],
            $data['type'],
            $data['departure_time'],
            $data['passengers_info'] ?? [],
        );
        if ($preorder === null) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'Failed to create preorder'
            ], 400);
        }
        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'order_id' => $preorder->get_order()->get_id()
            ]
        ]);
    }
}