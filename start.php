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

		// Extend tidypics image menu
		elgg_extend_view('tidypics/image_menu', 'facebook/image_menu');
		
		// Extend tidypics album view
		elgg_extend_view('object/album', 'facebook/album');
		
		// Hook into the tidypics thumbnail handler
		elgg_register_plugin_hook_handler('tp_thumbnail_link', 'album', 'facebook_image_thumbnail_handler');
		
		// Hook into entity menu for tidypics albums
		elgg_register_plugin_hook_handler('register', 'menu:entity', 'facebook_setup_entity_menu');
	}
	
	if (elgg_is_logged_in()) {
		// JS SDK
		elgg_extend_view('page/elements/topbar', 'facebook/js-sdk');
	} else {
		//elgg_register_plugin_hook_handler('register', 'menu:entity', 'facebook_setup_public_entity_menu');
		elgg_register_plugin_hook_handler('view', 'all', 'facebook_full_view_handler');

		elgg_extend_view('page/elements/footer', 'facebook/js-sdk');
		$facebook_like = elgg_view('facebook/like');
		//elgg_register_menu_item('extras', array(
		//	'name' => 'facebook_like',
		//	'text' => $facebook_like,
		//	'href' => FALSE,
		//));
	}
	
	// Hook into facebook open graph image for tidypics
	elgg_register_plugin_hook_handler('opengraph:image', 'facebook', 'tidypics_opengraph_image_handler');
	
	// Facebook footer
	elgg_extend_view('page/elements/footer', 'facebook/footer');
	
	// Wire extender
	elgg_extend_view('forms/thewire/add', 'facebook/wirepost');

	// Facebook page handler
	elgg_register_page_handler('facebook', 'facebook_page_handler');
	
	// Pagesetup event handler
	elgg_register_event_handler('pagesetup','system','facebook_pagesetup');
	
	// Register event handler for facebook access updates
	elgg_register_event_handler('update:access', 'object', 'facebook_access_update_handler');
	
	// Hook into object create event
	elgg_register_event_handler('create', 'object', 'facebook_wire_post_handler');
	
	// Actions
	$action_base = elgg_get_plugins_path() . 'facebook/actions/facebook';
	elgg_register_action("facebook/connect", "$action_base/connect.php");
	elgg_register_action("facebook/disconnect", "$action_base/disconnect.php");
	elgg_register_action("facebook/return", "$action_base/return.php");
	elgg_register_action("facebook/usersettings", "$action_base/usersettings.php");
	elgg_register_action("facebook/set_token", "$action_base/set_token.php");
	elgg_register_action("facebook/uploadphoto", "$action_base/uploadphoto.php");
	elgg_register_action("facebook/uploadalbum", "$action_base/uploadalbum.php");
	elgg_register_action("facebook/share", "$action_base/share.php");
	elgg_register_action("facebook/updateaccess", "$action_base/updateaccess.php");
	
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
 * Hook to customize tidypics image thumbnail display
 *
 * @param string $hook   Name of hook
 * @param string $type   Entity type
 * @param mixed  $value  Return value
 * @param array  $params Parameters
 * @return mixed
 */
function facebook_image_thumbnail_handler($hook, $type, $value, $params) {
	$image = $params['image'];
	if (elgg_instanceof($image, 'object', 'image')  && $image->canEdit() && !elgg_in_context('ajaxmodule')) {
		elgg_load_js('lightbox');
		$form = elgg_view('forms/facebook/hover_upload', array('image_guid' => $image->guid));
		
		// This is junk.. tidypics needs to be re-worked
		if (!$value) {
			$url = elgg_get_site_url();
			
			$lightbox_url = $url . 'ajax/view/tidypics/image_lightbox?guid=' . $image->guid;

			$value = "<div class='tidypics_album_images tp-publish-flickr'>
						<a rel='tidypics-lightbox' class='tidypics-lightbox' href='{$lightbox_url}'><img id='{$image->guid}' src='{$url}photos/thumbnail/{$image->guid}/small/' alt='{$image->title}' /></a>
					</div>";
		}
		
		return $value . $form;
	} else {
		return FALSE;
	}
}

/**
 * Adds items to the entity menu
 *
 * @param sting  $hook   view
 * @param string $type   input/tags
 * @param mixed  $return  Value
 * @param mixed  $params Params
 *
 * @return array
 */
