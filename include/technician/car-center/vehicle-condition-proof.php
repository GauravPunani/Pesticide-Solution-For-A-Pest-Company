<?php
$technician_id = (new Technician_details)->get_technician_id();
$vehicle_id = (new CarCenter)->getTechnicianVehicleId($technician_id);
$vehicle_data = (new CarCenter)->getVehicleById($vehicle_id);

?>

<div class="row">
    <div class="col-sm-12">
        <?php if($vehicle_data): ?>
            <form enctype="multipart/form-data" method="post" id="vehicle_condition_form" action="<?= admin_url('admin-post.php'); ?>" class="res-form">
                <h2 class="form-head text-center">Vehicle Condition Proof</h2>
                <?php (new GamFunctions)->getFlashMessage(); ?>

                <?php wp_nonce_field('upload_vehicle_condition'); ?>
                <input type="hidden" name="action" value="upload_vehicle_condition">
                <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                <div class="notice notice-error">
                    <h4 class="text text-danger">*Read all the instructions first before recording the video.</h4>
                    <ul>
                        <li>
                            Make sure to view all these items while taping video.
                            <ul>
                                <li>Entire exterior</li>
                                <li>Entire interior. </li>
                                <li>Floors,</li>
                                <li>Mirrors</li>
                                <li>Tires</li>
                                <li>windows</li>
                                <li>seats</li>
                                <li>dashboard while cars started </li>
                                <li>Organized chemicals and materials</li>
                                <li>Gloveboxes</li>
                            </ul>
                        </li>
                        <li>You must verbally say today's date and mileage</li>
                        <li>Make sure to show current mileage on dashboard and proof of mileage as well while taping video.</li>
                        <li>Video must be recorded in one shot. There must be no retakes or edits in the video.</li>
                        <li>Make sure video is in mp4 format.</li>
                        <li>Makre sure video size is not more than 500 MB.</li>
                    </ul>
                </div>


                <div class="form-group">
                    <label for="">Upload Video</label>
                    <input type="file" class="form-control" name="vehicle_video" id="vehicle_video" accept="video/mp4">
                </div>

                <button class="btn btn-primary submit_btn"><span><i class="fa fa-upload"></i></span> Upload Vehicle Condition Proof</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <p>No Vehicle linked to your account , Please add a new vehicle by <a href="<?= site_url()."/technician-dashboard/?view=vehicle-details&cnw=true" ?>">Clicking here</a></p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>

(function($){
    $(document).on('ready', function(){

        const chunkSize = 5000000; //5 Mb
        let start = 0;
        let chunkCounter = 0;
        let numberofChunks = 0;
        let file;
        let chunkEnd = 0;

        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param)
        }, 'File size must be less than 500Mb');        

        $('#vehicle_condition_form').validate({
            rules: {
                vehicle_video: {
                    required: true,
                    filesize: 500000000
                }
            }
        })

        $(document).on('click', '.retry_uploading', function() {
            showUploader();
            createChunk();
        })

        $('#vehicle_condition_form').on('submit', function(e){
            e.preventDefault();

            file = $('#vehicle_video').prop('files')[0];
            const filename = file.name;
            numberofChunks = Math.ceil(file.size/chunkSize);

            console.log('video start', start);

            showUploader();
            createChunk();
        })

        function createChunk(){

            chunkEnd = Math.min(start + chunkSize , file.size);
            const chunk = file.slice(start, chunkEnd);
            console.log(chunk);
            console.log("i created a chunk of video" + start + "-" + chunkEnd + "minus 1");
            const chunkForm = new FormData();

            chunkForm.append('file', chunk, file.name);
            chunkForm.append('file_name', file.name);
            chunkForm.append('chunk_end', chunkEnd);
            chunkForm.append('action', "upload_vehicle_condition");
            chunkForm.append('_wpnonce', "<?= wp_create_nonce('upload_vehicle_condition'); ?>");

            if(chunkEnd == file.size){
                chunkForm.append('finished', 'true');
            }
            
            console.log(chunkForm);

            //created the chunk, now upload iit
            uploadChunk(chunkForm);
        }

        function uploadChunk(chunkForm){

            jQuery.ajax({
                type: "post",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                dataType: "json",
                data: chunkForm,
                processData: false,
                contentType: false,
                success: function(data){
                    console.log(data);
                    if(data.status === "success"){
                        start = Math.min(start + chunkSize , file.size);
                        if(start == file.size){
                            new Swal('Success!', 'Vehicle condition video uploaded successfully', 'success')
                            .then(() => {
                                location.reload();
                            })
                        }
                        else{
                            updateProgress(chunkCounter++);
                            createChunk();
                            console.log('uploading next part');
                        }
                    }
                    else{                                           
                        new Swal('Oops!', data.message, 'error');
                    }

                },
                error: function(){
                    Swal.fire({
                        title: "Oops!", 
                        html: `
                            <p>Something went wrong. <a class="retry_uploading" href="javascript:void(0)">Click here</a> to retry</p>
                        `,  
                        showConfirmButton: false, 
                        showCancelButton: false, 
                        allowOutsideClick: false, 
                    });
                    // new Swal('Oops!', 'Something went wrong, please try again later', 'error');
                }

            });
        }

        const updateProgress = (chunkCounter) => {
            console.log('chunkcounter is', chunkCounter);
            const percentComplete = Math.round((chunkCounter / numberofChunks) * 100);
            $('.progress-bar').width(`${percentComplete}%`);
            $('.progress-bar').html(`${percentComplete}%`);            
        }

        const showUploader = () => {
            Swal.fire({
                title: "<i>Uploading, Please wait...</i>", 
                html: `
                    <div class="progress-div">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
                        </div>
                    </div>                
                `,  
                showConfirmButton: false, 
                showCancelButton: false, 
                allowOutsideClick: false, 
            });            
        }
    });
})(jQuery);

</script>