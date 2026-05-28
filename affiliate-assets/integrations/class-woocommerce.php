<?php
/**
 * WooCommerce integration.
 *
 * @package AffiliateAssets\Integrations
 */

namespace AffiliateAssets\Integrations;

if (!defined('ABSPATH')) {
    exit;
}

class Class_Woocommerce {
    
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
     * Run the integration.
     */
    public function run() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Create affiliate when membership product is purchased
        add_action('woocommerce_order_status_completed', array($this, 'handle_membership_purchase'));
        
        // Track referral on checkout
        add_action('woocommerce_checkout_create_order', array($this, 'save_referral_to_order'), 10, 2);
        
        // Add referral meta to order display
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'display_referral_in_admin'));
    }
    
    /**
     * Handle membership product purchase.
     *
     * @param int $order_id Order ID.
     */
    public function handle_membership_purchase($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        $settings = aa_get_settings();
        $membership_product_id = isset($settings['aa_membership_product_id']) ? intval($settings['aa_membership_product_id']) : 0;
        
        if (!$membership_product_id) {
            return;
        }
        
        // Check if order contains membership product
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            
            if ($product_id == $membership_product_id) {
                $user_id = $order->get_user_id();
                
                if (!$user_id) {
                    return;
                }
                
                // Check if user is already an affiliate
                $existing_affiliate = aa_get_affiliate_by_user_id($user_id);
                
                if ($existing_affiliate) {
                    return; // Already an affiliate
                }
                
                // Create new affiliate
                $this->create_affiliate_from_purchase($user_id, $order_id);
                break;
            }
        }
    }
    
    /**
     * Create affiliate from membership purchase.
     *
     * @param int $user_id User ID.
     * @param int $order_id Order ID.
     */
    private function create_affiliate_from_purchase($user_id, $order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aa_affiliates';
        
        // Generate unique referral code
        $referral_code = $this->generate_referral_code($user_id);
        
        $data = array(
            'user_id' => $user_id,
            'status' => aa_get_settings()['aa_auto_approve_affiliates'] ? 'active' : 'pending',
            'referral_code' => $referral_code,
            'referral_url' => home_url('/?aa_ref=' . $referral_code),
            'payment_email' => get_user_meta($user_id, 'billing_email', true) ?: wp_get_current_user()->user_email,
            'website_url' => get_user_meta($user_id, 'billing_company', true) ?: '',
            'registration_date' => current_time('mysql'),
        );
        
        if (aa_get_settings()['aa_auto_approve_affiliates']) {
            $data['approved_date'] = current_time('mysql');
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->insert($table_name, $data);
        
        $affiliate_id = $wpdb->insert_id;
        
        // Send welcome email
        $this->send_welcome_email($affiliate_id);
        
        // Notify admin if approval is required
        if (!aa_get_settings()['aa_auto_approve_affiliates']) {
            $this->notify_admin_new_affiliate($affiliate_id);
        }
    }
    
    /**
     * Generate unique referral code.
     *
     * @param int $user_id User ID.
     * @return string
     */
    private function generate_referral_code($user_id) {
        $user = get_userdata($user_id);
        $base_code = sanitize_title($user->user_login);
        
        // Ensure uniqueness
        $code = $base_code;
        $counter = 1;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'aa_affiliates';
        
        while (true) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE referral_code = %s", $code));
            
            if (!$exists) {
                break;
            }
            
            $code = $base_code . '-' . $counter;
            $counter++;
        }
        
        return $code;
    }
    
    /**
     * Save referral information to order.
     *
     * @param \WC_Order $order Order object.
     * @param array $data Checkout data.
     */
    public function save_referral_to_order($order, $data) {
        $affiliate_id = aa_get_current_affiliate_id();
        
        if ($affiliate_id) {
            $order->update_meta_data('_aa_referrer_id', $affiliate_id);
        }
    }
    
    /**
     * Display referral info in admin order page.
     *
     * @param \WC_Order $order Order object.
     */
    public function display_referral_in_admin($order) {
        $referrer_id = $order->get_meta('_aa_referrer_id');
        
        if ($referrer_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'aa_affiliates';
            
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $referrer_id));
            
            if ($affiliate) {
                echo '<p><strong>' . __('Referido por:', 'affiliate-assets') . '</strong> ' . esc_html($affiliate->referral_code) . '</p>';
            }
        }
    }
    
    /**
     * Send welcome email to new affiliate.
     *
     * @param int $affiliate_id Affiliate ID.
     */
    private function send_welcome_email($affiliate_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aa_affiliates';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $affiliate_id));
        
        if (!$affiliate) {
            return;
        }
        
        $user = get_userdata($affiliate->user_id);
        
        if (!$user) {
            return;
        }
        
        $settings = aa_get_settings();
        $subject = isset($settings['aa_email_welcome_subject']) ? $settings['aa_email_welcome_subject'] : __('¡Bienvenido al programa de afiliados!', 'affiliate-assets');
        $body = isset($settings['aa_email_welcome_body']) ? $settings['aa_email_welcome_body'] : '';
        
        // Replace placeholders
        $body = str_replace(
            array('{{affiliate_name}}', '{{referral_link}}', '{{qr_code_url}}', '{{site_name}}'),
            array($user->display_name, $affiliate->referral_url, aa_get_qr_code_url($affiliate->referral_url), get_bloginfo('name')),
            $body
        );
        
        wp_mail($user->user_email, $subject, nl2br($body));
    }
    
    /**
     * Notify admin of new affiliate pending approval.
     *
     * @param int $affiliate_id Affiliate ID.
     */
    private function notify_admin_new_affiliate($affiliate_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aa_affiliates';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $affiliate_id));
        
        if (!$affiliate) {
            return;
        }
        
        $user = get_userdata($affiliate->user_id);
        
        if (!$user) {
            return;
        }
        
        $admin_email = get_option('admin_email');
        $subject = sprintf(__('Nuevo afiliado pendiente de aprobación: %s', 'affiliate-assets'), $user->display_name);
        $body = sprintf(
            __('Un nuevo afiliado se ha registrado y está pendiente de aprobación.\n\nNombre: %s\nEmail: %s\n\nPuedes aprobarlo o rechazarlo en: %s', 'affiliate-assets'),
            $user->display_name,
            $user->user_email,
            admin_url('admin.php?page=affiliate-assets-affiliates')
        );
        
        wp_mail($admin_email, $subject, nl2br($body));
    }
}
