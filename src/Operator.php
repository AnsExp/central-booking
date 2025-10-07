<?php
namespace CentralTickets;

use CentralTickets\Persistence\CouponRepository;
use CentralTickets\Persistence\TransportRepository;
use WP_User;
use WP_Post;

class Operator extends WP_User
{
    /**
     * @var array<WP_Post>
     */
    private array $coupons;
    /**
     * @var array<Transport>
     */
    private array $transports;
    public string $phone;
    public bool $logo_sale = false;

    private array $business_plan = [
        'limit' => 0,
        'counter' => 0,
    ];

    /**
     * @return array{counter: int, limit: int}
     */
    public function get_business_plan()
    {
        return [
            'limit' => $this->business_plan['limit'],
            'counter' => $this->business_plan['counter'],
        ];
    }

    public function set_business_plan(int $limit, int $counter)
    {
        $this->business_plan['limit'] = $limit;
        $this->business_plan['counter'] = $counter;
    }

    public function __construct(int $id_user = 0)
    {
        parent::__construct($id_user);
    }

    public function get_coupons()
    {
        if (!isset($this->coupons)) {
            $repository = new CouponRepository;
            $this->coupons = $repository->get_coupons_by_operator($this);
        }

        return $this->coupons;
    }

    /**
     * @param array<WP_Post> $coupons
     * @return void
     */
    public function set_coupons(array $coupons)
    {
        $this->coupons = $coupons;
    }

    public function get_transports()
    {
        if (!isset($this->transports)) {
            $repository = new TransportRepository;
            $this->transports = $repository->find_by(
                ['id_operator' => $this->ID],
                'nicename'
            );
        }
        return $this->transports;
    }

    public function set_transports(array $transports)
    {
        $this->transports = $transports;
    }
}
