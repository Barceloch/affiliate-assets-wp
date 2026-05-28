<?php
/**
 * Dashboard Shortcode Class
 * 
 * Renders the affiliate dashboard shortcode.
 *
 * @package AffiliateAssets
 * @since 1.0.0
 */

namespace AffiliateAssets\Frontend\Shortcodes;

use AffiliateAssets\Core\Class_Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Class_Dashboard_Shortcode
 */
class Class_Dashboard_Shortcode {

    /**
     * Initialize shortcode.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode( 'aa_affiliate_dashboard', array( $this, 'render' ) );
    }

    /**
     * Render the dashboard shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     * @since 1.0.0
     */
    public function render( $atts = array() ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<div class="aa-notice aa-notice-error">' . esc_html__( 'Please log in to view your affiliate dashboard.', 'affiliate-assets' ) . '</div>';
        }

        $current_user_id = get_current_user_id();
        $affiliate       = aa_get_affiliate_by_user_id( $current_user_id );

        // Check if user is an affiliate
        if ( ! $affiliate ) {
            return '<div class="aa-notice aa-notice-info">' . esc_html__( 'You are not registered as an affiliate yet.', 'affiliate-assets' ) . '</div>';
        }

        // Check affiliate status
        if ( 'active' !== $affiliate->get_status() ) {
            $status_message = '';
            switch ( $affiliate->get_status() ) {
                case 'pending':
                    $status_message = __( 'Your affiliate application is pending approval.', 'affiliate-assets' );
                    break;
                case 'rejected':
                    $status_message = __( 'Your affiliate application has been rejected.', 'affiliate-assets' );
                    break;
                case 'inactive':
                    $status_message = __( 'Your affiliate account is inactive.', 'affiliate-assets' );
                    break;
            }
            return '<div class="aa-notice aa-notice-warning">' . esc_html( $status_message ) . '</div>';
        }

        // Get stats
        $stats = $this->get_affiliate_stats( $affiliate->get_id() );

        // Load template
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/affiliate-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Get affiliate statistics.
     *
     * @param int $affiliate_id Affiliate ID.
     * @return array
     * @since 1.0.0
     */
    private function get_affiliate_stats( $affiliate_id ) {
        global $wpdb;
        $table_visits = $wpdb->prefix . 'aa_visits';

        $stats = array(
            'total_visits'      => 0,
            'converted_visits'  => 0,
            'conversion_rate'   => 0,
            'total_commissions' => 0,
        );

        // Total visits
        $stats['total_visits'] = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_visits} WHERE affiliate_id = %d",
                $affiliate_id
            )
        );

        // Converted visits
        $stats['converted_visits'] = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_visits} WHERE affiliate_id = %d AND is_converted = 1",
                $affiliate_id
            )
        );

        // Conversion rate
        if ( $stats['total_visits'] > 0 ) {
            $stats['conversion_rate'] = round( ( $stats['converted_visits'] / $stats['total_visits'] ) * 100, 2 );
        }

        // Total commissions (placeholder for Phase 2)
        $stats['total_commissions'] = 0;

        return $stats;
    }
}
