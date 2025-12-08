<?php
namespace CentralTickets\Services\Actions;

use DateTime;

class DateTrip
{
    public static function valid(string $date_trip)
    {
        $date = DateTime::createFromFormat('Y-m-d', $date_trip);
        $min_date = DateTime::createFromFormat('Y-m-d', self::min_date());
        if (!$date || !$min_date)
            return false;
        return $date->format('Y-m-d') >= $min_date->format('Y-m-d');
    }

    public static function min_date()
    {
        $offset = self::get_days_without_sale();
        $min_date = new DateTime;
        if ($offset > 0) {
            $min_date->modify("+$offset days");
        } else if ($offset < 0) {
            $min_date->modify("$offset days");
        }
        return $min_date->format('Y-m-d');
    }

    public static function set_days_without_sale(int $days_without_sale)
    {
        git_set_setting('days_without_sale', $days_without_sale);
        return true;
    }

    public static function get_days_without_sale()
    {
        return git_get_setting('days_without_sale', 0);
    }
}
