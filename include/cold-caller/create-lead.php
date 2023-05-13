<?php
    $cold_caller_id = (new ColdCaller)->getLoggedInColdCallerId();
    $is_realtor = (new ColdCallerRoles)->doesColdCalllerHaveRealtorRole($cold_caller_id);
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12 res-form">
            <?php (new GamFunctions)->getFlashMessage(); ?>
                <h3 class="page-header text-center">Create Lead</h3>
                <div class="form-group">
                    <label for="">Lead Type</label>
                    <select name="lead_type" class="form-control select2-field">
                        <option value="normal" selected>Normal</option>
                        <option value="property_management">Property Management</option>
                        <?php if($is_realtor): ?>
                            <option value="realtor">Realtor</option>
                        <?php endif; ?>
                    </select>
                </div>

                <?php if($is_realtor): ?>
                    <div class="realtor-lead-form hidden">
                        <?php get_template_part('include/cold-caller/lead/realtor-lead'); ?>
                    </div>
                <?php endif; ?>

                <div class="normal-lead-form">
                    <?php get_template_part('include/cold-caller/lead/normal-lead'); ?>
                </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('select[name="lead_type"]').on('change', function(){
                const lead_type = $(this).val();

                $('#create_lead_form input[name="lead_type"]').val(lead_type);

                if(lead_type == 'normal' || lead_type == 'property_management'){
                    $('.realtor-lead-form').addClass('hidden');
                    $('.normal-lead-form').removeClass('hidden');
                }
                else if(lead_type == "realtor"){
                    $('.realtor-lead-form').removeClass('hidden');
                    $('.normal-lead-form').addClass('hidden');
                }
                else{
                    $('.realtor-lead-form').addClass('hidden');
                    $('.normal-lead-form').removeClass('hidden');
                }

            });
        })
    })(jQuery);
</script>