<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;
use InvalidArgumentException;
use WP_User;
use WP_Post;

class Operator
{
    /**
     * @var array<WP_Post>
     */
    private array $coupons;
    /**
     * @var array<Transport>
     */
    private array $transports;
    private WP_User $user;
    public string $brand_media = '';
    private string $phone = '';

    private array $business_plan = [
        'limit' => 0,
        'counter' => 0,
    ];

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(WP_User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array{counter: int, limit: int}
     */
    public function getBusinessPlan()
    {
        return [
            'limit' => $this->business_plan['limit'],
            'counter' => $this->business_plan['counter'],
        ];
    }

    public function setBusinessPlan(int $limit, int $counter)
    {
        if ($limit < $counter) {
            throw new InvalidArgumentException('Business plan limit cannot be less than counter.');
        }
        $this->business_plan['limit'] = $limit;
        $this->business_plan['counter'] = $counter;
    }

    public function getCoupons()
    {
        if (!isset($this->coupons)) {
            $this->coupons = LazyLoader::loadCouponsByOperator($this);
        }

        return $this->coupons;
    }

    /**
     * @param array<WP_Post> $coupons
     * @return void
     */
    public function setCoupons(array $coupons)
    {
        $this->coupons = $coupons;
    }

    public function getTransports()
    {
        if (!isset($this->transports)) {
            $this->transports = LazyLoader::loadTransportsByOperator($this);
        }
        return $this->transports;
    }

    public function setTransports(array $transports)
    {
        $this->transports = $transports;
    }

    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getBrandMedia()
    {
        return $this->brand_media;
    }

    public function setBrandMedia(string $brand_media)
    {
        $this->brand_media = $brand_media;
    }
}
