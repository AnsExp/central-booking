<?php
namespace CentralBooking\Data\Constants;

enum TransportCustomeFieldConstants: string
{
    case TEXT = 'text';
    case ACTION = 'action';
    case IA_PROMPT = 'ia_prompt';
    case BRAND_BANNER = 'brand_banner';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Texto',
            self::ACTION => 'Acción',
            self::IA_PROMPT => 'Prompt de IA',
            self::BRAND_BANNER => 'Banner de transporte',
            default => $this->value,
        };
    }
}