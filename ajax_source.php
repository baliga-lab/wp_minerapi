<?php

/**
 * AJAX backend.
 */
function completions_callback() {
    header("Content-type: application/json");
    $term = $_POST['term'];
    $source_url = get_option('source_url', '');
    $comps_json = file_get_contents($source_url . "/completions/" . $term);
    echo $comps_json;
    wp_die();
}

function bicluster_exps_dt_callback() {
    header("Content-type: application/json");
    $bicluster = $_GET['bicluster'];
    $source_url = get_option('source_url', '');
    $exps_json = file_get_contents($source_url . "/bicluster_expressions/" . rawurlencode($bicluster));
    $exps = json_decode($exps_json);
    $data = json_encode($exps->data);
    $doc = <<<EOT
{
  "data": $data
}
EOT;
    echo $doc;
    wp_die();
}

function bicluster_enrichment_dt_callback() {
    header("Content-type: application/json");
    $bicluster = $_GET['bicluster'];
    $source_url = get_option('source_url', '');
    $exps_json = file_get_contents($source_url . "/bicluster_enrichment/" . rawurlencode($bicluster));
    $exps = json_decode($exps_json);
    $conditions = json_encode($exps->conditions);
    $expdata = array();
    foreach ($exps->expressions as $gene => $values) {
        $expdata []= (object) array('name' => $gene, 'data' => $values);
    }
    $data = json_encode($expdata);

    $doc = <<<EOT
{
  "conditions": $conditions,
  "expressions": $data
}
EOT;
    echo $doc;
    wp_die();
}

function minerapi_ajax_source_init()
{
    // a hook Javascript to anchor our AJAX call
    wp_enqueue_script('ajax_dt', plugins_url('js/ajax_dt.js', __FILE__), array('jquery'));
    wp_localize_script('ajax_dt', 'ajax_dt', array('ajax_url' => admin_url('admin-ajax.php')), '1.0', true);

    // We need callbacks for both non-privileged and privileged users
    add_action('wp_ajax_nopriv_completions', 'completions_callback');
    add_action('wp_ajax_completions', 'completions_callback');

    add_action('wp_ajax_nopriv_bicluster_exps_dt', 'bicluster_exps_dt_callback');
    add_action('wp_ajax_bicluster_exps_dt', 'bicluster_exps_dt_callback');

    add_action('wp_ajax_nopriv_bicluster_enrichment_dt', 'bicluster_enrichment_dt_callback');
    add_action('wp_ajax_bicluster_enrichment_dt', 'bicluster_enrichment_dt_callback');
}

?>
