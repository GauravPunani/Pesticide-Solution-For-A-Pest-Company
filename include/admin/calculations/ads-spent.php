<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="text-center">Ad Spend</h3>
            <?php
                $user = wp_get_current_user();
                $roles = ( array ) $user->roles;
                if(in_array('administrator',$roles) || in_array('partner',$roles)){
                    (new Navigation)->calculation_navigation($_GET['page']); 
                }

                (new Navigation)->ads_spent_navigation(@$_GET['ads_tab']);
            ?>
        </div>
    </div>

    <?php

        if(isset($_GET['ads_tab'])){
            switch ($_GET['ads_tab']) {
                case 'daily-data':
                    get_template_part('include/admin/calculations/ad-spent/google-daily-data');
                break;
                case 'ad-spent-calculation':
                    get_template_part('include/admin/calculations/ad-spent/ad-spent-calculation-new');
                break;
                case 'weekly-alert-report':
                    get_template_part('include/admin/calculations/ad-spent/weekly-alert-report');
                break;
                case 'pl-calculator':
                    get_template_part('include/admin/calculations/ad-spent/pl-calculator');
                break;
                case 'unknown-spents':
                    get_template_part('include/admin/calculations/ad-spent/unknown-spents');
                break;
                case 'missing-ad-spent-data':
                    get_template_part('include/admin/calculations/ad-spent/missing-ad-spent-data');
                break;
                default:
                    get_template_part('include/admin/calculations/ad-spent/google-daily-data');
                break;
            }
        }
        else{
            get_template_part('include/admin/calculations/ad-spent/google-daily-data');
        }

    ?>
</div>