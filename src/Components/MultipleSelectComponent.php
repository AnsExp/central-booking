<?php
namespace CentralTickets\Components;

class MultipleSelectComponent extends SelectComponent
{
    private array $values = [];

    public function __construct(string $name = '')
    {
        parent::__construct($name);
        $this->class_list->add('git-multiselect');
        wp_enqueue_style(
            'git-multiselect_style',
            CENTRAL_BOOKING_URL . 'assets/css/components/multiselect-component.css'
        );
        wp_enqueue_script_module(
            'git-multiselect_script',
            CENTRAL_BOOKING_URL . 'assets/js/components/multiselect-component.js'
        );
    }

    public function compact()
    {
        $this->set_attribute('data-selected', git_serialize($this->values));
        return parent::compact();
    }

    public function set_value(mixed $value)
    {
        $this->values[] = git_serialize($value);

        // Marcar la opción como oculta y seleccionada
        foreach ($this->options as $option) {
            if ($option->get_attribute('value') == $value) {
                $option->set_attribute('style', 'display: none;');
                break;
            }
        }
    }

    /**
     * @return BaseComponent
     */
    public function get_options_container()
    {
        $options_container = new CompositeComponent('div');
        $options_container->id = "{$this->id}-container";
        $options_container->class_list->add('git-multiselect-container');

        foreach ($this->values as $value) {
            // Buscar el texto de la opción seleccionada
            $option_text = '';
            foreach ($this->options as $option) {
                if ($option->get_attribute('value') == $value) {
                    $option_text = $option->get_text();
                    break;
                }
            }

            $option_selected = new TextComponent('span', $option_text);
            $option_selected->class_list->add('git-option-item-selected');
            $option_selected->set_attribute('data-value', $value);

            $remove_btn = new TextComponent('i');
            $remove_btn->class_list->add(
                'bi',
                'bi-x',
                'git-remove-option'
            );
            $option_selected->append($remove_btn);

            $options_container->add_child($option_selected);
        }

        return $options_container;
    }
}
