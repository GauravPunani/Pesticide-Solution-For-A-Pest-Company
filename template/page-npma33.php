<?php 
/* Template Name: NPMA 33 */ 
get_header();
?>

<div class="container" id="content">
    <div class="row">
        <div class="col-sm-12 res-form">
            <h3 class="page-header text-center">NPMA33 FORM</h3>
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <form id="florida_wood_inspection_report_form" method="post" action="<?= admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('npma33_form'); ?>
                <input type="hidden" name="action" value="npma33_form">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="npma_form" value="npma_form">
                
                <!-- SECTION I - GENERAL INFORMATION  -->
                <div class="setion-1">
                    <h4 class="page-header"><strong>Section I. GENERAL INFORMATION</strong></h4>
 
                    <!-- INSPECTION DATE -->
                    <div class="form-group">
                        <label for="">Inspection Date</label>
                        <input type="date" name="inspection_date" class="form-control">
                    </div>

                    <!-- INSPECTOR NAME  -->
                    <div class="form-group">
                        <label for="">Inspector Name</label>
                        <select name="inspector_name" class="form-control">
                            <option value="">Select</option>
                            <option value="Greg Migliaccio C1881257">Greg Migliaccio C1881257</option>
                            <option value="Chris Davis T9901387">Chris Davis T9901387</option>
                            <option value="John Long JF5267">John Long JF5267</option>
                        </select>
                    </div>

                    <!-- ADDRESS OF PROPERTY -->
                    <div class="form-group">
                        <label for="">Address of property</label>
                        <input type="text" name="address_of_property" cols="30" rows="5" class="form-control">
                    </div>

                    <!-- Structure Property Inspected -->
                    <div class="form-group">
                        <label for="">Structures Inspected</label>
                        <input type="text" name="structures_inspected" class="form-control">
                    </div>

                    <!-- Inspection & Report Requested By -->
                    <div class="form-group">
                        <label for="">Inspection & Report Requested By</label>
                        <input type="text" name="inspection_and_report_requested_by" class="form-control">
                    </div>

                    <!-- CLIENT NAME -->
                    <div class="form-group">
                        <label for="">Client Name</label>
                        <input type="text" class="form-control" name="client_name">
                    </div>

                    <!-- CLIENT EMAIL -->
                    <div class="form-group">
                        <label for="">Client Email</label>
                        <input type="email" name="client_email" class="form-control">
                    </div>

                    <!-- Report Sent to Requestor and to: -->
                    <div class="form-group">
                        <label for="">Report Sent to Requestor and to:</label>
                        <input type="text" name="report_sent_to" class="form-control">
                    </div>
                </div>
                
                <!-- SECTION II - Inspection Findings -->
                <div class="section-2">
                    <h4 class="page-header"><strong>Section II. Inspection Findings</strong></h4>

                    <div class="radio">
                        <label><input type="radio" value="no" name="evidence_of_wood_destroying"> No visible evidence of wood destroying insects was observed.</label>
                    </div>
                    
                    <div class="radio">
                        <label><input type="radio" value="yes" name="evidence_of_wood_destroying">Visible evidence of wood destroying insects was observed as follows:</label>
                    </div>

                    <div class="visible_evidence-of-wood-destroying hidden">
                        <p><b>Select Type</b></p>
                        <div class="radio">
                            <label><input type="radio" value="option1" name="evidence_type">Live insects.</label>
                        </div>

                        <div class="radio">
                            <label><input type="radio" value="option2" name="evidence_type">Dead insects, insect parts, frass, shelter tubes, exit holes, or staining.</label>
                        </div>

                        <div class="radio">
                            <label><input type="radio" value="option3" name="evidence_type">Visible damage from wood destroying insects was noted as follows.</label>
                        </div>

                        <div class="form-group">
                            <label for="">Description and Location</label>
                            <textarea name="evidence_description_and_location" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION III - Recommendations -->
                <div class="section-2">
                    <h4 class="page-header"><strong>Section III. Recommendations</strong></h4>
                    <div class="radio">
                        <label><input type="radio" value="option1" name="treatement_recommendation">No treatment recommended: </label>
                    </div>

                    <div class="radio">
                        <label><input type="radio" value="option2" name="treatement_recommendation">Recommend treatment for the control of:</label>
                    </div>

                    <div class="form-group">
                        <label for="">Description and Location</label>
                        <textarea name="recommendation_note" cols="30" rows="5" class="form-control"></textarea>
                    </div>
                </div>


                <!-- Section IV. Obstructions and Inaccessible Areas -->
                <div class="section-3">
                    <h4 class="page-header"><strong>Section IV. OBSTRUCTIONS AND INACCESSIBLE AREAS</strong></h4>

                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[0][type]" value="Basement">Basement</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[0][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[1][type]" value="Crawlspace">Crawlspace</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[1][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[2][type]" value="Main Level">Main Level</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[2][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[3][type]" value="Attic">Attic</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[3][note]"></textarea>
                    </div>     
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[4][type]" value="Garage">Garage</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[4][note]"></textarea>
                    </div>   
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[5][type]" value="Exterior">Exterior</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[5][note]"></textarea>
                    </div>   
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[6][type]" value="Porch">Porch</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[6][note]"></textarea>
                    </div>                  
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[7][type]" value="Addition">Addition</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[7][note]"></textarea>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[8][type]" value="Other">Other</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[8][note]"></textarea>
                    </div>
                </div>

                <!-- SECTION V - COMMENTS AND FINANCIAL DISCLOSURE -->
                <div class="section-5">
                    <h4 class="page-header"><strong>SECTION V - COMMENTS AND FINANCIAL DISCLOSURE</strong></h4>
                    <div class="form-group">
                        <label for="">Comments</label>
                        <textarea name="comments" cols="30" rows="5" class="form-control"></textarea>
                    </div>
                </div>

                <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Report</button>
                
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
            $('input[name="evidence_of_wood_destroying"]').on('change',function(){
                if($(this).val()=="yes"){
                    $('.visible_evidence-of-wood-destroying').removeClass('hidden');
                }
                else{
                    $('.visible_evidence-of-wood-destroying').addClass('hidden');
                }
            });

            $('input[name="previously_treated"]').on('change',function(){
                if($(this).val()=="yes"){
                    $('.previous_treatement_note').removeClass('hidden');
                }
                else{
                    $('.previous_treatement_note').addClass('hidden');
                }
            });

            $('input[name="structure_treated_at_inspection"]').on('change',function(){
                if($(this).val()=="yes"){
                    $('.structure_treated_at_inspection_box').removeClass('hidden');
                }
                else{
                    $('.structure_treated_at_inspection_box').addClass('hidden');
                }
            });

            $('.o_i_a').on('change',function(){
                $(this).closest('div').find('textarea').toggleClass('hidden');
            });       

            $('#florida_wood_inspection_report_form').validate({
                rules:{
                    inspection_date:"required",
                    inspector_name:"required",
                    address_of_property:"required",
                    structures_inspected:"required",
                    inspection_and_report_requested_by:"required",
                    client_name:"required",
                    client_email:"required",
                    evidence_of_wood_destroying:"required",
                    evidence_type:"required",
                    treatement_recommendation:"required",
                    recommendation_note:"required",
                    evidence_description_and_location:"required",
                    obstruction_and_inacessible_areas:"required",
                    notes_for_obstruction_and_inaccessible_area:"required",
                    previously_treated:"required",
                    previous_treatement_note:"required",
                    notice_of_inspection:"required",
                    structure_treated_at_inspection:"required",
                    common_name_of_organism_treated:"required",
                    name_of_pesticide_used:"required",
                    terms_and_condition_of_treatement:"required",
                    method_of_treatement:"required",
                    treatement_notice_location:"required",
                    comments:"required",
                }
            });

        });
    })(jQuery);
</script>

<?php
get_footer();
