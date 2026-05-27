<?php
/**
 * The core class that initializes and runs the plugin.
 * Follows singleton pattern inspired by SliceWP architecture.
 *
 * @package AffiliateAssets\Core
 */

namespace AffiliateAssets\Core;

class Class_Core {
    
    /**
     * The single instance of this class.
     *
     * @var Class_Core
     */
    private static $instance = null;
    
    /**
     * The loader for hooks and filters.
     *
     * @var Class_Loader
     */
    protected $loader;
    
    /**
     * Plugin configuration.
     *
     * @var array
     */
    protected $config;
    
    /**
     * Get singleton instance.
     *
     * @return Class_Core
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - private to enforce singleton.
     */
    private function __construct() {
        $this->load_dependencies();
        $this->define_constants();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        // Core classes already loaded in main file
        $this->loader = new Class_Loader();
    }
    
    /**
     * Define additional constants if needed.
     */
    private function define_constants() {
        // Additional constants can be defined here
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            $this->init_admin();
        }
        
        // Frontend hooks
        $this->init_frontend();
        
        // Tracking hooks
        $this->init_tracking();
        
        // Integration hooks
        $this->init_integrations();
    }
    
    /**
     * Initialize admin components.
     */
    private function init_admin() {
        aa_load_class('admin/class-admin.php');
        $admin = new \AffiliateAssets\Admin\Class_Admin($this->loader);
        $admin->run();
        
        aa_load_class('admin/class-settings-page.php');
        $settings_page = new \AffiliateAssets\Admin\Class_Settings_Page($this->loader);
        $settings_page->run();
    }
    
    /**
     * Initialize frontend components.
     */
    private function init_frontend() {
        aa_load_class('frontend/class-affiliate-area.php');
        $affiliate_area = new \AffiliateAssets\Frontend\Class_Affiliate_Area($this->loader);
        $affiliate_area->run();
        
        // Shortcodes
        aa_load_class('frontend/shortcodes/class-dashboard-shortcode.php');
        aa_load_class('frontend/shortcodes/class-register-shortcode.php');
        aa_load_class('frontend/shortcodes/class-login-shortcode.php');
        
        $dashboard_shortcode = new \AffiliateAssets\Frontend\Shortcodes\Class_Dashboard_Shortcode();
        $register_shortcode = new \AffiliateAssets\Frontend\Shortcodes\Class_Register_Shortcode();
        $login_shortcode = new \AffiliateAssets\Frontend\Shortcodes\Class_Login_Shortcode();
    }
    
    /**
     * Initialize tracking components.
     */
    private function init_tracking() {
        aa_load_class('tracking/class-referral-tracker.php');
        $tracker = new \AffiliateAssets\Tracking\Class_Referral_Tracker($this->loader);
        $tracker->run();
        
        aa_load_class('tracking/class-cookie-handler.php');
        $cookie_handler = new \AffiliateAssets\Tracking\Class_Cookie_Handler();
        
        aa_load_class('tracking/class-visit-logger.php');
        $visit_logger = new \AffiliateAssets\Tracking\Class_Visit_Logger($this->loader);
        $visit_logger->run();
    }
    
    /**
     * Initialize integrations.
     */
    private function init_integrations() {
        aa_load_class('integrations/class-woocommerce.php');
        $woocommerce = new \AffiliateAssets\Integrations\Class_Woocommerce($this->loader);
        $woocommerce->run();
    }
    
    /**
     * Initialize QR components.
     */
    public function init_qr() {
        aa_load_class('qr/class-qr-generator.php');
        return new \AffiliateAssets\QR\Class_QR_Generator();
    }
    
    /**
     * Run the plugin.
     */
    public function run() {
        $this->loader->run();
        
        // Load includes
        aa_load_class('includes/functions.php');
        aa_load_class('includes/class-settings.php');
        aa_load_class('includes/class-affiliate.php');
        aa_load_class('includes/class-visit.php');
        aa_load_class('includes/class-commission.php');
        
        // Abstract classes
        aa_load_class('includes/abstract/class-object.php');
        aa_load_class('includes/abstract/class-database.php');
    }
    
    /**
     * Get the loader.
     *
     * @return Class_Loader
     */
    public function get_loader() {
        return $this->loader;
    }
}
