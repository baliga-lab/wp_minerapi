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
    $content .= "    <tr><td>" . $summary->num_causal_flows . "</td><td>CM Flows</td></tr>";
    $content .= "    <tr><td>" . $summary->num_trans_programs . "</td><td>Transcriptional Programs</td></tr>";
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

/*
 * TODO: Add information from EnsEMBL and Uniprot
 *
 * Example call to EnsEMBL
 * https://rest.ensembl.org/lookup/id/ENSG00000214900?content-type=application/json;expand=1
 *
 * XREF to Uniprot
 * https://rest.ensembl.org/xrefs/id/ENSG00000181991?content-type=application/json
 */
function regulon_genes_shortcode($attr, $content=null)
{
    $regulon_name = get_query_var('regulon');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/regulon/" .
                                     rawurlencode($regulon_name));
    $entries = json_decode($result_json)->genes;
    $content = "<a name=\"genes\"></a>";
    $content .= "<ul style=\"list-style: none\">";
    foreach ($entries as $e) {
        $content .= "  <li style=\"display: inline\"><a href=\"index.php/gene?gene=" . $e . "\">" . $e . "</a></li>";
    }
    $content .= "</ul>";
    return $content;
}

/*
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
*/

/*
 * CAUSAL FLOW RELATED SHORT CODES
 */

function render_causalflows_table($result_json, $table_id, $title)
{
    $entries = json_decode($result_json)->cm_flows;
    $content = "";
    $content .= "<h3>$title</h3>";
    $content .= "<table id=\"" . $table_id . "\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>ID</th><th>Mutation</th><th>Role</th><th>Regulator</th><th>Role</th><th>Regulon</th><th># downstream regulons</th><th># diffexp regulons</th><th>Drugs</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($entries as $idx=>$e) {
        $mutgen = $e->mutation_gene_symbol ? $e->mutation_gene_symbol : $e->mutation_gene_ensembl;
        $mutation = ($e->pathway) ? $e->pathway : $mutgen;
        $drugs = implode(', ', $e->drugs);
        $num_drugs = count($e->drugs);

        $content .= "    <tr><td>" . $e->cmf_id .
		 "</td><td><a href=\"index.php/mutation/?mutation=" .
		 $e->mutation . "\">" . $mutation . "</a></td><td>" .
		 $e->mutation_role . "</td><td>" .
		 "<a href=\"index.php/regulator/?regulator=" . $e->regulator . "\">" .
		 $e->regulator_preferred . "</a></td><td>" .
		 $e->regulator_role . "</td><td>" .
         "<a href=\"index.php/regulon/?regulon=" . $e->regulon . "\">" .
		 $e->regulon . "</a></td><td>" .
		 $e->num_downstream_regulons . "</td><td>" .
		 $e->num_diffexp_regulons . "</td><td>" .
         "<a href=\"#coll_$idx\" data-toggle=\"collapse\" aria-expanded=\"false\" aria-controls=\"help\"><i class=\"fas fa-info-circle pull-right\"></i></a><div class=\"collapse\" id=\"coll_$idx\"><div class=\"card card-body\"><p class=\"card-text\"><h4>Regulator Drugs ($num_drugs)</h4><p>$drugs</p></div>" .
		 "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#" . $table_id . "').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}


function regulon_causalflows_shortcode($attr, $content=null)
{
    $regulon = get_query_var('regulon');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causalflows_for_regulon/" .
                                     rawurlencode($regulon));
    return render_causalflows_table($result_json, "regulon_cmf",
                                    "Causal Mechanistic Flows for Regulon <b>$regulon</b>");
}

function _regulator_causalflows_shortcode($query_var)
{
    $regulator = get_query_var($query_var);
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causalflows_for_regulator/" .
                                     rawurlencode($regulator));
    return render_causalflows_table($result_json, "regulator_cmf",
                                    "Causal Mechanistic Flows with <b>$regulator</b> as Regulator");
}

