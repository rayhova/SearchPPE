<?php
/**
 * Template Name: Home Page Template
 *
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if (is_user_logged_in() ) {
wp_redirect ( home_url("/edit-profile") );
exit;
}


get_header(); ?>
<div class="home-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 form-side">
				<div class="content-wrapper">
					<div class="cta"><?php the_field( 'cta' ); ?></div>
					<div class="form-buttons">
						<div class="buyer-form-button form-button">
							Buyer
						</div>
						<div class="seller-form-button form-button">
							Seller
						</div>
					</div>
					<div class="buyer-form home-forms"><?php $buyer_form = get_field('buyer_form');
					gravity_form($buyer_form['id'], true, true, false, '', true, 1); ?>
					</div>
					<div class="seller-form home-forms"><?php $seller_form = get_field('seller_form');
					gravity_form($seller_form['id'], true, true, false, '', true, 1); ?>
					</div>
				</div>
			</div>
			<div class="d-none d-lg-block col-lg-6 hero-side">
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(function ($) {
		jQuery(document).ready(function($) {

	

$('#homeloginform input[type="text"]').attr('placeholder', 'Email');
	$('#homeloginform input[type="password"]').attr('placeholder', 'Password');
	
	$('#homeloginform label[for="user_login"]').contents().filter(function() {
		return this.nodeType === 3;
	}).remove();
	$('#homeloginform label[for="user_pass"]').contents().filter(function() {
		return this.nodeType === 3;
	}).remove();
	
	

});
  
  $(".buyer-form-button").click(function () {
  	$(".home-forms").hide();
  	$(".form-button").removeClass('active');
  	$(".buyer-form-button").addClass('active');
    
    $(".buyer-form").show();
  });
  $(".seller-form-button").click(function () {
  	 $(".home-forms").hide();
  	$(".form-button").removeClass('active');
  	$(".seller-form-button").addClass('active');
   
    $(".seller-form").show();
  });
});
</script>

<?php get_footer('home');
