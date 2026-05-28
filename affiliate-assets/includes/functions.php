<?php
/**
 * Global helper functions.
 *
 * @package AffiliateAssets\Includes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get visitor IP address.
 *
 * @return string
 */
function aa_get_visitor_ip() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
    } else {
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    }
    
    // Handle multiple IPs in X-Forwarded-For
    if (strpos($ip, ',') !== false) {
        $ips = explode(',', $ip);
        $ip = trim($ips[0]);
    }
    
    return $ip;
}

/**
 * Get current affiliate ID from cookie.
 *
 * @return int|null
 */
function aa_get_current_affiliate_id() {
    if (!isset($_COOKIE['aa_referral'])) {
        return null;
    }
    
    return absint($_COOKIE['aa_referral']);
}

/**
 * Get current affiliate object.
 *
 * @return \AffiliateAssets\Includes\Class_Affiliate|null
 */
function aa_get_current_affiliate() {
    $affiliate_id = aa_get_current_affiliate_id();
    
    if (!$affiliate_id) {
        return null;
    }
    
    return new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
}

/**
 * Check if current user is an affiliate.
 *
 * @param int $user_id Optional user ID. Defaults to current user.
 * @return bool
 */
function aa_is_affiliate($user_id = 0) {
    if ($user_id === 0) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $affiliate = \AffiliateAssets\Includes\Class_Affiliate::get_by_user_id($user_id);
    return $affiliate !== null;
}

/**
 * Get affiliate object by user ID.
 *
 * @param int $user_id User ID.
 * @return \AffiliateAssets\Includes\Class_Affiliate|null
 */
function aa_get_affiliate($user_id = 0) {
    if ($user_id === 0) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return null;
    }
    
    return \AffiliateAssets\Includes\Class_Affiliate::get_by_user_id($user_id);
}

/**
 * Create affiliate for user.
 *
 * @param int $user_id User ID.
 * @return int|false Affiliate ID on success.
 */
function aa_create_affiliate($user_id) {
    // Check if already exists
    $existing = \AffiliateAssets\Includes\Class_Affiliate::get_by_user_id($user_id);
    if ($existing) {
        return $existing->get_id();
    }
    
    $affiliate = new \AffiliateAssets\Includes\Class_Affiliate();
    
    $referral_code = \AffiliateAssets\Includes\Class_Affiliate::generate_referral_code($user_id);
    
    $affiliate->set('user_id', $user_id);
    $affiliate->set('referral_code', $referral_code);
    $affiliate->set('status', 'pending');
    
    $settings = aa_get_settings();
    if (isset($settings['aa_auto_approve_affiliates']) && $settings['aa_auto_approve_affiliates']) {
        $affiliate->set('status', 'active');
    }
    
    $affiliate_id = $affiliate->save();
    
    if ($affiliate_id) {
        // Send welcome email
        aa_send_welcome_email($affiliate);
        
        // Trigger action
        do_action('aa_affiliate_created', $affiliate_id, $user_id);
    }
    
    return $affiliate_id;
}

/**
 * Send welcome email to new affiliate.
 *
 * @param \AffiliateAssets\Includes\Class_Affiliate $affiliate Affiliate object.
 */
function aa_send_welcome_email($affiliate) {
    $settings = aa_get_settings();
    $user = get_userdata($affiliate->get('user_id'));
    
    if (!$user) {
        return;
    }
    
    $subject = isset($settings['aa_email_welcome_subject']) ? $settings['aa_email_welcome_subject'] : __('¡Bienvenido al programa de afiliados!', 'affiliate-assets');
    $body = isset($settings['aa_email_welcome_body']) ? $settings['aa_email_welcome_body'] : '';
    
    // Replace placeholders
    $subject = str_replace(
        array('{{site_name}}'),
        array(get_bloginfo('name')),
        $subject
    );
    
    $body = str_replace(
        array('{{affiliate_name}}', '{{referral_link}}', '{{qr_code_url}}', '{{site_name}}'),
        array(
            $user->display_name,
            $affiliate->get_referral_url(),
            admin_url('admin-ajax.php?action=aa_generate_qr&affiliate_id=' . $affiliate->get_id()),
            get_bloginfo('name')
        ),
        $body
    );
    
    wp_mail($user->user_email, $subject, nl2br($body));
}

/**
 * Send approval email to affiliate.
 *
 * @param \AffiliateAssets\Includes\Class_Affiliate $affiliate Affiliate object.
 */
