<?php $youtube_link = get_field( 'youtube_link' ); ?>
<?php $thumbnail = get_field( 'thumbnail' ); ?>
<?php $external_video_link = get_field( 'external_video_link' ); ?>

<?php if(!$youtube_link){

	$youtube_link = get_sub_field( 'youtube_link' );
	$thumbnail = get_sub_field( 'thumbnail' );
  $external_video_link = get_sub_field( 'external_video_link' ); 
}

?>
<script type="text/javascript">
    var embedCode = '<iframe id="video" src="https://www.youtube.com/embed/<?php echo $youtube_link ?>?hd=1&rel=0&autohide=1&showinfo=0&enablejsapi=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
</script>						

<div id='videocontainer' class="video-block">
  <?php if ( !$external_video_link ): ?>
    <?php if ( $thumbnail ): ?>
    	<img src="<?php echo $thumbnail['url']; ?>" class="video-poster-overlay" id="video-poster-overlay-<?php echo get_row_index(); ?>" /><div id="play-video-<?php echo get_row_index(); ?>" class="video-overlay"><i class="fa fa-chevron-right"></i></div>
      <script type="text/javascript">
        jQuery(function($) {
            $('#video-<?php echo get_row_index(); ?>').hide();
            $('#play-video-<?php echo get_row_index(); ?>').on('click', function() {
              $('#play-video-<?php echo get_row_index(); ?>').hide();
              $('#video-poster-overlay-<?php echo get_row_index(); ?>').hide();
               $('#video-<?php echo get_row_index(); ?>').show();
              $('#video-<?php echo get_row_index(); ?>')[0].contentWindow.postMessage('{"event":"command","func":"' + 'playVideo' + '","args":""}', '*');
            });
            
          
        });
      </script>
    <?php else: ?>
    <?php endif; ?>
        
    		<iframe id="video-<?php echo get_row_index(); ?>" class="youtube-video" src="https://www.youtube.com/embed/<?php echo $youtube_link ?>?hd=1&rel=0&autohide=1&showinfo=0&enablejsapi=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        
    
  <?php else: ?>
    <?php if ( $thumbnail ): ?>
      <img src="<?php echo $thumbnail['url']; ?>" class="video-poster-overlay" id="video-poster-overlay-<?php echo get_row_index(); ?>"  /><div id="play-video-<?php echo get_row_index(); ?>" class="video-overlay"><i class="fa fa-chevron-right"></i></div>
      <script type="text/javascript">
        jQuery(function($) {
            $('#video-<?php echo get_row_index(); ?>').hide();
            $('#play-video-<?php echo get_row_index(); ?>').on('click', function() {
              $('#play-video-<?php echo get_row_index(); ?>').hide();
              $('#video-poster-overlay-<?php echo get_row_index(); ?>').hide();
              $('#video-<?php echo get_row_index(); ?>').show();
              $('#video-<?php echo get_row_index(); ?>').get(0).play();
               // $('#video-<?php echo get_row_index(); ?>').zIndex(1);
            });
            
          
        });
      </script>
    <?php else: ?>
      
    <?php endif; ?>
    <video controls id="video-<?php echo get_row_index(); ?>">
       <source src="<?php echo $external_video_link; ?>" type="video/mp4">
       
       Your browser does not support the video tag.
      </video>
  <?php endif; ?>
</div>

