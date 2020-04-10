<?php $tag_or_post = get_sub_field( 'tag_or_post' ); ?>
<?php $tag_term = get_sub_field( 'tag' ); ?>

<?php if ( $tag_or_post == 'post' ): ?>
	<?php $post_object = get_sub_field( 'blog_post' ); ?>
	<?php if ( $post_object ): ?>
		<?php $post = $post_object; ?>
		<?php setup_postdata( $post ); ?> 
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
					<div class="col-md-12 p-0">
						<p class="tags"><?php echo get_the_tag_list('<span>', ',','</span>');?></p>
	        		</div>
	        		<div class="col-md-6 p-0">
						<!-- <p class="date">
							<?php the_date(); ?>
						</p> -->
	        		</div>
	        	</div>
			</div>
		<?php wp_reset_postdata(); ?>
	<?php endif; ?>
<?php else: ?>
	<?php if ($tag_term) {
		$first_tag = $tag_term->term_id;
		$args=array(
		'tag__in' => array($first_tag),
		'posts_per_page'=>1,
		'ignore_sticky_posts' => 1
		);
		$my_query = new WP_Query($args);
		if( $my_query->have_posts() ) {
		while ($my_query->have_posts()) : $my_query->the_post(); ?>
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
					<div class="col-md-12 p-0">
						<p class="tags"><?php echo get_the_tag_list('<span>', ',','</span>');?></p>
	        		</div>
	        		<div class="col-md-6 p-0">
						<p class="date">
							<?php the_date(); ?>
						</p>
	        		</div>
	        	</div>
			</div>
			<?php
		endwhile;
		}
		wp_reset_query();
		}
		?>
<?php endif; ?>