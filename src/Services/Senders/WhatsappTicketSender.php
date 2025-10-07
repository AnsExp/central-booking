<?php
namespace CentralTickets\Services\Senders;

use CentralTickets\Constants\LogLevelConstants;
use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Services\LogService;
use CentralTickets\Ticket;
use Exception;

class WhatsAppTicketSender implements TicketSender
{
    public function send(Ticket $ticket)
    {
        try {
            $phone_number = $ticket->get_order()->get_billing_phone();
            $url = 'https://graph.facebook.com/v22.0/742399228946134/messages';
            $token = 'EAAR1hDZBR91oBOxnTCsOfJqx7e9dDCb6KG53RqZBmDJPrI1LV1XGTSLTkA9t2850X0lqv5kZBZAHOQKfgA9J6zZC8yCnPn2mWRTQUROt73bPaMZA919E6zezkGhUmlukqH9G1bjkChilc5BIC4FFwJ70IpD0oJ6Y6hLihA3DmueHMt0NwnMOnDtd1bJffgKIiHSuHaZBJ1FS0ILq0yMyZAmdczGHPpM5riPdjm0mFtpynaZAjmCGMVZCygAOiZCTIZC37wZDZD';

            $messageQR = json_encode([
                'messaging_product' => 'whatsapp',
                'to' => $phone_number,
                'type' => 'template',
                'template' => [
                    'name' => 'checkout_ticket',
                    'language' => [
                        'code' => 'es_EC'
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => git_create_code_qr($ticket->id),
                                ]
                            ],
                        ]
                    ]
                ]
            ]);

            $headers = [
                "Authorization: Bearer $token",
                "Content-Type: application/json",
            ];

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $messageQR);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpStatus >= 200 && $httpStatus < 300) {
                LogService::create_git_log(
                    LogSourceConstants::TICKET,
                    $ticket->id,
                    "Se ha emitido un mensaje de WhatsApp con el código QR al número {$phone_number}.",
                    LogLevelConstants::INFO
                );
            } else {
                LogService::create_git_log(
                    LogSourceConstants::SYSTEM,
                    null,
                    "Error a la hora enviar el codigo QR del ticket {$ticket->id} al telefono {$phone_number}.",
                    LogLevelConstants::ERROR
                );
            }
        } catch (Exception $th) {
            LogService::create_git_log(
                LogSourceConstants::SYSTEM,
                null,
                "Error al procesar la solicitud de envío del ticket {$ticket->id} al telefono {$phone_number}.",
                LogLevelConstants::ERROR
            );
        }
    }
}