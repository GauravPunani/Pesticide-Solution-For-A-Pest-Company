<?php
global $wpdb;
	$whereSearch="";

    if(isset($_GET['search'])){
    
        $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'leads');
        $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'and');  //genereate where query string
    
    }
    else{
        $whereSearch="";
    }
	if (isset($_GET['pageno'])) {
        $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }

    $no_of_records_per_page = 10;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    
    
    $total_pages_sql = "select COUNT(*) from  {$wpdb->prefix}leads $whereSearch";
    
    $total_rows= $wpdb->get_var($total_pages_sql);
    
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    
    


$leads=$wpdb->get_results("select * from {$wpdb->prefix}leads WHERE cold_caller_id='{$_SESSION['cold_caller_id']}' $whereSearch order by date DESC LIMIT $offset, $no_of_records_per_page
");

?>
<div class="table-responsive">
    <div class="row">
			<div class="col-md-3">
                <form action="<?= $_SERVER['REQUEST_URI']; ?>">
                    <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                    <div class="form-group">
                        <label for="">Search Records</label>
                        <input type="text" name="search" value="<?= @$_GET['search']; ?>" class="form-control">
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                </form>
            </div>
            <div class="col-md-9">
                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <p class="alert alert-success alert-dismissible">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=<?= $_GET['view']; ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
                    </p>
                <?php endif; ?>
            </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Establishment Name</th>
							    <th>Name</th>
                                <th>Email</th>
                                <th>Phone No.</th>
                                <th>Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($leads) && count($leads)>0): ?>
                                <?php foreach($leads as $lead): ?>
                                    <tr>
									    <td><?= $lead->establishment_name; ?></td>
									    <td><?= $lead->name; ?></td>
                                        <td><?= $lead->email; ?></td>
                                        <td><?= $lead->phone; ?></td>
                                        <td><?= $lead->address; ?></td>
                                        <td><?= date('d M Y',strtotime($lead->date)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No Lead Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
</div>