function regulator_causalflows_shortcode($attr, $content=null)
{
    return _regulator_causalflows_shortcode('regulator');
}

function program_causalflows_shortcode($attr, $content=null)
{
    $program = get_query_var('program');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causalflows_for_program/" .
                                     rawurlencode($program));
    return render_causalflows_table($result_json, "program_cmf",
                                    "Causal Mechanistic Flows with Program <b>$program</b>");
}

function mutation_causalflows_shortcode($attr, $content=null)
{
    $mutation = get_query_var('mutation');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causalflows_for_mutation/" .
                                     rawurlencode($mutation));
    return render_causalflows_table($result_json, "mutation_cmf",
                                    "Causal Mechanistic Flows regulated by Mutation <b>$mutation</b>");
}

/*
 * SEARCH RELATED SHORT CODES
 */
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

function search_regulator_causalflows_shortcode($attr, $content=null)
{
    return _regulator_causalflows_shortcode('search_term');
}

function search_gene_mutation_causalflows_shortcode($attr, $content=null)
{
    $search_term = get_query_var('search_term');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causalflows_with_mutation_in/" .
                                     rawurlencode($search_term));
    return render_causalflows_table($result_json, "mutation_gene_cmf",
                                    "Causal Mechanistic Flows with Mutation in <b>$search_term</b>");
}

function search_regulon_gene_causalflows_shortcode($attr, $content=null)
{
    $search_term = get_query_var('search_term');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/causalflows_for_regulons_containing/" .
                                     rawurlencode($search_term));
    return render_causalflows_table($result_json, "regulon_gene_cmf",
                                    "Causal Mechanistic Flows with Regulons containing <b>$search_term</b>");
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
    if ($gene_info->preferred != null) {
        $preferred_name = $gene_info->preferred;
    } else {
        $preferred_name = $gene_name;
        $gene_info->preferred = '-';
    }
    if ($gene_info->entrez_id == null) { $entrez_link = '-'; }
    else {
        $entrez_link = "<a href=\"https://www.ncbi.nlm.nih.gov/gene/?term=" . $gene_info->entrez_id . "\" target=\"_blank\">" . $gene_info->entrez_id . "</a>";
    }

    $desc = preg_replace('/\[.*\]/', '', $gene_info->description);
    $content .= "<h3>" . $preferred_name . " - " . $desc;
    $content .= "</h3>";
    $content .= "<a href=\"index.php/gene-uniprot/?gene=" . $gene_name . "\">" . "Uniprot Browser" . "</a>";
    $content .= "<table>";
    $content .= "  <thead>";
    $content .= "    <tr><th>Entrez ID</th><th>EnsEMBL ID</th><th>Preferred Name</th><th>Uniprot ID</th></tr>";
    $content .= "  </thead>";
    $content .= "  <tbody>";
    $content .= "    <tr>";
    $content .= "      <td>" . $entrez_link . "</td>";
    $content .= "      <td><a href=\"http://www.ensembl.org/id/" . $gene_info->ensembl_id . "\" target=\"_blank\">" . $gene_info->ensembl_id . "</a></td>";
    $content .= "      <td>" . $gene_info->preferred . "</td>";
    $content .= "      <td><a href=\"https://www.uniprot.org/uniprot/" . $gene_info->uniprot_id . "\" target=\"_blank\">" . $gene_info->uniprot_id . "</a></td>";
    $content .= "    </tr><tr>";
    //$content .= "      <td colspan=\"4\"><b>Function:</b> " . $gene_info->function . "</td>";
    $content .= "    </tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
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

