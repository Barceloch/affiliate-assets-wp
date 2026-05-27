<?php
/**
 * Abstract database wrapper class.
 * Provides common database operations inspired by SliceWP.
 *
 * @package AffiliateAssets\Includes\Abstracts
 */

namespace AffiliateAssets\Includes\Abstracts;

abstract class Class_Database {
    
    /**
     * Table name.
     *
     * @var string
     */
    protected $table_name = '';
    
    /**
     * Primary key column.
     *
     * @var string
     */
    protected $primary_key = 'id';
    
    /**
     * Get full table name with prefix.
     *
     * @return string
     */
    public function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . $this->table_name;
    }
    
    /**
     * Get single record by ID.
     *
     * @param int $id Record ID.
     * @return object|null
     */
    public function get($id) {
        global $wpdb;
        
        $table = $this->get_table_name();
        $primary = $this->primary_key;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE $primary = %d", $id));
    }
    
    /**
     * Get all records.
     *
     * @param array $args Query arguments.
     * @return array
     */
    public function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit'   => 100,
            'offset'  => 0,
            'orderby' => $this->primary_key,
            'order'   => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table = $this->get_table_name();
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        ));
    }
    
    /**
     * Insert a new record.
     *
     * @param array $data Data to insert.
     * @return int|false Record ID on success, false on failure.
     */
    public function insert($data) {
        global $wpdb;
        
        $table = $this->get_table_name();
        
        $result = $wpdb->insert($table, $data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update a record.
     *
     * @param int   $id   Record ID.
     * @param array $data Data to update.
     * @return bool
     */
    public function update($id, $data) {
        global $wpdb;
        
        $table = $this->get_table_name();
        $primary = $this->primary_key;
        
        $result = $wpdb->update(
            $table,
            $data,
            array($primary => $id)
        );
        
        return $result !== false;
    }
    
    /**
     * Delete a record.
     *
     * @param int $id Record ID.
     * @return bool
     */
    public function delete($id) {
        global $wpdb;
        
        $table = $this->get_table_name();
        $primary = $this->primary_key;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE $primary = %d", $id));
    }
    
    /**
     * Count records.
     *
     * @param array $where Where clause conditions.
     * @return int
     */
    public function count($where = array()) {
        global $wpdb;
        
        $table = $this->get_table_name();
        
        $sql = "SELECT COUNT(*) FROM $table";
        
        if (!empty($where)) {
            $conditions = array();
            $values = array();
            
            foreach ($where as $column => $value) {
                $conditions[] = "$column = %s";
                $values[] = $value;
            }
            
            $sql .= " WHERE " . implode(' AND ', $conditions);
            
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            return (int) $wpdb->get_var($wpdb->prepare($sql, $values));
        }
        
        return (int) $wpdb->get_var($sql);
    }
    
    /**
     * Check if table exists.
     *
     * @return bool
     */
    public function table_exists() {
        global $wpdb;
        
        $table = $this->get_table_name();
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table;
    }
}
