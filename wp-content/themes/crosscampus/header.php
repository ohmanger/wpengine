<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package ydnxc
 * @since ydnxc 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<link rel="icon" 
      type="image/gif" 
      href="wp-content/themes/yaledailynews/ydn-logo.gif">
<link href="//cloud.webtype.com/css/4596b2de-7ff9-443c-a183-c8e0e32196e1.css" rel="stylesheet" type="text/css" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );
 
	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'ydnxc' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>
  <nav id="top" class="top-bottom">
    <div class="container clearfix">
    <span class="pull-left"><?php wp_nav_menu( array('theme_location' => 'top') ); ?></span>
      <span class="pull-right">
      	<?php 
      	if (is_user_logged_in()) {
      		$logout_url = wp_logout_url( get_home_url() );
      		echo "<a href=\"{$logout_url}\">Logout</a>";
      	} else
      	{
      		echo "<a href=\"http://yaledailynews.com/crosscampus/login\">Login</a>";
      	}?></span>
    </div>
  </nav>
	<header id="masthead" class="site-header container" role="banner"> 
			<h1 class="site-title"><a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
	</header><!-- #masthead .site-header -->

	<div id="main" class="container">
    <div class="row">
