<?php $title = get_field( 'title' );
if (!$title) {
	$title = get_sub_field( 'title' );
} ?>

<?php $learn_more_link = get_field( 'learn_more_link' );
if (!$learn_more_link) {
	$learn_more_link = get_sub_field( 'learn_more_link' );
} ?>
<div class="row">
	<div class="col-lg-6"><h2><?php echo $title ?></h2></div>
	<div class="col-lg-6"><a href="<?php echo $learn_more_link?>" class="cta-button slider-more-link">Learn More</a></div>
</div>
<div id="stepCarousel" class="carousel slide" data-interval="false">
    <div class="carousel-inner" role="listbox">
<?php 
$steps = get_sub_field('steps');
if( have_rows('steps') ):
	$chunks = array_chunk($steps, 3);
	$html = "";
	foreach($chunks as $chunk):
	($chunk === reset($chunks)) ? $active = "active" : $active = "";
    $html .= '<div class="carousel-item '.$active.'">';
    $i = 0;
    foreach($chunk as $step){
    	$get_row_index = get_row_index();
    	$i++;
    	//$stepp = get_sub_field( 'step' );
    	$html .= '<div class="step-wrapper">';
    	$html .= '<div class="step-block">';
    	$html .= '<div class="step-square">';
    	$html .= '<div class="step">Step</div>';
    	$html .= '<div class="step-number">'.$step['step_number'].'</div>';
    	$html .= '</div>';
    	$html .= '</div>';
    	$html .= '<div class="step-text">'.$step['step'].'</div>';
    	$html .= '</div>';
    	
            }
            $html .= '</div>';
	$html .= '<div class="cleared"></div>';
	endforeach;


	echo $html;
	endif
	 ?>
	
			<a class="carousel-control-prev" href="#stepCarousel" role="button" data-slide="prev">
			<i class="fa fa-chevron-left"></i>
			<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next" href="#stepCarousel" role="button" data-slide="next">
			<i class="fa fa-chevron-right"></i>
			<span class="sr-only">Next</span>
			</a>
		</div>
	</div>
