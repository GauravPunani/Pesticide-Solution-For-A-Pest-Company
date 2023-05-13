var row_id = '';
var client_data = [];

(function ($) {

    $(document).ready(function () {

        $('#collection_add_debt_note_form').validate({
            rules: {
                collection_agency_name: "required",
            }
        });

        $('#collection_upload_payment_proof_form').validate({
            rules: {
                collection_payment_proof: "required",
            }
        });

        $('#select_all_bills').on('click', function () {

            if ($(this).is(':checked')) {
                $('.multi_mini_statement_checkbox').attr('checked', true);
                $('.send_multiple_mini_statements,.move_client_to_in_collection').attr('disabled', false);
            }
            else {
                $('.multi_mini_statement_checkbox').attr('checked', false);
                $('.send_multiple_mini_statements,.move_client_to_in_collection').attr('disabled', true);
            }

        });

        $('.download_mini_statement').on('click', function () {

            let branch_id = $(this).attr('data-branch-id');
            let dates = $(this).attr('data-service-dates');
            let client_name = $(this).attr('data-client-name');
            let client_address = $(this).attr('data-client-address');
            let total_amount = $(this).attr('data-total-amount');
            let invoice_ids = $(this).attr('data-invoice-ids');

            //push the data in form 
            $('#mini_statement_form input[name="branch_id"]').val(branch_id);
            $('#mini_statement_form input[name="name"]').val(client_name);
            $('#mini_statement_form input[name="address"]').val(client_address);
            $('#mini_statement_form input[name="total_amount"]').val(total_amount);

            invoice_ids = JSON.parse(invoice_ids);
            let temp_id = '';
            $.each(invoice_ids, function (key, value) {
                if (key != 0) {
                    temp_id += "," + value;
                }
                else {
                    temp_id += value;
                }
            });

            $('#mini_statement_form input[name="invoice_ids"]').val(temp_id);

            dates = $.parseJSON(dates);

            let dates_html;

            $.each(dates, function (index, value) {
                dates_html += `
                    <div class="form-group">
                        <p class="text-center font-weight-bold">Invoice #${index + 1}</p>
                        <label class="control-label col-sm-4" for="email">Date of service:</label>
                        <input type='hidden' name="invoice[${index}][invoice_no]" value="${value.invoice_no}" />
                        <div class="col-sm-8">
                            <input type="text" name="invoice[${index}][date]" class="form-control" placeholder="Enter Date" value="${value.date}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4" for="email">Amount:</label>
                        <div class="col-sm-8">
                            <input type="text" name="invoice[${index}][amount]" class="form-control" placeholder="Enter amount" value="${value.amount}">
                        </div>
                    </div>
                `;
            });

            $('.invoice-dates').html(dates_html);
        });

        $('.invoice_add_email').on('click', function () {

            let invoice_id = $(this).attr('data-invoice-id');
            row_id = "row_" + invoice_id;
            $('#invoice-email-form input[name="invoice-id"]').val(invoice_id);
            console.log(invoice_id);

        });


        $('.update_collection_payment_status').on('click', function () {
            swal.fire({
                title: "Are you sure",
                text: "You want to mark as paid ?",
                showCancelButton: true,
                confirmButtonText: 'Yes, I am sure!',
                icon: "warning",
            }).then((willDelete) => {
                if (willDelete.isConfirmed) {
                    let invoice_ids = $(this).attr('data-invoice-ids');
                    invoice_ids = JSON.parse(invoice_ids);
                    jQuery.ajax({
                        type: "post",
                        url: my_ajax_object.ajax_url,
                        data: {
                            action: "client_paid_collection_status",
                            data: invoice_ids
                        },
                        beforeSend: function () {
                            showLoader('Processing request, please wait...');
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data.status === "success") {
                                new swal('Great!', data.message, 'success').then(() => {
                                    window.location.reload();
                                })
                            } else {
                                new Swal('Oops!', data.message, 'error');
                            }
                        },
                        error: function () {
                            new Swal('Oops!', 'Something went wrong, please try again later', 'error');
                        }
                    })
                }
            });
        });

        $('#invoice-email-form').on('submit', function (e) {

            e.preventDefault();

            $.ajax({
                type: "post",
                url: my_ajax_object.ajax_url,
                data: $(this).serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status == "success") {
                        $(`#${row_id}`).remove();
                        $('#myModal').modal('hide');
                        alert("Email added successfully");
                    }
                    else {
                        alert("Something went wrong , please try again later");
                    }
                }

            })

        });

        $('.update_debt_note,.upload_collection_pay_proof').on('click', function () {
            let invoice_ids = $(this).attr('data-invoice-ids');
            if ($(this).hasClass('upload_collection_pay_proof')) {
                $('#collection_upload_payment_proof_form input[name="invoice_all_ids"]').val(invoice_ids);
            } else {
                $('#collection_add_debt_note_form input[name="invoice_all_ids"]').val(invoice_ids);
            }
        });

        $('.update_group_email').on('click', function () {

            let invoice_address = $(this).attr('data-invoice-address');
            let row_id = $(this).attr('data-row-id');

            $('#invoice_email_update_form input[name="invoice_address"]').val(invoice_address);
            $('#invoice_email_update_form input[name="row_id"]').val(row_id);


        });

        $('.update_group_address').on('click', function () {

            let invoice_address = $(this).attr('data-invoice-address');
            let row_id = $(this).attr('data-row-id');

            $('#invoice_address_update_form input[name="actual_address"]').val(invoice_address);
            $('#invoice_address_update_form input[name="new_address"]').val(invoice_address);
            $('#invoice_address_update_form input[name="row_id"]').val(row_id);


        });

        $('#invoice_email_update_form').on('submit', function (e) {
            e.preventDefault();
            let row_id = $('#invoice_email_update_form input[name="row_id"]').val();
            let email = $('#invoice_email_update_form input[name="email"]').val();
            $.ajax({
                type: "post",
                url: my_ajax_object.ajax_url,
                data: $(this).serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status == "success") {

                        let input_checkbox_json = $(`#mini_statement_checkbox_${row_id}`).attr('data-grouped-json');
                        input_checkbox_json = $.parseJSON(input_checkbox_json);
                        console.log(input_checkbox_json);

                        input_checkbox_json.client_email = email;
                        console.log(input_checkbox_json);
                        $(`#mini_statement_checkbox_${row_id}`).attr('data-grouped-json', JSON.stringify(input_checkbox_json));


                        $(`.row_email_${row_id}`).html(email);
                        $('#update_email_modal').modal('hide');
                        alert("Email Updated successfully");
                    }
                    else {
                        alert("Something went wrong , please try again later");
                    }
                }

            })
        });

        // address submit form ajax 
        $('#invoice_address_update_form').on('submit', function (e) {
            e.preventDefault();
            let row_id = $('#invoice_address_update_form input[name="row_id"]').val();
            let address = $('#invoice_address_update_form input[name="new_address"]').val();
            $.ajax({
                type: "post",
                url: my_ajax_object.ajax_url,
                data: $(this).serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status == "success") {

                        let input_checkbox_json = $(`#mini_statement_checkbox_${row_id}`).attr('data-grouped-json');
                        input_checkbox_json = $.parseJSON(input_checkbox_json);
                        console.log(input_checkbox_json);

                        input_checkbox_json.client_address = address;
                        console.log(input_checkbox_json);
                        $(`#mini_statement_checkbox_${row_id}`).attr('data-grouped-json', JSON.stringify(input_checkbox_json));


                        $(`.row_address_${row_id}`).html(address);
                        $('#update_address_modal').modal('hide');
                        alert("Address Updated successfully");
                    }
                    else {
                        alert("Something went wrong , please try again later");
                    }
                }

            })
        });

        function generate_mini_statement_and_move_in_collection(msg, action, err_msg) {
            client_data = [];
            swal.fire({
                title: "Are you sure",
                text: `You want to ${msg}`,
                showCancelButton: true,
                confirmButtonText: 'Yes, I am sure!',
                icon: "warning",
            })
                .then((willDelete) => {
                    if (willDelete.isConfirmed) {
                        let atleast_one_checked = false;
                        $('.multi_mini_statement_checkbox').each(function (key, value) {
                            if (this.checked == true) {
                                atleast_one_checked = true;
                                let data = $(this).attr('data-grouped-json');
                                data = $.parseJSON(data);
                                client_data.push(data);
                            }
                        });

                        if (atleast_one_checked) {
                            block_all_fields();
                            console.log(action);
                            if (action == 'mini_statment') {
                                send_mini_statement_to_clients();
                            } else {
                                move_client_to_collection_folder();
                            }
                        }
                        else {
                            alert(`Please Select atleast one client to ${err_msg}`);
                        }
                    }
                });
        }
        $('.send_multiple_mini_statements').on('click', function () {
            generate_mini_statement_and_move_in_collection(
                'send Mini statements to selected clients?',
                'mini_statment',
                'send mini statement'
            );
        });

        $('.move_client_to_in_collection').on('click', function () {
            generate_mini_statement_and_move_in_collection(
                'move selected clients in collections tab?',
                'collection',
                'move in collection folder'
            );
        });

        $('.multi_mini_statement_checkbox').on('change', function () {
            let client_checked = false;

            $('.multi_mini_statement_checkbox').each(function (key, value) {
                if (this.checked == true) {
                    client_checked = true;
                    return false;
                }
            });

            if (client_checked) {
                $('.send_multiple_mini_statements,.move_client_to_in_collection').prop('disabled', false);
            }
            else {
                $('.send_multiple_mini_statements,.move_client_to_in_collection').prop('disabled', true);
            }
        });
    });
})(jQuery);

