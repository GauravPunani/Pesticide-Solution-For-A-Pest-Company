<?php

global $wpdb;
$technicians=(new Technician_details)->get_all_technicians(true);
?>

<div class="container">
    <div class="row">
        <div class="col-md-offset-2 col-md-5">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Create Calendar Event</h3>               
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="create_event_form" method="post" action="<?= admin_url('admin-post.php'); ?>">
						<?php wp_nonce_field('send_event_details_to_client'); ?>
                        <input type="hidden" name="action" value="send_event_details_to_client">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <div class="form-group">
                            <label for="">Select Technician</label>
                            <select name="technician" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($technicians) && count($technicians)>0): ?>
                                    <?php foreach($technicians as $technician): ?>
                                        <option value="<?= $technician->id; ?>"><?= $technician->first_name." ".$technician->last_name ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Title</label>
                            <input type="text" class="form-control" name="title">
                        </div>
                        <div class="form-group">
                            <label for="">Location</label>
                            <input id="autocomplete" type="text" class="form-control" name="location">
                        </div>
                        <div class="form-group">
                            <label for="">Description</label>
                            <textarea name="description" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="">Start Time</label>
                            <input type="datetime-local" class="form-control" name="start_time">
                        </div>
                        <div class="form-group">
                            <label for="">End Time</label>
                            <input type="datetime-local" class="form-control" name="end_time">
                        </div>
                        <div class="form-group">
                            <label for="">Client Email</label>
                            <input type="email" name="client_email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Reocurring Event ?</label>
                            <div class="radio">
                                <label><input type="radio" value="yes" name="reocurring_event">Yes</label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" value="no" name="reocurring_event" checked>No</label>
                            </div>
                        </div>

                        <div class="recurring_event_fields hidden">
                            <div class="form-group">
                                <label for="">Repeat Every</label>
                                <input type="number" min="1" name="interval" value="1">
                                <select name="repeate_type" id="">
                                    <option value="DAILY">Days</option>
                                    <option value="WEEKLY">Weeks</option>
                                    <option value="MONTHLY">Months</option>
                                    <option value="YEARLY">years</option>
                                </select>
                            </div>
                            <div class="form-group repeats_on hidden">
                                <label for="">Repeats On</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="SU" checked>S</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="MO">M</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="TU">T</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="WE">W</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="TH">T</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="FR">F</label>
                                <label class="checkbox-inline"><input type="checkbox" name="repeats_on[]" value="SA">S</label>
                            </div>
                            <div class="form-group">
                                <label for="">Ends</label>
                                <div class="radio">
                                    <label><input type="radio" value="never" name="ends" checked>Never</label>
                                </div>
                                <div class="radio">
                                    <label><input type="radio" value="on" name="ends">On</label>
                                </div>
                                <input type="date" class="form-control hidden ends_on" name="ends_on" value="<?= date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380&libraries=places&callback=initAutoComplete">
</script>

<script>

    (function($){
        $(document).ready(function(){

            $('select[name="repeate_type"]').on('change',function(){
                if($(this).val()=="WEEKLY"){
                    $('.repeats_on').removeClass('hidden');
                }
                else{
                    $('.repeats_on').addClass('hidden');
                }
            });

            $('input[name="reocurring_event"]').on('change',function(){
                if($(this).val()=="yes"){
                    $('.recurring_event_fields').removeClass('hidden');
                }
                else{
                    $('.recurring_event_fields').addClass('hidden');
                }
            });

            $('input[name="ends"]').on('change',function(){
                if($(this).val()=="on"){
                    $('.ends_on').removeClass('hidden');
                }
                else{
                    $('.ends_on').addClass('hidden');
                }
            });

            $('#create_event_form').validate({
                rules:{
                    technician:"required",
                    title:"required",
                    location:"required",
                    description:"required",
                    start_time:"required",
                    end_time:"required",
                    client_email:"required",
                    "repeats_on[]":"required",
                }
            })
        })
    })(jQuery);

    function initAutoComplete(){
        autocomplete=new google.maps.places.Autocomplete(
            document.getElementById('autocomplete'),
            {
                componentRestrictions:{'country':['US']},
                fields:['place_id','geometry','name','formatted_address','plus_code','business_status']
            }
        )
    }
</script>