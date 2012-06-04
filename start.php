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
	
	// Register facebook JS
	$fb_js = elgg_get_simplecache_url('js', 'facebook/facebook');
	elgg_register_simplecache_view('js/facebook/facebook');
	elgg_register_js('elgg.facebook', $fb_js);
	
	// Register facebook CSS
	$fb_css = elgg_get_simplecache_url('css', 'facebook/css');
	elgg_register_simplecache_view('css/facebook/css');
	elgg_register_css('elgg.facebook', $fb_css);
	elgg_load_css('elgg.facebook');

	// Load fb related js if user is connected
	if (elgg_is_logged_in() && elgg_get_logged_in_user_entity()->facebook_account_connected) {
		//elgg_load_js('elgg.facebookchannel');
		elgg_load_js('elgg.facebook');
	}

	// JS SDK
	elgg_extend_view('page/elements/topbar', 'facebook/js-sdk');
	
	// Facebook footer
	elgg_extend_view('page/elements/footer', 'facebook/footer');
	
	// Wire extender
	elgg_extend_view('forms/thewire/add', 'facebook/wirepost');

	// Facebook page handler
	elgg_register_page_handler('facebook', 'facebook_page_handler');
	
	// Pagesetup event handler
	elgg_register_event_handler('pagesetup','system','facebook_pagesetup');
	
	// Hook into object create event
	elgg_register_event_handler('create', 'object', 'facebook_wire_post_handler');
	
	// Actions
	$action_base = elgg_get_plugins_path() . 'facebook/actions/facebook';
	elgg_register_action("facebook/connect", "$action_base/connect.php");
	elgg_register_action("facebook/disconnect", "$action_base/disconnect.php");
	elgg_register_action("facebook/return", "$action_base/return.php");
	elgg_register_action("facebook/usersettings", "$action_base/usersettings.php");
	elgg_register_action("facebook/set_token", "$action_base/set_token.php");
	
	// Register plugin hook for status updates
	elgg_register_plugin_hook_handler('status', 'user', 'facebook_status_hook_handler');

	// Admin menu items
	elgg_register_admin_menu_item('administer', 'account_connections', 'facebook');

	return TRUE;
}

/**
 * Facebook page handler
 *
 * @param array $page Array of url parameters
 * @return bool
 */
function facebook_page_handler($page) {
	// Ajax requests
	if (elgg_is_xhr()) {
		switch ($page[0]) {
			case 'check':
				echo facebook_check_token(elgg_get_logged_in_user_entity());
				break;
			default:
				break;
		}
	} else {
		switch ($page[0]) {
			case 'test':
				$params = facebook_test();
				break;
			default;
			case 'settings':
			default:
				gatekeeper();
				$params = facebook_get_user_settings_content();
				break;
		}

		$body = elgg_view_layout('one_sidebar', $params);

		echo elgg_view_page($params['title'], $body);
	}
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
			'href' => "facebook/settings?cfb=0",
		);
		elgg_register_menu_item('page', $params);
	}
}

/**
 * Hook into the user status update handler
 * 
 * @param string $hook   Name of hook
 * @param string $type   Entity type
 * @param mixed  $value  Return value
 * @param array  $params Parameters
 * @return mixed
 */
function facebook_status_hook_handler($hook, $type, $value, $params) {
	if (!elgg_instanceof($params['user'], 'user')) {
		return;
	}

	// If user has enabled automatic wire posts, then post this message to facebook
	if (elgg_get_plugin_user_setting('auto_post_wire', $params['user']->guid, 'facebook')) {
		facebook_post_user_status($params['message'], $params['user']);
	}

	return TRUE;
}

/**
 * Hook into the object create handler for wire posts
 * 
 * @param string $event        Name of hook
 * @param string $object_type  Entity type
 * @param ElggObject  $object  Return value
 * @return bool
 */
function facebook_wire_post_handler($event, $object_type, $object) {
	$user = elgg_get_logged_in_user_entity();
	if (elgg_instanceof($object, 'object', 'thewire') && $user->facebook_account_connected) {
		$post_wall = get_input('facebook_post_wall');
		
		if ($post_wall != 0) {
			facebook_post_user_status($object->description, $user);
		}
	}
	return TRUE;
}
