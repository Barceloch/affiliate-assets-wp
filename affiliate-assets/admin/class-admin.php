<?php
/**
 * Admin class.
 * Handles admin menu, pages, and assets.
 *
 * @package AffiliateAssets\Admin
 */

namespace AffiliateAssets\Admin;

use AffiliateAssets\Core\Class_Loader;

class Class_Admin {
    
    /**
     * Loader instance.
     *
     * @var Class_Loader
     */
    protected $loader;
    
    /**
     * Menu slug.
     *
     * @var string
     */
    protected $menu_slug = 'affiliate-assets';
    
    /**
     * Constructor.
     *
     * @param Class_Loader $loader Loader instance.
     */
    public function __construct($loader) {
        $this->loader = $loader;
    }
    
    /**
     * Run admin initialization.
     */
    public function run() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_init', array($this, 'handle_actions'));
    }
    
    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('AffiliateAssets', 'affiliate-assets'),
            __('AffiliateAssets', 'affiliate-assets'),
            'manage_options',
            $this->menu_slug,
            array($this, 'render_dashboard'),
            'dashicons-networking',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            $this->menu_slug,
            __('Dashboard', 'affiliate-assets'),
            __('📊 Dashboard', 'affiliate-assets'),
            'manage_options',
            $this->menu_slug,
            array($this, 'render_dashboard')
        );
        
        // Affiliates submenu
        add_submenu_page(
            $this->menu_slug,
            __('Affiliates', 'affiliate-assets'),
            __('👥 Afiliados', 'affiliate-assets'),
            'manage_options',
            $this->menu_slug . '-affiliates',
            array($this, 'render_affiliates')
        );
        
        // Visits submenu
        add_submenu_page(
            $this->menu_slug,
            __('Visits', 'affiliate-assets'),
            __('👁️ Visitas', 'affiliate-assets'),
            'manage_options',
            $this->menu_slug . '-visits',
            array($this, 'render_visits')
        );
        
        // Commissions submenu (placeholder for Phase 2)
        add_submenu_page(
            $this->menu_slug,
            __('Commissions', 'affiliate-assets'),
            __('💰 Comisiones', 'affiliate-assets'),
            'manage_options',
            $this->menu_slug . '-commissions',
            array($this, 'render_commissions')
        );
        
        // Settings submenu
        add_submenu_page(
            $this->menu_slug,
            __('Settings', 'affiliate-assets'),
            __('⚙️ Configuración', 'affiliate-assets'),
            'manage_options',
            $this->menu_slug . '-settings',
            array($this, 'render_settings')
        );
    }
    
    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets($hook) {
        $screen = get_current_screen();
        
        // Only load on our pages
        if (strpos($screen->id, 'affiliate-assets') === false && strpos($hook, 'affiliate-assets') === false) {
            return;
        }
        
        wp_enqueue_style(
            'aa-admin-css',
            AA_PLUGIN_URL . 'admin/assets/css/admin.css',
            array(),
            AA_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'aa-admin-js',
            AA_PLUGIN_URL . 'admin/assets/js/admin.js',
            array('jquery'),
            AA_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('aa-admin-js', 'aaAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aa_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('¿Estás seguro de que deseas eliminar este elemento?', 'affiliate-assets'),
                'error' => __('Error al procesar la solicitud.', 'affiliate-assets'),
            ),
        ));
    }
    
    /**
     * Handle admin actions.
     */
    public function handle_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Approve affiliate
        if (isset($_GET['aa_action']) && $_GET['aa_action'] === 'approve_affiliate' && isset($_GET['affiliate_id'])) {
            check_admin_referer('aa_approve_affiliate');
            
            $affiliate_id = absint($_GET['affiliate_id']);
            $affiliate = new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
            
            if ($affiliate->get_id()) {
                $affiliate->approve();
                \AffiliateAssets\Includes\aa_send_approval_email($affiliate);
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>' . __('Afiliado aprobado correctamente.', 'affiliate-assets') . '</p></div>';
                });
            }
            
            wp_redirect(remove_query_arg(array('aa_action', 'affiliate_id', '_wpnonce')));
            exit;
        }
        
        // Reject affiliate
        if (isset($_GET['aa_action']) && $_GET['aa_action'] === 'reject_affiliate' && isset($_GET['affiliate_id'])) {
            check_admin_referer('aa_reject_affiliate');
            
            $affiliate_id = absint($_GET['affiliate_id']);
            $affiliate = new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
            
            if ($affiliate->get_id()) {
                $affiliate->reject();
                \AffiliateAssets\Includes\aa_send_rejection_email($affiliate);
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>' . __('Afiliado rechazado correctamente.', 'affiliate-assets') . '</p></div>';
                });
            }
            
            wp_redirect(remove_query_arg(array('aa_action', 'affiliate_id', '_wpnonce')));
            exit;
        }
        
        // Delete affiliate
        if (isset($_GET['aa_action']) && $_GET['aa_action'] === 'delete_affiliate' && isset($_GET['affiliate_id'])) {
            check_admin_referer('aa_delete_affiliate');
            
            $affiliate_id = absint($_GET['affiliate_id']);
            $affiliate = new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
            
            if ($affiliate->get_id()) {
                $affiliate->delete();
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>' . __('Afiliado eliminado correctamente.', 'affiliate-assets') . '</p></div>';
                });
            }
            
            wp_redirect(remove_query_arg(array('aa_action', 'affiliate_id', '_wpnonce')));
            exit;
        }
    }
    
    /**
     * Render dashboard page.
     */
    public function render_dashboard() {
        include AA_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Render affiliates page.
     */
    public function render_affiliates() {
        include AA_PLUGIN_DIR . 'admin/views/affiliates.php';
    }
    
    /**
     * Render visits page.
     */
    public function render_visits() {
        include AA_PLUGIN_DIR . 'admin/views/visits.php';
    }
    
    /**
     * Render commissions page (placeholder).
     */
    public function render_commissions() {
        echo '<div class="wrap">';
        echo '<h1>' . __('Comisiones', 'affiliate-assets') . '</h1>';
        echo '<p>' . __('Esta funcionalidad estará disponible en la Fase 2.', 'affiliate-assets') . '</p>';
        echo '</div>';
    }
    
    /**
     * Render settings page.
     */
    public function render_settings() {
        include AA_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
