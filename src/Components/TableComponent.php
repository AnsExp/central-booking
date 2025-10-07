<?php
namespace CentralTickets\Components;

use InvalidArgumentException;

class TableComponent extends BaseComponent
{
    private array $sections = [
        TableConstants::HEAD => [],
        TableConstants::BODY => [],
        TableConstants::FOOT => [],
    ];

    public function __construct()
    {
        parent::__construct('table');
        // $this->class_list->add('wp-list-table', 'widefat', 'fixed', 'striped');
        $this->class_list->add('table', 'table-striped', 'table-bordered', 'table-hover');
    }

    /**
     * @param array<Component|string> $data
     * @param string $section
     * @param array $attributes
     * @throws \InvalidArgumentException
     * @return TableComponent
     */
    public function add_row(
        array $data,
        string $section = TableConstants::BODY,
        array $attributes = []
    ): self {
        if (!isset($this->sections[$section])) {
            throw new InvalidArgumentException("Invalid section: $section");
        }

        $row = new CompositeComponent('tr');

        foreach ($attributes as $key => $value) {
            $row->set_attribute($key, $value);
        }

        foreach ($data as $info) {
            $tag = $section === 'head' ? 'th' : 'td';
            $row->add_child(new class ($tag, $info) implements Component {
                public function __construct(private $tag, private $info)
                {
                }
                public function compact()
                {
                    // return "<{$this->tag}>" . ($this->info instanceof Component ? $this->info->compact() : ($this->info)) . "</{$this->tag}>";
                    return "<{$this->tag}>" . ($this->info instanceof Component ? $this->info->compact() : htmlspecialchars($this->info)) . "</{$this->tag}>";
                }
            });
        }

        $this->sections[$section][] = $row;
        return $this;
    }

    public function compact()
    {
        $components = [
            'head' => 'thead',
            'body' => 'tbody',
            'foot' => 'tfoot',
        ];

        $content = '';

        foreach ($components as $section => $tag) {
            if (!empty($this->sections[$section])) {
                $wrapper = new CompositeComponent($tag);
                foreach ($this->sections[$section] as $row)
                    $wrapper->add_child($row);
                $content .= $wrapper->compact();
            }
        }

        $html = parent::compact();
        $html .= $content;
        $html .= "</{$this->tag}>";

        return $html;
    }

    /**
     * @return Component
     */
    public function convert_to_responsive()
    {
        $wrapper = new CompositeComponent('div');
        $wrapper->class_list->add('table-responsive');
        $wrapper->add_child($this);
        return $wrapper;
    }
}
