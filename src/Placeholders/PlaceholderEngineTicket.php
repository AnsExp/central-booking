<?php
namespace CentralTickets\Placeholders;

use CentralTickets\Components\StandaloneComponent;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Ticket;

final class PlaceholderEngineTicket extends PlaceholderEngine
{
    public function __construct(private readonly Ticket $ticket)
    {
        $this->add_placeholders();
    }

    private function add_placeholders()
    {
        $this->add_placeholder('name_buyer', fn(array $params) => $this->ticket->get_order()->get_billing_first_name() . ' ' . $this->ticket->get_order()->get_billing_last_name());
        $this->add_description('name_buyer', [
            'title' => 'Nombre del Comprador',
            'description' => 'Nombre completo del comprador',
            'parameters' => [],
        ]);
        $this->add_placeholder('phone_buyer', fn(array $params) => $this->ticket->get_order()->get_billing_phone());
        $this->add_description('phone_buyer', [
            'title' => 'Teléfono del Comprador',
            'description' => 'Número de teléfono del comprador',
            'parameters' => [],
        ]);
        $this->add_placeholder('order_number', fn(array $params) => $this->ticket->get_order()->get_id());
        $this->add_description('order_number', [
            'title' => 'Número de Orden',
            'description' => 'Identificador único de la orden',
            'parameters' => [],
        ]);
        $this->add_placeholder('date_buyer', function (array $params) {
            $date_obj = $this->ticket->get_order()->get_date_created();
            if (!$date_obj) {
                return 'Fecha no disponible';
            }
            $format = $params['format'] ?? 'iso';
            $include_time = isset($params['time']) && $params['time'] === 'true';
            $result = match ($format) {
                'long' => function_exists('git_date_format')
                ? git_date_format($date_obj->format('Y-m-d'), false)
                : $date_obj->format('j \d\e F \d\e Y'),
                'short' => function_exists('git_date_format')
                ? git_date_format($date_obj->format('Y-m-d'), true)
                : $date_obj->format('j M, Y'),
                'iso' => $date_obj->format('Y-m-d'),
                default => $date_obj->format('Y-m-d')
            };
            if ($include_time) {
                $time_format = $params['time_format'] ?? 'H:i';
                $result .= ' ' . $date_obj->format($time_format);
            }
            return $result;
        });
        $this->add_description('date_buyer', [
            'title' => 'Fecha del Comprador',
            'description' => 'Fecha de creación del pedido del comprador',
            'parameters' => [
                [
                    'param' => 'format',
                    'values' => [
                        [
                            'value' => 'long',
                            'description' => 'Formato largo (ej. 1 de enero de 2023)'
                        ],
                        [
                            'value' => 'short',
                            'description' => 'Formato corto (ej. 01 ene, 2023)'
                        ],
                        [
                            'value' => 'iso',
                            'description' => 'Formato ISO (ej. 2023-01-01)'
                        ]
                    ]
                ],
                [
                    'param' => 'time',
                    'values' => [
                        [
                            'value' => 'true',
                            'description' => 'Incluir hora en el resultado'
                        ],
                        [
                            'value' => 'false',
                            'description' => 'Solo mostrar fecha (por defecto)'
                        ]
                    ]
                ]
            ],
        ]);
        $this->add_placeholder('passengers_count', function (array $params) {
            return count($this->ticket->get_passengers());
        });
        $this->add_description('passengers_count', [
            'title' => 'Cantidad de Pasajeros',
            'description' => 'Número de pasajeros en el pedido',
            'parameters' => [],
        ]);
        $this->add_placeholder('status_ticket', function (array $params) {
            return git_get_text_by_status($this->ticket->status);
        });
        $this->add_description('status_ticket', [
            'title' => 'Estado del Ticket',
            'description' => 'Estado actual del ticket',
            'parameters' => [],
        ]);
        $this->add_placeholder('brand_media', function (array $params) {
            $width = $params['width'] ?? null;
            $height = $params['height'] ?? null;
            $img = new StandaloneComponent('img');
            if ($width !== null) {
                if (str_contains($width, 'px')) {
                    $width = (int) str_replace('px', '', $width);
                }
                $img->set_attribute('width', $width . 'px');
            }
            if ($height !== null) {
                if (str_contains($height, 'px')) {
                    $height = (int) str_replace('px', '', $height);
                }
                $img->set_attribute('height', $height . 'px');
            }
            $img->set_attribute('alt', 'Logo de la Venta');
            $brand_media_url = git_get_map_setting('ticket_viewer.default_media') ?? '';
            if ($this->ticket->status === TicketConstants::PAYMENT) {
                if ($this->ticket->get_coupon() === null) {
                    $brand_media_url = $this->ticket->get_passengers()[0]->get_transport()->get_operator()->brand_media ?: $brand_media_url;
                } else {
                    $brand_media_url = get_post_meta($this->ticket->get_coupon()->ID,'brand_media', true) ?: $brand_media_url;
                }
            }
            $img->set_attribute('src', $brand_media_url);
            return $img->compact();
        });
        $this->add_description('brand_media', [
            'title' => 'Banner asociado a la venta',
            'description' => 'Muestra el banner asociado a la venta. Primero, busca si el operador tiene un banner de venta. Si no lo tiene, busca el banner de venta del cupón de la venta. En caso de no encontrar un banner de cupón, no mostrará nada.',
            'parameters' => [
                [
                    'param' => 'width',
                    'values' => [
                        [
                            'value' => 'any-number',
                            'description' => 'Ancho del banner en píxeles (ej. 350px)'
                        ]
                    ]
                ],
                [
                    'param' => 'height',
                    'values' => [
                        [
                            'value' => 'any-number',
                            'description' => 'Altura del banner en píxeles (ej. 350px)'
                        ]
                    ]
                ],
            ],
        ]);
        $this->add_placeholder('qr_ticket', function (array $params) {
            $size = 350;
            if (str_contains($params['size'] ?? '350', 'px')) {
                $size = (int) str_replace('px', '', $params['size']);
            }
            $url = git_get_ticket_viewer_url($this->ticket->id, $size);
            if ($url === null) {
                return 'QR no disponible';
            }
            $img = new StandaloneComponent('img');
            $img->set_attribute('src', $url);
            $img->set_attribute('alt', 'Código QR del Ticket');
            return $img->compact();
        });
        $this->add_description('qr_ticket', [
            'title' => 'Código QR del Ticket',
            'description' => 'Código QR asociado al ticket',
            'parameters' => [
                [
                    'param' => 'size',
                    'values' => [
                        [
                            'value' => 'any-number',
                            'description' => 'Tamaño del código QR en píxeles (ej. 350px)'
                        ]
                    ]
                ]
            ],
        ]);
        $this->add_placeholder('total_amount', function (array $params) {
            $format = $params['format'] ?? 'currency';
            if ($format === 'number') {
                return number_format((float) $this->ticket->total_amount, 2, '.', '');
            }
            return git_currency_format($this->ticket->total_amount, true);
        });
        $this->add_description('total_amount', [
            'title' => 'Monto Total del Ticket',
            'description' => 'Monto total del ticket',
            'parameters' => [
                [
                    'param' => 'format',
                    'values' => [
                        [
                            'value' => 'number',
                            'description' => 'Solo el número (ej. 1000.50)'
                        ],
                        [
                            'value' => 'currency',
                            'description' => 'Número con formato de moneda (ej. $1,000.50)'
                        ]
                    ]
                ]
            ],
        ]);
        $this->add_placeholder('coupon_code', function (array $params) {
            $coupon = $this->ticket->get_coupon();
            if ($coupon === null) {
                return '';
            }
            return $coupon->post_title;
        });
        $this->add_description('coupon_code', [
            'title' => 'Código de Cupón',
            'description' => 'Código de cupón aplicado al pedido',
            'parameters' => [],
        ]);
    }
}