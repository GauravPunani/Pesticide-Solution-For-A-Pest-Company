<?php

$upload_dir = wp_upload_dir();
$base_path = $upload_dir['baseurl'];
$gam_vidoes =  (new GamFunctions)->getGamVideos();

$employee_types = (new Employee\Employee)->getEmployeeTypes();

?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">GAM Videos</h3>
                    <button data-toggle="modal" data-target="#uploadVideoModal" class="btn btn-default text-right"><span><i class="fa fa-upload"></i> Upload Video/Script</span></button>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Video</th>
                                <th>Script</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($gam_vidoes) && count($gam_vidoes) > 0): ?>
                                <?php foreach($gam_vidoes as $gam_video): ?>
                                    <tr>
                                        <td><?= $gam_video->name; ?></td>
                                        <td><?= str_replace(',', ' || ', $gam_video->type); ?></td>
                                        <?php if(!empty($gam_video->aws_s3_key)): ?>
                                            <td>
                                                <a onclick="viewVideo('<?= $gam_video->aws_s3_key; ?>')" class="btn btn-primary" href="javascript:void(0)"><span><i class="fa fa-eye"></i></span> View</a>
                                            </td>
                                        <?php else: ?>
                                            <td>-</td>
                                        <?php endif; ?>
                                        <?php if(!empty($gam_video->script_path)): ?>
                                            <td>
                                                <a target="_blank" class="btn btn-primary" href="<?= $base_path.$gam_video->script_path; ?> "><span><i class="fa fa-eye"></i></span> View</a>
                                            </td>
                                        <?php else: ?>
                                            <td>-</td>
                                        <?php endif; ?>
                                        <td><?= date('d M Y',strtotime($gam_video->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_template_part('template-parts/employee/training-material-popup');?>

<div id="uploadVideoModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Upload Video/Script</h4>
            </div>
            <div class="modal-body">
                <form id="uploadVideoForm">
                    
                    
                
                    <div class="form-group">
                        <label for="">Title</label>
                        <input type="text" class="form-control" name="title">
                    </div>

                    <div class="form-group">
                        <label for="">Employee Type</label>
                        <select name="employee_type" class="form-control select2-field">
                            <option value="">Select</option>
                            <?php foreach($employee_types as $employee_type): ?>
                                <option value="<?= $employee_type->slug; ?>"><?= $employee_type->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="">Video</label>
                        <input accept="video/*" type="file" name="video" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="">Script</label>
                        <input type="file" name="script" class="form-control">
                    </div>

                    <button class="btn btn-primary"><span><i class="fa fa-upload"></i> Upload Video/Script</span></button>

                    <div class="progress-div hidden">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
                        </div>
                    </div>                    

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        $(document).ready(function(){
            $('#uploadVideoForm').validate({
                rules: {
                    title: "required",
                    employee_type: "required",
                    video: {
                        required: function(){
                            return $('#uploadVideoForm input[name="script"]').val() === '' ? true : false;
                        }
                    },
                    script: {
                        required: function(){
                            return $('#uploadVideoForm input[name="video"]').val() ===  '' ? true : false;
                        }
                    }
                },
                submitHandler: function(form){

                    let formdata = new FormData(form);
                    formdata.append('action','upload_video_script');

                    $.ajax({
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: formdata,
                        type: 'POST',
                        dataType:"json",
                        xhr: function() {
                            $('.progress-div').removeClass('hidden');
                                let xhr = new window.XMLHttpRequest();
                                xhr.upload.addEventListener("progress", function(evt) {
                                    if (evt.lengthComputable) {
                                        let percentComplete = (evt.loaded / evt.total) * 100;
                                        console.log("done is "+percentComplete);
                                        percentComplete=Math.floor(percentComplete);
                                        $('.progress-bar').width(`${percentComplete}%`);
                                        $('.progress-bar').html(`${percentComplete}%`);
                                    }
                                }, false);
                                return xhr;
                            },
                        processData: false,
                        contentType: false,
                        success: function(data){
                            alert(data.message);
                            if(data.status=="success"){
                                location.reload();
                            }
                            else{
                                $('.submit_btn').attr('disabled', false);
                                $('.progress-div').addClass('hidden');
                                $('.progress-bar').width(`0%`);
                                $('.progress-bar').html(`0%`);                            
                            }
                        }
                    });                    

                }
            })
        })
    })(jQuery);
</script>