<?php
namespace CentralTickets\REST\Controllers;

use CentralTickets\Operator;
use CentralTickets\Services\ArrayParser\OperatorArray;
use CentralTickets\Services\OperatorService;
use CentralTickets\Services\PackageData\OperatorData;

/**
 * @extends parent<Operator>
 */
class OperatorController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            new OperatorService,
            new OperatorArray()
        );
    }

    protected function parse_payload(array $payload)
    {
        return new OperatorData(
            isset($payload['firstname']) ? trim($payload['firstname']) : '',
            isset($payload['lastname']) ? trim($payload['lastname']) : '',
            isset($payload['phone']) ? trim($payload['phone']) : '',
            $payload['coupons'] ?? [],
            $payload['coupons_counter']['counter'] ?? 0,
            $payload['coupons_counter']['limit'] ?? 0,
        );
    }

    /**
     * @param OperatorData $data
     * @return bool
     */
    protected function validate($data)
    {
        $pass = true;
        $this->issues = [];

        if (empty($data->firstname)) {
            $this->issues[] = "El nombre no puede estar vacío.";
            $pass = false;
        }

        if (empty($data->lastname)) {
            $this->issues[] = "El apellido no puede estar vacío.";
            $pass = false;
        }

        if (empty($data->phone)) {
            $this->issues[] = "El teléfono no puede estar vacío.";
            $pass = false;
        }

        if (!is_array($data->coupons) || !$this->all_numeric($data->coupons)) {
            $this->issues[] = "Coupons debe ser un array de números.";
            $pass = false;
        }

        return $pass;
    }

    private function all_numeric($array)
    {
        if (empty($array)) {
            return true;
        }
        return is_array($array) && count($array) > 0 && array_reduce($array, fn($carry, $item) => $carry && is_numeric($item), true);
    }
}
