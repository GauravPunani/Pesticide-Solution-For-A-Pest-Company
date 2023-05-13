		<section id="problem">
			<div class="container">
				<div class="row">
					<div class="col-xs-12">
						<?php get_redux('problem');?>
					</div>
					<div class="cleafix"></div>
					
					<?php wp_reset_query();
						  query_posts('post_type=service&meta_key=show&meta_value=problem&posts_per_page=-1&orderby=date&order=DESC');
						  while (have_posts()) : the_post(); ?>
						
						<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full'); ?>
						<div class="col-xs-12 col-sm-6 col-md-3 text-center">
							<p class="icon"><a href="<?php the_permalink();?>"><img src="<?php echo $img[0];?>"/></a></p>
							<p><a href="<?php the_permalink();?>"><?php the_title();?></a></p>
						</div>
					
					<?php endwhile; wp_reset_query();?>

				</div>
			</div>
		</section>	