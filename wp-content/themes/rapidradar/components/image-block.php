<?php $image = get_field( 'image' );  ?>
<?php $caption = get_field( 'caption' ); ?>
<?php $url = get_field( 'url' ); ?>
<?php if(!$image){

	$image = get_sub_field( 'image' );
	$caption = get_sub_field( 'caption' );
	$url = get_sub_field( 'url' );
	$new_window = get_sub_field( 'open_in_new_window');
}

?>
						
<?php get_sub_field( 'caption' ); ?>
<div class="image-block">
<?php if ( $url ) { ?>
<a href="<?php echo $url ?>" <?php if ( $new_window == 1 ) { ?>target="_blank"<?php } ?> ><?php } ?>
	<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
<?php if ( $url ) { ?></a><?php } ?>
<?php if ( $caption ) { ?>
	<div class="caption-box"><?php echo $caption ?></div>
	
<?php } ?>
</div>