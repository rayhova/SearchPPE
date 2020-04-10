<?php global $wp;
if ( is_404() ) {
    $currentpage = '';
}
else{
$currentpage =  $post->ID;
} ?>


		<div id="navbarNavPrimary" class="collapse navbar-collapse">
			<?php if ( have_rows( 'top_level_navigation', 'option' ) ) : ?>
				
						<ul id="main-menu" class="navbar-nav ml-auto">
							<?php while ( have_rows( 'top_level_navigation', 'option' ) ) : the_row(); ?>
							<?php if ( have_rows( 'links' ) ) : ?>
							<?php while ( have_rows( 'links' ) ) : the_row(); ?>
								<?php $type = get_sub_field( 'type' ); ?>
								<?php $label = get_sub_field( 'label' ); ?>

								<?php if($type == 'page'): ?>
									
								<?php $post_object = get_sub_field( 'page' ); ?>
									<?php if ( $post_object ): ?>
										<?php $post = $post_object; ?>
										<?php setup_postdata( $post ); ?> 
										<?php if(!$label): $pagetitle = preg_replace("/[^a-zA-Z]+/", "",get_the_title()); else: $pagetitle = preg_replace("/[^a-zA-Z]+/", "",$label); endif; ?>
											<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-<?php echo get_row_index(); ?>" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-<?php echo get_row_index(); ?> nav-item <?php if($currentpage == $post->ID):?>current-menu-item active<?php endif ?>" data-menu="submenu-<?php echo $pagetitle ?>"><a title="<?php if(!$label): the_title(); else: echo $label; endif; ?>" href="<?php the_permalink(); ?>" class="nav-link"><?php if(!$label): the_title(); else: echo $label; endif; ?> </a></li>
										<?php wp_reset_postdata(); ?>
									<?php endif; ?>
								<?php else: ?>
									<?php if(!$label): $pagetitle = preg_replace("/[^a-zA-Z]+/", "",get_the_title()); else: $pagetitle = preg_replace("/[^a-zA-Z]+/", "",$label); endif; ?>
								<li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-<?php echo get_row_index(); ?>" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-<?php echo get_row_index(); ?> nav-item" data-menu="submenu-<?php echo $pagetitle ?>"><a href="<?php the_sub_field( 'link_url' ); ?>" class="nav-link"><?php echo $label; ?></a></li>
								<?php endif; ?>
								
							<?php endwhile; ?>
						<?php else : ?>
							<?php // no rows found ?>
						<?php endif; ?>

							<?php if ( have_rows( 'sub_menu' ) ) : ?>
								<div class="submenu submenu-<?php echo $pagetitle ?>">
									<div class="submenu-container">
										<div class="row justify-content-md-center">
										<?php while ( have_rows( 'sub_menu' ) ) : the_row(); ?>
											<div class="col">
												<div class="title">
													<?php the_sub_field( 'title' ); ?>
												</div>
												
						
						<?php if ( have_rows( 'links' ) ) : ?>
							<ul class="submenu-menu">
							<?php while ( have_rows( 'links' ) ) : the_row(); ?>
								<?php $type = get_sub_field( 'type' ); ?>
								<?php $label = get_sub_field( 'label' ); ?>

								<?php if($type == 'page'): ?>
									
								<?php $post_object = get_sub_field( 'page' ); ?>
									<?php if ( $post_object ): ?>
										<?php $post = $post_object; ?>
										<?php setup_postdata( $post ); ?> 
											<li><a href="<?php the_permalink(); ?>"><?php if(!$label): the_title(); else: echo $label; endif; ?> </a></li>
										<?php wp_reset_postdata(); ?>
									<?php endif; ?>
								<?php else: ?>
								<li><a href="<?php the_sub_field( 'link_url' ); ?>"><?php echo $label; ?></a></li>
								<?php endif; ?>
								
							<?php endwhile; ?>
							</ul>
							
						<?php else : ?>
							<?php // no rows found ?>
						<?php endif; ?>
					
				

			
			
											</div>
												
										<?php endwhile; ?>
										</div>
									</div>
									<div class="submenubottom"></div>
								</div>
							<?php else : ?>
								<?php // no rows found ?>
							<?php endif; ?>
						<?php endwhile; ?> <!-- main top level while -->
						</ul>
						
					
			<?php else : ?>
				<?php // no rows found ?>
			<?php endif; ?>
		</div>

