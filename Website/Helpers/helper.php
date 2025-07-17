<?php

if (!function_exists('website_asset')) {
    function website_asset($path)
    {
        list($module, $assetPath) = explode(':', $path);
        return asset('modules/' . $module . '/resources/assets/' . $assetPath);
    }
}

