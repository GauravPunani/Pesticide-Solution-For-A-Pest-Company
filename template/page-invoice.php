<?php
/* Template Name: Invoice */
get_header();
?>
<section id="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?php (new GamFunctions)->getFlashMessage(); ?>
            </div>
        </div>
    <?php
        if(!empty($_SESSION['invoice_step'])){
            switch ($_SESSION['invoice_step']) {
                case 'select_calendar_event':
                    get_template_part('include/frontend/invoice-flow/index');
                break;
                case 'chemical_report':
                    get_template_part('include/frontend/chemical-reports/chemical-report');
                break;
                case 'maintenance_plan':
                    $maintenance_url = (new InvoiceFlow)->getMaintenancePageUrl();
                    if($maintenance_url){
                        wp_redirect($maintenance_url); exit;
                    }
                    else{
                        get_template_part('include/frontend/maintenance-step');
                    }
                break;
                case 'animal-cage-tracker':
                    get_template_part('include/frontend/invoice-flow/animal-cage-tracker');
                break;
                case 'invoice':
                    get_template_part('include/frontend/invoice');
                break;
                case 'tekcard_payment':
                    get_template_part('include/frontend/credit-card-payment');
                break;
                case 'office_feedback':
                    get_template_part('include/frontend/invoice-flow/office-feedback');
                break;
                case 'prospect_form':
                    get_template_part('include/frontend/invoice-flow/prospect');
                break;
                case 'update_prospect_status':
                    get_template_part('include/frontend/invoice-flow/update-prospect-info');
                break;
                default:
                    get_template_part('include/frontend/invoice-flow/index');
                break;
            }
        }
        else{
            get_template_part('include/frontend/invoice-flow/index');
        }
    ?>
    </div>
</section>
<?php 
get_footer();

