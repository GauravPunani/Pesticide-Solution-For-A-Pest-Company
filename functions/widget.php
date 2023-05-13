<?php
function get_widget_data_for($sidebar_name) {
	global $wp_registered_sidebars, $wp_registered_widgets;
	
	$output = array();
	
	$sidebar_id = false;
	foreach( $wp_registered_sidebars as $sidebar ) {
		if( $sidebar['name'] == $sidebar_name ) {
			$sidebar_id = $sidebar['id'];
			break;
		}
	}
	
	if( !$sidebar_id ) {
		return $output;
	} 
	
	$sidebars_widgets = wp_get_sidebars_widgets();
	$widget_ids = $sidebars_widgets[$sidebar_id];
	
	if( !$widget_ids ) {
		return array();
	}
	
	foreach( $widget_ids as $id ) {
		$option_name = $wp_registered_widgets[$id]['callback'][0]->option_name;
		$key = $wp_registered_widgets[$id]['params'][0]['number'];
		$widget_data = get_option($option_name);
		$output[] = (object) $widget_data[$key];
	}
	
	return $output;
}
?>