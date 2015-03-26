<?php
/**
 * Facebook Integration start.php
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org/
 * 
 * Includes the PHP Facebook SDK from: https://github.com/facebook/facebook-php-sdk
 * PHP SDK Reference: https://developers.facebook.com/docs/reference/php/
 *
 * Latest PHP tag: 3.2.3
 * 
 */

elgg_register_event_handler('init','system','facebook_init');

// Register/Load helper library
elgg_register_library('elgg:facebook_helper', elgg_get_plugins_path() . 'facebook/lib/facebook_helper.php');
elgg_load_library('elgg:facebook_helper');

// Init function
function facebook_init() {

	// Register facebook JS
	$fb_js = elgg_get_simplecache_url('js', 'facebook/facebook');
	elgg_register_js('elgg.facebook', $fb_js);
	
	// Register facebook CSS
	elgg_extend_view('css/elgg', 'css/facebook/css');

	// Load lightbox js
	elgg_load_js('lightbox');

	// Extend login view for facebook login button
	if (facebook_can_login() && !elgg_is_logged_in()) {
		elgg_extend_view('forms/login', 'facebook/login');
		elgg_extend_view('css/elgg', 'css/social_login');
	}

	// Load fb related js if user is connected
	if (elgg_is_logged_in() && elgg_get_logged_in_user_entity()->facebook_account_connected) {
		elgg_load_js('elgg.facebook');
		
		// Hook into the tidypics photo thumbnail handler
		elgg_register_plugin_hook_handler('photo_summary_params', 'tidypics', 'facebook_photo_summary_handler');

		// Hook into the tidypics album thumbnail handler
		elgg_register_plugin_hook_handler('album_summary_params', 'tidypics', 'facebook_album_summary_handler');
		
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
	}
	
	// Hook into facebook open graph image for tidypics
	elgg_register_plugin_hook_handler('opengraph:image', 'facebook', 'tidypics_opengraph_image_handler');

	// Hook into facebook open graph image for simplekaltura videos
	elgg_register_plugin_hook_handler('opengraph:image', 'facebook', 'simplekaltura_opengraph_image_handler');
	
	// Facebook footer
	elgg_extend_view('page/elements/footer', 'facebook/footer');
	
	// Wire extender
	elgg_extend_view('forms/thewire/extend', 'facebook/wirepost');

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
	elgg_register_action("facebook/disconnect", "$action_base/disconnect.php");
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
	
	// Ajax view whitelist
	elgg_register_ajax_view('forms/facebook/wall');

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
				$user = elgg_get_logged_in_user_entity();
				$result = facebook_check_token(elgg_get_logged_in_user_entity());
				if ($result && facebook_can_page_publish($user) && facebook_get_admin_page($user)) {
					$user->facebook_can_post_to_admin_page = TRUE;
				} else {
					$user->facebook_can_post_to_admin_page = FALSE;
				}
				echo $result;
				break;
			default:
				break;
		}
	} else {
		switch ($page[0]) {
			case 'login':
				if ($page[1] == 'connect') {
					$skip_login = TRUE;
				}
				facebook_login($skip_login);
				break;
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
 * Hook to customize tidypics photo summary and include facebook content
 *
 * @param string $hook   Name of hook
 * @param string $type   Entity type
 * @param mixed  $value  Return value
 * @param array  $params Parameters
 * @return mixed
 */
function facebook_photo_summary_handler($hook, $type, $value, $params) {
	$image = $params['entity'];
	$user = elgg_get_logged_in_user_entity();

	if (elgg_instanceof($image, 'object', 'image') 
		&& ($image->canEdit() || $user->facebook_can_post_to_admin_page)
		&& !elgg_in_context('ajaxmodule')) 
	{

		$form = elgg_view('forms/facebook/hover_upload', array('image_guid' => $image->guid));

		$value['class'] .= ' tp-post-facebook';
		$value['footer'] .= $form;
	}

	return $value;
}

/**
 * Hook to customize tidypics album summary and include facebook content
 *
 * @param string $hook   Name of hook
 * @param string $type   Entity type
 * @param mixed  $value  Return value
 * @param array  $params Parameters
 * @return mixed
 */
function facebook_album_summary_handler($hook, $type, $value, $params) {
	$album = $params['entity'];
	$user = elgg_get_logged_in_user_entity();
	if (elgg_instanceof($album, 'object', 'album') 
		&& ($album->canEdit() || $user->facebook_can_post_to_admin_page)
		&& !elgg_in_context('ajaxmodule')) 
	{

		$form = elgg_view('forms/facebook/hover_upload_album', array('album_guid' => $album->guid)); 

		$value['class'] .= ' tp-post-facebook';
		$value['footer'] .= $form;
	}

	return $value;
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

	$user = elgg_get_logged_in_user_entity();

	// Post album to facebook link
	if (elgg_instanceof($entity, 'object', 'album') && ($entity->canEdit() || $user->facebook_can_post_to_admin_page)) {
			$lightbox = "<div style='display: none;'>
				<div class='facebook-post-album-lightbox' id='facebook-post-album-{$entity->guid}'>
				</div>
			</div>";
			
			if ($user->facebook_can_post_to_admin_page) {
				$post_admin = ' facebook-post-admin-page';
				$href = elgg_get_site_url() . 'ajax/view/forms/facebook/wall?album_guid=' . $entity->guid;
			} else {
				$href = '#facebook-post-album-' . $entity->guid;
			}
			
			if ($entity->posted_to_facebook_page) {
				$repost = ' facebook-repost';
			}
			
			$options = array(
				'name' => 'post_album_to_facebook',
				'text' => elgg_echo('facebook:label:postalbum') . $lightbox,
				'title' => '',
				'id' => $entity->guid,
				'href' => $href,
				'class' => 'post-album-facebook-submit facebook-upload-lightbox' . $post_admin . $repost,
				'section' => 'actions',
				'priority' => 200,
			);
			$return[] = ElggMenuItem::factory($options);
	}


	// Post photo to facebook link
	if (elgg_instanceof($entity, 'object', 'image') && ($entity->canEdit() || $user->facebook_can_post_to_admin_page)) {
		$text = elgg_echo('facebook:label:postphoto');

		$form = elgg_view('forms/facebook/uploadphoto', array('image_guid' => $entity->guid));

		$text .= "<div style='display: none;'>
			<div id='facebook-photo-post-{$entity->guid}' class='facebook-post-container'>
				$form
			</div>
		</div>";

		$options = array(
			'name' => 'post_photo_to_facebook',
			'text' => $text,
			'section' => 'actions',
			'priority' => 901,
			'link_class' => 'facebook-photo-lightbox',
			'href' => '#facebook-photo-post-' . $entity->guid,
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
				'priority' => 200,
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
				'priority' => 200,
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
		// Fix for object views that output another object view (ie pages and page_top)
		if (!get_input('opengraph_added')) {
			set_input('opengraph_added', TRUE);
			$return .= elgg_view('facebook/opengraph', $params['vars']);
		}
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
 * Provide video thumbnail for open graph image
 *
 * @param sting  $hook   view
 * @param string $type   input/tags
 * @param mixed  $return  Value
 * @param mixed  $params Params
 *
 * @return array
 */
function simplekaltura_opengraph_image_handler($hook, $type, $return, $params) {
	$entity = $params['entity'];
	if (elgg_instanceof($entity, 'object', 'simplekaltura_video')) {
		return $entity->getIconURL();
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
		$post_page = get_input('facebook_post_page');

		// User selected admin wall, post to page
		if ($post_page) {
			facebook_make_page_post(array(
				'message' => $object->description,
			), $user);
		} else if ($post_wall) {
			// Post to user's wall
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
