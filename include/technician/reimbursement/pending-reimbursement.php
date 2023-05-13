<?php
    global $wpdb;
    if (isset($_GET['pageno'])) {
      $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }


    $no_of_records_per_page = 50;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    
    $search_query=[];

    $whereSearch=[];
    
    if(isset($_GET['search'])){
    
      $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'reimbursement_proof');
      $whereSearch=(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'and',$wpdb->prefix.'reimbursement_proof');  //genereate where query string

      $search_query[]=$whereSearch;    
    }

    if(isset($_GET['date']) && !empty($_GET['date'])){
        $search_query[]=" and DATE({$wpdb->prefix}reimbursement_proof.date_requested)='{$_GET['date']}' ";
    }

    if(count($search_query)>0){
        $search_query=implode(' ',$search_query);
    }
    else{
        $search_query="";
    }

    if(isset($_SESSION['employee']) && !empty($_SESSION['employee'])){
        $where = "employee_id={$_SESSION['employee']['id']}";
    }else{
        $technician_id=(new Technician_details)->get_technician_id();
        $where = "technician_id={$technician_id}";
    }
    $total_pages_sql = "select COUNT(*) from {$wpdb->prefix}reimbursement_proof where $where $search_query and status='not_paid'";
    
    $total_rows= $wpdb->get_var($total_pages_sql);
    
    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $data=$wpdb->get_results("select * from {$wpdb->prefix}reimbursement_proof where $where $search_query and status='not_paid' order by created_at desc");
?>

<div class="row">

    <?php if(!empty($search_query)): ?>
        <p>Total Records Found : <b><?= $total_rows; ?></b> <a href="<?= strtok($_SERVER["REQUEST_URI"], '?').'?view=pending-reimbursement'; ?>"><span><i class="fa fa-database"></i></span> Show All</a></p>
    <?php endif; ?>

    <!-- filter box  -->
    <div class="col-sm-12 col-md-4">
          <div class="card full_width table-responsive">
              <div class="card-body">
                  <form action="<?= $_SERVER['REQUEST_URI']; ?>">
                      <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                          <!--<div class="form-group">
                              <label for="">Search By Keyword</label>
                              <input type="text" class="form-control" name="search" value="<?= (isset($_GET['search']) && !empty($_GET['search'])) ? $_GET['search'] : ''; ?>">
                          </div>-->

                          <div class="form-group">
                              <label for="">Date</label>
                              <input type="date" class="form-control" name="date" value="<?= (isset($_GET['date']) && !empty($_GET['date'])) ? $_GET['date'] : ''; ?>">
                          </div>
                          
                          <button class="btn btn-primary"><span><i class="fa fa-filter"></i></span> Filter Records</button>
                  </form>
              </div>
          </div>

    </div>

    <!-- listing table  -->
    <div class="col-sm-12 col-md-8">
        <div class="card full_width table-responsive">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Receipts</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($data) && count($data)>0): ?>
                            <?php foreach($data as $key=>$val): ?>
                            <tr>
                                <td>$<?= $val->amount; ?></td>
                                <td><a class="btn btn-primary" target="_blank" href="<?= $val->receipts; ?>"><span><i class="fa fa-eye"></i></span> View</a></td>
                                <td><?= (new GamFunctions)->beautify_string($val->status); ?></td>
                                <td><?= date('d M Y',strtotime($val->date_requested)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No Log Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
            </div>
        </div>
    </div>
</div>

<!-- docs bootstrap modal  -->
<div id="docs_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Daily Deposit Docs</h4>
      </div>
      <div class="modal-body deposit_docs">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
