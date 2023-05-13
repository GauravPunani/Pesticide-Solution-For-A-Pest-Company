<?php 

$index = $_POST['index'] ?? 0;
$chemicals=(new ChemicalReport)->get_all_chemicals();
$organisms=(new ChemicalReport)->get_all_organisms();

?>

<div class="row product_<?= $index; ?>">

    <h3 class="text-center">Product Details</h3>
    
    <?php if($index!=0): ?>
        <div class="col-md-12 text-right">
            <span class="float-right">
                <button onclick="remove_product(this,'<?= $index; ?>')" class="btn btn-danger">x</button>
            </span>
        </div>
    <?php endif; ?>    

    <!-- Product  -->
    <div class="col-md-12">
        <div class="form-group">
            <label for="product">Select Product</label>
            <select class="form-control select2-field product" data-product-index="<?= $index; ?>" name="product[<?= $index; ?>]" id="product_<?= $index; ?>" required>
                <option value="">Select</option>

                <?php if(is_array($chemicals) && count($chemicals)>0): ?>
                    <?php foreach($chemicals as $chemical): ?>
                        <option value="<?= $chemical->id; ?>"><?= $chemical->name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    
    <!-- Product Quantity  -->
    <div class="col-md-6">
        <div class="form-group">

                <label for="product">How much of the product was used?</label>
                <select class="form-control product_quantity" name="product_quantity[<?= $index; ?>]" id="product_quantity_<?= $index; ?>" required>
                    <option value="">Select</option>
                    <option value=".25">.25</option>
                    <option value=".5">.5</option>
                    <option value=".75">.75</option>
                    <option value=".8">.8</option>
                    <option value="1">1</option>
                    <option value="1.25">1.25</option>
                    <option value="1.5">1.5</option>
                    <option value="1.75">1.75</option> 
                    <option value="2">2</option>
                    <option value="2.25">2.25</option>
                    <option value="2.5">2.5</option>
                    <option value="2.75">2.75</option>
                    <option value="3">3</option>
                    <option value="other">Other (please type in)</option>

                </select>
        </div>
        <div class="form-group hidden"> 
            <label for="product">Enter Quantity here</label>
                <input type="text" class="form-control" name="product_other_quantity[<?= $index; ?>]" placeholder="e.g. 2.25" required>    
        </div>
    </div>

    <!-- Unit Of Measurement -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="">What unit of measurement was used</label>

            <select name="unit_of_measure[<?= $index; ?>]" id="unit_of_measure_<?= $index; ?>" class="form-control" required>
                <option value="">Select</option>
                <option value="Oz">Oz</option>
                <option value="FL oz">FL oz</option>
                <option value="Gallons">Gallons</option>
                <option value="Pounds">Pounds</option>
                <option value="Gr - grams">Gr - grams</option>
            </select>
        </div>
    </div>

    <!-- Method of application  -->
    <div class="col-md-6">
        <div class="form-group">
                <label for="application method">Please select method of application</label>

                <select class="form-control" name="method_of_application[<?= $index; ?>]" required>
                    <option value="">Select</option>
                    <option value="Sprayer">Sprayer</option>
                    <option value="Fog machine">Fog machine</option>
                    <option value="Aerosol can">Aerosol can</option>
                    <option value="Bait station">Bait station</option>
                    <option value="Duster">Duster</option>
                </select>
        </div>
    </div>

    <!-- Target Organisms -->
    <div class="col-md-6">
        <div class="form-group">
                <label for="application method">Select target organisms </label>

                <select class="form-control" name="target_oranisms[<?= $index; ?>]" id="" required>
                    <option value="">Select</option>
                    <?php if(is_array($organisms) && count($organisms)>0): ?>
                        <?php foreach($organisms as $organism): ?>
                            <option value="<?= $organism->name; ?>"><?= $organism->name; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
        </div>
    </div>

    <!-- Place of application  -->
    <div class="col-md-6">
        <div class="form-group">
                <label for="application method">Please select place of application</label>

                <select class="form-control" name="application_place[<?= $index; ?>]" id="application_place_<?= $index; ?>" required>
                    <option value="">Select</option>
                    <option value="Residential home">Residential home</option>
                    <option value="Commercial Establishment">Commercial Establishment</option>
                    <option value="Construction site">Construction site</option>
                </select>
        </div>
    </div>

</div>