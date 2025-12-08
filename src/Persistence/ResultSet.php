<?php
namespace CentralTickets\Persistence;

final class ResultSet
{
    public array $items = [];
    public int $per_page = 10;
    public int $total_pages = 0;
    public int $current_page = 0;
    public int $total_items = 10;
    public bool $has_items = false;
    public bool $has_next_page = false;
    public bool $has_previous_page = false;
}
