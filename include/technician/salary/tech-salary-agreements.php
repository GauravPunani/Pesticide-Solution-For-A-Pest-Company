<?php
if (isset($args['user'])) : $technician_data = $args['user'];
    $tech_contract = (new Technician_details)->getTechnicianSalaryContractById($technician_data->id, ['salary_contract_pdf']);
?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <?php 
                    if(empty($tech_contract->salary_contract_pdf)){
                        echo '<div class="alert alert-danger">You are not allowed to access this page.</div>';
                        return false;
                    }
                ?>
                <form method="post" class="res-form" id="current_tech_salary_agreement_form" action="<?= admin_url('admin-post.php'); ?>">
                    <div class="col-sm-12">
                        <?php (new GamFunctions)->getFlashMessage(); ?>
                    </div>
                    <h2 class="text-center form-head">Technician Salary Contract</h2>
                    <input type="hidden" name="action" value="tech_salary_contract">
                    <input type="hidden" name="signature_data" value="">
                    <input type="hidden" name="salary_contract_pdf" value="<?= @$tech_contract->salary_contract_pdf;?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <?php wp_nonce_field('current_tech_salary_contract'); ?>

                    <div class="form-group">
                        <label for="">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="<?= @$technician_data->first_name; ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="<?= @$technician_data->last_name; ?>">
                    </div>

                    <!----------c-name+sign---->
                    <div class="form-group last-dsc notStaffField">
                        <div class="row">
                            <div class="col-75 col-md-offset-2 c-name">
                                <div id="signArea">
                                    <label for="sign">Signature</label>
                                    <div class="sig sigWrapper" style="height:auto;">
                                        <div class="typed"></div>
                                        <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas>
                                        <a class="clear-canvas" onclick="clearCanvas()">Clear Signature</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p>Please check and read all Contract/Agreement Documents : 

                    <a target="_blank" href="<?php echo get_template_directory_uri() . "/assets/pdf/technician/salary/{$tech_contract->salary_contract_pdf}"; ?>"><span><i class="fa fa-file"></i></span> Form 1099</a></p>

                    <div class="checkbox">
                        <label><input type="checkbox" name="agree_checkbox">I have read all contract/agreement related documents and agree to the same. </label>
                    </div>

                    <button id="submit_btn" class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Details</button>
                </form>
            </div>
        </div>
    </div>

<?php endif; ?>

<script>
    (function($) {

        $(document).ready(function() {
            if ($('#sign-pad').length) {
                signaturePad = new SignaturePad(document.getElementById('sign-pad'), {
                    backgroundColor: 'rgba(197, 197, 197, 0.6)',
                    penColor: 'rgb(0, 0, 0)'
                });
            }

            $('#current_tech_salary_agreement_form').validate({
                rules: {
                    first_name: "required",
                    last_name: "required",
                    agree_checkbox: "required"
                },
                submitHandler: function(form) {
                    if (signaturePad.isEmpty()) {
                        alert('please fill the signature pad');
                        return false;
                    } else {
                        //disable the submit button
                        $('#submit_btn').prop('disabled', true).text('Processing...');

                        let data = signaturePad.toDataURL('image/png');
                        let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");

                        $('input[name="signature_data"]').val(img_data);
                        return true;
                    }
                }
            });
        });

    })(jQuery);

    function clearCanvas() {
        signaturePad.clear();
    }
</script>