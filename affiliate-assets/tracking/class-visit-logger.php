<?php
/**
 * Visit logger class.
 * Handles visit logging and management.
 *
 * @package AffiliateAssets\Tracking
 */

namespace AffiliateAssets\Tracking;

use AffiliateAssets\Core\Class_Loader;

class Class_Visit_Logger {
    
    /**
     * Loader instance.
     *
     * @var Class_Loader
     */
    protected $loader;
    
    /**
     * Constructor.
     *
     * @param Class_Loader $loader Loader instance.
     */
    public function __construct($loader) {
        $this->loader = $loader;
    }
    
    /**
     * Run logger initialization.
     */
    public function run() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action('wp_ajax_aa_log_qr_visit', array($this, 'ajax_log_qr_visit'));
        add_action('wp_ajax_nopriv_aa_log_qr_visit', array($this, 'ajax_log_qr_visit'));
    }
    
    /**
     * Log a QR scan visit via AJAX.
     */
    public function ajax_log_qr_visit() {
        check_ajax_referer('aa_qr_nonce', 'nonce');
        
        $affiliate_id = isset($_POST['affiliate_id']) ? absint($_POST['affiliate_id']) : 0;
        
        if (!$affiliate_id) {
            wp_send_json_error(array('message' => __('Invalid affiliate ID', 'affiliate-assets')));
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : home_url();
        
        $visit_id = \AffiliateAssets\Includes\Class_Visit::log(
            $affiliate_id,
            $url,
            'qr_scan',
            'qr_code'
        );
        
        if ($visit_id) {
            wp_send_json_success(array(
                'visit_id' => $visit_id,
                'message' => __('Visit logged successfully', 'affiliate-assets'),
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to log visit', 'affiliate-assets')));
        }
    }
    
    /**
     * Log a visit programmatically.
     *
     * @param int    $affiliate_id Affiliate ID.
     * @param string $url          URL visited.
     * @param string $type         Visit type.
     * @param string $source       Referral source.
     * @return int|false
     */
    public function log_visit($affiliate_id, $url, $type = 'referral', $source = '') {
        return \AffiliateAssets\Includes\Class_Visit::log($affiliate_id, $url, $type, $source);
    }
    
    /**
     * Get visits for an affiliate.
     *
     * @param int   $affiliate_id Affiliate ID.
     * @param array $args         Query arguments.
     * @return array
     */
    public function get_visits($affiliate_id, $args = array()) {
        return \AffiliateAssets\Includes\Class_Visit::get_by_affiliate($affiliate_id, $args);
    }
    
    /**
     * Count visits for an affiliate.
     *
     * @param int $affiliate_id Affiliate ID.
     * @return int
     */
    public function count_visits($affiliate_id) {
        return \AffiliateAssets\Includes\Class_Visit::count_by_affiliate($affiliate_id);
    }
    
    /**
     * Mark a visit as converted.
     *
     * @param int $visit_id  Visit ID.
     * @param int $reference Reference ID (e.g., order ID).
     * @return bool
     */
    public function mark_converted($visit_id, $reference = 0) {
        $visit = new \AffiliateAssets\Includes\Class_Visit($visit_id);
        
        if (!$visit->get_id()) {
            return false;
        }
        
        return $visit->mark_converted($reference);
    }
    
    /**
     * Get recent visits.
     *
     * @param int $limit Number of visits to retrieve.
     * @return array
     */
    public function get_recent_visits($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_visits';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table ORDER BY date DESC LIMIT %d",
            $limit
        ));
    }
}
