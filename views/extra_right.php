<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( isset($product_info->avg_rating) ) {
	$avg_rating = round($product_info->avg_rating, 1);
	$num_ratings = $product_info->num_ratings;
} else {
	$avg_rating = null;
	$num_ratings = null;
}

$stars = '';

for ($i=1; $i<=5; $i++) {
    switch ($avg_rating) {
        case ($avg_rating >= $i):
            $stars .= '<span class="midstar midstar_active5"></span>';
            break;
        case ($avg_rating >= (($i-1)+0.75)):
            $stars .= '<span class="midstar midstar_active4"></span>';
            break;
        case ($avg_rating >= (($i-1)+0.5)):
            $stars .= '<span class="midstar midstar_active3"></span>';
            break;
        case ($avg_rating >= (($i-1)+0.25)):
            $stars .= '<span class="midstar midstar_active2"></span>';
            break;
        case ($avg_rating < $i):
            $stars .= '<span class="midstar midstar_active1"></span>';
            break;
        default:
            $stars .= '<span class="midstar midstar_active1"></span>';
            break;
    }
}




if ($avg_rating > 0): ?>
<div class="wee_rating_container">
	
    <a href="#tab-reviews" class="wee-reviews-trigger">
    <div class="wee_stars_container"><?=$stars?></div>
	</a>
    <br />
    <a href="#tab-reviews" class="wee-reviews-trigger weecomments-link"><small>view reviews</small></a>
    
    <?php if (($weecomments_options['WEE_SUBSCRIPTION']) > 1 && ($num_ratings > 0)):?>
    <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <?php if($WEE_RATING_TYPE == 5):?>
        <meta itemprop="ratingValue" content="<?=$avg_rating?>">
        <meta itemprop="bestRating" content="5">
        <?php else:?>
        <meta itemprop="ratingValue" content="<?=round($avg_rating * 2, 1)?>">
        <meta itemprop="bestRating" content="10">
        <?php endif;?>
        <meta itemprop="ratingCount" content="<?=$num_ratings?>">
        <meta itemprop="name" content="<?=get_the_title()?>">
    </div>
    <?php endif;?>
    
</div>

<script type="text/javascript">
var $ = jQuery.noConflict();
$("document").ready(function(){
	$(".wee-reviews-trigger").click(function() {
		$(".reviews_tab a").click();
	});
});
</script>

<?php endif;?>