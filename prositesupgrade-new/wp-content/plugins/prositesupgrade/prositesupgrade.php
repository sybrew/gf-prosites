<?php 
/*
 * Plugin Name: GForms ProSite Upgrade/Downgrade/Extend
 * Plugin URI: https://hostmijnpagina.nl/
 * Description: Allows your users to upgrade, downgrade or extend their chosen website using Pro Sites and Gravity Forms 
 * Author: Sybre Waaijer
 * Version: 1.1.0
 * Author URI: https://cyberwire.nl/
 */

/* Remove comments from here if desired (not recommended for updates). Keep the above comments so you're able to find the plugin in your plugin page */
/* IMPORTANT NOTE: BEFORE YOU ACTIVATE THIS PLUGIN BE SURE TO READ THOROUGHLY THROUGH THIS FILE AND UPLOAD/CREATE YOUR FORM FIRST */
/* ANOTHER (MORE) IMPORTANT NOTE: DO NOT TEST THIS FORM OUT ON A SUPER ADMIN ACCOUNT (although I have built in a safety feature for this, please don't test it for your own sake) */

/* Have a great day everyone! Got a question? Post it here: http://premium.wpmudev.org/forums/topic/pro-sites-extending-through-gforms-how-to */

/*	UPDATES 
	
	1.1.0: Added blog meta to determine previous payment for better discounts
	1.0.1: commas, comma, dots and dots are now all valid.

*/

/* BASIC HIERARCHY OF THIS PAGE:
	
	Every line WITHOUT a comment defaults to this: DO NOT ALTER UNLESS YOU KNOW WHAT YOU'RE DOING.
	
	Sections:
	1. The shortcode for calling the form.
		1. Change the shortcode to your likings :)
		2. MUST EDIT: Change $myformid on line 73
		
	2. Fill in the (hidden) form contents
		What you need to do:
			1. MUST EDIT $myform_id to your upgrade form's ID on line 125
			2. MUST EDIT line 132 to your currency symbol
			3. MUST EDIT line 133 to comma or dot
			4. MUST EDIT 135 to 148 (fill in your prices with DOTS) 
			
		If you have made your own form: 
			2. Edit $levelfield_id to the field for your user's pro site level 
			3. Edit $leveltimefield_id to get the users time right
			5. Edit the extend_level_1,2,3 to the price fields for the 3 different pro levels.
			6. Allow all the above fields to by dynamically filled in (advanced option in Gravity Forms fields)
			7. Edit the level prices (currency gets handled by gforms)
			
			I could've used CSS classes instead of field ID's so you would only have to change a lot of stuff within Gravity Forms. 
			But I already have predefined CSS classes so this accounts for a little more editing by you here instead of editing in gravity forms <3.
			
	3. Form submission
		If you've made your own form:
			1. edit those lines D: good luck.

*/

/*	1. - The shortcode for calling the form */

/* USE THE FOLLOWING SHORTCODE ON /premium-site/ PAGE: [upgrade-prosite-page] */
/* USE THE FOLLOWING SHORTCODE ON /pro-sites/ PAGE: [redirect-to-gforms-upgrade] */

add_action( 'init', 'add_prosite_upgrade_shortcodes' );
function add_prosite_upgrade_shortcodes() {
	add_shortcode( 'upgrade-prosite-page', 'add_prosite_upgrade_shortcode');
	add_shortcode( 'redirect-to-gforms-upgrade', 'add_prosite_redirect_shortcode');
}

function add_prosite_redirect_shortcode() {
	if (!is_user_logged_in() ) {
		wp_redirect(home_url()); exit;
	} else if (!is_super_admin()) {
		wp_redirect(home_url('/premium-site/'));  /* change to your gform upgrade page */
	/*	wp_redirect(home_url('/examplepage/')); */
		exit;
	}
}

