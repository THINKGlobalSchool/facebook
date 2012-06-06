<?php
/**
 * Facebook Photo Upload Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$photo_guid = get_input('photo_guid');

$photo = get_entity($photo_guid);

// Check for proper image
if (!elgg_instanceof($photo, 'object', 'image')) {
	register_error(elgg_echo('facebook:error:invalidphoto'));
	forward(REFERER);
}

// Get a facebook client
try {
	$facebook = facebook_get_client();
} catch (FacebookApiException $e) {
	register_error(elgg_echo('facebook:error:accounterror'));
	forward(REFERER);
}

$facebook->setFileUploadSupport(true);

$message = strip_tags($photo->description);

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

try {
	// Post it!
	$data = $facebook->api('/me/photos', 'post', $args);
	system_message(elgg_echo('facebook:success:photoupload'));
} catch (Exception $e) {
	// Something went wrong, display message
	register_error(elgg_echo('facebook:error:photoupload', array($e->getMessage())));
}

forward(REFERER);
