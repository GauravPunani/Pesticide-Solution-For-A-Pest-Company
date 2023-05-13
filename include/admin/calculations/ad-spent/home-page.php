<?php
    // get all tracking phone no
    $tracking_nos=(new Callrail_new)->get_all_tracking_no();
    $locations=(new GamFunctions)->get_all_locations();

    $user = wp_get_current_user();
    $roles = ( array ) $user->roles;

?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <form action="" id="ads_spent_form">
                    <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
                    <div class="form-group">
                        <label for="">Select Location</label>
                        <select name="tracking_location"  class="form-control">
                            <option value="">Select</option>
                            <?php if(is_array($locations) && count($locations)>0): ?>
                                <?php foreach($locations as $key=>$val): ?>
                                    <option value="<?= $val->slug; ?>" ><?= $val->location_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Select Tracking Phone No</label>
                        <select name="tracking_id"  class="form-control">
                            <option value="">Select</option>
                            <?php if(is_array($tracking_nos) && count($tracking_nos)>0): ?>
                                <?php foreach($tracking_nos as $key=>$val): ?>
                                    <option data-location="<?= $val->actual_location; ?>" value="<?= $val->id; ?>" <?= (isset($_GET['tracking_id']) && $_GET['tracking_id']==$val->id) ? 'selected': ''; ?>><?= $val->tracking_phone_no; ?> - <?= $val->tracking_name; ?></option>
                                <?php endforeach; ?>
                            <?php endif ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="">From Date</label>
                        <input type="date" name="from_date"  value="<?= (isset($_GET['from_date']) && !empty($_GET['from_date']))  ? date("Y-m-d",strtotime($_GET['from_date'])) : ''; ?>" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="">To Date</label>
                        <input type="date" name="to_date"  class="form-control" value="<?= (isset($_GET['to_date']) && !empty($_GET['to_date']))  ? date("Y-m-d",strtotime($_GET['to_date'])) : ''; ?>" >
                    </div>
                    <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                </form>
            </div>
        </div>

    </div>
    <div class="col-md-8">
        <div class="card full_width table-responsive">
            <div class="card-body">
                <?php 
                if(isset($_GET['tracking_id']) && !empty($_GET['tracking_id'])){
                    require_once "tracking-no-details.php";
                }
                else{
                    require_once "tracking-no-listing.php";
                }
                ?>
            </div>
        </div>
    </div>
    <?php if(in_array('ads_manager',$roles)): ?>
        <div class="col-sm-12">
            <?php get_template_part('include/admin/notices/google','ads'); ?>
        </div>
    <?php endif; ?>
</div>
