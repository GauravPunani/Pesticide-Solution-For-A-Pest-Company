<?php

class Admin_page{

    function __construct(){

        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;

        if(in_array('administrator',$roles) || in_array('partner',$roles)){
            add_action('admin_menu',array($this,'admin_menu_pages'));
        }

        if(in_array('ads_manager',$roles)){
            add_action('admin_menu',array($this,'ads_manager_pages'));
        }

        if(in_array('cold_caller',$roles)){
            add_action('admin_menu',array($this,'cold_caller_pages'));
        }

    }

    public function cold_caller_pages(){
        // Admin Tools Page 
        add_menu_page(
            'All Tools',     // page title
            'All Tools',     // menu title
            'create_posts',   // capability
            'all-tools',     // menu slug
            array($this,'cold_caller_tools'), // callback function
            'dashicons-admin-site',
            1
        );

        // cold caller page
        add_submenu_page(
            null, //parent slug
            'Cold Caller',     // page title
            'Cold Caller',     // menu title
            'create_posts',   // capability
            'cold-caller',     // menu slug
            array($this,'cold_caller') // callback function
        );

        // Residential Quote Upstate
        add_submenu_page(
            null, //parent slug
            'Residential Quotes',     // page title
            'Residential Quotes',     // menu title
            'create_posts',   // capability
            'resdiential-quotesheet',     // menu slug
            array($this,'render_residential_quotes') // callback function
        );

        // Commercial Quotes Upstate
        add_submenu_page(
            null, //parent slug
            'Commercial Quotes',     // page title
            'Commercial Quotes',     // menu title
            'create_posts',   // capability
            'commercial-quotesheet',     // menu slug
            array($this,'render_commercial_quotes') // callback function
        );

        // Non Interested Maintenance Quotes
        add_submenu_page(
            null, //parent slug
            'Non Interested Maintenance Quotes',     // page title
            'Non Interested Maintenance Quotes',     // menu title
            'create_posts',   // capability
            'non-interested-maintenance-quotes',     // menu slug
            array($this,'non_interested_maintenance_quotes') // callback function
        );
    }

    public function ads_manager_pages(){

        // Admin Tools Page 
        add_menu_page(
            'Admin Tools',     // page title
            'Admin Tools',     // menu title
            'manage_options',   // capability
            'all-tools',     // menu slug
            array($this,'render_ads_manager_tools'), // callback function
            'dashicons-admin-site',
            1
        );        

        add_submenu_page(
            null,     // page title
            'Ads Spent',     // Page title
            'Ads Spent',     // menu title
            'manage_options',   // capability
            'ads-spent',     // menu slug
            array($this,'render_ads_spent_page') // callback function
        );


        add_submenu_page(
            null, //parent slug
            'Alerts History',     // page title
            'Alerts History',     // menu title
            'manage_options',   // capability
            'alerts-history',     // menu slug
            array($this,'render_alerts_history') // callback function
        );

    }
    
