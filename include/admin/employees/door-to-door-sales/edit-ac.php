<?php
$sales_person_id = $args['sales_person_id'];
$sales_person_data = (new Employee\Employee)->getEmployee($sales_person_id);
$branches = (new Branches)->getAllBranches();
$application_status = (new GamFunctions)->getAllApplicationStatus();
$account_stauts = (new GamFunctions)->getAllAccountStatus();
?>

<?php if ($sales_person_data) : ?>

    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="page-header">Edit Door To Door Sales Ac</h3>
                        <?php (new GamFunctions)->getFlashMessage(); ?>
                        <form id="doorToDorrSalesForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                            <?php wp_nonce_field('update_door_to_door_sales_ac'); ?>
                            <input type="hidden" name="action" value="update_door_to_door_sales_ac">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            <input type="hidden" name="employee_id" value="<?= $sales_person_data->id; ?>">

                            <div class="form-group">
                                <label for="">Name</label>
                                <input type="text" class="form-control" name="name" value="<?= $sales_person_data->name; ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="<?= $sales_person_data->email; ?>">
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" id="address" value="<?= $sales_person_data->address; ?>">
                            </div>

                            <div class="form-group">
                                <label for="address">Phone No.</label>
                                <input type="text" class="form-control" name="phone_no" id="phone_no" value="<?= $sales_person_data->phone_no; ?>">
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control select2-field">
                                    <option value="">Select</option>
                                    <option value="active" <?= $sales_person_data->status == 1 ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?= $sales_person_data->status == 0 ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <!-- Branch  -->
                            <div class="form-group">
                                <label for="">Branch</label>
                                <select name="branch_id" class="form-control select2-field">
                                    <?php if (is_array($branches) && count($branches) > 0) : ?>
                                        <?php foreach ($branches as $branch) : ?>
                                            <option value="<?= $branch->id; ?>" <?= $sales_person_data->branch_id == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>


                            </div>
                            <div class="form-group">
                                <label>Application Status</label>
                                <select name="application_status" class="form-control select2-field">
                                    <option value="<?= $sales_person_data->application_status; ?>"><?= $sales_person_data->application_status; ?></option>
                                    <?php if (is_array($application_status) && count($application_status) > 0) : ?>
                                        <?php foreach ($application_status as $status) : ?>
                                            <option value="<?= $status->slug; ?>" <?= $status->slug == $sales_person_data->application_status ? 'selected' : ''; ?>><?= $status->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <h1>No Record Found</h1>
<?php endif; ?>

<script>
    (function($) {
        $(document).ready(function() {
            $('#doorToDorrSalesForm').validate({
                rules: {
                    name: "required",
                    email: "required",
                    address: "required",
                    phone_no: "required",
                    status: "required",
                    branch_id: "required",
                    application_status: "required"
                }
            });
        });
    })(jQuery);
</script>