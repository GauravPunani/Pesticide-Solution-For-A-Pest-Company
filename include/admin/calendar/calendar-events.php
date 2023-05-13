<?php

global $wpdb;

$branches = (new Branches)->getAllBranches();

$technicians=(new Technician_details)->get_all_technicians();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center"><span><i class="fa fa-calendar"></i></span> Check Calendar Events</h4>
                </div>
                <div class="card-body">
                        <div class="form-group">
                            <label for="">Select Branch</label>
                            <select onchange="filterTechnicians()" name="branch_id" class="form-control">
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches) > 0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="technician_id">Select Technician</label>
                            <select name="technician_id" id="technician_id" class="form-control">
                                <option value="">Select</option>
                                <?php foreach ($technicians as $key => $technician): ?>
                                    <?php if (!current_user_can( 'other_than_upstate' ) ) {
                                        if($technician->state!="upstate"){
                                            continue;
                                        }
                                    } ?>
                                    <option data-branch-id="<?= $technician->branch_id; ?>" value="<?= $technician->id; ?>"><?= ucwords(str_replace('_',' ',$technician->first_name." ".$technician->last_name)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="from date">From Date</label>
                            <input type="date" name="from_date" id="from_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="to date">To Date</label>
                            <input type="date" name="to_date" id="to_date" class="form-control" required>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary checkcalendarevents"><span><i class="fa fa-calendar"></i></span> Check Events</button>
                        </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center"><span><i class="fa fa-list"></i></span> Summary</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped summary-content">
                        
                    </table>

                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-5">
            <div class="card calendar_events table-responsive">
                <div class="card-body">
                    <h3 class="text-center"><span><i class="fa fa-calendar"></i></span> Google Calendar Events</h3>
                        <table class="table table-striped  calendar-content">
                            
                        </table>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="text-center text-danger"><span><i class="fa fa-clock-o"></i></span> Pending Events</h3></div>
                <div class="card-body">
                        <table class="table table-striped calendar_pending_events">
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function filterTechnicians(){
        const branch_id = jQuery('select[name="branch_id"]').val();
        
        jQuery('select[name="technician_id"] option').each(function(){

            if(jQuery(this).val() === "") return;

            if(jQuery(this).attr('data-branch-id') === branch_id){
                jQuery(this).removeClass('hidden');
            }
            else{
                jQuery(this).addClass('hidden');
            }
        })

    }
</script>