<?php
/**
 * Facebook Album Upload Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

// Could take a while
set_time_limit(0);

$album_guid = get_input('album_guid');

$album = get_entity($album_guid);

// Check for proper album
if (!elgg_instanceof($album, 'object', 'album')) {
	register_error(elgg_echo('facebook:error:invalidalbum'));
	forward(REFERER);
}

// Get a facebook client
try {
	$facebook = facebook_get_client();
} catch (FacebookApiException $e) {
	register_error(elgg_echo('facebook:error:accounterror'));
	forward(REFERER);
}

// Set file upload support
$facebook->setFileUploadSupport(true);

// Facebook album details
$album_details = array(
        'name' => $album->title
);

$message = strip_tags($album->description);

// Add description
if ($message) {
	$album_details['message'] = $message;
}

// Create the album
try {
	$create_album = $facebook->api('/me/albums', 'post', $album_details);	
} catch (FacebookApiException $e) {
	register_error($e->getMessage());
	forward(REFERER);
}
  
//Get album ID of the album you've just created
$album_uid = $create_album['id'];

// Get photo count
$options = array(
	'types' => 'object', 
	'subtypes' => 'image', 
	'container_guid' => $album_guid, 
	'limit' => 0,
	'count' => TRUE,
);

$photo_count = elgg_get_entities($options);

// Album url for graph request
$album_url = '/'. $album_uid . '/photos';

unset($options['count']);

// If we have less than 50 images
if ($photo_count <= 50) { // Batch limit
	// Get photos in a batch
	$photos = new ElggBatch('elgg_get_entities', $options);

	$result = facebook_batch_upload_photos($facebook, $photos, $album_url);
	
	if (!$result['error']) {
		system_message(elgg_echo('facebook:success:albumupload'));
	} else {
		register_error(elgg_echo('facebook:error:albumupload', array($result['error'])));
	}
} else {
	// Determine number batch uploads we need to perform
	$batch_count = ceil($photo_count / 50);

	$options['limit'] = 50;
	
	$errors = array();

	for ($i = 0; $i < $batch_count; $i++) {
		// Set offset
		$options['offset'] = $options['limit'] * $i;
		$photos = new ElggBatch('elgg_get_entities', $options);
		
		$result = facebook_batch_upload_photos($facebook, $photos, $album_url);
		
		if ($result['error']) {
			$errors[] = $result['error'];
		}
	}

	// Check for errors and display accordingly
	if (count($errors) > 0) {
		foreach ($errors as $error) {
			register_error(elgg_echo('facebook:error:albumupload', array($error)));
		}
	} else {
		// All was good!
		system_message(elgg_echo('facebook:success:albumupload'));
	}
}
forward(REFERER);