<?php
/**
 * Facebook User Settings Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$user = elgg_extract('user', $vars);

$auto_wire_enabled = elgg_get_plugin_user_setting('auto_post_wire', $user->getGUID(), 'facebook');

$wire_label = elgg_echo('facebook:label:autopostwire');

$wire_input = elgg_view('input/checkboxes', array(
	'name' => "auto_post_wire", 
	'value' => $auto_wire_enabled,  
	'options' => array($wire_label => 1),
));

$save_input = elgg_view('input/submit', array(
	'name' => 'facebook_usersettings_save',
	'value' => elgg_echo('save'),
));

$content = <<<HTML
	<div>
		$wire_input
	</div>
	<div class='elgg-foot'>
		$save_input
	</div><br />
HTML;

echo $content;