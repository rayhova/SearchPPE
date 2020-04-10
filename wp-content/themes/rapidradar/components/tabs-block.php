<?php $tabs_title = get_sub_field( 'tabs_title' ); 
$tabs_id = preg_replace("/[^a-zA-Z]+/", "", $tabs_title);
$caption = get_sub_field( 'caption' );?>
<h2 class="center"><?php echo $tabs_title ?></h2>
<?php if ($caption):?><p><?php echo $caption ?></p><?php else: ?><p></p><?php endif; ?>
<?php if( have_rows('tabs') ): ?>
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<?php $i=0; while ( have_rows('tabs') ) : the_row(); ?>
			<?php 
				$string = sanitize_title( get_sub_field('tab_title') ); 
			?>
			<li role="presentation" <?php if ($i==0) { ?>class="active"<?php } ?>  >
				<a href="#<?php echo $string ?>-<?php echo $tabs_id; ?>" aria-controls="<?php echo $string ?>" role="tab" data-toggle="tab" <?php if ($i==0) { ?>class="active show"<?php } ?>><?php the_sub_field('tab_title'); ?></a>
			</li>
		<?php $i++; endwhile; ?>
	</ul>
	<div class="tab-content">
		<?php $i=0; while ( have_rows('tabs') ) : the_row(); ?>
			<?php 
				$string = sanitize_title( get_sub_field('tab_title') ); 
			?>
		    <div role="tabpanel" class="tab-pane text-center fade <?php if ($i==0) { ?>in active show<?php } ?>" id="<?php echo $string; ?>-<?php echo $tabs_id; ?>">
		    	
		    	<?php the_sub_field('tab_text'); ?>
				<?php $tab_content_type = get_sub_field( 'tab_content_type' ); ?>
				<?php if($tab_content_type == "carousel"): ?>
					<?php if ( have_rows( 'carousel' ) ) : ?>
						<?php while ( have_rows( 'carousel' ) ) : the_row(); ?>
		    	<?php $carousel_content_type = get_sub_field( 'content_type' ); ?>
<h2 class="carousel-title"><?php the_sub_field( 'title' ); ?></h2>
<p class="carousel-text"><?php the_sub_field( 'carousel_text' ); ?></p>
	<section class="carousel-slide">
		<div id="tab-carousel-slide-<?php echo get_row_index(); ?>" class="carousel slide" data-interval="false" data-ride="carousel" data-pause="hover" data-touch="true"> 
			<div class="carousel-inner row w-100 mx-auto" role="listbox">
				<?php $count = 0; 
				?>
			<?php if($carousel_content_type == "custom"): ?>
				
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
			<?php else: ?>
			<?php endif; ?>
			</div><!-- carousel-inner -->
			<a class="carousel-control-prev" href="#tab-carousel-slide-<?php echo get_row_index(); ?>" role="button" data-slide="prev">
		    <i class="fa fa-chevron-left"></i>
		    <span class="sr-only">Previous</span>
		  </a>
		  <a class="carousel-control-next" href="#tab-carousel-slide-<?php echo get_row_index(); ?>" role="button" data-slide="next">
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
	  $('#<?php echo $tabs_id?> #tab-carousel-slide-<?php echo get_row_index(); ?>').on('slide.bs.carousel', function (e) {

	    var $e = $(e.relatedTarget);
	    var idx = $e.index();
	    if ($(window).width() < 600) {
		    var itemsPerSlide = 1;
		}
		else {
		   var itemsPerSlide = 3;
		}
	   
	    var totalItems = $('#<?php echo $tabs_id?> #tab-carousel-slide-<?php echo get_row_index(); ?>.carousel-item').length;
	    
	    if (idx >= totalItems-(itemsPerSlide-1)) {
	        var it = itemsPerSlide - (totalItems - idx);
	        for (var i=0; i<it; i++) {
	            // append slides to end
	            if (e.direction=="left") {
	                $('#<?php echo $tabs_id?> #tab-carousel-slide-<?php echo get_row_index(); ?>.carousel-item').eq(i).appendTo('#<?php echo $tabs_id?> #tab-carousel-slide-<?php echo get_row_index(); ?>.carousel-inner');
	            }
	            else {
	                $('#<?php echo $tabs_id?> #tab-carousel-slide-<?php echo get_row_index(); ?>.carousel-item').eq(0).appendTo('#<?php echo $tabs_id?> #tab-carousel-slide-<?php echo get_row_index(); ?>.carousel-inner');
	            }
	        }
	    }
	});
	  });
  </script>
			<?php endwhile; ?>
		<?php endif ?>
			<?php elseif($tab_content_type == "page-blocks"): ?>
		    	<?php if ( have_rows( 'page_blocks' ) ) : ?>
		    		<div class="row">
					<?php while ( have_rows( 'page_blocks' ) ) : the_row(); 
						$type = get_sub_field( 'type' );
						$post_object = get_sub_field( 'page' );
						$image = get_sub_field( 'image' ); 
						$bottom_title = get_sub_field( 'bottom_title' );
						$bottom_description = get_sub_field( 'bottom_description' );
						$page_title = get_sub_field( 'page_title' );
						$outside_url = get_sub_field( 'outside_url' );
						$new_window = get_sub_field( 'open_in_new_window'); ?>
						<div class="col">
							<?php if($type == 'custom'): ?>
								<div class="page-block <?php if ( $image ) { ?>pgb-img<?php }?>">
								<?php if ( $image ) { ?>
										<a href="<?php echo $outside_url ?>" <?php if ( $new_window == 1 ) { ?>target="_blank"<?php } ?> ><img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" /></a>
									<?php } ?>
									<?php if ( $image ) { ?><div class="pb-title"><?php } ?>
									<div class="page-title"><a href="<?php echo $outside_url ?>" <?php if ( $new_window == 1 ) { ?>target="_blank"<?php } ?>><?php echo $page_title ?></a></div>
									<?php if ( $image ) { ?></div><?php } ?>
								</div>
							<?php else: ?>
								<?php $post = $post_object; ?>
								<div class="page-block <?php if ( $image ) { ?>pgb-img<?php }?>"> 
								<?php setup_postdata( $post ); ?>
									<?php if ( $image ) { ?>
										<a href="<?php the_permalink(); ?>" <?php if ( $new_window == 1 ) { ?>target="_blank"<?php } ?>><img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" /></a>
									<?php } ?>
									<?php if ( $image ) { ?><div class="pb-title"><?php } ?>
									<div class="page-title"><a href="<?php the_permalink(); ?>" <?php if ( $new_window == 1 ) { ?>target="_blank"<?php } ?>><?php the_title(); ?></a></div>
									<?php if ( $image ) { ?></div><?php } ?>
								<?php wp_reset_postdata(); ?>
								</div>
							<?php endif; ?>
								
								<h2><?php echo $bottom_title ?></h2>
								<p><?php echo $bottom_description ?></p>
						</div>
					<?php endwhile; ?>
					</div>
				<?php else : ?>
					<?php // no rows found ?>
				<?php endif; ?>
				<?php elseif($tab_content_type == "wysiwyg"): ?>
					<?php the_sub_field( 'wysiwyg' ); ?>
				<?php endif; ?>	
		    </div>
		<?php $i++; endwhile; ?>
	</div>
<?php endif; ?>

<script type="text/javascript">
jQuery(function($) {
	$('ul#myTab li').click(function() {
  		$('ul#myTab li').removeClass('active');
  		$(this).addClass('active');
	});
});
</script>
