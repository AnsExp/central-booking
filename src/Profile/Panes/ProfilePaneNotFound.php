<?php
namespace CentralTickets\Profile\Panes;

use CentralBooking\GUI\ComponentInterface;

final class ProfilePaneNotFound implements ComponentInterface
{
    public function compact()
    {
        return '<div class="pane-not-found">Pestaña no encontrada</div>';
    }
}
