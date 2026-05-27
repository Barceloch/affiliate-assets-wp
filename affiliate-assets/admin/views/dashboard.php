<?php
/**
 * Admin dashboard view.
 *
 * @package AffiliateAssets\Admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get stats
$affiliates_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aa_affiliates");
$active_affiliates = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aa_affiliates WHERE status = 'active'");
$pending_affiliates = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aa_affiliates WHERE status = 'pending'");
$total_visits = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aa_visits");
$converted_visits = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aa_visits WHERE is_converted = 1");
?>

<div class="wrap aa-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Configuración guardada correctamente.', 'affiliate-assets'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="aa-stats-grid">
        <div class="aa-stat-card">
            <div class="aa-stat-icon dashicons dashicons-groups"></div>
            <div class="aa-stat-content">
                <span class="aa-stat-value"><?php echo esc_html($affiliates_count); ?></span>
                <span class="aa-stat-label"><?php _e('Total Afiliados', 'affiliate-assets'); ?></span>
            </div>
        </div>
        
        <div class="aa-stat-card">
            <div class="aa-stat-icon dashicons dashicons-yes-alt"></div>
            <div class="aa-stat-content">
                <span class="aa-stat-value"><?php echo esc_html($active_affiliates); ?></span>
                <span class="aa-stat-label"><?php _e('Afiliados Activos', 'affiliate-assets'); ?></span>
            </div>
        </div>
        
        <div class="aa-stat-card">
            <div class="aa-stat-icon dashicons dashicons-clock"></div>
            <div class="aa-stat-content">
                <span class="aa-stat-value"><?php echo esc_html($pending_affiliates); ?></span>
                <span class="aa-stat-label"><?php _e('Pendientes Aprobación', 'affiliate-assets'); ?></span>
            </div>
        </div>
        
        <div class="aa-stat-card">
            <div class="aa-stat-icon dashicons dashicons-visibility"></div>
            <div class="aa-stat-content">
                <span class="aa-stat-value"><?php echo esc_html($total_visits); ?></span>
                <span class="aa-stat-label"><?php _e('Visitas Totales', 'affiliate-assets'); ?></span>
            </div>
        </div>
        
        <div class="aa-stat-card">
            <div class="aa-stat-icon dashicons dashicons-cart"></div>
            <div class="aa-stat-content">
                <span class="aa-stat-value"><?php echo esc_html($converted_visits); ?></span>
                <span class="aa-stat-label"><?php _e('Conversiones', 'affiliate-assets'); ?></span>
            </div>
        </div>
    </div>
    
    <div class="aa-dashboard-widgets">
        <div class="aa-widget">
            <h2><?php _e('🚀 Inicio Rápido', 'affiliate-assets'); ?></h2>
            <ul>
                <li>
                    <span class="dashicons dashicons-admin-users"></span>
                    <a href="<?php echo admin_url('admin.php?page=affiliate-assets-affiliates'); ?>">
                        <?php _e('Gestionar Afiliados', 'affiliate-assets'); ?>
                    </a>
                </li>
                <li>
                    <span class="dashicons dashicons-visibility"></span>
                    <a href="<?php echo admin_url('admin.php?page=affiliate-assets-visits'); ?>">
                        <?php _e('Ver Visitas', 'affiliate-assets'); ?>
                    </a>
                </li>
                <li>
                    <span class="dashicons dashicons-admin-settings"></span>
                    <a href="<?php echo admin_url('admin.php?page=affiliate-assets-settings'); ?>">
                        <?php _e('Configurar Plugin', 'affiliate-assets'); ?>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="aa-widget">
            <h2><?php _e('📌 Próximas Funcionalidades (Fase 2)', 'affiliate-assets'); ?></h2>
            <ul class="aa-feature-list">
                <li><span class="dashicons dashicons-hourglass"></span> Sistema de Inventario Virtual</li>
                <li><span class="dashicons dashicons-download"></span> Descuento de Stock por Ventas</li>
                <li><span class="dashicons dashicons-money-alt"></span> Cálculo Automático de Comisiones</li>
                <li><span class="dashicons dashicons-networking"></span> Transferencias entre Afiliados</li>
            </ul>
        </div>
    </div>
</div>

<style>
.aa-dashboard { max-width: 1200px; }
.aa-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
.aa-stat-card { background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.aa-stat-icon { font-size: 40px; color: #2271b1; }
.aa-stat-content { display: flex; flex-direction: column; }
.aa-stat-value { font-size: 28px; font-weight: bold; color: #1d2327; }
.aa-stat-label { font-size: 13px; color: #646970; }
.aa-dashboard-widgets { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
.aa-widget { background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; }
.aa-widget h2 { margin-top: 0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
.aa-widget ul { list-style: none; padding: 0; margin: 0; }
.aa-widget li { padding: 8px 0; display: flex; align-items: center; gap: 10px; }
.aa-widget li a { text-decoration: none; color: #2271b1; }
.aa-widget li a:hover { color: #135e96; }
.aa-feature-list .dashicons { color: #f0b849; }
</style>
