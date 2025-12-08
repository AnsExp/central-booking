<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\InputComponent;
use CentralTickets\Services\Actions\DateTrip;

class DateTripInput
{
    public function __construct(private string $name = 'date_trip')
    {
    }

    public function create()
    {
        $input = new InputComponent($this->name, 'date');
        $date_min = DateTrip::min_date();
        $input->set_value($date_min);
        $input->set_attribute('min', $date_min);
        return $input;
    }
}
