<?php $logo = get_field( 'logo', 'option' ); ?>
<?php $logo_url = get_field( 'logo_url', 'option' ); ?>
<?php
function logo() {
	$logo = get_field( 'logo_image', 'option' );
	$logo_url = get_field( 'logo_url', 'option' );
	if(!$logo):
		
	else: 
		if(!$logo_url):
		echo '<a href="/"><img id="site-logo"  src="'.$logo['url'].' " alt="'.$logo['alt'].'" /></a>';
		else: 
		echo '<a href="'.$logo_url.'" target="_blank"><img id="site-logo"  src="'.$logo['url'].' " alt="'.$logo['alt'].'" /></a>';
		endif;
	endif;
}
