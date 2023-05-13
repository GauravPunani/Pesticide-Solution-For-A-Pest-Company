<?php
$client_details=$_SESSION['invoice-data']['client-data'];

?>

<div class="row">

    <h3 class="page-header">Chemical Report New jersey</h3>

    <h4 class="text-danger">Before you can create an invoice, you must complete your chemical report!</h4>

    <p>If no pesticide application was conducted on this invoice <span><button type="button" onclick="skip_chemical_report()" class="btn btn-danger">Click Here</button></span></p>

    <!-- <p>Directly bypass using Calendar Event <button type="button" onclick="opne_technician_skip_model()" class="btn btn-primary">Click Here</button></p> -->

    <p>Application Date :- <?= date('d M Y'); ?></p>
    <p>Applicator Name :- Greg Migilacio</p>

    <div class="event_error"></div>

    <input type="hidden" name="action" value="newjersey_chemical_report">


    <!-- Date  -->
    <!-- <div class="col-md-6">
        <div class="form-group">
            <label for="">Date</label>
            <input type="date" name="date" class="form-control date" required>
        </div>
    </div> -->

    <!-- Calendar Event  -->
    <!-- <div class="col-md-6">
        <div class="form-group">
        <label for="">Calendar Event</label>
        <select name="calendar_events" id="calendar_events_newjersey" class="form-control select2-field calendar_events">
            <option value="">Select</option>
        </select>
        </div>
    </div> -->
    

    <!-- PLACE OF APPLICATION  -->
    <div class="form-group">
        <label for="">Place of Application</label>
        <input type="text" class="form-control" name="address" value="<?= $client_details['client-location']; ?>">
    </div>



    <div class="product-related-details">
        <?php get_template_part('/include/frontend/chemical-reports/newjersey-chemical-template'); ?>
    </div>

    <button type="button" data-location="newjersey" class="btn btn-primary add-product"><span><i class="fa fa-plus"></i></span> If you used multiple products on this job, you must click here to add additional products</button>

</div>