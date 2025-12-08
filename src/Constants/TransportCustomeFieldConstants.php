<?php
namespace CentralTickets\Constants;

final class TransportCustomeFieldConstants
{
    public const TEXT = 'text';
    public const ACTION = 'action';
    public const IA_PROMPT = 'ia_prompt';
    public const BRAND_BANNER = 'brand_banner';

    public static function get_all()
    {
        return [
            self::TEXT,
            self::ACTION,
            self::IA_PROMPT,
            self::BRAND_BANNER,
        ];
    }

    public static function display(string $field)
    {
        return [
            self::TEXT => 'Texto',
            self::ACTION => 'Acción',
            self::IA_PROMPT => 'Prompt de IA',
            self::BRAND_BANNER => 'Banner de transporte',
        ][$field] ?? $field;
    }

    public static function is_valid(string $value): bool
    {
        return in_array($value, self::get_all(), true);
    }
}