<?php $content_type = get_sub_field( 'content_type' ); ?>
<h2 class="carousel-title"><?php the_sub_field( 'carousel_title' ); ?></h2>
<p class="carousel-text"><?php the_sub_field( 'carousel_text' ); ?></p>

	<section class="carousel-slide">
		<div id="carousel-slide-<?php echo get_row_index(); ?>" class="carousel slide" data-interval="false" data-ride="carousel" data-pause="hover">
			<div class="carousel-inner row w-100 mx-auto" role="listbox">
				<?php $count = 0; 
				?>
			<?php if($content_type == "custom"): ?>
				<?php if ( have_rows( 'custom_content' ) ) : ?>
					<?php while ( have_rows( 'custom_content' ) ) : the_row(); ?>
					<?php $row_number = get_row_index(); ?>
						<div class="carousel-item col-md-4 <?php if ( !$count ) { echo 'active'; } ?> slide-<?php echo $row_number; ?>">
							<div class="carousel-block">
								<?php $image = get_sub_field( 'image' ); ?>
							 	<img class="d-block w-100" src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>">
								 <h4><?php the_sub_field( 'title' ); ?></h4>
								 <p><?php the_sub_field( 'content' ); ?></p>
								 <a href="<?php the_sub_field( 'cta_url' ); ?>" class="carousel-cta btn"><?php the_sub_field( 'cta' ); ?></a>
							</div>
					 	</div>
						
					<?php $count++; ?>
					<?php 
					
					endwhile; ?>
				<?php endif; ?>
			<?php elseif($content_type == "posts") : ?>
					<?php $row_number = get_row_index(); ?>
					<?php
					   $args=array(
						
						'post_type' => array('post'),
						'posts_per_page'=>9,
						'orderby' => 'date'
						);
						$my_query = new WP_Query($args);
						if( $my_query->have_posts() ) {
						while ($my_query->have_posts()) : $my_query->the_post(); 
					?>
					<?php $cat = get_the_category(); ?>
						        <div class="carousel-item col-md-4 <?php if ( !$count ) { echo 'active'; } ?> slide-<?php echo $row_number; ?>">
									<?php $image = get_sub_field( 'image' ); ?>
								 	<?php the_post_thumbnail(); ?>
									<h5 class="blog-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h5>
									 <p class="tags"><a href="<?php echo esc_url( get_category_link( $cat[0]->term_id ) ) ?>"><?php echo $cat[0]->cat_name; ?></a></p>
									 
							 	</div>
							 	<?php $count++; ?>
						   <?php endwhile;
				}
				wp_reset_postdata();
				 ?>
				<?php endif; ?>
			
			</div><!-- carousel-inner -->
			<a class="carousel-control-prev" href="#carousel-slide-<?php echo get_row_index(); ?>" role="button" data-slide="prev">
		    <i class="fa fa-chevron-left"></i>
		    <span class="sr-only">Previous</span>
		  </a>
		  <a class="carousel-control-next" href="#carousel-slide-<?php echo get_row_index(); ?>" role="button" data-slide="next">
		    <i class="fa fa-chevron-right"></i>
		    <span class="sr-only">Next</span>
		  </a>
		</div> 
	</section>
	<!-- <script type="text/javascript">
	jQuery(function($){
		$('.carousel').carousel();
		});
	</script> -->
	<script>
	jQuery(function($){
	  $('#carousel-slide-<?php echo get_row_index(); ?>').on('slide.bs.carousel', function (e) {

	    var $e = $(e.relatedTarget);
	    var idx = $e.index();
	    if ($(window).width() < 600) {
		    var itemsPerSlide = 1;
		}
		else {
		   var itemsPerSlide = 3;
		}
	    var totalItems = $('#carousel-slide-<?php echo get_row_index(); ?>.carousel-item').length;
	    
	    if (idx >= totalItems-(itemsPerSlide-1)) {
	        var it = itemsPerSlide - (totalItems - idx);
	        for (var i=0; i<it; i++) {
	            // append slides to end
	            if (e.direction=="left") {
	                $('#carousel-slide-<?php echo get_row_index(); ?>.carousel-item').eq(i).appendTo('#carousel-slide-<?php echo get_row_index(); ?>.carousel-inner');
	            }
	            else {
	                $('#carousel-slide-<?php echo get_row_index(); ?>.carousel-item').eq(0).appendTo('#carousel-slide-<?php echo get_row_index(); ?>.carousel-inner');
	            }
	        }
	    }
	});
	  });
  </script>