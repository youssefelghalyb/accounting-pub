<?php

if (!function_exists('adjustBrightness')) {
    /**
     * Adjust the brightness of a hex color
     *
     * @param string $hex
     * @param int $steps (-255 to 255)
     * @return string
     */
    function adjustBrightness($hex, $steps)
    {
        // Remove # if present
        $hex = str_replace('#', '', $hex);
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convert back to hex
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
                  . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
                  . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }




    
}