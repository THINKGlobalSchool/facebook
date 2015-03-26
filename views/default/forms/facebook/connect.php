<?php
/**
 * Facebook Connect Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org/
 * 
 */

$user = elgg_extract('user', $vars);

$redirect_url = elgg_get_site_url() . 'facebook/login/connect';

$url = facebook_get_authorize_url($redirect_url);

$connect_input = elgg_view('output/url', array(
	'name' => 'facebook_connect',
	'text' => elgg_echo('facebook:label:connectaccount'),
	'href' => $url,
	'class' => 'elgg-button elgg-button-submit'
));

echo $connect_input;