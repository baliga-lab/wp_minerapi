<?php

/**********************************************************************
 * Custom Short codes
 * Render the custom fields by interfacting with the web service
 **********************************************************************/

function summary_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    error_log("in summary code");
    $summary_json = file_get_contents($source_url . "/summary");
    $summary = json_decode($summary_json);
    $content = "<h2>Model Overview</h2>";
    $content .= "<table id=\"summary\" class=\"row-border\">";
    $content .= "  <thead><tr><th>#</th><th>Description</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td>" . $summary->num_biclusters . "</td><td>Regulons</td></tr>";
    $content .= "    <tr><td>" . $summary->num_mutations . "</td><td>Mutations</td></tr>";
    $content .= "    <tr><td>" . $summary->num_regulators . "</td><td>Regulators</td></tr>";
    //$content .= "    <tr><td>" . $summary->num_causal_flows . "</td><td>CM Flows</td></tr>";
    $content .= "    <tr><td>" . $summary->num_trans_programs . "</td><td>Transcriptional Programs</td></tr>";
    $content .= "    <tr><td> 881 </td><td>Patients</td></tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";

    $content .= "    jQuery('#summary').DataTable({";
    $content .= "      'paging': false,";
    $content .= "      'info': false,";
    $content .= "      'searching': false";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    //$content = "<b>Summary2</b>";
    return $content;
}


function mutation_table_shortcode($attr, $content=null)
{
    $mutation_name = get_query_var('mutation');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/mutation/" .
                                     rawurlencode($mutation_name));
    $entries = json_decode($result_json)->entries;

    $content = "";
    $content .= "<h3>Causal Mechanistic Flows for Mutation <i>" . $mutation_name . "</i></h3>";
    $content .= "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Regulator</th><th>Role</th><th>Regulon</th><th>Cox Hazard Ratio (Regulon)</th><th>Transcriptional Programs</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $idx=>$e) {
        $prog_json = json_decode(file_get_contents($source_url . "/program/" . $e->trans_program));
        // build gene links
        $ens_genes = array();
        foreach ($prog_json->genes as $g) {
            $preferred = $g->preferred;
            if (strlen($preferred) > 0) {
                array_push($ens_genes, "<a href=\"index.php/gene-biclusters/?gene=$preferred\">$preferred</a>");
            }
        }
        $num_genes = $prog_json->num_genes;
        $num_regulons = $prog_json->num_regulons;
        $genes = implode(", ", $ens_genes);
        // build regulon links
        $regulon_links = array();
        foreach ($prog_json->regulons as $r) {
            $regulon_id = $r->name;
            array_push($regulon_links, "<a href=\"index.php/bicluster/?bicluster=$regulon_id\">$regulon_id</a>");
        }
        $regulons = implode(", ", $regulon_links);

        $content .= "    <tr><td><a href=\"index.php/regulator/?regulator=" . $e->regulator . "\">" . $e->regulator_preferred . "</a></td><td class=\"$e->role\">" . $e->role . "</td><td><a href=\"index.php/bicluster/?bicluster=" . $e->bicluster . "\">" . $e->bicluster . "</a></td><td>$e->bc_cox_hazard_ratio</td><td><a href=\"index.php/program/?program=" . $e->trans_program . "\">Pr-" . $e->trans_program . "</a>  <a href=\"#coll_$idx\" data-toggle=\"collapse\" aria-expanded=\"false\" aria-controls=\"help\"><i class=\"fas fa-info-circle pull-right\"></i></a><div class=\"collapse\" id=\"coll_$idx\"><div class=\"card card-body\"><p class=\"card-text\"><h4>Genes ($num_genes)</h4><p>$genes</p><h4>Regulons ($num_regulons)</h4><p>$regulons</p></td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#biclusters').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}


function regulator_table_shortcode($attr, $content=null)
{
    $regulator_name = get_query_var('regulator');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/regulator/" .
                                     rawurlencode($regulator_name));
    $result = json_decode($result_json);
    $entries = $result->entries;
    $content = "";
    $content = "<h3>Causal Mechanistic Flows for Regulator: " . $result->regulator_preferred . "</h3>";
    $content .= "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Mutation</th><th>Regulator</th><th>Role</th><th>Regulon</th><th>Cox Hazard Ratio</th><th>Transcriptional Program</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $idx=>$e) {
        $prog_json = json_decode(file_get_contents($source_url . "/program/" . $e->trans_program));
        // build gene links
        $ens_genes = array();
        foreach ($prog_json->genes as $g) {
            $preferred = $g->preferred;
            if (strlen($preferred) > 0) {
                array_push($ens_genes, "<a href=\"index.php/gene-biclusters/?gene=$preferred\">$preferred</a>");
            }
        }
        $num_genes = $prog_json->num_genes;
        $num_regulons = $prog_json->num_regulons;
        $genes = implode(", ", $ens_genes);
        // build regulon links
        $regulon_links = array();
        foreach ($prog_json->regulons as $r) {
            $regulon_id = $r->name;
            array_push($regulon_links, "<a href=\"index.php/bicluster/?bicluster=$regulon_id\">$regulon_id</a>");
        }
        $regulons = implode(", ", $regulon_links);

        $content .= "    <tr><td><a href=\"index.php/mutation/?mutation=" . $e->mutation . "\">" . $e->mutation . "</a></td><td>$result->regulator_preferred</td><td class=\"$e->role\">" . $e->role . "</td><td><a href=\"index.php/bicluster/?bicluster=" . $e->bicluster . "\">" .
                 $e->bicluster . "</a></td><td>" . $e->hazard_ratio  . "</td><td><a href=\"index.php/program/?program=" . $e->trans_program . "\">Pr-$e->trans_program</a>   <a href=\"#coll_$idx\" data-toggle=\"collapse\" aria-expanded=\"false\" aria-controls=\"help\"><i class=\"fas fa-info-circle pull-right\"></i></a><div class=\"collapse\" id=\"coll_$idx\"><div class=\"card card-body\"><p class=\"card-text\"><h4>Genes ($num_genes)</h4><p>$genes</p><h4>Regulons ($num_regulons)</h4><p>$regulons</p>  </td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#biclusters').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

/*
 * TODO: Add information from EnsEMBL and Uniprot
 *
 * Example call to EnsEMBL
 * https://rest.ensembl.org/lookup/id/ENSG00000214900?content-type=application/json;expand=1
 *
 * XREF to Uniprot
 * https://rest.ensembl.org/xrefs/id/ENSG00000181991?content-type=application/json
 */
