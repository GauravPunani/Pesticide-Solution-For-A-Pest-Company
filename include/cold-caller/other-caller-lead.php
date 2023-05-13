<?php

global $wpdb;
$maxleadsid=$wpdb->get_results("select 
								cold_caller_id
								from {$wpdb->prefix}leads
								WHERE date >= DATE(NOW()) - INTERVAL 7 DAY
								group by cold_caller_id
								order by count(*) desc
								LIMIT 1");

$lead_id = $maxleadsid[0]->cold_caller_id; 
$no_of_leads=$wpdb->get_var("select COUNT(*) from {$wpdb->prefix}leads WHERE cold_caller_id='{$lead_id}' AND date >= DATE(NOW()) - INTERVAL 7 DAY");

$cold_caller = $wpdb->get_row("select name from {$wpdb->prefix}cold_callers WHERE id='{$lead_id}'");

?>

<div class="card full_width table-responsive">
            <div class="card-body">
			<h4 class="page-header">Cold Caller Score Board</h4>
			<div class="perfomance_html">
            <p>Last Week Highest Lead</p>
            <table class="table table-striped table-hover">
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td><?php echo $cold_caller->name;?></td>
                        <th>Total Leads</th>
                        <td><?php echo $no_of_leads;?></td>
                    </tr>
                </tbody>
            </table>
			</div>
            </div>
</div>