function facebook_setup_entity_menu($hook, $type, $return, $params) {
	$entity = $params['entity'];
	
	if (!elgg_instanceof($entity, 'object')) {
		return $return;
	}

	// Post album to facebook link
	if (elgg_instanceof($entity, 'object', 'album') && $entity->canEdit()) {
			$lightbox = "<div style='display: none;'>
				<div class='facebook-post-album-lightbox' id='facebook-post-album-{$entity->guid}'>
				</div>
			</div>";
			
			$options = array(
				'name' => 'post_album_to_facebook',
				'text' => elgg_echo('facebook:label:postalbum') . $lightbox,
				'title' => '',
				'id' => $entity->guid,
				'href' => "#facebook-post-album-{$entity->guid}",
				'class' => 'post-album-facebook-submit facebook-upload-lightbox',
				'section' => 'actions',
				'priority' => 100,
			);
			$return[] = ElggMenuItem::factory($options);
	}
	
	// @TODO should probable be a plugin hook
	$share_exceptions = array(
		'todo',
		'todosubmission',
		'forum', 
		'forum_topic',
		'forum_reply',
		'book',
		'shared_doc',
		'site',
		'image',
	);
	
	// If we're allowed to share this subtype, show the share link
	if (!in_array($entity->getSubtype(), $share_exceptions)) {
		if ($entity->access_id == ACCESS_PUBLIC) {
			$options = array(
				'name' => 'share_on_facebook',
				'text' => elgg_echo('facebook:label:sharefacebook') . $share_lightbox,
				'title' => '',
				'id' => $entity->guid,
				'href' => '#facebook-share-' . $entity->guid,
				'class' => 'facebook-share',
				'section' => 'actions',
				'priority' => 100,
			);
			$return[] = ElggMenuItem::factory($options);
		
		} else if ($entity->canEdit()){
			$share_form = elgg_view_form('facebook/share', array(), array('entity_guid' => $entity->guid));
		
			$share_lightbox = "<div style='display: none;'>
				<div class='facebook-share-lightbox' id='facebook-share-{$entity->guid}'>
					$share_form
				</div>
			</div>";

			$options = array(
				'name' => 'share_on_facebook',
				'text' => elgg_echo('facebook:label:sharefacebook') . $share_lightbox,
				'title' => '',
				'id' => $entity->guid,
				'href' => '#facebook-share-' . $entity->guid,
				'class' => 'facebook-non-public elgg-lightbox',
				'section' => 'actions',
				'priority' => 100,
			);
			$return[] = ElggMenuItem::factory($options);
		}
	}

	return $return;
}

/**
 * Adds items to the public entity menu
 *
 * @param sting  $hook   view
 * @param string $type   input/tags
 * @param mixed  $return  Value
 * @param mixed  $params Params
 *
 * @return array
 */
function facebook_full_view_handler($hook, $type, $return, $params) {
	// Only dealing with straight up object views here
	if (strpos($params['view'], 'object/') === 0                  // Check that view is an object view
		&& isset($params['vars']['entity'])                       // Make sure we have an entity
		&& strpos($params['view'], 'object/elements') !== 0       // Ignore object/elements views
		&& strpos($params['view'], 'object/summary') !== 0       // Ignore object/elements views
		&& $params['vars']['full_view']) 
	{	
		$return .= elgg_view('facebook/opengraph', $params['vars']);
	}
	return $return;
}

/**
 * Provide album cover for open graph image
 *
 * @param sting  $hook   view
 * @param string $type   input/tags
 * @param mixed  $return  Value
 * @param mixed  $params Params
 *
 * @return array
 */
function tidypics_opengraph_image_handler($hook, $type, $return, $params) {
	$entity = $params['entity'];
	if (elgg_instanceof($entity, 'object', 'album')) {
		return elgg_get_site_url() . 'photos/thumbnail/' . $entity->cover . "/small/";
	}
	return $return;
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

/**
 * Hook into update:access handler and perform extra processing for entities
 * 
 * @param string $event        Name of hook
 * @param string $object_type  Entity type
 * @param ElggObject  $object  Return value
 * @return bool
 */
function facebook_access_update_handler($event, $object_type, $object) {
	if (elgg_instanceof($object, 'object', 'album')) {
		//get images from album and update access on image entities
		$images = elgg_get_entities(array(
			'types' => 'object',
			'subtypes' => 'image',
			'container_guids' => $object->guid,
			'limit' => 0,
		));

		foreach ($images as $image) {
			$image->access_id = ACCESS_PUBLIC;
			$image->save();
		}
	}
	return TRUE;
}
