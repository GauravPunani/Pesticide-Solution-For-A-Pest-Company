<?php

global $wpdb;

$codes=$wpdb->get_results("select * from {$wpdb->prefix}technician_codes WHERE DATE(`date_created`) = CURDATE() order by date_created DESC");
?>

<div class="row">
	<div class="col-md-6">
		<div class="card full_width table-responsive">
			<div class="card-head">
				<p class="text-center"><b>Verification Codes</b></p>
			</div>
			<div class="card-body">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Name</th>
							<th>Code</th>
							<th>Type</th>
							<th>Link</th>
							<th>Time</th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($codes) && count($codes)>0): ?>
							<?php foreach ($codes as $key => $value): ?>
								<tr>
									<td><?= $value->name; ?></td>
									<td><?= $value->code; ?></td>
									<td><?= !empty($value->type) ? str_replace('_',' ',ucwords($value->type))  : ""; ?></td>
									<?php if(!empty($value->link)): ?>
									<td><a target="_blank" href="<?= $value->link; ?>">View <?= str_replace('_',' ',ucwords($value->type)); ?></a></td>
									<?php else: ?>
									<td></td>
									<?php endif; ?>
									<td><?= date('h:i A', strtotime($value->date_created)); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="5">No code right now.</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>				
			</div>
		</div>
	</div>
</div>