    public function admin_menu_pages(){

        // Admin Tools Page 
        add_menu_page(
            'Admin Tools',     // page title
            'Admin Tools',     // menu title
            'manage_options',   // capability
            'all-tools',     // menu slug
            array($this,'render_all_tools'), // callback function
            'dashicons-admin-site',
            1
        );

        // Developer Area Page 
        add_menu_page(
            'Developer Area',     // page title
            'Developer Area',     // menu title
            'manage_options',   // capability
            'developer-area',     // menu slug
            array($this,'render_developer_dashboard'), // callback function
            'dashicons-admin-site',
            1
        );


        // Monthly Maintenance 
        add_submenu_page(
            null,
            'Monthly Maintenance',     // page title
            'Monthly Maintenance',     // menu title
            'manage_options',   // capability
            'maintenance',     // menu slug
            array($this,'render_monthly_maintenance') // callback function
        );

        // Quarterly Maintenance 
        add_submenu_page(
            null,
            'Quarterly Maintenance',     // page title
            'Quarterly Maintenance',     // menu title
            'manage_options',   // capability
            'quarterly-maintenance',     // menu slug
            array($this,'render_quarterly_maintenance') // callback function
        );

        // Commercial Maintenance 
        add_submenu_page(
            null,
            'Commercial Maintenance',     // page title
            'Commercial Maintenance',     // menu title
            'manage_options',   // capability
            'commercial-maintenance',     // menu slug
            array($this,'render_commercial_maintenance') // callback function
        );

        // Special Maintenance 
        add_submenu_page(
            null,
            'Special Maintenance',     // page title
            'Special Maintenance',     // menu title
            'manage_options',   // capability
            'special-maintenance',     // menu slug
            array($this,'render_special_maintenance') // callback function
        );

        // Special Maintenance 
        add_submenu_page(
            null,
            'Yearly Termite Contract',     // page title
            'Yearly Termite Contract',     // menu title
            'manage_options',   // capability
            'yearly-termite-contract',     // menu slug
            array($this,'render_yearly_termite_contract') // callback function
        );
        
        // Invoice Upstate 
        add_submenu_page(
            null, //parent slug
            'Invoices',     // page title
            'Invoices',     // menu title
            'manage_options',   // capability
            'invoice',     // menu slug
            array($this,'render_invoices') // callback function
        );

        add_submenu_page(
            null,     // page title
            'Invoice Calculations',     // Page title
            'Invoice Calculations',     // menu title
            'manage_options',   // capability
            'invoice-calculations',     // menu slug
            array($this,'render_invoice_calculations_page') // callback function
        );
        add_submenu_page(
            null,     // page title
            'Calendar Events',     // Page title
            'Calendar Events',     // menu title
            'manage_options',   // capability
            'calendar-events',     // menu slug
            array($this,'render_calendar_events_page') // callback function
        );

        add_submenu_page(
            null,     // page title
            'Sales Tax',     // Page title
            'Sales Tax',     // menu title
            'manage_options',   // capability
            'sales-tax',     // menu slug
            array($this,'render_sales_tax_page') // callback function
        );
        add_submenu_page(
            null,     // page title
            'Ads Spent',     // Page title
            'Ads Spent',     // menu title
            'manage_options',   // capability
            'ads-spent',     // menu slug
            array($this,'render_ads_spent_page') // callback function
        );

        // Residential Quote Upstate
        add_submenu_page(
            null, //parent slug
            'Residential Quotes',     // page title
            'Residential Quotes',     // menu title
            'manage_options',   // capability
            'resdiential-quotesheet',     // menu slug
            array($this,'render_residential_quotes') // callback function
        );

        // Commercial Quotes Upstate
        add_submenu_page(
            null, //parent slug
            'Commercial Quotes',     // page title
            'Commercial Quotes',     // menu title
            'manage_options',   // capability
            'commercial-quotesheet',     // menu slug
            array($this,'render_commercial_quotes') // callback function
        );
    
        // Billing page Upstate 
        add_submenu_page(
            null, //parent slug
            'Upstate',     // page title
            'Upstate',     // menu title
            'manage_options',   // capability
            'billing',     // menu slug
            array($this,'render_billing_page') // callback function
        );

        // Client Payments Page 
        add_submenu_page(
            null,     // page title
            'Clients Payments',     // page title
            'Clients Payments',     // menu title
            'manage_options',   // capability
            'clients-payments',     // menu slug
            array($this,'render_clients_payments')
        );

        // daily proof of deposit page 

        add_submenu_page(
            null, //parent slug
            'Daily Deposit',     // page title
            'Daily Deposit',     // menu title
            'manage_options',   // capability
            'daily-deposit',     // menu slug
            array($this,'render_daily_deposit') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Office Notes',     // page title
            'Office Notes',     // menu title
            'manage_options',   // capability
            'office-notes-old',     // menu slug
            array($this,'render_office_notes_old') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Office Notes',     // page title
            'Office Notes',     // menu title
            'manage_options',   // capability
            'office-notes',     // menu slug
            array($this,'render_office_notes') // callback function
        );
        
        // Chemical Report New York
        add_submenu_page(
            null, //parent slug
            'New York Chemical Report',     // page title
            'New York Chemical Report',     // menu title
            'manage_options',   // capability
            'chemical-reports-newyork',     // menu slug
            array($this,'render_chemical_reports_newyork') // callback function
        );

        // New York Animal Trapping Report
        add_submenu_page(
            null, //parent slug
            'New York Animal Trapping',     // page title
            'New York Animal Trapping',     // menu title
            'manage_options',   // capability
            'newyork-animal-trapping-report',     // menu slug
            array($this,'render_newyork_animal_trapping_report') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Non Interested Maintenance Quotes',     // page title
            'Non Interested Maintenance Quotes',     // menu title
            'manage_options',   // capability
            'non-interested-maintenance-quotes',     // menu slug
            array($this,'non_interested_maintenance_quotes') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Alerts History',     // page title
            'Alerts History',     // menu title
            'manage_options',   // capability
            'alerts-history',     // menu slug
            array($this,'render_alerts_history') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Reimbursement',     // page title
            'Reimbursement',     // menu title
            'manage_options',   // capability
            'reimbursement',     // menu slug
            array($this,'render_reimbursement') // callback function
        );
        
        add_submenu_page(
            null, //parent slug
            'Weekly Ads Alert',     // page title
            'Weekly Ads Alert',     // menu title
            'manage_options',   // capability
            'weekly-ads-alert',     // menu slug
            array($this,'render_weekly_ads_alert') // callback function
        );
        
        add_submenu_page(
            null, //parent slug
            'Callrail Trackers',     // page title
            'Callrail Trackers',     // menu title
            'manage_options',   // capability
            'callrail-trackers',     // menu slug
            array($this,'render_callrail_trackers') // callback function
        );
        
        add_submenu_page(
            null, //parent slug
            'Gam Employees',     // page title
            'Gam Employees',     // menu title
            'manage_options',   // capability
            'gam-employees',     // menu slug
            array($this,'render_gam_employees') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Gam Technicians',     // page title
            'Gam Technicians',     // menu title
            'manage_options',   // capability
            'gam-technicians',     // menu slug
            array($this,'render_gam_technicians') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Office Staff',     // page title
            'Office Staff',     // menu title
            'manage_options',   // capability
            'office-staff',     // menu slug
            array($this,'render_office_staff') // callback function
        );

        // TECHNICIAN NOTICES
        add_submenu_page(
            null, //parent slug
            'Technician Notices',     // page title
            'Technician Notices',     // menu title
            'manage_options',   // capability
            'technician-notices',     // menu slug
            array($this,'render_technician_notices') // callback function
        );

        // VEHICLES
        add_submenu_page(
            null, //parent slug
            'Vehicles',     // page title
            'Vehicles',     // menu title
            'manage_options',   // capability
            'gam-vehicles',     // menu slug
            array($this,'render_vehicles') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Task Manager',     // page title
            'Task Manager',     // menu title
            'manage_options',   // capability
            'task-manager',     // menu slug
            array($this,'render_task_manager') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'System Codes',     // page title
            'System Codes',     // menu title
            'manage_options',   // capability
            'system-codes',     // menu slug
            array($this,'render_system_codes') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Animal Cage Tracker',     // page title
            'Animal Cage Tracker',     // menu title
            'manage_options',   // capability
            'animal-cage-tracker',     // menu slug
            array($this,'animal_cage_tracker') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Animal Cage Tracker',     // page title
            'Animal Cage Tracker',     // menu title
            'manage_options',   // capability
            'animal-cage-tracker-new',     // menu slug
            array($this,'animal_cage_tracker_new') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Track Technician',     // page title
            'Track Technician',     // menu title
            'manage_options',   // capability
            'track-technician',     // menu slug
            array($this,'track_technician') // callback function
        );

        add_submenu_page(
            null, //parent slug
            'Email Database',     // page title
            'Email Database',     // menu title
            'manage_options',   // capability
            'email-database',     // menu slug
            array($this,'email_database') // callback function
        );
        
        add_submenu_page(
            null, //parent slug
            'Cold Caller',     // page title
            'Cold Caller',     // menu title
            'manage_options',   // capability
            'cold-caller',     // menu slug
            array($this,'cold_caller') // callback function
        );

        // TERMITE PAPERWORK 
        add_submenu_page(
            null, //parent slug
            'Termite Paperwork',     // page title
            'Termite Paperwork',     // menu title
            'manage_options',   // capability
            'termite-paperwork',     // menu slug
            array($this,'render_termite_paperwork') // callback function
        );
   
        // if current user is administrative , then only he can access these pages
        if(current_user_can('other_than_upstate')){

            // Chemical Report Texas
            add_submenu_page(
                null, //parent slug
                'Texas Chemical Report',     // page title
                'Texas Chemical Report',     // menu title
                'manage_options',   // capability
                'chemical-report-texas',     // menu slug
                array($this,'render_chemical_reports_texas') // callback function
            );
            

            // Chemical Reports California
            add_submenu_page(
                null, //parent slug
                'California',     // page title
                'California',     // menu title
                'manage_options',   // capability
                'chemical-reports-california',     // menu slug
                array($this,'render_chemical_reports_california') // callback function
            );

            // Chemical Reports Florida
            add_submenu_page(
                null, //parent slug
                'Florida',     // page title
                'Florida',     // menu title
                'manage_options',   // capability
                'chemical-reports-florida',     // menu slug
                array($this,'render_chemical_reports_florida') // callback function
            );

            // Chemical Reports New Jersey
            add_submenu_page(
                null, //parent slug
                'New Jersey',     // page title
                'New Jersey',     // menu title
                'manage_options',   // capability
                'chemical-reports-newjersey',     // menu slug
                array($this,'render_chemical_reports_newjersey') // callback function
            );

            // billing page new york 
            add_submenu_page(
                null, //parent slug
                'New York',     // page title
                'New York',     // menu title
                'manage_options',   // capability
                'billing-newyork',     // menu slug
                array($this,'render_billing_page') // callback function
            );

            // billing page los angeles
            add_submenu_page(
                null, //parent slug
                'Los Angeles',     // page title
                'Los Angeles',     // menu title
                'manage_options',   // capability
                'billing-los-angeles',     // menu slug
                array($this,'render_billing_page') // callback function
            );

            // billing page san-fancisco 
            add_submenu_page(
                null, //parent slug
                'San Francisco',     // page title
                'San Francisco',     // menu title
                'manage_options',   // capability
                'billing-san-francisco',     // menu slug
                array($this,'render_billing_page') // callback function
            );

            // billing page houston 
            add_submenu_page(
                null, //parent slug
                'Houston',     // page title
                'Houston',     // menu title
                'manage_options',   // capability
                'billing-houston',     // menu slug
                array($this,'render_billing_page') // callback function
            );

            add_submenu_page(
                null, //parent slug
                'Miami',     // page title
                'Miami',     // menu title
                'manage_options',   // capability
                'billing-miami',     // menu slug
                array($this,'render_billing_page') // callback function
            );

            add_submenu_page(
                null, //parent slug
                'Branches',     // page title
                'Branches',     // menu title
                'manage_options',   // capability
                'branches',     // menu slug
                array($this,'render_branches') // callback function
            );

            // TEKCARD PAYMENTS 
            add_submenu_page(
                null, //parent slug
                'Tekcard Payments',     // page title
                'Tekcard Payments',     // menu title
                'manage_options',   // capability
                'tekcard-payments',     // menu slug
                array($this,'render_tekcard_payments') // callback function
            );

            // GAM VIDEOS PAGE 
            add_submenu_page(
                null, //parent slug
                'Gam Videos',     // page title
                'Gam Videos',     // menu title
                'manage_options',   // capability
                'gam-videos',     // menu slug
                array($this,'render_gam_videos') // callback function
            );

            // EMPLOYEES PAYMENT STRUCTURE 
            add_submenu_page(
                null, //parent slug
                'Payment Structure',     // page title
                'Payment Structure',     // menu title
                'manage_options',   // capability
                'payment-structure',     // menu slug
                array($this,'render_payment_structure') // callback function
            );

            // EMPLOYEES PENDING PAYMENTS 
            add_submenu_page(
                null, //parent slug
                'Pending Payments',     // page title
                'Pending Payments',     // menu title
                'manage_options',   // capability
                'pending-payments',     // menu slug
                array($this,'render_pending_payments') // callback function
            );

            // EMPLOYEES PAYMENT PROOFS 
            add_submenu_page(
                null, //parent slug
                'Payment Proofs',     // page title
                'Payment Proofs',     // menu title
                'manage_options',   // capability
                'payment-proofs',     // menu slug
                array($this,'render_payment_proofs') // callback function
            );

            // PARKING TICKETS 
            add_submenu_page(
                null, //parent slug
                'Parking Tikcets',     // page title
                'Parking Tikcets',     // menu title
                'manage_options',   // capability
                'parking-tickets',     // menu slug
                array($this,'render_parking_tickets') // callback function
            );

            // DOOR TO DOOR SALES 
            add_submenu_page(
                null, //parent slug
                'Door To Door Sales',     // page title
                'Door To Door Sales',     // menu title
                'manage_options',   // capability
                'door-to-door-sales',     // menu slug
                array($this,'render_door_to_door_sales') // callback function
            );

            //Roles page
            add_submenu_page(
                null, //parent slug
                'Employee Roles',     // page title
                'Employee Roles',     // menu title
                'manage_options',   // capability
                'employee-roles',     // menu slug
                array($this,'render_roles') // callback function
            );

            //Roles page
            add_submenu_page(
                null, //parent slug
                'Bria License Keys',     // page title
                'Bria License Keys',     // menu title
                'manage_options',   // capability
                'bria-license-keys',     // menu slug
                array($this,'render_bria_license_keys') // callback function
            );

            //Cold Caller Roles
            add_submenu_page(
                null, //parent slug
                'Cold Caller Roles',     // page title
                'Cold Caller Roles',     // menu title
                'manage_options',   // capability
                'cold-caller-roles',     // menu slug
                array($this,'render_cold_caller_roles') // callback function
            );

            //Cold Caller Roles
            add_submenu_page(
                null, //parent slug
                'Employee Dashboards',     // page title
                'Cold Employee Dashboards',     // menu title
                'manage_options',   // capability
                'employee-dashboards',     // menu slug
                array($this,'render_employee_dashboards') // callback function
            );

            //Employee Notices
            add_submenu_page(
                null, //parent slug
                'Employee Notices',     // page title
                'Employee Notices',     // menu title
                'manage_options',   // capability
                'employee-notices',     // menu slug
                array($this,'render_employee_notices') // callback function
            );

            //Employee Attendence
            add_submenu_page(
                null, //parent slug
                'Employee Attendence',     // page title
                'Employee Attendence',     // menu title
                'manage_options',   // capability
                'employee-attendence',     // menu slug
                array($this,'render_employee_attendence') // callback function
            );

            //Disatisfied Clients
            add_submenu_page(
                null, //parent slug
                'Dissatisfied Clients',     // page title
                'Dissatisfied Clients',     // menu title
                'manage_options',   // capability
                'dissatisfied-clients',     // menu slug
                array($this,'render_dissatisfied_clients') // callback function
            );

            //Prospectus
            add_submenu_page(
                null, //parent slug
                'Prospectus',     // page title
                'Prospectus',     // menu title
                'manage_options',   // capability
                'prospectus',     // menu slug
                array($this,'render_prospectus') // callback function
            );

            //Prospectus
            add_submenu_page(
                null, //parent slug
                'Gam Settings',     // page title
                'Gam Settings',     // menu title
                'manage_options',   // capability
                'gam-settings',     // menu slug
                array($this,'render_gam_settings') // callback function
            );

            // Quote service offered
            add_submenu_page(
                null, //parent slug
                'Quote Offered Services ',     // page title
                'Quote Offered Services',     // menu title
                'manage_options',   // capability
                'quote-offered-services',     // menu slug
                array($this,'render_quote_offered_services') // callback function
            );


            //Cold Caller Roles
            add_submenu_page(
                null, //parent slug
                'Cold Calls',     // page title
                'Cold Calls',     // menu title
                'manage_options',   // capability
                'cold-calls',     // menu slug
                array($this,'render_cold_calls') // callback function
            );

            //Ads landing page
            add_submenu_page(
                null, //parent slug
                'Ads Landing Pages',     // page title
                'Ads Landing Pages',     // menu title
                'manage_options',   // capability
                'landing-pages',     // menu slug
                array($this,'render_gam_ads_landing_page') // callback function
            );

            // Calendar technician address
            add_submenu_page(
                null, //parent slug
                'Calendars Techniciain Address',     // page title
                'Calendars Techniciain Address',     // menu title
                'manage_options',   // capability
                'calendar-tech-address',     // menu slug
                array($this,'render_calendar_technician_address') // callback function
            );
        }

    }

