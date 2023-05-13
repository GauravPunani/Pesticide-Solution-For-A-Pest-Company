<?php 
/* Template Name: Homepage */
get_header();
?>
		<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full'); ?>
	
		
	<style>
    #quote{
    display:none;
    }
    </style>
	
<?php get_footer();?>