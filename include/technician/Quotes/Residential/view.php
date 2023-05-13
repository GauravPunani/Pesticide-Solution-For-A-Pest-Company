<?php
    
    if(!empty($_GET['quote_id'])) return require_once "details.php";

    global $wpdb;
    
    $user = $args['user'];

    $whereSearch="";

    if(!empty($_GET['search'])){
        $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'quotesheet');
        $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'and');
    }
    else{
        $whereSearch="";
    }

    $pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
    $no_of_records_per_page = 10;
    $offset = ($pageno-1) * $no_of_records_per_page;
    $total_rows= $wpdb->get_var("
        select COUNT(*)
        from  {$wpdb->prefix}quotesheet
        where branch_id = '$user->branch_id'
        and technician_id='$user->id' 
        $whereSearch
    ");
    
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    
    $result = $wpdb->get_results("
        select *
        from {$wpdb->prefix}quotesheet
        where branch_id = '$user->branch_id'
        and technician_id='$user->id'
        $whereSearch 
        order by date DESC LIMIT $offset, $no_of_records_per_page
    ");

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
                <?php if(!empty($_GET['search'])): ?>
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
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span><i class="fa fa-ellipsis-v"></i></span></button>
                                <ul class="custom-dropdown dropdown-menu dropdown-menu-left">
                                    <li><a href="<?= $_SERVER['REQUEST_URI']; ?>&quote_id=<?= $val->id; ?>""><span><i class="fa fa-eye"></i></span> View</a></li>
                                    <li><a onclick="downloadResidentialQuote(<?= $val->id; ?>)" href="javascript:void(0)"><span><i class="fa fa-download"></i></span> Download</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>

<form id="downloadQuoteForm" method="post" action="<?= admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('download_quote_by_technician'); ?>
    <input type="hidden" name="action" value="download_quote_by_technician">
    <input type="hidden" name="quote_id">
    <input type="hidden" name="quote_type" value="residential">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
</form>

<script>
    const downloadResidentialQuote = (quote_id) => {
        document.querySelector('#downloadQuoteForm input[name="quote_id"]').value = quote_id;
        document.getElementById('downloadQuoteForm').submit();
    }
</script>