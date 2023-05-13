<?php

$user = $args['user'];
$florida_locations=['miami','florida','tampa','fort_myers','global'];
$technician_id = (new Technician_details)->get_technician_id();

// get vehicle id to check if vehicle is assigned or not
$vehicle_id = (new CarCenter)->getTechnicianVehicleId( $technician_id );

if($vehicle_id){   
    $vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);
}
else{
    $vehicle_data = false;
}

?>

<ul class="nav nav-pills nav-stacked">
    <li class="active"><a href="<?= site_url(); ?>/technician-dashboard"><span><i class="fa fa-dashboard"></i></span> Dashboard</a></li>
    <li><a href="?view=training-material"><span><i class="fa fa-video-camera"></i></span> Training Material</a></li>
    <li><a href="?view=service-reports"><span><i class="fa fa-file"></i></span> Service Reports</a></li>

    <li><a href="?view=calendar-events"><span><i class="fa fa-calendar"></i></span> Calendar Events</a></li>

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-file"></i></span> Invoice
        <span class="caret"></span></a>
        <ul class="dropdown-menu">

            <li><a target="_blank" href="<?= site_url(); ?>/invoice"><span><i class="fa fa-home"></i></span> Create Invoice</a></li>

            <li><a target="_blank" href="?view=invoice"><span><i class="fa fa-building-o"></i></span> View Invoices</a></li>

        </ul>
    </li>

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-file"></i></span> Quotesheet
        <span class="caret"></span></a>

        <ul class="dropdown-menu">

            <li class="dropdown-submenu">
                <a class="test" tabindex="-1" href="#"><span><i class="fa fa-plus"></i></span> Create <span class="caret"></span></a>
                <ul class="dropdown-menu submenu">
                    <li><a target="_blank" href="<?= site_url(); ?>/residential-quote-sheet"><span><i class="fa fa-home"></i></span> Residential</a></li>
                    <li><a target="_blank" href="<?= site_url(); ?>/commercial-quote-sheet "><span><i class="fa fa-building-o"></i></span> Commercial</a></li>                
                </ul>
            </li>            

            <li class="dropdown-submenu">
                <a class="test" tabindex="-1" href="#"><span><i class="fa fa-eye"></i></span> View <span class="caret"></span></a>
                <ul class="dropdown-menu submenu">
                    <li><a href="?view=residential-quote"><span><i class="fa fa-home"></i></span> Residential</a></li>
                    <li><a href="?view=commercial-quote"><span><i class="fa fa-building-o"></i></span> Commercial</a></li>
                </ul>
            </li>            

            
        </ul>
    </li>

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-wrench"></i></span> Maintenance Contract <span class="caret"></span></a>
        <ul class="dropdown-menu">

            <li class="dropdown-submenu">
                <a class="test" tabindex="-1" href="#"><span><i class="fa fa-eye"></i></span> Create <span class="caret"></span></a>
                <ul class="dropdown-menu submenu">
                    <li><a target="_blank" href="<?= (new Maintenance)->monthlyPageUrl(); ?>"><span><i class="fa fa-wrench"></i></span> Monthly</a></li>
                    <li><a target="_blank" href="<?= (new Maintenance)->quarterlyPageUrl(); ?>"><span><i class="fa fa-wrench"></i></span> Quarterly</a></li>
                    <li><a target="_blank" href="<?= (new Maintenance)->specialPageUrl(); ?>"><span><i class="fa fa-wrench"></i></span> Special</a></li>
                    <li><a target="_blank" href="<?= (new Maintenance)->commercialPageUrl(); ?>"><span><i class="fa fa-wrench"></i></span> Commercial</a></li>
                    <li><a target="_blank" href="<?= (new Maintenance)->yearlyTermitePageUrl(); ?>"><span><i class="fa fa-wrench"></i></span> Yearly Termite</a></li>
                </ul>
            </li>

            <li class="dropdown-submenu">
                <a class="test" tabindex="-1" href="#"><span><i class="fa fa-eye"></i></span> View <span class="caret"></span></a>
                <ul class="dropdown-menu submenu">
                    <li><a href="?view=monthly-maintenance"><span><i class="fa fa-wrench"></i></span> Monthly</a></li>
                    <li><a href="?view=quarterly-maintenance"><span><i class="fa fa-wrench"></i></span> Quarterly</a></li>
                    <li><a href="?view=special-maintenance"><span><i class="fa fa-wrench"></i></span> Special</a></li>
                    <li><a href="?view=commercial-maintenance"><span><i class="fa fa-wrench"></i></span> Commercial</a></li>
                </ul>
            </li>
        </ul>
    </li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-wrench"></i></span> Termite Paperwork
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a target="_blank" href="<?= site_url(); ?>/termite-certificate"><span><i class="fa fa-certificate"></i></span> Termite Certificate</a></li>
            <li><a target="_blank" href="<?= site_url(); ?>/termite-graph"><span><i class="fa fa-bar-chart"></i></span> Termite Graph</a></li>
            <?php if(in_array($user->state,$florida_locations)): ?>
                <li><a target="_blank" href="<?= site_url(); ?>/florida-consumer-consent-form"><span><i class="fa fa-file"></i></span> Florida Consumer Consent Form</a></li>
                <li><a target="_blank" href="<?= site_url(); ?>/florida-wood-inspection-report"><span><i class="fa fa-file"></i></span> Florida Wood Inspection Report</a></li>
            <?php endif; ?>
            <li><a target="_blank" href="<?= site_url(); ?>/npma33"><span><i class="fa fa-file"></i></span> NPMA 33 FORM</a></li>
        </ul>
    </li>

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-money"></i></span> Deposit Proofs
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="?view=add-proof-of-deposit"><span><i class="fa fa-plus"></i></span> Add Daily Deposit Proof</a></li>
            <li><a href="?view=daily-deposit-listing"><span><i class="fa fa-eye"></i></span> View Daily Deposit Proof</a></li>
        </ul>
    </li>
    <li><a href="?view=weekly-payment-proof"><span><i class="fa fa-money"></i></span> Weekly Payment Proofs</a></li>
    <li><a href="?view=payment-eligibility"><span><i class="fa fa-money"></i></span> Payment Eligibility</a></li>

    <!-- <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-file"></i></span> Notes
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="?view=add-notes"><span><i class="fa fa-plus"></i></span> Add Normal Notes</a></li>
            <li><a href="?view=add-special-notes"><span><i class="fa fa-plus"></i></span> Add Special Notes</a></li>
            <li><a href="?view=view-notes"><span><i class="fa fa-eye"></i></span> View Normal Notes</a></li>
            <li><a href="?view=view-special-notes"><span><i class="fa fa-eye"></i></span> View Special Notes</a></li>
        </ul>
    </li> -->

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-exchange"></i></span> Reimbursement<span class="caret"></span></a>

        <ul class="dropdown-menu">
            <li><a href="?view=reimbursement"><span><i class="fa fa-plus"></i></span> Add Reimbursement Proof</a></li>
            <li><a href="?view=pending-reimbursement"><span><i class="fa fa-clock-o"></i></span> Pending Reimbursement</a></li>
            <li><a href="?view=reimbursed"><span><i class="fa fa-exchange"></i></span> Reimbursed</a></li>
        </ul>
    </li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-user"></i></span> Profile<span class="caret"></span></a>

        <ul class="dropdown-menu">
            <li><a href="?view=profile"><span><i class="fa fa-eye"></i></span> View Profile</a></li>
            <li><a href="?view=edit-profile"><span><i class="fa fa-edit"></i></span> Edit Profile</a></li>
            <li><a href="?view=resign"><span><i class="fa fa-ban"></i></span> Resign</a></li>
        </ul>
    </li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-car"></i></span> Car Center<span class="caret"></span></a>

        <ul class="dropdown-menu">
            <li><a href="?view=vehicle-details"><span><i class="fa fa-eye"></i></span> Vehicle Details</a></li>
            <?php if($vehicle_data): ?>
                <li><a href="?view=mileage-proof"><span><i class="fa fa-upload"></i></span> Mileage Proof</a></li>
                <li><a href="?view=oil-change-proof"><span><i class="fa fa-upload"></i></span> Oil Change Proof</a></li>
                <li><a href="?view=vehicle-condition-proof"><span><i class="fa fa-upload"></i></span> Vehicle Condition Proof</a></li>
                <li><a href="?view=break-pad-proof"><span><i class="fa fa-upload"></i></span> Break Pad Proof</a></li>
                <li><a href="?view=car-wash-proof"><span><i class="fa fa-upload"></i></span> Car Wash Proof</a></li>
                <?php if((new Technician_Details)->isPesticideDecalPending($technician_id, $user->branch_id, $vehicle_data->pesticide_decal)): ?>
                    <li><a href="?view=pesticide-decal-proof"><span><i class="fa fa-upload"></i></span> Pesticide Decal Proof</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </li>
	<li><a href="?view=view-task"><span><i class="fa fa-tasks"></i></span> Task</a></li>
    <li><a href="?view=view-prices"><span><i class="fa fa-money"></i></span> Price Sheet </a></li>
	<!-- <li><a href="?view=add-prospectus"><span><i class="fa fa-plus"></i></span> Add prospective client</a></li> -->
    <li>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <?php wp_nonce_field('logout_technician'); ?>
            <input type="hidden" name="action" value="logout_technician">
            <button class="btn btn-default"><span><i class="fa fa-sign-out"></i></span> Log Out</button>
        </form>
    </li>

</ul>

<style>
.dropdown-submenu {
  position: relative;
}

.dropdown-submenu .dropdown-menu {
  top: 0;
  left: 100%;
  margin-top: -1px;
}
</style>

<script>
    (function($){
        $(document).ready(function(){
        $('.dropdown-submenu a.test').on("click", function(e){
            $('.submenu').hide();
            $(this).next('ul').toggle();
            e.stopPropagation();
            e.preventDefault();
        });
        });
    })(jQuery);
</script>