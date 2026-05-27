<?php
/**
 * Abstract base class for all objects.
 * Inspired by SliceWP's object architecture.
 *
 * @package AffiliateAssets\Includes\Abstracts
 */

namespace AffiliateAssets\Includes\Abstracts;

abstract class Class_Object {
    
    /**
     * Object ID.
     *
     * @var int
     */
    protected $id = 0;
    
    /**
     * Object data.
     *
     * @var array
     */
    protected $data = array();
    
    /**
     * Constructor.
     *
     * @param int|object $data Object ID or data object.
     */
    public function __construct($data = 0) {
        if ($data instanceof \WP_Post || is_object($data)) {
            $this->id = absint($data->id ?? $data->ID ?? 0);
        } elseif (is_numeric($data)) {
            $this->id = absint($data);
        }
        
        if ($this->id > 0) {
            $this->read();
        }
    }
    
    /**
     * Read object from database.
     */
    abstract public function read();
    
    /**
     * Save object to database.
     *
     * @return bool
     */
    abstract public function save();
    
    /**
     * Delete object from database.
     *
     * @return bool
     */
    abstract public function delete();
    
    /**
     * Get object ID.
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Get property.
     *
     * @param string $key     Property key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get($key, $default = '') {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * Set property.
     *
     * @param string $key   Property key.
     * @param mixed  $value Property value.
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }
    
    /**
     * Get all data.
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
    
    /**
     * Set all data.
     *
     * @param array $data Data to set.
     */
    public function set_data($data) {
        $this->data = $data;
    }
}