function add_prosite_upgrade_shortcode() {
	global $wpdb,$psts;
	if ( is_user_logged_in() ) {
		$myformid = '10'; /* change to your form ID, this has to be done again at (2.) below */
	
		$user_arg = wp_get_current_user();
		$user_id = $user_arg->ID;
		$user_blog_id = get_user_meta($user_id, 'primary_blog', true);
		$user_is_admin = current_user_can_for_blog($user_blog_id, 'edit_pages');
		
		$protimesql = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$max_1_year = time() + 63115200; /* 2 years in seconds, the maximum allowed time that a user is able to edit his subscription - NOTE: this will redirect the user after form submission if his subscription ends beyond 2 years */
		/* $max_1_year = 9999999999; */ /*Uncomment this if you basically want people to extend forever, lol (the line above is allowed to stay, this will overwrite it - might throw a notice through) */
		
		if ($protimesql < $max_1_year || is_super_admin() && $user_is_admin) {			
			wp_enqueue_style( 'pro-gform-styles', plugins_url( 'css/pro-upgrade.css', __FILE__ ), false, '1.1.0' );
			
			$mapped_domain = $wpdb->get_var($wpdb->prepare("SELECT domain FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $user_blog_id));
			$user_blog_url = get_blogaddress_by_id( $user_blog_id );
			$prolevelsql = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
			$prositelevelname = $psts->get_level_setting( $psts->get_level( $user_blog_id ), 'name' );
		
			$striphttp = is_ssl() ? 7 : 6; /* Missing a letter in the title? Change the 7 to 6 */ 
			$user_mapped_url = !empty($mapped_domain) ? $mapped_domain : strstr(substr($user_blog_url, $striphttp), '/', true);
	
			if ($prolevelsql == 0) {
				$prositeleveltime = "Undetermined";
			} else {
				$prositeleveltime = date_i18n('d F, Y', $protimesql); /* exchange d and F if you're a weird American, example: ('F d, Y', $protimesql) */ 
			}
			
			echo '<h2 class="upgradetitle">Upgrade Site ' . $user_mapped_url . '</h2>'; /* Change the title if you'd like, '<h2 ... Site ' and '</h2>' allows HTML changes, keep the '' */
			echo '<ul class="prositesupgrade">';
			echo '<li class="prositecurrentlevel">Current Pro Site level: ' . $prositelevelname . '</li>'; /* Change the title if you'd like, '<li ... vel: ' and '</li>' allows HTML changes, keep the '' */
			echo '<li class="prositecurrenttime">Expires on: ' . $prositeleveltime . '</li>'; /* Change the title if you'd like, '<li on: ' and '</li>' allows HTML changes, keep the '' */
			echo '</ul>';
			echo do_shortcode( '[gravityform id="' . $myformid . '" name="Upgrade" title="true" description="false"]' );
		} else {
			wp_redirect(home_url()); exit;
		}
	} else {
		wp_redirect(home_url()); exit;
	}
}

/*	2. - Fill in the (hidden) form contents */

add_filter("gform_field_input", "prepopulate_the_fields", 10, 5);
function prepopulate_the_fields($input, $field, $value, $lead_id, $form_id){
	global $wpdb;
	
	/*	Failure of putting in the correct form and field ID's might destroy your form and will force you to make a new form
		I suggest you use my attached form data file to prevent this from happening */
	/* NOTE: YOU STILL NEED TO CHANGE THE CORRECT FORM ID in $myform_id */

	$myform_id = '10'; /* Change to your form ID */
	
	if ($form_id == $myform_id) {
		$levelfield_id = '2'; /* Change to your Field ID for user's pro site level - We need this to determine the extend/upgrade/downgrade capabilities */
		
		/* We use a price field (shipping) so the user cannot change its price in F12 - This is why you need to add a currency symbol */
		$discountfield_id = '34'; /* Change to your Shipping Field ID - We need this to output the discount */
		$currency_type = '€'; /* Change this to the currency sign your site is using, html isn't allowed. Grab them here for example: € $ £ ¥ */
		$currency_comma_or_dot = 'comma'; /* change to 'dot' if your currency decimals are after a dot */
		
		/* I call this level Basic in the example form (lowest paid subscription) */
		$level_1_price_month = '5.00'; /* Give a price for 1 month */
		$level_1_price_3months = '13.50'; /* Give a price for 3 months */
		$level_1_price_year = '48.00'; /* Give a price for a year */
		
		/* I call this level Pro in the example form (second paid subscription) */
		$level_2_price_month = '10.00'; /* Give a price for 1 month */
		$level_2_price_3months = '27.00'; /* Give a price for 3 months */
		$level_2_price_year = '96.00'; /* Give a price for a year */
		
		/* I call this level Business in the example form (third paid subscription) */
		$level_3_price_month = '20.00'; /* Give a price for 1 month */
		$level_3_price_3months = '54.00'; /* Give a price for 3 months */
		$level_3_price_year = '192.00'; /* Give a price for a year */
		
		/* STOP EDITING HERE - SCROLL DOWN FOR NEXT SECTION (3.) - if you wish you can change the decimal in $timeleftindaysflat (13 lines down)*/ 
		
		$user_arg = wp_get_current_user();
		$user_id = $user_arg->ID;
		$user_blog_id = get_user_meta($user_id, 'primary_blog', true);

		$currentprositelevel = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		$currentprositetime = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id));
		
		/* new since 1.1.0 */
		$pro_blog_option = 'pro_site_last_payment';
		$pro_last_payment = get_blog_option($user_blog_id, $pro_blog_option, false);
		
		/* Calculating the pro-time left in days: 1 decimals = every 8640 seconds (or 144 minutes) the price is bound to change. You could use hours and even seconds, but that will mean the price will change multiple times a day.
			(Changing this will confuse and might angry your customers) */
		$timeleftindays = ($currentprositetime - time()) / 86400;
		$timeleftindaysflat = sprintf("%.0f", $timeleftindays); /* change the 0 to the amount of decimals. 1 is 144 minutes, 2 is 14.4 minutes, 3 is 1.44 minute. */
		
		if (empty($pro_last_payment)) { /* deprecated */
			if (empty($currentprositelevel)) {
				$discount = 0;
			} else if ($currentprositelevel == 1) {
				$discount = sprintf("%.2f",(($level_1_price_year / 12) + ($level_1_price_3months / 3) + ($level_1_price_month))/91.3125*$timeleftindaysflat);
			} else if ($currentprositelevel == 2) {
				$discount = sprintf("%.2f",(($level_2_price_year / 12) + ($level_2_price_3months / 3) + ($level_2_price_month))/91.3125*$timeleftindaysflat);
			} else if ($currentprositelevel == 3) {
				$discount = sprintf("%.2f",(($level_3_price_year / 12) + ($level_3_price_3months / 3) + ($level_3_price_month))/91.3125*$timeleftindaysflat);
			}
		} else { /* new since 1.1.0 */
			if (empty($currentprositelevel)) {
				$discount = 0;
			} else if ($pro_last_payment == '1m1') {
				$discount = sprintf("%.2f",($level_1_price_month/30.4375*$timeleftindaysflat));
			} else if ($pro_last_payment == '3m1') {
				$discount = sprintf("%.2f",($level_1_price_3months/91.3125*$timeleftindaysflat));
			} else if ($pro_last_payment == '1y1') {
				$discount = sprintf("%.2f",($level_1_price_year/365.25*$timeleftindaysflat));
			} else if ($pro_last_payment == '1m2') {
				$discount = sprintf("%.2f",($level_2_price_month/30.4375*$timeleftindaysflat));
			} else if ($pro_last_payment == '3m2') {
				$discount = sprintf("%.2f",($level_2_price_3months/91.3125*$timeleftindaysflat));
			} else if ($pro_last_payment == '1y2') {
				$discount = sprintf("%.2f",($level_2_price_year/365.25*$timeleftindaysflat));
			} else if ($pro_last_payment == '1m3') {
				$discount = sprintf("%.2f",($level_3_price_month/30.4375*$timeleftindaysflat));
			} else if ($pro_last_payment == '3m3') {
				$discount = sprintf("%.2f",($level_3_price_3months/91.3125*$timeleftindaysflat));
			} else if ($pro_last_payment == '1y3') {
				$discount = sprintf("%.2f",($level_3_price_year/365.25*$timeleftindaysflat));
			}
		}
		
		/* cute faces time! */
		$cute_dot = '.';
		$cute_comma = ',';
		$cute_count = '1';
		
		if ($currency_comma_or_dot == 'comma' || $currency_comma_or_dot == 'commas') {
			$discountfinal = str_replace($cute_dot, $cute_comma, $discount, $cute_count);
		} else if ($currency_comma_or_dot == 'dot' || $currency_comma_or_dot == 'dots') {
			$discountfinal = $discount;
		} else {
			$discountfinal = $discount;
		}
		
		/* populating the fields */
		if ($field["id"] == $levelfield_id) {
			$input = '<input name="input_' . $levelfield_id . '" id="input_' . $myform_id . '_' . $levelfield_id . '" type="hidden"  class="gform_hidden" value="' . $currentprositelevel . '">';
		}
		if ($field["id"] == $discountfield_id) {
			$input = '<div class="ginput_container">
			<input type="hidden" name="input_' . $discountfield_id . '" value="-' . $discountfinal . ' ' . $currency_type . '" class="gform_hidden"></input>
			<span class="ginput_shipping_price" id="input_' . $myform_id . '_' . $discountfield_id . '">' . $discountfinal . ' ' . $currency_type . '</span>
			</div>';
		}
		return $input;
	}
}

