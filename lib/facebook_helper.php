<?php
/**
 * Facebook Integration Helper Library
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org/
 * 
 */

//define('FACEBOOK_SDK_V4_SRC_DIR',  elgg_get_plugins_path() . 'facebook/vendors/facebook-php-sdk/src/Facebook/');
require elgg_get_plugins_path() . 'facebook/vendors/facebook-php-sdk/autoload.php';

$appId = elgg_get_plugin_setting('app_id', 'facebook');
$secret = elgg_get_plugin_setting('app_secret', 'facebook');

use Facebook\Entities;
use Facebook\Entities\AccessToken;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\GraphUserPage;
use Facebook\FacebookRequestException;

FacebookSession::setDefaultApplication($appId, $secret);


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
 * Get a Facebook session object from a local user entity
 * 
 * @param  ElggUser                       $user
 * @return Facebook\FacebookSession|FALSE
 */
function facebook_get_session_from_user($user) {
	if (!$user->facebook_access_token) {
		return FALSE;
	}

	$access_token = new AccessToken($user->facebook_access_token);
	$session = new FacebookSession($access_token);

	if ($session) {
		return $session;
	} else {
		return FALSE;
	}
}

/**
 * Get the facebook user entity (GraphUser) from a valid session
 *
 * @param Facebook\FacebookSession $session
 * @return Facebook\GraphUser
 */   
function facebook_get_graph_user_from_session($session) {
	try {
		$fb_user = (new FacebookRequest(
			$session, 'GET', '/me'
		))->execute()->getGraphObject(GraphUser::className());
	} catch (FacebookRequestException $e) {
		// The Graph API returned an error
		register_error($e->getMessage());
	} catch (Exception $e) {
		// Some other error occurred
		register_error($e->getMessage());
	}

	if ($fb_user) {
		return $fb_user;
	} else {
		return FALSE;
	}
}

/**
 * Disconnect user from facebook app
 * 
 * @param ElggUser $user
 * @return bool
 */
