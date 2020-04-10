<?php
/**
 * Template Name: Edit Profile Page
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
global $current_user; 
get_currentuserinfo();
$container = get_theme_mod( 'understrap_container_type' );

// Example: Get ID of current user
$user_id = get_current_user_id();

// Define prefixed user ID
$user_acf_prefix = 'user_';
$user_id_prefixed = $user_acf_prefix . $user_id;
?>

<?php if ( is_front_page() ) : ?>
  <?php get_template_part( 'global-templates/hero' ); ?>
<?php endif; ?>


<div class="wrapper" id="full-width-page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content">

		<div class="row">

			<div class="col-md-12 content-area" id="primary">
				<h1 class="edit-profile-title">Your <?php if ( user_can( $current_user, "buyer" ) ){ echo 'Buyer'; } elseif ( user_can( $current_user, "supplier" ) ){  echo 'Seller'; } ?> Profile</h1>
				
				<main class="site-main" id="main" role="main">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'loop-templates/content', 'page' ); ?>
						<h4><?php the_field( 'company_name', $user_id_prefixed ); ?></h4>
						<h5>Duns#: <?php the_field( 'duns', $user_id_prefixed ); ?></h5>
						<?php  
							if ( user_can( $current_user, "buyer" ) ){ 
							$form = get_field('edit_buyer_form');
							gravity_form($form['id'], false, false, false, '', true, 1);
							}
							if ( user_can( $current_user, "supplier" ) ){ 
						 	$form = get_field('edit_seller_form');
							gravity_form($form['id'], false, false, false, '', true, 1);
							}
							 ?>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #full-width-page-wrapper -->

<?php get_footer();
