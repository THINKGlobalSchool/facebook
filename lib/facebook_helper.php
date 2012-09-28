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
	if (!$user) {
		$user = elgg_get_logged_in_user_entity();
	}

	$access_token = $user->facebook_access_token;

	if (!$access_token) {
		return FALSE;
	}

	$appId = elgg_get_plugin_setting('app_id', 'facebook');
	$secret = elgg_get_plugin_setting('app_secret', 'facebook');

	$facebook = facebook_get_bare_client();

	$facebook->setAccessToken($access_token);

	return $facebook;
}

/**
 * Grab a bare facebook client for logins
 */
function facebook_get_bare_client() {
	elgg_load_library('elgg:facebook_sdk');
	
	$appId = elgg_get_plugin_setting('app_id', 'facebook');
	$secret = elgg_get_plugin_setting('app_secret', 'facebook');
	
	$facebook = new Facebook(array(
	  'appId'  => $appId,
	  'secret' => $secret,
	));
	
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

	$result = facebook_make_post($params, 'me', $user);

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
function facebook_make_post($params = array(), $location = 'me', $user = NULL) {
	try {
		$facebook = facebook_get_client($user);
		$ret_obj = $facebook->api("/{$location}/feed", 'POST', $params);
		return TRUE;
	} catch (Exception $e) {
		return array(
			'error' => $e->getMessage(),
		);
	}
}

/**
 * Make a facebook post to the admin page wall
 * 
 * @param array    $params
 * @param ElggUser $user (Optional)
 * @return mixed
 */
function facebook_make_page_post($params = array(), $user = NULL) {
	// Get the page
	$page = facebook_get_admin_page($user);
	
	if (!$page) {
		register_error(elgg_echo('facebook:error:admin_page'));
		return FALSE;
	}

	// Set page access token
	$params['access_token'] = $page['access_token'];

	$result = facebook_make_post($params, $page['id'], $user);

	if ($result['error']) {
		register_error(elgg_echo('facebook:error:statuspost', array($result['error'])));
	} else {
		return TRUE;
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
 * @param array     $fb_params extra parameters
 * @return array
 */
function facebook_batch_upload_photos($facebook, $photos, $location = "/me/photos", $fb_params = array()) {
	// Build facebook batch request
	$fb_batch = array();

	$count = 1;

	foreach ($photos as $photo) {
		if ($count > 10) {
			break; // If we pass more than 10 objects in, stop
		}
		
		// Get photo file name
		$large_thumb = new ElggFile();
		$large_thumb->owner_guid = $photo->owner_guid;
		$large_thumb->setFilename($photo->largethumb);
		$filename = $large_thumb->getFilenameOnFilestore();

		unset($large_thumb);

		if (strpos($filename,'largethumb') === FALSE) {
			continue;
		}

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
 * Get a list of pages the user can administer
 * 
 * @param ElggUser $user
 * @return array
 */
function facebook_get_pages($user = NULL) {
	try {
		$facebook = facebook_get_client($user);
		$ret_obj = $facebook->api('/me/accounts', 'GET');
		return $ret_obj;
	} catch (Exception $e) {
		return array(
			'error' => $e->getMessage(),
		);
	}
}


/**
 * Determine if user can post as the admin set page (See plugin settings)
 * 
 * @param ElggUser $user
 * @return mixed
 */
function facebook_get_admin_page($user = NULL) {
	$pages = facebook_get_pages($user);
	if ($pages['error']) {
		error_log($pages['error']);
		return FALSE;
	}
	
	$admin_page = elgg_get_plugin_setting('admin_page', 'facebook');
	
	foreach ($pages['data'] as $page) {
		// If this page id matches ours, and we have create access
		if ($page['id'] == $admin_page && in_array('CREATE_CONTENT', $page['perms'])) {
			return $page;
		}
	}
	
	return FALSE;
}

/**
 * Helper function to determine if given user
 * can publish to admin fb page
 * 
 * @param ElggUser $user The user
 * @return bool
 */
function facebook_can_page_publish($user = NULL) {
	if (elgg_is_admin_logged_in()) {
		return true;
	}
	
	if (!elgg_instanceof($user, 'user')) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	$role = get_entity(elgg_get_plugin_setting('admin_page_role', 'facebook'));
	
	if (elgg_instanceof($role, 'object', 'role') && $role->isMember($user)) {
		return true;
	} 

	return false;
}

/**
 * Get the required scope string for our app
 * @return string
 */
function facebook_get_scope() {
	return 'email,user_status,publish_stream,user_photos,photo_upload,manage_pages';
}

/**
 * Helper function to parse and prepare encoded text for posting to facebook
 */
function facebook_decode_text($text) {
	$text = strip_tags($text);
	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	return $text;
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

/**
 * Determine if we can use facebook to login
 * 
 * @return bool
 */
function facebook_can_login() {
	if (!$key = elgg_get_plugin_setting('app_id', 'facebook')) {
		return FALSE;
	}
	
	if (!$secret = elgg_get_plugin_setting('app_secret', 'facebook')) {
		return FALSE;
	}
	
	return elgg_get_plugin_setting('login_enabled', 'facebook');
}

/**
 * Get facebook login URL
 * 
 * @return string
 */
function facebook_get_authorize_url($next='') {
	global $CONFIG;
	
	if (!$next) {
		// default to login page
		$next = "{$CONFIG->site->url}facebook/login";
	}
	
	$facebook = facebook_get_bare_client();
	$url = $facebook->getLoginUrl(array(
		'redirect_uri' => $next,
		'scope' => facebook_get_scope(),
	));
	return $url;
}

function facebook_login() {
	global $CONFIG;
	
	// sanity check
	if (!facebook_can_login()) {
		forward();
	}
	$facebook = NULL;
	$facebook = facebook_get_bare_client();

	$fb_user_id = $facebook->getUser();

	/* attempt to find user */
	$options = array(
		'type' => 'user',
		'metadata_name' => 'facebook_account_id',
		'metadata_value' => $fb_user_id,
	);
	
	$users = elgg_get_entities_from_metadata($options);

	if (!$users) {
		$data = $facebook->api('/me');

		// check new registration allowed
		if (!$CONFIG->allow_registration) {
			register_error(elgg_echo('registerdisabled'));
			forward();
		}

		// trigger a hook for plugin authors to intercept
		if (!trigger_plugin_hook('new_facebook_user', 'facebook', array('account' => $data), TRUE)) {
			// halt execution
			register_error(elgg_echo('facebook:login:error'));
			forward();
		}
		
		$username = substr(parse_url($data['link'], PHP_URL_PATH), 1) . '_fb';
		$password = generate_random_cleartext_password();

		try {			
			// create new account
			if (!$user_id = register_user($username, $password, $data['name'], $data['email'])) {
				register_error(elgg_echo('registerbad'));
				forward();
			}
		} catch (RegistrationException $r) {
			register_error($r->getMessage());
			forward();
		}
		
		$user = new ElggUser($user_id);

		// pull in Facebook icon
	//	if (! facebookservice_update_user_avatar($user, "https://graph.facebook.com/{$data['id']}/picture?type=large")) {
	//		register_error(elgg_echo('facebookservice:avatar:error'));
	//	}
		
		$user->facebook_account_connected = TRUE;		
		$user->facebook_access_token = $facebook->getAccessToken();
		$user->facebook_account_id = $fb_user_id;
		
		$user->save();

		system_message(elgg_echo('facebook:login:new'));
		login($user);
		forward();	
	} elseif (count($users) == 1) {
		// Got a user, log them in
		login($users[0]);
		system_message(elgg_echo('facebook:login:success'));
		forward();
	}
	
	// register login error
	register_error(elgg_echo('facebook:login:error'));
	forward();
}