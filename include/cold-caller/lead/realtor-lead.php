<form id="realtorLeadForm" method="post" action="<?= admin_url('admin-post.php'); ?>">

    <?php wp_nonce_field('cc_realtor_lead'); ?>

    <input type="hidden" name="action" value="cc_realtor_lead">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <div class="form-group">
        <label for="">Name</label>
        <input type="text" class="form-control" name="name">
    </div>
    <div class="form-group">
        <label for="">Email</label>
        <div class="notice notice-error">Please make sure to use genuine email and not use any fake email at all. Do not use office email too. Make sure you've the client email address before you submit this lead.</div>
        <input type="email" class="form-control" name="email">
    </div>
    <div class="form-group">
        <label for="">Phone Number</label>
        <input type="text" class="form-control" name="phone">
    </div>
    <div class="form-group">
        <label for="">Notes for office</label>
        <textarea name="notes" cols="30" rows="5" class="form-control" required=""></textarea>
    </div>

    <div class="form-group">
        <label for="">Does realtor want to meet face to face ?</label>
        <label class="radio-inline"><input type="radio" value="yes" name="realtor_wanna_meet">Yes</label>
        <label class="radio-inline"><input type="radio" value="no" name="realtor_wanna_meet">No</label>
    </div>

    <div class="form-group appointement-date-box hidden">
        <label for="">Set Appointment Date</label>
        <input type="date" name="appointement_date" class="form-control">
    </div>

    <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Lead</button>
</form>

<script>
    (function($){
        $(document).ready(function(){

            $('input[name="realtor_wanna_meet"]').on('change', function(){
                const realtor_wanna_meet = $(this).val();

                if(realtor_wanna_meet == "yes"){
                    $('.appointement-date-box').removeClass('hidden');
                }
                else{
                    $('.appointement-date-box').addClass('hidden');
                }

            })

            $('#realtorLeadForm').validate({
                rules: {
                    name: "required",
                    email: {
                        required: true,
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
                    phone: "required",
                    notes: "required",
                    realtor_wanna_meet: "required",
                    appointement_date: "required",
                },
                messages: {
                    email : {
                        remote : 'Please use a valid email and make sure email does not belongs to office'
                    }
                }
            })
        })
    })(jQuery);
</script>