function bicluster_genes_table_shortcode($attr, $content=null)
{
    $bicluster_name = get_query_var('bicluster');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/bicluster/" .
                                     rawurlencode($bicluster_name));
    $entries = json_decode($result_json)->genes;
    $content = "<a name=\"genes\"></a>";
    //$content .= "<h3>Genes for regulon " . $bicluster_name . "</h3>";
    $content .= "<ul style=\"list-style: none\">";
    foreach ($entries as $e) {
        $content .= "  <li style=\"display: inline\"><a href=\"index.php/gene-biclusters?gene=" . $e . "\">" . $e . "</a></li>";
    }
    $content .= "</ul>";
    return $content;
}

function bicluster_tfs_table_shortcode($attr, $content=null)
{
    $bicluster_name = get_query_var('bicluster');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/bicluster/" .
                                     rawurlencode($bicluster_name));
    $entries = json_decode($result_json)->tfs_bc;
    $content = "<a name=\"regulators\"></a>";
    //$content .= "<h3>Regulators for regulon " . $bicluster_name . "</h3>";
    $content .= "<table id=\"bc_tfs\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Regulator</th><th>Role</th><th>Cox Hazard Ratio</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $e) {
        $content .= "    <tr><td><a href=\"index.php/regulator/?regulator=" . $e->tf . "\">" . $e->tf_preferred .
                 "</a></td><td>" . $e->role . "</td><td>" . $e->hazard_ratio .  "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#bc_tfs').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function bicluster_mutation_tfs_table_shortcode($attr, $content=null)
{
    $bicluster_name = get_query_var('bicluster');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/bicluster/" .
                                     rawurlencode($bicluster_name));
    $entries = json_decode($result_json)->mutations_tfs;
    $content = "";
    //$content = "<h3>Mutations - Regulators for regulon " . $bicluster_name . "</h3>";
    $content .= "<table id=\"bc_mutations_tfs\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Mutation</th><th>Role</th><th>Regulator</th><th>Role</th><th>Regulon</th><th>Hazard Ratio</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $e) {
        $content .= "    <tr><td><a href=\"index.php/mutation/?mutation=" . $e->mutation . "\">" . $e->mutation . "</a></td><td>" . $e->mutation_role . "</td><td><a href=\"index.php/regulator/?regulator=" . $e->tf . "\">" . $e->tf_preferred . "</a></td><td>" . $e->regulator_role . "</td><td>" . $bicluster_name . "</td><td>" . $e->hazard_ratio . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#bc_mutations_tfs').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function bicluster_patients_table_shortcode($attr, $content=null)
{
    $bicluster_name = get_query_var('bicluster');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/bicluster_patients/" .
                                     rawurlencode($bicluster_name));
    $entries = json_decode($result_json)->data;
    $content .= "<table id=\"bc_patients\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Patient</th><th>Survival</th><th>Survival Status</th><th>Sex</th><th>Age</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $e) {
        $content .= "    <tr><td>$e->name</td><td>$e->survival</td><td>$e->survival_status</td><td>$e->sex</td><td>$e->age</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#bc_patients').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}


