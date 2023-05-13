<?php
//template name: Commercial Quote sheet
get_header();

$technician = (new Technician_details)->get_technician_data();
$technician_id = (new Technician_details)->get_technician_id();
$tech_name = (new Technician_details)->getTechnicianName($technician_id);
$visit_frequency = (new Quote)->visit_frequency();
$visit_duration = (new Quote)->visit_duration();
?>
<section id="content">
    <div class="container"> <?php (new GamFunctions)->getFlashMessage(); ?>
        <form method="post" class="maintenance-forms" id="commercial_quotesheet" name="commercial_quotesheet" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

            <span class="label label-primary"><i class="fa fa-user"></i> <?= $tech_name; ?> </span>

            <h2 class="form-head text-center">COMMERCIAL QUOTE SHEET</h2>

            <div class="event_error text-danger text-left hidden"></div>

            <?php wp_nonce_field('commercial_quotesheet'); ?>

            <input type="hidden" name="action" value="commercial_quotesheet">
            <input type="hidden" name="callrail_id" value="unknown">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

            <div class="form-group">
                <label for=" name">Event Date</label>
                <input type="date" max="<?= date('Y-m-d'); ?>" ; name="event_date" id="event_date" class="form-control">
            </div>

            <div class="form-group">
                <label for=" name">Select Event/Appointment</label>
                <select name="technician_appointment" id="technician_appointment" class="form-control technician_appointment calendar_events">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="form-group">
                <label for="techName">Technician Quote Name <span class="text-danger"><small>(Leave blank if you don't want different name)</small></span></label>
                <input type="text" maxlength="100" class="form-control tech_diff_name"  name="tech_diff_name" id="techDiffName">
            </div>

            <div class="form-group">
                <label for="licenses">CLIENT LICENSES <span class="text-danger"></span>
                </label>
                <select name="licenses" id="licenses" class="form-control">
                    <optgroup label="Ny">
                        <option value="license # c1881257">license # c1881257</option>
                        <option value="NYS DEC Reg 15693">NYS DEC Reg 15693</option>
                    </optgroup>
                    <optgroup label="California">
                        <option value="operator license # 12907">operator license # 12907</option>
                        <option value="business license # 7373">business license # 7373</option>
                    </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label for="establishmentName">CLIENT NAME</label>
                <input type="text" class="form-control client_name" name="client_name" id="client_name">
            </div>

            <div class="form-group">
                <label for="establishmentName">CLIENT ADDRESS</label>
                <input type="text" class="form-control client_address" name="client_address" id="clientAddress">
            </div>

            <div class="form-group">
                <label for="clientAddress">CLIENT EMAIL <span><?= (new GamFunctions)->fakeEmailAlertMessage(); ?></span> </label>
                <input type="email" class="form-control client_email" name="clientEmail" id="clientEmail">
                <p><input type="checkbox" id="no_email_to_offer"> Client does not have email to offer?</p>
            </div>

            <div class="form-group">
                <label for="establishmentName">DECISION MAKER NAME</label>
                <input type="text" class="form-control" name="decision_maker_name" id="decision_maker_namee">
            </div>

            <div class="form-group">
                <label for="clientAddress">CLIENT PHONE NUMBER</label>
                <input type="text" class="form-control client_phone_no" maxlength="12" name="client_phone" id="client_phone">
                <p><input type="checkbox" id="no_phone_to_offer"> Client does not have phone to offer?</p>
            </div>

            <div class="form-group">
                <label for="establishmentName">Frequency of visits ?</label>
                <select name="visits_duration_recurring" class="form-control">
                    <option value="">Select</option>
                    <?php foreach($visit_frequency as $k=>$val) : ?>
                        <option value="<?= $k;?>"><?= $val;?></option>
                    <?php endforeach;?>
                </select>
            </div>

            <div class="form-group single_no_of_visit hidden">
                <label for="no_of_time">Every</label>
                <input type="text" min="1" class="form-control numberonly" placeholder="Visit frequency for e.g : 1,2,3 etc..." name="no_of_times" id="no_of_times"><br>
                <select name="visits_duration_fixed" class="form-control">
                    <option value="">Select</option>
                    <?php foreach($visit_duration as $k=>$val) : ?>
                        <option value="<?= $k;?>"><?= $val;?></option>
                    <?php endforeach;?>
                </select>
            </div>

            <!-- every month, every 2 months, quarterly, weekly or every 2 weeks -->

            <div class="form-group">
                <label for="establishmentName">INITIAL COST</label>
                <input type="text" class="form-control numberonly" name="initial_cost" id="initial_cost">
            </div>

            <div class="form-group">
                <label for="establishmentName">COST PER VISIT</label>
                <input type="text" class="form-control numberonly" name="cost_per_visit" id="cost_per_visit">
            </div>

            <div class="form-group">
                <label for="additional Comment">Please explain in detail your quote and recommended treatment <small>(will be sent to client as well)</small></label>
                <textarea name="notes_for_client" id="" cols="30" rows="5" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="">Anything you want to tell the office about this quote privately please comment here</label>
                <textarea name="office_notes" cols="30" rows="5" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label for="additional-materials">Additional Materials</label>
                <br>
                <div id="materiallist" class="dropdown-check-list" tabindex="100">
                    <span class="selectmaterial">Select Additional Materials</span>
                    <ul class="items">
                        <li>
                            <input name="additional_materials[]" value="Rat Bait stations $29.00" type="checkbox" />Rat Bait stations $29.00
                        </li>
                        <li>
                            <input name="additional_materials[]" value="Mouse bait station $10.00" type="checkbox" />Mouse bait station $10.00
                        </li>
                        <li>
                            <input name="additional_materials[]" value="Rat snap traps $10.00" type="checkbox" />Rat snap traps $10.00
                        </li>
                        <li>
                            <input name="additional_materials[]" value="Mice snap traps $2.00" type="checkbox" />Mice snap traps $2.00
                        </li>
                        <li>
                            <input name="additional_materials[]" value="Nuvan strip $10.00" type="checkbox" />Nuvan strip $10.00
                        </li>
                        <li>
                            <input name="additional_materials[]" value="Glue board $1.00" type="checkbox" />Glue board $1.00
                        </li>
                    </ul>
                </div>
            </div>

            <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Quote </button>

        </form>
    </div>
</section>

<script>
    let service_index = 0;
    let item_id = 0;
    let db_code_id = 0;

    const clientAddress = document.getElementById('clientAddress');
    let autocomplete_client_address;

    let checkList = document.getElementById('materiallist');

    checkList.getElementsByClassName('selectmaterial')[0].onclick = function(evt) {
        if (checkList.classList.contains('visible')) checkList.classList.remove('visible');
        else checkList.classList.add('visible');
    };


    (function($) {

        $(document).ready(function() {

            initMap('clientAddress', (err, autoComplete) => {
                autoComplete.addListener('place_changed', function() {
                    let place = autoComplete.getPlace();
                    clientAddress.value = place.formatted_address;
                    autocomplete_client_address = clientAddress.value;
                });
            });

            $.validator.addMethod("alphanumeric", function(value, element) {
                return this.optional(element) || /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/i.test(value);
            }, "Numbers and dashes only");

            $.validator.addMethod("noSpace", function(value, element) {
                return value.indexOf(" ") < 0 && value != "";
            }, "Space not allowed");

            $('select[name="visits_duration_recurring"]').on('change', function() {
                if ($(this).val() == "custom")
                    $('.single_no_of_visit').removeClass('hidden');
                else
                    $('.single_no_of_visit').addClass('hidden');
            });

            $("#commercial_quotesheet").validate({
                rules: {
                    licenses: "required",
                    client_name: "required",
                    event_date: "required",
                    technician_appointment: "required",
                    client_address: "required",
                    decision_maker_name: "required",
                    visits_duration_recurring: "required",
                    visits_duration_fixed: "required",
                    client_phone: {
                        required: true,
                        minlength: 10,
                        maxlength: 12,
                        alphanumeric: true
                    },
                    no_of_times: {
                        number: true,
                        required: true
                    },
                    initial_cost: {
                        number: true,
                        required: true
                    },
                    cost_per_visit: {
                        number: true,
                        required: true
                    },
                    clientEmail: {
                        required: true,
                        email: true,
                        remote: {
                            url: my_ajax_object.ajax_url,
                            data: {
                                action: "check_for_banned_email",
                                email: function() {
                                    return $('#commercial_quotesheet input[name="clientEmail"]').val()
                                }
                            },
                            type: "post"
                        }
                    },
                    notes_for_client: "required",
                    office_notes: "required",
                },
                messages: {
                    code: {
                        remote: "Code did't matched"
                    },
                    clientEmail: {
                        remote: ERROR_MESSAGES.invalid_email
                    }
                }
            });

        });

    })(jQuery);
</script>

<style>
    .dropdown-check-list {
        display: grid;
    }

    .dropdown-check-list .selectmaterial {
        position: relative;
        cursor: pointer;
        display: inline-block;
        padding: 5px 50px 5px 10px;
        border: 1px solid #ccc;
    }

    .dropdown-check-list .selectmaterial:after {
        position: absolute;
        content: "";
        border-left: 2px solid black;
        border-top: 2px solid black;
        padding: 5px;
        right: 10px;
        top: 20%;
        -moz-transform: rotate(-135deg);
        -ms-transform: rotate(-135deg);
        -o-transform: rotate(-135deg);
        -webkit-transform: rotate(-135deg);
        transform: rotate(-135deg);
    }

    .dropdown-check-list .selectmaterial:active:after {
        right: 8px;
        top: 21%;
    }

    .dropdown-check-list ul.items {
        padding: 2px;
        display: none;
        margin: 0;
        border: 1px solid #ccc;
        border-top: none;
    }

    .dropdown-check-list ul.items li {
        list-style: none;
    }

    .dropdown-check-list.visible .selectmaterial {
        color: #0094ff;
    }

    .dropdown-check-list.visible .items {
        display: block;
    }
</style>

<?php
get_footer();
?>