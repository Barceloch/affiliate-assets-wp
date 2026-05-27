<?php
/**
 * Define the internationalization functionality.
 *
 * @package AffiliateAssets\Core
 */

namespace AffiliateAssets\Core;

class Class_I18n {
    
    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'affiliate-assets',
            false,
            dirname(AA_PLUGIN_BASENAME) . '/languages'
        );
    }
}
