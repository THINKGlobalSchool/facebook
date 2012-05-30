<?php
/**
 * Facebook Integration Helper Library
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

function facebook_test() {
	$params = array();

	elgg_load_library('elgg:facebook_sdk');
	
	$appId = elgg_get_plugin_setting('app_id', 'facebook');
	$secret = elgg_get_plugin_setting('app_secret', 'facebook');
	
	$facebook = new Facebook(array(
	  'appId'  => $appId,
	  'secret' => $secret,
	));
	
	$user = $facebook->getUser();
	
	$params['title'] = elgg_echo('facebook:label:facebooksettings');
	$params['content'] = $content;

	return $params;
}