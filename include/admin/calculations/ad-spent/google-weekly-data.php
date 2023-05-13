<?php
    global $wpdb;

    $tracking_nos=(new Callrail_new)->get_all_tracking_no();
    $locations=(new Callrail_new)->get_all_locations();

    $where_location=$where_date=$where_tracking_id=$disabled="";
    $tracking_ids=[];
    if($_SERVER['REQUEST_METHOD']=="GET"){

        if(isset($_GET['tracking_location']) && !empty($_GET['tracking_location'])){
            $where_location="and {$wpdb->prefix}callrail.actual_location='{$_GET['tracking_location']}'";
        }

        if(isset($_GET['from_date']) && !empty($_GET['from_date'])){
            $where_date.=" and DATE({$wpdb->prefix}googleads_weekly_data.start_date) >= '{$_GET['from_date']}' ";
        }

        if(isset($_GET['to_date']) && !empty($_GET['to_date'])){
            $where_date.=" and DATE({$wpdb->prefix}googleads_weekly_data.end_date) <= '{$_GET['to_date']}' ";
        }

        if(isset($_GET['week']) && !empty($_GET['week'])){
            list($from_date,$to_date)=(new GamFunctions)->get_date_range($_GET['week']);
            $where_date.=" and (DATE({$wpdb->prefix}googleads_weekly_data.start_date) >= '{$from_date}' OR DATE({$wpdb->prefix}googleads_weekly_data.end_date) <= '{$to_date}') ";
            
        }
        
        if(isset($_GET['tracking_id']) && is_array($_GET['tracking_id']) && count($_GET['tracking_id'])>0 ){
            $tracking_ids=$_GET['tracking_id'];
            // echo "<pre>";print_r($_GET['tracking_id']);wp_die();
            $concatenated_ids="'" . implode ( "', '", $_GET['tracking_id'] ) . "'";
            $where_tracking_id=" and {$wpdb->prefix}googleads_weekly_data.tracking_id IN ($concatenated_ids)";
        }


    }

    if (isset($_GET['pageno'])) {
        $pageno = $_GET['pageno'];
    } else {
        $pageno = 1;
    }

    $no_of_records_per_page =50;
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "select COUNT(*) as total_rows , SUM({$wpdb->prefix}googleads_weekly_data.total_cost) as total_cost from {$wpdb->prefix}googleads_weekly_data INNER JOIN {$wpdb->prefix}callrail on  {$wpdb->prefix}googleads_weekly_data.tracking_id={$wpdb->prefix}callrail.id  $where_location $where_tracking_id  $where_date";
    
    $total_rows= $wpdb->get_row($total_pages_sql);

    $total_pages = ceil($total_rows->total_rows / $no_of_records_per_page);

    $weekly_data=$wpdb->get_results("select {$wpdb->prefix}googleads_weekly_data.*,{$wpdb->prefix}callrail.tracking_name from {$wpdb->prefix}googleads_weekly_data INNER JOIN {$wpdb->prefix}callrail on  {$wpdb->prefix}googleads_weekly_data.tracking_id={$wpdb->prefix}callrail.id  $where_location $where_tracking_id  $where_date order by {$wpdb->prefix}googleads_weekly_data.start_date DESC LIMIT $offset, $no_of_records_per_page ");

    // echo "<pre>";print_r($weekly_data);wp_die();

