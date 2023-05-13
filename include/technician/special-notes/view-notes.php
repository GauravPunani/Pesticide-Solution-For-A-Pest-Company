<?php
    global $wpdb;

    $technician_id = (new Technician_details)->get_technician_id();

    $whereSearch="";

    if(!empty($_GET['search'])){
        $whereSearch = (new GamFunctions)->get_table_coloumn($wpdb->prefix.'special_notes');
        $whereSearch = (new GamFunctions)->create_search_query_string($whereSearch,trim($_GET['search']),'and');
    }

    $pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;

    $no_of_records_per_page =50;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    $total_pages_sql = "
        select COUNT(*) 
        from {$wpdb->prefix}special_notes 
        where technician_id='$technician_id' 
        $whereSearch
    ";
    
    $total_rows= $wpdb->get_var($total_pages_sql);
    $total_pages = ceil($total_rows / $no_of_records_per_page);
    $notes=$wpdb->get_results("
        select * 
        from {$wpdb->prefix}special_notes 
        where technician_id='$technician_id' 
        $whereSearch 
        order by date DESC 
        LIMIT $offset, $no_of_records_per_page
    ");
    
?>

<div class="row">
    <div class="col-sm-3">
        <form action="<?= $_SERVER['REQUEST_URI']; ?>">
            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
            <div class="form-group">
                <label for="">Search Records</label>
                <input type="text" name="search" value="<?= @$_GET['search']; ?>" class="form-control">
            </div>
            <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
        </form>
    </div>
    <div class="col-sm-9">
        <?php if(!empty($_GET['search'])): ?>
            <p class="alert alert-success alert-dismissible">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <?= $total_rows; ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= strtok($_SERVER["REQUEST_URI"], '?'); ?>?view=<?= $_GET['view']; ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
            </p>
        <?php endif; ?>

    </div>
</div>


<div class="card">
    <div class="card-body">
        <h3 class="page-header">Special Notes</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Note</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($notes) && count($notes)>0): ?>
                    <?php foreach($notes as $note): ?>
                        <tr>
                            <td><?= $note->client_name; ?></td>
                            <td><?= $note->note; ?></td>
                            <td><?= date('d M Y',strtotime($note->date)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="3">No Record Found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
    </div>
</div>