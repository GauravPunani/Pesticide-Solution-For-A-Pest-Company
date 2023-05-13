<?php

global $wpdb;
$upload_dir=wp_upload_dir();
$quote_id=$args['data'];
$quote=$wpdb->get_row("
    select * 
    from {$wpdb->prefix}commercial_quotesheet 
    where id='$quote_id'
");

$branch = (new Branches)->getBranchName($quote->branch_id);
$tech_name = (new Technician_details)->getTechnicianName($quote->technician_id);

?>

<div class="card full_width table-responsive">
    <div class="card-body">
        <?php if($quote): ?>
            <h3 class="page-header">Commercial Quote</h3>
                <table class="table table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Client Name</th>
                            <td><?= $quote->client_name; ?></td>
                        </tr>
                        <tr>
                            <th>Client Address</th>
                            <td><?= $quote->client_address; ?></td>
                        </tr>
                        <tr>
                            <th>Decision Maker Name</th>
                            <td><?= $quote->decision_maker_name; ?></td>
                        </tr>
                        <tr>
                            <th>Client Phone</th>
                            <td><?= $quote->client_phone; ?></td>
                        </tr>
                        <tr>
                            <th>Client Email</th>
                            <td><?= $quote->clientEmail; ?></td>
                        </tr>
                        <tr>
                            <th>Technician</th>
                            <td><?= $tech_name; ?></td>
                        </tr>
                        <tr>
                            <th>Branch</th>
                            <td><?= $branch; ?></td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Frequency of visits?</th>
                            <td>
                            <?php
                            if(!empty($quote->no_of_times)){
                                if(!empty($quote->visits_duration)){
                                    echo sprintf("Every %u %s",$quote->no_of_times,$quote->visits_duration);
                                }else{
                                    echo sprintf("Every %u %s",$quote->no_of_times,'month');
                                }
                            }else{
                                echo  (new GamFunctions)->beautify_string($quote->visits_duration);
                            }
                            ?>
                            </td>
                        </tr>
                        <tr>
                            <th>INITIAL COST</th>
                            <td><?= $quote->initial_cost; ?></td>
                        </tr>
                        <tr>
                            <th>COST PER VISIT</th>
                            <td><?= $quote->cost_per_visit; ?></td>
                        </tr>
                        <tr>
                            <th>Client Notes By Technician</th>
                            <td><?= nl2br($quote->notes_for_client); ?></td>
                        </tr>
                        <tr>
                            <th>Office Notes By Technician</th>
                            <td><?= nl2br($quote->tech_notes_for_office); ?></td>
                        </tr>
                        <tr>
                            <th>DATE</th>
                            <td><?= $quote->date; ?></td>
                        </tr>
                        <tr>
                            <th>CALENDAR EVENT ID</th>
                            <td><?= $quote->calendar_event_id; ?></td>
                        </tr>
                    </tbody>
                </table>
        <?php else: ?>
            <h3 class="text-center text-danger">No Record Found</h3>
        <?php endif; ?>
    </div>
</div>
