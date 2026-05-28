<?php
/**
 * Affiliate Settings Template
 * 
 * Displays affiliate settings form.
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

$user = wp_get_current_user();
?>

<div class="aa-settings-template">
    <h2><?php esc_html_e( 'Affiliate Settings', 'affiliate-assets' ); ?></h2>
    
    <form method="post" action="" class="aa-settings-form">
        <?php wp_nonce_field( 'aa_update_settings', 'aa_settings_nonce' ); ?>
        
        <div class="aa-form-group">
            <label for="aa_payment_email"><?php esc_html_e( 'Payment Email', 'affiliate-assets' ); ?></label>
            <input type="email" id="aa_payment_email" name="aa_payment_email" value="<?php echo esc_attr( $affiliate->get_payment_email() ? $affiliate->get_payment_email() : $user->user_email ); ?>" class="regular-text" />
            <p class="description"><?php esc_html_e( 'Email address where you want to receive commission payments.', 'affiliate-assets' ); ?></p>
        </div>
        
        <div class="aa-form-group">
            <label for="aa_website_url"><?php esc_html_e( 'Website URL', 'affiliate-assets' ); ?></label>
            <input type="url" id="aa_website_url" name="aa_website_url" value="<?php echo esc_attr( $affiliate->get_website_url() ); ?>" class="regular-text" placeholder="https://yourwebsite.com" />
            <p class="description"><?php esc_html_e( 'Your website or blog URL.', 'affiliate-assets' ); ?></p>
        </div>
        
        <div class="aa-form-group">
            <label for="aa_promotional_methods"><?php esc_html_e( 'Promotional Methods', 'affiliate-assets' ); ?></label>
            <textarea id="aa_promotional_methods" name="aa_promotional_methods" rows="4" class="large-text"><?php echo esc_textarea( $affiliate->get_promotional_methods() ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Describe how you plan to promote our products (social media, email marketing, etc.).', 'affiliate-assets' ); ?></p>
        </div>
        
        <div class="aa-form-group">
            <label><?php esc_html_e( 'Your Referral Code', 'affiliate-assets' ); ?></label>
            <p><code><?php echo esc_html( $affiliate->get_referral_code() ); ?></code></p>
        </div>
        
        <p class="submit">
            <button type="submit" name="aa_save_settings" class="button button-primary"><?php esc_html_e( 'Save Settings', 'affiliate-assets' ); ?></button>
        </p>
    </form>
</div>
