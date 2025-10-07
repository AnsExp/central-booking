<?php
namespace CentralTickets\Components\Implementation;

use CentralTickets\Components\MultipleSelectComponent;
use CentralTickets\Components\SelectComponent;

class PageSelect
{
    private array $pages;
    public function __construct(private string $name = 'page', ?int $operator = null)
    {
        $this->pages = $this->get_pages();
    }

    public function create(bool $multiple = false)
    {

        $selectComponent = $multiple
            ? new MultipleSelectComponent($this->name)
            : new SelectComponent($this->name);

        $selectComponent->add_option('Seleccione...', '');

        foreach ($this->pages as $page) {
            $selectComponent->add_option(
                $page->post_title,
                $page->ID
            );
        }

        return $selectComponent;
    }

    private function get_pages()
    {
        if (isset($this->pages)) {
            return $this->pages;
        }
        return get_pages([
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ]);
    }
}