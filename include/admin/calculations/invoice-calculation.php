<?php

global $wpdb;
$technicians = (new Technician_details)->get_all_technicians();
$payment_methods = $wpdb->get_results("select * from {$wpdb->prefix}payment_methods");
$branches = (new Branches)->getAllBranches();
$lead_sources = (new Callrail_new)->get_all_tracking_no('',true);
?>



<div class="container">

    <div class="row">
        <?php (new Navigation)->calculation_navigation($_GET['page']); ?>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">

                    <h4 class="page-header">Invoice/Quote Calculation</h4>

                    <div class="form-group">
                        <label for="">Select type of calculation</label><br>
                        <label class="radio-inline"><input type="radio" value="invoice" name="calculation_type" checked>Invoice</label>
                        <label class="radio-inline"><input type="radio" value="quote" name="calculation_type">Quote</label>                        
                    </div>
                
                    <div class="invoice-form">
                        <form id="invoice_calculation">

                            <input type="hidden" name="action" value="calculate_invoice_data">

                            <!-- Branches -->
                            <div class="form-group">
                                <label >Select Branch</label>
                                <select name="branch_ids[]"  class="form-control branch_ids select2-field" multiple>
                                    <?php if(is_array($branches) && count($branches)>0): ?>
                                        <?php foreach($branches as $branch): ?>
                                            <option value="<?= $branch->id; ?>"><?= ucwords(str_replace('_',' ',$branch->location_name)); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                    
                            </div>

                            <div class="form-group">
                                <label for="">Select Technician <small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                <select name="technician_ids[]" class="form-control select2-field" multiple>
                                    <?php if(is_array($technicians) && count($technicians) > 0): ?>
                                        <?php foreach($technicians as $technician): ?>
                                            <option value="<?= $technician->id; ?>"><?= ucwords(str_replace('_',' ',$technician->first_name." ".$technician->last_name)); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                                
                            <!-- Payment Methods -->
                            <div class="form-group">
                                <label>Select Payment Methods (Optional)</label>
                                <select class="form-control select2-field" name="payment_methods[]" multiple>
                                    <?php foreach($payment_methods as $payment_method): ?>
                                        <option value="<?= $payment_method->slug; ?>"><?= $payment_method->name; ?></option>
                                    <?php endforeach; ?>                            
                                </select>
                            </div>

                            <!-- Lead Sources -->
                            <div class="form-group">
                                <label for="">Select Lead Sources (Optional)</label>
                                <select name="lead_sources[]" class="form-control select2-field" multiple>
                                    <?php if(is_array($lead_sources) && count($lead_sources)>0): ?>
                                        <?php foreach($lead_sources as $lead_source): ?>
                                            <option value="<?= $lead_source->id; ?>"><?= $lead_source->tracking_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <!-- Date Range  -->
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="from">From Date<small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                        <input type="date" class="form-control" name="from_date" >
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="to">To Date<small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                        <input type="date" class="form-control" name="to_date" >
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-2"><span><i class="fa fa-calculator"></i></span> Calculate Data</button>     
                        </form>
                    </div>

                    <div class="quote-form hidden">
                        <form id="quote_calculation">
                            <?php wp_nonce_field('quote_data_calculation'); ?>
                            <input type="hidden" name="action" value="quote_data_calculation">

                            <!-- Branches -->
                            <div class="form-group">
                                <label >Select Branch</label>
                                <select name="branch_ids[]" class="form-control branch_ids select2-field" multiple>
                                    <?php if(is_array($branches) && count($branches)>0): ?>
                                        <?php foreach($branches as $branch): ?>
                                            <option value="<?= $branch->id; ?>"><?= ucwords(str_replace('_',' ',$branch->location_name)); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                    
                            </div>

                            <div class="form-group">
                                <label for="">Select Technician <small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                <select name="technician_ids[]" class="form-control select2-field" multiple>
                                    <?php if(is_array($technicians) && count($technicians) > 0): ?>
                                        <?php foreach($technicians as $technician): ?>
                                            <option value="<?= $technician->id; ?>"><?= ucwords(str_replace('_',' ',$technician->first_name." ".$technician->last_name)); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Quote Type  -->
                            <div class="form-group">
                                <label for="">Quote Type <small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                <select name="quote_type" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>

                            <!-- Lead Sources -->
                            <div class="form-group">
                                <label for="">Select Lead Sources (Optional)</label>
                                <select name="lead_sources[]" class="form-control select2-field" multiple>
                                    <?php if(is_array($lead_sources) && count($lead_sources)>0): ?>
                                        <?php foreach($lead_sources as $lead_source): ?>
                                            <option value="<?= $lead_source->id; ?>"><?= $lead_source->tracking_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Date Range  -->
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="from">From Date<small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                        <input type="date" class="form-control" name="from_date" >
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="to">To Date<small class="text-danger"><i class="fa fa-asterisk"></i></small></label>
                                        <input type="date" class="form-control" name="to_date" >
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-2"><span><i class="fa fa-calculator"></i></span> Calculate Data</button>     
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center"><span><i class="fa fa-list"></i></span> Result</h4>
                    <div class="invoice-maintenance-result"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

function doCalculation(form){
    jQuery.ajax({
        type:"post",
        data:jQuery(form).serialize(),
        dataType:'html',
        url:"<?= admin_url('admin-ajax.php'); ?>",
        beforeSend:function(){
            jQuery('.invoice-maintenance-result').html('<div class="loader"></div>');
        },
        success: function(data){
            jQuery('.invoice-maintenance-result').html(data);
        }
    });    
}

(function($){
    $(document).ready(function() {

        $('#invoice_calculation').validate({
            rules: {
                from_date: "required",
                to_date: "required",
                "technician_ids[]": "required",
            },
            submitHandler: function(form) {
                doCalculation(form)
                return false;
            }
        });

        $('#quote_calculation').validate({
            rules: {
                from_date: "required",
                to_date: "required",
                "technician_ids[]": "required",
                quote_type: "required"
            },
            submitHandler: function(form) {
                doCalculation(form)
                return false;
            }
        });     

    });
})(jQuery);
</script>