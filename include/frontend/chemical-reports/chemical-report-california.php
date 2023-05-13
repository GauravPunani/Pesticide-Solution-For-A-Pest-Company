<?php 
 $client_details = $_SESSION['invoice-data']['client-data'];
?>

<h3 class="page-header text-center">Chemical Report California</h3>

<h4 class="text-danger">Before you can create an invoice, you must complete your chemical report!</h4>

<p>If no pesticide application was conducted on this invoice <span><button type="button" onclick="skip_chemical_report()" class="btn btn-danger">Click Here</button></span></p>

<!-- <p>Directly bypass using Calendar Event <button type="button" onclick="opne_technician_skip_model()" class="btn btn-primary">Click Here</button></p> -->

<input type="hidden" name="action" value="chemical_report_california">


<div class="basic-technician-details">
    <div class="row">
        <div class="col-md-12">
                <!-- business reg # -->

                <div class="form-group">
                        <label for="business reg">Please select Business reg #</label>
                        <select class="form-control" name="business_reg" id="business_reg" required>
                            <option value="7373">Business reg # pr 7373</option>        
                        </select>
                </div>
        </div>
    </div>
    <div class="row">
        <h3 class="text-center">Client Details</h3>
        
        <div class="event_error"></div>

        <!-- Client Name  -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="name reg">Client Name</label>
                <input type="text" class="form-control" placeholder="Enter name here" name="client_name" value="<?= $client_details['client-name'] ?>" />
            </div>
        </div>

        <!-- Client Address -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="address reg">Client Address</label>
                <input type="text" class="form-control" placeholder="Enter address here" name="client_address" value="<?= $client_details['client-location'] ?>" />
            </div>
        </div>
    </div>
</div>

<div class="product-related-details">
    <?php get_template_part('/include/frontend/chemical-reports/california-addon'); ?>
</div>

<div class="row">
    <div class="col-md-12 text-center">
        <button type="button" data-location="california"  class="btn btn-primary add-product">if you used multiple products on this job, you must click here to add additional products</button>
    </div>
</div>