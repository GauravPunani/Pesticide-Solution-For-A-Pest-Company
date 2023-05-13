<!-- email sending modal -->
<div id="email_sending_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Sent Signature Email</h4>
      </div>
      <div class="modal-body">
        <form method="post" action="<?= admin_url('admin-post.php'); ?>" id="signature_email_form">
            <input type="hidden" name="action" value="send_signature_email">
            <input type="hidden" name="contract_id" value="">
            <input type="hidden" name="contract_type" value="">
            <div class="form-group">
                <label for="">Client Email</label>
                <input type="email" name="client_email" class="form-control" required>
            </div>
            <button class="btn btn-primary email_btn"><span><i class="fa fa-envlope"></i></span> Send Email</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>