<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\InputComponent;
use DateTime;

class DateTripInput
{
    public function __construct(private string $name = 'date_trip')
    {
    }

    public function create()
    {
        $input = new InputComponent($this->name, 'date');
        $date_min = $this->get_min_date();
        $input->set_value($date_min);
        $input->set_attribute('min', $date_min);
        return $input;
    }

    private function get_min_date()
    {
        $date_min_buyer = git_get_setting('date_min_buyer', 'none');
        if ($date_min_buyer === 'none') {
            return '';
        }
        if ($date_min_buyer === 'today') {
            return (new DateTime())->format('Y-m-d');
        }
        if ($date_min_buyer === 'custome') {
            $date_min_buyer_custome = git_get_setting('date_min_buyer_custome', 0);
            if (is_numeric($date_min_buyer_custome) && $date_min_buyer_custome > 0) {
                return (new DateTime)->modify("+{$date_min_buyer_custome} days")->format('Y-m-d');
            }
        }
        return '';
    }
}
