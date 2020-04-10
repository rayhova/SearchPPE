<div class="quote-box">
<?php 
$rows = get_field('quote');
if(!$rows){
$rows = get_sub_field('quote');
$quote = '<i class="fa fa-quote-left"></i>';
}
$rand_row = $rows[ array_rand( $rows ) ];?>
<div class="title"><?php echo $rand_row['title']; ?></div>
<div class="quote"><?php echo $quote?><?php echo $rand_row['quote']; ?></div>
<div class="author"><?php echo $rand_row['author']; ?></div>
<div class="author-title"><?php echo $rand_row['author_title']; ?></div>
		
	
</div>

