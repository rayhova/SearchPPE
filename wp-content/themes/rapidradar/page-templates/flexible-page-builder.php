<?php if ( have_rows( 'flexible_page_builder' ) ): ?>
	<div class="flex-page-wrapper">
	<?php $i = 0; ?>
	<?php while ( have_rows( 'flexible_page_builder' ) ) : the_row();
	$i++; ?>
		<?php if ( get_row_layout() == 'full_width_image' ) : ?>
			<?php include( get_template_directory() . '/components/image-block.php'); ?>
		<?php elseif ( get_row_layout() == 'sub_hero' ) : ?>
			<div class="sub-hero">
				<?php $row_index = get_row_index(); ?>
			<?php include( get_template_directory() . '/components/sub-hero.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'job_filter_block' ) : ?>
			<div id="job-filter-block-<?php echo get_row_index();?>">
				<?php if ( get_sub_field( 'job_filter_section' ) == 1 ) { 
					include( get_template_directory() . '/components/job-search-block.php'); 
				} else { 
				 
				} ?>
			</div>
		<?php elseif ( get_row_layout() == 'spacer' ) : ?>
			<?php if ( get_sub_field( 'spacer' ) == 1 ) { 
				include( get_template_directory() . '/components/spacer-block.php'); 
			} else { 
			 
			} ?>
		<?php elseif ( get_row_layout() == 'blog_filter_block' ) : ?>
			<?php if ( get_sub_field( 'blog_filter_section' ) == 1 ) { 
				include( get_template_directory() . '/components/blog-filter-block.php'); 
			} else { 
			 
			} ?>
		<?php elseif ( get_row_layout() == 'map_section' ) : ?>
			<?php if ( get_sub_field( 'include_map_section' ) == 1 ) { 
				include( get_template_directory() . '/components/map-block.php'); 
			} else { 
			 
			} ?>
		<?php elseif ( get_row_layout() == 'carousel_block' ) : ?>
			<div class="container carousel_block">
			<?php include( get_template_directory() . '/components/carousel-block.php'); ?>
			</div>
			
		<?php elseif ( get_row_layout() == 'wysiwyg_block' ) : ?>
			<div class="container">
				<div class="row justify-content-md-center">
					<div class="col-lg<?php the_sub_field( 'column_width' ); ?>">
					<?php include( get_template_directory() . '/components/wysiwyg-block.php'); ?>
					</div>
				</div>
			</div>
		<?php elseif ( get_row_layout() == 'separator' ) : ?>
			<div class="container">
			<?php include( get_template_directory() . '/components/separator-block.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'post_query_block' ) : ?>
			<div class="container">
			<?php include( get_template_directory() . '/components/post-type-block.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'tabs_block' ) : ?>
			<div class="container">
			<?php include( get_template_directory() . '/components/tabs-block.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'accordion_block' ) : ?>
			<div class="container">
			<?php include( get_template_directory() . '/components/accordion-block.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'related_blog_posts' ) : ?>
			<div class="container">
			<?php include( get_template_directory() . '/components/related-post-blocks.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'testimonial_block' ) : ?>
			<div class="container">
			<div class="center">
			<?php include( get_template_directory() . '/components/quote-block.php'); ?>
			</div>
			</div>
		<?php elseif ( get_row_layout() == 'form_section' ) : ?>
			<div id="fw-form-<?php echo get_row_index(); ?>">
			<?php include( get_template_directory() . '/components/full-width-form-block.php'); ?>
			</div>
		<?php elseif ( get_row_layout() == 'row' ) : ?>
			<?php $uneven = get_sub_field( 'uneven' ) ?>
			<?php $smaller_side = get_sub_field( 'which_side_is_smaller' ); ?>
			<div id="full-row-<?php echo get_row_index(); ?>" class="full-row <?php the_sub_field( 'background_color' ); ?>">
				<div class="container">
					<div class="row justify-content-md-center ue-<?php echo $uneven ?>">
						<?php while ( have_rows( 'block' ) ) : the_row(); ?>
							<?php if ( get_row_layout() == 'image' ) : ?>
								<div class="col-lg<?php the_sub_field( 'column_width' ); ?><?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/image-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'related_blog_post' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/related-post-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'quote' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
									
								<?php include( get_template_directory() . '/components/quote-block.php'); ?>
									
								</div>
							<?php elseif ( get_row_layout() == 'page_block' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/page-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'large_page_block' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/large-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'statistic_block' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/statistic-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'video_block' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/video-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'title_block' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/title-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'wysiwyg_block' ) : ?>
								<div class="col-lg<?php the_sub_field( 'column_width' ); ?><?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/wysiwyg-block.php'); ?>
								</div>
							<?php elseif ( get_row_layout() == 'side_form_block' ) : ?>
								<div class="col<?php if ( ($uneven == 1) && get_row_index() == $smaller_side ) {?>-md-5<?php } ?>">
								<?php include( get_template_directory() . '/components/form-block.php'); ?>
								</div>
							<?php endif; ?>
						<?php endwhile; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endwhile; ?>
	</div>
<?php else: ?>
	<?php // no layouts found ?>
<?php endif; ?>