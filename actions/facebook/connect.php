<?php
/**
 * Facebook Connect Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$user = elgg_get_logged_in_user_entity();

$state = md5(time() . $user->username . $user->salt);

$oauth_url = "https://www.facebook.com/dialog/oauth?";

$redirect_uri = elgg_add_action_tokens_to_url(elgg_get_site_url() . 'action/facebook/return');

// Save state and redirect to use upon returning from facebook authentication/authorization
$_SESSION['facebook_state'] = $state;
$_SESSION['facebook_redirect'] = $redirect_uri;

$parts = array(
	'client_id' => elgg_get_plugin_setting('app_id', 'facebook'),
	'redirect_uri' => $redirect_uri,
	'scope' => 'user_status,publish_stream,user_photos,photo_upload',
	'state' => $state,
);

$oauth_url .= http_build_query($parts);

forward($oauth_url);