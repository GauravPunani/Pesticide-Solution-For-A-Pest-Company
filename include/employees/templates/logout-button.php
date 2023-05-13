<form action="<?= admin_url('admin-post.php'); ?>" method="post">

    <?php wp_nonce_field('logout_employee'); ?>

    <input type="hidden" name="action" value="logout_employee">
    <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

    <button class="btn btn-default"><span><i class="fa fa-sign-out"></i></span> Logout</button>
</form>