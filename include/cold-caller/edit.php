<?php 

global $wpdb;

if(empty($_SESSION['caller_editable']['id'])){
    echo "Something Went Wrong";wp_die();
}

$cold_caller_id= (new ColdCaller)->getLoggedInColdCallerId();
$cold_caller_data = (new ColdCaller)->getColdCallerById($cold_caller_id);
$branches= (new Branches)->getAllBranches(false);
?>

<?php if(!$cold_caller_data): ?> 

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="text-right">
                    <input type="hidden" name="action" value="cancel_cold_caller_form_edit">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">
                    <button class="btn btn-danger"><span><i class="fa fa-remove"></i></span> Cancel Edit</button>
                </form>
            </div>
            <div class="col-sm-12 col-md-8">
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">

                    <?php wp_nonce_field('edit_cold_caller'); ?>

                    <input type="hidden" name="action" value="edit_cold_caller">
                    <input type="hidden" name="cold_caller_id" value="<?= $_SESSION['cold_caller_id']; ?>">
                    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                    <div class="invoice_table_wrapper">
                        <h1>Edit Profile</h1>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <tr>
                                    <th>Name</th>
                                    <td> <input class="form-control" type="text" name="name"  value="<?= $cold_caller_data->name; ?>"></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><input class="form-control" type="text" name="email" value="<?= $cold_caller_data->email; ?>"></td>
                                </tr>
                                <tr>
                                    <th>Phone No</th>
                                    <td><input class="form-control" type="text" name="phone" value="<?= $cold_caller_data->phone_no; ?>"></td>
                                </tr>
								<tr>
                                    <th>Branch</th>
									<td><select name="branch_id" class="form-control select2-field">
									<option value="">Select</option>
									<?php if(is_array($branches) && count($branches)>0): ?>
											<?php foreach($branches as $branch): ?>
												<option value="<?= $branch->id; ?>" <?= $cold_caller_data->branch_id == $branch->id ? 'selected' : ''; ?>><?= $branch->location_name; ?></option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select></td>
                                </tr>
								<tr>
									<th colspan="2">
										<button class="btn btn-primary"><span><i class="fa fa-refresh"></i></span> Update Profile</button>
									</th>
								</tr>

                            </table>
                        </div>

                        </div>

                </form>        
            </div>
        </div>
    </div>

<?php else: ?>
    <h1>No Record Found</h1>
<?php endif; ?>