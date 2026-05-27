<?php
/**
 * Admin visits view.
 *
 * @package AffiliateAssets\Admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Pagination
$per_page = 50;
$current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Filters
$type_filter = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
$affiliate_filter = isset($_GET['affiliate_id']) ? absint($_GET['affiliate_id']) : 0;

// Build query
$table = $wpdb->prefix . 'aa_visits';
$where = array('1=1');

if (!empty($type_filter)) {
    $where[] = $wpdb->prepare('type = %s', $type_filter);
}

if ($affiliate_filter > 0) {
    $where[] = $wpdb->prepare('affiliate_id = %d', $affiliate_filter);
}

$where_clause = implode(' AND ', $where);

// Get total count
$total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_clause");
$total_pages = ceil($total / $per_page);

// Get visits
$visits = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table WHERE $where_clause ORDER BY date DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

// Get all affiliates for filter
$affiliates_table = $wpdb->prefix . 'aa_affiliates';
$all_affiliates = $wpdb->get_results("SELECT id, referral_code FROM $affiliates_table ORDER BY referral_code");
?>

<div class="wrap aa-visits-page">
    <h1><?php _e('Visitas de Referidos', 'affiliate-assets'); ?></h1>
    
    <!-- Filters -->
    <form method="get" action="" class="aa-filters">
        <input type="hidden" name="page" value="affiliate-assets-visits">
        
        <select name="type" class="postform">
            <option value=""><?php _e('Todos los tipos', 'affiliate-assets'); ?></option>
            <option value="direct" <?php selected($type_filter, 'direct'); ?>><?php _e('Directas', 'affiliate-assets'); ?></option>
            <option value="referral" <?php selected($type_filter, 'referral'); ?>><?php _e('Referidos', 'affiliate-assets'); ?></option>
            <option value="qr_scan" <?php selected($type_filter, 'qr_scan'); ?>><?php _e('Escaneo QR', 'affiliate-assets'); ?></option>
        </select>
        
        <select name="affiliate_id" class="postform">
            <option value=""><?php _e('Todos los afiliados', 'affiliate-assets'); ?></option>
            <?php foreach ($all_affiliates as $aff): ?>
                <option value="<?php echo esc_attr($aff->id); ?>" <?php selected($affiliate_filter, $aff->id); ?>>
                    <?php echo esc_html($aff->referral_code); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="button"><?php _e('Filtrar', 'affiliate-assets'); ?></button>
        <a href="<?php echo admin_url('admin.php?page=affiliate-assets-visits'); ?>" class="button"><?php _e('Limpiar', 'affiliate-assets'); ?></a>
    </form>
    
    <!-- Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'affiliate-assets'); ?></th>
                <th><?php _e('Afiliado', 'affiliate-assets'); ?></th>
                <th><?php _e('URL', 'affiliate-assets'); ?></th>
                <th><?php _e('IP', 'affiliate-assets'); ?></th>
                <th><?php _e('Tipo', 'affiliate-assets'); ?></th>
                <th><?php _e('Convertida', 'affiliate-assets'); ?></th>
                <th><?php _e('Fecha', 'affiliate-assets'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($visits)): ?>
                <tr>
                    <td colspan="7"><?php _e('No se encontraron visitas.', 'affiliate-assets'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($visits as $visit): ?>
                    <?php
                    $affiliate = new \AffiliateAssets\Includes\Class_Affiliate($visit->affiliate_id);
                    $type_labels = array(
                        'direct' => __('Directo', 'affiliate-assets'),
                        'referral' => __('Referido', 'affiliate-assets'),
                        'qr_scan' => __('QR Scan', 'affiliate-assets'),
                    );
                    ?>
                    <tr>
                        <td><?php echo esc_html($visit->id); ?></td>
                        <td>
                            <?php if ($affiliate->get_id()): ?>
                                <code><?php echo esc_html($affiliate->get('referral_code')); ?></code>
                            <?php else: ?>
                                <em><?php _e('Desconocido', 'affiliate-assets'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url($visit->url); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html(wp_trim_words($visit->url, 5)); ?>
                            </a>
                        </td>
                        <td><code><?php echo esc_html($visit->ip); ?></code></td>
                        <td><?php echo esc_html($type_labels[$visit->type] ?? $visit->type); ?></td>
                        <td>
                            <?php if ($visit->is_converted): ?>
                                <span class="dashicons dashicons-yes" style="color: #00a32a;"></span>
                                <?php _e('Sí', 'affiliate-assets'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-no" style="color: #d63638;"></span>
                                <?php _e('No', 'affiliate-assets'); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date_i18n(__('d/m/Y H:i', 'affiliate-assets'), strtotime($visit->date)); ?></td>
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
.aa-visits-page .aa-filters { margin: 20px 0; display: flex; gap: 10px; align-items: center; }
.aa-visits-page td code { font-size: 12px; }
</style>
