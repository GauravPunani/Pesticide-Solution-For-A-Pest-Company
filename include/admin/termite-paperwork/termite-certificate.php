<?php

global $wpdb;

$upload_dir=wp_upload_dir();
$conditions=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches(true);
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";
    $conditions[]=" T.branch_id IN ($accessible_branches)";
}

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all")
    $conditions[]=" T.branch_id='{$_GET['branch_id']}' ";

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_pages_sql = "
    select count(*)
    from {$wpdb->prefix}certificate C
    left join {$wpdb->prefix}technician_details T
    on C.technician_id=T.id
    $conditions
";

$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$certificates=$wpdb->get_results("
    select C.*,T.first_name,T.last_name
    from {$wpdb->prefix}certificate C
    left join {$wpdb->prefix}technician_details T
    on C.technician_id=T.id
    $conditions 
    order by DATE(C.date_created) DESC
    LIMIT $offset, $no_of_records_per_page
");
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Termite Certificates <small>(<?= $total_rows ?> Records Found)</small></h3>
                    <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Client Name</th>
                                <th>Client Email</th>
                                <th>Building Address</th>
                                <th>Date of Treatement</th>
                                <th>Method of Treatement</th>
                                <th>PDF</th>
                                <th>Tech Sign.</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($certificates) && count($certificates)>0): ?>
                                <?php foreach($certificates as $certificate): ?>
                                    <tr>
                                        <td><?= $certificate->first_name." ".$certificate->last_name; ?></td>
                                        <td><?= $certificate->client_name; ?></td>
                                        <td><?= $certificate->client_email; ?></td>
                                        <td><?= $certificate->building_address; ?></td>
                                        <td><?= !empty($certificate->date_of_treatement) ? date('d M Y',strtotime($certificate->date_of_treatement)) : ''; ?></td>
                                        <td><?= $certificate->method_of_treatement; ?></td>
                                        <td><a target="_blank" class="btn btn-primary" href="<?= $upload_dir['baseurl'].$certificate->certificate_pdf; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <td><a target="_blank" class="btn btn-primary" href="<?= $upload_dir['baseurl'].$certificate->tech_sign; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                        <td><?= !empty($certificate->date_created) ? date('d M Y',strtotime($certificate->date_created)) : ''; ?></td>
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