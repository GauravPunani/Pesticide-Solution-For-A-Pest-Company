<?php 
    $branches = (new Branches)->getAllBranches(); 
?>

<?php if($args['data']=="form_fields"): ?>

    <!-- FILLED BY STAFF CHECKBOX  -->
    <?php if((new Technician_details)->is_technician_logged_in()): ?>
        <!-- form filled by staff  -->
        <div class="checkbox">
            <label><input name="form_filled_by_staff" type="checkbox" value="" class="filled-by-staff"><a data-toggle="tooltip" title="Use this option if client not present right now and want to sign and provide the contract details on his convenience. This option wlil send contract link to client on email to provide card details and sign the contract to confirm details.">Mail contract to client to sign and provide cc details</a></label>
        </div>
    <?php endif; ?>

    <!-- ESTABLISHEMENT NAME  -->
    <div class="form-group">
        <label for="establishmentName">Establishment name:</label>
        <input type="text" class="form-control" name="establishement_name">
    </div>

    <!-- PERSON NAME  -->
    <div class="form-group">
        <label for="inchargeName">Responsible person in charge name:</label>
        <input type="text" class="form-control" name="person_in_charge">
    </div>

    <!-- LOCATION  -->
    <div class="form-group">
        <label for=" name">Location</label>
        <select name="branch_id" class="form-control select2-field">
            <option value="">Select</option>
                <?php $branches=(new GamFunctions)->get_all_locations(); ?>
                <?php if(is_array($branches) && count($branches)>0): ?>
                    <?php foreach($branches as $branch): ?>
                        <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>                            

        </select>
    </div>

    <!-- ADDRESS  -->
    <div class="form-group">
        <label for="clientAddress">Address:</label>
        <input type="text" id="client_address" class="form-control" name="client_address">
    </div>

    <!-- ESTABLISHEMENT PHONE NO  -->
    <div class="form-group">
        <label for="establishmentPhn">Establishment phone number</label>
        <input type="text" class="form-control m_phone_no" name="establishment_phoneno">
    </div>

    <!-- RESPONSIBLE PERSON IN CHARGE PHONE NO  -->
    <div class="form-group">
        <label for="inchargePhn">Responsible person in charge phone number</label>
        <input type="text" class="form-control phone_no" name="res_person_in_charge_phone_no">
    </div>

    <!-- EMAIL  -->
    <div class="form-group">
        <label for="clientEmail">Email <?= (new Maintenance)->fakeEmailAlertMessage(); ?></label>
        <input type="text" class="form-control" required name="client_email">
    </div>

    <!-- COST PER VISIT  -->
    <div class="form-group">
        <label for="costPerVisit">Cost per visit</label>
        <input type="text" class="form-control" name="cost_per_visit">
    </div>

    <!-- FREQUENCY OF VISIT  -->
    <div class="form-group">
        <div class="row">
            <div class="col-75">
                <label for="visitfrequency">Frequency of visit</label>
                <input type="text" class="form-control" name="frequency_of_visit">
            </div>
            <div class="col-25">
                <label for="frequencyPer">Per</label>
                <input type="text" class="form-control" name="frequency_per">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="serviceDay">Preffered time and days of service:</label><br/>
    </div>

    <!-- DAY/TIME  -->
    <div class="form-group">
        <div class="row">
            <div class="col-sm-4">
                    <label for="serviceDay" class="week">Day(s) <span style="font-weight:normal">(input days of week)</span></label>
                    <input type="text" class="form-control"  name="prefered_days">
            </div>
            <div class="col-sm-3">
                    <label for="serviceDay" class="week">Time</label>
                    <input type="time" name="prefered_time" class="form-control">
            </div>
        </div>
    </div>

    <!-- Notes  -->
    <div class="form-group">
        <label for="">Notes</label>
        <textarea name="client_notes" cols="30" rows="5" class="form-control" placeholder="place any special notes, terms, requests, inclusions or exclusion pests here"></textarea>
    </div>

    <!-- CONTRACT START END DATES  -->
    <div class="form-group">
        <div class="row">
            <div class="col-50">
            
                <label for="start_date">Contract Start Date</label>
                <input type="date" class="form-control" value="<?= date('Y-m-d'); ?>" name="contract_start_date">
            </div>
            <div class="col-50">
                <label for="end_date">Contract End Date</label>
                <input type="date" class="form-control" value="<?= date('Y-m-d',strtotime(date("Y-m-d", time()) . " + 365 day")); ?>" name="contract_end_date">
            </div>
        </div>
    </div>

<?php endif; ?>

<?php if($args['data']=="checkbox_line"): ?>
    <!----------check-box---->
    <div class="checkbox">
        <label><input name="checkterms" type="checkbox"><small>* I understand I am responsible for the maintenance plan i have selected for the property I have listed, and I am responsible for the full value of this contract. I understand my card will be billed in accordance to the terms of this agreement for the amount stated above.</small></label>
    </div>
<?php endif; ?>

<script>
    initMap('client_address', (err, autoComplete) => {});
</script>