?>
<div class="row">

    <div class="col-sm-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <form action="" id="filter_by_callrail">
                    <?php (new GamFunctions)->pass_all_get_field_as_hidden_fields(); ?>

                    <!-- Location  -->
                    <div class="form-group">
                        <label for="">Select Location</label>
                        <select name="tracking_location"  class="form-control" required>
                            <option value="">Select</option>
                            <?php if(is_array($locations) && count($locations)>0): ?>
                                <?php foreach($locations as $key=>$val): ?>
                                    <option value="<?= $val->slug; ?>" <?= (isset($_GET['tracking_location']) && $_GET['tracking_location']==$val->slug) ? 'selected' : ""; ?>><?= $val->location_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif ?>
                        </select>
                    </div>
                    
                    <!-- Tracking Phone No.  -->
                    <div class="loader-box"></div>
                    <div id="tracking_ids">
                        <?php if(isset($_GET['tracking_location']) && !empty($_GET['tracking_location'])){
                            (new Callrail_new)->get_callrail_radio_form_by_location($_GET['tracking_location'],$tracking_ids);
                        } ?>
                    </div>
                    
                    <div class="form-group">

                        <h5><b>Select Date Type</b></h5>

                        <?php
                            $checked="checked";
                            if(isset($_GET['date_type']) && $_GET['date_type']=="date_range"){
                                $hidden="";
                            }
                        ?>
                        
                        <label class="radio-inline"><input type="radio" value="date_range" name="date_type" <?= $checked; ?>>Date Range</label> &nbsp; &nbsp; &nbsp; OR &nbsp; &nbsp; &nbsp;
                        <label class="radio-inline"><input type="radio" value="week" name="date_type" <?= (isset($_GET['date_type']) && $_GET['date_type']=="week") ? 'checked' : ''; ?>>Week</label>                        
                    </div>
                    
                    <div class="date-range-box <?= (isset($_GET['date_type']) && $_GET['date_type']=="week") ? 'hidden' : ''; ?>">

                        <!-- From Date  -->
                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" name="from_date" max="<?= date('Y-m-d'); ?>"  value="<?= (isset($_GET['from_date']) && !empty($_GET['from_date']))  ? date("Y-m-d",strtotime($_GET['from_date'])) : ''; ?>" class="form-control date-range" <?= (isset($_GET['week']) && !empty($_GET['week'])) ? 'disabled' : ''; ?> required>
                        </div>
                        
                        <!-- To Date  -->
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" name="to_date"  class="form-control date-range" value="<?= (isset($_GET['to_date']) && !empty($_GET['to_date']))  ? date("Y-m-d",strtotime($_GET['to_date'])) : ''; ?>" <?= isset($_GET['week']) && !empty($_GET['week'])? 'disabled' : ''; ?> required>
                        </div>
                    
                    </div>

                    <?php
                        $week_hidden="hidden";
                        if(isset($_GET['date_type']) && $_GET['date_type']=="week"){
                            $week_hidden="";
                        }
                    ?>

                    <div class="week-box <?= $week_hidden; ?>">
                        
                        <!-- week  -->
                        <div class="form-group">
                            <label for="">Select Week</label>
                            <input type="week" name="week" value="<?= isset($_GET['week']) && !empty($_GET['week'])? $_GET['week'] : ''; ?>" class="form-control">
                        </div>
                        
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-sm-12 col-md-8">
        <div class="card full_width table-responsive">
            <div class="card-body">
                <table class="table table-hover table-striped">
                    <caption>Ad Spend Weekly Data (<?= $total_rows->total_rows; ?> Reocrds) Total Cost - $<?= number_format((float)$total_rows->total_cost, 2, '.', ''); ?></caption>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Campaign Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Cost Per Call</th>
                            <th>Total Cost</th>
                            <th>Total Call</th>
                            <th>Total Revenue (Invoice)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($weekly_data) && count($weekly_data)>0): ?>
                            <?php foreach($weekly_data as $key=>$val): ?>
                                <tr>
                                    <td><?= $val->tracking_id; ?></td>
                                    <td><?= $val->tracking_name; ?></td>
                                    <td><?= date('d M Y',strtotime($val->start_date)); ?></td>
                                    <td><?= date('d M Y',strtotime($val->end_date)); ?></td>
                                    <td>$<?= number_format((float)$val->cost_per_call, 2, '.', ''); ?></td>
                                    <td>$<?= number_format((float)$val->total_cost, 2, '.', ''); ?></td>
                                    <td><?= $val->total_calls; ?></td>
                                    <td>$<?= number_format((float)$val->total_revenue, 2, '.', ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif ?>
                    </tbody>
                </table>
                <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
            </div>
        </div>
    </div>
    
</div>

<script>
(function($){
    $(document).ready(function() {

        $('select[name="tracking_location"]').on('change',function(){
            
            let location=$(this).val();
            console.log('location is'+location);
            $.ajax({

                type:"post",
                url:"<?= admin_url('admin-ajax.php'); ?>",
                data:{
                    action:"get_tracking_no_by_location",
                    location:location,
					"_wpnonce": "<?= wp_create_nonce('get_tracking_no_by_location'); ?>"
                },
                dataType:"html",
                beforeSend:function(){
                    //freeze the page untill we get the data
                    $('.tracking_phone_no').addClass('hidden');
                    $('.loader-box').addClass('loader');
                },
                success:function(data){
                    console.log('data is');
                    console.log(data);

                    $('#tracking_ids').html(data);

                    $('.tracking_phone_no').removeClass('hidden');
                    $('.loader-box').removeClass('loader');

                    
                }
            })

        });

        $('input[name="date_type"]').on('change',function(){
            let type=$(this).val();

            console.log('type is'+type);

            if(type=="date_range"){
                $('.date-range-box').removeClass('hidden');
                $('.week-box').addClass('hidden');
            }
            else{
                $('.date-range-box').addClass('hidden');
                $('.week-box').removeClass('hidden');
            }
        });
        
        // select all tracking number by click on checkbox 
        $(document).on('click','#select_all',function(){

            if($(this).prop("checked") == true){
                    console.log('select all')
                $('.tracking_no_checkboxes').prop('checked',true)

            }
            else{
                    console.log('not select all')
                $('.tracking_no_checkboxes').prop('checked',false)
            }

        });                
        
    });
})(jQuery);
</script>
