<?php get_header(); ?>
		
		<?php $pest_page = get_page_by_title('Pest control company in Texas') ;?>
		<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id($pest_page->ID), 'full'); ?>
		<section id="banner" class="inner" style="background-image: url('<?php echo $img[0];?>');">
			<div class="container">
				<div class="col-xs-12">
					<div class="banner-text">
						<h1><?php the_field('banner_title', $pest_page->ID);?></h1>
					</div>
				</div>
			</div>
		</section>
		
		<section id="content">
			<div class="container">
				<div class="col-xs-12 col-sm-8" id="single">
					
					
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<article>
						
						<?php if(has_post_thumbnail()){ ?>
						<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full'); ?>
						<div class="service-icon">
							<img src="<?php echo $img[0];?>"/>
						</div>
						<?php } ?>
						
						<h3><?php the_title();?></h3>
						<?php the_content();?>
					</article>
					<?php endwhile; endif; ?>
					
				</div>
				
				<div class="col-xs-12 col-sm-4">
					<?php get_sidebar();?>
				</div>
				
			</div>
		</section>
		
		<?php get_template_part('functions/problem'); ?>
	
<?php get_footer();?>