<?php
/**
 * Facebook Return Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$state = get_input('state');

$user = elgg_get_logged_in_user_entity();

// @TODO check state
if ($state != $_SESSION['facebook_state']) {
	elgg_register_error('facebook:error:invalidstate');
	forward();
}

// @TODO make this prettier
if ($error = get_input('error')) {
	echo "Error: $error<br />";
	echo "Error Reason: " . get_input('error_reason');
	echo "Error Description: " . get_input('error_description');
	die;
} else {
	$code = get_input('code');

	// Start building token URL
	$oauth_token_url = "https://graph.facebook.com/oauth/access_token?";
 	
	// Needs to be IDENTICAL to the origin redirect
	$redirect_uri = $_SESSION['facebook_redirect'];

	// URL Parts
	$parts = array(
		'client_id' => elgg_get_plugin_setting('app_id', 'facebook'),
		'redirect_uri' => $redirect_uri,
		'client_secret' => elgg_get_plugin_setting('app_secret', 'facebook'),
		'code' => $code
	);

	// Comine parts and URL
	$oauth_token_url .= http_build_query($parts);
	
	// Fetch access token
	$response = curl_get_file_contents($oauth_token_url);
	
	// Check for response error (will be a json string in that case)
	$decoded_response = json_decode($response);
	if ($decoded_response->error) {
		register_error($decoded_response->error->message . $decoded_response->error->code);
	} else {
		$params = NULL;
		parse_str($response, $params);
		
		$user->facebook_account_connected = TRUE;
		$user->facebook_access_token = $params['access_token'];
		$user->facebook_access_token_expires = $params['expires'];
		
		$facebook = facebook_get_client();
	 	$user->facebook_account_id = $facebook->getUser();
		
		system_message(elgg_echo('facebook:success:connectedaccount'));
	}	
	forward(elgg_get_site_url() . 'facebook/settings');
}