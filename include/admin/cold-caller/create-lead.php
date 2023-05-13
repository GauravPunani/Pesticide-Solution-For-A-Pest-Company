<?php

global $wpdb;
$cold_callers=$wpdb->get_results("select * from {$wpdb->prefix}cold_callers");
?>

<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card full_width table-responsive jumbotron">
                <div class="card-body">
                    <h3 class="page-header">Create Lead</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="create_lead_form" method="post" action="<?= admin_url('admin-post.php'); ?>">
                        <?php wp_nonce_field(); ?>
                        <input type="hidden" name="action" value="create_cold_caller_lead">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Select Cold Caller</label>
                            <select name="cold_caller" class="form-group select2-field" required>
                                <option value="">Select</option>
                                <?php if(is_array($cold_callers) && count($cold_callers)>0): ?>
                                    <?php foreach($cold_callers as $cold_caller): ?>
                                        <option value="<?= $cold_caller->id; ?>"><?= $cold_caller->name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
						<div class="form-group">
                            <label for="">Establishment Name</label>
                            <input type="text" class="form-control" name="establishment_name" required>
                        </div>
                        <div class="form-group">
                            <label for="">Decision Maker Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="">Decision maker Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="form-group">
                            <label for="">Decision Maker Number </label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="">Decision Maker Address </label>
                            <input type="text" class="form-control" name="address">
                        </div>
						<div class='form-group'>
							<label for="">Time of Appointment</label>
							<input type='text' id="datetimepicker3" name="time" class="form-control">
							</span>
						</div>
						<div class="form-group">
							<label for="">Day of Appointment</label>
							<input type="text" id="day_appointment" name="day" class="form-control">
							</span>
						</div>
						<div class="form-group">
                            <label for="">Special Notes</label>
                            <textarea name="notes" cols="30" rows="5" class="form-control" required=""></textarea>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Lead</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function($){

        jQuery.validator.addMethod("alphanumeric", function(value, element) {
			return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/i.test(value);
		}, "Numbers and dashes only");
        
        
		$('#datetimepicker3').datetimepicker({
			format: 'HH:mm'
		});
		$('#day_appointment').datetimepicker({
            format: 'YYYY-MM-DD',
		});

        $('#create_lead_form').validate({
            rules: {
                establishment_name: "required",
                name: "required",
                email: {
                    email: true,
                    required: true
                },
                phone: {
                    required: true,
                    alphanumeric: true
                },
                address: "required",
                time: "required",
                day: "required",
                notes: "required",

            }
        })

    })(jQuery);
</script>