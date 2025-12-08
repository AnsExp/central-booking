<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;

class ProfilePanePreorder implements Component
{
    public function compact()
    {
        ob_start();
        return ob_get_clean();
    }
}
