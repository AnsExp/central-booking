<?php
namespace CentralTickets\Services;

use CentralTickets\Zone;
use CentralTickets\Persistence\ZoneRepository;

/**
 * @extends parent<Zone>
 */
class ZoneService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new ZoneRepository);
    }

    /**
     * @param Zone $zone
     * @return bool
     */
    protected function verify($zone)
    {
        $pass = true;
        if (!$this->verify_field($zone, 'name')) {
            $this->error_stack = ['Ya existe una zona con el mismo nombre.'];
            $pass = false;
        }
        return $pass;
    }
}
