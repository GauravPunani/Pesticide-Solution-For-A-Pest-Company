<?php
$client_data=$args['data'];
?>

<table class="table table-striped table-hover">
    <tbody>
        <tr>
            <th>Establishment name</th>
            <td><?= $client_data['establishement_name']; ?></td>
        </tr>
        <tr>
            <th>Responsible person in charge name</th>
            <td><?= $client_data['person_in_charge']; ?></td>
        </tr>
        <tr>
            <th>Location</th>
            <td><?= (new Branches)->getBranchName($client_data['branch_id']); ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= $client_data['client_address']; ?></td>
        </tr>
        <tr>
            <th>Establishment phone number</th>
            <td><?= $client_data['establishment_phoneno']; ?></td>
        </tr>
        <tr>
            <th>Responsible person in charge phone number</th>
            <td><?= $client_data['res_person_in_charge_phone_no']; ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= $client_data['client_email']; ?></td>
        </tr>
        <tr>
            <th>Cost per visit</th>
            <td><?= $client_data['cost_per_visit']; ?></td>
        </tr>
        <tr>
            <th>Frequency of visit</th>
            <td><?= $client_data['frequency_of_visit']; ?></td>
        </tr>
        <tr>
            <th>Notes</th>
            <td><?= nl2br($client_data['client_notes']); ?></td>
        </tr>
        <tr>
            <th>Per</th>
            <td><?= $client_data['frequency_per']; ?></td>
        </tr>
        <tr>
            <th>Preffered time and days of service</th>
            <td><?= $client_data['prefered_days']." - ". date('h:i A',strtotime($client_data['prefered_time'])); ?></td>
        </tr>
        <tr>
            <th>Contract Start Date</th>
            <td><?= !empty($client_data['contract_start_date']) ? date('d M Y',strtotime($client_data['contract_start_date'])) : ''; ?></td>
        </tr>
        <tr>
            <th>Contract End Date</th>
            <td><?= !empty($client_data['contract_end_date']) ? date('d M Y',strtotime($client_data['contract_end_date'])) : ''; ?></td>
        </tr>
    </tbody>
</table>