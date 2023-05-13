<?php
$pests = DB::table('pests')->columns(['id','name', 'description'])->all();
?>

<div class="form-group">
    <label for="">Pests Included <span class="text-info"><small>(Pests which are not selected will be labeled as "Not Included" in contract)</small></span></label>
    <select name="pests_included[]" class="form-control select2-field" multiple required>
        <?php foreach($pests as $pest): ?>
            <option value="<?= $pest->id; ?>"><?= $pest->name; ?> <small><i><?= !empty($pest->description) ? "(".$pest->description.")" : ''; ?></i></small></option>
        <?php endforeach; ?>
    </select>
</div>