function search_box_shortcode($attr, $content)
{
    $ajax_action = "completions";
    $content = "<form action=\"" . esc_url(admin_url('admin-post.php')) .  "\" method=\"post\">";
    $content .= "Search Term: ";
    $content .= "<div><input name=\"search_term\" type=\"text\" id=\"minerapi-search\"></input><input type=\"submit\" value=\"Search\" id=\"minerapi-search-button\"></input></div>";
    $content .= "<input type=\"hidden\" name=\"action\" value=\"search_minerapi\">";
    $content .= "</form>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#minerapi-search').autocomplete({";
    $content .= "      source: function(request, response) {";
    $content .= "                jQuery.ajax({ url: ajax_dt.ajax_url, type: 'POST', data: { action: '" . $ajax_action . "', term: request.term }, success: function(data) { response(data.completions) }});";
    $content .= "              },";
    $content .= "      minLength: 2";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function search_results_shortcode($attr, $content)
{
    $search_term = $_GET['search_term'];
    $content = "<div>Search Term: " . $search_term . "</div>";
    $result_json = file_get_contents($source_url . "/search/" .
                                     rawurlencode($search_term));
    $result = json_decode($result_json);
    if ($result->found == "no") {
        $content .= "<div>no entries found</div>";
    } else {
        $content .= "<div>yes, entries found, type: " . $result->data_type .  "</div>";
    }
    return $content;
}

function no_search_results_shortcode($attr, $content)
{
    $search_term = $_GET['search_term'];
    $content .= "<p>The search term '$search_term' did not yield any results.</p>";
    return $content;
}

function bicluster_cytoscape_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/bicluster_network/" .
                                     rawurlencode($bicluster_name));
    $content = "";
    $content .= "<div id=\"cytoscape\"><h3>Causal Mechanistic Flow Network</h3></div>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    var cy = cytoscape({";
    $content .= "      container: jQuery('#cytoscape'),";
    $content .= "      style: [";
    $content .= "        { selector: 'node', style: { label: 'data(id)'}},";
    # make these edges colorful 1!!
    $content .= "        { selector: 'edge', style: { 'line-color': '#000', 'target-arrow-shape': 'triangle', 'target-arrow-color': '#000', 'opacity': 0.8, 'curve-style': 'bezier'}},";
    $content .= "        { selector: '.bicluster', style: { 'background-color': 'red', 'shape': 'square'}},";
    $content .= "        { selector: '.tf', style: { 'background-color': 'blue', 'shape': 'triangle'}},";
    $content .= "        { selector: '.mutation', style: { 'background-color': '#eb008b', 'shape': 'polygon', 'shape-polygon-points': '-1 -1 0 -0.45 1 -1 0 1'}},";
    $content .= "        { selector: '.activates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.represses', style: { 'line-color': 'green', 'opacity': 0.5}},";
    $content .= "        { selector: '.up_regulates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.down_regulates', style: { 'line-color': 'green', 'opacity': 0.5}},";
    $content .= "      ],";
    $content .= "      layout: { name: 'dagre' },";
    $content .= "      elements: " . json_encode(json_decode($result_json)->elements);
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function gene_biclusters_table_shortcode($attr, $content=null)
{
    $gene_name = get_query_var('gene');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/biclusters_for_gene/" .
                                     rawurlencode($gene_name));
    $entries = json_decode($result_json)->biclusters;
    $content = "";
    //$content = "<h3>Regulons for gene " . $gene_name . "</h3>";
    $content .= "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Regulon</th><th>Survival (Hazard Ratio)</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $e) {
        $content .= "    <tr><td><a href=\"index.php/bicluster/?bicluster=" . $e->cluster_id . "\">" . $e->cluster_id . "</a></td><td>$e->hazard_ratio</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#biclusters').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function gene_info_table($gene_name)
{
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/gene_info/" .
                                     rawurlencode($gene_name));
    $gene_info = json_decode($result_json);
    $content = "";
    if ($gene_info->preferred == 'NA') {
        return $content;
    }
    $desc = preg_replace('/\[.*\]/', '', $gene_info->description);
    $content .= "<h3>" . $gene_info->preferred . " - " . $desc;
    $content .= "</h3>";
    $content .= "<a href=\"index.php/gene-uniprot/?gene=" . $gene_name . "\">" . "Uniprot Browser" . "</a>";
    $content .= "<table>";
    $content .= "  <thead>";
    $content .= "    <tr><th>Entrez ID</th><th>EnsEMBL ID</th><th>Preferred Name</th><th>Uniprot ID</th></tr>";
    $content .= "  </thead>";
    $content .= "  <tbody>";
    $content .= "    <tr>";
    $content .= "      <td><a href=\"https://www.ncbi.nlm.nih.gov/gene/?term=" . $gene_info->entrez_id . "\" target=\"_blank\">" . $gene_info->entrez_id . "</a></td>";
    $content .= "      <td><a href=\"http://www.ensembl.org/id/" . $gene_info->ensembl_id . "\" target=\"_blank\">" . $gene_info->ensembl_id . "</a></td>";
    $content .= "      <td>" . $gene_info->preferred . "</td>";
    $content .= "      <td><a href=\"https://www.uniprot.org/uniprot/" . $gene_info->uniprot_id . "\" target=\"_blank\">" . $gene_info->uniprot_id . "</a></td>";
    $content .= "    </tr><tr>";
    //$content .= "      <td colspan=\"4\"><b>Function:</b> " . $gene_info->function . "</td>";
    $content .= "    </tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
    /*
    $content .= "<div><span class=\"entry-title\">Entrez ID: </span><span><a href=\"https://www.ncbi.nlm.nih.gov/gene/?term=" . $gene_info->entrez_id . "\" target=\"_blank\">" . $gene_info->entrez_id . "</a></span></div>";
    $content .= "<div><span class=\"entry-title\">Ensembl ID: </span><span><a href=\"http://www.ensembl.org/id/" . $gene_info->ensembl_id . "\" target=\"_blank\">" . $gene_info->ensembl_id . "</a></span></div>";
    $content .= "<div><span class=\"entry-title\">Preferred Name: </span><span>" . $gene_info->preferred . "</span></div>";


    $content .= "<div><span class=\"entry-title\">UniProt ID: </span><span><a href=\"https://www.uniprot.org/uniprot/" . $gene_info->uniprot_id . "\" target=\"_blank\">" . $gene_info->uniprot_id . "</a></span></div>";
    $content .= "<div><span class=\"entry-title\">Function: </span><span>" . $gene_info->function . "</span></div>";
    */
    $content .= "";
    return $content;
}

function gene_info_shortcode($attr, $content=null)
{
    return gene_info_table(get_query_var('gene'));
}

function regulator_info_shortcode($attr, $content=null)
{
    return gene_info_table(get_query_var('regulator'));
}

function gene_uniprot_shortcode($attr, $content=null)
{
    $gene_name = get_query_var('gene');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/gene_info/" .
                                     rawurlencode($gene_name));
    $gene_info = json_decode($result_json);
    $content = "";
    //$content .= "<h3>UniProtKB " . $gene_info->uniprot_id . "</h3>";
    $content .= "<div id=\"uniprot-viewer\"></div>";
    $content .= "  <script>";
    $content .= "    window.onload = function() {";
    $content .= "      var yourDiv = document.getElementById('uniprot-viewer');";
    $content .= "      var ProtVista = require('ProtVista');";
    $content .= "      var instance = new ProtVista({";
    $content .= "        el: yourDiv,";
    $content .= "        uniprotacc: '" . $gene_info->uniprot_id . "'";
    $content .= "      });";
    $content .= "    }";
    $content .= "  </script>";
    $content .= "";
    return $content;
}

function bicluster_summary_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/bicluster/" .
                                     rawurlencode($bicluster_name));
    $result = json_decode($result_json);
    $num_genes = count($result->genes);
    $num_regulators = count($result->tfs_bc);
    $drugs = implode(', ', $result->drugs);
    $moas = implode(', ', $result->mechanism_of_action);
    $hallmarks = implode(', ', $result->hallmarks);
    if (count($result->target_classes) > 0) {
        $target_class = $result->target_classes[0]->name;
        $target_class_pval = $result->target_classes[0]->pval;
    } else {
        $target_class = '';
        $target_class_pval = '';
    }

    $content = "";
    $content .= "<table id=\"summary1\" class=\"row-border\" style=\"margin-bottom: 10px\">";
    $content .= "  <thead><tr><th>Genes</th><th>Cox Hazard Ratio</th><th>Regulators</th><th>Causal Flows</th><th>Transcriptional Program</th><th>Drugs</th><th>Mechanism of Action</th><th>Hallmarks</th><th>Target Class</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td><a href=\"#genes\">$num_genes</a></td><td>$result->hazard_ratio</td><td><a href=\"#regulators\">$num_regulators</a></td><td>$result->num_causal_flows</td><td><a href=\"index.php/program/?program=" . $result->trans_program . "\">Pr-$result->trans_program</a></td><td>$drugs</td><td>$moas</td><td>$hallmarks</td><td>$target_class</td></tr>";
    $content .= "  </tbody>";
    $content .= "</table>";

    return $content;
}

