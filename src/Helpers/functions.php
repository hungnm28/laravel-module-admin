<?php
if (!function_exists('lmaIcon')) {
    function lmaIcon($name, $width = 18, $height = 0, $class = null, $attribute = null)
    {
        if ($height == 0) $height = $width;
        return '
            <svg width="' . $width . 'px" height="' . $height . 'px"  viewBox="0 0 24 24" class="mcon ' . $class . '" ' . $attribute . ' fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="' . env('ASSET_ICON', 'assets') . '/images/icons.svg#' . $name . '"/>
            </svg>
        ';
    }
}

