<?php 
/* Template Name: Blog */
get_header();
?>
		
		<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'full'); ?>
		<?php if(!$img){ $img[0] = get_redux('default-banner', true, false); } ?>
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
				<div class="col-xs-12 col-sm-8" id="single">					
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<article>
						<h2><?php the_title();?></h2>
						<?php the_content();?>
					</article>
					<?php endwhile; endif; ?>
					
					<?php wp_reset_query();
					$paged = get_query_var('page') ? get_query_var('page') : 1;
					$offset =  ($paged - 1) * get_option('posts_per_page'); 
					query_posts('post_type=post&orderby=date&order=DESC&offset='.$offset);
					while (have_posts()) : the_post(); ?>
					
					<div class="post">
						<a href="<?php the_permalink();?>"><h3><?php the_title();?></h3></a>
						<?php the_excerpt();?>
					</div>
					
					<?php endwhile; wp_reset_query();?>
					
					<nav>
						<ul class="pager">
							<?php blog_paging();?>
						</ul>
					</nav>
				
				</div>
				
				<div class="col-xs-12 col-sm-4">
					<aside>
						<?php get_sidebar();?>
					</aside>
				</div>
				
			</div>
		</section>
		
		<?php get_template_part('functions/problem'); ?>
	
<?php get_footer();?>