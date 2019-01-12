<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * sds_start_css
 * void
 * Description:
 *
 *
 */
function sds_start_css()
{
    $pluginDirPath = plugin_dir_path(__FILE__);

    $removeIncludeFromPath = str_replace('includes', '', $pluginDirPath);

    if (is_admin()) {
        include $removeIncludeFromPath . 'css/sds-style.css';
    }
}

add_action('admin_head', 'sds_start_css');