<?php
$staff_member_id = $args['member_id'];

$member_data = (new OfficeStaff)->getStaffMemberById($staff_member_id);
$branches = (new Branches)->getAllBranches();
$application_status = (new GamFunctions)->getAllApplicationStatus();
$account_stauts = (new GamFunctions)->getAllAccountStatus();

?>

<?php if ($member_data) : ?>

    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="page-header">Edit Office Staff Ac</h3>
                        <?php (new GamFunctions)->getFlashMessage(); ?>
                        <form id="officeStaffEditForm" action="<?= admin_url('admin-post.php'); ?>" method="post">

                            <?php wp_nonce_field('update_office_staff_ac'); ?>
                            <input type="hidden" name="action" value="update_office_staff_ac">
                            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                            <input type="hidden" name="account_id" value="<?= $member_data->id; ?>">

                            <div class="form-group">
                                <label for="">Name</label>
                                <input type="text" class="form-control" name="name" value="<?= $member_data->name; ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="<?= $member_data->email; ?>">
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" id="address" value="<?= $member_data->address; ?>">
                            </div>

                            <!-- Branch  -->
                            <div class="form-group">
                                <label for="">Branch</label>
                                <select name="branch_id" class="form-control select2-field">
                                    <?php if (is_array($branches) && count($branches) > 0) : ?>
                                        <?php foreach ($branches as $branch) : ?>
                                            <option value="<?= $branch->id; ?>" <?= $member_data->branch_id == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>

                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Application Status</label>
                                <select name="application_status" class="form-control select2-field">
                                    <option value="<?= $member_data->application_status; ?>"><?= $member_data->application_status; ?></option>
                                    <?php if (is_array($application_status) && count($application_status) > 0) : ?>
                                        <?php foreach ($application_status as $status) : ?>
                                            <option value="<?= $status->slug; ?>" <?= $status->slug == $member_data->application_status ? 'selected' : ''; ?>><?= $status->name; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <button class="btn btn-primary"><span><i class="fa fa-user-refresh"></i></span> Update</button>

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
            $('#officeStaffEditForm').validate({
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