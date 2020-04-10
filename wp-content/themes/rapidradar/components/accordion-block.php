<?php if (!$i) {
	$i = '';
} ?>
<div class="accordion-wrapper accordion-<?php echo $i ?>">
	
<h2><?php the_sub_field( 'accordion_header' ); ?></h2>
		
<?php if ( have_rows( 'accordion' ) ) : ?>
	<div id="accordion<?php echo $i ?>">

	<?php while ( have_rows( 'accordion' ) ) : the_row(); ?>
		<div class="card">
	    <div class="card-header" id="heading-<?php echo get_row_index(); ?>-<?php echo $i ?>">
	      <h5 class="mb-0">
	        <button id="btn-<?php echo get_row_index(); ?>-<?php echo $i ?>" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse-<?php echo get_row_index(); ?>-<?php echo $i ?>" aria-expanded="false" aria-controls="collapse-<?php echo get_row_index(); ?>-<?php echo $i ?>">
	          <?php the_sub_field( 'title' ); ?> <i class="fa fa-plus"></i></i>
	        </button>
	      </h5>
	    </div>

	    <div id="collapse-<?php echo get_row_index(); ?>-<?php echo $i ?>" class="collapse" aria-labelledby="heading-<?php echo get_row_index(); ?>-<?php echo $i ?>" data-parent="#accordion<?php echo $i ?>">
	      <div class="card-body">
	        <?php the_sub_field( 'content' ); ?>
	      </div>
	    </div>
	  </div>
		
		
	<?php endwhile; ?>
	</div>
<?php else : ?>
	<?php // no rows found ?>
<?php endif; ?>


  </div>

  