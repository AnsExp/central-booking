<?php
namespace CentralTickets\Admin\Setting;

use CentralTickets\Components\Displayer;

final class SettingsInteractivity implements Displayer
{
    public function display(): void
    {
        ?>
        <p>
            Lorem, ipsum dolor sit amet consectetur adipisicing elit. Mollitia odio accusamus delectus aut, nesciunt provident,
            voluptate officia veritatis eius, minima consectetur tempora sunt dolorem! Earum consequuntur nisi minima enim hic?
        </p>
        <?php
    }
}
