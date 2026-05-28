<?php
/**
 * Plugin Name: AffiliateAssets
 * Plugin URI: https://example.com/affiliate-assets
 * Description: Sistema de gestión de afiliados e inversores con tracking, QR y panel personalizado. Inspirado arquitectónicamente en SliceWP.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: affiliate-assets
 * Domain Path: /languages
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AA_PLUGIN_VERSION', '1.0.0');
define('AA_PLUGIN_FILE', __FILE__);
define('AA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AA_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader PSR-4
spl_autoload_register(function ($class) {
    $prefix = 'AffiliateAssets\\';
    $base_dir = AA_PLUGIN_DIR;
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', strtolower(str_replace('_', '-', $relative_class))) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Manual class loading for non-namespaced files
function aa_load_class($file) {
    $path = AA_PLUGIN_DIR . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

// Load core classes
aa_load_class('core/class-loader.php');
aa_load_class('core/class-i18n.php');
aa_load_class('core/class-activator.php');
aa_load_class('core/class-deactivator.php');
aa_load_class('core/class-core.php');

// Initialize plugin on plugins_loaded
add_action('plugins_loaded', 'aa_init_plugin');

function aa_init_plugin() {
    // Load text domain
    load_plugin_textdomain('affiliate-assets', false, dirname(AA_PLUGIN_BASENAME) . '/languages');
    
    // Check WooCommerce dependency
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'aa_woocommerce_missing_notice');
        return;
    }
    
    // Initialize core using singleton pattern
    $plugin = AffiliateAssets\Core\Class_Core::get_instance();
    $plugin->run();
}

function aa_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('AffiliateAssets requiere WooCommerce para funcionar. Por favor instala y activa WooCommerce.', 'affiliate-assets'); ?></p>
    </div>
    <?php
}

// Activation hook
register_activation_hook(__FILE__, array('AffiliateAssets\Core\Class_Activator', 'activate'));

// Deactivation hook
register_deactivation_hook(__FILE__, array('AffiliateAssets\Core\Class_Deactivator', 'deactivate'));
