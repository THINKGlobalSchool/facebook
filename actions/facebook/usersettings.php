<?php
/**
 * Facebook User Settings Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$user = elgg_get_logged_in_user_entity();

$auto_post_wire = get_input('auto_post_wire');

if (!empty($auto_post_wire)) {
	elgg_set_plugin_user_setting('auto_post_wire', TRUE, $user->guid, 'facebook');
} else {
	elgg_set_plugin_user_setting('auto_post_wire', FALSE, $user->guid, 'facebook');
}

system_message(elgg_echo('facebook:success:usersettings'));
forward(REFERER);