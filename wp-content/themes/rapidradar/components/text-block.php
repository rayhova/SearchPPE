<?php $headline = get_sub_field( 'headline' ); ?>
<?php $content = get_sub_field( 'content' ); ?>

<div class="text-block">
	<?php if($headline): ?>
		<div class="headline"><?php echo $headline ?> </div>
	<?php endif; ?>
	<?php if($content): ?>
		<p><?php echo $content ?> </p>
	<?php endif; ?>
	<?php require __DIR__ . '/cta.php'; ?>
	<?php if ( have_rows( 'download' ) ) : ?>
		<?php while ( have_rows( 'download' ) ) : the_row(); ?>
			<?php $file = get_sub_field( 'file' ); ?>
			<?php if ( $file ) { ?>
				<div class="download-file"><span><img src="<?php echo get_stylesheet_directory_uri() ?>/lib/images/icon-pdf.svg" class="pdf-icon"></span><a href="<?php echo $file['url']; ?>" target="_blank"><?php the_sub_field( 'title' ); ?></a>
			<?php } ?>
		<?php endwhile; ?>
	<?php endif; ?>
</div>