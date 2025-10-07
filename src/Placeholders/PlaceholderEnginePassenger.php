<?php
namespace CentralTickets\Placeholders;

use CentralTickets\Components\StandaloneComponent;
use CentralTickets\Passenger;
use DateInterval;
use DateTime;
use Exception;

final class PlaceholderEnginePassenger extends PlaceholderEngine
{
    public function __construct(private readonly Passenger $passenger)
    {
        $this->add_placeholders();
    }

    private function add_placeholders()
    {
        $this->add_placeholder('name', fn(array $params) => $this->passenger->name);
        $this->add_description('name', [
            'title' => 'Nombre del Pasajero',
            'description' => 'Nombre completo del pasajero.',
            'parameters' => [],
        ]);

        $this->add_placeholder('nationality', fn(array $params) => $this->passenger->nationality);
        $this->add_description('nationality', [
            'title' => 'Nacionalidad del Pasajero',
            'description' => 'Nacionalidad del pasajero.',
            'parameters' => [],
        ]);

        $this->add_placeholder('type_document', fn(array $params) => $this->passenger->type_document);
        $this->add_description('type_document', [
            'title' => 'Tipo de Documento del Pasajero',
            'description' => 'Tipo de documento del pasajero.',
            'parameters' => [],
        ]);

        $this->add_placeholder('data_document', fn(array $params) => $this->passenger->data_document);
        $this->add_description('data_document', [
            'title' => 'Datos del Documento del Pasajero',
            'description' => 'Datos del documento del pasajero.',
            'parameters' => [],
        ]);

        $this->add_placeholder('served', function (array $params) {
            $yes_value = $params['yes'] ?? 'Sí';
            $no_value = $params['no'] ?? 'No';
            return $this->passenger->served ? $yes_value : $no_value;
        });
        $this->add_description('served', [
            'title' => '¿Ha sido atendido el pasajero?',
            'description' => 'Indica si el pasajero ha sido atendido o no.',
            'parameters' => [
                [
                    'param' => 'yes',
                    'values' => [
                        [
                            'value' => 'any-value',
                            'description' => 'En caso de haber sido transportado, muestra el valor que se indique (por defecto "Sí")'
                        ]
                    ]
                ],
                [
                    'param' => 'no',
                    'values' => [
                        [
                            'value' => 'any-value',
                            'description' => 'En caso de no haber sido transportado, muestra el valor que se indique (por defecto "No")'
                        ]
                    ]
                ]
            ],
        ]);

        $this->add_placeholder('approved', function (array $params) {
            $yes_value = $params['yes'] ?? 'Sí';
            $no_value = $params['no'] ?? 'No';
            return $this->passenger->approved ? $yes_value : $no_value;
        });
        $this->add_description('approved', [
            'title' => '¿Ha sido aprobado el pasajero?',
            'description' => 'Indica si el pasajero ha sido aprobado o no.',
            'parameters' => [
                [
                    'param' => 'yes',
                    'values' => [
                        [
                            'value' => 'any-value',
                            'description' => 'En caso de haber sido aprobado, muestra el valor que se indique (por defecto "Sí")'
                        ]
                    ]
                ],
                [
                    'param' => 'no',
                    'values' => [
                        [
                            'value' => 'any-value',
                            'description' => 'En caso de no haber sido aprobado, muestra el valor que se indique (por defecto "No")'
                        ]
                    ]
                ]
            ],
        ]);

        $this->add_placeholder('origin', fn(array $params) => match ($params['info'] ?? 'name') {
            'id' => $this->passenger->get_route()->get_origin()->id,
            'name' => $this->passenger->get_route()->get_origin()->name,
            'zone_name' => $this->passenger->get_route()->get_origin()->get_zone()->name,
            default => '',
        });
        $this->add_description('origin', [
            'title' => 'Información del punto de Origen',
            'description' => 'Indica información del punto de origen del pasajero.',
            'parameters' => [
                [
                    'param' => 'info',
                    'values' => [
                        [
                            'value' => 'id',
                            'description' => 'ID del punto de origen.'
                        ],
                        [
                            'value' => 'name',
                            'description' => 'Nombre del punto de origen (por defecto).'
                        ],
                        [
                            'value' => 'zone_name',
                            'description' => 'Nombre de la zona del punto de origen.'
                        ],

                    ]
                ]
            ],
        ]);

        $this->add_placeholder('destiny', fn(array $params) => match ($params['info'] ?? 'name') {
            'id' => $this->passenger->get_route()->get_destiny()->id,
            'name' => $this->passenger->get_route()->get_destiny()->name,
            'zone_name' => $this->passenger->get_route()->get_destiny()->get_zone()->name,
            default => '',
        });
        $this->add_description('destiny', [
            'title' => 'Información del punto de Destino',
            'description' => 'Indica información del punto de destino del pasajero.',
            'parameters' => [
                [
                    'param' => 'info',
                    'values' => [
                        [
                            'value' => 'id',
                            'description' => 'ID del punto de destino.'
                        ],
                        [
                            'value' => 'name',
                            'description' => 'Nombre del punto de destino (por defecto).'
                        ],
                        [
                            'value' => 'zone_name',
                            'description' => 'Nombre de la zona del punto de destino.'
                        ],

                    ]
                ]
            ],
        ]);

        $this->add_placeholder('qr_ticket', function (array $params) {
            $size = 350;
            if (str_contains($params['size'] ?? '350', 'px')) {
                $size = (int) str_replace('px', '', $params['size']);
            }
            $url = git_get_ticket_viewer_url($this->passenger->get_ticket()->id, $size);
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

        $this->add_placeholder('logo_sale', function (array $params) {
            $width = $params['width'] ?? null;
            $height = $params['height'] ?? null;
            $img = new StandaloneComponent('img');
            if ($width !== null) {
                if (str_contains($width, 'px')) {
                    $width = (int) str_replace('px', '', $width);
                }
                $img->set_attribute('width', $width);
            }
            if ($height !== null) {
                if (str_contains($height, 'px')) {
                    $height = (int) str_replace('px', '', $height);
                }
                $img->set_attribute('height', $height);
            }
            $img->set_attribute('src', $this->passenger->get_ticket()->get_brand_logo());
            $img->set_attribute('alt', 'Logo de la Venta');
            return $img->compact();
        });
        $this->add_description('logo_sale', [
            'title' => 'Logo de la Venta',
            'description' => 'Logo asociado a la venta',
            'parameters' => [
                [
                    'param' => 'width',
                    'values' => [
                        [
                            'value' => 'any-number',
                            'description' => 'Ancho del logo en píxeles (ej. 350px)'
                        ],
                    ],
                ],
                [
                    'param' => 'height',
                    'values' => [
                        [
                            'value' => 'any-number',
                            'description' => 'Alto del logo en píxeles (ej. 150px)'
                        ],
                    ],
                ],
            ],
        ]);

        $this->add_placeholder('transport', fn(array $params) => match ($params['info'] ?? 'name') {
            'id' => $this->passenger->get_transport()->id,
            'code' => $this->passenger->get_transport()->code,
            'type' => $this->passenger->get_transport()->type,
            'name' => $this->passenger->get_transport()->nicename,
            'capacity' => $this->passenger->get_transport()->get_meta('capacity') ?? 0,
            'operator_name' => $this->passenger->get_transport()->get_operator()->first_name . ' ' . $this->passenger->get_transport()->get_operator()->last_name,
            default => '',
        });
        $this->add_description('transport', [
            'title' => 'Nombre del Medio de Transporte',
            'description' => 'Indica información del medio de transporte del pasajero.',
            'parameters' => [
                [
                    'param' => 'info',
                    'values' => [
                        [
                            'value' => 'id',
                            'description' => 'ID del transporte.'
                        ],
                        [
                            'value' => 'name',
                            'description' => 'Nombre del transporte (por defecto).'
                        ],
                        [
                            'value' => 'capacity',
                            'description' => 'Capacidad del transporte.'
                        ],
                        [
                            'value' => 'code',
                            'description' => 'Código del transporte.'
                        ],
                        [
                            'value' => 'operator_name',
                            'description' => 'Nombre del operador del transporte.'
                        ],
                        [
                            'value' => 'type',
                            'description' => 'Tipo del transporte.'
                        ],
                    ]
                ]
            ],
        ]);

        $this->add_placeholder('date_trip', function (array $params) {
            $date_obj = new DateTime($this->passenger->date_trip);
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
        $this->add_description('date_trip', [
            'title' => 'Fecha del Viaje',
            'description' => 'Indica la fecha del viaje del pasajero.',
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
                ]
            ],
        ]);

        $this->add_placeholder('schedule_trip', function (array $params) {
            $route = $this->passenger->get_route();
            if (!$route) {
                return 'Horario no disponible';
            }

            $type = $params['type'] ?? 'departure';
            $format = $params['format'] ?? 'H:i';

            return match ($type) {
                'departure' => $route->departure_time
                ? date($format, strtotime($route->departure_time))
                : 'Hora de salida no disponible',

                'arrival' => $this->calculate_arrival_time($route, $format),

                'duration' => $route->duration_trip
                ? $this->format_duration($route->duration_trip, $params['duration_format'] ?? 'text')
                : 'Duración no disponible',

                'both' => sprintf(
                    '%s - %s',
                    $route->departure_time ? date($format, strtotime($route->departure_time)) : 'N/A',
                    $this->calculate_arrival_time($route, $format)
                ),

                default => $route->departure_time
                ? date($format, strtotime($route->departure_time))
                : 'Horario no disponible'
            };
        });

        $this->add_description('schedule_trip', [
            'title' => 'Horario del Viaje',
            'description' => 'Indica los horarios de salida, llegada o duración del viaje.',
            'parameters' => [
                [
                    'param' => 'type',
                    'values' => [
                        [
                            'value' => 'departure',
                            'description' => 'Hora de salida (por defecto)'
                        ],
                        [
                            'value' => 'arrival',
                            'description' => 'Hora de llegada'
                        ],
                        [
                            'value' => 'duration',
                            'description' => 'Duración del viaje'
                        ],
                        [
                            'value' => 'both',
                            'description' => 'Salida y llegada (ej. 08:00 - 12:00)'
                        ]
                    ]
                ],
                [
                    'param' => 'format',
                    'values' => [
                        [
                            'value' => 'H:i',
                            'description' => 'Formato 24h (ej. 14:30)'
                        ],
                        [
                            'value' => 'h:i A',
                            'description' => 'Formato 12h (ej. 2:30 PM)'
                        ],
                        [
                            'value' => 'H:i:s',
                            'description' => 'Con segundos (ej. 14:30:00)'
                        ]
                    ]
                ]
            ],
        ]);
    }
    /**
     * Calcula la hora de llegada sumando duration_trip a departure_time
     */
    private function calculate_arrival_time($route, string $format): string
    {
        if (!$route->departure_time || !$route->duration_trip) {
            return 'Hora de llegada no disponible';
        }

        try {
            $departure = new DateTime($route->departure_time);

            $duration_parts = explode(':', $route->duration_trip);
            if (count($duration_parts) !== 3) {
                return 'Duración inválida';
            }

            $hours = (int) $duration_parts[0];
            $minutes = (int) $duration_parts[1];
            $seconds = (int) $duration_parts[2];

            // ✅ Sumar la duración a la hora de salida
            $departure->add(new DateInterval("PT{$hours}H{$minutes}M{$seconds}S"));

            return $departure->format($format);

        } catch (Exception $e) {
            return 'Error calculando llegada';
        }
    }

    /**
     * Formatea la duración según el formato solicitado
     */
    private function format_duration(string $duration_trip, string $duration_format): string
    {
        $duration_parts = explode(':', $duration_trip);
        if (count($duration_parts) !== 3) {
            return $duration_trip; // Retorna tal como está si no puede parsear
        }

        $hours = (int) $duration_parts[0];
        $minutes = (int) $duration_parts[1];
        $seconds = (int) $duration_parts[2];

        return match ($duration_format) {
            'hours' => $hours > 0 ? "{$hours}h" : ($minutes > 0 ? "{$minutes}m" : "{$seconds}s"),
            'full' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
            'short' => $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m",
            'text' => $this->duration_to_text($hours, $minutes, $seconds),
            default => $duration_trip
        };
    }

    /**
     * Convierte duración a texto legible
     */
    private function duration_to_text(int $hours, int $minutes, int $seconds): string
    {
        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours === 1 ? '1 hora' : "{$hours} horas";
        }

        if ($minutes > 0) {
            $parts[] = $minutes === 1 ? '1 minuto' : "{$minutes} minutos";
        }

        if ($seconds > 0 && $hours === 0) { // Solo mostrar segundos si no hay horas
            $parts[] = $seconds === 1 ? '1 segundo' : "{$seconds} segundos";
        }

        return empty($parts) ? '0 minutos' : implode(' y ', $parts);
    }
}