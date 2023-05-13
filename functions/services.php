<?php
global $type_name;
$type_name = 'Service';

function codex_services(){ 
  global $type_name; 
  $name = $type_name;
  $labels = array(
    'name'               => $name.'s',
    'singular_name'      => $name,
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New '.$name,
    'edit_item'          => 'Edit '.$name,
    'new_item'           => 'New '.$name,
    'all_items'          => 'All '.$name.'s',
    'view_item'          => 'View '.$name,
    'search_items'       => 'Search '.$name,
    'not_found'          => 'No '.$name.'s found',
    'not_found_in_trash' => 'No '.$name.'s found in Trash',
    'parent_item_colon'  => '',
    'menu_name'          => $name.'s'
  );
  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'rewrite'            => array( 'slug' => strtolower($name) ),
    'capability_type'    => 'post',
    'menu_position'      => 6,
    'supports'           => array( 'title','thumbnail', 'editor', 'excerpt' )
  );
  register_post_type( strtolower($name), $args );
}
add_action( 'init', 'codex_services' );
?>