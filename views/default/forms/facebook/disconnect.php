<?php
/**
 * Facebook Disconnect Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$user = elgg_extract('user', $vars);

$disconnect_input = elgg_view('input/submit', array(
	'name' => 'facebook_disconnect',
	'value' => elgg_echo('facebook:label:disconnectaccount'),
));

echo $disconnect_input;