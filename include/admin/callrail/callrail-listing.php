<?php

global $wpdb;

$branches=(new Branches)->getAllBranches();

$conditions=[];

if(!current_user_can('other_than_upstate')){
    $accessible_branches=(new Branches)->partner_accessible_branches();
    $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";

    $conditions[]=" actual_location IN ($accessible_branches)";
}


if (isset($_GET['location']) && !empty($_GET['location']) && $_GET['location']!="all") {
    $conditions[]=" actual_location='{$_GET['location']}' ";
}

if(count($conditions)>0){
    $conditions=(new GamFunctions)->generate_query($conditions);
}
else{
    $conditions="";
}

if(isset($_GET['search'])){

    $whereSearch=(new GamFunctions)->get_table_coloumn($wpdb->prefix.'callrail');
    if(!empty($conditions)){
        $conditions.=" ".(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search'],'and');
    }
    else{
        $conditions=(new GamFunctions)->create_search_query_string($whereSearch,$_GET['search']);
    }
}

if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

$total_pages_sql = "
select * 
from {$wpdb->prefix}callrail 
$conditions 
";

$no_of_records_per_page =50;
$offset = ($pageno-1) * $no_of_records_per_page; 
$total_rows= $wpdb->get_var($total_pages_sql);
$total_pages = ceil($total_rows / $no_of_records_per_page);

$trackers=$wpdb->get_results("
select * 
from {$wpdb->prefix}callrail 
$conditions 
order by date_created DESC 
LIMIT $offset, $no_of_records_per_page");

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php if(@$_GET['tab']!="unattributed-trackers"): ?>
                <?php (new Navigation)->location_tabs(@$_GET['location']); ?>
            <?php endif; ?>
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <div class="text-right">
                        <form action="<?= $_SERVER['REQUEST_URI']; ?>" >
                            <h3>Search Callrail</h3>
                            <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>
                            <input name="search" value="<?= @$_GET['search']; ?>" placeholder="Enter Name,email etc.." type="text"><span><button class="btn btn-primary"><span><i class="fa fa-search"></i></span>Search</button></span>
                        </form>
                    </div>
                    <h4 class="page-header">Callrail Trackers</h4>
                <?php if(isset($_GET['search'])): ?>
                    <p class="alert alert-success alert-dismissible">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?= $total_rows ?> Records Found for the search : <b><?= $_GET['search']; ?></b> <a class="btn btn-info" href="<?= admin_url('admin.php?page='.$_GET['page']."&location={$_GET['location']}"); ?>"><span><i class="fa fa-database"></i></span> Show All Records</a>
                    </p>
                <?php endif; ?>

                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Tracking Name</th>
                                <th>Tracking No.</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    <?php if(is_array($trackers) && count($trackers)>0): ?>
                        <?php foreach($trackers as $tracker): ?>
                            <tr>
                                <td><?= $tracker->tracking_name; ?></td>
                                <td><?= $tracker->tracking_phone_no; ?></td>
                                <td><?= (new GamFunctions)->beautify_string($tracker->actual_location); ?></td>
                                <td><button data-current-location="<?= $tracker->actual_location; ?>" data-tracker-id="<?= $tracker->id; ?>" class="btn btn-primary link_location_to_tracker"><span><i class="fa fa-link"></i></span> Link Location</button>
                                <button onclick="deleteCallrailNumber('<?= $tracker->id; ?>',this)" class="btn btn-danger"><span><i class="fa fa-trash"></i></span></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No Tracker Found</td>
                        </tr>
                    <?php endif; ?>
                    </table>
                    <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="link_location_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Link Location To Tracker</h4>
      </div>
      <div class="modal-body">
            <form action="<?= admin_url('admin-post.php'); ?>" method="post" id="link_location_to_tracker_form">
				<?php wp_nonce_field('update_callrail_tracker_location'); ?>
                <input type="hidden" name="action" value="update_callrail_tracker_location">
                <input type="hidden" name="tracker_id" value="">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <!-- location  -->
                <div class="form-group">
                    <label for="">Select Location</label>
                    <select name="tracker_location" class="form-control select2-field" required>
                        <option value="">Select</option>
                        <?php if(is_array($branches) && count($branches)>0): ?>
                            <?php foreach($branches as $location): ?>
                                <option value="<?= $location->slug; ?>"><?= $location->location_name;?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-link"></i></span> Link Location</button>
            </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('.link_location_to_tracker').on('click',function(){

                $('select[name="tracker_location"]').prop('selectedIndex',0);
                $('.select2-field').trigger('change.select2');

                let tracker_id=$(this).attr('data-tracker-id');
                let current_location=$(this).attr('data-current-location');

                $('input[name="tracker_id"]').val(tracker_id);

                $('select[name="tracker_location"] > option').each(function(index,value){
                    if(current_location==this.value){
                        $(this).prop('selected',true);
                        $('.select2-field').trigger('change.select2');
                    }
                });

                $('#link_location_modal').modal('show');

            });        
        });
    })(jQuery);

    function deleteCallrailNumber(tracker_id,ref){
        console.log('in method');
        if(confirm("Are you sure, you want to delete this number ?")){
            jQuery.ajax({
                type:"post",
                url:"<?= admin_url('admin-ajax.php'); ?>",
                dataType:"json",
                data:{
                    action:"delete_callrail_tracker",
                    tracker_id:tracker_id,
					"_wpnonce": "<?= wp_create_nonce('delete_callrail_tracker'); ?>"
                },
                success:function(data){
                    if(data.status=="success"){
                        jQuery(ref).parent().parent().fadeOut();
                    }
                    else{
                        alert("Something, went wrong, please try again later");
                    }
                }
            })
        }
    }
</script>