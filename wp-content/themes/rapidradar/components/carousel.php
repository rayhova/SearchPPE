<?php $content_type = get_field( 'content_type' ); ?>


	<section class="carousel-slide">
		<div id="carousel-slide" class="carousel slide" data-ride="carousel">
			<div class="carousel-inner ow w-100 mx-auto">
				<?php $count = 0; 
				?>
				<?php if($content_type == "custom"): ?>
				<?php if ( have_rows( 'custom_content' ) ) : ?>
				<?php while ( have_rows( 'custom_content' ) ) : the_row(); ?>
				<?php $row_number = get_row_index(); ?>
					<div class="carousel-item col-md-3<?php if ( !$count ) { echo ' active'; } ?> slide-<?php echo $row_number; ?>">
					 	<img class="d-block w-100" src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>">
						 <?php the_sub_field( 'title' ); ?>
						 <?php the_sub_field( 'content' ); ?>
						 <a href="<?php the_sub_field( 'cta_url' ); ?>"><?php the_sub_field( 'cta' ); ?></a>
					 	</div>
					</div>
				<?php $count++; ?>
				<?php 
				
				endwhile; ?>
				<?php else : ?>
					<?php // no rows found ?>
				<?php endif; ?>
				<?php else: ?>
			</div><!-- carousel-inner -->
			<a class="carousel-control-prev" href="#hero-slide" role="button" data-slide="prev">
		    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
		    <span class="sr-only">Previous</span>
		  </a>
		  <a class="carousel-control-next" href="#hero-slide" role="button" data-slide="next">
		    <span class="carousel-control-next-icon" aria-hidden="true"></span>
		    <span class="sr-only">Next</span>
		  </a>
		</div> 
	</section>
	<script type="text/javascript">
	jQuery(function($){
		$('.carousel').carousel();
		});
	</script>

