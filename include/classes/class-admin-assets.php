<?php

class Admin_assets
{

    function __construct()
    {

        add_action('wp_enqueue_scripts', array($this, 'frontendScripts'));
        add_action('admin_enqueue_scripts', array($this, 'backendScripts'));
        add_action('wp_head', array($this, 'frontendCommonVariable'));
        add_action('init', array($this, 'gamex_email_templates'));
        add_action('admin_footer', array($this, 'backendMobileStyleForModal'));
    }

    /* This function is used to create email template in admin */
    function gamex_email_templates()
    {
        register_post_type(
            'Email Templates',
            // CPT Options
            array(
                'labels' => array(
                    'name' => __('Email Templates'),
                    'singular_name' => __('Email Template')
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'email-templates'),
                'show_in_rest' => false,
            )
        );
    }

    public function frontendScripts()
    {

        // CSS FILES

        wp_enqueue_style('select2-css', "https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css");

        wp_enqueue_style('bootstrap-css', "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.css");

        wp_enqueue_style('bootstrap-datetimepicker-css', "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css");

        // JS FILES

        wp_enqueue_script('common-script', get_template_directory_uri() . '/assets/js/common.js', array('jquery'), '1.0.4', true);

        // FRONTEND BACKEND COMMON SCRIPT
        wp_enqueue_script('frontend-backend-common-script', get_template_directory_uri() . '/assets/js/frontend-backend-common.js', array('jquery'), '1.2.7', true);
        $script_variables = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'     =>  wp_create_nonce('fbcs_nonce')
        ];
        wp_localize_script('frontend-backend-common-script', 'fbcs', $script_variables);

        wp_enqueue_script('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11', '1.0.0', true);
        wp_enqueue_script('bootstrap-moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.js', '1.0.0', true);

        wp_enqueue_script('formvalidation', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js', '1.0.0', true);
        wp_enqueue_script('signaturpad', "https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js", array('jquery'), false, true);

        wp_enqueue_script('bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js', array(), '1.0.0', true);

        wp_enqueue_script('btmoment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js', array(), '1.0.0', true);

        wp_enqueue_script('btdatepickerjs', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js', array(), '1.0.0', true);

        wp_enqueue_script('select2-script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array(), '1.0.0', true);

        wp_enqueue_script('coldcaller', get_template_directory_uri() . '/assets/js/cold-caller.js', array('jquery'), '1.0.1', true);

        wp_localize_script('coldcaller', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

        wp_localize_script('common-script', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

        // FOR QUOTESHEET
        $quotes_pages = ['residential-quote-sheet', 'commercial-quote-sheet'];
        if (is_page($quotes_pages)) {
            wp_enqueue_script('frontend-quote', get_template_directory_uri() . '/assets/js/quote.js', array('jquery'), '1.0.3', true);
        }

        // IF INVOICE/QUOTE/DASHBOARD PAGE
        if (is_page(['invoice']) && isset($_SESSION['invoice_step']) && $_SESSION['invoice_step'] == "invoice") {
            wp_enqueue_script('frontend-invoice-script', get_template_directory_uri() . '/assets/js/invoice.js', array('jquery'), '1.2.6', true);
        }

        // IF INVOICE PAGE
        if (is_page(['invoice'])) {
            // if chemical report
            if (!isset($_SESSION['invoice_step']) || (isset($_SESSION['invoice_step']) && $_SESSION['invoice_step'] == "chemical_report")) {
                wp_enqueue_script('chemical-report-script', get_template_directory_uri() . '/assets/js/chemical-report.js', array('jquery'), '1.0.1', true);
                wp_localize_script('chemical-report-script', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            }
        }

        // IF MAINTENANCE CONTRAC PAGES 
        $maintenance_pages = [
            'monthly-maintenance',
            'quarterly-maintenance',
            'special-maintenance',
            'commercial-maintenance-contract',
            'renew-maintenance'
        ];

        $termite_pages = [
            'yearly-termite-contract',
            'termite-paperwork',
            'termite-certificate',
            'florida-consumer-consent-form'
        ];

        $maintenance_js_pages = array_merge($maintenance_pages, $termite_pages);

        if (is_page($maintenance_js_pages)) {
            wp_enqueue_script('maintenance-script', get_template_directory_uri() . '/assets/js/maintenance.js', array('jquery'), '1.0.3', true);

            wp_localize_script(
                'maintenance-script',
                'my_ajax_object',
                array('ajax_url' => admin_url('admin-ajax.php'))
            );
        }

        // TECHNICIAN DASHBOARD 
        if (is_page(['technician-dashboard'])) {
            wp_enqueue_style('technician-css', get_template_directory_uri() . "/assets/css/technician-dashboard.css");
            wp_enqueue_script('notify-alert', get_template_directory_uri() . '/assets/js/notify.min.js', array('jquery'), '1.0.0', true);
            wp_enqueue_script('technician-dashboard', get_template_directory_uri() . '/assets/js/technician-dashboard.js', array('jquery'), '1.0.2', true);

            wp_enqueue_script('tracking-technician', get_template_directory_uri() . '/assets/js/map.js', array('jquery'), '1.0.2', true);

            wp_localize_script('technician-dashboard', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

            wp_localize_script('tracking-technician', 'my_ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' =>  wp_create_nonce('save_cordinates')
            ));
        }

        wp_enqueue_script('owl-carousel', get_template_directory_uri() . "/assets/js/owl.carousel.min.js", array('jquery'), '1.0.0', true);

        wp_enqueue_script('header-nav-script', get_template_directory_uri() . '/assets/js/script.js', array('jquery'), '1.0.1', true);


        $autocomplete_address_pages = [
            'technician-signup-form',
            'technician-dashboard',
            'cold-caller-dashboard',
            'residential-quote-sheet',
            'commercial-quote-sheet'
        ];

        $autocomplete_address_pages = array_merge($autocomplete_address_pages, $maintenance_pages);

        if (is_page($autocomplete_address_pages)) {

            wp_enqueue_script('google-places-script', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380&libraries=places');

            wp_enqueue_script('google-autocomplete-address', get_template_directory_uri() . '/assets/js/google-autocomplete-address.js');
        }
    }

    public function frontendCommonVariable()
    {
        $messages = json_encode([
            'invalid_email' => 'Provided email is not valid, but if you believe email is valid then please search this email in admin tools > client database -> edit client and mark "email valid" as yes'
        ]);

        echo '
            <script type="text/javascript">
                    var messages;        
                    messages = ' . $messages . '
            </script>
        ';
    }

    /* This function is used add style in responsive mode for admin screen */
public function backendMobileStyleForModal(){
    echo '<style>
    body.mobile.modal-open #wpwrap {
        position: relative;
        height: auto;
    }
    </style>';
}

    public function backendScripts($hook_suffix)
    {

        $upload_dir = wp_upload_dir();

        // CSS FILES 

        wp_register_style('custom_admin_css', get_template_directory_uri() . '/assets/admin/style.css', false, '1.0.1');
        wp_enqueue_style('custom_admin_css');

        wp_enqueue_style('admin_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css');

        wp_enqueue_style('admin_fontawesome', get_template_directory_uri() . "/assets/css/font-awesome.min.css?ver=1.0");

        wp_enqueue_style('datatable-css', "https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css");

        wp_enqueue_style('select2-css', "https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css");

        wp_enqueue_style('bootstrap-datetimepicker-css', "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css");

        // JS FILES

        wp_enqueue_script('admin-common-script', get_template_directory_uri() . '/assets/admin/js/common.js', array('jquery'), '1.0.4', true);

        // FRONTEND BACKEND COMMON SCRIPT
        wp_enqueue_script('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11', '1.0.0', true);

        wp_enqueue_script('frontend-backend-common-script', get_template_directory_uri() . '/assets/js/frontend-backend-common.js', array('jquery'), '1.2.4', true);
        $script_variables = [
            'ajax_url'  => admin_url('admin-ajax.php'),
            'nonce'     =>  wp_create_nonce('fbcs_nonce')
        ];
        wp_localize_script('frontend-backend-common-script', 'fbcs', $script_variables);

        wp_enqueue_script('invoice-script', get_template_directory_uri() . '/assets/admin/js/invoice.js', array('jquery'), '1.0.2', true);

        wp_enqueue_script('cold-caller', get_template_directory_uri() . '/assets/admin/js/cold-caller.js', array('jquery'), '1.0.3', true);

        wp_register_script('client-billing-script', get_template_directory_uri() . '/assets/admin/js/billing.js', array('jquery'), '1.0.3' , true);

        wp_enqueue_script('admin_bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js', array(), false, true);

        wp_localize_script(
            'invoice-script',
            'my_ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php'), 'upload_dir_url' => $upload_dir['baseurl'])
        );

        wp_localize_script(
            'admin-common-script',
            'my_ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php'), 'upload_dir_url' => $upload_dir['baseurl'])
        );

        wp_localize_script(
            'cold-caller',
            'my_ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php'))
        );

