<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\InputFloatingLabelComponent;
use CentralBooking\Implementation\GUI\NationalitySelect;
use CentralBooking\Implementation\GUI\TypeDocumentSelect;

class PassengerForm implements ComponentInterface
{
    public function compact()
    {
        $name_input = new InputComponent("passenger[name]");
        $birthday_input = new InputComponent("passenger[birthday]", 'date');
        $data_document_input = new InputComponent("passenger[data_document]");
        $nationality_select = (new NationalitySelect("passenger[nationality]"))->create();
        $type_document_select = (new TypeDocumentSelect("passenger[type_document]"))->create();
        foreach ([
            $name_input,
            $birthday_input,
            $data_document_input,
            $nationality_select,
            $type_document_select
        ] as $input) {
            $input->setRequired(true);
        }
        $floating_name = new InputFloatingLabelComponent($name_input, 'Nombre');
        $floating_birthday = new InputFloatingLabelComponent($birthday_input, 'Fecha de Nacimiento');
        $floating_nationality = new InputFloatingLabelComponent($nationality_select, 'Nacionalidad');
        $floating_data_document = new InputFloatingLabelComponent($data_document_input, 'Número de Documento');
        $floating_type_document = new InputFloatingLabelComponent($type_document_select, 'Tipo de Documento');

        $output = '';

        $output .= $floating_name->compact();
        $output .= $floating_nationality->compact();
        $output .= $floating_type_document->compact();
        $output .= $floating_data_document->compact();
        $output .= $floating_birthday->compact();

        return $output;
    }
}