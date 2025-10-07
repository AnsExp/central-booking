<?php
namespace CentralTickets\Services\Actions;

use CentralTickets\Constants\DateTripConstants;
use DateTime;

class DateTrip
{
    public static function valid(string $date_trip)
    {
        $rule = self::get_rule();

        if ($rule === DateTripConstants::NONE) {
            return true;
        }

        $date = DateTime::createFromFormat('Y-m-d', $date_trip);
        if (!$date)
            return false;

        if ($rule === DateTripConstants::TODAY) {
            $today = new DateTime();
            return $date->format('Y-m-d') === $today->format('Y-m-d');
        }

        if ($rule === DateTripConstants::CUSTOME) {
            $offset = self::get_offset();
            $min_date = (new DateTime)->modify("+$offset days");
            return $date->format('Y-m-d') >= $min_date->format('Y-m-d');
            // return $date >= $min_date;
        }

        return true;
    }

    public static function min_date()
    {
        $rule = self::get_rule();

        if ($rule === DateTripConstants::NONE) {
            return null;
        }

        if ($rule === DateTripConstants::TODAY) {
            $today = new DateTime();
            return $today->format('Y-m-d');
        }

        if ($rule === DateTripConstants::CUSTOME) {
            $offset = self::get_offset();
            $min_date = (new DateTime)->modify("+$offset days");
            return $min_date->format('Y-m-d');
        }

        return null;
    }

    public static function set_rule(string $rule, int $offset)
    {
        if (!DateTripConstants::is_valid($rule)) {
            return false;
        }

        git_set_setting('date_min_buyer', $rule);

        if (DateTripConstants::CUSTOME === $rule) {
            git_set_setting('date_min_buyer_custome', $offset);
        }

        return true;
    }

    public static function get_rule()
    {
        return git_get_setting('date_min_buyer', DateTripConstants::NONE);
    }

    public static function get_offset()
    {
        $rule = self::get_rule();
        if ($rule !== DateTripConstants::CUSTOME) {
            return false;
        }
        return git_get_setting('date_min_buyer_custome', 0);
    }
}
