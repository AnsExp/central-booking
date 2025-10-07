<?php
namespace CentralTickets;

use CentralTickets\Components\Component;
use CentralTickets\Components\InputComponent;
use CentralTickets\Components\InputFloatingLabelComponent;
use CentralTickets\Components\Implementation\NationalitySelect;
use CentralTickets\Components\Implementation\TypeDocumentSelect;

class PassengerForm implements Component
{
    public function __construct(private int $passenger_count)
    {
    }

    public function compact()
    {
        ob_start();
        for ($i = 0; $i < $this->passenger_count; $i++) {
            $name_input = new InputComponent("passengers[$i][name]");
            $birthday_input = new InputComponent("passengers[$i][birthday]", 'date');
            $data_document_input = new InputComponent("passengers[$i][data_document]");
            $nationality_select = (new NationalitySelect("passengers[$i][nationality]"))->create();
            $type_document_select = (new TypeDocumentSelect("passengers[$i][type_document]"))->create();
            foreach ([
                $name_input,
                $birthday_input,
                $data_document_input,
                $nationality_select,
                $type_document_select
            ] as $input) {
                $input->set_required(true);
            }
            $floating_name = new InputFloatingLabelComponent($name_input, 'Nombre');
            $floating_birthday = new InputFloatingLabelComponent($birthday_input, 'Fecha de Nacimiento');
            $floating_nationality = new InputFloatingLabelComponent($nationality_select, 'Nacionalidad');
            $floating_data_document = new InputFloatingLabelComponent($data_document_input, 'Número de Documento');
            $floating_type_document = new InputFloatingLabelComponent($type_document_select, 'Tipo de Documento');
            ?>
            <div class="form_passenger <?= $i !== 0 ? 'mt-4' : '' ?>">
                <?php
                $floating_name->display();
                $floating_nationality->display();
                $floating_type_document->display();
                $floating_data_document->display();
                $floating_birthday->display();
                ?>
            </div>
            <?php
        }
        return ob_get_clean();
    }
}