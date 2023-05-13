<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="card full_width table-responsive">
                <div class="card-body">
                    <h3 class="page-header">Create Vehicle</h3>
                    <?php (new GamFunctions)->getFlashMessage(); ?>
                    <form id="createVehicleForm" action="<?= admin_url('admin-post.php') ?>" method="post" enctype="multipart/form-data">

                        <?php wp_nonce_field('create_vehicle');?>

                        <input type="hidden" name="action" value="create_vehicle">
                        <input type="hidden" name="vehicle_owner" value="company">
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

                        <?php get_template_part('template-parts/car-center/create-vehicle-fields'); ?>

                        <button class="btn btn-primary"><span><i class="fa fa-plus"></i></span> Create Vehicle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    const parking_address = document.getElementById('parking_address');
    let autocomplete_parking_address;    

    // intialise map from google-autocomplete.js
    initMap('parking_address', (err, autoComplete) => {
        autoComplete.addListener('place_changed', function() {
            let place = autoComplete.getPlace();
            parking_address.value = place.formatted_address;
            autocomplete_parking_address = parking_address.value;
        });
    });

    (function($){
        $('#createVehicleForm').validate({
            rules: {
                year  : "required",
                make  : "required",
                model  : "required",
                plate_number  : "required",
                vin_number  : "required",
                parking_address  : "required",
                last_break_change_mileage  : {
                    required: true,
                    digits: true
                },
                last_oil_change_mileage  : {
                    required: true,
                    digits: true
                },
                current_mileage  : {
                    required: true,
                    digits: true
                },
                insurance_document: "required",
                insurance_expiry_date: "required",
                registration_document: "required",
                registration_expiry_date: "required",
            },
            submitHandler: function(form){
                if(autocomplete_parking_address !== parking_address.value){
                    alert('Please make sure parking address is selected from suggessted address');
                    return false;
                }

                return true;
            }
        })
    })(jQuery);
</script>