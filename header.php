<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-135609897-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-135609897-1');
</script>

    <meta charset="utf-8">
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php wp_title('|', true, 'right'); ?> <?php bloginfo('name'); ?></title>

    <link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="<?php bloginfo('template_directory'); ?>/assets/img/apple-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php bloginfo('template_directory'); ?>/assets/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php bloginfo('template_directory'); ?>/assets/img/apple-icon-114x114.png">
    
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/assets/css/bootstrap.min.css?ver=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,800italic,700,600,800,400" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/assets/css/owl.carousel.css?ver=1.0" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/assets/css/owl.theme.css?ver=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/assets/css/font-awesome.min.css?ver=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/assets/css/style.css?ver=1.0" type="text/css" media="all" />

    <!--[if lt IE 9]>
        <script src="<?php bloginfo('template_directory'); ?>/assets/js/html5.js"></script>
        <script src="<?php bloginfo('template_directory'); ?>/assets/js/respond.min.js"></script>
    <![endif]-->
    
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
    <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/style.css" type="text/css">
<script>

  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-98905113-1', 'auto');
  ga('send', 'pageview');

</script>
</head>

<body>
	<div class="page-wrapper">
		
		<header>
			<div class="container" id="top-header">
				<div class="row">
					<div class="col-xs-5 col-sm-3 col-md-3 col-lg-2">
						<a href="<?php echo home_url();?>">
							<img id="logo" src="<?php get_redux('logo', true);?>" class="img-responsive"/>
						</a>
					</div>
					<div class="col-lg-5 visible-lg-block">
						<div id="tagline">
							“24/7 pest control”<br>
                             "Unbeatable prices”
						</div>
					</div>
					<div class="col-xs-5 col-sm-4 col-md-5 col-lg-2">
						<ul class="social-list">
							<?php $socials = array('facebook','youtube','google-plus');?>
							<?php foreach($socials as $s){ ?>
								<?php if(get_redux($s, false, false)){ ?>
									<li>
										<a target="_blank" href="<?php get_redux($s);?>">
											<span class="social"><i class="fa fa-<?php echo $s;?> fa-inverse"></i></span>
										</a>
									</li>
								<?php } ?>
							<?php } ?>
						</ul>
					</div>
					<div class="col-sm-5 col-md-4 col-lg-3 hidden-xs">
						<div class="contact">
						<?php 
						
						$contact_no=(new GamFunctions)->getPhoneNo(get_queried_object_id());
							
								?>
									<div class="textwidget">
										<p>Call for a free estimate now</p>
										<h4><a href="tel:<?= $contact_no ?>"> <?= $contact_no ?></a></h4>
										<p>Se Habla Español</p>
									</div>
								<?php
						?>
						</div>
					</div>
					<!-- menu toggle -->
					<div class="col-xs-2 col-sm-1 visible-xs-block">
						<a href="" id="toggle"><i class="fa fa-bars fa-inverse"></i></a>
					</div>
				</div>
			</div>
			
			<nav>
				<div class="container">
					<ul id="main-nav" class="header-navigation">
						<li>
							<a href="<?php echo home_url();?>">
								<img src="http://www.gamexterminating.com/wp-content/uploads/2018/06/logo-icon.png" class="img-responsive"/>
							</a>
						</li>
						<?php wp_nav_menu( array( 'container_class' => '', 'container' => '', 'theme_location' => 'header', 'items_wrap' => '%3$s' ) ); ?>
					</ul>
						<div class="contact visible-xs-block">
							
							<?php if($contact_no): ?>
								<p>Call for a free estimate now <span class="bigger"><a href="tel:<?= $contact_no;  ?>"><?= $contact_no;  ?></a></span></p>
								<p>Se Habla Español <span class="bigger"></span></p>
							<?php else: ?>
								<?php 
									$phone_header = get_widget_data_for('Header Phone'); $phone_header = $phone_header[0];
									$pr = array('</p>', '<h4>', '</h4>'); $pt = array(' <span class="bigger">','','</span></p>');
									echo str_replace($pr,$pt,$phone_header->text);
								?>
							<?php endif; ?>
						</div>
				</div>
			</nav>
		</header>