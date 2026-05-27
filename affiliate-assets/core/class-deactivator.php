<?php
/**
 * Fired during plugin deactivation.
 * Cleans up rewrite rules and optionally removes data.
 *
 * @package AffiliateAssets\Core
 */

namespace AffiliateAssets\Core;

class Class_Deactivator {
    
    /**
     * Deactivate the plugin.
     */
    public static function deactivate() {
        // Flush rewrite rules to remove pretty URLs
        flush_rewrite_rules();
        
        // Note: We don't delete tables or options on deactivation
        // That should only happen on uninstall if user chooses to
    }
}
