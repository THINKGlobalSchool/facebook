<?php
/**
 * Facebook Photo Upload Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org/
 * 
 */

use Facebook\FacebookSession;
use Facebook\FacebookRequest;


$photo_guid = get_input('photo_guid');
$post_page = get_input('post_page');

$photo = get_entity($photo_guid);

// Check for proper image
if (!elgg_instanceof($photo, 'object', 'image')) {
	register_error(elgg_echo('facebook:error:invalidphoto'));
	forward(REFERER);
}

$user = elgg_get_logged_in_user_entity();

$session = facebook_get_session_from_user($user);
$fb_user = facebook_get_graph_user_from_session($session);

if (!$session || !$user) {
	register_error(elgg_echo('facebook:error:accounterror'));
	forward(REFERER);
}

$message = facebook_decode_text($photo->description);

// Args array for photo upload
$args = array();

if (!empty($message)) {
	$args['message'] = $message;
}

$large_thumb = new ElggFile();
$large_thumb->owner_guid = $photo->owner_guid;
$large_thumb->setFilename($photo->largethumb);

$filename = $large_thumb->getFilenameOnFilestore();

// Add image path to upload args
$args['image'] = '@' . realpath($filename);

// If we're attempting to post a photo to the admin page
if ($post_page) {
	// Get the page
	$page = facebook_get_admin_page($user);

	if (!$page) {
		register_error(elgg_echo('facebook:error:admin_page'));
		forward(REFERER);
	}

	// Set page access token
	$args['access_token'] = $page->getAccessToken();
	
	$location = $page->getId();
} else {
	// Just posting to our wall
	$location = 'me';
}

try {
	// Post it!
	$request = new FacebookRequest($session, 'POST', "/{$location}/photos", $args);
	$request->execute();
	system_message(elgg_echo('facebook:success:photoupload'));
	
	if ($post_page) {
		$photo->posted_to_facebook_page = TRUE;
	}
} catch (Exception $e) {
	// Something went wrong, display message
	register_error(elgg_echo('facebook:error:photoupload', array($e->getMessage())));
}

forward(REFERER);
