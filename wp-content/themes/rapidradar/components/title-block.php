<?php $title = get_field( 'title' ); 
$sub_header = get_field( 'sub_header' );
$background_color = get_field( 'background_color' );
if(!$title){

	$title = get_sub_field( 'title' );
	$sub_header = get_sub_field( 'sub_header' );
	$background_color = get_sub_field( 'background_color' );
}

?>
						

<div class="title-block <?php echo $background_color ?>">
	<div class="title-box">
		<hr align="left">
		<div class="title"><?php echo $title ?>	</div>
		<div class="sub_header"><?php echo $sub_header ?></div>
	</div>
</div>