<?php

/* Template Name: Florida Consumer Consent Form */
get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <!-- FLORIDA CONSUMER CONSENT FORM -->
            <form class="res-form" id="florida_consent_form" action="<?= admin_url('admin-post.php'); ?>" method="post">
                <?php wp_nonce_field('florida_consent_form_new'); ?>
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <input type="hidden" name="action" value="florida_consent_form_new">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="signimgurl" value="">

                <h4 class="text-center">Florida Department of Agriculture and Consumer Services</h4>
                <h4 class="text-center">Division of Agricultural Environmental Services</h4>

                <h3 class="text-center">Consumer Constent Form</h3>

                <p class="text-center"><small>Rule 5E-14.105, F.A.C.</small></p>
                <p class="text-center"><small>Telephone: (850) 617-7996; Fax: (850) 617-7981</small></p>

                <p>A pest control company must give you a written contract prior to any preventative or corrective treatment of each woad-destroying organism. Unless issued for pre-construction treatment, this contract must be provided to you before any work is done and before any payment is made so that you have an opportunity to thoroughly read it and understand exactly what services are being provided.</p>

                <p>TIPS: Be sure you understand:</p>

                <ul>
                    <li>All structures or building that will be included in the contract.</li>

                    <li>The duration of the contract and its renewal terms. (Most contracts are for five year periods, renewable annually, but others renew perpetually.) Verify how long the renewal rate will remain the same and, if it's allowed to increase, does the contract disclose a basis for the renewal increase (maximum percentage, cost of living, inflation, etc.)</li>

                    <li>Make sure the common name of the wood-destroying organism to be controlled by the contract is indicated and you understand which organisms are NOT covered.</li>

                    <li>The contract should state whether the treatment is preventative or corrective (treating an active infestation). Verify if a treatment is to be performed or not. If not, verify that the company has appropriate insurance coverage based on inspection and not based on “work performed”</li>

                    <li>The contract should state if it is a retreatment only or a retreatment and repair contract. If itis a retreatment and repair contract, make sure you understand what condition must occur to require the company to perform retreatment and/or repair. Also confirm that the maximum repair amount the company will pay is disclosed.</li>

                    <li>
                        Finally, determine if the contract is transferable to a new owner if you happen to sell your property and the terms associated with this. Some companies charge a fee and others just request a written notification.

                        <ul>
                            <li>Rule 5E-14.105(7), Florida Administrative Code, states, “A structure shall nat be knowingly placed under a second contract for the same wood-destroying organism control or preventative treatment in disregard of the first contract, without first obtaining specific written consent signed by the property owner or authorized agent using the Consumer Consent Form(FDACS-13671 Rev. 09/16).”</li>

                            <li>| understand that | have an existing contract with <b>GAM Exterminating Services</b> to provide wood-destroying organism(s) control or preventative treatment, and | am voluntarily entering into a second contract for control or preventative treatment for the same wood-destroying organism(s), which may void the terms of the existing contract.</li>
                        </ul>

                    </li>
                </ul>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Client Email</label>
                            <input type="email" class="form-control" name="client_email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Print Name of Consumer</label>
                            <input type="text" class="form-control" name="client_name">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Date</label>
                            <input type="date" name="date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sign">Signature of Consumer</label>
                            <div class="sig sigWrapper" style="height:auto;">
                                <div class="typed"></div>
                                <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                                <a class="clear-canvas"  onclick="clearCanvas()">Clear Signature</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Title</label>
                            <input type="text" class="form-control" name="title">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p><small><b><?= (new Technician_details)->get_technician_name(); ?></b> <br> Name of Pest Control Representative</small></p>
                    </div>
                    <div class="col-md-6">
                        <label for="">Date</label>
                        <input type="date" name="date_2" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <p><small><b><?= strtoupper((new Technician_details)->get_technician_name()); ?></b> <br> Signature of Pest Control Representative</small></p>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <p><small>Company : <b>GAM Exterminating Services</b></small></p>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary sendform"><span><i class="fa fa-check"></i></span> Confrim & Submit</button>

            </form>

        </div>
    </div>
</div>


<script>

    (function($){

        $('#skip_form').on('submit',function(e){
            if(confirm('Are you sure, you wan to skip this page ?')){
                return true;
            }
            else{
                return false;
            }
        });

        $(document).ready(function(){
            $('#florida_consent_form').validate({
                rules:{
                    client_email:"required",
                    client_name:"required",
                    date:"required",
                    title:"required",
                    date_2:"required",
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
                        $('input[name="signimgurl"]').val(img_data);
                        return true;
                    }

                }

                
            });
        });

    })(jQuery);
</script>

<?php
get_footer();