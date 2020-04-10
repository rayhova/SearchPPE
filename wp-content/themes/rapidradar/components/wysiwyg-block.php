<div class="wysiwyg-block">
	<?php $wysiwyg = get_field( 'wysiwyg' );
if (!$wysiwyg) {
	$wysiwyg = get_sub_field( 'wysiwyg' );
}
echo $wysiwyg;  ?>
</div>