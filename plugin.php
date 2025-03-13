<?php
/*
Plugin Name: UmamiTelemetry
Plugin URI: https://github.com/DasDunkel/yourls-umami
Description: Implements privacy preserving telemetry using Umami
Version: 1.0.0
Author: Dunk
Author URI: https://dunk.dev
*/
// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Uncomment & edit below code to automatically add tracking script to all pages if the user is not logged in
//yourls_add_action( 'html_head_meta', 'add_umami_script');
//function add_umami_script( $context ) {
//	if (yourls_is_valid_user() !== true) {
//		echo '<script defer src="https://umami.example.com/script.js" data-website-id="WebsiteID"></script>';
//	}
//}

// Hook on basic redirect
yourls_add_action( 'redirect_shorturl', 'ping_umami' );
function ping_umami( $args ) {

	$umamiCollect = 'UMAMI_ENDPOINT'; // Must include path: https://umami.example.com/api/send
	$website = "WEBSITE_ID";
	
	// Collected info from original call
	$language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$ua = $_SERVER['HTTP_USER_AGENT'];
	$referrer = $_SERVER['HTTP_REFERER']; // 1x"r" vs 2x"r" !!!
	$ip = $_SERVER['REMOTE_ADDR'];
	$host = $_SERVER['HTTP_HOST'];
	$requestUrl = $_SERVER['REQUEST_URI'];
	
	// Set referrer to blank string if one wasn't provided
	if ($referrer == NULL) {
		$referrer = "";
	}

	$json_array = [
		'payload' => [
		  'hostname' => $host,
		  'website' => $website,
		  'url' => $requestUrl,
		  'language' => $language,
		  'referrer' => $referrer
		],
		'type' => 'event'
	];

	// Build curl command
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $umamiCollect);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
	  "Content-Type: application/json; charset=utf-8",
	  "Accept-Language: $language",
	  "User-Agent: $ua",
	  "Referer: $referrer"
	]);

	// Encode body and send to umami
	$body = json_encode($json_array);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_exec($ch);
	curl_close($ch);

}
?>
