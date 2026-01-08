<?php
namespace CentralTickets\Profile\Panes;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\Profile\Forms\FormPreorder;

class ProfilePanePreorder implements ComponentInterface
{
    public function compact()
    {
        $form = new FormPreorder;
        return $form->compact();
    }
}
