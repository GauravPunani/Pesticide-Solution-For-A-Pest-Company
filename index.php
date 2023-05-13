<?php
get_header(); ?>

<?php
if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div id="post">
		<article>
			<h1><?php
 the_title();?></h1>
			<?php the_content();?>
		</article>
	</div>
<?php endwhile; endif; ?>
	
<?php get_footer();?>