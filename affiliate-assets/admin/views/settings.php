<?php
/**
 * Admin settings view.
 *
 * @package AffiliateAssets\Admin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('aa_settings', array());
?>

<div class="wrap aa-settings-page">
    <h1><?php _e('Configuración de AffiliateAssets', 'affiliate-assets'); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field('aa_save_settings'); ?>
        <input type="hidden" name="action" value="aa_save_settings">
        
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'affiliate-assets'); ?></a>
            <a href="#tracking" class="nav-tab"><?php _e('Tracking', 'affiliate-assets'); ?></a>
            <a href="#qr" class="nav-tab"><?php _e('QR', 'affiliate-assets'); ?></a>
            <a href="#emails" class="nav-tab"><?php _e('Emails', 'affiliate-assets'); ?></a>
        </h2>
        
        <div class="aa-settings-tabs">
            <!-- General Tab -->
            <div id="general" class="aa-tab-content active">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Programa Habilitado', 'affiliate-assets'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="aa_settings[aa_program_enabled]" value="1" <?php checked(isset($settings['aa_program_enabled']) ? $settings['aa_program_enabled'] : 1, 1); ?> />
                                <?php _e('Habilitar programa de afiliados', 'affiliate-assets'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Aprobación Automática', 'affiliate-assets'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="aa_settings[aa_auto_approve_affiliates]" value="1" <?php checked(isset($settings['aa_auto_approve_affiliates']) ? $settings['aa_auto_approve_affiliates'] : 0, 1); ?> />
                                <?php _e('Aprobar automáticamente nuevos afiliados', 'affiliate-assets'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Producto de Membresía', 'affiliate-assets'); ?></th>
                        <td>
                            <select name="aa_settings[aa_membership_product_id]" class="regular-text">
                                <option value="0"><?php _e('— Seleccionar —', 'affiliate-assets'); ?></option>
                                <?php
                                if (class_exists('WooCommerce')) {
                                    $products = wc_get_products(array('limit' => -1));
                                    foreach ($products as $product) {
                                        $selected = selected(isset($settings['aa_membership_product_id']) ? $settings['aa_membership_product_id'] : 0, $product->get_id(), false);
                                        echo '<option value="' . esc_attr($product->get_id()) . '" ' . $selected . '>' . esc_html($product->get_name()) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <p class="description"><?php _e('Producto que otorga acceso al programa ($500)', 'affiliate-assets'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Comisión Predeterminada (%)', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="number" name="aa_settings[aa_default_commission_rate]" value="<?php echo esc_attr(isset($settings['aa_default_commission_rate']) ? $settings['aa_default_commission_rate'] : 10); ?>" min="0" max="100" class="small-text" />
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Tracking Tab -->
            <div id="tracking" class="aa-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Duración de Cookie (días)', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="number" name="aa_settings[aa_cookie_duration]" value="<?php echo esc_attr(isset($settings['aa_cookie_duration']) ? $settings['aa_cookie_duration'] : 30); ?>" min="1" class="small-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('URLs Amigables', 'affiliate-assets'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="aa_settings[aa_enable_pretty_urls]" value="1" <?php checked(isset($settings['aa_enable_pretty_urls']) ? $settings['aa_enable_pretty_urls'] : 0, 1); ?> />
                                <?php _e('Usar /referido/CODE/ en lugar de ?aa_ref=CODE', 'affiliate-assets'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Slug de Referido', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="text" name="aa_settings[aa_referral_slug]" value="<?php echo esc_attr(isset($settings['aa_referral_slug']) ? $settings['aa_referral_slug'] : 'referido'); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- QR Tab -->
            <div id="qr" class="aa-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Tamaño QR (px)', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="number" name="aa_settings[aa_qr_size]" value="<?php echo esc_attr(isset($settings['aa_qr_size']) ? $settings['aa_qr_size'] : 300); ?>" min="100" max="1000" class="small-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Color de Frente', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="color" name="aa_settings[aa_qr_fg_color]" value="#<?php echo esc_attr(isset($settings['aa_qr_fg_color']) ? $settings['aa_qr_fg_color'] : '000000'); ?>" class="aa-color-picker" />
                            <span class="aa-color-value">#<?php echo esc_attr(isset($settings['aa_qr_fg_color']) ? $settings['aa_qr_fg_color'] : '000000'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Color de Fondo', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="color" name="aa_settings[aa_qr_bg_color]" value="#<?php echo esc_attr(isset($settings['aa_qr_bg_color']) ? $settings['aa_qr_bg_color'] : 'FFFFFF'); ?>" class="aa-color-picker" />
                            <span class="aa-color-value">#<?php echo esc_attr(isset($settings['aa_qr_bg_color']) ? $settings['aa_qr_bg_color'] : 'FFFFFF'); ?></span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Emails Tab -->
            <div id="emails" class="aa-tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Asunto - Bienvenida', 'affiliate-assets'); ?></th>
                        <td>
                            <input type="text" name="aa_settings[aa_email_welcome_subject]" value="<?php echo esc_attr(isset($settings['aa_email_welcome_subject']) ? $settings['aa_email_welcome_subject'] : ''); ?>" class="large-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cuerpo - Bienvenida', 'affiliate-assets'); ?></th>
                        <td>
                            <textarea name="aa_settings[aa_email_welcome_body]" rows="6" class="large-text"><?php echo esc_textarea(isset($settings['aa_email_welcome_body']) ? $settings['aa_email_welcome_body'] : ''); ?></textarea>
                            <p class="description"><?php _e('Placeholders: {{affiliate_name}}, {{referral_link}}, {{qr_code_url}}, {{site_name}}', 'affiliate-assets'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php submit_button(__('Guardar Configuración', 'affiliate-assets')); ?>
    </form>
</div>

<style>
.aa-settings-tabs { background: #fff; padding: 20px; border: 1px solid #c3c4c7; border-top: none; }
.aa-tab-content { display: none; }
.aa-tab-content.active { display: block; }
.nav-tab-wrapper { margin-bottom: 0; }
.nav-tab { cursor: pointer; }
.nav-tab-active { background: #fff; border-bottom-color: #fff; }
.aa-color-picker { vertical-align: middle; }
.aa-color-value { margin-left: 10px; font-family: monospace; }
</style>

<script>
jQuery(document).ready(function($) {
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.aa-tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    $('.aa-color-picker').on('input', function() {
        $(this).next('.aa-color-value').text($(this).val());
    });
});
</script>
