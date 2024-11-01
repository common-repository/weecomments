<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: weeComments
Plugin URI: weecomments.com
Description: Plugin de weeComments para WooCommerce v2.2 o posterior
Version: 3.1.4
Author: <a href="https://weecomments.com">weeComments</a>
*/


require_once('classes/wee_weemailClass.php');

add_action('wp_print_styles', 'weecomments_styles'); 
add_action('wp_footer', 'wee_javascripts');
add_action('admin_enqueue_scripts', 'weecomments_admin_styles');

register_activation_hook(__FILE__, 'weecomments_install');
register_deactivation_hook(__FILE__,'weecomments_uninstall');

function weecomments_install()
{
	wee_createWeecommentsDatabase();
}

function weecomments_uninstall()
{
	delete_option( 'weecomments_options'); 	
}

function weecomments_admin_styles()
{
    wp_enqueue_style( 'back_css', plugins_url( '/css/back.css', __FILE__ ));
}

//Añade el css
function weecomments_styles()
{
    wp_enqueue_style( 'weecomments_external_css', "https://weecomments.com/css/style_webservice.css");
    wp_enqueue_style( 'inner_css', plugins_url('/css/style.css', __FILE__) );
}

//Añade el javascript
function wee_javascripts()
{
	$weecomments_options = get_option('weecomments_options');
    
    if (ICL_LANGUAGE_CODE && strlen(ICL_LANGUAGE_CODE) >= 2 && ICL_LANGUAGE_CODE != 'ICL_LANGUAGE_CODE') {
        $lang = substr(ICL_LANGUAGE_CODE, 0, 2);
    } else {
        $lang = $weecomments_options['WEE_LANG'];
    }
    
	
echo "
<script type='text/javascript'>
jQuery('document').ready(function($){

	if (document.querySelector('.weecomments') !== null) {
		
		var xhr_widget = new XMLHttpRequest();
		xhr_widget.open('GET', 'https://weecomments.com/".$lang."/webservice/show_small?callback=lol&id_shop=".$weecomments_options['WEE_ID_SHOP']."&lang=".$lang."&css=0', true);
		xhr_widget.send(null);

		toReadyStateDescriptionWidget = function (state_widget){
			switch (state_widget) {
			  case 4:
				var string = xhr_widget.responseText;
				var jsonObject = string.substring(4, string.length - 1);	
				
				var data = (typeof jsonObject == 'object' ? jsonObject : JSON.parse(jsonObject));
				/*console.log(data.widget);*/
				document.getElementsByClassName('weecomments')[0].innerHTML = data.widget;

				return 'Widget DONE';
			  default:
				return '';
			}
		};

		xhr_widget.onreadystatechange = function () {
			console.log('Inside the onreadystatechange event with readyState: ' + toReadyStateDescriptionWidget(xhr_widget.readyState));
		};
	
	}

});
</script>
";
	
	
echo "
<script type='text/javascript'>
	jQuery('document').ready(function($){
		
		if (document.querySelector('#wee_prod') !== null) {
			
			var xhr_product = new XMLHttpRequest();
			xhr_product.open('GET', 'https://weecomments.com/".$lang."/webservice/show_product_v2?callback=lol&seo=0&id_shop=".$weecomments_options['WEE_ID_SHOP']."&id_product=".get_the_ID()."&lang=".$lang."&css=0', true);
			xhr_product.send(null);
			
			toReadyStateDescriptionProduct = function (state_product){
				switch (state_product) {
				  case 4:
					var string = xhr_product.responseText;
					var jsonObject = string.substring(4, string.length - 1);	

					var data = (typeof jsonObject == 'object' ? jsonObject : JSON.parse(jsonObject));
					/*console.log(data.widget);*/
					document.getElementById('wee_prod').innerHTML = data.widget;

					return 'Product DONE';
				  default:
					return '';
				}
			};
			
			xhr_product.onreadystatechange = function () {
				console.log('Inside the onreadystatechange event with readyState: ' + toReadyStateDescriptionProduct(xhr_product.readyState));
			};
		
		}
		
	});
</script>
";

echo '</script><script type="text/javascript" src="https://weecomments.com/js/widget-product-wordpress.js"></script>';
	
	
}



