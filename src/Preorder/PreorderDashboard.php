<?php
namespace CentralTickets\Preorder;

use CentralTickets\Components\Component;
use CentralTickets\Preorder\PreorderRecover;

class PreorderDashboard implements Component
{
    public function compact()
    {
        ob_start();
        if ($this->verify_preorder()) {
            (new PreorderForm($this->get_preorder()))->display();
        } else {
            $this->not_preorder();
        }
        return ob_get_clean();
    }

    private function not_preorder()
    {
        ?>
        <form method="get" class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Buscar Preorden</h5>
                            <div class="input-group mb-3">
                                <input type="number" name="preorder" class="form-control" placeholder="Número de Preorden"
                                    required>
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }

    private function verify_preorder()
    {
        if (!isset($_GET['preorder'])) {
            return false;
        }
        $preorder = $this->get_preorder();
        if ($preorder === null) {
            return false;
        }
        return true;
    }

    private function get_preorder()
    {
        $preorder_id = intval($_GET['preorder']);
        return PreorderRecover::recover($preorder_id);
    }
}
