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
    <div class="col-md-6">
        <div class="form-group">
            <label for="product reg">Select Product</label>
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
    
    <!-- Total Product Used  -->
    <div class="col-md-6">
        <div class="form-group">
                <label for="total product">Total Product Used</label>
                <select class="form-control product_quantity select2-field"  name="product_quantity[<?= $index; ?>]" id="product_quantity_<?= $index; ?>" required>

                        <option value="">Select</option>        
                        <option value=".25">.25</option>        
                        <option value=".50">.50</option>        
                        <option value=".75">.75</option>        
                        <option value="1.0">1.0</option>        
                        <option value="1.25">1.25</option>        
                        <option value="1.50">1.50</option>      
                        <option value="other">Other (please type in)</option>        
                </select>
        </div>
        <div class="form-group hidden">
            <label for="total product">Enter Quantity used</label>
            <input type="text" name="product_other_quantity[<?= $index; ?>]" class="form-control" required>
        </div>
    </div>

    <!-- Unit Of Measurement -->
    <div class="col-md-6">
        <div class="form-group">
            <label for="unit of measurement">What unit of measurement was used</label>
            <select class="form-control select2-field" name="unit_of_measure[<?= $index; ?>]" id="unit_of_measure_<?= $index; ?>" required>
                <option value="">Select</option>        
                <option value="Oz - ounce">Oz - ounce</option>        
                <option value="Gr - grams">Gr - grams</option>        
                <option value="Ga - gallon">Ga - gallon</option>        
                <option value="Lb - pounds">Lb - pounds</option>
                <option value="Fl - oz">Fl - oz</option>                
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="target organisms">Select target organisms</label>
            <select class="form-control select2-field" name="target_oranisms[<?= $index; ?>]" id="target_oranisms_<?= $index; ?>" required>
                <option value="">Select</option>  
                <?php if(is_array($organisms) && count($organisms)>0): ?>
                    <?php foreach($organisms as $organism): ?>
                        <option value="<?= $organism->name; ?>"><?= $organism->name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="place of application">Please select place of application</label>
            <select class="form-control select2-field" name="application_place[<?= $index; ?>]" id="application_place_<?= $index; ?>" required>
                <option value="">Select</option>  
                <option value="Residential home">Residential home</option>  
                <option value="Commercial Establishment">Commercial Establishment</option>  
                <option value="Construction site">Construction site</option>  
            </select>
        </div>
    </div>

</div>