function regulon_summary_shortcode($attr, $content)
{
    $regulon_name = get_query_var('regulon');
    $source_url = get_option('source_url', '');
    $result_json = file_get_contents($source_url . "/regulon/" .
                                     rawurlencode($regulon_name));
    $result = json_decode($result_json);
    $num_genes = count($result->genes);
    $num_regulators = count($result->regulon_regulators);
    $drugs = implode(', ', $result->drugs);

    $content = "";
    $content .= "<table id=\"summary1\" class=\"row-border\" style=\"margin-bottom: 10px\">";
    $content .= "  <thead><tr><th>Genes</th><th>Cox Hazard Ratio</th><th>Regulators</th><th>Causal Flows</th><th>Transcriptional Programs</th><th>Drugs</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td><a href=\"#genes\">$num_genes</a></td><td>$result->hazard_ratio</td><td><a href=\"#regulators\">$num_regulators</a></td><td>$result->num_causal_flows</td><td><a href=\"index.php/program/?program=" . $result->program . "\">" . $result->program . "</a></td><td>$drugs</td></tr>";
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

function regulon_name_shortcode($attr, $content)
{
    $regulon_name = get_query_var('regulon');
    return $regulon_name;
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
    $content .= "      window.location.href = 'index.php/regulon/?regulon=' + bcName;";
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

function regulator_causalflows_cytoscape_shortcode($attr, $content)
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

    $content .= "        { selector: '.regulon', style: { 'background-color': 'red', 'shape': 'square'}},";
    $content .= "        { selector: '.regulator', style: { 'background-color': 'blue', 'shape': 'triangle'}},";
    $content .= "        { selector: '.mutation', style: { 'background-color': '#eb008b', 'shape': 'polygon', 'shape-polygon-points': '-1 -1 0 -0.45 1 -1 0 1'}}";
    $content .= "      ],";
    $content .= "      layout: { name: 'dagre' },";
    $content .= "      elements: " . json_encode(json_decode($result_json));
    $content .= "    });";
    $content .= "    cy.on('tap', '.regulon', function (e) {";
    $content .= "      window.location.href = 'index.php/regulon/?regulon=' + this.data('name');";
    $content .= "    });";
    $content .= "    cy.on('tap', '.mutation', function (e) {";
    $content .= "      window.location.href = 'index.php/mutation/?mutation=' + this.data('name');";
    $content .= "    });";
    $content .= "    cy.on('tap', '.regulator', function (e) {";
    $content .= "      window.location.href = 'index.php/regulator/?regulator=' + this.data('name');";
    $content .= "    });";
    $content .= "  });";
    $content .= "</script>";
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
        if ($g->preferred) $preferred = $g->preferred;
        else if ($g->ensembl_id) $preferred = $g->ensembl_id;
        else $preferred = $g->entrez_id;
        array_push($ens_genes, "<a href=\"index.php/gene/?gene=$preferred\">$preferred</a>");
    }
    $genes = implode(", ", $ens_genes);
    $regulon_links = array();
    foreach ($info->regulons as $r) {
        $regulon = $r->name;
        array_push($regulon_links, "<a href=\"index.php/regulon/?regulon=$regulon\">$regulon</a>");
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


function program_cmflow_summary_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $program = get_query_var('program');
    $program_num = explode("-", $program)[1];
    $prog = implode("-", ["PR", $program_num]);
    $static_url = get_option('static_url', '');
    $html_url = $static_url . "/Program_Enrichment_Summaries/" . rawurlencode($prog) . "_CNFlow_summary.html";

    // check if available, otherwise return nothing
    $file_headers = @get_headers($html_url);
    if (!$file_headers || strtoupper($file_headers[0]) == 'HTTP/1.1 404 NOT FOUND'
        || strtoupper($file_headers[0]) == 'HTTP/1.1 400 BAD REQUEST') {
        return "<p>CMFlow summary not available</p>";
    }
    else {
        $content = "<iframe src=\"$html_url\" style=\"width: 100%; height: 280px\"></iframe>";
        return $content;
    }
}


function program_enrichment_summary_pdf_shortcode($attr, $content=null)
{
    $source_url = get_option('source_url', '');
    $program = get_query_var('program');
    $program_num = explode("-", $program)[1];
    $prog = implode("-", ["PR", $program_num]);
    $static_url = get_option('static_url', '');
    $pdf_url = $static_url . "/Program_Enrichment_Summaries/" . rawurlencode($prog) . "_enrichment_summary.pdf";

    // check if available, otherwise return nothing
    $file_headers = @get_headers($pdf_url);
    if (!$file_headers || strtoupper($file_headers[0]) == 'HTTP/1.1 404 NOT FOUND'
        || strtoupper($file_headers[0]) == 'HTTP/1.1 400 BAD REQUEST') {
        return "<p>Enrichment summary not available</p>";
    }
    else {
        $content = "<iframe src=\"$pdf_url\" style=\"width: 100%; height: 280px\"></iframe>";
        return $content;
    }
}


function minerapi_add_shortcodes()
{
    add_shortcode('summary', 'summary_shortcode');

    // Regulon short codes
    add_shortcode('regulon_genes', 'regulon_genes_shortcode');
    add_shortcode('regulon_causalflows', 'regulon_causalflows_shortcode');
    add_shortcode('regulator_causalflows', 'regulator_causalflows_shortcode');
    add_shortcode('program_causalflows', 'program_causalflows_shortcode');
    add_shortcode('mutation_causalflows', 'mutation_causalflows_shortcode');
    add_shortcode('regulon_name', 'regulon_name_shortcode');
    add_shortcode('regulon_summary', 'regulon_summary_shortcode');
    add_shortcode('regulator_info', 'regulator_info_shortcode');

    // Program related short codes
    add_shortcode('program_regulon_table', 'program_regulon_table_shortcode');
    add_shortcode('program_gene_table', 'program_gene_table_shortcode');
    add_shortcode('program_info', 'program_info_shortcode');
    add_shortcode('program_cmflow_summary', 'program_cmflow_summary_shortcode');
    add_shortcode('program_enrichment_summary_pdf', 'program_enrichment_summary_pdf_shortcode');

    // Gene related short codes
    add_shortcode('gene_info', 'gene_info_shortcode');
    add_shortcode('gene_uniprot', 'gene_uniprot_shortcode');

    // Search related short codes
    add_shortcode('minerapi_search_box', 'search_box_shortcode');
    add_shortcode('search_regulator_causalflows', 'search_regulator_causalflows_shortcode');
    add_shortcode('search_gene_mutation_causalflows', 'search_gene_mutation_causalflows_shortcode');
    add_shortcode('search_regulon_gene_causalflows', 'search_regulon_gene_causalflows_shortcode');

    // Cytoscape related short codes
    add_shortcode('regulator_causalflows_cytoscape', 'regulator_causalflows_cytoscape_shortcode');

    // OLD short codes
    /*
    add_shortcode('bicluster_tfs_table', 'bicluster_tfs_table_shortcode');
    add_shortcode('gene_biclusters_table', 'gene_biclusters_table_shortcode');
    add_shortcode('bicluster_cytoscape', 'bicluster_cytoscape_shortcode');
    add_shortcode('bicluster_expressions', 'bicluster_expressions_graph_shortcode');
    add_shortcode('bicluster_enrichment', 'bicluster_enrichment_graph_shortcode');
    add_shortcode('bicluster_hallmarks', 'bicluster_hallmarks_shortcode');

    add_shortcode('regulator_survival_plot', 'regulator_survival_plot_shortcode');
    add_shortcode('bicluster_survival_plot', 'bicluster_survival_plot_shortcode');
    add_shortcode('mutation_survival_plot', 'mutation_survival_plot_shortcode');
    add_shortcode('program_survival_plot', 'program_survival_plot_shortcode');

    add_shortcode('causal_flow_table', 'causal_flow_table_shortcode');
    add_shortcode('causal_flow_cytoscape', 'causal_flow_cytoscape_shortcode');
    add_shortcode('causal_flow_mutation_cytoscape', 'causal_flow_mutation_cytoscape_shortcode');
    */
}

?>
