<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;

final class ProfilePaneNotFound implements Component
{
    public function compact()
    {
        return '<div class="pane-not-found">Pestaña no encontrada</div>';
    }
}
