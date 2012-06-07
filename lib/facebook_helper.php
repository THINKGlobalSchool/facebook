<?php
/**
 * Facebook Integration Helper Library
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

function facebook_test() {
	$params = array();

	elgg_load_library('elgg:facebook_sdk');
	
	$params['title'] = elgg_echo('facebook:label:facebooksettings');
	$params['content'] = $content;

	return $params;
}

/**
 * Build content for user settings
 */
function facebook_get_user_settings_content() {
	// Set the context to settings
	elgg_set_context('settings');

	elgg_set_page_owner_guid(elgg_get_logged_in_user_guid());
	$user = elgg_get_page_owner_entity();
	
 	$title = elgg_echo('facebook:label:facebooksettings');
	
	elgg_push_breadcrumb(elgg_echo('settings'), "settings/user/$user->username");
	elgg_push_breadcrumb($title);
	
	$content = elgg_view('facebook/settings', array('user' => $user));
	
	$params['title'] = $title;
	$params['content'] = $content;

	return $params;
}

/**
 * Grab a facebook client
 * 
 * @param ElggUser $user (Optional)
 * @return mixed
 */
function facebook_get_client($user = NULL) {
	elgg_load_library('elgg:facebook_sdk');

	if (!$user) {
		$user = elgg_get_logged_in_user_entity();
	}

	$access_token = $user->facebook_access_token;

	if (!$access_token) {
		return FALSE;
	}

	$appId = elgg_get_plugin_setting('app_id', 'facebook');
	$secret = elgg_get_plugin_setting('app_secret', 'facebook');

	$facebook = new Facebook(array(
	  'appId'  => $appId,
	  'secret' => $secret,
	));

	$facebook->setAccessToken($access_token);

	return $facebook;
}

/**
 * Wrapper method to post to a user's status
 * 
 * @param string   $message
 * @param ElggUser $user (Optional)
 * @return bool
 */
function facebook_post_user_status($message, $user = NULL) {
	$params['message'] = $message;

	$result = facebook_make_post($params, $user);

	if ($result['error']) {
		register_error(elgg_echo('facebook:error:statuspost', array($result['error'])));
	} else {
		return TRUE;
	}
}


/**
 * Make a facebook post to user's wall
 * 
 * @param array    $params
 * @param ElggUser $user (Optional)
 * @return mixed
 */
function facebook_make_post($params, $user = NULL) {
	try {
		$facebook = facebook_get_client($user);
		$ret_obj = $facebook->api('/me/feed', 'POST', $params);
		return TRUE;
	} catch (Exception $e) {
		return array(
			'error' => $e->getMessage(),
		);
	}
}

/**
 * Helper function to check for valid token
 * 
 * @param ElggUser $user
 * @return 
 */
function facebook_check_token($user = NULL) {
	if (!elgg_instanceof($user, 'user')) {
		$user = elgg_get_logged_in_user();
	}
	
	$access_token = $user->facebook_access_token;
	
	// Attempt to query the fb graph
	$graph_url = "https://graph.facebook.com/me?" . "access_token=" . $access_token;
	
	$response = curl_get_file_contents($graph_url);

	return $response;
}

/**
 * Helper function to exchange short lived token for a long lived token
 * 
 */
function facebook_get_extended_token($token, $user = NULL) {
	if (!elgg_instanceof($user, 'user')) {
		$user = elgg_get_logged_in_user_entity();
	}

	// Start building token URL
	$oauth_token_url = "https://graph.facebook.com/oauth/access_token?";

	// URL Parts
	$parts = array(
		'client_id' => elgg_get_plugin_setting('app_id', 'facebook'),
		'client_secret' => elgg_get_plugin_setting('app_secret', 'facebook'),
		'grant_type' => 'fb_exchange_token',
		'fb_exchange_token' => $token,
	);

	// Combine parts and URL
	$oauth_token_url .= http_build_query($parts);
	
	// Fetch access token
	$response = curl_get_file_contents($oauth_token_url);
	
	// Check for response error (will be a json string in that case)
	$decoded_response = json_decode($response);
	if ($decoded_response->error) {
		$params = array(
			'error' => $decoded_response->error->message . " ({$decoded_response->error->code})",
		);
	} else {
		$params = NULL;
		parse_str($response, $params);
	}
	
	return $params;
}

/**
 * Upload multiple photos to facebook
 * Note: batch requests are limited to 50
 *
 * @param Facebook  $facebook  facebook client
 * @param ElggBatch $photos    an ElggBatch of photos
 * @param string    $location  graph api location for photo upload (Default: /me/photos)
 * @return array
 */
function facebook_batch_upload_photos($facebook, $photos, $location = "/me/photos") {
	// Build facebook batch request
	$fb_batch = array();
	$fb_params = array();

	$count = 1;

	foreach ($photos as $photo) {
		if ($count > 50) {
			break; // If we pass more than 50 objects in, stop
		}
		
		// Get photo file name
		$large_thumb = new ElggFile();
		$large_thumb->owner_guid = $photo->owner_guid;
		$large_thumb->setFilename($photo->largethumb);
		$filename = $large_thumb->getFilenameOnFilestore();

		unset($large_thumb);
		
		// This image's request
		$req = array(
			'method' => 'POST',
			'relative_url' => $location,
			'attached_files' => 'file' . $count
		);
	
		// Add request to batch
		$fb_batch[] = json_encode($req);
		
		// Set filepath for uploaded image in params
		$fb_params['file' . $count] = '@' . realpath($filename);
		
		$count++;
	}

	$fb_params['batch'] = '[' . implode(',' ,$fb_batch) . ']';

	try {
		$result = $facebook->api('/','post', $fb_params);
	} catch(FacebookApiException $e) {
		$result = array('error' => $e->getMessage());	
	}
	
	return $result;
}

/**
 * Get the required scope string for our app
 * @return string
 */
function facebook_get_scope() {
	return 'user_status,publish_stream,user_photos,photo_upload';
}

// Helper function to circumvent PHP's strict handling of file_get_contents
function curl_get_file_contents($URL) {
	$c = curl_init();
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_URL, $URL);
	$contents = curl_exec($c);
	$err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
	curl_close($c);
	if ($contents) {
		return $contents;
	} else {
		return FALSE;
	}
}