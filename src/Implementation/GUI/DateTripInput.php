<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\GUI\InputComponent;

class DateTripInput
{
    public function __construct(private string $name = 'date_trip')
    {
    }

    public function create()
    {
        $input = new InputComponent($this->name, 'date');
        $date_min = git_date_trip_min();
        // $input->setValue($date_min->format('Y-m-d'));
        $input->attributes->set('min', $date_min->format('Y-m-d'));
        return $input;
    }
}
