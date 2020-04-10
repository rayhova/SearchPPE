<?php

$alert_url = get_field( 'alert_url' );
$alert_text = get_field( 'alert_text' );
 ?>

<?php if ( get_field( 'alert_on' ) == 1 ) {  ?>
<div class="alert-bar">
	<div class="container" >
		<?php if($alert_url):?><a href="<?php echo $alert_url ?>"><?php endif; ?><?php echo $alert_text ?><?php if($alert_url):?></a><?php endif; ?><span class="close-btn"><i class="fa fa-times"></i></span>
	</div>
</div>
<?php } ?>

<script type="text/javascript">
jQuery(function($) {
	$('.close-btn').click(function(){
	    $('.alert-bar').hide();
	});

});
</script>