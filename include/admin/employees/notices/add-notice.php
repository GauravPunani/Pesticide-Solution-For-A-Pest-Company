<div class="container">
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h4 class="card-title text-center">Add Employee Notice</h4>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form action="<?= admin_url('admin-post.php'); ?>" method="post">
                        <?php wp_nonce_field('add_new_employee_notice'); ?>
                        <input type="hidden" name="action" value="add_new_employee_notice">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <div class="form-group">
                            <label for="parking_address">Select Notice Type</label>
                            <select name="notice_type" id="notice_type" class="form-control select2-field" required> 
                                <option value="">Select</option>
                                <option value="all">All Employees</option>
                                <option value="technician">Technician</option>
                                <option value="coldcallers">Cold Callers</option>
                                <option value="office">Office Staff</option>
                                <option value="door">Door To Door</option>
                                <option value="single">Single Employee</option>
                            </select>
                        </div>
                        <div class="form-group employee_tab">
                            
                        </div>
                        <div class="form-group">
                            <label for="parking_address">Notice</label>
                            <textarea type="text" name="notice" cols="5" rows="5" class="form-control" required></textarea>
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Add Notice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function($){
    $(document).ready(function(){
        $('#notice_type').on('change',function(){
            let notice_type=$(this).val();
            console.log(notice_type);
            // call ajax for 
            if(notice_type == "single"){
                $.ajax({
                    type:"post",
                    url:"<?= admin_url('admin-ajax.php'); ?>",
                    data:{
                        action:"get_employees_for_notice",
                        "_wpnonce": "<?= wp_create_nonce('get_employees_for_notice'); ?>"
                    },
                    dataType:"html",
                    beforeSend:function(){
                        $('.employee_tab').html("<div class='loader'></div>");
                    },
                    success:function(data){
                        $('.employee_tab').html(data);
                        // console.log(data);
                    }
                })
            }else{
                $('.employee_tab').empty();
            }
        })
    })
})(jQuery);
</script>