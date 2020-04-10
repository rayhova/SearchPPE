<?php if ( !have_rows( 'cta' ) ):
$cta_text = get_field( 'cta_text' );
$cta_link = get_field( 'page_link' );
$outside_url = get_field( 'outside_url' );
if(!$cta_text){
	$cta_text = get_sub_field( 'cta_text' );
	$cta_link = get_sub_field( 'page_link' );
	$outside_url = get_sub_field( 'outside_url' );
	$url = get_sub_field( 'url' );
} ?>
<a href="<?php if(!$outside_url): echo $cta_link; else: echo $outside_url; endif;?>" class="cta-button"><?php echo $cta_text ?></a>



<?php elseif ( have_rows( 'cta' ) ) : ?>
	<div class="row">
	<?php while ( have_rows( 'cta' ) ) : the_row(); ?>
		<div class="col">
		<?php $cta_text = get_sub_field( 'cta_text' );
		$cta_link = get_sub_field( 'page_link' );
		$url = get_sub_field( 'url' );
		if($cta_text): ?>
			<div class="cta-btn"> 
			<a href="<?php if(!$url): echo $cta_link; else: echo $url; endif;?>" class="cta-link"><?php echo $cta_text ?></a>
			</div>
		<?php endif; ?>
		</div>
	<?php 

	endwhile; ?>
 	</div><!-- row -->
 <?php endif; ?>