function bicluster_expressions_graph_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');

    $source_url = get_option('source_url', '');
    $content .= '<div id="bicluster_exps" style="width: 100%; height: 300px"></div>';
    $content .= "<script>\n";
    $content .= "    function makeBiclusterExpChart(data) {";
    $content .= "      var x, chart = Highcharts.chart('bicluster_exps', {\n";
    $content .= "        chart: { type: 'boxplot' },";
    $content .= "        title: { text: 'Regulon Expression' },\n";
    $content .= "        xAxis: { title: { text: 'Conditions' }},\n";
    $content .= "        yAxis: { title: { text: 'Relative expression'} },\n";
    $content .= "        series: [{name: 'All', showInLegend: false, colorByPoint: true, data: data.data}]\n";
    $content .= "     })\n";
    $content .= "   }\n";

    $content .= "  function loadBiclusterExpressions() {\n";
    $content .= "    jQuery.ajax({\n";
    $content .= "      url: ajax_dt.ajax_url,\n";
    $content .= "      method: 'GET',\n";
    $content .= "      data: {'action': 'bicluster_exps_dt', 'bicluster': '" . $bicluster_name . "' }\n";
    $content .= "    }).done(function(data) {\n";
    $content .= "      makeBiclusterExpChart(data);\n";
    $content .= "    });\n";
    $content .= "  };\n";


    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    loadBiclusterExpressions();\n";
    $content .= "  });\n";
    $content .= "</script>\n";
    return $content;
}

function bicluster_name_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');
    return $bicluster_name;
}

function bicluster_enrichment_graph_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');

    $source_url = get_option('source_url', '');
    $content .= '<div id="bicluster_enrich" style="width: 100%; height: 300px"></div>';
    $content .= "<script>\n";
    $content .= "    function makeBiclusterEnrichmentChart(data, conds) {";
    $content .= "      var x, chart = Highcharts.chart('bicluster_enrich', {\n";
    $content .= "        chart: { type: 'column' },";
    $content .= "        title: { text: 'Enrichment of Tumor Subtypes in Quintiles (Example Data)' },\n";
    $content .= "        xAxis: { title: { text: 'Conditions' }, categories: conds,\n";
    $content .= "                 labels: {\n";
    $content .= "                   formatter: function() {\n";
    $content .= "                     return this.axis.categories.indexOf(this.value);\n";
    $content .= "                   }}},\n";
    $content .= "        yAxis: { title: { text: 'Enrichment of Subtypes in Quintiles'} },\n";
    $content .= "        series: data\n";
    $content .= "     })\n";
    $content .= "   }\n";

    $content .= "  function loadBiclusterEnrichment() {\n";
    $content .= "    jQuery.ajax({\n";
    $content .= "      url: ajax_dt.ajax_url,\n";
    $content .= "      method: 'GET',\n";
    $content .= "      data: {'action': 'bicluster_enrichment_dt', 'bicluster': '" . $bicluster_name . "' }\n";
    $content .= "    }).done(function(data) {\n";
    $content .= "      makeBiclusterEnrichmentChart(data.expressions, data.conditions);\n";
    $content .= "    });\n";
    $content .= "  };\n";


    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    loadBiclusterEnrichment();\n";
    $content .= "  });\n";
    $content .= "</script>\n";
    return $content;
}


function bicluster_hallmarks_shortcode($attr, $content)
{
    $content = "";
    $content = "<a name=\"hallmarks\"></a>";
    $content .= "<div style=\"width:100%\">";
    $content .= "<div style=\"width: 45%; display: inline-block; vertical-align: top\">";
    $content .= "  <b>Regulon is enriched for the following hallmarks of cancer</b>";
    $content .= "  <ul style=\"list-style: none\">";
    $content .= "    <li><img style=\"width: 20px\" src=\"" . esc_url(plugins_url('images/angiogenesis.gif', __FILE__)). "\"> Inducing angiogenesis</li>";
    $content .= "  </ul>";

    $content .= "</div>";
    $content .= "<div style=\"width: 50%; display: inline-block\">";
    $content .= "  <h4>Legend</h4>";
    $content .= "  <img src=\"" . esc_url(plugins_url('images/legend.jpg', __FILE__)). "\">";
    $content .= "</div>";
    $content .= "</div>";
    return $content;
}

function regulator_survival_plot_shortcode($attr, $content=null)
{
    $regulator_name = get_query_var('regulator');
    $static_url = get_option('static_url', '');
    $img_url = $static_url . "/survival_plots_tf/" . rawurlencode($regulator_name) . ".png";

    // check if available, otherwise return nothing
    $file_headers = @get_headers($img_url);
    if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found'
        || $file_headers[0] == 'HTTP/1.1 400 Bad Request') {
        return "<p>no survival information available</p>";
    }
    else {
        return "<img src=\"" . $img_url . "\"></img>";
    }
}

function bicluster_survival_plot_shortcode($attr, $content=null)
{
    $bicluster_name = get_query_var('bicluster');
    $rname = str_replace("R-", "regulon_", $bicluster_name) . "_survival";
    $static_url = get_option('static_url', '');
    // check if available, otherwise return nothing
    $img_url = $static_url . "/regulon_survival_plots/" . rawurlencode($rname) . ".png";
    $file_headers = @get_headers($img_url);
    if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found'
        || $file_headers[0] == 'HTTP/1.1 400 Bad Request') {
        return "<p>no survival information available</p>";
    }
    else {
        return "<img src=\"" . $img_url . "\"></img>";
    }
}

function mutation_survival_plot_shortcode($attr, $content=null)
{
    $mutation_name = get_query_var('mutation');
    $mname = "mutation_" . $mutation_name . "_survival";
    $static_url = get_option('static_url', '');
    // check if available, otherwise return nothing
    $img_url = $static_url . "/mutation_survival_plots/" . rawurlencode($mname) . ".png";
    $file_headers = @get_headers($img_url);
    if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found'
        || $file_headers[0] == 'HTTP/1.1 400 Bad Request') {
        return "<p>no survival information available</p>";
    }
    else {
        return "<img src=\"" . $img_url . "\"></img>";
    }
}


function program_survival_plot_shortcode($attr, $content=null)
{
    $program_name = get_query_var('program');
    $pname = "program_" . $program_name . "_survival";
    $static_url = get_option('static_url', '');
    // check if available, otherwise return nothing
    $img_url = $static_url . "/program_survival_plots/" . rawurlencode($pname) . ".png";
    $file_headers = @get_headers($img_url);
    if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found'
        || $file_headers[0] == 'HTTP/1.1 400 Bad Request') {
        return "<p>no survival information available</p>";
    }
    else {
        return "<img src=\"" . $img_url . "\"></img>";
    }
}


