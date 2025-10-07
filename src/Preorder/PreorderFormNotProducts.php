<?php
namespace CentralTickets\Preorder;

use CentralTickets\Components\Component;

class PreorderFormNotProducts implements Component
{
    public function compact()
    {
        ob_start();
        ?>
        <div class="container mt-4">
            <div class="alert alert-warning text-center" role="alert">
                <h5 class="alert-heading">Sin productos disponibles</h5>
                <p>No hay productos que cumplan tu orden. Por favor, ve a la tienda.</p>
                <a href="<?= esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary mt-2">
                    Ir a la tienda
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