function aa_send_approval_email($affiliate) {
    $settings = aa_get_settings();
    $user = get_userdata($affiliate->get('user_id'));
    
    if (!$user) {
        return;
    }
    
    $subject = isset($settings['aa_email_approval_subject']) ? $settings['aa_email_approval_subject'] : __('Tu afiliación ha sido aprobada', 'affiliate-assets');
    $body = isset($settings['aa_email_approval_body']) ? $settings['aa_email_approval_body'] : '';
    
    // Replace placeholders
    $subject = str_replace(
        array('{{site_name}}'),
        array(get_bloginfo('name')),
        $subject
    );
    
    $body = str_replace(
        array('{{affiliate_name}}', '{{referral_link}}', '{{qr_code_url}}', '{{site_name}}'),
        array(
            $user->display_name,
            $affiliate->get_referral_url(),
            admin_url('admin-ajax.php?action=aa_generate_qr&affiliate_id=' . $affiliate->get_id()),
            get_bloginfo('name')
        ),
        $body
    );
    
    wp_mail($user->user_email, $subject, nl2br($body));
}

/**
 * Send rejection email to affiliate.
 *
 * @param \AffiliateAssets\Includes\Class_Affiliate $affiliate Affiliate object.
 */
function aa_send_rejection_email($affiliate) {
    $settings = aa_get_settings();
    $user = get_userdata($affiliate->get('user_id'));
    
    if (!$user) {
        return;
    }
    
    $subject = isset($settings['aa_email_rejection_subject']) ? $settings['aa_email_rejection_subject'] : __('Tu solicitud de afiliación ha sido rechazada', 'affiliate-assets');
    $body = isset($settings['aa_email_rejection_body']) ? $settings['aa_email_rejection_body'] : '';
    
    // Replace placeholders
    $subject = str_replace(
        array('{{site_name}}'),
        array(get_bloginfo('name')),
        $subject
    );
    
    $body = str_replace(
        array('{{affiliate_name}}', '{{site_name}}'),
        array(
            $user->display_name,
            get_bloginfo('name')
        ),
        $body
    );
    
    wp_mail($user->user_email, $subject, nl2br($body));
}

/**
 * Parse referral code from URL.
 *
 * @param string $url URL to parse.
 * @return string|null
 */
function aa_parse_referral_code($url) {
    $settings = aa_get_settings();
    
    // Check for pretty URLs
    if (isset($settings['aa_enable_pretty_urls']) && $settings['aa_enable_pretty_urls']) {
        $slug = isset($settings['aa_referral_slug']) ? $settings['aa_referral_slug'] : 'referido';
        $pattern = '#' . preg_quote($slug, '#') . '/([^/]+)/?#';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    // Check for query parameter
    $parsed = parse_url($url);
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $params);
        if (isset($params['aa_ref'])) {
            return sanitize_text_field($params['aa_ref']);
        }
    }
    
    return null;
}

/**
 * Get commission rate.
 *
 * @param int $product_id Optional product ID.
 * @return float
 */
function aa_get_commission_rate($product_id = 0) {
    $settings = aa_get_settings();
    
    // For now, return default rate
    // In Phase 2, we'll check product-specific rates
    return floatval(isset($settings['aa_default_commission_rate']) ? $settings['aa_default_commission_rate'] : 10);
}

/**
 * Get affiliate by user ID.
 *
 * @param int $user_id User ID.
 * @return \AffiliateAssets\Includes\Class_Affiliate|null
 */
function aa_get_affiliate_by_user_id($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'aa_affiliates';
    
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $affiliate_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE user_id = %d", $user_id));
    
    if (!$affiliate_id) {
        return null;
    }
    
    return new \AffiliateAssets\Includes\Class_Affiliate($affiliate_id);
}

/**
 * Count visits by affiliate ID.
 *
 * @param int $affiliate_id Affiliate ID.
 * @return int
 */
function aa_count_visits_by_affiliate($affiliate_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'aa_visits';
    
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE affiliate_id = %d", $affiliate_id));
}

/**
 * Count conversions by affiliate ID.
 *
 * @param int $affiliate_id Affiliate ID.
 * @return int
 */
function aa_count_conversions_by_affiliate($affiliate_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'aa_visits';
    
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE affiliate_id = %d AND is_converted = 1", $affiliate_id));
}

/**
 * Get QR code URL for display.
 *
 * @param string $referral_url Referral URL.
 * @return string
 */
function aa_get_qr_code_url($referral_url) {
    return add_query_arg(array(
        'aa_action' => 'qr_code',
        'url' => base64_encode($referral_url),
    ), admin_url('admin-ajax.php'));
}

/**
 * Get QR code download URL.
 *
 * @param string $referral_url Referral URL.
 * @param string $format Format (png or svg).
 * @return string
 */
function aa_get_qr_code_download_url($referral_url, $format = 'png') {
    return add_query_arg(array(
        'aa_action' => 'download_qr',
        'url' => base64_encode($referral_url),
        'format' => $format,
    ), admin_url('admin-ajax.php'));
}
