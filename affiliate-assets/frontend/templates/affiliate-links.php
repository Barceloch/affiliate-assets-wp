<?php
/**
 * Affiliate Links Template
 * 
 * Displays affiliate referral links and QR code.
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

<div class="aa-links-section">
    <h2><?php esc_html_e( 'Your Referral Links', 'affiliate-assets' ); ?></h2>
    
    <div class="aa-link-card">
        <label><?php esc_html_e( 'Main Referral Link', 'affiliate-assets' ); ?></label>
        <div class="aa-link-input-wrapper">
            <input type="text" class="aa-referral-link" value="<?php echo esc_attr( $referral_url ); ?>" readonly />
            <button type="button" class="button aa-copy-link" data-link="<?php echo esc_attr( $referral_url ); ?>">
                <?php esc_html_e( 'Copy', 'affiliate-assets' ); ?>
            </button>
        </div>
    </div>

    <div class="aa-qr-section">
        <h3><?php esc_html_e( 'QR Code', 'affiliate-assets' ); ?></h3>
        <img src="<?php echo esc_url( $qr_code_url ); ?>" alt="<?php esc_attr_e( 'Referral QR Code', 'affiliate-assets' ); ?>" class="aa-qr-image" />
        <div class="aa-qr-download">
            <a href="<?php echo esc_url( aa_get_qr_code_download_url( $referral_url, 'png' ) ); ?>" class="button" download>
                <?php esc_html_e( 'Download PNG', 'affiliate-assets' ); ?>
            </a>
            <a href="<?php echo esc_url( aa_get_qr_code_download_url( $referral_url, 'svg' ) ); ?>" class="button" download>
                <?php esc_html_e( 'Download SVG', 'affiliate-assets' ); ?>
            </a>
        </div>
    </div>

    <div class="aa-share-section">
        <h3><?php esc_html_e( 'Share on Social Media', 'affiliate-assets' ); ?></h3>
        <div class="aa-share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( $referral_url ); ?>" target="_blank" rel="noopener noreferrer" class="aa-share-btn aa-facebook">
                Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( $referral_url ); ?>&text=<?php echo urlencode( __( 'Check this out!', 'affiliate-assets' ) ); ?>" target="_blank" rel="noopener noreferrer" class="aa-share-btn aa-twitter">
                Twitter/X
            </a>
            <a href="https://wa.me/?text=<?php echo urlencode( $referral_url ); ?>" target="_blank" rel="noopener noreferrer" class="aa-share-btn aa-whatsapp">
                WhatsApp
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode( $referral_url ); ?>" target="_blank" rel="noopener noreferrer" class="aa-share-btn aa-linkedin">
                LinkedIn
            </a>
            <a href="mailto:?subject=<?php echo urlencode( __( 'Check this out!', 'affiliate-assets' ) ); ?>&body=<?php echo urlencode( $referral_url ); ?>" class="aa-share-btn aa-email">
                Email
            </a>
        </div>
    </div>
</div>
