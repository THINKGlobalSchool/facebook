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

$access_token = get_input('access_token');
$user = elgg_get_logged_in_user_entity();


if (!$access_token || !elgg_instanceof($user, 'user')) {
	register_error(elgg_echo('facebook:error:set_token'));
} else {
	$extended_token = facebook_get_extended_token($access_token, $user);
	if ($extended_token['error']) {
		register_error($extended_token['error']);
	} else {
		$user->facebook_access_token = $extended_token['access_token'];
		$user->facebook_access_token_expires = $extended_token['expires'];
		system_message(elgg_echo('facebook:success:set_token'));
	}
	
}

forward();