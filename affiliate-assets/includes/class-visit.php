<?php
/**
 * Visit entity class.
 * Manages visit tracking data.
 *
 * @package AffiliateAssets\Includes
 */

namespace AffiliateAssets\Includes;

use AffiliateAssets\Includes\Abstracts\Class_Object;

class Class_Visit extends Class_Object {
    
    /**
     * Table name.
     *
     * @var string
     */
    protected $table_name = 'aa_visits';
    
    /**
     * Read visit from database.
     */
    public function read() {
        global $wpdb;
        
        $table = $wpdb->prefix . $this->table_name;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $visit = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $this->id));
        
        if ($visit) {
            $this->data = get_object_vars($visit);
        }
    }
    
    /**
     * Save visit to database.
     *
     * @return bool|int
     */
    public function save() {
        global $wpdb;
        
        $table = $wpdb->prefix . $this->table_name;
        
        $data = array(
            'affiliate_id'      => $this->get('affiliate_id'),
            'url'               => $this->get('url'),
            'ip'                => $this->get('ip'),
            'user_agent'        => $this->get('user_agent'),
            'referral_source'   => $this->get('referral_source'),
            'type'              => $this->get('type', 'direct'),
            'is_converted'      => $this->get('is_converted', 0),
            'reference'         => $this->get('reference'),
        );
        
        if ($this->id > 0) {
            // Update existing
            $result = $wpdb->update($table, $data, array('id' => $this->id));
            return $result !== false;
        } else {
            // Insert new
            $data['date'] = current_time('mysql');
            $result = $wpdb->insert($table, $data);
            
            if ($result) {
                $this->id = $wpdb->insert_id;
                $this->read();
                return $this->id;
            }
            return false;
        }
    }
    
    /**
     * Delete visit from database.
     *
     * @return bool
     */
    public function delete() {
        if ($this->id <= 0) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . $this->table_name;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE id = %d", $this->id));
    }
    
    /**
     * Log a new visit.
     *
     * @param int    $affiliate_id Affiliate ID.
     * @param string $url          Visited URL.
     * @param string $type         Visit type (direct, referral, qr_scan).
     * @param string $source       Referral source.
     * @return int|false Visit ID on success.
     */
    public static function log($affiliate_id, $url, $type = 'referral', $source = '') {
        $visit = new self();
        
        $visit->set('affiliate_id', absint($affiliate_id));
        $visit->set('url', esc_url_raw($url));
        $visit->set('ip', aa_get_visitor_ip());
        $visit->set('user_agent', isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '');
        $visit->set('referral_source', sanitize_text_field($source));
        $visit->set('type', sanitize_key($type));
        
        return $visit->save();
    }
    
    /**
     * Mark visit as converted.
     *
     * @param int $reference Reference ID (e.g., order ID).
     * @return bool
     */
    public function mark_converted($reference = 0) {
        $this->set('is_converted', 1);
        if ($reference > 0) {
            $this->set('reference', absint($reference));
        }
        return $this->save();
    }
    
    /**
     * Get visits by affiliate ID.
     *
     * @param int   $affiliate_id Affiliate ID.
     * @param array $args         Query arguments.
     * @return array
     */
    public static function get_by_affiliate($affiliate_id, $args = array()) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_visits';
        
        $defaults = array(
            'limit' => 100,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE affiliate_id = %d ORDER BY date DESC LIMIT %d",
            $affiliate_id,
            $args['limit']
        ));
    }
    
    /**
     * Count visits by affiliate.
     *
     * @param int $affiliate_id Affiliate ID.
     * @return int
     */
    public static function count_by_affiliate($affiliate_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_visits';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE affiliate_id = %d",
            $affiliate_id
        ));
    }
    
    /**
     * Count converted visits by affiliate.
     *
     * @param int $affiliate_id Affiliate ID.
     * @return int
     */
    public static function count_converted_by_affiliate($affiliate_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_visits';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE affiliate_id = %d AND is_converted = 1",
            $affiliate_id
        ));
    }
}
