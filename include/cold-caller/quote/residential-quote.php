<?php
    
    global $wpdb;
    
    $whereSearch="";

    if(isset($_GET['search'])){
    
        $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'quotesheet');
        $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'where');  //genereate where query string
    
    }
    else{
        $whereSearch="";
    }
    

    if(isset($_GET['quote_id']) && !empty($_GET['quote_id'])){
        require_once "residential-quote-details.php";
        return;
    }

    if (isset($_GET['pageno'])) {
        $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }

    $no_of_records_per_page = 10;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    
    
    $total_pages_sql = "select COUNT(*) from  {$wpdb->prefix}quotesheet $whereSearch";
    
    $total_rows= $wpdb->get_var($total_pages_sql);
    
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    
    $result = $wpdb->get_results("select * from {$wpdb->prefix}quotesheet $whereSearch order by date DESC LIMIT $offset, $no_of_records_per_page ");

?>
<div class="table-responsive">
        <div class="row">
            <h3 class="text-center">Residential Quotes</h3>
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
        </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone No</th>
                <th>Email</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if( is_array($result) && !empty($result)): ?>
                <?php foreach($result as $key=>$val): ?>
                    <tr>
                        <td><?= $val->clientName; ?></td>
                        <td><?= $val->clientPhn; ?></td>
                        <td><?= $val->clientEmail; ?></td>
                        <td><?= $val->date; ?></td>
                        <td><a href="<?= $_SERVER['REQUEST_URI']; ?>&quote_id=<?= $val->id; ?>">View Quote</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
