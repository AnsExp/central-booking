<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class NationalitySelect
{
    private static array $countries;

    public function __construct(private string $name = 'nationality')
    {
    }

    public function create(bool $multiple = false)
    {
        $select = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);
        $select->addOption('Seleccione...', '');
        foreach ($this->get_countries() as $country) {
            $select->addOption($country, $country);
        }
        return $select;
    }

    private function get_countries()
    {
        if (isset(self::$countries)) {
            return self::$countries;
        }
        $jsonFilePath = CENTRAL_BOOKING_DIR . '/assets/data/countries.json';
        $jsonString = file_get_contents($jsonFilePath);

        if ($jsonString === false) {
            die('Error: No se pudo leer el archivo JSON.');
        }

        self::$countries = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            die('Error al decodificar el JSON: ' . json_last_error_msg());
        }

        return self::$countries;
    }
}
