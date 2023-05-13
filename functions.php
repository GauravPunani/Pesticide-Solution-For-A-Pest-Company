<?php

if(!isset($_SESSION)){session_start();}

add_theme_support( 'post-thumbnails' );
add_action('init', 'register_custom_menu');
// add_shortcode( 'address', 'address_shortcode' );

add_filter( 'auto_update_theme', '__return_false' );
// add_action('admin_menu','admin_menu_pages');


function register_my_session()
{
	if(!isset($_SESSION)){session_start();}
}

add_action('init', 'register_my_session');

function register_custom_menu() {
	register_nav_menu('header', __('Header'));
	register_nav_menu('footer-information', __('Footer Information'));
	register_nav_menu('footer-services', __('Footer Services'));
}

if (function_exists('register_sidebar')) {
	$sidebars = array('Header_Phone', 'Sidebar', 'Testimonial', 'Quote');
	foreach($sidebars as $sidebar){
		register_sidebar(array(
			'name' => str_replace('_',' ',$sidebar),
			'id'   => strtolower($sidebar),
			'description'   => 'Sidebar for '.strtolower(str_replace('_',' ',$sidebar)),
			'before_widget' => '',
			'after_widget'  => '',
		));
	}
}

# redux
if ( !class_exists( 'ReduxFramework' ) && file_exists( dirname( __FILE__ ) . '/framework/ReduxCore/framework.php' ) ) {
    require_once( dirname( __FILE__ ) . '/framework/ReduxCore/framework.php' );
}
if ( !isset( $redux_demo ) && file_exists( dirname( __FILE__ ) . '/framework/config.php' ) ) {
    require_once( dirname( __FILE__ ) . '/framework/config.php' );
}

function get_redux($for, $url = false, $echo = true){
	$return = null;
	$data = get_option( 'redux_data' );
	if ( !empty($data[$for]) ){
		if($url){
			$return = $data[$for]['url'];
		} else {
			$return = $data[$for];
		}
	}
	
	if($echo){
		echo $return;
	} else {
		return $return;
	}
}

/* paging */
function blog_paging(){
	$count_posts = wp_count_posts();
	$total = ceil( $count_posts->publish / get_option("posts_per_page") );
	if($total > 1){
		$current_page = get_query_var('paged') ? get_query_var('paged') : 1; 
		$html = paginate_links(array(
			'base' => get_pagenum_link(1) . '%_%',
			'format' => get_option('permalink_structure') ? 'page/%#%/' : '&paged=%#%',
			'current' => $current_page,
			'total' => $total,
			'prev_next' => true,
			'prev_text' => __('Prev'),
			'next_text' => __('Next'),
			'type' => 'array'
		));
		
		foreach ($html as $number) { 
			if(strip_tags($number)=='Prev'){
				echo '<li class="previous">'.$number.'</li>';
			} else if(strip_tags($number)=='Next'){
				echo '<li class="next">'.$number.'</li>';
			}
		}
	}
}

get_template_part('functions/services');
get_template_part('functions/widgets');
get_template_part('functions/widget');

/*
	This method add pre formatted and print the array or string and halt the script execution
*/
if(!function_exists('pdie')){
	function pdie($data,$end=true){
		echo "<pre>" ; print_r($data); ($end ? wp_die() : '');
	}
}

// TRAITS

// Trait validation
require_once( __DIR__ . '/include/traits/input-validation.php');

// DB methods trait
// require_once( __DIR__ . '/include/traits/db.php');

// CLASSES 

// DB Modal Class 
require_once( __DIR__ . '/include/classes/class-db-modal.php');

// Admin Assets Class 
require_once( __DIR__ . '/include/classes/class-admin-assets.php');

// aaa theme  general  functions 
require_once( __DIR__ . '/include/classes/class-gamex-functions.php');

// aaa Navigation functions 
require_once( __DIR__ . '/include/classes/class-navigations.php');

// MPDF Class 
require_once( __DIR__ . '/include/classes/class-mpdf.php');

// Sendgrid email functions (library) 
require_once( __DIR__ . '/include/classes/class-sendgrid.php');

// Goodle Calendar functions/events 
require_once( __DIR__ . '/include/classes/class-calendar.php');

// Admin Pages Class 
require_once( __DIR__ . '/include/classes/class-admin-pages.php');

// Chemical Report Parent Class 
require_once( __DIR__ . '/include/classes/chemical-report/class-chemical-report.php');

// chemical report newyork 
require_once( __DIR__ . '/include/classes/chemical-report/class-chemical-report-newyork.php');

// chemical report california 
require_once( __DIR__ . '/include/classes/chemical-report/class-chemical-report-california.php');

// chemical report florida 
require_once( __DIR__ . '/include/classes/chemical-report/class-chemical-report-florida.php');

// chemical report texas 
require_once( __DIR__ . '/include/classes/chemical-report/class-chemical-report-texas.php');

// chemical report newjersey 
require_once( __DIR__ . '/include/classes/chemical-report/class-chemical-report-newjersey.php');

// Invoice Class 
require_once( __DIR__ . '/include/classes/class-invoice.php');

// Quotesheet Class 
require_once( __DIR__ . '/include/classes/class-quote.php');

// Renew Maintenance Contracts 
require_once( __DIR__ . '/include/classes/class-renew-maintenance-contract.php');

