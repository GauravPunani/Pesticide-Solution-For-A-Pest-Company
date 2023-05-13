<?php

$conditions=[];
$where_query="";

if(isset($_GET['alert_type']) && !empty($_GET['alert_type'])){
    $conditions[]="type='{$_GET['alert_type']}'";
}
if(isset($_GET['from_date']) && !empty($_GET['from_date'])){
    $conditions[]="DATE(date_created) >= '{$_GET['from_date']}'";
}
if(isset($_GET['to_date']) && !empty($_GET['to_date'])){
    $conditions[]="DATE(date_created) <= '{$_GET['to_date']}'";
}

if(count($conditions)>0){
    for($i=0;$i < count($conditions); $i++){
        if($i==0){
            $where_query.=" where $conditions[$i] ";
        }
        else{
            $where_query.=" and $conditions[$i] ";

        }
    }
}

// echo $where_query;wp_die();

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 

$total_pages_sql = "select COUNT(*) as total_rows from {$wpdb->prefix}notices $where_query";

$total_rows= $wpdb->get_row($total_pages_sql);

$total_pages = ceil($total_rows->total_rows / $no_of_records_per_page);



$alerts=$wpdb->get_results("select * from {$wpdb->prefix}notices $where_query order by date_created DESC LIMIT $offset, $no_of_records_per_page");

$types=$wpdb->get_results("select type from {$wpdb->prefix}notices GROUP BY `type`");

?>
<div class="container">
    <div class="row">
        <h3 class="text-center">Alert History</h3>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center"><b>Filter Data</b></h4>
                    <form action="">
                        <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                        <div class="form-group">
                            <label for="">Select Type</label>
                            <select name="alert_type" class="form-control">
                                <option value="">Select</option>
                                <?php if(is_array($types) && count($types)>0): ?>
                                    <?php foreach($types as $type): ?>
                                        <option value="<?= $type->type; ?>" <?= (isset($_GET['alert_type']) && $_GET['alert_type']==$type->type) ? 'selected' : ""; ?>><?= (new GamFunctions)->beautify_string($type->type); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="<?= (isset($_GET['from_date']) && !empty($_GET['from_date'])) ? date('Y-m-d',strtotime($_GET['from_date'])) : ""; ?>">
                        </div>
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="<?= (isset($_GET['to_date']) && !empty($_GET['to_date'])) ? date('Y-m-d',strtotime($_GET['to_date'])) : ""; ?>">
                        </div>
                        <button class="btn btn-primary"><span class="i fa fa-filter"></span> Filter Alerts</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="text-center">All Alerts</h3>
                    <?php if(is_array($alerts) && count($alerts)>0): ?>
                        <?php foreach($alerts as $alert): ?>
                            <div class="notice notice-<?= $alert->class; ?>">
                                <p><?= $alert->notice; ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>

                </div>
            </div>
        </div>
    </div>
</div>