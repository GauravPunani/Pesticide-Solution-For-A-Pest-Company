<?php
    $index = $_POST['index'] ?? 0;
    $chemicals=(new ChemicalReport)->get_all_chemicals();
?>

<div class="product_<?= $index; ?>">

    <h3 class="text-center">Product Details</h3>
    
    <?php if($index!=0): ?>
        <div class="col-sm-12 text-right">
            <span class="float-right">
                <button onclick="remove_product(this,'<?= $index; ?>')" class="btn btn-danger">x</button>
            </span>
        </div>
    <?php endif; ?>

    <!-- PRODUCT  -->
    <div class="form-group">
        <label for="">Please select which product you used</label>
        <select name="product[<?= $index; ?>][product]" data-product-index="<?= $index; ?>" class="form-control select2-field product" required>
            <option value="">Select</option>
            <?php if(is_array($chemicals) && count($chemicals)>0): ?>
                <?php foreach($chemicals as $chemical): ?>
                    <option value="<?= $chemical->id; ?>"><?= $chemical->name; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <!-- TOTAL APPLIED  -->
    <div class="form-group">
        <label for="">Total Applied</label>
        <input type="text" class="form-control" name="product[<?= $index; ?>][total_applied]" required>
    </div>
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

    <!-- APPLICATION SITE  -->
    <div class="form-group">
        <label for="">Application Site (Select all that apply)</label>
        
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="exterior of home" required>exterior of home
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="interior of home" required>interior of home
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="bedrooms" required>bedrooms
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="kitchen" required>kitchen
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="garage" required>garage
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="basement" required>basement
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="whole home" required>whole home
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="front of property" required>front of property
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="back of property" required>back of property
        </label>
        <label class="checkbox-inline">
            <input type="checkbox" name="product[<?= $index; ?>][applicator_site][]" value="living room" required>living room
        </label>
    
    </div>
</div>
