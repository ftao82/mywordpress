<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Simone
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php
if ( is_singular() && pings_open() ) {
	printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
}

wp_head();
?>
</head>
<body <?php body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
} else {
	do_action( 'wp_body_open' );
}
?>
<div id="page" class="hfeed site">

	<header id="masthead" class="site-header" role="banner">
			<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'simone' ); ?></a>
			<?php if ( get_header_image() && ( 'blank' == get_header_textcolor() ) ) { ?>
				<figure class="header-image">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img src="<?php header_image(); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="">
					</a>
				</figure>
			<?php } // End header image check. ?>
			<?php
			if ( get_header_image() && ! ( 'blank' == get_header_textcolor() ) ) {
				echo '<div class="site-branding header-background-image" style="background-image: url(' . esc_url( get_header_image() ) . ')">';
			} else {
				echo '<div class="site-branding">';
			}
			?>
			<div class="title-box">
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</div>
		</div>

		<nav id="site-navigation" class="main-navigation clear" role="navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'simone' ); ?>">
			<h2 class="menu-toggle"><a href="#"><?php _e( 'Menu', 'simone' ); ?></a></h2>

			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
			<div class="extra-menu">
				<?php simone_social_menu(); ?>
				<div class="search-toggle">
					<a href="#search-container"><span class="screen-reader-text"><?php _e( 'Search', 'simone' ); ?></span></a>
				</div>
			</div>
		</nav><!-- #site-navigation -->

				<div id="header-search-container" class="search-box-wrapper clear hide">
			<div class="search-box clear">
				<?php get_search_form(); ?>
			</div>
		</div>

	</header><!-- #masthead -->

	<div id="content" class="site-content">
