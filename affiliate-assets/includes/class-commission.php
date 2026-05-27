<?php
/**
 * Commission entity class.
 * Manages commission data (placeholder for Phase 2).
 *
 * @package AffiliateAssets\Includes
 */

namespace AffiliateAssets\Includes;

use AffiliateAssets\Includes\Abstracts\Class_Object;

class Class_Commission extends Class_Object {
    
    /**
     * Table name.
     *
     * @var string
     */
    protected $table_name = 'aa_commissions';
    
    /**
     * Read commission from database.
     */
    public function read() {
        global $wpdb;
        
        $table = $wpdb->prefix . $this->table_name;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $commission = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $this->id));
        
        if ($commission) {
            $this->data = get_object_vars($commission);
        }
    }
    
    /**
     * Save commission to database.
     *
     * @return bool|int
     */
    public function save() {
        global $wpdb;
        
        $table = $wpdb->prefix . $this->table_name;
        
        $data = array(
            'affiliate_id'      => $this->get('affiliate_id'),
            'order_id'          => $this->get('order_id'),
            'amount'            => floatval($this->get('amount', 0)),
            'commission_rate'   => floatval($this->get('commission_rate', 0)),
            'commission_amount' => floatval($this->get('commission_amount', 0)),
            'status'            => $this->get('status', 'pending'),
            'reference_type'    => $this->get('reference_type', 'order'),
            'reference_id'      => $this->get('reference_id'),
            'notes'             => $this->get('notes'),
        );
        
        if ($this->id > 0) {
            // Update existing
            $result = $wpdb->update($table, $data, array('id' => $this->id));
            return $result !== false;
        } else {
            // Insert new
            $data['created_date'] = current_time('mysql');
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
     * Delete commission from database.
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
     * Get commissions by affiliate ID.
     *
     * @param int   $affiliate_id Affiliate ID.
     * @param array $args         Query arguments.
     * @return array
     */
    public static function get_by_affiliate($affiliate_id, $args = array()) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_commissions';
        
        $defaults = array(
            'limit' => 100,
            'status' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = "affiliate_id = %d";
        $values = array($affiliate_id);
        
        if (!empty($args['status'])) {
            $where .= " AND status = %s";
            $values[] = $args['status'];
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE $where ORDER BY created_date DESC LIMIT %d",
            $values[0],
            $values[1] ?? $args['status'],
            $args['limit']
        ));
    }
    
    /**
     * Get total commissions by affiliate.
     *
     * @param int    $affiliate_id Affiliate ID.
     * @param string $status       Optional status filter.
     * @return float
     */
    public static function get_total_by_affiliate($affiliate_id, $status = '') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aa_commissions';
        
        if (!empty($status)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            return (float) $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(commission_amount) FROM $table WHERE affiliate_id = %d AND status = %s",
                $affiliate_id,
                $status
            ));
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(commission_amount) FROM $table WHERE affiliate_id = %d",
            $affiliate_id
        ));
    }
}
