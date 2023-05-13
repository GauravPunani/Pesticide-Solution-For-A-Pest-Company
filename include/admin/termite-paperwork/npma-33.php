<?php

if(isset($_GET['report-id']) && !empty($_GET['report-id'])){
    get_template_part('/include/admin/termite-paperwork/florida-wood-inspection-data',null,['id'=>$_GET['report-id']]);
    return;
}

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
    from {$wpdb->prefix}npma G
    left join {$wpdb->prefix}technician_details T
    on G.technician_id=T.id
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$florida_report=$wpdb->get_results("
    select N.form_type, N.id, N.technician_id, N.client_name, N.client_email, N.address_of_property, N.pdf_link, N.date_created, T.first_name, T.last_name
    from {$wpdb->prefix}npma N
    left join {$wpdb->prefix}technician_details T
    on N.technician_id=T.id 
    where N.form_type ='npma_form' 
    order by DATE(N.date_created) DESC
    LIMIT $offset, $no_of_records_per_page
");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">NPMA 33</h3>
                    <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Client Name</th>
                                <th>Client Email</th>
                                <th>Address Of property</th>
                                <th>PDF</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($florida_report) && count($florida_report)>0): ?>
                                <?php foreach($florida_report as $report): ?>
                                    <tr>
                                        <td><?= $report->first_name." ".$report->last_name; ?></td>
                                        <td><?= $report->client_name; ?></td>
                                        <td><?= $report->client_email; ?></td>
                                        <td><?= $report->address_of_property; ?></td>
                                        <td><a target="_blank" class="btn btn-primary" href="<?= $upload_dir['baseurl'].$report->pdf_link; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <td><?= !empty($report->date_created) ? date('d M Y',strtotime($report->date_created)) : ''; ?></td>
                                        <th><a class="btn btn-primary" href="<?= $_SERVER['REQUEST_URI']; ?>&report-id=<?= $report->id; ?>"><span><i class="fa fa-eye"></i></span> View Data</a></th>
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