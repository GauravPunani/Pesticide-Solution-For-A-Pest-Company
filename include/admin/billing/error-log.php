<?php

global $wpdb;

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$limit =10;
$offset = ($pageno-1) * $limit; 


list($logs,$total_rows)=(new Autobilling)->get_mini_statement_log($offset,$limit,'error_sending_email');
$total_pages = ceil($total_rows / $limit);

$upload_dir=wp_upload_dir();
// echo '<pre>';print_r($upload_dir);wp_die();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3>Search For Records</h3>
                </div>
                <div class="card-body">
                <form class="form-inline" action="<?= $_SERVER['REQUEST_URI']; ?>">
                    <div class="form-group">
                        <input type="hidden" name="page"  value="<?= $_GET['page']; ?>">
                        <input type="hidden" name="tab"  value="<?= isset($_GET['tab']) ? urlencode($_GET['tab']) : 'all'; ?>">    
                        <input type="text" class="form-control" name="search" id="search_box">
                        <label for="email"><button class="btn btn-primary"><span><i class="fa fa-search"></i> Search</span></button></label>
                    </div>
                </form>    
                </div>
            </div>
        </div>
        <div class="col-sm-12">
        <?php if(isset($_GET['search'])): ?>
            <p class="alert alert-success alert-dismissible">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page='.$_GET['page'].'&tab='.$_GET['tab']); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
            </p>
        <?php endif; ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Phone No.</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Date Sent</th>
                        <th>Mini Statment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(is_array($logs) && count($logs)>0): ?>
                        <?php foreach($logs as $key=>$val): ?>
                            <tr>
                                <td><?= $val->client_name; ?></td>
                                <td><?= $val->phone_no; ?></td>
                                <td><?= $val->address; ?></td>
                                <td><?= $val->email; ?></td>
                                <td>$<?= $val->amount; ?></td>
                                <td><?= date('d M Y',strtotime($val->date_created)); ?></td>
                                <td><a href="<?= $upload_dir['baseurl'].$val->pdf_path; ?>" target="_blank">View Mini Statement</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No Record Found</td>
                            </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
