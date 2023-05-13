<?php

$client_data=$args['data'];

?>

<table class="table table-striped table-hover">
    <tbody>
        <tr>
            <th>Service Type</th>
            <td><?=  $client_data['service_type']; ?></td>
        </tr>
        <tr>
            <th>Cost Per Visit</th>
            <td>$<?=  $client_data['cost']; ?></td>
        </tr>
        <tr>
            <th>Every</th>
            <td><?=  $client_data['days']; ?> <?=  (new GamFunctions)->getFormattedServiceDuration($client_data['service_type']); ?></td>
        </tr>
        <tr>
            <th>From Date</th>
            <td><?= !empty($client_data['from_date']) ? date('d M Y',strtotime($client_data['from_date'])) : ''; ?></td>
        </tr>
        <tr>
            <th>To Date</th>
            <td><?=  !empty($client_data['to_date']) ? date('d M Y',strtotime($client_data['to_date'])) : ''; ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?=  $client_data['client_name']; ?></td>
        </tr>
        <tr>
            <th>Location</th>
            <td><?= (new Branches)->getBranchName($client_data['branch_id']); ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?=  $client_data['client_address']; ?></td>
        </tr>
        <tr>
            <th>Phone No.</th>
            <td><?=  $client_data['client_phone']; ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?=  $client_data['client_email']; ?></td>
        </tr>
        <tr>
            <th>Notes</th>
            <td><?=  nl2br($client_data['notes']); ?></td>
        </tr>
    </tbody>
</table>