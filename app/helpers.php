<?php
/**
 * Helper: Extract country code from stored format "India [IN]" and return flag img tag.
 * If no code found, returns a globe icon.
 */
function getCountryFlag($country_str, $size = 20) {
    $code = '';
    if (preg_match('/\[([A-Z]{2})\]/', $country_str, $matches)) {
        $code = strtolower($matches[1]);
    }
    if ($code) {
        return '<img src="https://flagcdn.com/' . $size . 'x' . round($size * 0.75) . '/' . $code . '.png" alt="' . $code . '" style="width:' . $size . 'px; border-radius:2px; vertical-align:middle;">';
    }
    return '<i class="fa-solid fa-globe" style="opacity:0.4; font-size:' . ($size - 4) . 'px;"></i>';
}

/**
 * Helper: Extract just the country name (strip the code bracket).
 */
function getCountryName($country_str) {
    return trim(preg_replace('/\s*\[.*?\]/', '', $country_str)) ?: 'Unknown';
}
?>
