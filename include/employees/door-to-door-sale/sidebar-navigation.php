<ul class="nav nav-pills nav-stacked">
    <li class="active"><a href="<?= site_url(); ?>/employee-dashboard"><span><i class="fa fa-dashboard"></i></span> Dashboard</a></li>
    <li><a href="?view=training-material"><span><i class="fa fa-video-camera"></i></span> Training Material</a></li>
    <li><a href="?view=view-task"><span><i class="fa fa-tasks"></i></span> Task</a></li>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span><i class="fa fa-exchange"></i></span> Reimbursement<span class="caret"></span></a>

        <ul class="dropdown-menu">
            <li><a href="?view=reimbursement"><span><i class="fa fa-plus"></i></span> Add Reimbursement Proof</a></li>
            <li><a href="?view=pending-reimbursement"><span><i class="fa fa-clock-o"></i></span> Pending Reimbursement</a></li>
            <li><a href="?view=reimbursed"><span><i class="fa fa-exchange"></i></span> Reimbursed</a></li>
        </ul>
    </li>
    <li>
        <?php get_template_part('include/employees/templates/logout-button'); ?>
    </li>
</ul>