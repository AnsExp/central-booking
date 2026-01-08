<?php
namespace CentralBooking\GUI;

class TableCellComponent extends StandaloneComponent
{
    private array $actions;

    public function __construct(private ComponentInterface $content)
    {
        parent::__construct('td');
        $this->actions = [];
    }

    public function addActions(string $action, ?string $url = null, bool $openNewTab = false)
    {
        $this->actions[$action] = [
            'url' => $url ?? '',
            'openNewTab' => $openNewTab
        ];
    }

    private function getPanelActions()
    {
        $isFirst = true;
        $container = new CompositeComponent('div');
        $container->class_list->add('row-actions', 'visible');
        foreach ($this->actions as $action => $details) {
            $labelHtml = $details['url'] === '' ? 'span' : 'a';
            $actionComponent = new TextComponent($labelHtml);
            $actionComponent->append($this->content->compact());
            if (!$isFirst) {
                $container->addChild($this->getSeparator());
            }
            if ($labelHtml === 'a') {
                $actionComponent->attributes->set('href', $details['url']);
                if ($details['openNewTab']) {
                    $actionComponent->attributes->set('target', '_blank');
                }
            }
            $container->addChild($actionComponent);
            $actionComponent->class_list->add('action');
            $isFirst = false;
        }
        return $container;
    }

    private function getSeparator()
    {
        $separator = new TextComponent('span');
        $separator->append(' | ');
        return $separator;
    }

    public function compact()
    {
        $html = parent::compact();
        $html .= $this->content->compact();
        $html .= $this->getPanelActions()->compact();
        $html .= "</{$this->tag}>";
        return $html;
    }
}