//PRODUCT EXTRA RIGHT
add_filter( 'woocommerce_single_product_summary', 'wee_product_extra_right', 36 );
function wee_product_extra_right()
{
    $weecomments_options = get_option('weecomments_options');
    global $post;
    $id_product = $post->ID;
    $product_info = wee_getProduct($id_product);
    $WEE_RATING_TYPE = $weecomments_options['WEE_RATING_TYPE'];
    include 'views/extra_right.php';
}


//Rewrite Reviews Tab
add_filter( 'woocommerce_product_tabs', 'woo_custom_reviews_tab', 120 );
function woo_custom_reviews_tab( $tabs )
{
	$tabs['reviews']['callback'] = 'wee_custom_reviews_tab_content';	// Custom description callback
	$tabs['reviews']['title'] = __( 'Opiniones' );				// Rename the reviews tab
	return $tabs;
}
 
function wee_custom_reviews_tab_content()
{
    $weecomments_options = get_option('weecomments_options');
    global $post;
    $id_product = $post->ID;
    $product_info = wee_getProduct($id_product);
    $WEE_RATING_TYPE = $weecomments_options['WEE_RATING_TYPE'];
    include 'views/product_comments.php';
	//echo '<div id="wee_prod"></div>	';
}




add_filter( 'woocommerce_after_shop_loop_item_title', 'wee_product_list', 5 );
function wee_product_list()
{
    global $post;
    $id_product = $post->ID;
    $product_info = wee_getProduct($id_product);
    include 'views/product_list.php';
}
     



	
	
add_action( 'widgets_init', create_function('', 'return wee_register_widgets();') );
function wee_register_widgets()
{
	include_once('widgets/class-wee-widget.php');
	register_widget( 'wee_Widget' );
}

add_action('admin_menu', 'wee_plugin_admin_add_page');
function wee_plugin_admin_add_page()
{
	add_menu_page('Custom Plugin Page', 'weeComments', 'manage_options', 'plugin', 'wee_plugin_configuration_page', plugin_dir_url( __FILE__ ) . '/icon.png');
}

//PÁGINA DE CONFIGURACIÓN DEL PLUGIN
function wee_plugin_configuration_page()
{
	global $wpdb;
	$message = '';
    	
	if (isset($_POST['WEE_API_KEY']))
		$message = wee_updateShopInfo();
	$weecomments_options = get_option('weecomments_options');

	if ($message)
		echo '<h2 class="green">'.$message.'</h2>';
	
	if ($weecomments_options['WEE_API_KEY']) {
		switch ($weecomments_options['WEE_SUBSCRIPTION']) {
			case 2:
				$subscription = 'weeComments Pro';
				break;
			case 3:
				$subscription = 'weeComments Premium';
				break;
			default:
				$subscription = 'weeComments Free';
				break;
		}
		include 'views/settings.php';
        
	} else
		include 'views/login.php';

}


/* Query Vars */
add_filter( 'query_vars', 'weecomments_register_query_var' );
function weecomments_register_query_var( $vars )
{
    $vars[] = 'weecomments_page';
    return $vars;
}

