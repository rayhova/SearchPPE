<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$container = get_theme_mod( 'understrap_container_type' );
?>

<?php get_template_part( 'sidebar-templates/sidebar', 'footerfull' ); ?>
<div class="top-footer">
	<div class="container">
		<div class="row">
			<div class="col-md-3"><?php $radius_logo = get_field( 'radius_logo', 'option' ); ?>
				<?php if ( $radius_logo ) { ?>
					<img src="<?php echo $radius_logo['url']; ?>" alt="<?php echo $radius_logo['alt']; ?>" class="radius-logo" />
				<?php } ?>
			</div>
			<div class="col-md-9">
				<div class="disclaimer"><?php the_field( 'disclaimer', 'option' ); ?></div>
			</div>
		</div>
	</div>
</div>
<div class="wrapper" id="wrapper-footer">

	<div class="<?php echo esc_attr( $container ); ?>">

		<div class="row">

			<div class="col-md-12">

				<footer class="site-footer" id="colophon">

					<div class="site-info">

						<?php the_field( 'copyright', 'option' ); ?>

					</div><!-- .site-info -->

				</footer><!-- #colophon -->

			</div><!--col end -->

		</div><!-- row end -->

	</div><!-- container end -->

</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>

