<?php
    // get all tracking phone no
    $branches = (new Branches)->getAllBranches(false);
    $branch_slug = '';

    if(isset($_POST['branch_id']) && !empty($_POST['branch_id'])){
        $branch_id = esc_html($_POST['branch_id']);
        $branch_slug = (new Technician_details)->getTechnicianBranchSlug($branch_id);
    }

?>
<div class="container">
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <form action="" id="filter_by_callrail" method="post">
					<?php wp_nonce_field('get_ads_report'); ?>
                        <input type="hidden" name="action" value="get_ads_report">

                        <!-- Branch  -->
                        <div class="form-group">
                            <label for="">Select Branch</label>
                            <select name="branch"  class="form-control select2-field" required>
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <?php foreach($branches as $key=>$branch): ?>
                                        <?php if($branch->slug=="upstate"){continue;} ?>
                                        <option value="<?= $branch->slug; ?>" <?= (isset($branch_id) && $branch_id==$branch->id) ? 'selected' : ""; ?>><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif ?>
                            </select>
                        </div>
                        
                        <!-- Tracking Phone No.  -->
                        <div class="loader-box"></div>
                        
                        <!-- TRACKING IDS -->
                        <div id="tracking_ids">
                            <?php if(isset($branch_id) && !empty($branch_id)){
                                (new Callrail_new)->get_callrail_radio_form_by_location($branch_slug, $tracking_ids);
                            } ?>
                        </div>
                        
                        <!-- DATE TYPE -->
                        <div class="form-group">
                            <h5><b>Select Date Type</b></h5>

                            <?php
                                $checked="checked";
                                if(isset($_POST['date_type']) && $_POST['date_type']=="date_range"){
                                    $hidden="";
                                }
                            ?>
                            
                            <label class="radio-inline"><input type="radio" value="date_range" name="date_type" <?= $checked; ?>>Date Range</label> &nbsp; &nbsp; &nbsp; OR &nbsp; &nbsp; &nbsp;
                            <label class="radio-inline"><input type="radio" value="week" name="date_type" <?= (isset($_POST['date_type']) && $_POST['date_type']=="week") ? 'checked' : ''; ?>>Week</label>                        
                        </div>
                        
                        <div class="date-range-box">

                            <!-- From Date  -->
                            <div class="form-group">
                                <label for="">From Date</label>
                                <input type="date" name="from_date" max="<?= date('Y-m-d'); ?>" class="form-control date-range"  required>
                            </div>
                            
                            <!-- To Date  -->
                            <div class="form-group">
                                <label for="">To Date</label>
                                <input type="date" name="to_date" max="<?= date('Y-m-d'); ?>"  class="form-control date-range" required>
                            </div>
                        
                        </div>

                        <div class="week-box hidden">
                            <!-- week  -->
                            <div class="form-group">
                                <label for="">Select Week</label>
                                <input type="week" name="week" max="<?= date('Y-\WW'); ?>" class="form-control">
                            </div>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body report_data">
                                
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#filter_by_callrail').on('submit',function(e){
                e.preventDefault();
                $.ajax({
                    type:'post',
                    data:$(this).serialize(),
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    dataType:"html",
                    beforeSend:function(){
                        $('.report_data').html(`<div class="loader"></div>`);
                    },
                    success:function(data){
                        $('.report_data').html(data);
                    }
                })
            })
        })
    })(jQuery);
</script>