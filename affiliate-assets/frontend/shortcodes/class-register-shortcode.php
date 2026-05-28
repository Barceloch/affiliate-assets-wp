<?php
/**
 * Register Shortcode Class
 * 
 * Renders the affiliate registration form shortcode.
 *
 * @package AffiliateAssets
 * @since 1.0.0
 */

namespace AffiliateAssets\Frontend\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Class_Register_Shortcode
 */
class Class_Register_Shortcode {

    /**
     * Initialize shortcode.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode( 'aa_affiliate_register', array( $this, 'render' ) );
    }

    /**
     * Render the registration form shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     * @since 1.0.0
     */
    public function render( $atts = array() ) {
        // Check if user is already logged in
        if ( is_user_logged_in() ) {
            return '<div class="aa-notice aa-notice-info">' . esc_html__( 'You are already registered as an affiliate.', 'affiliate-assets' ) . '</div>';
        }

        // Check if user is already an affiliate
        $current_user_id = get_current_user_id();
        if ( $current_user_id && aa_get_affiliate_by_user_id( $current_user_id ) ) {
            return '<div class="aa-notice aa-notice-info">' . esc_html__( 'You are already registered as an affiliate.', 'affiliate-assets' ) . '</div>';
        }

        ob_start();
        ?>
        <div class="aa-register-form">
            <p><?php esc_html_e( 'To become an affiliate, you need to purchase a membership first.', 'affiliate-assets' ); ?></p>
            <a href="<?php echo esc_url( get_permalink( aa_get_membership_product_id() ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'Purchase Membership', 'affiliate-assets' ); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
}
