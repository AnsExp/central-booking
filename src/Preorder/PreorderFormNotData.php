<?php
namespace CentralTickets\Preorder;

use CentralBooking\GUI\ComponentInterface;

class PreorderFormNotData implements ComponentInterface
{
    public function compact()
    {
        ob_start();
        ?>
        <div class="alert alert-warning" role="alert">
            No hay datos mínimos suficientes para continuar con la reserva.
        </div>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
            Ir a la tienda
        </a>
        <?php
        return ob_get_clean();
    }
}
