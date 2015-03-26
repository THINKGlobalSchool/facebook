<?php
/**
 * Facebook Disconnect Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org/
 * 
 */

$user = elgg_get_logged_in_user_entity();

$result = facebook_disconnect_user($user);

if ($result) {		
	system_message(elgg_echo('facebook:success:disconnectedaccount'));
} else {
	register_error(elgg_echo('facebook:error:disconnectingaccount'));
}

$user->facebook_account_connected = FALSE;
$user->facebook_access_token = NULL;
$user->facebook_access_token_expires = NULL;

// Note, we're going to keep the account id metadata in case this user wants to reconnect

forward(REFERER);