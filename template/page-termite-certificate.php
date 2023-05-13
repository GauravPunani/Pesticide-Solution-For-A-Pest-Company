<?php
/* Template Name: Termite Certificate */
get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <!-- CERTIFICATE FORM  -->
            <form class="res-form" method="post" id="certificate_form" action="<?= admin_url('admin-post.php'); ?>">
                <h2 class="form-head text-center"> Termite Certificate</h2>
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <?php wp_nonce_field('termite_paperwork_certificate_new'); ?>

                <input type="hidden" name="action" value="termite_paperwork_certificate_new">
                <input type="hidden" name="signimgurl" value="">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <div class="form-group">
                    <label for="">Client Name</label>
                    <input type="text" class="form-control" name="client_name">
                </div>

                <div class="form-group">
                    <label for="">Client Email</label>
                    <input type="email" class="form-control" name="client_email">
                </div>

                <div class="form-group">
                    <label for="">Building Address</label>
                    <input type="text" class="form-control" name="building_address">
                </div>

                <div class="form-group">
                    <label for="">Date of termite prevention treatement</label>
                    <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="date_of_treatement">
                </div>

                <div class="form-group">
                    <label for="">Method of termite prevention treatement</label>
                    <input type="text" class="form-control" name="method_of_treatement">
                </div>

                <!----------c-name+sign---->
                <div class="form-group last-dsc notStaffField">
                    <div class="row">
                        <div class="col-75 col-md-offset-2 c-name">
                            <div id="signArea" >
                            <label for="sign">Technician Signature</label>
                            <div class="sig sigWrapper" style="height:auto;">
                                <div class="typed"></div>
                                <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                                <a class="clear-canvas"  onclick="clearCanvas()">Clear Signature</a>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                

                <button class="btn btn-primary sendform"><span><i class="fa fa-paper-plane"></i></span> Submit Certificate Data</button>

            </form>
        </div>
    </div>
</div>


<script>

    (function($){
        $(document).ready(function(){

            $('#skip_form').on('submit',function(e){
                if(confirm('Are you sure, you wan to skip this page ?')){
                    return true;
                }
                else{
                    return false;
                }
            });

            $('#certificate_form').validate({
                rules:{
                    client_name:"required",
                    client_email:"required",
                    building_address:"required",
                    date_of_treatement:"required",
                    method_of_treatement:"required",
                },
                submitHandler: function(form) {

                    $('.sendform').attr('disabled',true);

                    if(signaturePad.isEmpty()){
                        $('.sendform').attr('disabled',false);
                        alert('please fill the signature pad first');
                        return false;
                    }
                    else{
                        let data = signaturePad.toDataURL('image/png');
                        let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");                        

                        jQuery('input[name="signimgurl"]').val(img_data);
                        return true;
                    }                            
                }
                
            });

        });
    })(jQuery);
</script>
<?php
get_footer();