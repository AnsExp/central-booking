<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;
use CentralTickets\Profile\Forms\FormInvoiceOperator;
use CentralTickets\Profile\Tables\TableInvoiceOperator;

class ProfilePaneInvoice implements Component
{
    private FormInvoiceOperator $form_invoice;
    private TableInvoiceOperator $table_invoice;

    public function __construct()
    {
        $this->form_invoice = new FormInvoiceOperator;
        $this->table_invoice = new TableInvoiceOperator;
    }

    public function compact()
    {
        ob_start();
        echo $this->form_invoice->compact();
        echo $this->table_invoice->compact();
        return ob_get_clean();
    }
}
