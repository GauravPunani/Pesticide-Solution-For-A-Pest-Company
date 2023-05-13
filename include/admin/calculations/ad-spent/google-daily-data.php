<?php
    global $wpdb;

    $branches=(new Branches)->getAllBranches();

    $tracking_ids=[];
    $conditions=[];

    if(!current_user_can('other_than_upstate')){
        $accessible_branches=(new Branches)->partner_accessible_branches();
        $accessible_branches="'" . implode ( "', '", $accessible_branches ) . "'";
        $conditions[]=" CR.actual_location IN ($accessible_branches)";
    }
    

    if($_SERVER['REQUEST_METHOD']=="GET"){

        if(isset($_GET['tracking_location']) && !empty($_GET['tracking_location'])){
            $conditions[]=" CR.actual_location='{$_GET['tracking_location']}'";
        }

        if(isset($_GET['from_date']) && !empty($_GET['from_date'])){
            $conditions[]=" DATE(GDD.date) >= '{$_GET['from_date']}'";
        }

        if(isset($_GET['to_date']) && !empty($_GET['to_date'])){
            $conditions[]=" DATE(GDD.date) <= '{$_GET['to_date']}'";
        }

        if(isset($_GET['week']) && !empty($_GET['week'])){
            list($from_date,$to_date)=(new GamFunctions)->get_date_range($_GET['week']);
            $conditions[]=" DATE(GDD.date) >= '{$from_date}' and DATE(GDD.date) <= '{$to_date}'";
        }

        if(isset($_GET['tracking_id']) && is_array($_GET['tracking_id']) && count($_GET['tracking_id'])>0 ){
            $tracking_ids=$_GET['tracking_id'];
            $concatenated_ids="'" . implode ( "', '", $_GET['tracking_id'] ) . "'";
            $conditions[]=" GDD.tracking_id IN ($concatenated_ids)";
        }

    }

    $conditions = (count($conditions) > 0) ? (new GamFunctions)->generate_query($conditions) : '';
    $pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;

    $no_of_records_per_page =50;
    $offset = ($pageno-1) * $no_of_records_per_page; 
    $total_rows= $wpdb->get_var("
        select count(*)
        from {$wpdb->prefix}googleads_daily_data GDD 
        INNER JOIN {$wpdb->prefix}callrail CR 
        on GDD.tracking_id=CR.id 
        $conditions
    ");
    $total_pages = ceil($total_rows / $no_of_records_per_page);

    $daily_data=$wpdb->get_results("
        select GDD.*,CR.tracking_name 
        from {$wpdb->prefix}googleads_daily_data GDD 
        INNER JOIN {$wpdb->prefix}callrail CR 
        on GDD.tracking_id=CR.id 
        $conditions 
        order by GDD.date desc 
        LIMIT $offset, $no_of_records_per_page
    ");
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
                        <select name="tracking_location"  class="form-control select2-field" required>
                            <option value="">Select</option>
                            <?php if(is_array($branches) && count($branches)>0): ?>
                                <?php foreach($branches as $key=>$val): ?>
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
                    <caption>Ad Spend Daily Data (<?= $total_rows; ?> Records)</caption>
                    <thead>
                        <tr>
                            <th>Tracking ID</th>
                            <th>Campaign Name</th>
                            <th>Date</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_array($daily_data) && count($daily_data)>0): ?>
                            <?php foreach($daily_data as $key=>$val): ?>
                                <tr>
                                    <td><?= $val->tracking_id; ?></td>
                                    <td><?= $val->tracking_name; ?></td>
                                    <td><?= date('d M Y',strtotime($val->date)); ?></td>
                                    <td>$<?= $val->total_cost; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php (new GamFunctions)->render_pagination($pageno,$total_pages); ?>
    </div>
</div>