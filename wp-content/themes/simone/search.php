<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Simone
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		if ( have_posts() ) {
			?>
			<header class="page-header">
				<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'simone' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</header><!-- .page-header -->
			<?php
			/* Start the Loop */
			while ( have_posts() ) {
				the_post();
				get_template_part( 'content', 'search' );
			}
			simone_paging_nav();
		} else {
			get_template_part( 'content', 'none' );
		}
		?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