    public function render_quote_offered_services(){
        get_template_part('include/admin/quotesheet/services-offered');
    }

    public function render_gam_settings(){
        get_template_part('include/admin/settings');
    }

    public function render_prospectus(){
        get_template_part('include/admin/client/prospectus');
    }

    public function render_dissatisfied_clients(){
        get_template_part('include/admin/client/index');
    }

    public function render_employee_attendence(){
        get_template_part('include/admin/employees/attendence/index');
    }
    
    public function render_employee_notices(){
        get_template_part('include/admin/employees/notices/index');
    }
    
    public function render_employee_dashboards(){
        get_template_part('include/admin/employees/dashboard/index');
    }

    public function render_cold_caller_roles(){
        get_template_part('include/admin/employees/cold-caller/roles/index');
    }

    public function render_bria_license_keys(){
        get_template_part('include/admin/bria/index');
    }

    public function render_door_to_door_sales(){
        get_template_part('include/admin/employees/door-to-door-sales/index');
    }
    
    public function render_parking_tickets(){
        get_template_part('include/admin/employees/parking-tickets/index');
    }

    public function render_pending_payments(){
        get_template_part('include/admin/employees/pending-payments');
    }

    public function render_payment_proofs(){
        get_template_part('include/admin/employees/payment-proofs');
    }

