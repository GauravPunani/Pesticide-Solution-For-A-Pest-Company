<?php

if(!empty($_GET['branch-id'])) return get_template_part('/include/admin/branches/edit-branch');

global $wpdb;

$conditions = [];

if(!empty($_GET['tab'])){
  if($_GET['tab'] == "active-branches") $conditions[] = " B.status = 1";
  if($_GET['tab'] == "inactive-branches") $conditions[] = " B.status = 0";
}

$conditions = count($conditions) > 0 ? (new GamFunctions)->generate_query($conditions) : '';

$branches = $wpdb->get_results("
  SELECT B.*, CAI.email from 
  {$wpdb->prefix}branches B
  left join {$wpdb->prefix}callrail_accounts_info CAI
  on B.callrail_id = CAI.id
  $conditions
");

?>

<div class="container">
  <div class="row">
    <div class="col-sm-12">
      <div class="card full_width table-responsive">
        <div class="card-body">
          <?php (new GamFunctions)->getFlashMessage(); ?>
          <h3 class="page-header">Branches</h3>
          <p><?= count($branches); ?> brances found.</p>
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>Location Name</th>
                <th>Callrail Account</th>
                <th>Review Link</th>
				        <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if(is_array($branches) && count($branches)>0): ?>
              <?php foreach ($branches as $branch): ?>
                <tr>
                    <td><?= $branch->location_name; ?></td>
                    <td><?= $branch->email; ?></td>
                    <td><?= $branch->review_link; ?></td>
					          <td><?php if($branch->status == 1) echo 'Active'; else { echo 'Inactive'; } ?></td>
                    <td><a class="btn btn-primary" href="<?= $_SERVER['REQUEST_URI']; ?>&branch-id=<?=$branch->id; ?>"><span><i class="fa fa-edit"></i></span></a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
