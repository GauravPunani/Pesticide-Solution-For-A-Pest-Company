<?php

global $wpdb;

$branch_id = $branch_slug = "";

if(isset($_GET['branch_id']) && !empty($_GET['branch_id']) && $_GET['branch_id']!="all"){
    $branch_id = esc_html($_GET['branch_id']);
    $branch_slug = (new Branches)->getBranchSlug($branch_id);
}

$date = date('Y-m-d');

if(isset($_GET['date']) && !empty($_GET['date'])){
    $date = esc_html($_GET['date']);
}

$technicians = (new Technician_details)->get_all_technicians(true, $branch_slug);

?>
<div class="container">
    <div class="row">
        <h3 class="text-center">Office Notes</h3>
        <div class="col-sm-12">
            <?php (new Navigation)->location_tabs(@$_GET['branch_id']); ?>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <form action="<?= $_SERVER['PHP_SELF']; ?>">
                        <?php if(is_array($_GET) && count($_GET)>0): ?>
                            <?php foreach($_GET as $key=>$val): ?>
                                <input type="hidden" name="<?= $key; ?>" value="<?= $val; ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <h3 class="text-center">Search notes by date</h3>
                        <div class="form-group">
                            <label for="">Select Date</label>
                            <input type="date" name="date" >
                        </div>
                        <button class="btn btn-primary"><span><i class="fa fa-search"></i></span> Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center">Notes</h4>
                    <?php if(is_array($technicians) && count($technicians)>0): ?>
                        <?php foreach($technicians as $technician): ?>
                            <p>Techician : <b><?= $technician->first_name." ".$technician->last_name;?></b></p>
                            <?php $notes = $wpdb->get_results("
                                select * from {$wpdb->prefix}office_notes 
                                where technician_id='{$technician->id}' 
                                and DATE(date) = '$date'
                            ");?>
                            <?php if(is_array($notes) && count($notes)>0): ?>
                                <ul>
                                    <?php foreach($notes as $note): ?>
                                        <?php if(!empty($note->note)): ?>
                                            <li><?= $note->note; ?> - <b><?= $note->client_name; ?></b></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?> 
                                </ul>
                            <?php else: ?>
                                <p>No Notice Found</p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No Technician Found for the location</p> 
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>