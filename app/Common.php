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

if (! function_exists('normalize_product_image_path')) {
    function normalize_product_image_path($imagePath, bool $fileNameOnly = false): ?string
    {
        $imagePath = trim((string) ($imagePath ?? ''));
        if ($imagePath === '') {
            return null;
        }

        $imagePath = str_replace('\\', '/', $imagePath);
        $imagePath = preg_replace('#^https?://[^/]+/#i', '', $imagePath);
        $imagePath = preg_replace('#^/?(?:public/)?uploads/products/#i', '', $imagePath);
        $imagePath = ltrim($imagePath, '/');

        if ($imagePath === '' || str_contains($imagePath, '..')) {
            return null;
        }

        if ($fileNameOnly) {
            return basename($imagePath);
        }

        return $imagePath;
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url($imagePath): string
    {
        $imageName = normalize_product_image_path($imagePath, true);
        if ($imageName === null) {
            return '';
        }

        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $uploadUri = str_contains($scriptName, '/public/')
            ? 'uploads/products/' . $imageName
            : 'public/uploads/products/' . $imageName;

        return base_url($uploadUri);
    }
}
