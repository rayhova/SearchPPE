<?php $post_type = get_sub_field( 'post_type' ); ?>
<?php $posts_per_page = get_sub_field( 'posts_per_page' ); ?>
<?php $order = get_sub_field( 'order' ); ?>
<?php
			   // WP_Query arguments
				$args = array(
					'post_type'              => $post_type,
					'posts_per_page'         => $posts_per_page,
					'order'                  => $order,
					'orderby'                => 'date',
				);

				// The Query
				$query = new WP_Query( $args );
				?>
				<?php if ( $query->have_posts() ) : ?>

					<div class="row">

						<?php /* Start the Loop */ ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>

							<div class="col-md-4">
								<?php if($post_type == 'jobs'): 
									$locations = get_the_terms( $post->ID, 'location' );
									$location = array_shift( $locations );
									$locationslug = $location->slug;
									$locationname = $location->name;
									$date = get_field( 'open_date' , false, false);
									$date = new DateTime($date); ?>
									<div class="page-block">
										<div class="job-title"><a href="<?php the_field( 'link' ); ?>" target="_blank"><?php the_title(); ?></a></div>
										<div class="row">
											<div class="col-sm-2 p-0">
												<div class="date"><?php echo $date->format('d'); ?></div>
												<div class="month"><?php echo $date->format('M'); ?></div>
											</div>
											<div class="col-sm-8 p-0">
												
												<div class="job-info"><?php the_field( 'company' ); ?></div>
												<div class="job-location">
													<?php echo $locationname ?></div>
											</div>
										</div>
									</div>
								<?php else: ?>
								<div class="blog-box">
									<div class="image">
										<?php if ( have_rows( 'slider' ) ) : ?>
											<?php while ( have_rows( 'slider' ) ) : the_row(); ?>
												<?php $image = get_sub_field( 'image' ); ?>
												<?php if ( $image ) { ?>
													<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
												<?php } ?>
											<?php endwhile; ?>
										<?php else : ?>
											<?php // no rows found ?>
										<?php endif; ?>
									</div>
									<h3 class="blog-title">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
									<p class="author"><?php the_field( 'author' ); ?></p>
									<p><?php the_field( 'excerpt' ); ?></p>
									<div class="row">
										<div class="col-md-6 p-0">
											<p class="tags"><?php echo get_the_tag_list('<span>', ',','</span>');?></p>
						        		</div>
						        		<!-- <div class="col-md-6 p-0">
											<p class="date">
												<?php the_date(); ?>
											</p>
						        		</div> -->
						        	</div>
								</div>
								<?php endif; ?>
							</div><!-- col-md-4 -->
							

						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</div>
				<?php else : ?>

					<?php get_template_part( 'template-parts/content', 'none' ); ?>

				<?php endif; ?>