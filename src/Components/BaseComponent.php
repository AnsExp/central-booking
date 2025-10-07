<?php
namespace CentralTickets\Components;

abstract class BaseComponent implements Component, Displayer
{
    public string $id = '';
    public TokenMap $styles;
    public TokenList $class_list;
    private array $attributes = [];
    protected string $tag = '';

    /**
     * BaseComponent constructor.
     *
     * @param string $tag The HTML tag for the component.
     */
    public function __construct(string $tag)
    {
        $this->tag = $tag;
        $this->styles = new TokenMap;
        $this->class_list = new TokenList;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set_attribute(string $key, mixed $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get_attribute(string $key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public function get_attributes()
    {
        return array_map('git_unserialize', $this->attributes);
    }

    public function remove_attribute(string $key)
    {
        unset($this->attributes[$key]);
    }

    public function compact()
    {
        $meta = '';

        if ($this->id !== '') {
            $meta .= " id=\"" . htmlspecialchars($this->id) . "\"";
        }

        $classList = implode(' ', $this->class_list->values());
        if ($classList !== '') {
            $meta .= " class=\"" . htmlspecialchars($classList) . "\"";
        }

        $styleList = implode('; ', array_map(
            fn($key) => "$key: " . htmlspecialchars($this->styles->get($key)),
            $this->styles->keys_set()
        ));

        if ($styleList !== '') {
            $meta .= " style=\"" . htmlspecialchars($styleList) . "\"";
        }

        foreach ($this->attributes as $key => $value) {
            $meta .= " $key=\"" . htmlspecialchars(git_serialize($value)) . "\"";
        }

        $output = "<{$this->tag}$meta>";

        return $output;
    }

    public function display()
    {
        echo $this->compact();
    }
}