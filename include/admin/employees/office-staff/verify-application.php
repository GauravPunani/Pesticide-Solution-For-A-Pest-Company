<?php
$employee_id = $_GET['application_id'];
$door_employee_data = (new Employee\Employee)->getEmployee($employee_id);
$branches = (new Branches)->getAllBranches();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <h3 class="page-header">Verifiy Office Staff Application</h3>

                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <th>Username</th>
                                <td><?= $door_employee_data->username; ?></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><?= $door_employee_data->name; ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= $door_employee_data->email; ?></td>
                            </tr>
                            <tr>
                                <th>Phone No.</th>
                                <td><?= $door_employee_data->phone_no; ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- fields to be filled by office -->
                    <form id="verify_office_staff" action="<?= admin_url('admin-post.php'); ?>" method="post">

                        <?php wp_nonce_field('verify_office_staff'); ?>
                        <input type="hidden" name="action" value="verify_office_staff">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                        <input type="hidden" name="employee_id" value="<?= $employee_id; ?>">
                        <input type="hidden" name="application_status" value="verified">

                        <!-- Branch  -->
                        <div class="form-group">
                            <label for="">Office Staff Branch</label>
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
            $('#verify_door_employee').validate({
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