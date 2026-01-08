<?php
namespace CentralBooking\GUI;

class TableComponent extends BaseComponent
{
    /**
     * @param array<TableCellComponent> $headers
     * @param array<array<TableCellComponent>> $rows
     */
    public function __construct(
        private array $headers = [],
        private array $rows = []
    ) {
        parent::__construct('table');
        $this->class_list->add('wp-list-table', 'widefat', 'fixed', 'striped', 'table-view-list');
    }

    public function compact()
    {
        $html = parent::compact();
        // Render headers
        if (!empty($this->headers)) {
            $html .= '<thead><tr>';
            foreach ($this->headers as $header) {
                $html .= $header->compact();
            }
            $html .= '</tr></thead>';
        }
        // Render rows
        if (!empty($this->rows)) {
            $html .= '<tbody>';
            foreach ($this->rows as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= $cell->compact();
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
        $html .= "</{$this->tag}>";
        return $html;
    }
}
