<section class="blog-results">
		
		<!-- <div class="filter-bar">
			<div class="container">
				<div class="row">Filter By:
					<div class="col"><?php echo facetwp_display( 'facet', 'tags' ); ?></div>
					
					<div class="col"><?php echo facetwp_display( 'facet', 'search' ); ?></div>
				</div>
			</div>
		</div> -->
		


		<?php

			 echo facetwp_display( 'template', 'blogs' );
		?>

		<?php echo do_shortcode('[facetwp pager="true"]'); ?>
	</section>