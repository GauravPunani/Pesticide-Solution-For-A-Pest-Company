<?php

global $wpdb;

$week=$args['week'];

$branches = (new Branches)->getAllBranches(true);

$data=[];

if(is_array($branches) && count($branches)>0){

    $start_date=date('Y-m-d',strtotime('tuesday last week',strtotime($week)));
    $end_date=date(('Y-m-d'),strtotime('monday this week',strtotime($week)));

    foreach ($branches as $branch) {

        $pending_invoices=$wpdb->get_var("
            select COUNT(*) 
            from {$wpdb->prefix}invoices 
            where branch_id='$branch->id' 
            and (callrail_id IS NULL or callrail_id='unknown' or callrail_id='') 
            and DATE(date) >= '$start_date' 
            and is_deleted != 1
            and DATE(date) <= '$end_date'
        ");

        if($pending_invoices > 0){
            $data[] = [
                'branch_id'         =>  $branch->id,
                'slug'              =>  $branch->slug,
                'pending_invoices'  =>  $pending_invoices          
            ];
        }
    }
}
?>

<?php if(is_array($data) && count($data)>0): ?>
    <h5>These invoices are pending to be assigned callrail number between date range <?= date('d M Y',strtotime($start_date))." - ". date('d M Y',strtotime($end_date));   ?></h5>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Location</th>
                <th>No. of pending</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $val): ?>
                <?php if($val>0): ?>
                    <tr>
                        <td><?= (new GamFunctions)->beautify_string($val['slug']); ?></td>
                        <td><?= $val['pending_invoices']; ?></td>
                        <td>
                            <a target="_blank" href="<?= admin_url("admin.php?page=invoice&branch_id=".$val['branch_id']."&other_filters=unknown_leads&date_from=".$start_date."&date_to=".$end_date."") ?>" class="btn btn-primary">Go To Page <span><i class="fa fa-arrow-right"></i></span></a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No invoice is pending to be attributed for this week</p>
<?php endif; ?>
