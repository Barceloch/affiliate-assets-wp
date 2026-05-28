<?php
/**
 * Affiliate Dashboard Template
 *
 * @package AffiliateAssets\Frontend\Templates
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var \AffiliateAssets\Includes\Class_Affiliate $affiliate
 */
$affiliate = aa_get_affiliate_by_user_id(get_current_user_id());
$stats = array(
    'total_visits' => aa_count_visits_by_affiliate($affiliate->get_id()),
    'total_conversions' => aa_count_conversions_by_affiliate($affiliate->get_id()),
    'total_commissions' => 0, // Placeholder for Phase 2
);
?>

<div class="aa-affiliate-area">
    <div class="aa-tabs-header">
        <button class="aa-tab-btn active" data-tab="dashboard"><?php _e('📊 Dashboard', 'affiliate-assets'); ?></button>
        <button class="aa-tab-btn" data-tab="links"><?php _e('🔗 Enlaces de Referido', 'affiliate-assets'); ?></button>
        <button class="aa-tab-btn" data-tab="stats"><?php _e('📈 Estadísticas', 'affiliate-assets'); ?></button>
        <button class="aa-tab-btn" data-tab="settings"><?php _e('⚙️ Configuración', 'affiliate-assets'); ?></button>
    </div>
    
    <div class="aa-tabs-content">
        <!-- Dashboard Tab -->
        <div class="aa-tab-panel active" id="dashboard">
            <h2><?php _e('Resumen del Afiliado', 'affiliate-assets'); ?></h2>
            
            <div class="aa-stats-grid">
                <div class="aa-stat-card">
                    <span class="aa-stat-value"><?php echo esc_html($stats['total_visits']); ?></span>
                    <span class="aa-stat-label"><?php _e('Visitas Totales', 'affiliate-assets'); ?></span>
                </div>
                <div class="aa-stat-card">
                    <span class="aa-stat-value"><?php echo esc_html($stats['total_conversions']); ?></span>
                    <span class="aa-stat-label"><?php _e('Conversiones', 'affiliate-assets'); ?></span>
                </div>
                <div class="aa-stat-card">
                    <span class="aa-stat-value"><?php echo esc_html($stats['total_commissions']); ?>€</span>
                    <span class="aa-stat-label"><?php _e('Comisiones Ganadas', 'affiliate-assets'); ?></span>
                </div>
            </div>
            
            <div class="aa-recent-activity">
                <h3><?php _e('Actividad Reciente', 'affiliate-assets'); ?></h3>
                <p class="aa-placeholder"><?php _e('No hay actividad reciente.', 'affiliate-assets'); ?></p>
            </div>
        </div>
        
        <!-- Links Tab -->
        <div class="aa-tab-panel" id="links">
            <h2><?php _e('Tus Enlaces de Referido', 'affiliate-assets'); ?></h2>
            
            <div class="aa-referral-link-box">
                <label><?php _e('Enlace Principal:', 'affiliate-assets'); ?></label>
                <div class="aa-input-copy">
                    <input type="text" value="<?php echo esc_attr($affiliate->get_referral_url()); ?>" readonly id="aa-referral-url" />
                    <button type="button" class="aa-copy-btn" data-copy-target="aa-referral-url">
                        <?php _e('Copiar', 'affiliate-assets'); ?>
                    </button>
                </div>
            </div>
            
            <div class="aa-qr-section">
                <h3><?php _e('Código QR', 'affiliate-assets'); ?></h3>
                <div class="aa-qr-preview">
                    <img src="<?php echo esc_url(aa_get_qr_code_url($affiliate->get_referral_url())); ?>" alt="<?php _e('QR Code', 'affiliate-assets'); ?>" />
                </div>
                <div class="aa-qr-download">
                    <a href="<?php echo esc_url(aa_get_qr_code_download_url($affiliate->get_referral_url(), 'png')); ?>" class="aa-btn" download>
                        <?php _e('Descargar PNG', 'affiliate-assets'); ?>
                    </a>
                    <a href="<?php echo esc_url(aa_get_qr_code_download_url($affiliate->get_referral_url(), 'svg')); ?>" class="aa-btn" download>
                        <?php _e('Descargar SVG', 'affiliate-assets'); ?>
                    </a>
                </div>
            </div>
            
            <div class="aa-share-buttons">
                <h3><?php _e('Compartir en Redes Sociales', 'affiliate-assets'); ?></h3>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($affiliate->get_referral_url()); ?>" target="_blank" class="aa-share-btn facebook">Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($affiliate->get_referral_url()); ?>" target="_blank" class="aa-share-btn twitter">Twitter/X</a>
                <a href="https://wa.me/?text=<?php echo urlencode($affiliate->get_referral_url()); ?>" target="_blank" class="aa-share-btn whatsapp">WhatsApp</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($affiliate->get_referral_url()); ?>" target="_blank" class="aa-share-btn linkedin">LinkedIn</a>
                <a href="mailto:?subject=<?php echo urlencode(__('Invitación especial', 'affiliate-assets')); ?>&body=<?php echo urlencode($affiliate->get_referral_url()); ?>" class="aa-share-btn email">Email</a>
            </div>
        </div>
        
        <!-- Stats Tab -->
        <div class="aa-tab-panel" id="stats">
            <h2><?php _e('Estadísticas Detalladas', 'affiliate-assets'); ?></h2>
            <div class="aa-chart-container">
                <canvas id="aa-visits-chart"></canvas>
            </div>
            <p class="aa-placeholder"><?php _e('Gráfico de visitas (placeholder para Fase 2)', 'affiliate-assets'); ?></p>
        </div>
        
        <!-- Settings Tab -->
        <div class="aa-tab-panel" id="settings">
            <h2><?php _e('Configuración de tu Cuenta', 'affiliate-assets'); ?></h2>
            
            <form method="post" class="aa-settings-form">
                <?php wp_nonce_field('aa_update_affiliate_settings', 'aa_settings_nonce'); ?>
                
                <div class="aa-form-group">
                    <label for="payment_email"><?php _e('Email de Pago:', 'affiliate-assets'); ?></label>
                    <input type="email" id="payment_email" name="payment_email" value="<?php echo esc_attr($affiliate->get_payment_email()); ?>" />
                </div>
                
                <div class="aa-form-group">
                    <label for="website_url"><?php _e('Sitio Web:', 'affiliate-assets'); ?></label>
                    <input type="url" id="website_url" name="website_url" value="<?php echo esc_attr($affiliate->get_website_url()); ?>" />
                </div>
                
                <div class="aa-form-group">
                    <label for="promotional_methods"><?php _e('Métodos Promocionales:', 'affiliate-assets'); ?></label>
                    <textarea id="promotional_methods" name="promotional_methods" rows="4"><?php echo esc_textarea($affiliate->get_promotional_methods()); ?></textarea>
                    <small><?php _e('Describe cómo promocionas nuestros productos (redes sociales, blog, email, etc.)', 'affiliate-assets'); ?></small>
                </div>
                
                <button type="submit" name="aa_update_settings" class="aa-btn aa-btn-primary">
                    <?php _e('Guardar Cambios', 'affiliate-assets'); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.aa-tab-btn').on('click', function() {
        var tabId = $(this).data('tab');
        
        $('.aa-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.aa-tab-panel').removeClass('active');
        $('#' + tabId).addClass('active');
    });
    
    $('.aa-copy-btn').on('click', function() {
        var targetId = $(this).data('copy-target');
        var $input = $('#' + targetId);
        
        $input.select();
        document.execCommand('copy');
        
        var originalText = $(this).text();
        $(this).text('<?php _e('¡Copiado!', 'affiliate-assets'); ?>');
        
        setTimeout(function() {
            $('.aa-copy-btn').text(originalText);
        }, 2000);
    });
});
</script>