        wp_enqueue_script('bootstrap-datatable', 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', array('jquery'), '1.0.0', true);

        // IF ADS SPENT PAGE 
        if ($hook_suffix == "admin_page_ads-spent") {

            wp_enqueue_script('ads-script', get_template_directory_uri() . '/assets/admin/js/ad-spend.js', array('jquery'), '1.0.1', true);

            $file_data = [
                'ajax_url'  =>  admin_url('admin-ajax.php'),
                'nonce'     =>  wp_create_nonce('get_tracking_no_by_location')
            ];

            wp_localize_script('ads-script', 'my_ajax_object', $file_data);
        }

        wp_enqueue_script('admin-select2-script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array(), '1.0.0', true);

        wp_enqueue_script('admin-formvalidation', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js', '1.0.0', true);

        wp_enqueue_script('bootstrap-moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.js', '1.0.0', true);

        wp_enqueue_script('bootstrap-datetimepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js', '1.0.0', true);

        wp_enqueue_script('google-places-script', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBkVmhrScUM6KYaexQDQY8Colf1bnwZ380&libraries=places');

        wp_enqueue_script('google-autocomplete-address', get_template_directory_uri() . '/assets/js/google-autocomplete-address.js');

        if (
            $hook_suffix == "admin_page_maintenance" ||
            $hook_suffix == "admin_page_quarterly-maintenance" ||
            $hook_suffix == "admin_page_commercial-maintenance" ||
            $hook_suffix == "admin_page_special-maintenance"
        ) {
            wp_enqueue_script('maintenance-contract-script', get_template_directory_uri() . '/assets/admin/js/maintenance-contracts.js',array('jquery'), '5.9.5' , true);

            $maintenance_data = [
                'ajax_url'  =>  admin_url('admin-ajax.php'),
                'nonce'     =>  wp_create_nonce('maintenance_script_nonce')
            ];

            wp_localize_script('maintenance-contract-script', 'ajax_var', $maintenance_data);
        }
    }
}

new Admin_assets();
