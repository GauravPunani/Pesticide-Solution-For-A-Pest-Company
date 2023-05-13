		<section id="quote">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-5">
						<?php $quote = get_widget_data_for('Quote'); 
						$contact_no=(new GamFunctions)->getPhoneNo(get_queried_object_id());

						?>

						<h2>GOT A PEST PROBLEM?</h2>
						<p class='gray'>Eco-Friendly, Effective & Affordable Pest Protection.</p>
						<p class='green'>For 100% guaranteed results</p>
						
						<h3>Call Us <span class='red'><a href='tel:<?= $contact_no ?>'><?= $contact_no ?></a></span></h3>
						<p class='or'></p>
						<a><a href='https://www.gamexterminatingservices.com/contact-us/' class='btn btn-lg btn-red'>Request a Quote</a></a>

						<?php $pest_page = get_page_by_title('Pest control company in Texas') ;?>
						<?php $blink = $pest_page->ID==get_the_ID() ? 'class="blink-top"' : null; ?>
						<h2 <?php echo $blink;?>><?php echo @$quote->title;?></h2>
						<?php echo @$quote->text;?>
					</div>
					<div class="col-xs-12 col-sm-7">
						<img src="<?php get_redux('quote-image', true);?>" class="img-responsive"/>
					</div>
				</div>
			</div>
		</section>
		
		<footer>
			<div id="footer-top">
				<div class="container">
					<div class="row">
						<div class="col-xs-6 col-md-3">
							<p><img id="logo-footer" src="<?php get_redux('logo-footer', true);?>" class="img-responsive"/></p>
							<ul class="social-list">
								<?php $socials = array('facebook','youtube','google-plus');?>
								<?php foreach($socials as $s){ ?>
									<?php if(get_redux($s, false, false)){ ?>
									<li>
										<a target="_blank" href="<?php get_redux($s);?>">
											<span class="social <?php echo $s;?>"><i class="fa fa-<?php echo $s;?> fa-inverse"></i></span>
										</a>
									</li>
									<?php } ?>
								<?php } ?>
							</ul>
						</div>
						<div class="col-xs-6 col-md-3">
							<h4>Contact</h4>
							<p>
								<?php get_redux('sitename');?><br>
								<?php get_redux('address');?><br>
								
								<a href='tel:<?= $contact_no ?>'><?= $contact_no ?></a><br>

								<?php get_redux('email');?><br>
								<?php if(get_redux('contact-page', false, false)){ ?>
									<a href="<?php get_redux('contact-page');?>">Contact Us</a>
								<?php } ?>
							</p>
						</div>
						<div class="clearfix visible-sm-block"></div>
						
						<div class="col-xs-6 col-md-3">
							<h4>Information</h4>
							<ul>
								<?php wp_nav_menu( array( 'container_class' => '', 'container' => '', 'theme_location' => 'footer-information', 'items_wrap' => '%3$s' ) ); ?>
							</ul>
						</div>						
						<div class="col-xs-6 col-md-3">
							<h4>Services</h4>
							<ul>
								<?php wp_nav_menu( array( 'container_class' => '', 'container' => '', 'theme_location' => 'footer-services', 'items_wrap' => '%3$s' ) ); ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
			<div id="bottom-footer">
				<div class="container">
					<div class="row">
						<div class="col-xs-12 text-center">
							<?php get_redux('copyright');?>
						</div>
					</div>
				</div>
			</div>
		</footer>
		
	</div>
	

<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/assets/js/owl.carousel.min.js"></script>
	<?php wp_footer();?>
<style>iframe[name="google_conversion_frame"] {height: 0 !important;width: 0 !important;line-height: 0 !important;font-size: 0 !important;margin-top: -13px;float: left;}</style>

<!-- hibu remarketing tag START -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 853248105;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/853248105/?guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- hibu remarketing tag END -->

<style>iframe[name="google_conversion_frame"] {height: 0 !important;width: 0 !important;line-height: 0 !important;font-size: 0 !important;margin-top: -13px;float: left;}</style>

<!-- hibu remarketing tag START -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 853707419;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/853707419/?guid=ON&amp;script=0"/>
</div>
</noscript>
<!-- hibu remarketing tag END -->

</body>
</html>

<!-- //testing -->