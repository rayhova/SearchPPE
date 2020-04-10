
	<section class="job-results">
		
		<div class="filter-bar">
			<div class="container">
				<div class="row">Filter By:
					<div class="col"><?php echo facetwp_display( 'facet', 'position' ); ?></div>
					<div class="col"><?php echo facetwp_display( 'facet', 'location' ); ?></div>
					<div class="col"><?php echo facetwp_display( 'facet', 'search' ); ?></div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="col-md-12 p-0">
				<div class="job-header">
					<div class="row">
						<div class="col-sm-2 p-0">
							<h3>Date</h3>
						</div>
						<div class="col-sm-8 p-0">
							<h3>Jobs</h3>
						</div>
					</div>
				</div>
			</div>
			<hr>
		</div>


		<?php

			 echo facetwp_display( 'template', 'jobs' );
		?>

		<?php echo do_shortcode('[facetwp pager="true"]'); ?>
	</section>
