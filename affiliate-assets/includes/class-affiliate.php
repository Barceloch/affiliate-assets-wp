<?php
/**
 * Affiliate entity class.
 * Manages affiliate data and operations.
 *
 * @package AffiliateAssets\Includes
 */

namespace AffiliateAssets\Includes;

use AffiliateAssets\Includes\Abstracts\Class_Object;

class Class_Affiliate extends Class_Object {
    
    /**
     * Database wrapper.
     *
     * @var Class_Database
     */
    protected $db;
    
    /**
     * Table name.
     *
     * @var string
     */
    protected $table_name = 'aa_affiliates';
    
    /**
     * Constructor.
     *
     * @param int|object $data Object ID or data object.
     */
    public function __construct($data = 0) {
        parent::__construct($data);
        $this->db = new Class_Database();
        $this->db->table_name = $this->table_name;
    }
    
    /**
     * Read affiliate from database.
     */
    public function read() {
        global $wpdb;
        
        $table = $wpdb->prefix . $this->table_name;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $this->id));
        
        if ($affiliate) {
            $this->data = get_object_vars($affiliate);
        }
    }
    
    /**
     * Save affiliate to database.
     *
     * @return bool|int
     */
    public function save() {
        global $wpdb;
        
        $table = $wpdb->prefix . $this->table_name;
        
        $data = array(
            'user_id'             => $this->get('user_id'),
            'status'              => $this->get('status', 'pending'),
            'referral_code'       => $this->get('referral_code'),
            'referral_url'        => $this->get('referral_url'),
            'payment_email'       => $this->get('payment_email'),
            'website_url'         => $this->get('website_url'),
            'promotional_methods' => maybe_serialize($this->get('promotional_methods')),
            'notes'               => $this->get('notes'),
        );
        
        if ($this->id > 0) {
            // Update existing
            $result = $wpdb->update($table, $data, array('id' => $this->id));
            return $result !== false;
        } else {
            // Insert new
            $data['registration_date'] = current_time('mysql');
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
     * Delete affiliate from database.
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
     * Get affiliate by user ID.
     *
     * @param int $user_id WordPress user ID.
     * @return Class_Affiliate|null
     */
    public static function get_by_user_id($user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_affiliates';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id));
        
        if ($affiliate) {
            return new self($affiliate->id);
        }
        
        return null;
    }
    
    /**
     * Get affiliate by referral code.
     *
     * @param string $code Referral code.
     * @return Class_Affiliate|null
     */
    public static function get_by_referral_code($code) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_affiliates';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $affiliate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE referral_code = %s", $code));
        
        if ($affiliate) {
            return new self($affiliate->id);
        }
        
        return null;
    }
    
    /**
     * Generate unique referral code.
     *
     * @param int $user_id User ID.
     * @return string
     */
    public static function generate_referral_code($user_id) {
        $user = get_userdata($user_id);
        $base = sanitize_title($user->user_login);
        $code = $base;
        $counter = 1;
        
        global $wpdb;
        $table = $wpdb->prefix . 'aa_affiliates';
        
        while (true) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE referral_code = %s", $code));
            
            if (!$exists) {
                break;
            }
            
            $code = $base . '-' . $counter;
            $counter++;
        }
        
        return $code;
    }
    
    /**
     * Get full referral URL.
     *
     * @return string
     */
    public function get_referral_url() {
        $settings = aa_get_settings();
        $pretty_urls = isset($settings['aa_enable_pretty_urls']) && $settings['aa_enable_pretty_urls'];
        
        if ($pretty_urls) {
            $slug = isset($settings['aa_referral_slug']) ? $settings['aa_referral_slug'] : 'referido';
            return home_url("/{$slug}/{$this->get('referral_code')}/");
        }
        
        return add_query_arg('aa_ref', $this->get('referral_code'), home_url());
    }
    
    /**
     * Approve affiliate.
     *
     * @return bool
     */
    public function approve() {
        $this->set('status', 'active');
        $this->set('approved_date', current_time('mysql'));
        return $this->save();
    }
    
    /**
     * Reject affiliate.
     *
     * @return bool
     */
    public function reject() {
        $this->set('status', 'rejected');
        return $this->save();
    }
    
    /**
     * Deactivate affiliate.
     *
     * @return bool
     */
    public function deactivate() {
        $this->set('status', 'inactive');
        return $this->save();
    }
    
    /**
     * Check if affiliate is active.
     *
     * @return bool
     */
    public function is_active() {
        return $this->get('status') === 'active';
    }
}
