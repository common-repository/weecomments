<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div id="weecomments_back">

	<div id="wee_logo_container">
    
    	<img src="<?php echo plugins_url( 'img/logo-weecomments-new-v4.png', dirname(__FILE__) )?>" /><br />
        
        <h2><?php _e('Easy -> Professional -> Free', 'weecomments')?></h2>
        <h3><?php _e('Install & Configure weeComments in 5 minutes', 'weecomments')?><br />
        <a class="wee_button" target="_blank" href="http://weecomments.com/en">Try it for free</a></h3>
    

        <h2><?php _e('¿Ya tienes cuenta?', 'weecomments')?></h2>
        
        <form method="post">
            <!--
            <input type="text" size="30" name="WEE_USER" value="<?php if(isset($weecomments_options['WEE_USER']))echo $weecomments_options['WEE_USER']?>" placeholder="<?php _e('user', 'weecomments')?>" />
            <input type="password" size="30" name="WEE_PASS" value="<?php if(isset($weecomments_options['WEE_PASS']))echo $weecomments_options['WEE_PASS']?>" placeholder="<?php _e('pass', 'weecomments')?>" />
            -->
            <input type="text" size="30" name="WEE_API_KEY" value="<?php if(isset($weecomments_options['WEE_API_KEY']))echo $weecomments_options['WEE_API_KEY'] ?>" placeholder="API KEY"/>
            
            <input type="submit" name="submitConfiguration" value="<?php _e('log in', 'weecomments')?>" class="wee_button wee_button_small" />
            </form>
    
   </div>

</div><!--weecomments_back-->