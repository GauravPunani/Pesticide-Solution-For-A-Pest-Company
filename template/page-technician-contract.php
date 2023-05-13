<?php

/* Template Name: Current Technician Contract  */

get_header();

$technician_data=(new Technician_details)->get_technician_data();

if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'agreement_proofs'){
  return get_template_part("template-parts/technician-agreement/tech-legal-tax-agreements",null,['data' => $technician_data]);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <form method="post" class="res-form" id="current_tech_agreement_form" action="<?= admin_url('admin-post.php'); ?>">
                <h3 class="text-center">Technician Contract/Agreement Form</h3>

                <input type="hidden" name="action" value="current_technician_contract">
                <input type="hidden" name="signature_data" value="">
                <input type="hidden" name="page_url" value="/technician-dashboard">
                <?php wp_nonce_field('current_tech_contract'); ?>

                <div class="form-group">
                    <label for="">Please Enter Your state</label>
                    <select name="state" class="form-control select2-field">
                        <option value="">Select</option>
                        <option value="New York">New York</option>
                        <option value="New Jersey">New Jersey</option>
                        <option value="Florida">Florida</option>
                        <option value="California">California</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Please Enter your home address</label>
                    <input type="text" class="form-control" name="address" value="<?= $technician_data->address; ?>">
                </div>

                <p>Please check and read all Contract/Agreement Documents</p>

                <p><a href="javascript:void(0)" data-toggle="modal" data-target="#non_compete"><span><i class="fa fa-file"></i></span> Non Compete</a></p>

                <p><a href="javascript:void(0)" data-toggle="modal" data-target="#independent_contract"><span><i class="fa fa-file"></i></span> Independent Contract</a></p>
                             
                <!-- signature  -->
                <div class="form-group last-dsc signature-area">
                    <div id="signArea" >
                        <label for="sign">Client Signature</label>
                        <div class="sig sigWrapper" style="height:auto;">
                        <div class="typed"></div>
                        <canvas class="sign-pad" id="sign-pad" width="300" height="100"></canvas><br>
                        <button type="button" onclick="clearCanvas()" class="btn btn-danger">Clear Signature</button>
                        </div>
                    </div>		  
                </div>

                <div class="checkbox">
                <label><input type="checkbox" name="agree_checkbox">I have read all contract/agreement related documents and agree to the same.  </label>
                </div>                
                
                <button id="submit_btn" class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Details</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="non_compete" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Document</h4>
      </div>
      <div class="modal-body">
        <?php get_template_part("/template/contract-documents/non-dependent"); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- Modal -->
<div id="independent_contract" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Document</h4>
      </div>
      <div class="modal-body">
        <?php get_template_part("/template/contract-documents/independent-contractor"); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script>
(function($){

    $(document).ready(function(){
        if($('#sign-pad').length){
            signaturePad = new SignaturePad(document.getElementById('sign-pad'), {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });        
        }

        $('#current_tech_agreement_form').validate({
            rules:{
            state : "required",
            address : "required",
            agree_checkbox : "required",
            },
            submitHandler:function(form){
                if(signaturePad.isEmpty()){
                        alert('please fill the signature pad first');
                        return false;
                    }
                    else{
                        //disable the submit button
                        $('#submit_btn').prop('disabled',true).val('processing...');

                        let data = signaturePad.toDataURL('image/png');
                        let img_data = data.replace(/^data:image\/(png|jpg);base64,/, "");                        

                        $('input[name="signature_data"]').val(img_data);
                        return true;
                    }
            }
        });
    });

})(jQuery);

function clearCanvas(){
    signaturePad.clear();
}

</script>

<?php
get_footer();

