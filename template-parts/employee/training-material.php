<?php

$upload_dir = wp_upload_dir();
$base_path = $upload_dir['baseurl'];
$employee_id = (new Employee\Employee)->__getLoggedInEmployeeId();
$employee_slug = (new Employee\Employee)->getEmployeeTypeSlug($employee_id);
$employee_videos =  (new GamFunctions)->getGamVideos($employee_slug);
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Training Material</h3>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Video</th>
                                <th>Script</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($employee_videos) && count($employee_videos) > 0): ?>
                                <?php foreach($employee_videos as $employee_video): ?>
                                    <tr>
                                        <td><?= $employee_video->name; ?></td>
                                        <?php if(!empty($employee_video->aws_s3_key)): ?>
                                            <td>
                                                <a onclick="viewVideo('<?= $employee_video->aws_s3_key; ?>')" class="btn btn-primary" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View</a>
                                            </td>
                                        <?php else: ?>
                                            <td>-</td>
                                        <?php endif; ?>
                                        <?php if(!empty($employee_video->script_path)): ?>
                                            <td>
                                                <a target="_blank" class="btn btn-primary" href="<?= $base_path.$employee_video->script_path; ?> "><span><i class="fa fa-download"></i></span> download</a>
                                            </td>
                                        <?php else: ?>
                                            <td>-</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No Training Material Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_template_part('template-parts/employee/training-material-popup');?>