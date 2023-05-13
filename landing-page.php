<?php 
/* Template Name: landing page */ 
get_header();
?>
<section id="content">
	<div class="container">
		<div class="col-xs-12 col-sm-12" id="single">					
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<article>
				
				<?php the_content();?>
			</article>
			<?php endwhile; endif; ?>
		</div>
		
							
		</div>
		
	
</section>

<?php get_footer();?>	