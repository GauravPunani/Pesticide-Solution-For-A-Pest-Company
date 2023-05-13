<?php 
/* Template Name: Pest Library */
get_header();
?>
		
		<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full'); ?>
		<section id="banner" class="inner" style="background-image: url('<?php echo $img[0];?>');">
			<div class="container">
				<div class="col-xs-12">
					<div class="banner-text">
						<h1><?php the_field('banner_title');?></h1>
					</div>
				</div>
			</div>
		</section>
		
		<section id="content">
			<div class="container">
				<div class="col-xs-12">
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<article>
						<h2 class="blink green"><?php the_title();?></h2>
						<p><?php the_content();?></p>
					</article>
					<?php endwhile; endif; ?>
				</div>
				
				<?php wp_reset_query();
				query_posts('post_type=service&meta_key=show&meta_value=slide&posts_per_page=-1&orderby=date&order=DESC');
				while (have_posts()) : the_post(); ?>
				
				<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full'); ?>
				<div class="row service-block">
					<?php $offset = ($wp_query->current_post + 1)%2==0 ? 'col-sm-push-8 col-md-push-9' : null; ?>
					<div class="col-xs-12 col-sm-4 col-md-3 <?php echo $offset;?>">
						<div class="service-icon">
							<a href="<?php the_permalink();?>"><img src="<?php echo $img[0];?>"/></a>
						</div>
					</div>
					<?php $offset = ($wp_query->current_post + 1)%2==0 ? 'col-sm-pull-4 col-md-pull-3' : null; ?>
					<div class="col-xs-12 col-sm-8 col-md-9 <?php echo $offset;?>">
						<article>
							<a href="<?php the_permalink();?>"><h3><?php the_title();?></h3></a>
							<?php the_excerpt();?>
						</article>
					</div>
				</div>
				<?php endwhile; wp_reset_query();?>
				
			</div>
		</section>
		
		<?php get_template_part('functions/problem'); ?>
	
<?php get_footer();?>