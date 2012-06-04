<?php
/**
 * Facebook Wire Extender
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$user = elgg_get_logged_in_user_entity();

// User has connected account to facebook, show checkbox to post to wall
if ($user->facebook_account_connected) {
	$wire_label = elgg_echo('facebook:label:postwall');

	$wire_input = elgg_view('input/checkboxes', array(
		'name' => "facebook_post_wall", 
		'value' => 1,  
		'options' => array($wire_label => 'on'),
	));

	echo $wire_input;
}