<?php

$branches=(new Branches)->getAllBranches();

?>

<div class="container">
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="page-header">Generate Report</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="pl_report_form" method="post" action="<?= admin_url('admin-post.php');  ?>">
                        <?php wp_nonce_field('generate_pl_report'); ?>
                        <input type="hidden" name="action" value="generate_pl_report">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <div class="form-group">
                            <label for="">Select Branch</label>
                            <select name="branch" class="form-control select2-field" required>
                                <option value="">Select</option>
                                <option value="all_branches">All Branches</option>
                                <?php if(is_array($branches) && count($branches)>0): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <?php if($branch->slug!=="upstate"): ?>
                                        <option value="<?= $branch->slug; ?>"><?= $branch->location_name; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="radio-inline"><input type="radio" value="date" name="date_type" checked>By Date</label>
                            <label class="radio-inline"><input type="radio" value="week" name="date_type">By Week</label>                                
                        </div>
                        
                        <div class="date_type_date">
                            <div class="form-group">
                                <label for="">From Date</label>
                                <input type="date" name="from_date" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="">To Date</label>
                                <input type="date" name="to_date" class="form-control" required>
                            </div>          
                        </div>

                        <div class="date_type_week hidden">
                            <div class="form-group">
                                <label for="">Select Week</label>
                                <input type="week" name="week" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">Ad type</label>
                            <select name="account[]" class="form-control select2-field" required multiple>
                                <option value="pest_control">Google</option>
                                <option value="map_ads">Google map ads</option>
                                <option value="bing">Bing</option>
                                <option value="facebook">Facebook</option>
                            </select>
                        </div>

                        <button type="button" class="btn btn-primary generate_report"><span><i class="fa fa-plus"></i></span> Generate Report</button>

                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card full_width table-responsive">
                <div class="card-body report-data">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){

            $('input[name="date_type"]').on('change',function(){
                let date_type=$(this).val();

                if(date_type=="date"){
                    $('.date_type_date').removeClass('hidden');
                    $('.date_type_week').addClass('hidden');
                }
                else{
                    $('.date_type_date').addClass('hidden');
                    $('.date_type_week').removeClass('hidden');
                }

            })

            $('.generate_report').on('click',function(e){
                e.preventDefault();
                let obj = $(this);

                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:$('#pl_report_form').serialize(),
                    dataType:"html",
                    beforeSend:function(){
                        obj.attr('disabled','disabled');
                        $('.report-data').html(`<div class="loader"></div>`);
                    },
                    success:function(data){
                        obj.removeAttr('disabled','disabled');
                        $('.report-data').html(data);
                    }
                })
            });
        })
    })(jQuery);
</script>