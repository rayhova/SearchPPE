<?php
get_field( 'featured_post' ) = $featured_post;
get_field( 'number_of_posts' ) = $number_of_posts;
get_field( 'post_type' ) = $post_type; ?>

<section id="featured">
	<div class="container">
		<?php if($featured_post):
		$featured_post_query = array(
			            array(
			                'key' => 'featured',
			                'value' => true,
			                'compare' => '='
			            )
			        ) ?>
		<?php 
		else:
			$featured_post_query ='';
		endif;
		?>

			<h2>Featured Coaches</h2>
			<div class="row">
				<?php $args = array(
			        'post_type' => $post_type,
			        'orderby' => 'rand',
			        'numberposts' => $number_of_posts,
			        'meta_query' => $featured_post_query,
			    );
			     $featured = get_posts( $args );
			     if ( $featured ) {
			    foreach ( $featured as $post ) : 
			        setup_postdata( $post ); ?>
				<div class="col-md-4 col-sm-12">
					<div class="featured-box">
						<a href="<?php the_permalink(); ?>" target=""><img class="featured-thumb" src="<?php the_post_thumbnail_url(); ?>" /><div class="title-overlay"><?php echo $location ?></div></a>
						<div class="featured-title">
							<a href="<?php the_permalink(); ?>" target=""><?php the_title(); ?></a>
							
						</div>
						<div class="featured-location">
							<a href="<?php the_permalink(); ?>" target=""><?php the_title(); ?></a>
							
						</div>
						<div class="featured-date">
							<a href="<?php the_permalink(); ?>" target=""><?php the_title(); ?></a>
							
						</div>  
						<a href="" class="btn">Learn More</a>
					</div>
				</div>
				<?php
		    endforeach; 
		    wp_reset_postdata();
		    }
		    ?>
				
				<a href="/trips/" class="view-all">View All</a>
			</div><!--row -->
	</div><!-- container -->
</section>