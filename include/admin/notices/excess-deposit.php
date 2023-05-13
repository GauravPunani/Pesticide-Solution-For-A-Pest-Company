<div class="card">
    <div class="card-body">
        <div class="card-title">
            <h4 class="text-center">Excessive Deposit</h4>
        </div>
        <?php $notices=(new Notices)->get_all_notices('excessive_deposit'); ?>
        <?php if(is_array($notices) && count($notices)>0): ?>
            <?php foreach($notices as $notice): ?>
                <div data-notice-id="<?= $notice->id; ?>" class="notice notice-<?= $notice->class; ?> is-dismissible">
                    <p><?= $notice->notice; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nothing to show right now</p>
        <?php endif; ?>
    </div>
</div>