function patient_info_shortcode($attr, $content=null)
{
    $patient_name = get_query_var('patient');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/patient/" .
                                     rawurlencode($patient_name));
    $patient_info = json_decode($result_json);
    $content = "";
    $content .= "<table id=\"summary\" class=\"row-border\" style=\"margin-bottom: 10px\">";
    $content .= "  <thead><tr><th>Progression-free Survival</th><th>Survival Status</th><th>Sex</th><th>Age</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td>$patient_info->pfs_survival</td><td>$patient_info->pfs_status</td><td>$patient_info->sex</td><td>$patient_info->age</td></tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";

    $content .= "    jQuery('#summary').DataTable({";
    $content .= "      'paging': false,";
    $content .= "      'info': false,";
    $content .= "      'searching': false";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";

    return $content;
}

function patient_tf_activity_table_shortcode($attr, $content=null)
{
    $patient_name = get_query_var('patient');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/patient/" .
                                     rawurlencode($patient_name));
    $patient_info = json_decode($result_json);
    $entries = $patient_info->tf_activity;
    $content = "";
    $content = "<h3>Regulator Activity for Patient " . $patient_name . "</h3>";
    $content .= "<table id=\"tf_activity\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Regulator</th><th>Activity</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $e) {
        $content .= "    <tr><td><a href=\"index.php/regulator/?regulator=" . $e->tf . "\">" . $e->tf . "</a></td><td>$e->activity</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#tf_activity').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

/*
 * Generic code to generate causal flow table
 */
function add_causal_flow_table($content, $entries, $tableId) {
    $source_url = get_option('source_url', '');

    $content .= "<table id=\"$tableId\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Mutation</th><th>Role</th><th>Regulator</th><th>Role</th><th>Regulon</th><th>Hazard Ratio</th><th># regulon genes</th><th>Transcriptional Program</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $idx=>$e) {
        $prog_json = json_decode(file_get_contents($source_url . "/program/" . $e->trans_program));
        // build gene links
        $ens_genes = array();
        foreach ($prog_json->genes as $g) {
            $preferred = $g->preferred;
            if (strlen($preferred) > 0) {
                array_push($ens_genes, "<a href=\"index.php/gene-biclusters/?gene=$preferred\">$preferred</a>");
            }
        }
        $num_genes = $prog_json->num_genes;
        $num_regulons = $prog_json->num_regulons;
        $genes = implode(", ", $ens_genes);
        // build regulon links
        $regulon_links = array();
        foreach ($prog_json->regulons as $r) {
            $regulon_id = $r->name;
            array_push($regulon_links, "<a href=\"index.php/bicluster/?bicluster=$regulon_id\">$regulon_id</a>");
        }
        $regulons = implode(", ", $regulon_links);

        $content .= "    <tr><td><a href=\"index.php/mutation/?mutation=$e->mutation\">$e->mutation</a></td><td>$e->mutation_role</td>";
        $content .= "<td><a href=\"index.php/regulator/?regulator=$e->regulator\">$e->regulator_preferred</a></td><td>$e->regulator_role</td><td><a href=\"index.php/bicluster/?bicluster=$e->bicluster\">$e->bicluster</a></td>";
        $content .= "<td>$e->hazard_ratio</td>";
        $content .= "<td><a href=\"index.php/bicluster/?bicluster=$e->bicluster#genes\">$e->num_genes</a></td>";
        $content .= "<td><a href=\"index.php/program/?program=$e->trans_program\">Pr-$e->trans_program</a> <a href=\"#coll_$idx\" data-toggle=\"collapse\" aria-expanded=\"false\" aria-controls=\"help\"><i class=\"fas fa-info-circle pull-right\"></i></a><div class=\"collapse\" id=\"coll_$idx\"><div class=\"card card-body\"><p class=\"card-text\"><h4>Genes ($num_genes)</h4><p>$genes</p><h4>Regulons ($num_regulons)</h4><p>$regulons</p>  </td>";
        $content .= "</tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#" . $tableId . "').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function causal_flow_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causal_flow");
    $entries = json_decode($result_json)->entries;
    $content = "";
    $content = add_causal_flow_table($content, $entries, "causal_flow");
    return $content;
}