/* Template Include MAILER */
add_filter('template_include', 'weecomments_template_include', 1, 1);
function weecomments_template_include($template)
{
    global $wp_query;
    //$weecomments_page_value = $wp_query->query_vars['weecomments_page'];
    if (isset($_GET['weecomments_page']) && isset($_GET['weecomments_page']) == "mailer") {
		require_once(plugin_dir_path(__FILE__).'classes/wee_mailerClass.php');
		$mailerClass = new wee_mailerClass();
		$result_send = $mailerClass->wee_executeWeecomments();
		echo $result_send;
        return plugin_dir_path(__FILE__).'views/test.php';
    }

    return $template;
}


	function wee_updateShopInfo()
	{
        if (isset($_POST['WEE_API_KEY']) && (strlen($_POST['WEE_API_KEY']) == 32 || strlen($_POST['WEE_API_KEY']) == 64)) {
            $shop_info = wee_getWebPage('http://weecomments.com/wsrest/module_shop_info?api='.$_POST['WEE_API_KEY']);
            $shop_info = new SimpleXMLElement($shop_info);
            $api_key = $_POST['WEE_API_KEY'];
        }
        
		//Si el usuario y pass son correctos, actualiza los datos de la tienda
		if ($shop_info->id_shop > 0) {
			$weecomments_options['WEE_API_KEY'] 		= $api_key;
			$weecomments_options['WEE_ID_SHOP'] 		= trim($shop_info->id_shop);
			$weecomments_options['WEE_URL'] 			= trim($shop_info->friendly_url);
			$weecomments_options['WEE_SECURITY_KEY'] 	= trim($shop_info->security_key);
			$weecomments_options['WEE_SUBSCRIPTION'] 	= trim($shop_info->subscription);
			$weecomments_options['WEE_SUBJECT'] 		= trim($shop_info->mail_subject);
			$weecomments_options['WEE_TEXT'] 			= trim($shop_info->mail_text);
			$weecomments_options['WEE_EMAIL'] 			= trim($shop_info->email_reply);
			$weecomments_options['WEE_MAIL_FROM'] 		= trim($shop_info->mail_from);
			$weecomments_options['WEE_MAIL_TO'] 		= trim($shop_info->mail_to);
			$weecomments_options['WEE_MAIL_LIMIT'] 		= trim($shop_info->mail_limit);
			$weecomments_options['WEE_SHOP_AVG_RATING'] = round((float)$shop_info->avg_rating * 2, 2);
			$weecomments_options['WEE_SHOP_NUM_RATINGS'] = trim($shop_info->num_ratings);
			$weecomments_options['WEE_LANG'] 			= trim($shop_info->default_language);
            $weecomments_options['WEE_LOGO_URL'] 		= trim($shop_info->logo_url);
            $weecomments_options['WEE_RATING_TYPE'] 	= trim($shop_info->rating_type);
            $weecomments_options['WEE_SEND_MAIL_FROM'] 	= trim($shop_info->send_mail_from_wee);
            $weecomments_options['WEE_COLOUR'] 	        = trim($shop_info->colour);
            $weecomments_options['WEE_COLOUR2']         = trim($shop_info->colour2);
            $weecomments_options['WEE_MAIL_HEADER'] 	= trim($shop_info->mail_header);
            $weecomments_options['WEE_MAIL_BUTTON'] 	= trim($shop_info->mail_button);
            $weecomments_options['WEE_MAIL_LOGOHIDDEN'] 	= trim($shop_info->logo_hidden);
			update_option('weecomments_options', $weecomments_options);
			$message = 'Successful';
            
            $plugin_data = get_plugin_data( __FILE__ );
            $plugin_version = $plugin_data['Version'];
            
            //$this->wee_getWebPage('http://weecomments.com/wsrest/marketplace_new_register?api='.$api_key.'&version='.$plugin_version.'&cms=wordpress');
            
		} else {
            
			$weecomments_options['WEE_API_KEY'] = '';
			$weecomments_options['WEE_ID_SHOP'] = '';
			$weecomments_options['WEE_URL'] = '';
			$weecomments_options['WEE_SECURITY_KEY'] = '';
			$weecomments_options['WEE_SUBSCRIPTION'] = '';
			$weecomments_options['WEE_SUBJECT'] = '';
			$weecomments_options['WEE_TEXT'] = '';
			$weecomments_options['WEE_EMAIL'] = '';
			$weecomments_options['WEE_MAIL_FROM'] = '';
			$weecomments_options['WEE_MAIL_TO'] = '';
			$weecomments_options['WEE_MAIL_LIMIT'] = '';
			$weecomments_options['WEE_SHOP_AVG_RATING'] = '';
			$weecomments_options['WEE_SHOP_NUM_RATINGS'] = '';
			$weecomments_options['WEE_LANG'] = '';
            $weecomments_options['WEE_LOGO_URL'] = '';
            $weecomments_options['WEE_RATING_TYPE'] = '';
            $weecomments_options['WEE_SEND_MAIL_FROM'] 	= 0;
            $weecomments_options['WEE_COLOUR'] 	        = "#42BEB3";
            $weecomments_options['WEE_COLOUR2']         = "#42BEB3";
            $weecomments_options['WEE_MAIL_HEADER'] 	= 0;
            $weecomments_options['WEE_MAIL_BUTTON'] 	= "Enviar";
            $weecomments_options['WEE_MAIL_LOGOHIDDEN'] 	= 0;
			update_option('weecomments_options', $weecomments_options); 
			$message = 'email or password incorrect';
		}
		return $message;
	}

	function wee_createWeecommentsDatabase()
	{
		global $wpdb;
		$structure2 = "CREATE TABLE IF NOT EXISTS `wee_products` (
		  `id_product` BIGINT(20) NOT NULL,
		  `num_ratings` INT(10) NOT NULL,
		  `avg_rating` FLOAT(5) NOT NULL,
		  PRIMARY KEY (`id_product`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$wpdb->query($structure2);
		
		$structure3 = 'CREATE TABLE IF NOT EXISTS `wee_categories` (
		  `id_category` BIGINT(20) NOT NULL,
		  `num_ratings` INT(10) NOT NULL,
		  `avg_rating` FLOAT(5) NOT NULL,
		  PRIMARY KEY (`id_category`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
		$wpdb->query($structure3);
        
        $structure4 = "
        CREATE TABLE IF NOT EXISTS `wee_comments`(
              `id_product` BIGINT(20) NOT NULL,
              `id_comment` BIGINT(20) NOT NULL,
              `id_order` VARCHAR(11) NOT NULL,
              `id_shop` BIGINT(20) NOT NULL,
              `customer_name` VARCHAR(30) NOT NULL,
              `customer_lastname` VARCHAR(50) NOT NULL,
              `email` VARCHAR(100) NOT NULL,
              `IP` VARCHAR(45) NOT NULL,
              `date` DATETIME NOT NULL,
              `comment` VARCHAR(5000) NOT NULL,
              `rating` FLOAT(5) NOT NULL,
              `rating1` INT(1) NOT NULL,
              `rating2` INT(1) NOT NULL,
              `rating3` INT(1) NOT NULL,
              `status` INT(1) NOT NULL,
              `lang` VARCHAR(3) NOT NULL,
              `external` VARCHAR(20) NOT NULL,
              PRIMARY KEY (`id_product`, `id_comment`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        $wpdb->query($structure4);
        
        
        $structure5 = "
        CREATE TABLE IF NOT EXISTS `wee_comments_replies` (
        `id_reply` BIGINT(20) NOT null,
        `id_comment` BIGINT(20) NOT null,
        `reply` VARCHAR(5000) NOT null,
        `date` DATETIME NOT null,
        PRIMARY KEY (`id_reply`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        $wpdb->query($structure5);
        
        
	}
	
	function wee_getProduct($id_product)
	{	
		global $wpdb;
		$sql = "SELECT * FROM wee_products WHERE id_product = '$id_product'";
		$result = $wpdb->get_row($sql);
		if ($result)
			return $result;
		else
			return NULL;
	}



add_shortcode("weecomments_wide","weecomments_function_wide");
function weecomments_function_wide() {
    $weecomments_options = get_option('weecomments_options');
        
    if (ICL_LANGUAGE_CODE && strlen(ICL_LANGUAGE_CODE) >= 2 && ICL_LANGUAGE_CODE != 'ICL_LANGUAGE_CODE') {
        $lang = substr(ICL_LANGUAGE_CODE, 0, 2);
    } else {
        $lang = $weecomments_options['WEE_LANG'];
    }
    
    return '<div class="weecomments-widget-wide"></div>
    <script type="text/javascript">var $ = jQuery.noConflict();jQuery("document").ready(function($){$.ajax({type:"GET",url:"https://weecomments.com/'.$lang.'/webservice/show_widget_wide?callback=lol&id_shop='.$weecomments_options['WEE_ID_SHOP'].'&widget=2&css=0", dataType: "jsonp",success: function(resp){$(".weecomments-widget-wide").html(resp["widget"]);},error: function(e){}});});</script>
    <p class="wee_align_center"><small><a target="_blank" href="https://weecomments.com/'.$lang.'">reviews by <span class="wee_colour">weeComments</span></a></small></p>';
}

add_shortcode("weecomments_float","weecomments_function_float");
function weecomments_function_float() {
    $weecomments_options = get_option('weecomments_options');
    
    if (ICL_LANGUAGE_CODE && strlen(ICL_LANGUAGE_CODE) >= 2 && ICL_LANGUAGE_CODE != 'ICL_LANGUAGE_CODE') {
        $lang = substr(ICL_LANGUAGE_CODE, 0, 2);
    } else {
        $lang = $weecomments_options['WEE_LANG'];
    }
    
    return '<div class="weecomments_float"></div><script type="text/javascript">var $ = jQuery.noConflict();jQuery("document").ready(function($){$.ajax({type:"GET",url:"https://weecomments.com/'.$lang.'/webservice/show_widget_float?callback=lol&id_shop='.$weecomments_options['WEE_ID_SHOP'].'&widget=1&left=100&css=0",dataType:"jsonp",success: function(resp){$(".weecomments_float").html(resp["widget"]);},error: function(e){}});$(".weecomments_float").delegate("#wee-floating-1","hover",function(event){if(event.type=="mouseenter") {$("#wee-floating-1").filter(":not(:animated)").animate({bottom: "0px"}, 800, function(){});}else{$("#wee-floating-1").animate({bottom:"-202px"},400,function(){});}});});</script>';
}

add_shortcode("weecomments_general","weecomments_function_general");
function weecomments_function_general() {
    $weecomments_options = get_option('weecomments_options');
    
    if (ICL_LANGUAGE_CODE && strlen(ICL_LANGUAGE_CODE) >= 2 && ICL_LANGUAGE_CODE != 'ICL_LANGUAGE_CODE') {
        $lang = substr(ICL_LANGUAGE_CODE, 0, 2);
    } else {
        $lang = $weecomments_options['WEE_LANG'];
    }
    
    return '<a target="_blank" href="https://weecomments.com/'.$lang.'/reviews/'.$weecomments_options['WEE_URL'].'"><div class="weecomments"></div></a><script type="text/javascript">var $ = jQuery.noConflict();jQuery("document").ready(function($){$.ajax({type:"GET",url:"https://weecomments.com/'.$lang.'/webservice/show_small?callback=lol&id_shop='.$weecomments_options['WEE_ID_SHOP'].'&css=0", dataType: "jsonp",success: function(resp){$(".weecomments").html(resp["widget"]);},error: function(e){}});});</script><p class="wee_align_center"><small><a target="_blank" href="https://weecomments.com/'.$lang.'">reviews by <span class="wee_colour">weeComments</span></a></small></p>';
}

function wee_getWebPage($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => false,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 2,      // timeout on connect
        CURLOPT_TIMEOUT        => 10,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    curl_close($ch);

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $content;
}
	

?>