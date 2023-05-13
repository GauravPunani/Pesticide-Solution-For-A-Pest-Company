<?php 
get_header();
?>
		
		<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full'); ?>
		<?php if(!$img){ $img[0] = get_redux('default-banner', true, false); } ?>
        
        <?php if(!empty($img[0])){ ?>
		<section id="banner" class="inner" style="background-image: url('<?php echo $img[0];?>');">
			<div class="container">
				<div class="col-xs-12">
					<div class="banner-text">
						<h1><?php the_field('banner_title');?></h1>
					</div>
				</div>
			</div>
		</section>
        <?php } ?>
		
		<section id="content">
			<div class="container">
				<div class="col-xs-12 col-sm-12" id="single">					
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<article>
						<h3><?php the_title();?></h3>
						<?php the_content();?>
					</article>
					<?php endwhile; endif; ?>
				</div>
                
				<?php /*
				<div class="col-xs-12 col-sm-4">
					<aside>
					<?php	get_sidebar(); ?>
					</aside>
				</div>
                */?>
				
			</div>
		</section>
		
		<?php /* get_template_part('functions/problem'); */ ?>
	
<?php get_footer();?>