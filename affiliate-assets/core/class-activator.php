<?php
/**
 * Fired during plugin activation.
 * Creates database tables and sets initial options.
 *
 * @package AffiliateAssets\Core
 */

namespace AffiliateAssets\Core;

class Class_Activator {
    
    /**
     * Activate the plugin.
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create affiliates table
        self::create_affiliates_table($charset_collate);
        
        // Create visits table
        self::create_visits_table($charset_collate);
        
        // Create commissions table
        self::create_commissions_table($charset_collate);
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules for pretty URLs
        flush_rewrite_rules();
    }
    
    /**
     * Create the affiliates table.
     */
    private static function create_affiliates_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aa_affiliates';
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            status ENUM('pending', 'active', 'inactive', 'rejected') DEFAULT 'pending',
            referral_code VARCHAR(50) NOT NULL,
            referral_url TEXT,
            payment_email VARCHAR(255),
            website_url VARCHAR(255),
            promotional_methods TEXT,
            registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            approved_date DATETIME NULL,
            notes TEXT,
            PRIMARY KEY (id),
            UNIQUE KEY uk_user (user_id),
            UNIQUE KEY uk_referral_code (referral_code),
            INDEX idx_status (status),
            INDEX idx_referral_code (referral_code)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create the visits table.
     */
    private static function create_visits_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aa_visits';
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT,
            affiliate_id BIGINT UNSIGNED NOT NULL,
            url TEXT NOT NULL,
            ip VARCHAR(45),
            user_agent TEXT,
            referral_source VARCHAR(100),
            type ENUM('direct', 'referral', 'qr_scan') DEFAULT 'direct',
            date DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_converted TINYINT(1) DEFAULT 0,
            reference INT(11) NULL,
            PRIMARY KEY (id),
            INDEX idx_affiliate (affiliate_id),
            INDEX idx_date (date),
            INDEX idx_converted (is_converted)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Create the commissions table.
     */
    private static function create_commissions_table($charset_collate) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aa_commissions';
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT,
            affiliate_id BIGINT UNSIGNED NOT NULL,
            order_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            commission_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
            reference_type VARCHAR(50) DEFAULT 'order',
            reference_id BIGINT UNSIGNED NULL,
            notes TEXT,
            created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            paid_date DATETIME NULL,
            PRIMARY KEY (id),
            INDEX idx_affiliate (affiliate_id),
            INDEX idx_order (order_id),
            INDEX idx_status (status)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $defaults = array(
            // General settings
            'aa_program_enabled' => 1,
            'aa_auto_approve_affiliates' => 0,
            'aa_membership_product_id' => 0,
            'aa_investment_product_id' => 0,
            'aa_default_commission_rate' => 10,
            
            // Tracking settings
            'aa_cookie_duration' => 30,
            'aa_enable_pretty_urls' => 0,
            'aa_referral_slug' => 'referido',
            'aa_track_first_visit_only' => 0,
            
            // QR settings
            'aa_qr_size' => 300,
            'aa_qr_fg_color' => '000000',
            'aa_qr_bg_color' => 'FFFFFF',
            'aa_qr_margin' => 2,
            'aa_qr_logo_url' => '',
            
            // Appearance
            'aa_dashboard_page_id' => 0,
            'aa_register_page_id' => 0,
            'aa_login_page_id' => 0,
            
            // Email templates
            'aa_email_welcome_subject' => __('¡Bienvenido al programa de afiliados!', 'affiliate-assets'),
            'aa_email_welcome_body' => self::get_default_welcome_email(),
            'aa_email_approval_subject' => __('Tu afiliación ha sido aprobada', 'affiliate-assets'),
            'aa_email_approval_body' => self::get_default_approval_email(),
            'aa_email_rejection_subject' => __('Tu solicitud de afiliación ha sido rechazada', 'affiliate-assets'),
            'aa_email_rejection_body' => self::get_default_rejection_email(),
        );
        
        if (!get_option('aa_settings')) {
            add_option('aa_settings', $defaults);
        } else {
            // Merge with existing settings to preserve user data
            $existing = get_option('aa_settings');
            $merged = wp_parse_args($defaults, $existing);
            update_option('aa_settings', $merged);
        }
    }
    
    /**
     * Get default welcome email template.
     */
    private static function get_default_welcome_email() {
        return __("Hola {{affiliate_name}},\n\n¡Gracias por unirte a nuestro programa de afiliados!\n\nTu enlace de referido es: {{referral_link}}\n\nTambién puedes usar tu código QR: {{qr_code_url}}\n\nSaludos,\n{{site_name}}", 'affiliate-assets');
    }
    
    /**
     * Get default approval email template.
     */
    private static function get_default_approval_email() {
        return __("Hola {{affiliate_name}},\n\n¡Tu afiliación ha sido aprobada!\n\nYa puedes comenzar a generar comisiones.\n\nSaludos,\n{{site_name}}", 'affiliate-assets');
    }
    
    /**
     * Get default rejection email template.
     */
    private static function get_default_rejection_email() {
        return __("Hola {{affiliate_name}},\n\nLamentamos informarte que tu solicitud de afiliación ha sido rechazada.\n\nSaludos,\n{{site_name}}", 'affiliate-assets');
    }
}
