<?php

global $wpdb;

$week_start_date = date('Y-m-d',strtotime('monday this week'));
$week_end_date = date('Y-m-d',strtotime(' +6 days',strtotime($week_start_date)));

$cold_caller_data = $wpdb->get_results("
	select id, name,

	(
		select count(*) 
		from wp_leads L 
		where L.cold_caller_id = CC.id
		and date(L.date) >= '$week_start_date'
		and date(L.date) <= '$week_end_date'
	) as total_leads

	from wp_cold_callers CC 
	where CC.status = 'active'
	order by total_leads desc
");

?>
<div class="card full_width table-responsive">
	<div class="card-body">
		<h3 class="page-header">Cold Callers Performance</h3>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>Cold Caller Name </th>
					<th>Week</th>
					<th>Total Leads</th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($cold_caller_data) && count($cold_caller_data) > 0): ?>
					<?php foreach($cold_caller_data as $data): ?>
						<tr>
							<td><?= $data->name; ?></td>
							<td><?= date('d M Y',strtotime($week_start_date))." To ".date('d M Y',strtotime($week_end_date)); ?></td>
							<td><?= $data->total_leads; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="4">No Cold Caller Found With Data</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
