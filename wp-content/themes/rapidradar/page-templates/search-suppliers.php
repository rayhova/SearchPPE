<?php
/**
 * Template Name: Search Supplier Page
 *
 * Template for displaying a page without sidebar even if a sidebar widget is published.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
if (!is_user_logged_in() ) {
wp_redirect ( home_url() );
exit;
}

get_header();
$container = get_theme_mod( 'understrap_container_type' );
?>

<?php if ( is_front_page() ) : ?>
  <?php get_template_part( 'global-templates/hero' ); ?>
<?php endif; ?>


<div class="wrapper" id="full-width-page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content">

		<div class="row">

			<div class="col-md-12 content-area" id="primary">

				<div class="row filter-row"><div class="filter-text">Filter By:</div>
					
					<div class="col-md-12 col-lg-3"><?php echo facetwp_display( 'facet', 'products' ); ?></div>
					<div class="col-md-12 col-lg-3"><?php echo facetwp_display( 'facet', 'state' ); ?></div>
					
					<div class="col-md-12 col-lg-3"><div class="quantity">Quantity Available</div><?php echo facetwp_display( 'facet', 'quantity' ); ?></div>
					<div class="col-md-12 col-lg-3"><a href="#" onclick="FWP.reset(); event.preventDefault();" class="btn btn-secondary clear-all-btn">Clear All</a></div>
				</div>
				<main class="site-main" id="main">
					<div class="row supplier-results-heading">
						<div class="col-md-12 col-lg-4">
							Company
						</div>
						<div class="col-md-12 col-lg-3">
							Available Items
						</div>
						<div class="col-md-12 col-lg-3">
							Location
						</div>
						<div class="col-md-12 col-lg-2">
						</div>
					</div>
					<?php echo facetwp_display( 'template', 'seller' ); ?>
				</main><!-- #main -->

			

			<?php echo do_shortcode('[facetwp pager="true"]'); ?>

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #full-width-page-wrapper -->

<?php get_footer();
