<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//check to see if the $_POST variable is filled with something before checking it.
if(isset($_POST['userName']) && isset($_POST['directory']))
{

    //filter_var($_SESSION['userName'], FILTER_SANITIZE_STRING);

    //Sessions for creating a subdomain
    $_SESSION['userName'] = sanitize_text_field($_POST['userName']);
    $_SESSION['password'] = sanitize_text_field($_POST['password']);
    $_SESSION['hostName'] = sanitize_text_field($_POST['hostName']);
    $_SESSION['directory'] = sanitize_text_field($_POST['directory']);
    $_SESSION['newSubDomainName'] = sanitize_text_field($_POST['newSubDomainName']);
}

if(isset($_POST['userName']))
{
    //Sessions for creating a subdomain
    $_SESSION['userName'] = sanitize_text_field($_POST['userName']);
    $_SESSION['password'] = sanitize_text_field($_POST['password']);
    $_SESSION['hostName'] = sanitize_text_field($_POST['hostName']);
}

if(isset($_POST['pages']) && isset($_POST['post']))
{
    $newArrayForSessionPages = array();
    $newArrayForSessionPost = array();

    foreach ($_POST['pages'] as $each)
    {
        //echo 'this is a page: ' . $each;
        //var_dump($each);
        $sanitizedPages = sanitize_text_field($each);
        array_push($newArrayForSessionPages, $sanitizedPages);
    }

    foreach ($_POST['post'] as $eachPost)
    {
        $sanitizedPost = sanitize_text_field($eachPost);
        array_push($newArrayForSessionPost, $sanitizedPost);
    }

    //var_dump($_POST['pages']);

    $_SESSION['pages'] = $newArrayForSessionPages;
    $_SESSION['post'] = $newArrayForSessionPost;


    //Sessions for page checkboxes
    //$_SESSION['pages'] = $_POST['pages'];
    //$_SESSION['post'] = $_POST['post'];




}
elseif (isset($_POST['pages']))
{
    $newArrayForSessionPages = array();

    foreach ($_POST['pages'] as $each)
    {
        //echo 'this is a page: ' . $each;
        //var_dump($each);
        $sanitizedPages = sanitize_text_field($each);
        array_push($newArrayForSessionPages, $sanitizedPages);
    }

    $_SESSION['pages'] = $newArrayForSessionPages;

}
elseif (isset($_POST['post']))
{
    $newArrayForSessionPost = array();

    foreach ($_POST['post'] as $eachPost)
    {
        $sanitizedPost = sanitize_text_field($eachPost);
        array_push($newArrayForSessionPost, $sanitizedPost);
    }

    $_SESSION['post'] = $newArrayForSessionPost;
}


if(isset($_POST['editor']))
{
   // $_SESSION['editor'] = $_POST['editor'];
    $_SESSION['editor'] = sanitize_text_field( htmlentities($_POST['editor']));

}

if(isset($_POST['subdomainOptions']))
{
    //Session to associate content with subdomain
    $_SESSION['subdomainOptions'] = sanitize_text_field($_POST['subdomainOptions']);
}



if(isset($_POST['selectSubdomain']))
{
    //Sessions for creating a web page
    $_SESSION['selectSubdomain'] = sanitize_text_field($_POST['selectSubdomain']);
}

if(isset($_POST['headContent']))
{
    $_SESSION['headContent'] = sanitize_text_field( htmlentities($_POST['headContent']));

}