    public function render_payment_structure(){
        get_template_part('include/admin/employees/payment-structure');
    }

    public function render_gam_videos(){
        get_template_part('include/admin/gam-videos/index');
    }

    public function render_cold_caller_payments(){
        get_template_part('include/admin/cold-caller-pay/index');
    }

    public function cold_caller_tools(){
        get_template_part('include/cold-caller/all-tools');
    }

    public function render_tekcard_payments(){
        get_template_part('include/admin/tekcard/payments');
    }

    public function cold_caller(){
        get_template_part('include/admin/cold-caller/index');
    }

    public function email_database(){
        get_template_part('include/admin/email/index');
    }

    public function track_technician(){
        get_template_part('include/admin/technician-tracker/index');
    }

    public function animal_cage_tracker(){
        get_template_part('include/admin/animal-cage-tracker/index');
    }

    public function animal_cage_tracker_new(){
        get_template_part('include/admin/animal-cage-tracker/cage-listing');
    }

    public function non_interested_maintenance_quotes(){
        get_template_part('include/admin/quotesheet/non-interested-maintenance-quotes');
    }

    public function render_system_codes(){
        get_template_part('include/admin/system-codes');
    }

    public function render_termite_paperwork(){
        get_template_part('include/admin/termite-paperwork/index');
    }

