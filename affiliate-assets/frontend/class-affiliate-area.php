<?php
/**
 * Handles the affiliate area frontend.
 *
 * @package AffiliateAssets\Frontend
 */

namespace AffiliateAssets\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

class Class_Affiliate_Area {
    
    /**
     * The loader for hooks and filters.
     *
     * @var \AffiliateAssets\Core\Class_Loader
     */
    protected $loader;
    
    /**
     * Constructor.
     *
     * @param \AffiliateAssets\Core\Class_Loader $loader
     */
    public function __construct($loader) {
        $this->loader = $loader;
    }
    
    /**
     * Run the affiliate area.
     */
    public function run() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_shortcode('aa_affiliate_area', array($this, 'render_affiliate_area'));
        add_shortcode('aa_affiliate_dashboard', array($this, 'render_dashboard'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // Handle AJAX requests
        add_action('wp_ajax_aa_copy_referral_link', array($this, 'ajax_copy_referral_link'));
        add_action('wp_ajax_nopriv_aa_copy_referral_link', array($this, 'ajax_copy_referral_link'));
    }
    
    /**
     * Enqueue frontend assets.
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'affiliate-assets-frontend',
            AA_PLUGIN_URL . 'frontend/assets/css/frontend.css',
            array(),
            AA_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'affiliate-assets-frontend',
            AA_PLUGIN_URL . 'frontend/assets/js/frontend.js',
            array('jquery'),
            AA_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('affiliate-assets-frontend', 'aaFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aa_frontend_nonce'),
            'i18n' => array(
                'copied' => __('¡Copiado!', 'affiliate-assets'),
                'copy' => __('Copiar', 'affiliate-assets'),
                'error' => __('Error', 'affiliate-assets'),
            )
        ));
    }
    
    /**
     * Render the full affiliate area with tabs.
     *
     * @param array $atts
     * @return string
     */
    public function render_affiliate_area($atts = array()) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Debes iniciar sesión para acceder al panel de afiliado.', 'affiliate-assets') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $affiliate = aa_get_affiliate_by_user_id($user_id);
        
        if (!$affiliate || $affiliate->get_status() !== 'active') {
            return '<p>' . __('No tienes acceso al panel de afiliado. Tu solicitud está pendiente o fue rechazada.', 'affiliate-assets') . '</p>';
        }
        
        ob_start();
        include AA_PLUGIN_DIR . 'frontend/templates/affiliate-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Render only the dashboard tab.
     *
     * @param array $atts
     * @return string
     */
    public function render_dashboard($atts = array()) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Debes iniciar sesión para acceder al panel de afiliado.', 'affiliate-assets') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $affiliate = aa_get_affiliate_by_user_id($user_id);
        
        if (!$affiliate || $affiliate->get_status() !== 'active') {
            return '<p>' . __('No tienes acceso al panel de afiliado.', 'affiliate-assets') . '</p>';
        }
        
        ob_start();
        include AA_PLUGIN_DIR . 'frontend/templates/affiliate-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for copying referral link.
     */
    public function ajax_copy_referral_link() {
        check_ajax_referer('aa_frontend_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Debes iniciar sesión.', 'affiliate-assets')));
        }
        
        $user_id = get_current_user_id();
        $affiliate = aa_get_affiliate_by_user_id($user_id);
        
        if (!$affiliate) {
            wp_send_json_error(array('message' => __('No eres un afiliado.', 'affiliate-assets')));
        }
        
        wp_send_json_success(array(
            'referral_url' => $affiliate->get_referral_url(),
        ));
    }
}
