<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;

class TypeDocumentSelect
{
    private static array $types;

    public function __construct(private string $name = 'type_document')
    {
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->get_documents() as $document) {
            $selectComponent->addOption($document, $document);
        }

        return $selectComponent;
    }

    private function get_documents()
    {
        if (isset(self::$types)) {
            return self::$types;
        }

        $jsonFilePath = CENTRAL_BOOKING_DIR . '/assets/data/documents.json';
        $jsonString = file_get_contents($jsonFilePath);

        if ($jsonString === false) {
            die('Error: No se pudo leer el archivo JSON.');
        }

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            die('Error al decodificar el JSON: ' . json_last_error_msg());
        }

        self::$types = $data;

        return self::$types;
    }
}
