<?php

$cage_data = isset($args['cage_data']) ?: [];
$form_type = isset($args['form']) ?: "";

$cage_types = (new AnimalCageTracker)->getCagesTypes();
?>

<?php if(is_array($cage_types) && count($cage_types) > 0): ?>
    <?php foreach($cage_types as $cage_type): ?>
        <div class="cageType">
            <p><?= $cage_type->name; ?> quantity <?= !empty($form_type) && $form_type == "invoice_flow" ? 'added on current visit' : ''; ?></p>
            <div class="form-group">
                <input type="text" value="<?= (count((array)$cage_data) > 0 && $cage_data->cage_type_id == $cage_type->id) ? $cage_data->quantity : '0'; ?>" class="form-control cage_input_field numberonly" name="cages_data[<?= $cage_type->slug; ?>]">
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>