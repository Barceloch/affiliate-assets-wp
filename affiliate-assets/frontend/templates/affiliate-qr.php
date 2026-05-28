<?php
/**
 * Affiliate QR Template
 * 
 * Displays affiliate QR code for download.
 *
 * @package AffiliateAssets
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$affiliate = aa_get_affiliate_by_user_id( get_current_user_id() );
if ( ! $affiliate ) {
    return;
}

$referral_code = $affiliate->get_referral_code();
$referral_url  = aa_get_referral_url( $referral_code );
$qr_code_url   = aa_get_qr_code_url( $referral_url );
?>

<div class="aa-qr-template">
    <h2><?php esc_html_e( 'Your QR Code', 'affiliate-assets' ); ?></h2>
    
    <div class="aa-qr-display">
        <img src="<?php echo esc_url( $qr_code_url ); ?>" alt="<?php esc_attr_e( 'Referral QR Code', 'affiliate-assets' ); ?>" class="aa-qr-image-large" />
    </div>
    
    <div class="aa-qr-download-buttons">
        <a href="<?php echo esc_url( aa_get_qr_code_download_url( $referral_url, 'png' ) ); ?>" class="button button-primary" download>
            <?php esc_html_e( 'Download PNG', 'affiliate-assets' ); ?>
        </a>
        <a href="<?php echo esc_url( aa_get_qr_code_download_url( $referral_url, 'svg' ) ); ?>" class="button" download>
            <?php esc_html_e( 'Download SVG', 'affiliate-assets' ); ?>
        </a>
    </div>
    
    <p class="aa-qr-description">
        <?php esc_html_e( 'Share this QR code with your audience. When they scan it, they will be directed to your referral link.', 'affiliate-assets' ); ?>
    </p>
</div>
