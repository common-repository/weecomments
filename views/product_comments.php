<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( isset($product_info->avg_rating) ) {
	$avg_rating = round($product_info->avg_rating, 1);
	$num_ratings = $product_info->num_ratings;
} else {
	$avg_rating = null;
	$num_ratings = null;
}

?>

<div id="wee_prod"></div>


<?php if ($weecomments_options['WEE_SUBSCRIPTION'] > 1 && $avg_rating > 0): ?>
<div class="wee_rating_container">
    
	<small>
    <div xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Review-aggregate">
       <span property="v:itemreviewed"><strong><?=get_the_title()?></strong></span> has a rating of 
       <span rel="v:rating">
          <span typeof="v:Rating">
            <strong>
                
             <?php if($WEE_RATING_TYPE == 5):?>
             <span property="v:average"><?=$avg_rating?></span>
             of
             <span property="v:best">5</span>
             <?php else:?>
             <span property="v:average"><?=round($avg_rating * 2, 1)?></span>
             of
             <span property="v:best">10</span>
             <?php endif;?>
            </strong>
          </span>
       </span>
       based on <strong><span property="v:votes"><?=$num_ratings?></span> customer reviews.</strong>
    </div>
    </small>
    
</div>
<?php endif;?>
