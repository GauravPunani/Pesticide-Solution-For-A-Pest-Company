<?php

$contract = $args['data'];

?>

<table class="table table-striped table-hover">
    <tbody>
        <tr>
            <th>Name</th>
            <td><?= $contract->client_name; ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= $contract->client_address; ?></td>
        </tr>
        <tr>
            <th>Phone No.</th>
            <td><?= $contract->client_phone_no; ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= $contract->client_email; ?></td>
        </tr>
        <tr>
            <th>Cost Per Month</th>
            <td><?= $contract->cost_per_month; ?></td>
        </tr>
        <tr>
            <th>Notes</th>
            <td><?= nl2br($contract->client_notes); ?></td>
        </tr>
        <tr>
            <th>Contract Start Date</th>
            <td><?= date('d M Y',strtotime($contract->contract_start_date)); ?></td>
        </tr>
        <tr>
            <th>Contract End Date</th>
            <td><?= date('d M Y',strtotime($contract->contract_end_date)); ?></td>
        </tr>
        <tr>
            <th>Type</th>
            <td><?= $contract->type; ?></td>
        </tr>
    </tbody>
</table>