function causal_flow_cytoscape_shortcode($attr, $content)
{
    $static_url = get_option('static_url', '');
    $result_json = file_get_contents($static_url . "/mm_cytoscape.json");
    $content = "";
    $content .= "<div id=\"cytoscape2\"></div>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    var cy = cytoscape({";
    $content .= "      container: jQuery('#cytoscape2'),";
    $content .= "      style: [";
    $content .= "        { selector: 'node', style: { label: 'data(name)'}},";
    $content .= "        { selector: 'edge', style: { 'line-color': '#000', 'width': 3, 'opacity': 0.5}},";
    $content .= "        { selector: '.activates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.represses', style: { 'line-color': 'green', 'opacity': 0.5}},";
    $content .= "        { selector: '.up_regulates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.down_regulates', style: { 'line-color': 'green', 'opacity': 0.5}},";

    $content .= "        { selector: '.bicluster', style: { 'background-color': 'red', 'shape': 'square'}},";
    $content .= "        { selector: '.tf', style: { 'background-color': 'blue', 'shape': 'triangle'}},";
    $content .= "        { selector: '.mutation', style: { 'background-color': '#eb008b', 'shape': 'polygon', 'shape-polygon-points': '-1 -1 0 -0.45 1 -1 0 1'}}";
    $content .= "      ],";
    #$content .= "      layout: { name: 'cose-bilkent' },";
    $content .= "      layout: { name: 'dagre' },";
    $content .= "      elements: " . json_encode(json_decode($result_json));
    $content .= "    });";
    $content .= "    cy.on('tap', '.bicluster', function (e) {";
    $content .= "      var bcName = this.data('name');";
    $content .= "      window.location.href = 'index.php/bicluster/?bicluster=' + bcName;";
    $content .= "    });";
    $content .= "    cy.on('tap', '.mutation', function (e) {";
    $content .= "      var mutName = this.data('name');";
    $content .= "      window.location.href = 'index.php/mutation/?mutation=' + mutName;";
    $content .= "    });";
    $content .= "    cy.on('tap', '.tf', function (e) {";
    $content .= "      var tfName = this.data('name');";
    $content .= "      window.location.href = 'index.php/regulator/?regulator=' + tfName;";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function causal_flow_mutation_cytoscape_shortcode($attr, $content)
{
    $mutation_name = get_query_var('mutation');
    $static_url = get_option('static_url', '');
    $result_json = file_get_contents($static_url . "/cytoscape/mutations/" . $mutation_name . ".json");
    $content = "";
    $content .= "<div id=\"cytoscape_mutation\"></div>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    var cy = cytoscape({";
    $content .= "      container: jQuery('#cytoscape_mutation'),";
    $content .= "      style: [";
    $content .= "        { selector: 'node', style: { label: 'data(name)'}},";
    $content .= "        { selector: 'edge', style: { 'line-color': '#000', 'width': 3, 'opacity': 0.5}},";
    $content .= "        { selector: '.activates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.represses', style: { 'line-color': 'green', 'opacity': 0.5}},";
    $content .= "        { selector: '.up_regulates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.down_regulates', style: { 'line-color': 'green', 'opacity': 0.5}},";

    $content .= "        { selector: '.bicluster', style: { 'background-color': 'red', 'shape': 'square'}},";
    $content .= "        { selector: '.tf', style: { 'background-color': 'blue', 'shape': 'triangle'}},";
    $content .= "        { selector: '.mutation', style: { 'background-color': '#eb008b', 'shape': 'polygon', 'shape-polygon-points': '-1 -1 0 -0.45 1 -1 0 1'}}";
    $content .= "      ],";
    $content .= "      layout: { name: 'dagre' },";
    $content .= "      elements: " . json_encode(json_decode($result_json));
    $content .= "    });";
    $content .= "    cy.on('tap', '.bicluster', function (e) {";
    $content .= "      var bcName = this.data('name');";
    $content .= "      window.location.href = 'index.php/bicluster/?bicluster=' + bcName;";
    $content .= "    });";
    $content .= "    cy.on('tap', '.mutation', function (e) {";
    $content .= "      var mutName = this.data('name');";
    $content .= "      window.location.href = 'index.php/mutation/?mutation=' + mutName;";
    $content .= "    });";
    $content .= "    cy.on('tap', '.tf', function (e) {";
    $content .= "      var tfName = this.data('name');";
    $content .= "      window.location.href = 'index.php/regulator/?regulator=' + tfName;";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function causal_flow_regulator_cytoscape_shortcode($attr, $content)
{
    $regulator_name = get_query_var('regulator');
    $static_url = get_option('static_url', '');
    $result_json = file_get_contents($static_url . "/cytoscape/regulators/" . $regulator_name . ".json");
    $content = "";
    $content .= "<div id=\"cytoscape\"></div>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    var cy = cytoscape({";
    $content .= "      container: jQuery('#cytoscape'),";
    $content .= "      style: [";
    $content .= "        { selector: 'node', style: { label: 'data(name)'}},";
    $content .= "        { selector: 'edge', style: { 'line-color': '#000', 'width': 3, 'opacity': 0.5}},";
    $content .= "        { selector: '.activates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.represses', style: { 'line-color': 'green', 'opacity': 0.5}},";
    $content .= "        { selector: '.up_regulates', style: { 'line-color': 'red', 'opacity': 0.5}},";
    $content .= "        { selector: '.down_regulates', style: { 'line-color': 'green', 'opacity': 0.5}},";

    $content .= "        { selector: '.bicluster', style: { 'background-color': 'red', 'shape': 'square'}},";
    $content .= "        { selector: '.tf', style: { 'background-color': 'blue', 'shape': 'triangle'}},";
    $content .= "        { selector: '.mutation', style: { 'background-color': '#eb008b', 'shape': 'polygon', 'shape-polygon-points': '-1 -1 0 -0.45 1 -1 0 1'}}";
    $content .= "      ],";
    $content .= "      layout: { name: 'dagre' },";
    $content .= "      elements: " . json_encode(json_decode($result_json));
    $content .= "    });";
    $content .= "    cy.on('tap', '.bicluster', function (e) {";
    $content .= "      var bcName = this.data('name');";
    $content .= "      window.location.href = 'index.php/bicluster/?bicluster=' + bcName;";
    $content .= "    });";
    $content .= "    cy.on('tap', '.mutation', function (e) {";
    $content .= "      var mutName = this.data('name');";
    $content .= "      window.location.href = 'index.php/mutation/?mutation=' + mutName;";
    $content .= "    });";
    $content .= "    cy.on('tap', '.tf', function (e) {";
    $content .= "      var tfName = this.data('name');";
    $content .= "      window.location.href = 'index.php/regulator/?regulator=' + tfName;";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function bc_patient_survhisto_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');

    $source_url = get_option('source_url', '');
    $content .= '<div id="patient_survhisto" style="width: 100%; height: 300px"></div>';
    $content .= "<script>\n";
    $content .= "    function makePatientSurvHistoChart(data) {";
    $content .= "      var x, chart = Highcharts.chart('patient_survhisto', {\n";
    $content .= "        title: { text: 'Patient Survival' },\n";
    $content .= "        xAxis: [{ title: { text: 'Data' }, alignTicks: false }, { title: { text: 'Histogram' }, alignTicks: false, opposite: true }],";
    $content .= "       yAxis: [{ title: { text: 'Data' } }, { title: { text: 'Histogram' }, opposite: true }],";
    $content .= "        series: [{type: 'histogram', xAxis: 1, yAxis: 1, baseSeries: 's1'}, {id: 's1', type: 'scatter', data: data.data, marker: {radius: 1.5}}]\n";
    $content .= "     })\n";
    $content .= "   }\n";

    $content .= "  function loadPatientSurvivalHistogram() {\n";
    $content .= "    jQuery.ajax({\n";
    $content .= "      url: ajax_dt.ajax_url,\n";
    $content .= "      method: 'GET',\n";
    $content .= "      data: {'action': 'bicluster_survival_dt', 'bicluster': '" . $bicluster_name . "' }\n";
    $content .= "    }).done(function(data) {\n";
    $content .= "      makePatientSurvHistoChart(data);\n";
    $content .= "    });\n";
    $content .= "  };\n";
    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    loadPatientSurvivalHistogram();\n";
    $content .= "  });\n";
    $content .= "</script>\n";
    return $content;
}

function bc_patient_agehisto_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');

    $source_url = get_option('source_url', '');
    $content .= '<div id="patient_agehisto" style="width: 100%; height: 300px"></div>';
    $content .= "<script>\n";
    $content .= "    function makePatientAgeHistoChart(data) {";
    $content .= "      var chart = Highcharts.chart('patient_agehisto', {\n";
    $content .= "        title: { text: 'Patient Age' },\n";
    $content .= "        xAxis: [{ title: { text: 'Data' }, alignTicks: false }, { title: { text: 'Histogram' }, alignTicks: false, opposite: true }],";
    $content .= "       yAxis: [{ title: { text: 'Data' } }, { title: { text: 'Histogram' }, opposite: true }],";
    $content .= "        series: [{type: 'histogram', xAxis: 1, yAxis: 1, baseSeries: 's1'}, {id: 's1', type: 'scatter', data: data.data, marker: {radius: 1.5}}]\n";
    $content .= "     })\n";
    $content .= "   }\n";

    $content .= "  function loadPatientAgeHistogram() {\n";
    $content .= "    jQuery.ajax({\n";
    $content .= "      url: ajax_dt.ajax_url,\n";
    $content .= "      method: 'GET',\n";
    $content .= "      data: {'action': 'bicluster_ages_dt', 'bicluster': '" . $bicluster_name . "' }\n";
    $content .= "    }).done(function(data) {\n";
    $content .= "      makePatientAgeHistoChart(data);\n";
    $content .= "    });\n";
    $content .= "  };\n";
    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    loadPatientAgeHistogram();\n";
    $content .= "  });\n";
    $content .= "</script>\n";
    return $content;
}

function bc_patient_pie_shortcode($attr, $content)
{
    $bicluster_name = get_query_var('bicluster');

    $source_url = get_option('source_url', '');
    $content .= '<div><div id="patient_agepie" style="width: 30%; display: inline-block"></div><div id="patient_sexpie" style="width: 30%; display: inline-block"></div><div id="patient_survpie" style="width: 30%; display: inline-block"></div></div>';
    $content .= "<script>\n";
    $content .= "    function makePatientPieCharts(data) {";
    $content .= "      var chart1 = Highcharts.chart('patient_agepie', {\n";
    $content .= "        chart: { type: 'pie' },";
    $content .= "        title: { text: 'Patient Age' },\n";
    $content .= "        series: [{name: 'Ages', data: data.data.age}]\n";
    $content .= "     })\n";
    $content .= "      var chart2 = Highcharts.chart('patient_sexpie', {\n";
    $content .= "        chart: { type: 'pie' },";
    $content .= "        title: { text: 'Patient Sex' },\n";
    $content .= "        series: [{name: 'Sex', data: data.data.sex}]\n";
    $content .= "     })\n";
    $content .= "      var chart3 = Highcharts.chart('patient_survpie', {\n";
    $content .= "        chart: { type: 'pie' },";
    $content .= "        title: { text: 'Patient Survival' },\n";
    $content .= "        series: [{name: 'Survival', data: data.data.survival}]\n";
    $content .= "     })\n";
    $content .= "   }\n";

    $content .= "  function loadPatientPie() {\n";
    $content .= "    jQuery.ajax({\n";
    $content .= "      url: ajax_dt.ajax_url,\n";
    $content .= "      method: 'GET',\n";
    $content .= "      data: {'action': 'bicluster_patientstatus_dt', 'bicluster': '" . $bicluster_name . "' }\n";
    $content .= "    }).done(function(data) {\n";
    $content .= "       ";
    $content .= "      makePatientPieCharts(data);\n";
    $content .= "    });\n";
    $content .= "  };\n";
    $content .= "  jQuery(document).ready(function() {\n";
    $content .= "    loadPatientPie();\n";
    $content .= "  });\n";
    $content .= "</script>\n";
    return $content;
}


function mutation_causal_flow_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $search_term = get_query_var('search_term');
    $result_json = file_get_contents($source_url . "/cfsearch/" . $search_term);
    $entries = json_decode($result_json)->by_mutation;
    $content = "";
    $content .= "<h3>Causal Mechanistic Flows regulated by Mutation in <b>" . $search_term . "</b></h3>";
    if (count($entries) == 0) {
        $content .= "<p>No CM Flow results regulated by a mutation matched your query '$search_term'.</p>";
    } else {
        $content = add_causal_flow_table($content, $entries, "mut_causal_flow");
    }
    return $content;
}

