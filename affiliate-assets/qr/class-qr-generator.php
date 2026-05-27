<?php
/**
 * QR Generator class.
 * Generates QR codes for affiliate referral links.
 *
 * @package AffiliateAssets\QR
 */

namespace AffiliateAssets\QR;

class Class_QR_Generator {
    
    /**
     * Settings cache.
     *
     * @var array
     */
    protected $settings = array();
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->settings = \AffiliateAssets\Includes\aa_get_settings();
        
        // Register AJAX handlers
        add_action('wp_ajax_aa_generate_qr', array($this, 'ajax_generate_qr'));
        add_action('wp_ajax_nopriv_aa_generate_qr', array($this, 'ajax_generate_qr'));
        add_action('wp_ajax_aa_download_qr', array($this, 'ajax_download_qr'));
        add_action('wp_ajax_nopriv_aa_download_qr', array($this, 'ajax_download_qr'));
    }
    
    /**
     * Generate QR code for an affiliate.
     *
     * @param int $affiliate_id Affiliate ID.
     * @return string|false QR code data URL or false on failure.
     */
    public function generate($affiliate_id) {
        $affiliate = new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
        
        if (!$affiliate->get_id()) {
            return false;
        }
        
        $url = $affiliate->get_referral_url();
        
        // Check transient cache
        $cache_key = 'aa_qr_' . md5($url . serialize($this->settings));
        $cached = get_transient($cache_key);
        
        if ($cached) {
            return $cached;
        }
        
        // Generate QR code
        $qr_data = $this->generate_qr_code($url);
        
        // Cache for 24 hours
        set_transient($cache_key, $qr_data, DAY_IN_SECONDS);
        
        return $qr_data;
    }
    
    /**
     * Generate QR code from URL.
     *
     * @param string $url URL to encode.
     * @return string Data URL of QR code.
     */
    private function generate_qr_code($url) {
        $size = isset($this->settings['aa_qr_size']) ? absint($this->settings['aa_qr_size']) : 300;
        $fg_color = isset($this->settings['aa_qr_fg_color']) ? $this->settings['aa_qr_fg_color'] : '000000';
        $bg_color = isset($this->settings['aa_qr_bg_color']) ? $this->settings['aa_qr_bg_color'] : 'FFFFFF';
        $margin = isset($this->settings['aa_qr_margin']) ? absint($this->settings['aa_qr_margin']) : 2;
        
        // If phpqrcode library exists, use it
        if (file_exists(AA_PLUGIN_DIR . 'qr/libs/phpqrcode/phpqrcode.php')) {
            require_once AA_PLUGIN_DIR . 'qr/libs/phpqrcode/phpqrcode.php';
            
            // Generate to buffer
            ob_start();
            \QRcode::png($url, null, 'L', max(1, min(10, $size / 50)), $margin, false, 
                $this->hex_to_rgb($fg_color), 
                $this->hex_to_rgb($bg_color)
            );
            $image_data = ob_get_contents();
            ob_end_clean();
            
            // Convert to base64
            return 'data:image/png;base64,' . base64_encode($image_data);
        }
        
        // Fallback: Use Google Charts API (deprecated but still works)
        $chart_url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size;
        $chart_url .= '&cht=qr&chl=' . urlencode($url);
        $chart_url .= '&chco=' . $fg_color . ',' . $bg_color;
        
        return $chart_url;
    }
    
    /**
     * Convert hex color to RGB array.
     *
     * @param string $hex Hex color code.
     * @return array RGB values.
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return array(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }
    
    /**
     * AJAX handler for QR generation.
     */
    public function ajax_generate_qr() {
        check_ajax_referer('aa_qr_nonce', 'nonce');
        
        $affiliate_id = isset($_GET['affiliate_id']) ? absint($_GET['affiliate_id']) : 0;
        
        if (!$affiliate_id) {
            wp_send_json_error(array('message' => __('Invalid affiliate ID', 'affiliate-assets')));
        }
        
        $qr_data = $this->generate($affiliate_id);
        
        if ($qr_data) {
            wp_send_json_success(array(
                'qr_code' => $qr_data,
                'download_url' => admin_url('admin-ajax.php?action=aa_download_qr&affiliate_id=' . $affiliate_id . '&_wpnonce=' . wp_create_nonce('aa_qr_download')),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to generate QR code', 'affiliate-assets')));
        }
    }
    
    /**
     * AJAX handler for QR download.
     */
    public function ajax_download_qr() {
        check_ajax_referer('aa_qr_download', '_wpnonce');
        
        $affiliate_id = isset($_GET['affiliate_id']) ? absint($_GET['affiliate_id']) : 0;
        $format = isset($_GET['format']) ? sanitize_key($_GET['format']) : 'png';
        
        if (!$affiliate_id) {
            wp_die(__('Invalid affiliate ID', 'affiliate-assets'));
        }
        
        $affiliate = new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
        
        if (!$affiliate->get_id()) {
            wp_die(__('Affiliate not found', 'affiliate-assets'));
        }
        
        $url = $affiliate->get_referral_url();
        
        // Generate and output
        header('Content-Type: image/' . $format);
        header('Content-Disposition: attachment; filename="qr-' . sanitize_title($affiliate->get('referral_code')) . '.' . $format);
        
        $this->output_qr_image($url, $format);
        exit;
    }
    
    /**
     * Output QR image directly.
     *
     * @param string $url    URL to encode.
     * @param string $format Image format.
     */
    private function output_qr_image($url, $format = 'png') {
        $size = isset($this->settings['aa_qr_size']) ? absint($this->settings['aa_qr_size']) : 300;
        $fg_color = isset($this->settings['aa_qr_fg_color']) ? $this->settings['aa_qr_fg_color'] : '000000';
        $bg_color = isset($this->settings['aa_qr_bg_color']) ? $this->settings['aa_qr_bg_color'] : 'FFFFFF';
        $margin = isset($this->settings['aa_qr_margin']) ? absint($this->settings['aa_qr_margin']) : 2;
        
        if (file_exists(AA_PLUGIN_DIR . 'qr/libs/phpqrcode/phpqrcode.php')) {
            require_once AA_PLUGIN_DIR . 'qr/libs/phpqrcode/phpqrcode.php';
            
            \QRcode::png($url, null, 'L', max(1, min(10, $size / 50)), $margin, false,
                $this->hex_to_rgb($fg_color),
                $this->hex_to_rgb($bg_color)
            );
        } else {
            // Fallback: redirect to Google Charts
            $chart_url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size;
            $chart_url .= '&cht=qr&chl=' . urlencode($url);
            $chart_url .= '&chco=' . $fg_color . ',' . $bg_color;
            
            wp_redirect($chart_url);
            exit;
        }
    }
}
