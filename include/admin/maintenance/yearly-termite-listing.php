<?php

global $wpdb;

if(isset($_GET['contract-id']) && !empty($_GET['contract-id'])){
    $contract=$wpdb->get_row("select * from {$wpdb->prefix}yearly_termite_contract where id='{$_GET['contract-id']}'");
    if($contract){
        get_template_part("/include/admin/maintenance/yearly-termite-contract",null,['data'=>$contract]);
        return;
    }
}

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page = 50;
$offset = ($pageno-1) * $no_of_records_per_page;
$total_pages_sql = "select count(*) from {$wpdb->prefix}yearly_termite_contract";
$total_rows= $wpdb->get_var($total_pages_sql);    
$total_pages = ceil($total_rows / $no_of_records_per_page);


$contracts=$wpdb->get_results("select id,name,address,phone_no,pdf_path from {$wpdb->prefix}yearly_termite_contract LIMIT $offset, $no_of_records_per_page");

$upload_dir=wp_upload_dir();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="page-header text-center">Yearly Termite Contracts</h3>
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone No.</th>
                        <th>PDF Link</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(is_array($contracts) && count($contracts)>0): ?>
                        <?php foreach($contracts as $contract): ?>
                            <tr>
                                <td><?= $contract->name; ?></td>
                                <td><?= $contract->address; ?></td>
                                <td><?= $contract->phone_no; ?></td>
                                <?php if(!empty($contract->pdf_path)): ?>
                                    <td><a target="_blank" class="btn btn-primary" href="<?= $upload_dir['baseurl']."/".$contract->pdf_path; ?>"><span><i class="fa fa-file-pdf-o"></i></span> View</a></td>
                                <?php else: ?>
                                    <td class="text-danger">Not Available</td>
                                <?php endif; ?>
                                <td><a href="<?= $_SERVER['REQUEST_URI']; ?>&contract-id=<?= $contract->id; ?>" class="btn btn-primary"><span><i class="fa fa-eye"></i></span> View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
        </div>
    </div>
</div>