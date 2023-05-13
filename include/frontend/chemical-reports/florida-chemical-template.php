<?php 

$index = $_POST['index'] ?? 0;
$chemicals=(new ChemicalReport)->get_all_chemicals();

?>

<div class="product_<?= $index; ?>">

    <h3 class="text-center">Product Details</h3>

    <?php if($index!=0): ?>
        <div class="col-md-12 text-right">
            <span class="float-right">
                <button onclick="remove_product(this,'<?= $index; ?>')" class="btn btn-danger">x</button>
            </span>
        </div>
    <?php endif; ?>


    <!-- Prodouct  -->
    <div class="form-group">
        <label for="">Please select which product you used</label>
        <select name="product[<?= $index; ?>][product]" data-product-index="<?= $index; ?>"  class="form-control product" required>
            <option value="">Select</option>

            <?php if(is_array($chemicals) && count($chemicals)>0): ?>
                <?php foreach($chemicals as $chemical): ?>
                    <option value="<?= $chemical->id; ?>"><?= $chemical->name; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <!-- total size of treatement area  -->
    <div class="form-group">
        <label for="">Total Size of Treatment Area (R) </label>
        <input type="text" class="form-control" name="product[<?= $index; ?>][size_of_treatment]" value="" required>
    </div>


    <!-- Total Amout Applied  -->
    <div class="form-group">
        <label for="">Total Amt. of Pesticide Applied(R) </label>
        <input type="text" name="product[<?= $index; ?>][amount_of_pesticide]" class="form-control" value="" required>
    </div>

    <!-- unit of measurement  -->
    <div class="form-group">
        <label for="">Unit of Measurement</label>
        <select name="product[<?= $index; ?>][unit_of_measure]" class="form-control" required>
            <option value="">Select</option>
            <option value="Oz">Oz</option>
            <option value="FL oz">FL oz</option>
            <option value="Gallons">Gallons</option>
            <option value="Pounds">Pounds</option>
            <option value="Gr-grams">Gr-grams</option>
        </select>
    </div>

    <!-- Method of application  -->
    <div class="form-group">
        <label for="">Please select method of application</label>
        <select name="product[<?= $index; ?>][method_of_application]" class="form-control" required>
            <option value="">Select</option>
            <option value="Sprayer">Sprayer</option>
            <option value="Fog machine">Fog machine</option>
            <option value="Aerosol can">Aerosol can</option>
            <option value="Bait station">Bait station</option>
            <option value="Duster">Duster</option>
        </select>
    </div>

</div>

