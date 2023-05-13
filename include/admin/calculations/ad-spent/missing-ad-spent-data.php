<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Search Missing Data</h3>
                    <form id="missing_data_form" action="">
						<?php wp_nonce_field('search_missing_data'); ?>
                        <input type="hidden" name="action" value="search_missing_data">
                        <div class="form-group">
                            <label for="">From Date</label>
                            <input type="date" class="form-control" name="from_date" required>
                        </div>
                        <div class="form-group">
                            <label for="">To Date</label>
                            <input type="date" class="form-control" name="to_date" required>
                        </div>
                        <div class="form-group">
                            <label for="">Select Account</label>
                            <select name="account" class="form-control select2-field" required>
                                <option value="">Select</option>
                                <option value="pest_control">Pest Control</option>
                                <option value="map_ads">Mad Ads</option>
                            </select>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search Missing Data</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Results</h3>
                    <div class="missing-data-body"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){

        $(document).ready(function(){
            $('#missing_data_form').on('submit',function(e){
                e.preventDefault();

                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php') ;?>",
                    data:$(this).serialize(),
                    dataType:"html",
                    beforeSend:function(){
                        $('.missing-data-body').html(`<div class="loader"></div>`);
                    },
                    success:function(data){
                        $('.missing-data-body').html(data);
                    }
                })
            });

            $(document).on('click','.fetch_spents',function(){

                let date=$(this).attr('data-date');
                let account=$(this).attr('data-account');
                let ref=$(this);

                $.ajax({
                    type:'post',
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:{
                        action:"fetch_ads_spent_by_date",
                        date:date,
                        account:account
                    },
                    dataType:"json",
                    beforeSend:function(){
                        ref.html(`<div class='loader'></div>`);
                        ref.attr('disabled',true);
                    },
                    success:function(data){
                        if(data.status=="success"){
                            ref.parent().html(`<p class='text-success'>Ad spent fetched & linked</p>`);
                        }
                        else{
                            ref.parent().html(`<p class='text-green'>Something went wrong</p>`);
                        }
                    }
                });

            })
        });

    })(jQuery);
</script>