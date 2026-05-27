<?php
/**
 * Referral tracker class.
 * Handles cookie-based referral tracking inspired by SliceWP.
 *
 * @package AffiliateAssets\Tracking
 */

namespace AffiliateAssets\Tracking;

use AffiliateAssets\Core\Class_Loader;

class Class_Referral_Tracker {
    
    /**
     * Loader instance.
     *
     * @var Class_Loader
     */
    protected $loader;
    
    /**
     * Cookie name.
     *
     * @var string
     */
    const COOKIE_NAME = 'aa_referral';
    
    /**
     * Constructor.
     *
     * @param Class_Loader $loader Loader instance.
     */
    public function __construct($loader) {
        $this->loader = $loader;
    }
    
    /**
     * Run tracker initialization.
     */
    public function run() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action('init', array($this, 'check_referral'), 1);
        add_action('wp', array($this, 'maybe_log_visit'));
    }
    
    /**
     * Check for referral parameter in URL.
     */
    public function check_referral() {
        // Check for query parameter
        if (isset($_GET['aa_ref'])) {
            $code = sanitize_text_field($_GET['aa_ref']);
            $this->set_referral_cookie($code);
        }
        
        // Check for pretty URLs
        $settings = \AffiliateAssets\Includes\aa_get_settings();
        if (isset($settings['aa_enable_pretty_urls']) && $settings['aa_enable_pretty_urls']) {
            $slug = isset($settings['aa_referral_slug']) ? $settings['aa_referral_slug'] : 'referido';
            
            if (is_singular() || is_home()) {
                $request_uri = $_SERVER['REQUEST_URI'] ?? '';
                $pattern = '#' . preg_quote($slug, '#') . '/([^/]+)/?#';
                
                if (preg_match($pattern, $request_uri, $matches)) {
                    $code = $matches[1];
                    $this->set_referral_cookie($code);
                    
                    // Redirect to remove the slug from URL
                    $clean_url = preg_replace($pattern, '', $request_uri);
                    if ($clean_url !== $request_uri) {
                        wp_safe_redirect(home_url($clean_url));
                        exit;
                    }
                }
            }
        }
    }
    
    /**
     * Set referral cookie.
     *
     * @param string $code Referral code.
     */
    public function set_referral_cookie($code) {
        $affiliate = \AffiliateAssets\Includes\Class_Affiliate::get_by_referral_code($code);
        
        if (!$affiliate || !$affiliate->get_id()) {
            return;
        }
        
        $settings = \AffiliateAssets\Includes\aa_get_settings();
        $duration = isset($settings['aa_cookie_duration']) ? absint($settings['aa_cookie_duration']) : 30;
        $expire = time() + (DAY_IN_SECONDS * $duration);
        
        // Store affiliate ID (not code) for performance
        setcookie(self::COOKIE_NAME, $affiliate->get_id(), $expire, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        
        // Also store in session for same-session tracking
        if (!session_id()) {
            @session_start();
        }
        $_SESSION['aa_referral'] = $affiliate->get_id();
    }
    
    /**
     * Maybe log a visit.
     */
    public function maybe_log_visit() {
        $affiliate_id = $this->get_referral_id();
        
        if (!$affiliate_id) {
            return;
        }
        
        $settings = \AffiliateAssets\Includes\aa_get_settings();
        
        // Check if we should only track first visit
        if (isset($settings['aa_track_first_visit_only']) && $settings['aa_track_first_visit_only']) {
            $has_visits = \AffiliateAssets\Includes\Class_Visit::count_by_affiliate($affiliate_id);
            if ($has_visits > 0) {
                return;
            }
        }
        
        // Log the visit
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        \AffiliateAssets\Includes\Class_Visit::log(
            $affiliate_id,
            $current_url,
            'referral',
            isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
        );
    }
    
    /**
     * Get referral ID from cookie or session.
     *
     * @return int|null
     */
    public function get_referral_id() {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            return absint($_COOKIE[self::COOKIE_NAME]);
        }
        
        if (isset($_SESSION['aa_referral'])) {
            return absint($_SESSION['aa_referral']);
        }
        
        return null;
    }
    
    /**
     * Clear referral cookie.
     */
    public function clear_referral_cookie() {
        setcookie(self::COOKIE_NAME, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        
        if (isset($_SESSION['aa_referral'])) {
            unset($_SESSION['aa_referral']);
        }
    }
}
