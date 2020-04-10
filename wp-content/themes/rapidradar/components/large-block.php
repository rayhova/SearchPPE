<?php $title = get_field( 'title' ); 
$copy = get_field( 'copy' );
$cta_text = get_field( 'cta_text' );
$page_link = get_field( 'page_link' );
$outside_url = get_field( 'outside_url' );
$background_color = get_field( 'background_color' );

if(!$title){

$title = get_sub_field( 'title' );
$copy = get_sub_field( 'copy' );
$cta_text = get_sub_field( 'cta_text' );
$page_link = get_sub_field( 'page_link' );
$outside_url = get_sub_field( 'outside_url' );
$background_color = get_sub_field( 'background_color' );
}

?>
						

<div class="large-block <?php echo $background_color ?>">
	<hr align="left">
	<div class="title"><?php echo $title ?>	</div>
	<div class="copy"><?php echo $copy ?></div>
	<?php if($cta_text): ?>
		<a class="btn bg-secondary" href="<?php if(!$outside_url): echo $page_link; else: echo $outside_url; endif;?>"><?php echo $cta_text ?></a>
	<?php endif; ?>
</div>