function send_mini_statement_to_clients(index = 0) {
    console.log(client_data[index]);

    jQuery('.fixed-alert').removeClass('hidden');

    jQuery.ajax({
        type: "post",
        url: my_ajax_object.ajax_url,
        data: {
            action: "send_mini_statements_to_clients",
            data: client_data[index]
        },
        dataType: "json",
        success: function (res) {
            console.log(res);
            jQuery('.fixed-alert .alert-body').html(`<p> ${index} out of ${client_data.length} is sent </p>`);
            if (index == client_data.length - 1) {
                unblock_fields();
                return true;
            }
            send_mini_statement_to_clients(index + 1);
        }
    })

}

function move_client_to_collection_folder(index = 0) {
    console.log(client_data[index]);

    let process_btn = jQuery('.move_client_to_in_collection');
    jQuery.ajax({
        type: "post",
        url: my_ajax_object.ajax_url,
        data: {
            action: "move_client_in_collection",
            data: client_data[index]
        },
        beforeSend: function () {
            process_btn.attr('disabled', 'disabled').text('Processing please wait...');
        },
        dataType: "json",
        success: function (res) {
            jQuery('.fixed-alert .alert-body').html(`<p> ${index} out of ${client_data.length} is sent </p>`);
            if (index == client_data.length - 1) {
                new swal('Great!', 'Client Move To Collection Folder Successfully', 'success').then(() => {
                    location.reload();
                });
                return true;
            }
            move_client_to_collection_folder(index + 1);
        }
    })

}

function block_all_fields() {

    jQuery('.multi_mini_statement_checkbox').each(function (key, val) {

        // uncheck and disable untill previous request completes
        jQuery(this).prop('checked', false);
        jQuery(this).prop('disabled', true);

    });

    jQuery('.send_multiple_mini_statements').prop('disabled', true);

    jQuery('.fixed-alert .alert-title').html('Processing...');

    jQuery('.fixed-alert .alert-body').html('');


}

function unblock_fields(arg = '') {
    let msg;
    jQuery('.fixed-alert .alert-title').html('<h3>Done</h3>');

    if (arg != '') {
        msg = 'Client Move To Collection Folder Successfully';
    } else {
        msg = 'Mini Statements Sent Successfully';
    }
    jQuery('.fixed-alert .alert-body').html(`<p>${msg}</p><p><button class="btn btn-primary" onclick="reset_alert()">Okay</button</p>`);

    jQuery('.multi_mini_statement_checkbox').each(function (key, val) {
        jQuery(this).prop('disabled', false);

    });
}

function reset_alert() {
    jQuery('.fixed-alert').addClass('hidden');
    jQuery('.alert-body').html('');
    location.reload();
}