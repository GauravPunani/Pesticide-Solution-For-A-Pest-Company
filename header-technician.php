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
      <!--[if IE]>
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <![endif]-->
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
      <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/assets/technician/dashboard.css" type="text/css" />
      <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
      <?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
      <?php wp_head(); ?>
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