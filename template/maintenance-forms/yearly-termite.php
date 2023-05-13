<?php if((new Technician_details)->is_technician_logged_in()): ?>
    <!-- form filled by staff  -->
    <div class="checkbox">
        <label><input type="checkbox" value="" class="filled-by-staff">Form Fillled By Staff?</label>
    </div>
<?php endif; ?>

<div class="form-group">
    <label for="">Name</label>
    <input type="text" class="form-control" name="name" placeholder="Name">
</div>

<div class="form-group">
    <label for="">Address</label>
    <textarea name="address" cols="30" placeholder="Address" rows="5" class="form-control"></textarea>
</div>

<div class="form-group">
    <label for="">Phone Number</label>
    <input type="text" class="form-control" name="phone_no" placeholder="Phone Number">
</div>

<div class="form-group">
    <label for="">Email</label>
    <input type="email" class="form-control" name="email" placeholder="Email">
</div>

<div class="form-group">
    <label for="">Description of Structure(s) Covered</label>
    <textarea name="description_of_structure" placeholder="Description of Structure(s) Covered" cols="30" rows="5" class="form-control"></textarea>
</div>

<div class="form-group">
    <label for="">BUILDING(S) TREATED</label>
    <select name="buildings_treated" class="form-control">
        <option value="">Select</option>
        <option value="Home">Home</option>
        <option value="Building">Building</option>
        <option value="other">Other</option>
    </select>
</div>

<div class="form-group buildings_treated_other_box hidden">
    <label for="">Please Specifiy Type of Building(s)</label>
    <input type="text" class="form-control" name="buildings_treated_other" placeholder="Type of Building(s)">
</div>

<div class="form-group">
    <label for="">Area(s) treated</label>
    <textarea placeholder="please specify here" name="area_treated" class="form-control" cols="30" rows="5"></textarea>
</div>

<div class="form-group">
    <label for="">Type of termite treated for/guarenteed</label>
    <select name="type_of_termite" class="form-control">
        <option value="">Select</option>
        <option value="Subterrean">Subterrean</option>
        <option value="Dry Wood">Dry Wood</option>
        <option value="Subterrean,Dry Wood">Both (Subterrean,Dry Wood)</option>
    </select>
</div>

<div class="form-group">
    <label for="">Contract Amount</label>
    <input type="text" placeholder="Contract Amount" name="amount" class="form-control numberonly">
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d'); ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">End Date</label>
            <input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d',strtotime('+1 year')); ?>">
        </div>
    </div>
</div>
