<?php
/**
 * Minimalist PHP QR Code generator stub for AffiliateAssets.
 * In production, include the full phpqrcode library from:
 * https://sourceforge.net/projects/phpqrcode/
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Stub class for QRcode - in production use full library
class QRcode {
    
    /**
     * Generate PNG QR code (stub).
     * 
     * @param string $text Text to encode
     * @param string|false $outfile Output file path
     * @param int $level Error correction level
     * @param int $size Pixel size
     * @param int $margin Margin size
     * @return void
     */
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        // Return a simple placeholder image
        header('Content-Type: image/png');
        $im = imagecreatetruecolor(100, 100);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $fg = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $bg);
        imagerectangle($im, 10, 10, 90, 90, $fg);
        imagestring($im, 5, 20, 45, 'QR', $fg);
        imagepng($im);
        imagedestroy($im);
    }
}

// Error correction level constants
define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);
