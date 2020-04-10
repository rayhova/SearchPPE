<!-- <div id="mapdiv" style="width: 1000px; height: 450px;"></div> -->


<div class="container">
	<div class="col"><?php echo facetwp_display( 'facet', 'school_map' ); ?>
		<?php echo facetwp_display( 'facet', 'school_location' ); ?>
	</div>
</div>

<div class="container"><?php

			 echo facetwp_display( 'template', 'schools' );
		?>

		<?php echo do_shortcode('[facetwp pager="true"]'); ?>
	</div>