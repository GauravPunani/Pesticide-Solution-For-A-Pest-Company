<?php
global $wpdb;
$cold_callers=$wpdb->get_results("select * from {$wpdb->prefix}cold_callers");
?>

<form id="create_lead_form" method="post" action="<?= admin_url('admin-post.php'); ?>">

    <?php wp_nonce_field('create_cold_caller_lead'); ?>
    
    <input type="hidden" name="action" value="create_cold_caller_lead">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <input type="hidden" name="lead_type" value="normal">

    <div class="form-group">
        <label for="">Establishment Name</label>
        <input type="text" class="form-control" name="establishment_name" required>
    </div>
    <div class="form-group">
        <label for="">Decision Maker Name</label>
        <input type="text" class="form-control" name="name" required>
    </div>
    <div class="form-group">
        <label for="">Decision maker Email (optional) </label>
        <input type="email" class="form-control" name="email">
    </div>
    <div class="form-group">
        <label for="">Decision Maker Number </label>
        <input type="text" class="form-control" name="phone">
    </div>
    <div class="form-group">
        <label for="">Decision Maker Address </label>
        <input type="text" class="form-control" name="address" id="client_address">
    </div>
    <div class='form-group'>
        <label for="">Time of Appointment</label>
        <input type='time' name="time" class="form-control">
        </span>
    </div>
    <div class="form-group">
        <label for="">Day of Appointment</label>
        <input type="date" name="day" class="form-control">
        </span>
    </div>
    <div class="form-group">
        <label for="">Special Notes</label>
        <textarea name="notes" cols="30" rows="5" class="form-control" required=""></textarea>
    </div>
    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Lead</button>
</form>

<script type="text/javascript">
    const input_client_address = document.getElementById('client_address');
    let isAutoComplete = false;

    let autocomplete_client_address;
    (function($){

        $(document).ready(function(){

            $('#client_address').on('change', () => isAutoComplete = false);

            initMap('client_address', (err, autoComplete) => {
               autoComplete.addListener('place_changed', function() {
                    isAutoComplete = true;
               });
            });

            jQuery.validator.addMethod("alphanumeric", function(value, element) {
                return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/i.test(value);
            }, "Numbers and dashes only");        
        
            $('#create_lead_form').validate({
                rules: {
                    establishment_name: "required",
                    name: "required",
                    email: {
                        email: true,
                        remote : {
                            type: "post",
                            url: "<?= admin_url('admin-ajax.php'); ?>",
                            data: {
                                action: "check_for_banned_email",
                                "_wpnonce": "<?= wp_create_nonce('check_for_banned_email'); ?>",
                                checkForOfficeEmail : true
                            }                            
                        }
                    },
                    phone: {
                        required: true,
                        alphanumeric: true
                    },
                    address: "required",
                    time: "required",
                    day: "required",
                    notes: "required",

                },
                messages:{
                    email :{
                        remote : ERROR_MESSAGES.invalid_email,
                        isBannedEmail: "Email is not valid and banned to be used"
                    }
                },
                submitHandler: function(){
                    
                    if(!isAutoComplete)
                        return alert('Please make sure to select client address from suggested list of address from google');

                    return true;
                }
            });

        });

    })(jQuery);
</script>