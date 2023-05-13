<?php

global $wpdb;

$upload_dir=wp_upload_dir();
$conditions=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" T.branch_id IN ($accessible_branches)";
}

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
    $conditions[]=" T.branch_id='{$_GET['branch_id']}' ";
}

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var("
    select count(*)
    from {$wpdb->prefix}termite_graph G
    left join {$wpdb->prefix}technician_details T
    on G.technician_id=T.id
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$termite_graph=$wpdb->get_results("
    select G.*, T.first_name, T.last_name
    from {$wpdb->prefix}termite_graph G
    left join {$wpdb->prefix}technician_details T
    on G.technician_id=T.id 
    $conditions
    order by DATE(G.date_created) DESC
    LIMIT $offset, $no_of_records_per_page
");
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Termite Graph</h3>
                    <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Client Name</th>
                                <th>Client Email</th>
                                <th>PDF</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($termite_graph) && count($termite_graph)>0): ?>
                                <?php foreach($termite_graph as $graph): ?>
                                    <tr>
                                        <td><?= $graph->first_name." ".$graph->last_name; ?></td>
                                        <td><?= $graph->client_name; ?></td>
                                        <td><?= $graph->client_email; ?></td>
                                        <td><a target="_blank" class="btn btn-primary" href="<?= $upload_dir['baseurl'].$graph->termite_graph; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <td><?= !empty($graph->date_created) ? date('d M Y',strtotime($graph->date_created)) : ''; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){

        })
    })(jQuery);
</script>