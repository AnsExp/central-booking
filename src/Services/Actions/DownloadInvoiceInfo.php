<?php
namespace CentralTickets\Services\Actions;

use CentralTickets\Operator;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Ticket;
use DateTime;
use WP_Post;

final class DownloadInvoiceInfo
{
    public function download_csv(
        Operator $operator,
        string $date_start,
        string $date_end,
        ?WP_Post $coupon = null,
        array $columns = []
    ) {
        $pagination = new InvoiceInfoPagination(
            $operator,
            $date_start,
            $date_end,
            $coupon
        );
        $filename = 'facturas_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, $this->get_columns_name($columns));

        foreach ($pagination->fetch_all_tickets() as $ticket) {
            $row = $this->format_ticket_row($ticket, $columns);
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    private function format_ticket_row(Ticket $ticket, array $columns): array
    {
        $data_columns = [
            'ticket_num' => $ticket->id,
            'name' => $ticket->get_order()->get_billing_first_name() . ' ' . $ticket->get_order()->get_billing_last_name(),
            'order_num' => $ticket->get_order()->get_id(),
            'purchase_date' => $ticket->get_order()->get_date_created()->format('Y-m-d H:i:s'),
            'coupon_code' => $ticket->get_coupon() ? $ticket->get_coupon()->post_title : '—',
            'ticket_status' => git_get_text_by_status($ticket->status),
            'total_amount' => git_currency_format($ticket->total_amount, true),
            'passengers' => implode(', ', array_map(fn($p) => $p->name, $ticket->get_passengers())),
        ];

        $data = [];
        foreach ($columns as $column_key) {
            $data[$column_key] = $data_columns[$column_key] ?? '';
        }

        return $data;
    }

    private function get_columns_name(array $columns): array
    {
        $all_columns = [
            'ticket_num' => 'Número de Ticket',
            'name' => 'Nombre del cliente',
            'order_num' => 'Número de Pedido',
            'purchase_date' => 'Fecha de Compra',
            'coupon_code' => 'Código de Cupon',
            'ticket_status' => 'Estado del ticket',
            'total_amount' => 'Total',
            'passengers' => 'Pasajeros',
        ];

        $selected_columns = [];
        foreach ($columns as $column_key) {
            if (isset($all_columns[$column_key])) {
                $selected_columns[] = $all_columns[$column_key];
            }
        }

        return $selected_columns;
    }
}