// Maintenance Contracts Template Class 
require_once( __DIR__ . '/include/classes/maintenance/class-contracts-templates.php');

// Maintenance Class 
require_once( __DIR__ . '/include/classes/maintenance/class-maintenance.php');

// Monthly Quarterly Maintenance Class 
require_once( __DIR__ . '/include/classes/maintenance/class-monthly-quarterly-maintenance.php');

// Special Maintenance Class 
require_once( __DIR__ . '/include/classes/maintenance/class-special-maintenance.php');

// Commercial Maintenance Class 
require_once( __DIR__ . '/include/classes/maintenance/class-commercial-maintenance.php');

// Yearly Termite Maintenance Class 
require_once( __DIR__ . '/include/classes/maintenance/class-yearly-termite.php');

// Proof of Deposit functions 
require_once( __DIR__ . '/include/classes/class-proof-of-deposit.php');

// Task Manager functions 
require_once( __DIR__ . '/include/classes/class-task-manager.php');

// Proof of Deposit functions 
require_once( __DIR__ . '/include/classes/class-sales-tax.php');

// auto billing invoices
require_once( __DIR__ . '/include/classes/class-auto-billing.php');

// Stripe Payment Class
// require_once( __DIR__ . '/include/classes/class-stripe.php');

// Callrail Api  Class
require_once( __DIR__ . '/include/classes/class-callrail.php');

// Technician Class
require_once( __DIR__ . '/include/classes/class-technician-details.php');

// Notices Class
require_once( __DIR__ . '/include/classes/class-notices.php');

// Office Notes by technician Class
require_once( __DIR__ . '/include/classes/class-notes.php');

// Prospectus by technician Class
require_once( __DIR__ . '/include/classes/class-prospectus.php');

// Twillio sms service Class
require_once( __DIR__ . '/include/classes/class-twilio.php');

// Branch Class
require_once( __DIR__ . '/include/classes/class-branches.php');



// Car Center Class
require_once( __DIR__ . '/include/classes/class-car-center.php');

// Termite Paperwork Class
require_once( __DIR__ . '/include/classes/class-termite-paperwork.php');

// Technician Payment Class 
require_once( __DIR__ . '/include/classes/class-technician-pay.php');

// Ads Report Class 
require_once( __DIR__ . '/include/classes/ads-spent/class-ads-report.php');

// P/L Report Class 
require_once( __DIR__ . '/include/classes/ads-spent/class-pl-report.php');

// Tracking Technician Class 
require_once( __DIR__ . '/include/classes/class-technician-tracker.php');

// Emails Class 
require_once( __DIR__ . '/include/classes/class-emails.php');

// Cold Caller Class 
require_once( __DIR__ . '/include/classes/class-cold-caller.php');

// Animal Cage Tracker Class 
require_once( __DIR__ . '/include/classes/class-animal-cage-tracker-new.php');

// Tekcard Payment Gateway Class 
require_once( __DIR__ . '/include/classes/class-tekcard.php');

// Cold Caller Pay Class 
// require_once( __DIR__ . '/include/classes/class-cold-caller-pay.php');

// Invoice Flow Class 
require_once( __DIR__ . '/include/classes/invoice/class-invoice-flow.php');

// Employee Class 
require_once( __DIR__ . '/include/classes/employee/class-employee.php');

// Office Staff Class 
require_once( __DIR__ . '/include/classes/employee/class-office-staff.php');

// Door To Door Sales Class 
require_once( __DIR__ . '/include/classes/employee/class-door-to-door-sales.php');

// Payments Class 
require_once( __DIR__ . '/include/classes/employee/class-payments.php');

// Parking Tickets Class 
require_once( __DIR__ . '/include/classes/class-parking-tickets.php');

// Role Class
require_once( __DIR__ . '/include/classes/class-roles.php');

// Bria Keys Class
require_once( __DIR__ . '/include/classes/class-bria.php');

// Attendance Class
require_once( __DIR__ . '/include/classes/class-attendance.php');

// Office Tasks Class
require_once( __DIR__ . '/include/classes/tasks/class-office-tasks.php');

// Bitly shortend link Class
require_once( __DIR__ . '/include/classes/class-bitly.php');

// Codes Class
require_once( __DIR__ . '/include/classes/class-codes.php');

// Leads Class
require_once( __DIR__ . '/include/classes/class-leads.php');

// Services Class
require_once( __DIR__ . '/include/classes/class-services.php');

// AWS Class
require_once( __DIR__ . '/include/classes/class-aws-methods.php');

// ColdCalls Class
require_once( __DIR__ . '/include/classes/class-new-cold-calls.php');

// Admin pages meta box 
require_once( __DIR__ . '/include/classes/class-admin-meta-box.php');


// function defer_parsing_of_js( $url ) {
//     if ( is_user_logged_in() ) return $url; //don't break WP Admin
//     if ( FALSE === strpos( $url, '.js' ) ) return $url;
//     if ( strpos( $url, 'jquery.js' ) ) return $url;
//     if ( strpos( $url, 'jquery.min.js' ) ) return $url;
//     return str_replace( ' src', ' defer src', $url );
// }
// add_filter( 'script_loader_tag', 'defer_parsing_of_js', 10 );