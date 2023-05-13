<?php

global $wpdb;

$branches=(new Branches)->getAllBranches();

$pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

$conditions=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches();
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" location IN ($accessible_branches)";
}

if(isset($_GET['location']) && !empty($_GET['location']))
    $conditions[]=" location='{$_GET['location']}'";

if(isset($_GET['week']) && !empty($_GET['week']))
    $conditions[]=" week='{$_GET['week']}'";

$conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page;
$total_rows= $wpdb->get_var("
    select COUNT(*)
    from {$wpdb->prefix}weekly_reports 
    $conditions
");
$total_pages = ceil($total_rows / $no_of_records_per_page);

$reports=$wpdb->get_results("
    select * from
    {$wpdb->prefix}weekly_reports 
    $conditions 
    order by date DESC 
    LIMIT $offset, $no_of_records_per_page
");
$upload_dir=wp_upload_dir();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php (new GamFunctions)->getFlashMessage(); ?>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <form action="">
                        <h4 class="text-center">Filter Reports</h4>
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                        <!-- location  -->
                        <div class="form-group">
                            <label for="">Select Location</label>
                            <select name="location" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <?php foreach($branches as $location): ?>
                                        <?php if($location->slug=="upstate"){continue;} ?>
                                        <option value="<?= $location->slug; ?>"><?= $location->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- week  -->
                        <div class="form-group">
                            <label for="">Select Week</label>
                            <input type="week" name="week" class="form-control" max="<?= date('Y-\WW'); ?>">
                        </div>
                        
                        <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Reports</button>
                    </form>

                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="card-header">
                        <?php (new GamFunctions)->getFlashMessage(); ?>
                    </div>
                    
                    <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                        <h4 class="text-center">Generate Alert Report</h4>
                        <input type="hidden" name="action" value="generate_weekly_alert_report">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <!-- location  -->
                        <div class="form-group">
                            <label for="">Select Location</label>
                            <select name="location" class="form-control select2-field" required>
                                <option value="">Select</option>
                                <?php if(current_user_can('other_than_upstate')): ?>
                                    <option value="all_branches">All Branches</option>
                                <?php endif; ?>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <?php foreach($branches as $location): ?>
                                        <?php if($location->slug=="upstate"){continue;} ?>
                                        <option value="<?= $location->slug; ?>"><?= $location->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- week  -->
                        <div class="form-group">
                            <label for="">Select Week</label>
                            <input type="week" name="week" class="form-control" max="<?= date('Y-\WW'); ?>" required>
                        </div>
                        
                        <button class="btn btn-success"><span><i class="fa fa-download"></i></span> Download PDF</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>File</th>
                                <th>Week</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($reports) && count($reports)>0): ?>
                                <?php foreach($reports as $report): ?>
                                    <tr>
                                        <td><?= (new GamFunctions)->beautify_string($report->location); ?></td>
                                        <td><a target="_blank" href="<?= $upload_dir['baseurl']."/".$report->file_path; ?>" class="btn btn-primary"><span><i class="fa fa-eye"></i> View</span></a></td>
                                        <?php list($start_date,$end_date)=(new GamFunctions)->get_google_ads_week_dates($report->week); ?>
                                        <td><?= date('d M Y',strtotime($start_date))." to ".date('d M Y',strtotime($end_date)) ?></td>
                                        <td><?= date('d M Y',strtotime($report->date)); ?></td>
                                        <td><button onclick="deleteFile('<?= $report->id; ?>',this)" class="btn btn-danger"><span><i class="fa fa-trash"></i></span></button></td>
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

function deleteFile(report_id,ref){
    if(confirm('Are you sure you want to delte this file ?')){
        jQuery.ajax({
            type:"post",
            url:"<?= admin_url('admin-ajax.php'); ?>",
            data:{
                action:"delete_ad_report",
                report_id:report_id
            },
            dataType:"json",
            success:function(data){
                if(data.status=="success"){
                    jQuery(ref).parent().parent().fadeOut();
                }
                else{
                    alert(data.message);
                }
            }
        })
    }
}

</script>