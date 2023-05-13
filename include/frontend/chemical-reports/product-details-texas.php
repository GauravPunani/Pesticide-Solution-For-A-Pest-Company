<?php 
global $wpdb;

$index = $_POST['index'] ?? 0;
$products = $wpdb->get_results("select id,name from {$wpdb->prefix}chemicals");

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
        <label for="">Select product</label>
        <select name="product[<?= $index; ?>][product]" class="form-control" required>
            <option value="">Select</option>
            <?php if(is_array($products) && count($products)>0): ?>
                <?php foreach($products as $product): ?>
                    <option value="<?= $product->id; ?>"><?= $product->name; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
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

    <!-- WIND DIRECTION  -->

    <div class="form-group">
        <label for="">Wind Direction</label>
        <select name="product[<?= $index; ?>][wind_direction]" class="form-control" required>
            <option value="">Select</option>
            <option value="North">North</option>
            <option value="South">South</option>
            <option value="East">East</option>
            <option value="West">West</option>
            <option value="No Wind">No Wind</option>
        </select>
    </div>

    <!-- WIND VELOCITY  -->
    <div class="form-group">
        <label for="">Wind Velocity</label>
        <input type="text" class="form-control" name="product[<?= $index; ?>][wind_velocity]" required>
    </div>

    <!-- AIR TEMPRATURE  -->
    <div class="form-group">
        <label for="">Air Temprature</label>
        <input type="text" class="form-control" name="product[<?= $index; ?>][air_temprature]" required>
    </div>

    <!-- TARGET PEST  -->
    <div class="form-group">
        <label for="">Target Pest</label>
        <select name="product[<?= $index; ?>][target_pest]" class="form-control" required>
            <option value="">Select</option>
            <option value="Roches">Roches</option>
            <option value="Mice">Mice</option>
            <option value="Rats">Rats</option>
            <option value="Flies">Flies</option>
            <option value="Roaches">Roaches</option>
            <option value="Ants">Ants</option>
            <option value="Bees">Bees</option>
            <option value="Bed Bugs">Bed Bugs</option>
            <option value="Earwigs">Earwigs</option>
            <option value="Centipedes">Centipedes</option>
            <option value="Millipedes">Millipedes</option>
        </select>
    </div>

    <!-- METHOD OF APPLICATION  -->
    <div class="form-group">
        <label for="">Method or Type of Equipment Used To Make Application</label>
        <select class="form-control" name="product[<?= $index; ?>][method_of_application]" required>
            <option value="">Select</option>
            <option value="Sprayer">Sprayer</option>
            <option value="Fog machine">Fog machine</option>
            <option value="Aerosol can">Aerosol can</option>
            <option value="Bait station">Bait station</option>
            <option value="Duster">Duster</option>
        </select>
    </div>
    
</div>