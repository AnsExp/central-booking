<?php
namespace CentralTickets;

class CartPassenger
{
    public string $name;
    public string $nationality;
    public string $type_document;
    public string $data_document;
    public string $birthday;

    public static function create(array $data)
    {
        $passenger = new self();
        $passenger->name = $data['name'];
        $passenger->nationality = $data['nationality'];
        $passenger->type_document = $data['type_document'];
        $passenger->data_document = $data['data_document'];
        $passenger->birthday = $data['birthday'];
        return $passenger;
    }
}