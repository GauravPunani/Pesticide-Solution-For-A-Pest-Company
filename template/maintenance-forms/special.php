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


<!-- TYPE OF MAINTENANCE  -->
<div class="form-group">

    <div class="radio">
        <label><input type="radio" name="service_type" value="Monthly Service">Monthly Service</label>
    </div>

    <div class="radio">
        <label><input name="service_type" type="radio" value="Bi-Monthly">Bi-Monthly</label>
    </div>

    <div class="radio">
        <label><input name="service_type" type="radio" value="As needed service within 90 days of initial service"> As needed service within 90 days of initial service</label>
    </div>

</div>

<!-- COST PER VISIT  -->
<div class="service-box">
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <label for="cost">Cost Per Visit</label>
                <input type="text" name="cost" class="form-control" placeholder="e.g. $100">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label for="cost">Every (x) Months</label>
                <input type="text" name="days" class="form-control" placeholder="e.g. 1 month">
            </div>
        </div>
    </div>
</div>

<!-- DATES  -->
<div class="form-group">
    <div class="row">
        <div class="col-50">
            <label for="">From Date</label>
            <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="from_date" >
        </div>
        <div class="col-50">
            <label for="">To Date</label>
            <input type="date" value="<?= date('Y-m-d',strtotime(date("Y-m-d", time()) . " + 365 day")); ?>" class="form-control" name="to_date" >
        </div>
    </div>
</div>

<!---name--->
<div class="form-group">
    <label for=" name">Client name</label>
    <input type="text" name="client_name" class="form-control" placeholder="Name">
</div>

<!-- CLIENT LOCATION  -->
<div class="form-group">
    <label for=" name">Client Location</label>
    <select name="branch_id" class="form-control select2-field">
            <option value="">Select</option>
                <?php if(is_array($branches) && count($branches)>0): ?>
                    <?php foreach($branches as $branch): ?>
                        <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>                            
    </select>
</div>

<!------address---->
<div class="form-group">
    <label for="address">Client Address</label>
    <input type="text" class="form-control" name="client_address" id="client_address" placeholder="Address">
</div>

<!------Client phone number----->
<div class="form-group">
    <label for="number">Client phone number</label>
    <input type="tel" class="form-control m_phone_no" name="client_phone" placeholder="Phone Number">
</div>

<!-----Client email --->
<div class="form-group">
    <label for="Email">Email <?= (new Maintenance)->fakeEmailAlertMessage(); ?></label>
    <input type="email" class="form-control" name="client_email" placeholder="Email">
</div>

<!-- notes -->
<div class="form-group">
    <label for="">Notes</label>
    <textarea name="notes" cols="30" rows="5" class="form-control" placeholder="place any special notes, terms, requests, inclusions or exclusion pests here"></textarea>
</div>
<?php endif; ?>

<?php if($args['data']=="checkbox_line"): ?>
    <!----------check-box---->

    <div class="checkbox">
        <label><input name="checkterms" type="checkbox"><small>* I understand I am responsible for the maintenance plan i have selected for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be billed in accordance to the terms of this agreement for the amount stated above.</small></label>
    </div>

<?php endif; ?>