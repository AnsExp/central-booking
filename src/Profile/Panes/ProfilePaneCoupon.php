<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\Profile\Forms\FormEditCouponOperator;
use CentralBooking\Profile\Forms\FormSearchCouponOperator;
use CentralBooking\Profile\Tables\TableCouponOperator;

class ProfilePaneCoupon implements ComponentInterface
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
