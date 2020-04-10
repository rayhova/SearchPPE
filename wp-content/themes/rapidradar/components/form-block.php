<?php $side_form = get_field( 'side_form' );
$background_color = get_field( 'background_color' );

if(!$side_form){

$side_form = get_sub_field( 'side_form' );
$background_color = get_sub_field( 'background_color' );
}

?>

<div class="form-block-wrapper <?php echo $background_color ?>">
	<?php gravity_form($side_form, false, false, false, '', true, $side_form); ?>
	
</div>