<div class="twitter-block d-none d-sm-block tablet-up">
	<i class="fa fa-twitter"></i>
	<a class="twitter-timeline" data-width="<?php the_sub_field( 'width' ); ?>" data-height="<?php the_sub_field( 'height' ); ?>" data-link-color="<?php the_sub_field( 'link_color' ); ?>" data-tweet-limit="<?php the_sub_field( 'tweet_limit' ); ?>" href="https://twitter.com/<?php the_sub_field( 'twitter_username' ); ?>" data-chrome="<?php // options ( value )
	$options_array = get_sub_field( 'options' );
	if ( $options_array ):
		foreach ( $options_array as $options_item ):
		 	echo $options_item;
		endforeach;
	endif; ?>">Tweets</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
</div>
<div class="twitter-block d-block d-sm-none mobile-only">
	<i class="fa fa-twitter"></i>
	<a class="twitter-timeline" data-width="<?php the_sub_field( 'width' ); ?>" data-height="<?php the_sub_field( 'height' ); ?>" data-link-color="<?php the_sub_field( 'link_color' ); ?>" data-tweet-limit="2" href="https://twitter.com/<?php the_sub_field( 'twitter_username' ); ?>" data-chrome="<?php // options ( value )
	$options_array = get_sub_field( 'options' );
	if ( $options_array ):
		foreach ( $options_array as $options_item ):
		 	echo $options_item;
		endforeach;
	endif; ?>">Tweets</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
</div>

<script type="text/javascript">
	
	jQuery('.twitter-block').delegate('#twitter-widget-0','DOMSubtreeModified propertychange', function() {
  //function call to override the base twitter styles
  customizeTweetMedia();
 });
 
 var customizeTweetMedia = function() {
 
  //overrides font styles and removes the profile picture and media from twitter feed
  jQuery('.twitter-block').find('.twitter-timeline').contents().find('.timeline-Tweet-media').css('display', 'none');
  // jQuery('.twitter-block').find('.twitter-timeline').contents().find('img.Avatar').css('display', 'none');
  // jQuery('.twitter-block').find('.twitter-timeline').contents().find('span.TweetAuthor-avatar.Identity-avatar').remove();
  // jQuery('.twitter-block').find('.twitter-timeline').contents().find('span.TweetAuthor-screenName').css('font-size', '16px');
  // jQuery('.twitter-block').find('.twitter-timeline').contents().find('span.TweetAuthor-screenName').css('font-family', 'Raleway');
 
  //also call the function on dynamic updates in addition to page load
//   jQuery('.twitter-block').find('.twitter-timeline').contents().find('.timeline-TweetList').bind('DOMSubtreeModified propertychange', function() {
//    customizeTweetMedia(this);
// });

   
      //jQuery('.twitter-block.mobile-only').find('.twitter-timeline').contents().find('.timeline-TweetList-tweet.customisable-border').css('float', 'left'); 
      jQuery('.twitter-block.mobile-only').find('.twitter-timeline').contents().find('.timeline-TweetList-tweet.customisable-border').css('width', '100%');  
    
	
}
</script>