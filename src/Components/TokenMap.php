<?php
namespace CentralTickets\Components;

class TokenMap
{
    /**
     * @var array<string>
     */
    private array $map = [];

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->map[$key] = git_serialize($value);
    }

    public function remove(string $key)
    {
        unset($this->map[$key]);
    }

    public function keys_set()
    {
        return array_keys($this->map);
    }

    public function values_set()
    {
        return array_values($this->map);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key)
    {
        if (!$this->contains($key)) {
            return null;
        }
        return git_unserialize($this->map[$key]);
    }

    public function contains(string $key)
    {
        return array_key_exists($key, $this->map);
    }
}
