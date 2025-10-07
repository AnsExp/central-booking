<?php
namespace CentralTickets\Components;

class SelectComponent extends FormControlComponent
{
    /**
     * @var array<TextComponent>
     */
    protected array $options = [];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->tag = 'select';
        $this->id = "select-$name-" . rand();
        $this->class_list->remove('form-control');
        $this->class_list->add('form-select');
    }

    public function add_option(string $key, mixed $value = null, bool $selected = false, array $attributes = [])
    {
        $option = new TextComponent('option', $key);
        if ($value !== null) {
            $option->set_attribute('value', git_serialize($value));
        }
        if ($selected) {
            $option->set_attribute('selected', '');
        }
        if (isset($attributes['id'])) {
            $option->id = $attributes['id'];
            unset($attributes['id']);
        }
        if (isset($attributes['class'])) {
            if (is_array($attributes['class'])) {
                foreach ($attributes['class'] as $class) {
                    $option->class_list->add($class);
                }
            } else {
                $option->class_list->add($attributes['class']);
            }
            unset($attributes['class']);
        }
        foreach ($attributes as $name => $content) {
            $option->set_attribute($name, $content);
        }
        $this->options[] = $option;
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->options as $option) {
            $html .= $option->compact();
        }
        $html .= "</{$this->tag}>";

        return $html;
    }

    public function set_value(mixed $value)
    {
        foreach ($this->options as $option) {
            if (
                $option->get_attribute('value') !== null &&
                $option->get_attribute('value') == $value
            ) {
                $option->set_attribute('selected', '');
                break;
            }
        }
    }
}
