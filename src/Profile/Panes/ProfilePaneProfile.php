<?php
namespace CentralTickets\Profile\Panes;

use CentralTickets\Components\Component;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Constants\UserConstants;
use WP_User;

final class ProfilePaneProfile implements Component
{
    private WP_User $current_user;

    public function __construct()
    {
        $this->current_user = wp_get_current_user();
    }

    public function compact(): string
    {
        ob_start();
        ?>
        <div class="container-fluid py-4">
            <!-- Header del Usuario -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="me-4 position-relative">
                                            <?= get_avatar($this->current_user->ID, 80, '', '', [
                                                'class' => 'rounded-circle shadow-sm'
                                            ]) ?>
                                            <span
                                                class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1 border border-2 border-white">
                                                <span style="width: 8px; height: 8px;"
                                                    class="d-block bg-success rounded-circle"></span>
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="mb-2 text-dark fw-bold">
                                                <?= esc_html($this->get_full_name()) ?>
                                            </h3>
                                            <div class="mb-2">
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 me-2 fw-normal">
                                                    <i class="bi bi-person-badge me-1"></i>
                                                    <?= esc_html($this->get_role_label()) ?>
                                                </span>
                                                <span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 fw-normal">
                                                    <i class="bi bi-calendar-check me-1"></i>
                                                    Miembro desde <?= esc_html($this->get_member_since()) ?>
                                                </span>
                                            </div>
                                            <div class="text-muted">
                                                <i class="bi bi-envelope me-2"></i>
                                                <?= esc_html($this->current_user->user_email) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="<?= wp_logout_url(home_url()) ?>" class="btn btn-outline-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Cerrar Sesión
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del Usuario -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?= $this->get_payment_tickets_count() ?></h4>
                            <small class="text-muted">Pagos Completados</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-exclamation-circle text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?= $this->get_partial_tickets_count() ?></h4>
                            <small class="text-muted">Pagos Parciales</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-dash-circle text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?= $this->get_cancel_tickets_count() ?></h4>
                            <small class="text-muted">Pagos Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plugin Cliente Externo -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 text-dark fw-bold">
                            <i class="bi bi-plugin me-2 text-success"></i>
                            GIT Externo
                        </h5>
                    </div>
                    <div class="card-body">
                        <?= $this->render_external_client_info() ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_external_client_info(): string
    {
        ob_start();
        ?>
        <div class="text-center">
            <div class="mb-3">
                <i class="bi bi-wordpress text-primary" style="font-size: 3rem;"></i>
            </div>
            <h6 class="text-dark fw-bold mb-3">Plugin para WordPress</h6>
            <p class="text-muted small mb-4">
                ¿Ya tienes tu propio sitio web en WordPress? Descarga nuestro plugin para integrar
                Central Tickets directamente en tu sitio y acceder a toda tu información desde nuestros servidores.
            </p>

            <div class="d-grid gap-2">
                <a href="<?= CENTRAL_BOOKING_URL . 'assets/data/git-client.zip' ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-download me-2"></i>
                    Descargar Plugin
                </a>
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-book me-2"></i>
                    Ver Documentación
                </a>
            </div>

            <div class="row">
                <div class="text-start col">
                    <div class="mt-4 p-3 bg-light bg-opacity-50 rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Características:</strong>
                            <ul>
                                <li>Sincronización automática</li>
                                <li>Acceso a tickets</li>
                                <li>Gestión de reservas</li>
                                <li>Integración completa</li>
                            </ul>
                        </small>
                    </div>
                </div>
                <div class="text-start col">
                    <div class="mt-4 p-3 bg-light bg-opacity-50 rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Requisitos:</strong>
                            <ul>
                                <li>PHP >= 8.1</li>
                                <li>WordPress >= 6.8</li>
                                <li>WooCommerce >= 10.0</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>

            <div class="p-3 text-warning-emphasis bg-warning-subtle border border-warning-subtle rounded-3">
                <small>
                    Si necesitas ayuda en la instalación o expandir el plugin de forma segura, ponte en contacto con nosotros.
                </small>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_full_name(): string
    {
        $first = $this->current_user->first_name;
        $last = $this->current_user->last_name;

        if (!empty($first) || !empty($last)) {
            return trim($first . ' ' . $last);
        }

        return $this->current_user->display_name ?: $this->current_user->user_login;
    }

    private function get_role_label(): string
    {
        $roles = $this->current_user->roles;

        if (empty($roles)) {
            return 'Usuario';
        }

        $role_labels = [
            'administrator' => 'Administrador',
            'operator' => 'Operador',
            'customer' => 'Cliente',
            'subscriber' => 'Suscriptor'
        ];

        return $role_labels[$roles[0]] ?? ucfirst($roles[0]);
    }

    private function get_member_since(): string
    {
        return date('F Y', strtotime($this->current_user->user_registered));
    }

    private function get_partial_tickets_count()
    {
        $coupons = [];
        $tickets_count = 0;
        if (git_current_user_has_role(UserConstants::OPERATOR)) {
            $operator = git_get_operator_by_id(get_current_user_id());
            $coupons = $operator ? $operator->get_partial_tickets() : [];
        } elseif (git_current_user_has_role(UserConstants::ADMINISTRATOR)) {
            $coupons = git_get_query_persistence()->get_coupon_repository()->find_all();
        }

        foreach ($coupons as $coupon) {
            $tickets_count += git_get_query_persistence()
                ->get_ticket_repository()
                ->count([
                    'id_coupon' => $coupon->ID,
                    'status' => TicketConstants::PARTIAL
                ]);
        }

        return $tickets_count;
    }

    private function get_payment_tickets_count()
    {
        $coupons = [];
        $tickets_count = 0;
        if (git_current_user_has_role(UserConstants::OPERATOR)) {
            $operator = git_get_operator_by_id(get_current_user_id());
            $coupons = $operator ? $operator->get_partial_tickets() : [];
        } elseif (git_current_user_has_role(UserConstants::ADMINISTRATOR)) {
            $coupons = git_get_query_persistence()->get_coupon_repository()->find_all();
        }

        foreach ($coupons as $coupon) {
            $tickets_count += git_get_query_persistence()
                ->get_ticket_repository()
                ->count([
                    'id_coupon' => $coupon->ID,
                    'status' => TicketConstants::PAYMENT
                ]);
        }

        return $tickets_count;
    }

    private function get_cancel_tickets_count()
    {
        $coupons = [];
        $tickets_count = 0;
        if (git_current_user_has_role(UserConstants::OPERATOR)) {
            $operator = git_get_operator_by_id(get_current_user_id());
            $coupons = $operator ? $operator->get_partial_tickets() : [];
        } elseif (git_current_user_has_role(UserConstants::ADMINISTRATOR)) {
            $coupons = git_get_query_persistence()->get_coupon_repository()->find_all();
        }

        foreach ($coupons as $coupon) {
            $tickets_count += git_get_query_persistence()
                ->get_ticket_repository()
                ->count([
                    'id_coupon' => $coupon->ID,
                    'status' => TicketConstants::PENDING
                ]);
        }

        return $tickets_count;
    }
}