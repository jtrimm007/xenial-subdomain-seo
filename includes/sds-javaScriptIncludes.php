<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * sds_insert_scripts
 * void
 * Description:
 *
 *
 */
function sds_insert_scripts()
{
    //Future scripts go below
}

add_action( 'wp_enqueue_scripts', 'sds_insert_scripts' );


/**
 * sds_insert_admin_scripts
 * void
 * Description:
 *
 *
 */
function sds_insert_admin_scripts() {

    $pluginDirPath = plugin_dir_url( __FILE__ );
    $removeIncludeFromPath = str_replace('/includes', '', $pluginDirPath);

    wp_enqueue_script( 'check_all_pages',  $removeIncludeFromPath . 'js/checkAllPages.js' );

    //wp_enqueue_script( 'check_all_payment_types', plugin_dir_url( __FILE__ ) . 'js/checkAllPaymentTypes.js' );
}
add_action( 'admin_enqueue_scripts', 'sds_insert_admin_scripts' );