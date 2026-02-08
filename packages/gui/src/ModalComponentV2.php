<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\Constants\ButtonActionConstants;

final class ModalComponentV2 extends BaseComponent
{
    private DisplayerInterface|ComponentInterface|null $body = null;
    private DisplayerInterface|ComponentInterface|null $footer = null;

    public function __construct(private readonly string $title = '')
    {
        parent::__construct('div');
        $this->styles->set('display', 'none');
        $this->class_list->add('git-modal');
        $this->id = 'modal-' . rand();
    }

    private function initScripts()
    {
        wp_enqueue_script(
            'central-booking-modal',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/js/modal-component.js',
            [],
            time(),
            false,
        );
        wp_enqueue_style(
            'central-booking-modal',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/css/modal-component.css',
            [],
            time(),
            false,
        );
    }

    public function compact()
    {
        $this->initScripts();
        $html = parent::compact();
        ob_start();
        ?>
        <div class="git-modal-dialog">
            <div class="git-modal-content">
                <div class="git-modal-header">
                    <h4 class="git-modal-title">
                        <?= $this->title ?>
                    </h4>
                </div>
                <div class="git-modal-body">
                    <?php
                    if ($this->body !== null && $this->body instanceof DisplayerInterface) {
                        $this->body->render();
                    } elseif ($this->body !== null && $this->body instanceof ComponentInterface) {
                        echo $this->body->compact();
                    }
                    ?>
                </div>
                <div class="git-modal-footer">
                    <?php
                    if ($this->footer !== null && $this->footer instanceof DisplayerInterface) {
                        $this->footer->render();
                    } elseif ($this->footer !== null && $this->footer instanceof ComponentInterface) {
                        echo $this->footer->compact();
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $html .= ob_get_contents();
        $html .= '</div>';
        ob_end_clean();
        return $html;
    }

    public function setBodyComponent(DisplayerInterface|ComponentInterface $body)
    {
        $this->body = $body;
    }

    public function setFooterComponent(DisplayerInterface|ComponentInterface $footer)
    {
        $this->footer = $footer;
    }

    public function createButtonLaunch(string|ComponentInterface $text = 'Launch')
    {
        $button_launch = new ButtonComponent($text, ButtonActionConstants::BUTTON, 'git-btn git-btn-primary');
        $button_launch->attributes->set('data-target', '#' . $this->id);
        $button_launch->class_list->add('git-modal-launch');
        return $button_launch;
    }

    public function createButtonDismiss(string $text = 'Close')
    {
        $button_dismiss = new ButtonComponent($text, ButtonActionConstants::BUTTON, 'git-btn git-btn-secondary');
        $button_dismiss->attributes->set('data-target', '#' . $this->id);
        $button_dismiss->class_list->add('git-modal-dismiss');
        return $button_dismiss;
    }
}
