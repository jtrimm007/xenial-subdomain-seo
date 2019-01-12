<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( !current_user_can( 'activate_plugins' ) OR !current_user_can('update_core')) {
    echo '<h3>Admin access is required</h3>';
} else {


    // FOR TESTING
    //var_dump($_POST);
    //var_dump($_SESSION);

    //sessions and variables for creating subdomain
    $SDS_userName = '';
    $SDS_password = '';
    $SDS_skin = '';
    $SDS_hostName = '';
    $SDS_direcotry = '';
    $SDS_newSubDomainName = '';

    $SDS_sessionArray = array();

    //if statement to check create sub-domain sessions
    if(isset($_SESSION['userName']) && isset($_SESSION['directory']))
    {
        //sessions and variables for creating subdomain
        $SDS_userName = sanitize_text_field($_SESSION['userName']);
        $SDS_password = sanitize_text_field($_SESSION['password']);
        $SDS_hostName = sanitize_text_field($_SESSION['hostName']);
        $SDS_direcotry = sanitize_text_field($_SESSION['directory']);
        $SDS_newSubDomainName = sanitize_text_field($_SESSION['newSubDomainName']);

        $SDS_sessionArray = array($SDS_direcotry, $SDS_hostName, $SDS_newSubDomainName, $SDS_password,
            $SDS_skin, $SDS_userName);
    }

    //if statement to check login and get sub-domain list sessions
    if(isset($_SESSION['userName']))
    {
        //sessions and variables for creating subdomain
        $SDS_userName = filter_var($_SESSION['userName'], FILTER_SANITIZE_STRING);
        $SDS_password = filter_var($_SESSION['password'], FILTER_SANITIZE_STRING);
        $SDS_hostName = filter_var($_SESSION['hostName'], FILTER_SANITIZE_STRING);

    }

    //sessions post and page
    $SDS_page = array();
    $SDS_post = array();
    $SDS_contentSwitchCase = array();
    $combianPageandPost = array();

    //var_dump($_SESSION);

    //if statement to check select pages/post to get content from sessions
    if(isset($_SESSION['pages']) && isset($_SESSION['post']))
    {


        $newArrayForSessionPost = array();
        $newArrayForSessionPages = array();


        foreach ($_SESSION['post'] as $eachPost)
        {
            $sanitizedPost = sanitize_text_field($eachPost);
            array_push($newArrayForSessionPost, $sanitizedPost);
        }


        foreach ($_SESSION['pages'] as $each)
        {
            //echo 'this is a page: ' . $each;
            //var_dump($each);
            $sanitizedPages = sanitize_text_field($each);
            array_push($newArrayForSessionPages, $sanitizedPages);
        }


        $SDS_post = $newArrayForSessionPost;
        $SDS_page = $newArrayForSessionPages;


        $combianPageandPost = array_merge($SDS_page, $SDS_post);

        $SDS_contentSwitchCase = $combianPageandPost;

    }
    elseif (isset($_SESSION['post']))
    {

        $newArrayForSessionPost = array();

        foreach ($_SESSION['post'] as $eachPost)
        {
            $sanitizedPost = sanitize_text_field($eachPost);
            array_push($newArrayForSessionPost, $sanitizedPost);
        }

        $SDS_post = $newArrayForSessionPost;

        $SDS_contentSwitchCase = $SDS_post;
    }
    elseif (isset($_SESSION['pages']))
    {

        $newArrayForSessionPages = array();

        foreach ($_SESSION['pages'] as $each)
        {
            //echo 'this is a page: ' . $each;
            //var_dump($each);
            $sanitizedPages = sanitize_text_field($each);
            array_push($newArrayForSessionPages, $sanitizedPages);
        }

        $SDS_page = $newArrayForSessionPages;

        $SDS_contentSwitchCase = $SDS_page;
    }



    $SDS_subdomainOption = '';

    //if statement to check subdomain options located above editor
    if(isset($_SESSION['subdomainOptions']))
    {
        $SDS_subdomainOption = sanitize_text_field($_SESSION['subdomainOptions']);
    }


    //sessions for editor
    $SDS_editorContent = '';

    //if statement to check editor sessions
    if(isset($_SESSION['editor']))
    {
        $SDS_editorContent = addslashes($_SESSION['editor']);
    }

    //session for creating a web page
    $SDS_subdomainSelectedToCreateWebPage = '';

    //if
    if(isset($_SESSION['selectSubdomain']))
    {
        $SDS_subdomainSelectedToCreateWebPage = $_SESSION['selectSubdomain'];
    }

    $SDS_headContent = '';

    if(isset($_POST['headContent']))
    {
        $SDS_headContent = addslashes($_SESSION['headContent']);

    }


    sds_check_edit_form_nonce($SDS_editorContent, $SDS_headContent, $SDS_subdomainOption);


    //Disk space. Need to make some adjustments.
    //SDS_get_disk_totals();

    //Get the available subdomains from the database. This allows us to pull from an on damand list so we don't have to login to cpanel.
    SDS_selectSubDomainsFromDatabase();
?>


    <?php sds_check_nonce($SDS_hostName, $SDS_password, $SDS_newSubDomainName, $SDS_direcotry, $SDS_userName); ?>
    <h1>Create Sub-Domain</h1>
    <form method="post" action="<?php $_SERVER['REQUEST_URI']; ?>">
        cpanel User Name: <input name="userName" value="<?php $SDS_userName ?>" type="text" required><br>
        cpanel Password: <input name="password" value="<?php $SDS_password ?>" type="password" required><br>
        cpanel Host Name: <input name="hostName" value="<?php $SDS_hostName ?>" type="text" required><br>
        Directory: <input name="directory" value="<?php $SDS_direcotry ?>" placeholder="Ex: /public_html/" type="text" required><br>
        Sub Domain Name: <input name="newSubDomainName" value="<?php $SDS_newSubDomainName ?>" type="text" required><br>
        <input type="hidden" name="create_and_get_domain_nonce" value="<?php echo wp_create_nonce('create_and_get_domain_nonce'); ?>" />

        <input type="submit" value="submit">
    </form><br>


<hr>


    <h1>Select Pages/Post to Get Content From</h1>
    <p>Once boxes are checked, hit submit and the text editor will load at the bottom of the page.</p>
    <form method="post" action="<?php $_SERVER['REQUEST_URI']; ?>">

    <?php

    //Instantiate a new SDSContent object.
    $createWebPage = new SDSContent();

    //Get all the pages to select content from
    $selectPages = $createWebPage->sds_selectPages();


    //Get all the post to select content from
    $selectPost = $createWebPage->sds_selectPosts();

    ?>
    <br>
    <input type="submit" value="submit">
    </form>
<hr>

<?php


    //Get the content from the selected pages.
    $content = $createWebPage->sds_get_page_content($SDS_contentSwitchCase);

    //Start the editor once the content is selected.
    $editor = $createWebPage->start_editor($SDS_headContent, $content);


    ?>


    <h1>Create Web Page</h1>
<?php
    $subdomainArray = sds_get_wp_subDomainPages();


    sds_check_html_form_nonce($subdomainArray, $SDS_subdomainSelectedToCreateWebPage);



    // Final closing bracket below
}