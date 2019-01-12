<?php
/*
Plugin Name:  Xenial Subdomain SEO
Plugin URI:   https://seojohnsoncity.trimwebdesign.com
Description:  Create subdomains with content that is currently on your website
Header Comment
Version:      1.0.0
Author:       Joshua Trimm
Author URI:   https://trimwebdesign.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  seojohnsoncity.trimwebdesign
Domain Path:  /languages

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add includes and requires
if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}



//Define plugin version
define("SUBDOMAIN_SEO", "1.0.0");

//Define plugin path slug
define("SUBDOMAIN_SEO_PLUGINPATH", "/" . plugin_basename(dirname(__FILE__)) . "/");

//Define the plugin full url
define("SUBDOMAIN_SEO_PLUGINFULLURL", trailingslashit(plugins_url('sds-functions.php', __FILE__)));

//Define the plugin full dir
define("SUBDOMAIN_SEO_PLUGINFULLDIR", WP_PLUGIN_DIR . SUBDOMAIN_SEO_PLUGINPATH);

//Define the global var SUBDOMAINSEOWP1, returing bool if WP 7.0 or higher is running
define('SUBDOMAINSEOWP1', version_compare($GLOBALS['wp_version'], '6.9.999', '>'));

/**
 * include all the php files from the includes folder
 */
$forIncludesLoop = plugin_dir_path(__FILE__) . 'includes/';

$scanDirectory = scandir($forIncludesLoop);

// Loop through all the files that are php in the includes folder. Then include them in this file.
foreach($scanDirectory as $file)
{
    $phpFile = strpos($file, '.php');
    if($phpFile == True)
    {
        // $forIncludesLoop is the file page and $file is the name
        include $forIncludesLoop . $file;
        // echo $forIncludesLoop . $file;
    }
}

/**
 * xwp_activation
 * void
 * Description: Activation for SDS tables
 *
 *
 */

register_activation_hook(__FILE__, 'SDS_activation');

function SDS_activation()
{
    //include global config
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;

    $table_name = $wpdb->prefix . "subDomainPages";
    $table_name1 = $wpdb->prefix . "subdomainsFromServer";

    if ($wpdb->get_var('SHOW TABLES LIKE' . $table_name) != $table_name && $wpdb->get_var('SHOW TABLES LIKE' . $table_name1) != $table_name1) {
        $sql = 'CREATE TABLE ' . $table_name . '(
            id int NOT NULL AUTO_INCREMENT,
            subDomain VARCHAR(100),
            content LONGTEXT,
            PRIMARY KEY (id)         
        )';

        $sql2 = 'CREATE TABLE ' . $table_name1 . '(
            id int NOT NULL AUTO_INCREMENT,
            subDomains VARCHAR(1000),
            PRIMARY KEY (id)         
        )';

        $initializeSubDomainTable = "INSERT INTO `" . $table_name . "` (`id`, `subDomain`, `content`) VALUES ('1', NULL, NULL)";
        $initializeSubDomainsTable = "INSERT INTO `" . $table_name1 . "` (`id`, `subDomains`) VALUES ('1', NULL)";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        dbDelta($sql2);

        dbDelta($initializeSubDomainsTable);

        dbDelta($initializeSubDomainTable);

        add_option('subDomain_database_version', '1.0.1');
    }

    flush_rewrite_rules();

}



register_activation_hook( __FILE__, 'SDS_error_file' );



/**
 * login_test
 * void
 * Description: Login to cpanel and create a subdomain with the variables given. Then returns the results.
 * * @param $SDS_newSubDomainName
 * @param $SDS_hostName
 * @param $SDS_direcotry
 * @param $SDS_pass
 * @param $SDS_user
 * @param $SDS_ip
 * @return 'subdomain created: ' . $result
 */
