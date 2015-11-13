<?php
/*
Plugin Name: CAS Auth
Plugin URI: http://vaibhavsharma.com
Description: Enables Single Sign-on using Yale CAS
Author: VaibhaV Sharma (vaibhav@vaibhavsharma.com)
Version: 0.1
Author URI: http://vaibhavsharma.com/

*/

add_action('wp_authenticate', 'cas_login');
add_action('wp_logout', 'cas_logout');

function cas_login() {
	include_once('CAS/CAS.php');

	global $wpdb;
	$redirect_to = 'wp-admin/';

	phpCAS::setDebug();

	// initialize phpCAS
	phpCAS::client(CAS_VERSION_2_0,'your-cas-server.com',443,'/cas');

	// force CAS authentication
	phpCAS::forceAuthentication();

	// At this point, the user has been authenticated by the CAS server
	// See if there is a matching wordpress username. If not, redirect to the login page.
	if($userdata = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE user_login = '". phpCAS::getUser() ."'"))
	{
		$username = phpCAS::getUser();
		$password = $userdata->user_pass;
		wp_setcookie($username, md5($password), true);
		do_action('wp_login', $user_login);
		wp_redirect($redirect_to);
	} 
	else {
		// Clear the cookie, just in case.
		wp_clearcookie();
		phpCAS::logout('http://some-logout-landing-page.com/wp-admin');
		wp_redirect($redirect_to);
	}
}

// This may or may not work well. Need to do some cookie magic in all apps for logout to work well.
function cas_logout() {
	include_once('CAS/CAS.php');

        // initialize phpCAS
        phpCAS::client(CAS_VERSION_2_0,'your-cas-server.com',443,'/cas');

	wp_clearcookie();
	phpCAS::logout('http://some-logout-landing-page.com');
	wp_redirect($redirect_to);
}

?>
