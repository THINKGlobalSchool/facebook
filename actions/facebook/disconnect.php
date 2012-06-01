<?php
/**
 * Facebook Disconnect Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */


elgg_load_library('elgg:facebook_sdk');

$user = elgg_get_logged_in_user_entity();

$appId = elgg_get_plugin_setting('app_id', 'facebook');
$secret = elgg_get_plugin_setting('app_secret', 'facebook');

$facebook = new Facebook(array(
  'appId'  => $appId,
  'secret' => $secret,
));

$facebook->setAccessToken($user->facebook_access_token);

$user_id = $facebook->getUser(); 

// @TODO No user.. fix this
if ($user_id) {
	$result = $facebook->api('/me/permissions', 'DELETE');

	if ($result) {
		$user->facebook_account_connected = FALSE;
		$user->facebook_access_token = NULL;
		$user->facebook_access_token_expires = NULL;
		
		system_message(elgg_echo('facebook:success:disconnectedaccount'));
	} else {
		register_error(elgg_echo('facebook:error:disconnectingaccount'));
	}
}

forward(REFERER);