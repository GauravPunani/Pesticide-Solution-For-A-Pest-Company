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



    <!---Name--->
    <div class="form-group">
        <label for=" name">Name</label>
        <input type="text" name="client_name"  class="form-control" placeholder="Name">
    </div>

    <!-- client location  -->
    <div class="form-group">
        <label for=" name">Location</label>
        <select  name="branch_id" class="form-control select2-field">
            <option value="">Select</option>
            
            <?php if(is_array($branches) && count($branches)>0): ?>
            <?php foreach($branches as $branch): ?>
                    <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
            <?php endforeach; ?>
            <?php endif; ?>                            
        </select>
    </div>

    <!----client address---->
    <div class="form-group">
        <label for="address">Address</label>
        <input class="form-control" id="client_address" name="client_address"  placeholder="Address" />
    </div>

    <!------phone number----->
    <div class="form-group">
        <label for="number">phone number</label>
        <input type="text" class="form-control m_phone_no" maxlength="12" name="client_phone_no" value=""  placeholder="Phone Number">
    </div>

    <!-----email --->
    <div class="form-group">
        <label for="Email">Email <?= (new Maintenance)->fakeEmailAlertMessage(); ?></label>
        <input type="email" class="form-control" name="client_email" placeholder="Email">
    </div>

    <!---------Cost per month----->
    <div class="form-group">
        <label for="Cost">Cost per month</label>
        <input type="text" class="form-control numberonly" name="cost_per_month">
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
        <label><input name="checkterms" type="checkbox"><small>I understand this is a 12 month commitment for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be billed monthly for the amount stated above.</small></label>
    </div>    
<?php endif; ?>
