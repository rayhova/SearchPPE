<?php $type = get_field( 'type' ); ?>
<?php $post_object = get_field( 'page' ); ?>
<?php $image = get_field( 'image' ); ?>
<?php $bottom_title = get_field( 'bottom_title' ); ?>
<?php $bottom_description = get_field( 'bottom_description' ); 
$new_window = get_field( 'open_in_new_window'); ?>

<?php if(!$type){

	$type = get_sub_field( 'type' );
	$post_object = get_sub_field( 'page' );
	$image = get_sub_field( 'image' ); 
	$bottom_title = get_sub_field( 'bottom_title' );
	$bottom_description = get_sub_field( 'bottom_description' );
	$page_title = get_sub_field( 'page_title' );
	$outside_url = get_sub_field( 'outside_url' );
	$new_window = get_sub_field( 'open_in_new_window');
}

?>
						

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
