<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly



/**
 * sds_seo_menu
 * void
 * Description:
 *
 *
 */
function sds_seo_menu()
{
    if (is_admin()) {
        //local variables for main menu
        $page_title = 'Sub Domain SEO';
        $menu_title = 'SD SEO';
        $capability = 'administrator';
        $menu_slug = 'sds-about.php';
        $function = 'sds_seo_menu';
        $icon_url = 'dashicons-index-card';
        $position = 8;

        //local variables for settings page
        $parent_slug = 'sds-about.php';


        //Menu variables for about page
        $about_page_title = 'About SD SEO';
        $about_menu_title = 'About SD SEO';
        $sub_about_slug = 'sds-about.php';

        //Menu variables for create sub domain page
        $createSubdomain_page_title = 'Create Sub Domain';
        $createSubdomain_menu_title = 'Create Sub Domain';
        $sub_createSubdomain_slug = 'sds-create-subdomain.php';


        //Main menu on left side
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);

        //Add sds-about.php to menue
        add_submenu_page($parent_slug, $about_page_title, $about_menu_title, $capability, $sub_about_slug, $function);

        //Add sds-about.php to menue
        add_submenu_page($parent_slug, $createSubdomain_page_title, $createSubdomain_menu_title, $capability, $sub_createSubdomain_slug, $function);




    }
}

//action hook for the xenial menu
add_action('admin_menu', 'sds_seo_menu');


/**
 * sds_start_pages
 * void
 * Description:
 *
 *
 */
function sds_start_pages()
{

    $pluginDirPath = plugin_dir_path(__FILE__);

    $removeIncludeFromPath = str_replace('includes', '', $pluginDirPath);
    if(isset($_GET['page']))
    {
        $page = $_GET['page'];
        //var_dump($page);
        if (!strcmp($page, 'sds-about.php')) {

            include_once $removeIncludeFromPath . 'sds-about.php';

        } elseif (!strcmp($page, 'sds-create-subdomain.php')) {
            include_once $removeIncludeFromPath . 'sds-create-subdomain.php';
        }
    }


}

add_action('all_admin_notices', 'sds_start_pages');