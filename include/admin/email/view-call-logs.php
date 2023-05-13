<?php
    global $wpdb;
    $conditions=[];
        
    
    if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
        $conditions[]=" branch_id ='{$_GET['branch_id']}'";
    }
    
    if (isset($_GET['pageno'])) {
        $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }
    
    
    if(count($conditions)>0){
        $conditions=(new GamFunctions)->generate_query($conditions);
    }
    else{
        $conditions="";
    }
    
    
    $no_of_records_per_page =50;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    $total_rows = $wpdb->get_var("
        select count(*)
        from {$wpdb->prefix}cold_calls_log
        $conditions
    ");

    // print_r($total_rows);

    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $coldcalls = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}cold_calls_log
        LEFT JOIN {$wpdb->prefix}emails
        ON {$wpdb->prefix}cold_calls_log.cold_call_id = {$wpdb->prefix}emails.id ORDER BY {$wpdb->prefix}emails.id desc"
    );

?>

<style>

.card {
    max-width: 100%!important;
}
</style>
<?php if($coldcalls): ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card">  
                <div class="card-body">
                    <h3 class="card-title">Cold Calls Logs <small>(<?= $total_rows; ?> results found)</small></h3>
                    <table id="myTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email Id</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Description </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($coldcalls) && count($coldcalls)>0): ?>
                                <?php foreach($coldcalls as $coldcall): ?>
                                    <tr>
                                        <td><?= $coldcall->name; ?></td>
                                        <td><?= $coldcall->email; ?></td>
                                        <td><?= $coldcall->phone; ?></td>
                                        <td><?= $coldcall->cold_date; ?></td>
                                        <td><?= $coldcall->description; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">No Record Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>                   
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
    <h3 class="text-center text-danger">No Cold Call Found</h3>
<?php endif; ?>