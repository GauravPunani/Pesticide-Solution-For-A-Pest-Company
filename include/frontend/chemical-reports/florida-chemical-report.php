<?php
$client_details=$_SESSION['invoice-data']['client-data'];
?>
<div class="row">

    <h3 class="page-header text-center">Chemical Report Florida</h3>

    <h3>Before you can create an invoice, you must complete your chemical report!</h3>

    <p>If no pesticide application was conducted on this invoice <span><button type="button" onclick="skip_chemical_report()" class="btn btn-danger">Click Here</button></span></p>

    <!-- <p>Directly bypass using Calendar Event <button type="button" onclick="opne_technician_skip_model()" class="btn btn-primary">Click Here</button></p> -->
    
    <p>Restricted Entry Interval - <b>12 Hours</b></p>

    <input type="hidden" name="action" value="florida_chemical_report">

    <div class="event_error"></div>
    <p>Date : <?= date('d M Y',strtotime($_SESSION['invoice-data']['client-data']['date'])); ?></p>

    <!-- location/description  -->
    <div class="form-group">
        <label for="">Location/Description of Treatment Site (R/W) </label>
        <input type="text" class="form-control" name="description_of_treatment" value="<?= $client_details['client-location']; ?>" required>
    </div>

    <div class="product-related-details">
        <?php get_template_part("/include/frontend/chemical-reports/florida-chemical-template"); ?>
    </div>

    <p>If you used multiple products on this job, you must <a href="javascript:void(0)" type="button" data-location="florida" class="add-product"> Click Here</a> to add additional products</p>

</div>