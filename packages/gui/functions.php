<?php

use CentralBooking\GUI\ComponentBuilder;

function git_string_to_component(string $string)
{
    return ComponentBuilder::create($string);
}