function facebook_disconnect_user($user) {
	$session = facebook_get_session_from_user($user);
	$fb_user = facebook_get_graph_user_from_session($session);

	if ($fb_user) {
		$request = new FacebookRequest($session, 'DELETE', '/me/permissions');
		return $request->execute();
	} else {
		return FALSE;
	}
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
		if (!$user) {
			$user = elgg_get_logged_in_user_entity();
		}
		$session = facebook_get_session_from_user($user);
		$fb_user = facebook_get_graph_user_from_session($session);

		$request = new FacebookRequest($session, 'POST', "/{$location}/feed", $params);
		$request->execute();
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
	$params['access_token'] = $page->getAccessToken();

	$result = facebook_make_post($params, $page->getId(), $user);

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
 * Upload multiple photos to facebook
 * Note: batch requests are limited to 50
 *
 * @param Facebook\FacebookSession  $session   facebook session
 * @param ElggBatch                 $photos    an ElggBatch of photos
 * @param string                    $location  graph api location for photo upload (Default: /me/photos)
 * @param array                     $fb_params extra parameters
 * @return array
 */
function facebook_batch_upload_photos($session, $photos, $location = "/me/photos", $fb_params = array()) {
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
		$request = new FacebookRequest($session, 'POST', "/", $fb_params);
		$result = $request->execute();
	} catch(Exception $e) {
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
		$session = facebook_get_session_from_user($user);
		$fb_user = facebook_get_graph_user_from_session($session);

		$request = new FacebookRequest($session, 'GET', "/me/accounts");
		$result = $request->execute()->getGraphObjectList(GraphUserPage::className());
		return $result;
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

	$admin_page = elgg_get_plugin_setting('admin_page', 'facebook');
	
	foreach ($pages as $idx => $page) {
		// If this page id matches ours, and we have create access
		if ($page->getId() == $admin_page && in_array('CREATE_CONTENT', $page->getPermissions()->asArray())) {
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
function facebook_get_authorize_url($redirect_url = NULL) {

	if (!$redirect_url) {
		$redirect_url = elgg_get_site_url() . 'facebook/login';
	}
	
	$helper = new FacebookRedirectLoginHelper($redirect_url);
	$login_url = $helper->getLoginUrl(array('scope' => facebook_get_scope()));

	return $login_url;
}

function facebook_login($skip_login = FALSE) {

	// sanity check
	if (!$skip_login && !facebook_can_login()) {
		forward();
	}

	$redirect_url = elgg_get_site_url() . 'facebook/login';

	if ($skip_login) {
		$redirect_url .= '/connect';
	}

	$helper = new FacebookRedirectLoginHelper($redirect_url);
	try {
	  $session = $helper->getSessionFromRedirect();
	} catch(FacebookRequestException $ex) {
		// When Facebook returns an error
		register_error(elgg_echo('facebook:login:error', array($ex->getMessage())));
		forward();
		
	} catch(\Exception $ex) {
		// When validation fails or other local issues
		register_error(elgg_echo('facebook:login:error', array($ex->getMessage())));
		forward();
	}

	// Check for session
	if ($session) {

		// graph api request for user data
		$request = new FacebookRequest($session, 'GET', '/me');
		$response = $request->execute();

		// get response as graph user
		$user = $response->getGraphObject(GraphUser::className());

		if ($skip_login) {
			$current_user = elgg_get_logged_in_user_entity();

			// Existing user, connecting their account
			if (!$current_user->facebook_account_id) {
				// trigger a hook for plugin authors to intercept
				if (!trigger_plugin_hook('new_facebook_user', 'facebook', array('existing_user' => $current_user,'account' => $user), TRUE)) {
					// halt execution
					register_error(elgg_echo('facebook:login:error:hook'));
					forward();
				}
			} else if ($current_user->facebook_account_id != $user->getId()) {
				register_error(elgg_echo('facebook:error:usermismatch'));
				forward(elgg_get_site_url() . 'facebook/settings?cfb=0');
			} 


			// Get a long lived access token
			$access_token = $session->getAccessToken();
			$ll_access_token = $access_token->extend();

			// Set user metadata and save
			$current_user->facebook_account_connected = TRUE;		
			$current_user->facebook_access_token = $ll_access_token;
			$current_user->facebook_account_id = $user->getId();
			$current_user->save();

			// Success and fowards
			system_message(elgg_echo('facebook:success:connectedaccount'));
			forward(elgg_get_site_url() . 'facebook/settings?cfb=0');
		} else {
			// try to find local user
			$options = array(
				'type' => 'user',
				'metadata_name' => 'facebook_account_id',
				'metadata_value' => $user->getId(),
			);

			$users = elgg_get_entities_from_metadata($options);

			if (!$users) {
				// check new registration allowed
				if (!elgg_get_config('allow_registration')) {
					register_error(elgg_echo('registerdisabled'));
					forward();
				}

				// trigger a hook for plugin authors to intercept
				if (!trigger_plugin_hook('new_facebook_user', 'facebook', array('account' => $user), TRUE)) {
					// halt execution
					register_error(elgg_echo('facebook:login:error:hook'));
					forward();
				}

				// If we've successfully created a facebook user, login!
				if ($user = facebook_create_user_from_graph($session, $user)) {
					system_message(elgg_echo('facebook:login:new'));
					login($user);
					forward(elgg_get_site_url());
				} else {
					forward(elgg_get_site_url());
				}
			} elseif (count($users) == 1) {
				// Got a user, log them in
				login($users[0]);
				system_message(elgg_echo('facebook:login:success'));
				forward();
			}
			
			// register login error
			register_error(elgg_echo('facebook:login:error', array(elgg_echo('facebook:login:error:nosession'))));
			forward();
		}
	}
}

/**
 * Create a user with given facebook data
 * 
 * @param  Facebook\FacebookSession $session
 * @param  Facebook\GraphUser       $user
 * @return mixed
 */
function facebook_create_user_from_graph($session, $user) {
	// Can no longer grab the fb 'username', going to try building it from the email
	$user_email = $user->getEmail();

	if ($user_email) {
		$username = substr($user_email, 0, strpos($user_email, '@')) . '_fb';
	} else {
		// Fall back on app unique ID :/
		$username = $user->getId();
	}
	
	$password = generate_random_cleartext_password();

	// Try to create new account
	try {
		if (!$user_guid = register_user($username, $password, $user->getName(), $user->getEmail())) {
			register_error(elgg_echo('registerbad'));
			return FALSE;
		}
	} catch (RegistrationException $r) {
		register_error($r->getMessage());
		return FALSE;
	}

	$new_user = get_entity($user_guid);

	if (elgg_instanceof($new_user, 'user')) {
		// Get a long lived access token
		$access_token = $session->getAccessToken();
		$ll_access_token = $access_token->extend();

		$new_user->facebook_account_connected = TRUE;		
		$new_user->facebook_access_token = $ll_access_token;
		$new_user->facebook_account_id = $user->getId();
		$new_user->save();
		return $new_user;
	} else {
		register_error(elgg_echo('registerbad'));
		return FALSE;
	}
}