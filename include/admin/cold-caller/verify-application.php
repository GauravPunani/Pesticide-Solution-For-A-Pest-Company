<?php

$cold_caller_id = $args['id'];

$cold_caller = (new ColdCaller)->getColdCallerById($cold_caller_id);
$cold_caller_types = (new ColdCaller)->getColdCallersTypes();

if($cold_caller->application_status == 'verified'){
    echo "<h3>Application Already Verified</h3>";
    return;
}

$branches = (new Branches)->getAllBranches();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Verifiy Cold Caller Application</h3>

                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <th>Username</th>
                                <td><?= $cold_caller->username; ?></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><?= $cold_caller->name; ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= $cold_caller->email; ?></td>
                            </tr>
                            <tr>
                                <th>Phone No.</th>
                                <td><?= $cold_caller->phone_no; ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- fields to be filled by office -->
                    <form id="verify_cold_caller" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <?php wp_nonce_field('verify_cold_caller'); ?>
                        <input type="hidden" name="action" value="verify_cold_caller">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="cold_caller_id" value="<?= $cold_caller_id; ?>">

                        <!-- Branch  -->
                        <div class="form-group">
                            <label for="">Cold Caller Branch</label>
                            <select name="branch_id" class="form-control select2-field">
                                <option value="">Select</option>
                                <?php if(is_array($branches) && count($branches) > 0 ): ?>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?= $branch->id; ?>"><?= $branch->location_name; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <button class="btn btn-primary"><span><i class="fa fa-check"></i></span> Verify Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#verify_cold_caller').validate({
                rules: {
                    branch_id: "required",
                    type_id: "required",
                    password : {
                        minlength : 8,
                        required: true
                    },
                    confirm_password : {
                        minlength : 8,
                        equalTo : "#password",
                        required: true
                    },
                }
            })
        })
    })(jQuery);
</script>