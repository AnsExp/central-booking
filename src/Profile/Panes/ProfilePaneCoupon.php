<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;
use CentralTickets\Profile\Forms\FormEditCouponOperator;
use CentralTickets\Profile\Forms\FormSearchCouponOperator;
use CentralTickets\Profile\Tables\TableCouponOperator;

class ProfilePaneCoupon implements Component
{
    public function compact()
    {
        ob_start();
        $action = $_GET['action'] ?? 'search';
        if ($action === 'edit') {
            echo (new FormEditCouponOperator())->compact();
        } elseif ($action === 'search') {
            echo (new FormSearchCouponOperator())->compact();
            echo (new TableCouponOperator())->compact();
        } else {
            ?>
            <p>Lo sentimos, la opción que has seleccionado no está disponible.</p>
            <p>Puedes <a href="<?= add_query_arg(['action' => 'search']) ?>">volver a la búsqueda</a>.</p>
            <?php
        }
        return ob_get_clean();
    }
}
