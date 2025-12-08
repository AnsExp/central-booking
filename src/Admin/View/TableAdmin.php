<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\PaginationComponent;
use CentralTickets\Persistence\ResultSet;

abstract class TableAdmin implements Displayer
{
    protected function __construct(private bool $add_pagination_controls = true)
    {
    }

    public function display()
    {
        if (empty($this->get_result_set()->has_items)) {
            $this->no_content();
        } else {
            $this->table();
            if ($this->add_pagination_controls) {
                $this->display_pagination_controls();
            }
        }
    }

    private function display_pagination_controls()
    {
        $pagination = new PaginationComponent();
        $pagination->set_data(
            $this->get_result_set()->total_items,
            $this->get_result_set()->current_page,
            $this->get_result_set()->total_pages,
        );
        $pagination->set_links(
            $this->get_pagination_links()['first'],
            $this->get_pagination_links()['last'],
            $this->get_pagination_links()['prev'],
            $this->get_pagination_links()['next'],
        );
    }

    /**
     * @return array{first: string, prev: string, next: string, last: string}
     */
    abstract protected function get_pagination_links();
    /**
     * @return ResultSet
     */
    abstract protected function get_result_set();
    /**
     * @return void
     */
    abstract protected function no_content();
    /**
     * @return void
     */
    abstract protected function table();
}
