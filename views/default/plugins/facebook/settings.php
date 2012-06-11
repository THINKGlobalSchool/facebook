<?php
/**
 * Facebook JS SDK Channel File
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */


$roles = get_roles(0);
$roles_options = array();

foreach($roles as $role) {
	$roles_options[$role->guid] = $role->title;
}

$app_id = elgg_get_plugin_setting('app_id', 'facebook');
$app_secret = elgg_get_plugin_setting('app_secret', 'facebook');
$admin_page = elgg_get_plugin_setting('admin_page', 'facebook');
$admin_page_role = elgg_get_plugin_setting('admin_page_role', 'facebook');

$body .= '<div><label>' . elgg_echo('facebook:label:appid') . "</label><br />";
$body .= elgg_view('input/text', array('name' => 'params[app_id]', 'value' => $app_id)) . "</div>";

$body .= '<div><label>' . elgg_echo('facebook:label:appsecret') . "</label><br />";
$body .= elgg_view('input/text', array('name' => 'params[app_secret]', 'value' => $app_secret)) . "</div>";

$body .= '<div><label>' . elgg_echo('facebook:label:admin_page') . "</label><br />";
$body .= elgg_view('input/text', array('name' => 'params[admin_page]', 'value' => $admin_page)) . "</div>";

$body .= '<div><label>' . elgg_echo('facebook:label:admin_page_role') . "</label><br />";
$body .= elgg_view('input/dropdown', array(
	'name' => 'params[admin_page_role]',
	'options_values' => $roles_options,
	'value' => $admin_page_role,
)) . "</div>";



echo $body;