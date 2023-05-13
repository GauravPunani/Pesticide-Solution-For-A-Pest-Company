<?php

$docs = $args['data'];

if(empty($docs) || !is_array($docs)){
    echo "No discrepancy found";
    wp_die();
}

?>


<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>File Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($docs as $doc): ?>
        <tr>
            <td><?= isset($doc->file_name) ? $doc->file_name : (isset($doc->name) ? $doc->name : '-'); ?></td>
            <td><a target="_blank" href="<?= isset($doc->file_url) ? $doc->file_url : (isset($doc->url) ? $doc->url : '#'); ?>"><span><i class="fa fa-eye"></i></span> Show</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