    public function render_vehicles(){
        get_template_part('include/admin/car-center/index');
    }

    public function render_technician_notices(){
        get_template_part('include/admin/technician/technician-notices');
    }

    public function render_gam_employees(){
        get_template_part('include/admin/employees/index');
    }

    public function render_gam_technicians(){
        get_template_part('include/admin/technician/index');
    }

    public function render_office_staff(){
        get_template_part('include/admin/employees/office-staff/index');
    }

    public function render_callrail_trackers(){
        get_template_part('include/admin/callrail/index');
    }

    public function render_ads_manager_tools(){
        get_template_part('include/ads-manager/all-tools');
    }

    public function render_weekly_ads_alert(){
        get_template_part('include/admin/weekly-alert/index');
    }

    public function render_reimbursement(){
        get_template_part('include/admin/reimbursement/index');
    }
    public function renger_all_ads_notices(){
        get_template_part('include/admin/notices/all-ads-notices');
    }

    public function render_alerts_history(){
        get_template_part('include/admin/notices/alerts-history');
    }

    public function render_developer_dashboard(){
        get_template_part('include/admin/developer/dashboard');
    }

    public function render_branches(){
        get_template_part('include/admin/branches/index');
    }

    public function render_office_notes(){
        get_template_part('include/admin/office-notes/index');
    }

