<?php 
/*
 * Plugin Name: GForms ProSite Registration
 * Plugin URI: https://hostmijnpagina.nl/
 * Description: Allows your users to register their new using Pro Sites and Gravity Forms 
 * Author: Sybre Waaijer
 * Version: 1.2.0
 * Author URI: https://cyberwire.nl/
 */
 
 // sorry, no tutorial included - This one is also much simpler than the upgrade/extend/downgrade function as you can see :)
 // for users whom have installed this before, only line 43 has been updated to prevent SQL injections. Replace it with your own.
 // Also, switch_to_blog and restore_current_blog has been removed because they had no purpose.
 
 /*
  * Update 1.1.0 : Preparing for ProSite Upgrade/Downgrade/Extend
  * Update 1.2.0 : Fixed critical bug when the fields below are split over several pages.
  *
  */
 
//* Gforms Pro Sites Creation
function enter_pro_site_level($site_id, $user_id, $entry, $config, $user_pass) {
	global $wpdb,$psts;
	
	$pstslevel =  strstr($entry['12'], '|', true); // = Field ID that contains the level selection (0 = free, 1 = Pro level 1, 2 = Pro level 2, etc.)
	
	$getperiod = 	''; /* new since 1.2.0 */
	$getperiod .=  strstr($entry['17'], '|', true); /* changed 1.2.0 */ // = Field ID that contains the time selection for one of the 3 levels
	$getperiod .=  strstr($entry['18'], '|', true); // = Field ID that contains the time selection for one of the 3 levels
	$getperiod .=  strstr($entry['19'], '|', true); // = Field ID that contains the time selection for one of the 3 levels, add more if needed. Remove a line if needed.
	
	if (empty($getperiod)) { /* new since 1.2.0 */
		$pststime = time(); /* new since 1.2.0 */
		$pststimelastpaymenty = ''; /* new since 1.2.0 */
	} else if ($getperiod == "termmonth") { /* changed 1.2.0 */
		$pststime = strtotime("+1 month");
		$pststimelastpayment = '1m'; /* new since 1.1.0 */
	} else if ($getperiod == "term3months") {
		$pststime = strtotime("+3 month");
		$pststimelastpayment = '3m'; /* new since 1.1.0 */
	} else if ($getperiod == "termyear") {
		$pststime = strtotime("+1 year");
		$pststimelastpayment = '1y'; /* new since 1.1.0 */
	} else {
		$pststime = time();
	}
	
	/* new since 1.1.0 */
	/* upgraded option = termtime level */
	$upgraded_option = $pststimelastpayment . $pstslevel;
	
	$pro_blog_option_upgrade = 'pro_site_last_payment';
	add_blog_option($site_id, $pro_blog_option_upgrade, $upgraded_option);
	
	$pstsgateway = 'GFormRegister';
	$pststerm = '';
	$pstsamount = '';
	
	$nowplusonehour = time() + 3600; /* new since 1.2.0 */

	if(!empty($site_id)){
		if ( !empty($getperiod) || $pststime > $nowplusonehour ) { /* new since 1.2.0 */
			$update_level = $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->base_prefix}pro_sites (blog_ID, level, expire, gateway, term) VALUES (%d, %d, %d, %s, %s)", $site_id, $pstslevel, $pststime, $pstsgateway, $pststerm));
		}
	}

	$psts->record_stat($site_id, 'upgrade');
	if ( !empty($getperiod) || $pststime > $nowplusonehour ) { /* new since 1.2.0 */
		$psts->log_action($site_id, __("GFormsRegister changed Pro-Sites level to Level ID {$pstslevel} with site_option ({$upgraded_option}).") );
	}
}
add_action("gform_site_created", "enter_pro_site_level", 10, 5);