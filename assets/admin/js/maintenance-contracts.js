function deleteMaintenancePlan(ref) {

    const contract_id = jQuery(ref).attr('data-contract-id');
    const contract_type = jQuery(ref).attr('data-contract-type');

    swal.fire({
        title: "Are you sure",
        text: "You want to delete this contract ?",
        showCancelButton: true,
        confirmButtonText: 'Yes, I am sure!',
        icon: "warning",
    })
        .then((willDelete) => {
            if (willDelete.isConfirmed) {
                // make request on server and delete the contract from corrosponding table 
                jQuery.ajax({
                    type: "post",
                    url: ajax_var.ajax_url,
                    data: {
                        action: "delete_maintenance_record",
                        contract_id,
                        contract_type,
                        "_wpnonce": ajax_var.nonce
                    },
                    dataType: "json",
                    beforeSend: function () {
                        jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', true);
                        showLoader('Deleting contract from system, please wait...');
                    },
                    success: function (data) {
                        console.log('data deleted');

                        if (data.status == "success") {
                            swal.close();
                            jQuery(ref).closest('.dropdown').parent().parent().fadeOut();
                        }
                        else {
                            alert(data.message);
                            jQuery(ref).closest('.dropdown').find('.dropdown-toggle').attr('disabled', false);
                        }

                    }
                });
            }
        });
}

function smsContractLink(contract_id, contract_type, phone_no) {

    jQuery('#smsContractForm input[name="contract_id"]').val(contract_id);
    jQuery('#smsContractForm input[name="contract_type"]').val(contract_type);
    jQuery('#smsContractForm input[name="phone_no"]').val(phone_no);

    jQuery('#smsContractModal').modal('show');
}

function downloadMaintenanceContract(ref){
    let contract_id = jQuery(ref).attr('data-contract-id');
    jQuery('#downloadMaintenanceContractForm input[name="contract_id"]').val(contract_id);
    jQuery('#downloadMaintenanceContractForm').submit();
}