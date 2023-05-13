<?php $notices=(new Notices)->get_all_notices('weekly_notice_error'); ?>

<?php if(is_array($notices) && count($notices)>0): ?>

<div class="card full_width table-responsive">
    <div class="card-body">
        <div class="card-title">
            <h4 class="text-center">Weekly Notice Error</h4>
        </div>
            <?php foreach($notices as $notice): ?>
                <div data-notice-id="<?= $notice->id; ?>" class="notice notice-<?= $notice->class; ?> is-dismissible">
                    <p><?= $notice->notice; ?></p>
                </div>
            <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>
