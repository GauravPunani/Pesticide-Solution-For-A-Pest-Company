<?php $notices=$args['notices']; ?>

<div class="card full_width">
    <div class="card-body">
        <div class="card-title">
            <h4 class="text-center"><?= $args['title']; ?></h4>
        </div>
        <?php if(is_array($notices) && count($notices)>0): ?>
            <?php foreach($notices as $notice): ?>
                <div data-notice-id="<?= $notice->id; ?>" class="notice notice-<?= $notice->class; ?>">
                    <p><?= $notice->notice; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nothing to show right now</p>
        <?php endif; ?>
    </div>
</div>