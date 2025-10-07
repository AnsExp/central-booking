<?php
namespace CentralTickets\Components;

use CentralTickets\Components\Constants\ListConstants;

class TabComponent extends BaseComponent
{
    /**
     * @var array{label: string, active: bool, content: Component}
     */
    private array $tabs = [];

    public function __construct()
    {
        parent::__construct('div');
        $this->id = 'table-' . rand();
        $this->class_list->add('git-tab-pane');
        wp_enqueue_style(
            'tab_pane_styles',
            CENTRAL_BOOKING_URL . 'assets/css/components/tab-panel.css',
        );
        wp_enqueue_script(
            'tab_pane_script',
            CENTRAL_BOOKING_URL . 'assets/js/components/tab-panel.js',
        );
    }

    public function add_tab(string $title, Component $content, bool $active = false)
    {
        $this->tabs[] = [
            'label' => $title,
            'active' => $active,
            'content' => $content
        ];
    }

    public function compact()
    {
        $items_contents = [];
        $items_heading = new ListComponent(ListConstants::UNORDER);
        $items_heading->class_list->add('tab-nav');
        foreach ($this->tabs as $tab) {
            $item_id = 'git-tab-' . rand();
            $content = new CompositeComponent('div');
            $attr = [];
            if ($tab['active']) {
                $attr['class'] = 'active';
                $content->class_list->add('active');
            }
            $content->id = $item_id;
            $attr['data-tab'] = "#{$item_id}";
            $items_heading->add_item($tab['label'], $attr);
            $content->class_list->add('tab-content');
            $content->add_child($tab['content']);
            $items_contents[] = $content;
        }

        $output = parent::compact();
        $output .= $items_heading->compact();
        foreach ($items_contents as $item_content) {
            $output .= $item_content->compact();
        }
        $output .= "</{$this->tag}>";

        return $output;
    }
}
