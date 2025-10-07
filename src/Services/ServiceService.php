<?php
namespace CentralTickets\Services;

use CentralTickets\Service;
use CentralTickets\Persistence\ServiceRepository;

/**
 * @extends parent<Service>
 */
class ServiceService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new ServiceRepository);
    }

    /**
     * @param Service $service
     * @return bool
     */
    protected function verify($service)
    {
        $pass = true;
        if (!$this->verify_field($service, 'name')) {
            $this->error_stack = ['Ya existe servicio con el mismo nombre.'];
            $pass = false;
        }
        return $pass;
    }
}
