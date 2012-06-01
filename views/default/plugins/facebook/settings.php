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

$app_id = elgg_get_plugin_setting('app_id', 'facebook');
$app_secret = elgg_get_plugin_setting('app_secret', 'facebook');

$body .= '<p><label>' . elgg_echo('facebook:label:appid') . "</label><br />";
$body .= elgg_view('input/text', array('name' => 'params[app_id]', 'value' => $app_id)) . "</p>";

$body .= '<p><label>' . elgg_echo('facebook:label:appsecret') . "</label><br />";
$body .= elgg_view('input/text', array('name' => 'params[app_secret]', 'value' => $app_secret)) . "</p>";

echo $body;