function regulator_causal_flow_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $search_term = get_query_var('search_term');
    $result_json = file_get_contents($source_url . "/cfsearch/" . $search_term);
    $entries = json_decode($result_json)->by_regulator;
    $content = "";
    $content .= "<h3>Causal Mechanistic Flows with <b>" . $search_term . "</b> as Regulator</h3>";
    if (count($entries) == 0) {
        $content .= "<p>No CM Flow results regulated by a regulator matched your query '$search_term'.</p>";
    } else {
        $content = add_causal_flow_table($content, $entries, "reg_causal_flow");
    }
    return $content;
}


function reggenes_causal_flow_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $search_term = get_query_var('search_term');
    $result_json = file_get_contents($source_url . "/cfsearch/" . $search_term);
    $entries = json_decode($result_json)->by_reggenes;
    $content = "";
    $content .= "<h3>Causal Mechanistic Flows with regulons containing <b>" . $search_term . "</b></h3>";
    if (count($entries) == 0) {
        $content .= "<p>No CM Flow results contains genes matching your query '$search_term'.</p>";
    } else {
        $content = add_causal_flow_table($content, $entries, "rgg_causal_flow");
    }
    return $content;
}


function program_causal_flow_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $program = get_query_var('program');
    $result_json = file_get_contents($source_url . "/causal_flows_with_program/" . $program);
    $entries = json_decode($result_json)->entries;
    $content = "";
    $content .= "<h3>Causal Mechanistic Flows with regulons in program <b>" . $program . "</b></h3>";
    $content = add_causal_flow_table($content, $entries, "prog_causal_flow");
    return $content;
}

