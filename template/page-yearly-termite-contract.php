<?php

/* Template Name: Yearly Termite Contract */

get_header();

$default_form_action="yearly_termite_contract";

$filled_by_client=false;

if(isset($_GET['token']))
{
    global $wpdb;

    $data=$wpdb->get_row("select client_id from {$wpdb->prefix}tokens where token='{$_GET['token']}'");

    if($data){
        $client_data=$wpdb->get_row("select * from {$wpdb->prefix}yearly_termite_contract where id='$data->client_id'");

        if($client_data){
            $filled_by_client=true;
        }
    }
}

?>

<div class="container" id="content">
    <div class="row">
        <div class="col-sm-12">
            <form method="post" id="yearly_termite_contract_form" class="res-form" action="<?= admin_url('admin-post.php'); ?>">
                <h2 class="text-center form-head">Yearly Termite Contract</h2>
                <?php (new GamFunctions)->getFlashMessage(); ?>
                <?php wp_nonce_field($default_form_action); ?>
                <input type="hidden" name="action" value="<?= $default_form_action; ?>">

                <!-- COMMON FIELDS IN ALL CONTRACT PAGES  -->
                <?php get_template_part('/template/maintenance-forms/common-fields',null,['data'=>$filled_by_client]); ?>

                <?php if($filled_by_client): ?>
                    <input type="hidden" name="client_id" value="<?= $client_data->id; ?>">
                    <input type="hidden" name="method" value="update">
                <?php else: ?>
                    <input type="hidden" name="callrail_id" value="">
                    <input type="hidden" name="method" value="insert">
                <?php endif; ?>
                

                <!-- DISPLAY FILLED FILEDS OR INPUT FIELDS BASED ON CLIENT FILLED OR STAFF  -->
                <?php if(!$filled_by_client): ?>
                    <?php get_template_part('/template/maintenance-forms/yearly-termite',null,['data'=>'form_fields']); ?>
                <?php else: ?>
                    <?php get_template_part("/template/client-area/yearly-termite",null,['data'=>$client_data]); ?>
                <?php endif; ?>


                <?php get_template_part('/template/maintenance-forms/credit-card-signature'); ?>

                <h3 class="page-header">General Conditions</h3>

                <p>This contract between Gam Exterminating and Customer covers only the primary structure/  areas listed above.</p>

                <p>For the sum of <b><span class="contract_amount">-</span></b>, GAM will provide the necessary and appropriate service to protect the identified structure(s) against the infestation of termites. This Contract does not cover any infestation of, or damage by, any other wood destroying organism other than those identified here  in above. This contract does not cover any structural damage to home. This contract will award a guarentee of no termites for a period of 1 year at the specified property, otherwise GAM Exterminating shall return for reservice to remedy any such relevant problem at no additional charges. Proof of termites must be evident in order to issue reservice if requested.
                For the sum of <b><span class="contract_amount">-</span></b>, client may renew there contract for year 2 and beyond. This is subject to a termite inspection prior to issuance.</p>

                <ul>
                    <li>
                        This contract shall terminate upon transfer of ownership of the described structure
                        -Customer warrants full cooperation during the term of this contract, and agrees to maintain the treated area(s)  free from any factors contributing to infestation, such as wood, trash, lumber, direct wood-soil contact, or standing water under pier type structure. Customer agrees  to notify Central Termite and Pest Control of and to eliminate faulty plumbing, leaks, and dampness from drains, condensation or leaks from the roof or otherwise  onto, or under said area(s) treated. Specifically, if faulty roofs are the cause of creating termite damage in any form, the cost of repairs will be the sole responsibility  of the owner. GAM reserves the right to terminate the contract if Customer fails to correct any condition, including, but not limited  to, the conditions listed above, which contribute or may contribute to infestation. is not responsible for any damage caused to the structure(s) treated as a  result of any said conditions. GAM shall be released from any further obligation under the Contract upon notice of termination to Customer. 
                        -GAMs liability under the Contract shall be terminated and excused from the performance of any obligations  under this Contract should GAM be prevented or delayed from fulfilling its responsibilities under terms of this Contract by reasons or  circumstances reasonably beyond its control, including, but not limited to, acts of war, whether declared or undeclared, acts of any duly constituted government  authority, strikes, “acts of God” or refusal of Customer to allow Central Termite and Pest Control access to the structure(s) for the purpose of inspecting or carrying  out other terms and conditions of this Contract. 
                    </li>

                    <li> 
                        GAM is not responsible for the repair of either visible damage (noted on the attached graph) or of hidden damage existing as of the date of this Contract. The attached graph covers only those areas that were visible, accessible and unobstructed at the time of inspection and does not cover areas such as, but not limited  to, enclosed or inaccessible areas concealed by wall coverings, floor coverings, ceilings, furniture, equipment, appliances, stored articles, or any portion of the  structure in which inspection would necessitate removing or defacing any part of the structure because damage may be present in areas which are inaccessible to a  visual inspection. Central Termite does not guarantee the damage disclosed on the attached graph represents all of the existing damage as of the date of this  Contract. The graph is not to scale.
                    </li>  

                    <li>
                        GAM shall not be responsible for any damage to the structure(s) caused by wood destroying  organisms or insects whether visible or hidden, or  any cost or expenses incurred by Customer as a result of such damage, or any damage caused by or related  to any of the conditions described above. If at any time termite damage is discovered, treatment shall be rendered.
                    </li>

                    <li>
                        GAM Exerminatings liability under this contract will be terminated if GAM is prevented from fulfilling its responsibilities under the terms of  this Contract by circumstances or caused beyond the control of Gam Exterminating.
                    </li>

                </ul>                
                <div class="text-center">
                    <button class="btn btn-lg btn-primary sendform"><span><i class="fa fa-paper-plane"></i></span> Submit</button>            
                </div>
            </form>
        </div>
    </div>
