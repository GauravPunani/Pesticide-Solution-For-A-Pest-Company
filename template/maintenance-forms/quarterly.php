<?php 
    $branches = (new Branches)->getAllBranches(); 
?>


<?php if($args['data']=="form_fields"): ?>

    <?php if((new Technician_details)->is_technician_logged_in()): ?>
        <!-- form filled by staff  -->
        <div class="checkbox">
            <label><input name="form_filled_by_staff" type="checkbox" value="" class="filled-by-staff"><a data-toggle="tooltip" title="Use this option if client not present right now and want to sign and provide the contract details on his convenience. This option wlil send contract link to client on email to provide card details and sign the contract to confirm details.">Mail contract to client to sign and provide cc details</a></label>
        </div>
    <?php endif; ?>


    <!---name--->
    <div class="form-group">
        <label for=" name">Client name</label>
        <input type="text" id="name" name="client_name" class="form-control" placeholder="Name">
    </div>

    <!-- client location  -->
    <div class="form-group">
        <label for=" name">Client Location</label>
        <select name="branch_id"  class="form-control select2-field">
            <option value="">Select</option>
            <?php if(is_array($branches) && count($branches)>0): ?>
                <?php foreach($branches as $branch): ?>
                    <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>                            
        </select>
    </div>

    <!------client address---->
    <div class="form-group">
        <label for="address">Client Address</label>
        <input type="text" class="form-control" name="client_address" id="client_address" placeholder="Address">
    </div>

    <!------Client phone number----->
    <div class="form-group">
        <label for="number">Client phone number</label>
        <input type="tel" class="form-control m_phone_no" id="phone_no"  name="client_phone_no" placeholder="Phone Number">
    </div>

    <!-----Client email --->
    <div class="form-group">
        <label for="Email">Email <?= (new Maintenance)->fakeEmailAlertMessage(); ?></label>
        <input type="email" class="form-control" id="Email" required  name="client_email" placeholder="Email">
    </div>

    <!---------Cost per month----->
    <div class="form-group">
        <label for="Cost">Cost Per Quarter</label>
        <input type="text" class="form-control numberonly" name="cost_per_month">
    </div>

    <!-- Maintenance charges Interval  -->
    <div class="form-group">
        <label for="">Maintenance Charges Interval</label>
        <div class="radio">
            <label for=""><input type="radio" value="monthly" name="charge_type">Monthly</label>
        </div>
        <div class="radio">
            <label for=""><input type="radio" value="quarterly" name="charge_type">Quarterly</label>
        </div>
    </div>

    <!-- Notes  -->
    <div class="form-group">
        <label for="">Notes</label>
        <textarea name="client_notes" cols="30" rows="5" class="form-control" placeholder="place any special notes, terms, requests, inclusions or exclusion pests here"></textarea>
    </div>

    <!-----contract date --->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="">Start date</label>
                <input name="contract_start_date" value="<?= date('Y-m-d'); ?>"  type="date" class="form-control" /> 
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="">End date</label>
                <input name="contract_end_date" value="<?= date('Y-m-d',strtotime(date("Y-m-d", time()) . " + 365 day")); ?>"  type="date" class="form-control" /> 
            </div>  
        </div>
    </div>
<?php endif; ?>

<?php if($args['data']=="checkbox_line"): ?>
    <!----------check-box---->
    <div class="checkbox">
        <label><input name="checkterms" type="checkbox"><small>I understand this is a 12 month commitment for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be charged in accordance to the payment schedule listed on the maintenance agreement</small></label>
    </div>
<?php endif; ?>
