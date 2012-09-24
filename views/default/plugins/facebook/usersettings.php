<?php
/**
 * Facebook Usersettings
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */
$access_token = elgg_get_plugin_user_setting('access_token', 0, 'facebook');
$facebook_id = elgg_get_plugin_user_setting('uid', 0, 'facebook');

echo '<p>' . elgg_echo('facebook:usersettings:description') . '</p>';

if (!$access_token || !$facebook_id) {
	// authorize
	$authorize = facebook_get_authorize_url("{$vars['url']}facebook/authorize");
	echo '<p>' . sprintf(elgg_echo('facebook:usersettings:authorize'), $authorize) . '</p>';
} else {
	$facebook = facebook_get_bare_client();
	$user = $facebook->api('/me', 'GET', array('access_token' => $access_token));
	echo '<p>' . sprintf(elgg_echo('facebook:usersettings:authorized'), $user['name'], $user['link']) . '</p>';
	
	$revoke = "{$vars['url']}facebook/revoke";
	echo '<p>' . sprintf(elgg_echo('facebook:usersettings:revoke'), $revoke) . '</p>';
}
