<?php
/**
 * Settings management class.
 * Handles plugin options and configuration.
 *
 * @package AffiliateAssets\Includes
 */

namespace AffiliateAssets\Includes;

class Class_Settings {
    
    /**
     * Option name in wp_options.
     *
     * @var string
     */
    const OPTION_NAME = 'aa_settings';
    
    /**
     * Default settings.
     *
     * @var array
     */
    protected $defaults = array();
    
    /**
     * Current settings.
     *
     * @var array
     */
    protected $settings = array();
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->defaults = $this->get_default_settings();
        $this->settings = get_option(self::OPTION_NAME, $this->defaults);
    }
    
    /**
     * Get default settings.
     *
     * @return array
     */
    public function get_default_settings() {
        return array(
            // General
            'aa_program_enabled' => 1,
            'aa_auto_approve_affiliates' => 0,
            'aa_membership_product_id' => 0,
            'aa_investment_product_id' => 0,
            'aa_default_commission_rate' => 10,
            
            // Tracking
            'aa_cookie_duration' => 30,
            'aa_enable_pretty_urls' => 0,
            'aa_referral_slug' => 'referido',
            'aa_track_first_visit_only' => 0,
            
            // QR
            'aa_qr_size' => 300,
            'aa_qr_fg_color' => '000000',
            'aa_qr_bg_color' => 'FFFFFF',
            'aa_qr_margin' => 2,
            'aa_qr_logo_url' => '',
            
            // Appearance
            'aa_dashboard_page_id' => 0,
            'aa_register_page_id' => 0,
            'aa_login_page_id' => 0,
            
            // Emails
            'aa_email_welcome_subject' => __('¡Bienvenido al programa de afiliados!', 'affiliate-assets'),
            'aa_email_welcome_body' => __("Hola {{affiliate_name}},\n\n¡Gracias por unirte a nuestro programa de afiliados!\n\nTu enlace de referido es: {{referral_link}}\n\nTambién puedes usar tu código QR: {{qr_code_url}}\n\nSaludos,\n{{site_name}}", 'affiliate-assets'),
            'aa_email_approval_subject' => __('Tu afiliación ha sido aprobada', 'affiliate-assets'),
            'aa_email_approval_body' => __("Hola {{affiliate_name}},\n\n¡Tu afiliación ha sido aprobada!\n\nYa puedes comenzar a generar comisiones.\n\nSaludos,\n{{site_name}}", 'affiliate-assets'),
            'aa_email_rejection_subject' => __('Tu solicitud de afiliación ha sido rechazada', 'affiliate-assets'),
            'aa_email_rejection_body' => __("Hola {{affiliate_name}},\n\nLamentamos informarte que tu solicitud de afiliación ha sido rechazada.\n\nSaludos,\n{{site_name}}", 'affiliate-assets'),
        );
    }
    
    /**
     * Get a setting value.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get($key, $default = '') {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Set a setting value.
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     */
    public function set($key, $value) {
        $this->settings[$key] = $value;
    }
    
    /**
     * Get all settings.
     *
     * @return array
     */
    public function get_all() {
        return wp_parse_args($this->settings, $this->defaults);
    }
    
    /**
     * Save settings.
     *
     * @param array $settings Settings to save.
     * @return bool
     */
    public function save($settings = array()) {
        if (!empty($settings)) {
            $this->settings = wp_parse_args($settings, $this->settings);
        }
        
        return update_option(self::OPTION_NAME, $this->settings);
    }
    
    /**
     * Reset to defaults.
     *
     * @return bool
     */
    public function reset() {
        return delete_option(self::OPTION_NAME);
    }
}

/**
 * Helper function to get settings.
 *
 * @return array
 */
function aa_get_settings() {
    static $settings = null;
    
    if (null === $settings) {
        $settings_obj = new Class_Settings();
        $settings = $settings_obj->get_all();
    }
    
    return $settings;
}

/**
 * Update settings.
 *
 * @param array $settings Settings to update.
 * @return bool
 */
function aa_update_settings($settings) {
    $settings_obj = new Class_Settings();
    return $settings_obj->save($settings);
}
