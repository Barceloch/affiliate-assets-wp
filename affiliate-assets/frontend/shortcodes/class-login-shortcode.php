<?php
/**
 * Login Shortcode Class
 * 
 * Renders the affiliate login form shortcode.
 *
 * @package AffiliateAssets
 * @since 1.0.0
 */

namespace AffiliateAssets\Frontend\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Class_Login_Shortcode
 */
class Class_Login_Shortcode {

    /**
     * Initialize shortcode.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode( 'aa_affiliate_login', array( $this, 'render' ) );
    }

    /**
     * Render the login form shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     * @since 1.0.0
     */
    public function render( $atts = array() ) {
        // Check if user is already logged in
        if ( is_user_logged_in() ) {
            return '<div class="aa-notice aa-notice-info">' . esc_html__( 'You are already logged in.', 'affiliate-assets' ) . '</div>';
        }

        ob_start();
        ?>
        <div class="aa-login-form">
            <?php
            wp_login_form(
                array(
                    'echo'     => true,
                    'redirect' => '',
                    'form_id'  => 'aa-loginform',
                    'label_username' => __( 'Username or Email', 'affiliate-assets' ),
                    'label_password' => __( 'Password', 'affiliate-assets' ),
                    'label_remember' => __( 'Remember Me', 'affiliate-assets' ),
                    'label_log_in'   => __( 'Log In', 'affiliate-assets' ),
                )
            );
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
