<?php 
/* Template Name: Florida Wood Inspection Report */ 
get_header();
?>

<div class="container" id="content">
    <div class="row">
        <div class="col-sm-12 res-form">
            <h3 class="page-header text-center">Florida Wood Inspection Report</h3>
            <?php (new GamFunctions)->getFlashMessage(); ?>
            <form id="florida_wood_inspection_report_form" method="post" action="<?= admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('florida_wood_inspection_report'); ?>
                <input type="hidden" name="action" value="florida_wood_inspection_report">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                <input type="hidden" name="npma_form" value="">
                
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
                        <label><input type="radio" value="no" name="evidence_of_wood_destroying">No visible evidence of wood destroying insects was observed.</label>
                    </div>
                    
                    <div class="radio">
                        <label><input type="radio" value="yes" name="evidence_of_wood_destroying">Visible evidence of wood destroying insects was observed as follows:</label>
                    </div>

                    <div class="visible_evidence-of-wood-destroying hidden">
                        <p><b>Select Type</b></p>
                        <div class="radio">
                            <label><input type="radio" value="option1" name="evidence_type">Live WDO</label>
                        </div>

                        <div class="radio">
                            <label><input type="radio" value="option2" name="evidence_type">EVIDENCE of WDO(s) (dead wood-destroying insects or insect parts, frass, shelter tubes, exit holes, or other evidence)</label>
                        </div>

                        <div class="radio">
                            <label><input type="radio" value="option3" name="evidence_type">DAMAGE caused by WDO(s) was observed and noted</label>
                        </div>

                        <div class="form-group">
                            <label for="">Description and Location</label>
                            <textarea name="evidence_description_and_location" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                    
                    </div>


                </div>

                <div class="section-3">
                    <h4 class="page-header"><strong>Section III. OBSTRUCTIONS AND INACCESSIBLE AREAS</strong></h4>

                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[0][type]" value="Attic">Attic</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[0][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[1][type]" value="Interior">Interior</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[1][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[2][type]" value="Exterior">Exterior</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[2][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[3][type]" value="Crawlspace">Crawlspace</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[3][note]"></textarea>
                    </div>                    
                    <div class="checkbox">
                        <label><input type="checkbox" class="o_i_a" name="obstruction_and_inacessible_areas[4][type]" value="Other">Other</label>
                        <textarea class="form-control hidden" name="obstruction_and_inacessible_areas[4][note]"></textarea>
                    </div>

                </div>

                <!-- SECTION IV - NOTICE OF INSPECTION & TREATEMENT INFORMATION -->
                <div class="section-4">
                    <h4 class="page-header"><strong>Section IV. NOTICE OF INSPECTION AND TREATMENT INFORMATION</strong></h4>

                    <p>Visible evidence of possible previous treatment</p>

                    <div class="radio">
                        <label><input type="radio" value="yes" name="previously_treated">Yes</label>
                    </div>
                    
                    <div class="radio">
                        <label><input type="radio" value="no" name="previously_treated">No</label>
                    </div>

                    <div class="form-group previous_treatement_note hidden">
                        <label for="">Please provide note on possible previous treatment</label>
                        <textarea name="previous_treatement_note" cols="30" rows="5" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="">A Notice of Inspection has been affixed to the structure at</label>
                        <input type="text" name="notice_of_inspection" id="" class="form-control">
                    </div>

                    <p>Structure treated at the time of inspection ?</p>

                    <div class="radio"><label><input type="radio" value="yes" name="structure_treated_at_inspection">Yes</label></div>
                    <div class="radio"><label><input type="radio" value="no" name="structure_treated_at_inspection">No</label></div>

                    <div class="structure_treated_at_inspection_box hidden">

                        <div class="form-group">
                            <label for="">Common name of organism treated</label>
                            <input type="text" class="form-control" name="common_name_of_organism_treated">
                        </div>

                        <div class="form-group">
                            <label for="">Name of Pesticide Used</label>
                            <input type="text" class="form-control" name="name_of_pesticide_used">
                        </div>

                        <div class="form-group">
                            <label for="">Terms & Condition of Treatment</label>
                            <input type="text" class="form-control" name="terms_and_condition_of_treatement">
                        </div>

                        <div class="form-group">
                            <label for="">Method of Treatement</label>
                            <div class="radio">
                                <label><input type="radio" value="option1" name="method_of_treatement">Whole Structure</label>
                            </div>
                            
                            <div class="radio">
                                <label><input type="radio" value="option2" name="method_of_treatement">Spot Treatement</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">Treatement Notice Location</label>
                            <input type="text" class="form-control" name="treatement_notice_location">
                        </div>                    
                    
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