    public function render_office_notes_old(){
        get_template_part('include/admin/notes/notes');
    }

    public function render_daily_deposit(){
        get_template_part('/include/admin/proof-of-deoposit/daily-deposit');
    }

    public function renger_google_ads_page(){
        get_template_part('/include/admin/calculations/ads-spent');
    }

    public function render_all_tools(){
        get_template_part('/include/admin/all-tools');
    }

    public function render_clients_payments(){
        get_template_part('/include/admin/payments/clients-payments');
    }
    
    public function render_billing_page(){
        wp_enqueue_script('client-billing-script');
        wp_localize_script( 'client-billing-script', 'my_ajax_object',
        array( 'ajax_url' => admin_url( 'admin-ajax.php')  ) );
        get_template_part('/include/admin/billing/index');
    }

    public function render_invoice_calculations_page(){
        get_template_part('include/admin/calculations/invoice-calculation');
    }

    public function render_calendar_events_page(){
        get_template_part('include/admin/calendar/index');
    }

    public function render_sales_tax_page(){
        get_template_part('include/admin/calculations/sales-tax');
    }

    public function render_ads_spent_page(){
        get_template_part('include/admin/calculations/ads-spent');
    }

    public function render_commercial_quotes(){
        get_template_part('/include/admin/quotesheet/quotesheet-commercial');
    }

