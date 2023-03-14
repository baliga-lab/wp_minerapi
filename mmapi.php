<?php
/**
 * @package minerapi
 * @version 1.01
 */
/*
Plugin Name: wp-minerapi
Plugin URI: https://github.com/baliga-lab/wp-minerapi
Description: A plugin that pulls in information from a MINER webapi service
Author: Wei-ju Wu
Version: 1.0
Author URI: http://www.systemsbiology.org
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

/**********************************************************************
 * Settings Section
 * Users provide and store information about the web service and
 * structure of their web site here
 **********************************************************************/

function minerapi_settings_init() {

    // This is the General section
    add_settings_section(
        "general_section",
        "MINERAPI",
        "general_section_cb",
        'general'  // general, writing, reading, discussion, media, privacy, permalink
    );
    add_settings_field('source_url', 'Data Source URL', 'source_url_field_cb', 'general',
                       'general_section');
    add_settings_field('static_url', 'Static Data URL', 'static_url_field_cb', 'general',
                       'general_section');
    add_settings_field('minerapi_slug', 'MINERAPI Slug', 'slug_field_cb', 'general',
                       'general_section');

    register_setting('general', 'source_url');
    register_setting('general', 'static_url');
    register_setting('general', 'minerapi_slug');
}

function general_section_cb()
{
    echo "<p>General settings for the MINERAPI Plugin</p>";
}

function source_url_field_cb()
{
    $url = get_option('source_url', '');
    echo "<input type=\"text\" name=\"source_url\" value=\"" . $url . "\">";
}

function static_url_field_cb()
{
    $url = get_option('static_url', '');
    echo "<input type=\"text\" name=\"static_url\" value=\"" . $url . "\">";
}

function slug_field_cb()
{
    $slug = get_option('minerapi_slug', 'minerapi');
    echo "<input type=\"text\" name=\"minerapi_slug\" value=\"" . $slug . "\">";
}

/**********************************************************************
 * Plugin Section
 **********************************************************************/

require_once('short_codes.php');
require_once('ajax_source.php');

/*
 * Custom variables that are supposed to be used must be made
 * available explicitly through the filter mechanism.
 */
function add_query_vars_filter($vars) {
    $vars[] = "regulon";
    $vars[] = "regulator";
    $vars[] = "mutation";
    $vars[] = "gene";
    $vars[] = "search_term";
    $vars[] = "program";
    $vars[] = "drug";
    return $vars;
}

function minerapi_init()
{
    // add all javascript and style files that are used by our plugin
    wp_register_style('uniprot_viewer_css', 'https://ebi-uniprot.github.io/CDN/protvista/css/main.css');
    wp_enqueue_style('uniprot_viewer_css');
    wp_enqueue_style('jquery-ui', plugin_dir_url(__FILE__) . 'css/jquery-ui.css');
    wp_enqueue_style('datatables', plugin_dir_url(__FILE__) . 'css/jquery.dataTables.min.css');
    wp_enqueue_style('wp-minerapi', plugin_dir_url(__FILE__) . 'css/wp-minerapi.css');
    wp_enqueue_style('qtip', plugin_dir_url(__FILE__) . 'css/jquery.qtip.min.css', null, false, false);

    wp_register_script('uniprot_viewer', 'https://ebi-uniprot.github.io/CDN/protvista/protvista.js');
    wp_enqueue_script('uniprot_viewer');

    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('d3', plugin_dir_url(__FILE__) . 'js/d3.min.js', array('jquery'));
    wp_enqueue_script('datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', array('jquery'));
    wp_enqueue_script('qtip', plugin_dir_url(__FILE__) . 'js/jquery.qtip.min.js', array('jquery'), false, true);
    wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . 'js/highcharts.js', array('jquery'));
    wp_enqueue_script('highcharts-more', plugin_dir_url(__FILE__) . 'js/highcharts-more.js', array('jquery'));
    wp_enqueue_script('histogram-bellcurve', plugin_dir_url(__FILE__) . 'js/histogram-bellcurve.js', array('jquery'));
    wp_enqueue_script('cytoscape', plugin_dir_url(__FILE__) . 'js/cytoscape.min.js');
    wp_enqueue_script('dagre', plugin_dir_url(__FILE__) . 'js/dagre.min.js');
    wp_enqueue_script('cytoscape-dagre', plugin_dir_url(__FILE__) . 'js/cytoscape-dagre.js');
    wp_enqueue_script('cytoscape-cose-bilkent', plugin_dir_url(__FILE__) . 'js/cytoscape-cose-bilkent.js');

    minerapi_add_shortcodes();
    minerapi_ajax_source_init();
    add_filter('query_vars', 'add_query_vars_filter');
}

function search_minerapi()
{
    $search_term = $_POST['search_term'];
    $source_url = get_option('source_url', '');

    // Program search -> we just redirect to the Program page
    if (strpos($search_term, 'P-') === 0) {
        // Short circuit program search results
        $page = get_page_by_path('program');
        wp_safe_redirect(get_permalink($page->ID) . "?program=" . rawurlencode($search_term));
        exit;
    }

    // ask search API if there are results for this term and what type
    $result_json = file_get_contents($source_url . "/cfsearch/" .
                                     rawurlencode($search_term));
    $result = json_decode($result_json);

    // Short circuit search results
    $page = get_page_by_path('search-results');
    wp_safe_redirect(get_permalink($page->ID) . "?search_term=" . rawurlencode($search_term));
    exit;

    /*
    if ($result->found == "no") {
        $page = get_page_by_path('no-search-results-found');
        wp_safe_redirect(get_permalink($page->ID) . "?search_term=" . rawurlencode($search_term));
        exit;
    } else {
        $page = get_page_by_path('search-results');
        wp_safe_redirect(get_permalink($page->ID) . "?search_term=" . rawurlencode($search_term));
        exit;
        }*/
}

add_action('admin_init', 'minerapi_settings_init');
add_action('init', 'minerapi_init');
add_action('admin_post_nopriv_search_minerapi', 'search_minerapi');
add_action('admin_post_search_minerapi', 'search_minerapi');

?>
