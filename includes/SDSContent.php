<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class SDSContent
{

    private $scrapeUrl;
    private $searchForKeyWord;
    private $replaceKeyWord;
    private $htmlID;

    /**
     * SDSContent constructor.
     * @param $scrapeUrlapi2_query
     * @param $searchForKeyWord
     * @param $replaceKeyWord
     */
    /*
    function __construct($scrapeUrl, $searchForKeyWord, $replaceKeyWord, $htmlID)
    {
        $this->scrapeUrl=$scrapeUrl;
        $this->searchForKeyWord=$searchForKeyWord;
        $this->replaceKeyWord=$replaceKeyWord;
        $this->htmlID=$htmlID;
    }
    */

    /**
     * findAndReplace
     * mixed
     * Description:
     * * @param $searchForKeyWord
     * @param $replaceKeyWord
     * @param $content
     *
     */
    function findAndReplace($searchForKeyWord, $replaceKeyWord, $content)
    {
        $newContent = str_replace($searchForKeyWord,$replaceKeyWord,$content);

        return $newContent;
    }

    /**
     * insertContentIntoHTMLFile
     * void
     * Description:
     * * @param $content
     * @param $HTMLFile
     * @param $directory
     *
     */
    function insertContentIntoHTMLFile($content, $HTMLFile, $directory)
    {
        $newFile = fopen($directory . '/' . $HTMLFile, "w") or die("File did not open!");

        fwrite($newFile, $content);

        fclose($newFile);
    }

    /**
     * sds_get_all_post_ids
     * void
     * Description: Get and returns all the Post IDs in the form of an array
     *
     *
     */
    function sds_get_all_post_ids()
    {

        $postIdArray = array();

        $query = new WP_Query( 'p' );
        foreach($query as $postIds)
        {
            //var_dump($postIds);
        }


        if ( $query->have_posts() )
        {
            // The 2nd Loop
            while ( $query->have_posts() )
            {
                $query->the_post();
                //echo '<li>' . $query->post->ID  . '</li>';
                array_push($postIdArray, $query->post->ID);
            }

            // Restore original Post Data
            wp_reset_postdata();
            return $postIdArray;
        }
    }

    /**
     * sds_get_permalinks_from_ID_array
     * array
     * Description: turns page or post ID's into permalinks
     * * @param $array
     *
     */
    function sds_get_permalinks_from_ID_array( $array )
    {
        $permalinkArray = array();
        foreach ($array as $id)
        {
            $getPermalinks = get_permalink($id);
            array_push($permalinkArray, $getPermalinks);
        }
        return $permalinkArray;
    }

    /**
     * sds_selectPosts
     * void
     * Description: Select pages for schema to display on
     */
    function sds_selectPosts()
    {

        $page_ids = get_posts(array(
            'fields'          => 'ids', // Only get post IDs
            'posts_per_page'  => -1
        ));;

        echo '<h3>My Post List :</h3>';
        echo '<br>';
        echo '<input type="checkbox" name = "checkCon2" onClick="selectall2(this)" >' . ' <strong>Check all boxes</strong>';

        foreach ($page_ids as $page) {
            echo '<br>';
            echo '<input type="checkbox" name="post[]" value="' . esc_html($page) . '">' . ' ' . get_the_title($page) . ' ';
        }
    }

    /**
     * sds_selectPages
     * void
     * Description:
     */
    function sds_selectPages()
    {

        $page_ids = get_all_page_ids();

        echo '<h3>My Page List :</h3>';
        echo '<br>';
        echo '<input type="checkbox" name = "checkCon" onClick="selectall(this)" >' . ' <strong>Check all boxes</strong>';

        foreach ($page_ids as $page) {
            echo '<br>';
            echo '<input type="checkbox" name="pages[]" value="' . esc_html($page) . '">' . ' ' . get_the_title($page) . ' ';
        }
    }

    /**
     * sds_get_page_content
     * array
     * @param $idArray
     * @return array
     * Description:
     */
    function sds_get_page_content($idArray)
    {
        $contentArray = array();
        if($idArray != NULL)
        {
            foreach($idArray as $page)
            {
                $setVar = get_post($page);
                $getContent = $setVar->post_content;

                /** for testing */
                //echo '<br>';
                //echo 'Page id: ' . $page;
                //echo '<br>';
                //echo $getContent;
                /** for testing */

                array_push( $contentArray, $getContent);
            }
            return $contentArray;
        }
    }

    /**
     * sds_disk_space
     * bool|float
     * @return bool|float
     * Description:
     */
    function sds_disk_space()
    {
        $space = disk_free_space(dirname(__FILE__));

        return $space;
    }

    /**
     * sds_total_disk_space
     * bool|float
     * @return bool|float
     * Description:
     */
    function sds_total_disk_space()
    {
        $space = disk_total_space(dirname(__FILE__));

        return $space;
    }

    /**
     * free_disk_meas
     * string
     * @return string
     * Description:
     */
    function free_disk_meas()
    {
        $space = disk_free_space(dirname(__FILE__));

        $Type=array("", "kilo", "mega", "giga", "tera", "peta", "exa", "zetta", "yotta");
        $Index=0;
        while($space>=1024)
        {
        $space/=1024;
        $Index++;
        }
        return("".$space." ".$Type[$Index]."bytes");
    }

    /**
     * start_editor
     * void
     * @param $content
     * Description:
     */
    function start_editor($headContent, $content)
    {
        if($content != NULL)
        {
            $contentString = implode("\t", $content);

            ?>
            <form method="post" action="<?php $_SERVER['REQUEST_URI']; ?>">
                <h2>Add Header Content</h2>

                    <textarea name="headContent" value="<?php $headContent ?>" placeholder="Add scripts and meta here"></textarea>


                <?php
                //sessions for subdomain array
                $SDS_subdomainArray = $_SESSION['subdomainArray'];
                echo 'Select a subdomain to add content to.';
                echo '<select name="subdomainOptions">';
                foreach($SDS_subdomainArray as $sub)
                {
                    echo '<option value="' . $sub . '">' . $sub . '</option>';
                }
                echo '</select>';

                wp_editor($contentString, 'editor');
            ?>

                <input type="hidden" name="create_edit_form_nonce" value="<?php echo wp_create_nonce('create_edit_form_nonce'); ?>" />

                    <br>
                    <input type="submit" value="submit">
                </form>
            <?php
        }
    }

    //Last closing bracket below
}