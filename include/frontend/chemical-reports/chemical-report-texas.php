<?php
$client_details=$_SESSION['invoice-data']['client-data'];
?>

<div class="row">

    <h3 class="page-header text-center">Chemical Report Texas</h3>

    <h3>Before you can create an invoice, you must complete your chemical report!</h3>

    <p>If no pesticide application was conducted on this invoice <span><button type="button" onclick="skip_chemical_report()" class="btn btn-danger">Click Here</button></span></p>

    <!-- <p>Directly bypass using Calendar Event <button type="button" onclick="opne_technician_skip_model()" class="btn btn-primary">Click Here</button></p> -->

    <div class="event_error"></div>
    <input type="hidden" name="action" value="texas_chemical_report">

    <!-- Date  -->
    <!-- <div class="form-group">
        <label for="">Date</label>
        <input type="date" name="date" class="form-control date" required>
    </div> -->

    <p>Date : <?= date('d M Y',strtotime($_SESSION['invoice-data']['client-data']['date'])); ?></p>
    <div class="form-group">
        <label for="">Time</label>
        <input type="time" name="time" class="form-control">
    </div>

    <!-- Calendar Event  -->
    <!-- <div class="form-group">
        <label for="">Calendar Event</label>
        <select name="calendar_events" id="calendar_events_newjersey" class="form-control select2-field calendar_events">
            <option value="">Select</option>
        </select>
    </div> -->

    <!-- PLACE OF APPLICATION  -->
    <div class="form-group">
        <label for="">Place of Application</label>
        <input type="text" class="form-control" name="address" value="<?= $client_details['client-location']; ?>">
    </div>

    <!-- applicator name  -->
    <div class="form-group">
        <label for="">Applicator Name</label>
        <select name="applicator_name" class="form-control">
            <option value="">Select</option>
            <option value="Greg Migilaccio">Greg Migilaccio</option>
            <option value="Mark Charles">Mark Charles</option>
            <option value="Jose Luis Torres">Jose Luis Torres</option>
        </select>
    </div>
    <div class="form-group">
        <p>Name of the person for whom the application was made : <b><?= (new Technician_details)->get_technician_name(); ?></b></p>
    </div>
    <div class="product-related-details">
        <?php get_template_part('/include/frontend/chemical-reports/product-details-texas'); ?>
    </div>

    <button type="button" data-location="texas" class="btn btn-primary add-product"><span><i class="fa fa-plus"></i></span> If you used multiple products on this job, you must click here to add additional products</button>

</div>