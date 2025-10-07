<?php
namespace CentralTickets\Documents;

use Dompdf\Dompdf;

abstract class Document
{
    public function get_document()
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->get_template());
        return $dompdf;
    }

    private function get_template()
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?= $this->get_head() ?>
        </head>

        <body>
            <?= $this->get_body() ?>
        </body>

        </html>
        <?php
        return ob_get_clean();
    }

    abstract protected function get_head();

    abstract protected function get_body();
}
