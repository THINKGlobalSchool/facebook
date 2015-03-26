<?php
/**
 * Facebook Set token Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org/
 * 
 */

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

$access_token = get_input('access_token');
$user = elgg_get_logged_in_user_entity();

if (!$access_token || !elgg_instanceof($user, 'user')) {
	register_error(elgg_echo('facebook:error:set_token'));
} else {
	// Check to make sure the user id matches the current user's connected id

	$session = new FacebookSession($access_token);

	$access_token = $session->getAccessToken();

	$fb_user = facebook_get_graph_user_from_session($session);

	if ($fb_user) {
		if ($user->facebook_account_id != $fb_user->getId()) {
			register_error(elgg_echo('facebook:error:usermismatch'));
			forward(elgg_get_site_url() . 'facebook/settings?cfb=0');
		}
	}

	$ll_access_token = $access_token->extend();

	$user->facebook_account_connected = TRUE;		
	$user->facebook_access_token = $ll_access_token;
	$user->facebook_account_id = $fb_user->getId();
	$user->save();

	system_message(elgg_echo('facebook:success:set_token'));	
}

forward(REFERER);