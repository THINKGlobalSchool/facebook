<?php
/**
 * Facebook Integration start.php
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 * Includes the PHP Facebook SDK from: https://github.com/facebook/facebook-php-sdk
 * PHP SDK Reference: https://developers.facebook.com/docs/reference/php/
 *
 * Latest PHP tag: 3.1.1
 * 
 */

elgg_register_event_handler('init','system','facebook_init');

// Init function
function facebook_init() {
	// Register/Load helper library
	elgg_register_library('elgg:facebook_helper', elgg_get_plugins_path() . 'facebook/lib/facebook_helper.php');
	elgg_load_library('elgg:facebook_helper');
	
	// Register facebook SDK
	elgg_register_library('elgg:facebook_sdk', elgg_get_plugins_path() . 'facebook/vendors/facebook-php-sdk/src/facebook.php');	 

	// Facebook page handler
	elgg_register_page_handler('facebook', 'facebook_page_handler');
	
	// Pagesetup event handler
	elgg_register_event_handler('pagesetup','system','facebook_pagesetup');

	return TRUE;
}

/**
 * Facebook page handler
 *
 * @param array $page Array of url parameters
 * @return bool
 */
function facebook_page_handler($page) {
	switch ($page[0]) {
		case 'settings':
		default:
			gatekeeper();
			$params = facebook_test();
			break;
	}

	$body = elgg_view_layout('one_sidebar', $params);

	echo elgg_view_page($params['title'], $body);

	return TRUE;  
}


// Pagesetup hook
function facebook_pagesetup() {
	// User settings
	if (elgg_get_context() == "settings" && elgg_get_logged_in_user_guid()) {
		$user = elgg_get_logged_in_user_entity();

		$params = array(
			'name' => 'facebook_settings',
			'text' => elgg_echo('facebook:label:facebooksettings'),
			'href' => "facebook/settings",
		);
		elgg_register_menu_item('page', $params);
	}
	
	
	// Admin menu items
	if (elgg_in_context('admin')) {
	}
}