</div>

<script>

var default_form_action="<?= $default_form_action; ?>";
var filled_by_office=false;


(function($){
    $(document).ready(function(){

        $('select[name="buildings_treated"]').on('change',function(){
            if($(this).val()=="other"){
                $('.buildings_treated_other_box').removeClass('hidden');
            }
            else{
                $('.buildings_treated_other_box').addClass('hidden');
            }

        })

        jQuery('.filled-by-staff').on('click',function(){
            if(this.checked){
                filled_by_office=true;
                $('.cc_field').prop('disabled',true);
                $('.cc_details_box').addClass('hidden');
                $('input[name="action"]').val('yearly_termite_filled_by_staff');
            }
            else{
                filled_by_office=false;
                $('.cc_field').prop('disabled',false);
                $('.cc_details_box').removeClass('hidden');
                $('input[name="action"]').val(default_form_action);
            }
        });


        $('#yearly_termite_contract_form input[name="amount"]').on('change',function(){
            let amount=$(this).val();
            $('.contract_amount').html("$"+amount);
        });

        // validate the form
        $('#yearly_termite_contract_form').validate({
            rules:{
                name:"required",
                address:"required",
                phone_no:"required",
                email:"required",
                description_of_structure:"required",
                buildings_treated:"required",
                buildings_treated_other:"required",
                area_treated:"required",
                type_of_termite:"required",
                amount:"required",
                start_date:"required",
                end_date:"required",
                amount:"required",
                "card_details[creditcardnumber]":{
                    required: true,
                    maxlength:16
                },
                "card_details[cc_month]":"required",
                "card_details[cc_year]":"required",
                "card_details[cccode]":{
                    required:true,
                    maxlength:4
                }
                
            },
            submitHandler: function(form) {

                $('.sendform').attr('disabled',true);

                if(!filled_by_office){
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
                else{
                    return true;
                }
            }
            
        })

    });
})(jQuery);
</script>

<?php

get_footer();