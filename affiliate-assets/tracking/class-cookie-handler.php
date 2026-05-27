<?php
/**
 * Cookie handler class.
 * Manages cookie operations for referral tracking.
 *
 * @package AffiliateAssets\Tracking
 */

namespace AffiliateAssets\Tracking;

class Class_Cookie_Handler {
    
    /**
     * Cookie name prefix.
     *
     * @var string
     */
    const COOKIE_PREFIX = 'aa_';
    
    /**
     * Set a cookie.
     *
     * @param string $name   Cookie name.
     * @param mixed  $value  Cookie value.
     * @param int    $expire Expiration timestamp.
     * @return bool
     */
    public function set($name, $value, $expire = 0) {
        if ($expire === 0) {
            $expire = time() + (30 * DAY_IN_SECONDS); // Default 30 days
        }
        
        return setcookie(
            self::COOKIE_PREFIX . $name,
            is_array($value) ? json_encode($value) : $value,
            $expire,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // httponly
        );
    }
    
    /**
     * Get a cookie value.
     *
     * @param string $name    Cookie name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get($name, $default = '') {
        $cookie_name = self::COOKIE_PREFIX . $name;
        
        if (!isset($_COOKIE[$cookie_name])) {
            return $default;
        }
        
        $value = $_COOKIE[$cookie_name];
        $decoded = json_decode($value, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        
        return $value;
    }
    
    /**
     * Delete a cookie.
     *
     * @param string $name Cookie name.
     * @return bool
     */
    public function delete($name) {
        return setcookie(
            self::COOKIE_PREFIX . $name,
            '',
            time() - 3600,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }
    
    /**
     * Check if a cookie exists.
     *
     * @param string $name Cookie name.
     * @return bool
     */
    public function exists($name) {
        return isset($_COOKIE[self::COOKIE_PREFIX . $name]);
    }
    
    /**
     * Get all plugin cookies.
     *
     * @return array
     */
    public function get_all() {
        $cookies = array();
        
        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, self::COOKIE_PREFIX) === 0) {
                $cookies[str_replace(self::COOKIE_PREFIX, '', $name)] = $value;
            }
        }
        
        return $cookies;
    }
}
