<?php
namespace CentralTickets\Admin\View;

use CentralTickets\Components\Displayer;
use CentralTickets\Components\PaginationComponent;
use CentralTickets\Constants\LogSourceConstants;
use CentralTickets\Services\LogService;

final class TableTicketsLog implements Displayer
{
    public function display()
    {
        $logs = LogService::get_logs_with_pagination(
            source: LogSourceConstants::TICKET,
            id_source: $_GET['id'] ?? 0,
            page: $_GET['page_number'] ?? 1,
            per_page: $_GET['page_size'] ?? 10
        );
        $pagination = new PaginationComponent();
        $pagination->set_data(
            $logs['pagination']['total_items'],
            $logs['pagination']['current_page'],
            $logs['pagination']['total_pages'],
        );
        $pagination->set_links(
            link_first: add_query_arg(['page_number' => 1, 'page_size' => $logs['pagination']['per_page']]),
            link_prev: add_query_arg(['page_number' => $logs['pagination']['current_page'] - 1, 'page_size' => $logs['pagination']['per_page']]),
            link_next: add_query_arg(['page_number' => $logs['pagination']['current_page'] + 1, 'page_size' => $logs['pagination']['per_page']]),
            link_last: add_query_arg(['page_number' => $logs['pagination']['total_pages'], 'page_size' => $logs['pagination']['per_page']])
        );
        ob_start();
        ?>
        <div style="overflow-x: auto; max-width: 900px; padding-top: 20px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 200px;">Marca de tiempo</th>
                        <th scope="col" style="width: 500px;">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['logs'] as $log): ?>
                        <tr>
                            <td>
                                <span>
                                    <?= git_datetime_format($log->time_stamp) ?>
                                </span>
                                <div class="row-actions visible">
                                    <span>
                                        ID: <?= esc_html($log->id) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php
                                echo $log->message;
                                if ($log->id_source) {
                                    echo '<div class="row-actions visible">';
                                    echo '<span class="dashicons dashicons-info"></span> ';
                                    echo '<a target="_blank" href="' . esc_url(admin_url('admin.php?page=git_tickets&id=' . $log->id_source)) . '">Ver registro</a>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php $pagination->display() ?>
        </div>
        <?php
        echo ob_get_clean();
    }
}