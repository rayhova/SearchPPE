
	<?php while ( have_posts() ) : the_post(); 
		// Define user ID
		// Replace NULL with ID of user to be queried
		$user_id = UPT()->get_user_id();

		

		// Define prefixed user ID
		$user_acf_prefix = 'user_';
		$user_id_prefixed = $user_acf_prefix . $user_id;
		$product_1 = get_field( 'product_1', $user_id_prefixed );
		$product_2 = get_field( 'product_2', $user_id_prefixed );
		$product_3 = get_field( 'product_3', $user_id_prefixed );

		?>
		
		<div class="supplier-box">
			<div class="row">
				<div class="col-md-12 col-lg-4">
					<div class="company_name"><?php the_field( 'company_name', $user_id_prefixed ); ?></div>
					<br />
					<div class="contact_person"><?php the_field( 'contact_person', $user_id_prefixed ); ?></div>
					<div class="contact_email"><?php the_field( 'contact_email', $user_id_prefixed ); ?></div>
					<br />
					<div class="address"><?php the_field( 'street_address', $user_id_prefixed ); ?>
					
					<?php the_field( 'street_address_2', $user_id_prefixed ); ?>
					<br />
					<?php the_field( 'city', $user_id_prefixed ); ?>, <?php the_field( 'state', $user_id_prefixed ); ?>	<?php the_field( 'zip_code', $user_id_prefixed ); ?>
					</div>
					<br />
					<div class="duns">DUNS#: <?php the_field( 'duns', $user_id_prefixed ); ?></div>

				</div>
				<div class="col-md-12 col-lg-3">
					<?php echo $product_1; if($product_2): echo ", ".$product_2; endif; if($product_3): echo ", ".$product_3; endif;   ?>
				</div>
				<div class="col-md-12 col-lg-3">
					<?php the_field( 'state', $user_id_prefixed ); ?>
				</div>
				<div class="col-md-12 col-lg-2">
					<a href="<?php the_field( 'purchasing_website', $user_id_prefixed ); ?>"class="btn btn-primary visit-button" target="_blank">Visit Company Website</a>
				</div>
			</div>
		</div> <!-- .row -->
		
	<?php endwhile; ?>	
	<?php wp_reset_query(); ?>




