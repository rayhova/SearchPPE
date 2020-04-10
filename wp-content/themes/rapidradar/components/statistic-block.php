<?php $stat_title = get_field( 'stat_title' ); 
$statistic = get_field( 'statistic' );
$caption = get_field( 'caption' );
if(!$stat_title){

	$stat_title = get_sub_field( 'stat_title' );
	$statistic = get_sub_field( 'statistic' );
	$caption = get_sub_field( 'caption' );
}

?>
						

<div class="statistic-block">
	
	<div class="stat-title"><?php echo $stat_title ?>	</div>
	<div class="statistic"><?php echo $statistic ?></div>
	<div class="caption"><?php echo $caption ?></div>
</div>