/*	3. - Form submission */

add_action('gform_user_updated', 'upgrade_pro_site_level', 10, 4);
function upgrade_pro_site_level($site_id, $user_id, $entry, $config) {
	global $wpdb,$psts;
	
	$user_arg_upgrade = wp_get_current_user();
	$user_id_upgrade = $user_arg_upgrade->ID;
	$user_blog_id_upgrade = get_user_meta($user_id_upgrade, 'primary_blog', true);

	/* extending */
	$pstslevel =  strstr($entry['5'], '|', true); /* Extend subscription Basic */
	$getperiod =  strstr($entry['6'], '|', true); /* Extend Period Basic */
	
	$pstslevel .= strstr($entry['13'], '|', true); /* Extend subscription Pro */
	$getperiod .= strstr($entry['7'], '|', true); /* Extend Period Pro */
	
	$pstslevel .= strstr($entry['14'], '|', true); /* Extend subscription Business */
	$getperiod .= strstr($entry['8'], '|', true); /* Extend Period Business */
	
	$extendvar = $entry['6'] . $entry['7'] . $entry['8']; /* Replace these numbers according to the $pstsperiod above */
	
	/* upgrading */
	$pstslevel .= strstr($entry['9'], '|', true); /* Upgrade subscription from Free */
	$getperiod .= strstr($entry['10'], '|', true); /* Period Basic from Free */
	$getperiod .= strstr($entry['11'], '|', true); /* Period Pro from Free */
	$getperiod .= strstr($entry['12'], '|', true); /* Period Business from Free */
	
	$pstslevel .= strstr($entry['18'], '|', true); /* Upgrade subscription from Basic */
	$getperiod .= strstr($entry['21'], '|', true); /* Period Pro from Basic */
	$getperiod .= strstr($entry['22'], '|', true); /* Period Business from Basic */
	
	$pstslevel .= strstr($entry['19'], '|', true); /* Upgrade subscription from Pro */
	$getperiod .= strstr($entry['23'], '|', true); /* Period Business from Pro */
	
	/* downgrading */
	$pstslevel .= strstr($entry['27'], '|', true); /* Downgrade subscription from Pro */
	$getperiod .= strstr($entry['28'], '|', true); /* Period Basic from Pro */
	
	$pstslevel .= strstr($entry['29'], '|', true); /* Downgrade subscription from Business */
	$getperiod .= strstr($entry['30'], '|', true); /* Period Basic from Business */
	$getperiod .= strstr($entry['31'], '|', true); /* Period Pro from Business */
	
	/* STOP EDITING HERE - ENJOY */
	
	$extendprositetime = $wpdb->get_var($wpdb->prepare("SELECT expire FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id_upgrade));
	
	if ($getperiod == "termmonth") {
		$pststime = strtotime("+1 month");
		$pststimelastpayment = '1m'; /* new since 1.1.0 */
	} else if ($getperiod == "term3months") {
		$pststime = strtotime("+3 month");
		$pststimelastpayment = '3m'; /* new since 1.1.0 */
	} else if ($getperiod == "termyear") {
		$pststime = strtotime("+1 year");
		$pststimelastpayment = '1y'; /* new since 1.1.0 */
	} else {
		$pststime = time(); /* This shouldn't happen here */
	}
	
	if (!empty($extendvar)) {
		$pststime_extended = $pststime - time() + $extendprositetime;
	} else {
		$pststime_extended = $pststime;
	}
	
	if ($pststime_extended >= 9999999999) {
		$pststime_final = 9999999999;
	} else {
		$pststime_final = $pststime_extended;
	}
	
	/* new since 1.1.0 */
	$pro_blog_option_upgrade = 'pro_site_last_payment';
	$pro_last_payment = get_blog_option($user_blog_id_upgrade, $pro_blog_option_upgrade, false);

	/* upgraded option = termtime level */
	$upgraded_option = $pststimelastpayment . $pstslevel;
	
	if (empty($pro_last_payment)) {
		add_blog_option($user_blog_id_upgrade, $pro_blog_option_upgrade, $upgraded_option);
	} else {
		update_blog_option($user_blog_id_upgrade, $pro_blog_option_upgrade, $upgraded_option);
	}
	
	$pstsgateway = 'GformUpgrade';
	$pststerm = '';
	$pstsamount = '';
	
	$check_if_pro_site = $wpdb->get_var($wpdb->prepare("SELECT level FROM {$wpdb->base_prefix}pro_sites WHERE blog_ID = %d", $user_blog_id_upgrade));

	if(!empty($user_blog_id_upgrade)){
		if(!empty($check_if_pro_site)) {
			$update_level = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->base_prefix}pro_sites SET level = %d, expire = %d, gateway = %s, term = %s WHERE blog_ID = %d", $pstslevel, $pststime_final, $pstsgateway, $pststerm, $user_blog_id_upgrade));
		} else {
			$update_level = $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->base_prefix}pro_sites (blog_ID, level, expire, gateway, term) VALUES (%d, %d, %d, %s, %s)", $user_blog_id_upgrade, $pstslevel, $pststime_final, $pstsgateway, $pststerm)); 
		}
	}

	$prositeupgradename = $psts->get_level_setting( $psts->get_level( $user_blog_id_upgrade ), 'name' );
	
	$psts->record_stat($user_blog_id_upgrade, 'upgrade');
	if ($pststime_final == time()) {
		$psts->log_action($user_blog_id_upgrade, __("ERROR: GFormUpgrade changed Pro-Sites level to Level ID {$pstslevel}({$prositeupgradename}) with TIME OF SUBMISSION - This shouldn't happen and the user has paid to get a free account.") );	
	} else if ($pststime_final == 9999999999) {
		$psts->log_action($user_blog_id_upgrade, __("WARNING: GFormUpgrade FAILED to extend Pro-Sites level to Level ID {$pstslevel}({$prositeupgradename}) with time (UNIX): {$pststime_final} because the blog already has permanent status.") );	
	} else {
		$psts->log_action($user_blog_id_upgrade, __("SUCCESS: GFormUpgrade changed Pro-Sites level to Level ID {$pstslevel} ({$prositeupgradename}) until " . date_i18n('d F, Y', $pststime_final) . "with site option ({$upgraded_option}).") );
	}
}