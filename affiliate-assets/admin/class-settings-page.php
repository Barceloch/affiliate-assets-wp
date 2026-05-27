<?php
/**
 * Settings page class.
 * Handles plugin settings page and options.
 *
 * @package AffiliateAssets\Admin
 */

namespace AffiliateAssets\Admin;

use AffiliateAssets\Core\Class_Loader;

class Class_Settings_Page {
    
    /**
     * Loader instance.
     *
     * @var Class_Loader
     */
    protected $loader;
    
    /**
     * Settings sections.
     *
     * @var array
     */
    protected $sections = array();
    
    /**
     * Constructor.
     *
     * @param Class_Loader $loader Loader instance.
     */
    public function __construct($loader) {
        $this->loader = $loader;
    }
    
    /**
     * Run settings page initialization.
     */
    public function run() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_aa_save_settings', array($this, 'handle_save'));
    }
    
    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting('aa_settings_group', 'aa_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));
        
        // General section
        add_settings_section(
            'aa_general_section',
            __('Configuración General', 'affiliate-assets'),
            array($this, 'render_general_section'),
            'aa-settings'
        );
        
        add_settings_field(
            'aa_program_enabled',
            __('Programa Habilitado', 'affiliate-assets'),
            array($this, 'render_checkbox_field'),
            'aa-settings',
            'aa_general_section',
            array(
                'label_for' => 'aa_program_enabled',
                'option_name' => 'aa_program_enabled',
                'description' => __('Habilitar o deshabilitar el programa de afiliados.', 'affiliate-assets'),
            )
        );
        
        add_settings_field(
            'aa_auto_approve_affiliates',
            __('Aprobación Automática', 'affiliate-assets'),
            array($this, 'render_checkbox_field'),
            'aa-settings',
            'aa_general_section',
            array(
                'label_for' => 'aa_auto_approve_affiliates',
                'option_name' => 'aa_auto_approve_affiliates',
                'description' => __('Aprobar automáticamente nuevos afiliados sin revisión.', 'affiliate-assets'),
            )
        );
        
        add_settings_field(
            'aa_membership_product_id',
            __('Producto de Membresía', 'affiliate-assets'),
            array($this, 'render_product_select'),
            'aa-settings',
            'aa_general_section',
            array(
                'label_for' => 'aa_membership_product_id',
                'option_name' => 'aa_membership_product_id',
                'description' => __('Producto de WooCommerce que otorga acceso al programa de afiliados ($500).', 'affiliate-assets'),
            )
        );
        
        add_settings_field(
            'aa_default_commission_rate',
            __('Comisión Predeterminada', 'affiliate-assets'),
            array($this, 'render_number_field'),
            'aa-settings',
            'aa_general_section',
            array(
                'label_for' => 'aa_default_commission_rate',
                'option_name' => 'aa_default_commission_rate',
                'description' => __('Porcentaje de comisión por venta referida (%).', 'affiliate-assets'),
                'min' => 0,
                'max' => 100,
            )
        );
        
        // Tracking section
        add_settings_section(
            'aa_tracking_section',
            __('Tracking y Cookies', 'affiliate-assets'),
            array($this, 'render_tracking_section'),
            'aa-settings'
        );
        
        add_settings_field(
            'aa_cookie_duration',
            __('Duración de Cookie', 'affiliate-assets'),
            array($this, 'render_number_field'),
            'aa-settings',
            'aa_tracking_section',
            array(
                'label_for' => 'aa_cookie_duration',
                'option_name' => 'aa_cookie_duration',
                'description' => __('Días que la cookie de referido permanece activa (default: 30).', 'affiliate-assets'),
                'min' => 1,
            )
        );
        
        add_settings_field(
            'aa_enable_pretty_urls',
            __('URLs Amigables', 'affiliate-assets'),
            array($this, 'render_checkbox_field'),
            'aa-settings',
            'aa_tracking_section',
            array(
                'label_for' => 'aa_enable_pretty_urls',
                'option_name' => 'aa_enable_pretty_urls',
                'description' => __('Usar URLs tipo /referido/CODE/ en lugar de ?aa_ref=CODE.', 'affiliate-assets'),
            )
        );
        
        add_settings_field(
            'aa_referral_slug',
            __('Slug de Referido', 'affiliate-assets'),
            array($this, 'render_text_field'),
            'aa-settings',
            'aa_tracking_section',
            array(
                'label_for' => 'aa_referral_slug',
                'option_name' => 'aa_referral_slug',
                'description' => __('Slug para URLs amigables (default: "referido").', 'affiliate-assets'),
            )
        );
        
        // QR section
        add_settings_section(
            'aa_qr_section',
            __('Códigos QR', 'affiliate-assets'),
            array($this, 'render_qr_section'),
            'aa-settings'
        );
        
        add_settings_field(
            'aa_qr_size',
            __('Tamaño QR', 'affiliate-assets'),
            array($this, 'render_number_field'),
            'aa-settings',
            'aa_qr_section',
            array(
                'label_for' => 'aa_qr_size',
                'option_name' => 'aa_qr_size',
                'description' => __('Tamaño del código QR en píxeles.', 'affiliate-assets'),
                'min' => 100,
                'max' => 1000,
            )
        );
        
        add_settings_field(
            'aa_qr_fg_color',
            __('Color de Frente', 'affiliate-assets'),
            array($this, 'render_color_field'),
            'aa-settings',
            'aa_qr_section',
            array(
                'label_for' => 'aa_qr_fg_color',
                'option_name' => 'aa_qr_fg_color',
                'description' => __('Color hexadecimal para el frente del QR.', 'affiliate-assets'),
            )
        );
        
        add_settings_field(
            'aa_qr_bg_color',
            __('Color de Fondo', 'affiliate-assets'),
            array($this, 'render_color_field'),
            'aa-settings',
            'aa_qr_section',
            array(
                'label_for' => 'aa_qr_bg_color',
                'option_name' => 'aa_qr_bg_color',
                'description' => __('Color hexadecimal para el fondo del QR.', 'affiliate-assets'),
            )
        );
    }
    
    /**
     * Sanitize settings.
     *
     * @param array $input Input settings.
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['aa_program_enabled'] = isset($input['aa_program_enabled']) ? 1 : 0;
        $sanitized['aa_auto_approve_affiliates'] = isset($input['aa_auto_approve_affiliates']) ? 1 : 0;
        $sanitized['aa_membership_product_id'] = absint($input['aa_membership_product_id'] ?? 0);
        $sanitized['aa_default_commission_rate'] = floatval($input['aa_default_commission_rate'] ?? 10);
        $sanitized['aa_cookie_duration'] = absint($input['aa_cookie_duration'] ?? 30);
        $sanitized['aa_enable_pretty_urls'] = isset($input['aa_enable_pretty_urls']) ? 1 : 0;
        $sanitized['aa_referral_slug'] = sanitize_title($input['aa_referral_slug'] ?? 'referido');
        $sanitized['aa_qr_size'] = absint($input['aa_qr_size'] ?? 300);
        $sanitized['aa_qr_fg_color'] = sanitize_hex_color_no_hash($input['aa_qr_fg_color'] ?? '000000');
        $sanitized['aa_qr_bg_color'] = sanitize_hex_color_no_hash($input['aa_qr_bg_color'] ?? 'FFFFFF');
        
        return $sanitized;
    }
    
    /**
     * Handle settings save via admin_post.
     */
    public function handle_save() {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'affiliate-assets'));
        }
        
        check_admin_referer('aa_save_settings');
        
        $settings = isset($_POST['aa_settings']) ? $_POST['aa_settings'] : array();
        $sanitized = $this->sanitize_settings($settings);
        
        update_option('aa_settings', $sanitized);
        
        wp_redirect(add_query_arg(array(
            'page' => 'affiliate-assets-settings',
            'settings-updated' => 'true',
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Render general section description.
     */
    public function render_general_section() {
        echo '<p>' . __('Configuración general del programa de afiliados.', 'affiliate-assets') . '</p>';
    }
    
    /**
     * Render tracking section description.
     */
    public function render_tracking_section() {
        echo '<p>' . __('Configuración del sistema de tracking y cookies.', 'affiliate-assets') . '</p>';
    }
    
    /**
     * Render QR section description.
     */
    public function render_qr_section() {
        echo '<p>' . __('Configuración de apariencia para códigos QR.', 'affiliate-assets') . '</p>';
    }
    
    /**
     * Render checkbox field.
     *
     * @param array $args Field arguments.
     */
    public function render_checkbox_field($args) {
        $options = get_option('aa_settings', array());
        $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : 0;
        
        echo '<label for="' . esc_attr($args['label_for']) . '">';
        echo '<input type="checkbox" id="' . esc_attr($args['label_for']) . '" name="aa_settings[' . esc_attr($args['option_name']) . ']" value="1" ' . checked(1, $value, false) . ' />';
        echo ' ' . esc_html($args['description']);
        echo '</label>';
    }
    
    /**
     * Render text field.
     *
     * @param array $args Field arguments.
     */
    public function render_text_field($args) {
        $options = get_option('aa_settings', array());
        $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : '';
        
        echo '<input type="text" id="' . esc_attr($args['label_for']) . '" name="aa_settings[' . esc_attr($args['option_name']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Render number field.
     *
     * @param array $args Field arguments.
     */
    public function render_number_field($args) {
        $options = get_option('aa_settings', array());
        $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : ($args['min'] ?? 0);
        
        $extra = '';
        if (isset($args['min'])) {
            $extra .= ' min="' . esc_attr($args['min']) . '"';
        }
        if (isset($args['max'])) {
            $extra .= ' max="' . esc_attr($args['max']) . '"';
        }
        
        echo '<input type="number" id="' . esc_attr($args['label_for']) . '" name="aa_settings[' . esc_attr($args['option_name']) . ']" value="' . esc_attr($value) . '"' . $extra . ' class="small-text" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Render color field.
     *
     * @param array $args Field arguments.
     */
    public function render_color_field($args) {
        $options = get_option('aa_settings', array());
        $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : '';
        
        echo '<input type="color" id="' . esc_attr($args['label_for']) . '" name="aa_settings[' . esc_attr($args['option_name']) . ']" value="#' . esc_attr($value) . '" class="aa-color-picker" />';
        echo ' <span class="aa-color-value">#' . esc_attr($value) . '</span>';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Render product select field.
     *
     * @param array $args Field arguments.
     */
    public function render_product_select($args) {
        $options = get_option('aa_settings', array());
        $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : 0;
        
        echo '<select id="' . esc_attr($args['label_for']) . '" name="aa_settings[' . esc_attr($args['option_name']) . ']" class="regular-text">';
        echo '<option value="0">' . __('— Seleccionar producto —', 'affiliate-assets') . '</option>';
        
        if (class_exists('WooCommerce')) {
            $products = wc_get_products(array(
                'limit' => -1,
                'status' => 'publish',
            ));
            
            foreach ($products as $product) {
                echo '<option value="' . esc_attr($product->get_id()) . '" ' . selected($value, $product->get_id(), false) . '>' . esc_html($product->get_name()) . '</option>';
            }
        }
        
        echo '</select>';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
}
