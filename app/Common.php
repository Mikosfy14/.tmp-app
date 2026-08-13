<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('is_active')) {
    /**
     * Check if the current route matches the given route name
     * 
     * @param string $path Route path (misal: 'dashboard', 'projects')
     * @return string return 'active' if matches, return '' if not matches
     */
    function is_active(string $path): string
    {
        $uri = uri_string();

        if ($path == 'dashboard' || $path == '/') {
            return ($uri === '' || $uri === 'dashboard' || str_contains($uri, 'dashboard')) ? 'active' : '';
        }

        return str_contains($uri, $path) ? 'active' : '';
    }
}
