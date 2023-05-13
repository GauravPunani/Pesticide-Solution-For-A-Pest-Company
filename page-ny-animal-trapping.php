<?php 
/* Template Name: ny-animal-trapping-report*/

get_header();
?>

<section id="content">
    <div class="container res-form">
        <h3 class="page-header text-center">Newyork Animal Trapping Report</h3>
        <form action="<?= admin_url('admin-post.php') ?>" method="post">
            <input type="hidden" name="action" value="ny_animal_trapping_report">
            <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI']; ?>">

            <p>Complaing Type :- Inside the home</p>
            <p>Date :- <?= date('d M Y'); ?></p>

            <div class="form-group">
                <label for="">Name and address of complainant</label>
                <input type="text" name="name_address"  class="form-control">
            </div>
            <div class="form-group">
                <label for="">Date Performed</label>
                <input type="date" name="date_performed"  class="form-control">
            </div>

            <!-- Nuissance Species Options         -->
            <div class="form-group">
                <label for="">Nuissance Species Options</label>
                <select name="nuissance_species" class="form-control">
                    <option value="">Select</option>
                    <option value="racccon">racccon</option>
                    <option value="squirell">squirell</option>
                    <option value="ground hog">ground hog</option>
                    <option value="opposum">opposum</option>
                </select>
            </div>

            <!-- abatement method -->
            <div class="form-group">
                <label for="">Abatement Method</label>
                <select class="form-control" name="abatement_method">
                    <option value="">Select</option>
                    <option value="Hot Sealing">Hot Sealing</option>
                    <option value="Trapping">Trapping</option>
                </select>
            </div>

            <!-- Area of comlaint -->
            <div class="form-group">
                <label for="">Area of Complaint</label>
                <select class="form-control" name="area_of_complaint">
                    <option value="">Select</option>
                    <option value="Basement">Basement</option>
                    <option value="Attic">Attic</option>
                    <option value="Inside the home">Inside the home</option>
                </select>
            </div>

            <!-- No. of Traps -->
            <div class="form-group">
                <label for="">No. of Traps</label>
                <input type="text" name="no_of_traps" class="form-control">
            </div>

            <!-- Species & number taken -->
            <div class="form-group">
                <label for="">Species & number taken</label>
                <input type="text" name="speicies_no_taken" class="form-control">
            </div>


            <!-- Disposition of animal -->
            <div class="form-group">
                <label for="">Disposition of animal</label>
                <select class="form-control" name="desposition_of_animal">
                    <option value="">Select</option>
                    <option value="Calm">Calm</option>
                    <option value="Aggressive">Aggressive</option>
                    <option value="Not Yet Determined">Not Yet Determined</option>
                </select>
            </div>

            <button class="btn btn-primary"><span><i class="fa fa-paper-plane"></i></span> Submit Report</button>
        </form>
    </div>
</section>

<?php
get_footer();

