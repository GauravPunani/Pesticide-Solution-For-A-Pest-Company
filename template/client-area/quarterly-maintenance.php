<?php if(isset($args['data'])): ?>
    <?php $args['data']=(array)$args['data']; ?>
    <table class="table table-hover">
        <tbody>
            <tr>
                <th>Name</th>
                <td><?= $args['data']['client_name']; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?= $args['data']['client_address']; ?></td>
            </tr>
            <tr>
                <th>Phone No.</th>
                <td><?= $args['data']['client_phone_no']; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= $args['data']['client_email']; ?></td>
            </tr>
            <tr>
                <th>Cost Per Quarter</th>
                <td><?= $args['data']['cost_per_month']; ?></td>
            </tr>
            <tr>
                <th>Maintenance Charges Interval</th>
                <td><?= $args['data']['charge_type']; ?></td>
            </tr>
            <tr>
                <th>Total Cost of Contract</th>
                <td><?= $args['data']['total_cost']; ?></td>
            </tr>
            <tr>
            <th>Notes</th>
                <td><?= nl2br($args['data']['client_notes']); ?></td>
            </tr>
            <tr>
                <th>Contract Start Date</th>
                <td><?= date('d M Y',strtotime($args['data']['contract_start_date'])); ?></td>
            </tr>
            <tr>
                <th>Contract End Date</th>
                <td><?= date('d M Y',strtotime($args['data']['contract_end_date'])); ?></td>
            </tr>
            <tr>
                <th>Type</th>
                <td><?= ucwords($args['data']['type']); ?></td>
            </tr>
        </tbody>
    </table>  
<?php endif; ?>
