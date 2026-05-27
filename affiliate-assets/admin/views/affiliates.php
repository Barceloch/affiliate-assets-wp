<?php
/**
 * Admin affiliates view.
 *
 * @package AffiliateAssets\Admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Filters
$status_filter = isset($_GET['status']) ? sanitize_key($_GET['status']) : '';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Build query
$table = $wpdb->prefix . 'aa_affiliates';
$where = array('1=1');

if (!empty($status_filter)) {
    $where[] = $wpdb->prepare('status = %s', $status_filter);
}

if (!empty($search)) {
    $search_like = '%' . $wpdb->esc_like($search) . '%';
    $where[] = $wpdb->prepare('(referral_code LIKE %s OR payment_email LIKE %s)', $search_like, $search_like);
}

$where_clause = implode(' AND ', $where);

// Get total count
$total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_clause");
$total_pages = ceil($total / $per_page);

// Get affiliates
$affiliates = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table WHERE $where_clause ORDER BY registration_date DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));
?>

<div class="wrap aa-affiliates-page">
    <h1 class="wp-heading-inline"><?php _e('Afiliados', 'affiliate-assets'); ?></h1>
    
    <a href="<?php echo admin_url('user-new.php'); ?>" class="page-title-action"><?php _e('Añadir Nuevo', 'affiliate-assets'); ?></a>
    
    <hr class="wp-header-end">
    
    <!-- Filters -->
    <form method="get" action="" class="aa-filters">
        <input type="hidden" name="page" value="affiliate-assets-affiliates">
        
        <select name="status" class="postform">
            <option value=""><?php _e('Todos los estados', 'affiliate-assets'); ?></option>
            <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pendientes', 'affiliate-assets'); ?></option>
            <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('Activos', 'affiliate-assets'); ?></option>
            <option value="inactive" <?php selected($status_filter, 'inactive'); ?>><?php _e('Inactivos', 'affiliate-assets'); ?></option>
            <option value="rejected" <?php selected($status_filter, 'rejected'); ?>><?php _e('Rechazados', 'affiliate-assets'); ?></option>
        </select>
        
        <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Buscar...', 'affiliate-assets'); ?>" class="regular-text">
        
        <button type="submit" class="button"><?php _e('Filtrar', 'affiliate-assets'); ?></button>
        <a href="<?php echo admin_url('admin.php?page=affiliate-assets-affiliates'); ?>" class="button"><?php _e('Limpiar', 'affiliate-assets'); ?></a>
    </form>
    
    <!-- Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'affiliate-assets'); ?></th>
                <th><?php _e('Usuario', 'affiliate-assets'); ?></th>
                <th><?php _e('Código', 'affiliate-assets'); ?></th>
                <th><?php _e('Email Pago', 'affiliate-assets'); ?></th>
                <th><?php _e('Estado', 'affiliate-assets'); ?></th>
                <th><?php _e('Fecha Registro', 'affiliate-assets'); ?></th>
                <th><?php _e('Acciones', 'affiliate-assets'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($affiliates)): ?>
                <tr>
                    <td colspan="7"><?php _e('No se encontraron afiliados.', 'affiliate-assets'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($affiliates as $affiliate): ?>
                    <?php
                    $user = get_userdata($affiliate->user_id);
                    $status_labels = array(
                        'pending' => __('Pendiente', 'affiliate-assets'),
                        'active' => __('Activo', 'affiliate-assets'),
                        'inactive' => __('Inactivo', 'affiliate-assets'),
                        'rejected' => __('Rechazado', 'affiliate-assets'),
                    );
                    $status_class = 'status-' . $affiliate->status;
                    ?>
                    <tr>
                        <td><?php echo esc_html($affiliate->id); ?></td>
                        <td>
                            <?php if ($user): ?>
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <small><?php echo esc_html($user->user_email); ?></small>
                            <?php else: ?>
                                <em><?php _e('Usuario eliminado', 'affiliate-assets'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo esc_html($affiliate->referral_code); ?></code></td>
                        <td><?php echo esc_html($affiliate->payment_email); ?></td>
                        <td><span class="aa-status-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_labels[$affiliate->status]); ?></span></td>
                        <td><?php echo date_i18n(__('d/m/Y H:i', 'affiliate-assets'), strtotime($affiliate->registration_date)); ?></td>
                        <td>
                            <?php if ($affiliate->status === 'pending'): ?>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=affiliate-assets-affiliates&aa_action=approve_affiliate&affiliate_id=' . $affiliate->id), 'aa_approve_affiliate'); ?>" class="button button-small button-primary"><?php _e('Aprobar', 'affiliate-assets'); ?></a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=affiliate-assets-affiliates&aa_action=reject_affiliate&affiliate_id=' . $affiliate->id), 'aa_reject_affiliate'); ?>" class="button button-small button-secondary"><?php _e('Rechazar', 'affiliate-assets'); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=affiliate-assets-affiliates&aa_action=delete_affiliate&affiliate_id=' . $affiliate->id), 'aa_delete_affiliate'); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php _e('¿Estás seguro?', 'affiliate-assets'); ?>')"><?php _e('Eliminar', 'affiliate-assets'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php printf(_n('%s elemento', '%s elementos', $total, 'affiliate-assets'), number_format_i18n($total)); ?></span>
                <span class="pagination-links">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?php echo add_query_arg('paged', $i); ?>" class="<?php echo $i === $current_page ? 'current' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.aa-filters { margin: 20px 0; display: flex; gap: 10px; align-items: center; }
.aa-status-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
.status-pending { background: #f0b849; color: #fff; }
.status-active { background: #00a32a; color: #fff; }
.status-inactive { background: #646970; color: #fff; }
.status-rejected { background: #d63638; color: #fff; }
</style>