function program_regulon_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $program = get_query_var('program');
    $result_json = file_get_contents($source_url . "/program/" . $program);
    $regulons = json_decode($result_json)->regulons;
    $content = "";
    $content .= "<h3>Regulons in program <b>" . $program . "</b></h3>";
    $content .= "<table id=\"prog_regulons\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Regulon</th>";
    $content .= "  <th>Cox Hazard Ratio</th>";
    $content .= "  <th># genes</th>";
    $content .= "  <th># causal flows</th>";
    $content .= "</tr></thead>";
    $content .= "  <tbody>";
    foreach ($regulons as $r) {
        $content .= "    <tr><td><a href=\"index.php/bicluster/?bicluster=" . $r->name . "\">$r->name</a></td>";
        $content .= "<td>$r->cox_hazard_ratio</td>";
        $content .= "<td>$r->num_genes</td>";
        $content .= "<td>$r->num_causal_flows</td>";
        $content .= "</tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#prog_regulons').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function program_gene_table_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $program = get_query_var('program');
    $result_json = file_get_contents($source_url . "/program/" . $program);
    $genes = json_decode($result_json)->genes;
    $content = "";
    $content .= "<h3>Genes in program <b>" . $program . "</b></h3>";
    $content .= "<table id=\"prog_genes\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>EnsEMBL Id</th><th>Entrez Id</th><th>Preferred</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($genes as $g) {
        $content .= "    <tr><td><a href=\"http://www.ensembl.org/id/$g->ensembl_id\" target=\"_blank\">$g->ensembl_id</a></td>";
        $content .= "    <td><a href=\"https://www.ncbi.nlm.nih.gov/gene/?term=$g->entrez_id\" target=\"_blank\">$g->entrez_id</td>";
        $content .= "<td>$g->preferred</td>";
        $content .= "</tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#prog_genes').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function program_info_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $program = get_query_var('program');
    $result_json = file_get_contents($source_url . "/program/" . $program);
    $info = json_decode($result_json);
    $num_genes = $info->num_genes;
    $num_regulons = $info->num_regulons;
    $ens_genes = array();
    foreach ($info->genes as $g) {
        $preferred = $g->preferred;
        array_push($ens_genes, "<a href=\"index.php/gene-biclusters/?gene=$preferred\">$preferred</a>");
    }
    $genes = implode(", ", $ens_genes);
    $regulon_links = array();
    foreach ($info->regulons as $r) {
        $regulon_id = $r->name;
        array_push($regulon_links, "<a href=\"index.php/bicluster/?bicluster=$regulon_id\">$regulon_id</a>");
    }
    $regulons = implode(", ", $regulon_links);
    $content = "<h3><a href=\"#mutation_table\" data-toggle=\"collapse\" aria-expanded=\"false\" aria-controls=\"help\"><i class=\"fas fa-info-circle pull-right\"> $program</i> </a></h3>\n";
    $content .= "<div class=\"collapse\" id=\"mutation_table\">\n";
    $content .= "<div class=\"card card-body\">\n";
    $content .= "<div class=\"card-header\">\n";
    $content .= "<h2><a href=\"/program/?program=$program\">Program $program</a></h2>\n";
    $content .= "</div>\n";
    $content .= "<p class=\"card-text\"><h4>Genes ($num_genes)</h4>\n";
    $content .= "$genes";
    $content .= "</p>\n";
    $content .= "<p class=\"card-text\"><h4>Regulons ($num_regulons)</h4>\n";
    $content .= "$regulons";
    $content .= "</p>";
    $content .= "</div>\n";
    $content .= "</div>\n";

    return $content;
}

function minerapi_add_shortcodes()
{
    add_shortcode('summary', 'summary_shortcode');
    add_shortcode('mutation_table', 'mutation_table_shortcode');
    add_shortcode('regulator_table', 'regulator_table_shortcode');

    // bicluster page short codes
    add_shortcode('bicluster_genes_table', 'bicluster_genes_table_shortcode');
    add_shortcode('bicluster_patients_table', 'bicluster_patients_table_shortcode');
    add_shortcode('bicluster_tfs_table', 'bicluster_tfs_table_shortcode');
    add_shortcode('bicluster_mutation_tfs_table', 'bicluster_mutation_tfs_table_shortcode');

    add_shortcode('minerapi_search_box', 'search_box_shortcode');
    add_shortcode('minerapi_search_results', 'search_results_shortcode');
    add_shortcode('minerapi_no_search_results', 'no_search_results_shortcode');

    add_shortcode('gene_biclusters_table', 'gene_biclusters_table_shortcode');
    add_shortcode('gene_info', 'gene_info_shortcode');
    add_shortcode('regulator_info', 'regulator_info_shortcode');
    add_shortcode('gene_uniprot', 'gene_uniprot_shortcode');
    add_shortcode('bicluster_cytoscape', 'bicluster_cytoscape_shortcode');
    add_shortcode('bicluster_summary', 'bicluster_summary_shortcode');
    add_shortcode('bicluster_expressions', 'bicluster_expressions_graph_shortcode');
    add_shortcode('bicluster_enrichment', 'bicluster_enrichment_graph_shortcode');
    add_shortcode('bicluster_hallmarks', 'bicluster_hallmarks_shortcode');
    add_shortcode('bicluster_name', 'bicluster_name_shortcode');
    add_shortcode('bc_patient_survhisto', 'bc_patient_survhisto_shortcode');
    add_shortcode('bc_patient_agehisto', 'bc_patient_agehisto_shortcode');
    add_shortcode('bc_patient_pie', 'bc_patient_pie_shortcode');

    add_shortcode('regulator_survival_plot', 'regulator_survival_plot_shortcode');
    add_shortcode('bicluster_survival_plot', 'bicluster_survival_plot_shortcode');
    add_shortcode('mutation_survival_plot', 'mutation_survival_plot_shortcode');
    add_shortcode('program_survival_plot', 'program_survival_plot_shortcode');

    add_shortcode('patient_info', 'patient_info_shortcode');
    add_shortcode('patient_tf_activity_table', 'patient_tf_activity_table_shortcode');

    add_shortcode('causal_flow_table', 'causal_flow_table_shortcode');
    add_shortcode('causal_flow_cytoscape', 'causal_flow_cytoscape_shortcode');
    add_shortcode('causal_flow_mutation_cytoscape', 'causal_flow_mutation_cytoscape_shortcode');
    add_shortcode('causal_flow_regulator_cytoscape', 'causal_flow_regulator_cytoscape_shortcode');

    add_shortcode('mutation_causal_flow_table', 'mutation_causal_flow_table_shortcode');
    add_shortcode('regulator_causal_flow_table', 'regulator_causal_flow_table_shortcode');
    add_shortcode('reggenes_causal_flow_table', 'reggenes_causal_flow_table_shortcode');
    add_shortcode('program_causal_flow_table', 'program_causal_flow_table_shortcode');
    add_shortcode('program_regulon_table', 'program_regulon_table_shortcode');
    add_shortcode('program_gene_table', 'program_gene_table_shortcode');

    add_shortcode('program_info', 'program_info_shortcode');

}

?>
