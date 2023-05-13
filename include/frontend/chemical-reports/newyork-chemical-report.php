<?php
    global $wpdb;
    $client_details=$_SESSION['invoice-data']['client-data'];
    $counties = (new ChemicalReportNewyork)->newYorkCountyNames();
    $licenses = (new GamFunctions)->getAllLicenses(['certification_id','first_name','last_name']);

    if(!empty($client_details['zip-code'])){
        $zip_code = esc_html($client_details['zip-code']);
        $columns = ['city','county_name'];
        $county_data = (new ChemicalReportNewyork)->getCountyDataByZipCode($zip_code, $columns);
    }

?>

<h3 class="page-header text-center">Chemical Report New York</h3>

<h4 class="text-danger">Before you can create an invoice, you must complete your chemical report!</h4>

<p>If no pesticide application was conducted on this invoice <span><button type="button" onclick="skip_chemical_report()" class="btn btn-danger">Click Here</button></span></p>

<p>Are you reporting for new york animal trapping? If so , please <a href="<?= site_url(); ?>/new-york-animal-trapping">click here</a> to proceed.</p>

<input type="hidden" name="action" value="chemical_report_newyork">

<div class="basic-technician-details">
    <div class="row">

        <!-- License Numbers  -->
        <div class="form-group">
            <label for="certification id">Please select pesticide license holder</label>
            <select name="certification_id" class="form-control">
                <option value="">Select</option>
                <?php if(is_array($licenses) && count($licenses)>0): ?>
                    <?php foreach($licenses as $certificate): ?>
                        <option value="<?= $certificate->certification_id; ?>"><?= $certificate->first_name." ".$certificate->last_name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="location-box"></div>

        <!-- Address of application  -->
        <div class="col-md-4">
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" name="application_address" value="<?= $client_details['client-location'] ?? ""; ?>" required>    
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="">City</label>
                <input type="text" class="form-control" name="application_city" value="<?= $county_data->city ?? ""; ?>" required>    
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="">Zip</label>
                <input type="text" class="form-control" name="application_zip" value="<?= $client_details['zip-code'] ?? ""; ?>" required>    
            </div>
        </div>

        <!-- Country Code  -->
        <div class="col-md-12">

            <div class="form-group">
                <label for="">Please select the county code in which you provided this pesticide application</label>

                <select name="county_code" class="form-control" required>

                    <option value="">Select</option>

                    <?php if(is_array($counties) && count($counties)>0): ?>
                        <?php foreach($counties as $county): ?>
                            <option value="<?= $county->county_name; ?>" <?= (isset($county_data->county_name) && $county_data->county_name==$county->county_name) ? 'selected' : ''; ?>><?= $county->county_name; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </select>
            </div>
        </div>
    </div>
</div>

<div class="product-related-details">
    <?php get_template_part('/include/frontend/chemical-reports/new-york-addon'); ?>
</div> 

<div class="row">
    <div class="col-sm-12 text-center">
        <p>If you used multiple products on this job, you must <a href="javascript:void(0)" type="button" data-location="newyork" class="add-product"> Click Here</a> to add additional products</p>
    </div>
</div>