    public function render_residential_quotes(){
        get_template_part('/include/admin/quotesheet/quotesheet');
    }
    
    public function render_invoices(){
        get_template_part("/include/admin/invoice/invoice-all");
    }
    
    public function render_special_maintenance(){
        get_template_part("/include/admin/maintenance/special-maintenance");
    }

    public function render_yearly_termite_contract(){
        get_template_part("/include/admin/maintenance/yearly-termite-listing");
    }

    public function render_commercial_maintenance(){
        get_template_part("/include/admin/maintenance/commercial-maintenance");
    }
    
    public function render_monthly_maintenance(){
        get_template_part("/include/admin/maintenance/monthly-maintenance");
    }
    
    public function render_quarterly_maintenance(){
        get_template_part("/include/admin/maintenance/quarterly-maintenance");        
    }
    
    public function render_chemical_reports_newyork(){
        get_template_part('/include/admin/chemical-reports/newyork-report');
    }

    public function render_chemical_reports_texas(){
        get_template_part('/include/admin/chemical-reports/texas-report');
    }

    public function render_chemical_reports_california(){
        get_template_part('/include/admin/chemical-reports/chemical-report-california');
    }

    public function render_chemical_reports_florida(){
        get_template_part('/include/admin/chemical-reports/chemical-report-florida');        
    }

    public function render_chemical_reports_newjersey(){
        get_template_part('/include/admin/chemical-reports/chemical-report-newjersey');
    }

    public function render_newyork_animal_trapping_report(){
        get_template_part('/include/admin/chemical-reports/newyork-animal-trapping-report');
    }

	public function render_task_manager(){
        get_template_part("/include/admin/task-manager/index");
    } 
    
    public function render_roles(){
        get_template_part("/include/admin/employee-roles/index");
    } 

    // Cold Calls
    public function render_cold_calls(){
        get_template_part("/include/admin/cold-calls/index");
    } 

    // Ads landing tempate
    public function render_gam_ads_landing_page(){
        get_template_part('include/admin/ads-landing-pages/index');
    }

    // Calendar address template
    public function render_calendar_technician_address(){
        get_template_part('include/admin/calendar-tech-address/index');
    }

}

new Admin_page();