function SDS_cpanel_logon_and_create_subdomain($SDS_newSubDomainName, $SDS_hostName, $SDS_directory, $SDS_pass, $SDS_user)
{

    try
    {

        $json_client = new SDSxmlapi($SDS_hostName);

        $json_client->set_output('json');
        $json_client->set_port(2083);
        $json_client->password_auth($SDS_user, $SDS_pass);
        $json_client->set_debug(1);

        /** For Testing */
        //echo '<br>';
        //print $json_client->get_host();
        //echo '<br>';
       // var_dump($SDS_newSubDomainName);
        //$encoding = mb_detect_encoding( $SDS_newSubDomainName, "auto" );
        //echo '<br>';
        //echo $encoding;


        $args = array(
            'domain' => $SDS_newSubDomainName,
            'rootdomain' => $SDS_hostName,
            'dir'       => $SDS_directory . '/' . $SDS_newSubDomainName
        );

        $result = $json_client->api2_query( $SDS_user, 'SubDomain', 'addsubdomain', $args);

        echo '<br>';
        return 'subdomain created: ' . $result;
    }
    catch (Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
}

/**
 * SDS_get_disk_totals
 * void
 * Description: Give the disk space available on the server.
 */
function SDS_get_disk_totals()
{

    $diskSpace = new SDSContent();

    $freeDiskSpace = $diskSpace->sds_disk_space();

    echo '<br>';
    echo 'Free space: ';
    var_dump($freeDiskSpace);
    echo '<br>';

    $totalDiskSpace = $diskSpace->sds_total_disk_space();
    echo '<br>';
    echo 'disk total space: ';
    var_dump($totalDiskSpace);
    echo '<br>';
    echo 100 * floor($freeDiskSpace / $totalDiskSpace);
    echo '<br>';
    echo 'Free Space: ' . (floor(100 *($freeDiskSpace/$totalDiskSpace))) . '%';
    echo '<br>';
    echo 'used: ' . ($totalDiskSpace - $freeDiskSpace);
    echo '<br>';
    echo '<hr>';
}

/**
 * SDS_insertSubdomainArray
 * void
 * @param $subdomains
 * Description: Insert the subdomain array into the database.
 */
function SDS_insertSubdomainArray($subdomains)
{
    //var_dump($subdomains);


    //include global config
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;

    $arrayToString = implode(",", $subdomains);

    //var_dump($arrayToString);

    $data = array('subDomains' => $arrayToString);

    $where = array('id' => '1');

    $table_name = $wpdb->prefix . "subdomainsFromServer";

    $wpdb->update($table_name, array("subDomains" => $arrayToString), array("id" => "1"));
}

/**
 * SDS_insertNewSubdomainContent
 * void
 * @param $content
 * @param $subdomain
 * Description: Insert subdomain with content into the database.
 */
function SDS_insertNewSubdomainContent($content, $subdomain)
{
    //include global config
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;

    //$arrayToString = implode(",", $subdomains);

    $table_name = $wpdb->prefix . "subDomainPages";

    $insert = "INSERT INTO " . $table_name . " (subDomain, content) VALUES ('" . $subdomain . "', '" . $content . "')";

    $wpdb->query($insert);
}

/**
 * SDS_selectSubDomainsFromDatabase
 * void
 * Description: Get the subdomains array from the database.
 */
function SDS_selectSubDomainsFromDatabase()
{
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;

    $table_name = $wpdb->prefix . "subdomainsFromServer";

    $row = $wpdb->get_row('SELECT * FROM ' . $table_name);

    $subdomains = $row->subDomains;

    $subdomainArray = explode(",", $subdomains);

    $_SESSION['subdomainArray'] = $subdomainArray;

}

/**
 * SDS_cpanel_logon_and_get_subdomain
 * array
 * @param $SDS_hostName
 * @param $SDS_pass
 * @param $SDS_user
 * @return array
 * Description: Login to cpanel and get a list of all the subdomains to choose from.
 */
function SDS_cpanel_logon_and_get_subdomain($SDS_hostName, $SDS_pass, $SDS_user)
{
    try
    {

        $json_client = new SDSxmlapi($SDS_hostName);

        $json_client->set_output('json');
        $json_client->set_port(2083);
        $json_client->password_auth($SDS_user, $SDS_pass);
        $json_client->set_debug(1);

        /** For Testing */
        //echo '<br>';
        //print $json_client->get_host();
        //echo '<br>';

        $result2 = $json_client->api2_query( $SDS_user, 'SubDomain', 'listsubdomains', array('data' => 'subdomain', ));

        // Convert JSON string to Array
        $someArray = json_decode($result2, true);

        $test = $someArray['cpanelresult']['data']; // Access Array data

        $subdomainArray = array();

        foreach($test as $key => $value)
        {
            // USED FOR TESTING echo '<strong>Key: </strong>' . $key . ' Value: ' . $value;
            // echo '<br>';
            foreach($value as $key1 => $value1)
            {
                //USE FOR TESTING echo 'Key: ' . $key1 . ' Value: ' . $value1;
                //echo '<br>';
                if($key1 == 'subdomain')
                {
                    array_push($subdomainArray, $value1 );
                }
            }
        }
        /** for Testing */
        //echo '<br>';
        //echo 'subdomains pushed to array below: ';
        //var_dump($subdomainArray);

        // return the subdomain array that was created.
        return $subdomainArray;

    }
    catch (Exception $e)
    {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }
}

/**
 * sds_checkIfSubdomainExistsWith
 * bool
 * Description:
 * * @param $subdomainSelected
 *
 */
function sds_checkIfSubdomainExistsWith($subdomainSelected)
{
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;

    $subdomainArray = array();

    $table_name = $wpdb->prefix . "subDomainPages";

    $row = $wpdb->get_results('SELECT * FROM ' . $table_name);


    foreach ($row as $key)
    {

        $subdomain = $key->subDomain;
        $subdomainContent = $key->content;

        if($subdomain == $subdomainSelected)
        {
            return true;
        }
    }

    return false;
}

function sds_ifTrueSelectAndUpdateContentForSubdomain($trueorFalse, $subdomainSelected, $subdomainContent)
{
    if($trueorFalse == true)
    {
        //echo 'True got through ' . $trueorFalse;
        //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

        global $wpdb;

        $subdomainArray = array();

        $table_name = $wpdb->prefix . "subDomainPages";

        //$row = $wpdb->get_results('UPDATE' . $table_name . ' SET ' . $subdomainSelected . '=' . $subdomainContent);


        $wpdb->update($table_name, array('content' => $subdomainContent), array('subDomain' => $subdomainSelected));

    }
    else
    {
        //include global config
        //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

        global $wpdb;

        //$arrayToString = implode(",", $subdomains);

        $table_name = $wpdb->prefix . "subDomainPages";

        $insert = "INSERT INTO " . $table_name . " (subDomain, content) VALUES ('" . $subdomainSelected . "', '" . $subdomainContent . "')";

        $wpdb->query($insert);
    }

}

/**
 * sds_get_wp_subDomainPages
 * array
 * @return array
 * Description:
 */
function sds_get_wp_subDomainPages()
{
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;

    $subdomainArray = array();

    $table_name = $wpdb->prefix . "subDomainPages";

    $row = $wpdb->get_results('SELECT * FROM ' . $table_name);


    foreach ($row as $key)
    {

        $subdomain = $key->subDomain;
        $subdomainContent = $key->content;

        if($subdomain != NULL || $subdomainContent != NULL)
        {
            array_push($subdomainArray, $subdomain);
        }
    }

    return $subdomainArray;
}

/**
 * sds_getContentOfSelectedDomain
 * mixed
 * @param $subdomainSelected
 * @return mixed
 * Description:
 */
function sds_getContentOfSelectedDomain($subdomainSelected)
{
    //include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

    global $wpdb;


    $table_name = $wpdb->prefix . "subDomainPages";

    $row = $wpdb->get_results('SELECT * FROM ' . $table_name);


    foreach ($row as $key => $value)
    {

        $checkSubdomains = $value->subDomain;

        if($checkSubdomains == $subdomainSelected)
        {
            /** For Testing */
            //echo '<br>';
            //echo $checkSubdomains;
            //echo '<br>';
            //var_dump($value->content);

            $content = $value->content;

            return $content;
        }
    }
}

/**
 * sds_selectDomainToCreateWebPage
 * void
 * @param $subdomainArray
 * Description:
 */
function sds_selectDomainToCreateWebPage($subdomainArray)
{


    echo '<h3>Choose a subdomain</h3>';
    echo '<p>The subdomains below have content associated with them. They are safe to create a webpage.';
    echo '<br>';
    ?>
    <form method="post" action="<?php $_SERVER['REQUEST_URI']; ?>">
<?php
    echo '<select name="selectSubdomain">';
    foreach($subdomainArray as $sub)
    {
        echo '<option value="' . $sub . '">' . $sub . '</option>';
    }
    echo '</select>';
?>

        <input type="hidden" name="create_html_file_nonce" value="<?php echo wp_create_nonce('create_html_file_nonce'); ?>" />

        <br>
        <input type="submit" value="submit">
    </form>
<?php
}


function sds_docTypeHtmlHeadBody($head, $content)
{




    $htmlDoc = '<!DOCTYPE html>
    <html>
    <head>'
    . $head .
    '</head>
    <body>'
        . $content .

        '</body>
    </html>
    ';

    return $htmlDoc;
}
/**
 * sds_findSubdomainOnServer
 * void
 * @param $subdomain
 * @param $content
 * Description:
 */
function sds_findSubdomainOnServer($subdomain, $content)
{
    $documentRoot = $_SERVER["DOCUMENT_ROOT"];

    $files = scandir($documentRoot);

    foreach($files as $file)
    {

        $dot = '.';
        $checkForDot = strpos($file, $dot);

        if($checkForDot != true)
        {

            if($file == $subdomain)
            {
                $newFile = fopen($documentRoot . '/' . $subdomain . '/index.html', "w") or die("File did not open!");


                fwrite($newFile, html_entity_decode($content));

                fclose($newFile);
            }
        }
    }
}

/**
 * sds_check_nonce
 * void
 * @param $SDS_hostName
 * @param $SDS_password
 * @param $SDS_newSubDomainName
 * @param $SDS_direcotry
 * @param $SDS_userName
 * Description: Check nonce before logging into cpanel.
 */
function sds_check_nonce($SDS_hostName, $SDS_password, $SDS_newSubDomainName, $SDS_direcotry, $SDS_userName)
{
    if(isset($_POST['create_and_get_domain_nonce']))
    {
        if(wp_verify_nonce($_POST['create_and_get_domain_nonce'], 'create_and_get_domain_nonce'))
        {
            if($SDS_hostName != NULL || $SDS_password != NULL)
            {
                //Login to cpanel and create a subdomain
                $create_subdomain = SDS_cpanel_logon_and_create_subdomain($SDS_newSubDomainName, $SDS_hostName, $SDS_direcotry, $SDS_password, $SDS_userName);

                //Login to cpanel and get all the available subdomains
                $getSubDomainsList = SDS_cpanel_logon_and_get_subdomain($SDS_hostName, $SDS_password, $SDS_userName);
                //var_dump($getSubDomainsList);
                //Check to see if we have the information from SDS_cpanel_logon_and_get_subdomains. If we do insert it in the database.
                if($getSubDomainsList != NULL)
                {
                    SDS_insertSubdomainArray($getSubDomainsList);
                }

            }
        }
        else
        {
            echo 'nonce not verified'; exit;
        }
    }
}
add_action('init', 'sds_check_nonce');





/**
 * sds_check_html_form_nonce
 * void
 * @param $subdomainArray
 * @param $SDS_subdomainSelectedToCreateWebPage
 * Description: Check form nonce before creating the .html
 */
function sds_check_html_form_nonce($subdomainArray, $SDS_subdomainSelectedToCreateWebPage)
{
    sds_selectDomainToCreateWebPage($subdomainArray);

    echo $SDS_subdomainSelectedToCreateWebPage;

    if (isset($_POST['create_html_file_nonce']))
    {
        if (wp_verify_nonce($_POST['create_html_file_nonce'], 'create_html_file_nonce'))
        {


            $contentSelected = sds_getContentOfSelectedDomain($SDS_subdomainSelectedToCreateWebPage);


            sds_findSubdomainOnServer($SDS_subdomainSelectedToCreateWebPage, $contentSelected);
        }
    }
}
add_action('init', 'create_html_file_nonce');


/**
 * sds_check_edit_form_nonce
 * void
 * @param $SDS_editorContent
 * @param $SDS_headContent
 * @param $SDS_subdomainOption
 * Description: Check nonce before submitting content to database for the html file
 */
function sds_check_edit_form_nonce($SDS_editorContent, $SDS_headContent, $SDS_subdomainOption)
{


    if (isset($_POST['create_edit_form_nonce']))
    {
        if (wp_verify_nonce($_POST['create_edit_form_nonce'], 'create_edit_form_nonce'))
        {


            //If there is content submitted from the text editor, insert it in the database.
            if($SDS_editorContent != NULL)
            {
                $htmlContent = sds_docTypeHtmlHeadBody($SDS_headContent, $SDS_editorContent);


                $trueorFalse = sds_checkIfSubdomainExistsWith($SDS_subdomainOption);

                //var_dump($trueorFalse);

                sds_ifTrueSelectAndUpdateContentForSubdomain($trueorFalse, $SDS_subdomainOption, $htmlContent);

                //SDS_insertNewSubdomainContent($htmlContent, $SDS_subdomainOption);
            }
        }
    }
}
add_action('init', 'create_edit_form_nonce');