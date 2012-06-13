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
$post_page = get_input('post_page');

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
$params = array(
        'name' => $album->title
);

$message = facebook_decode_text($album->description);

// Add description
if ($message) {
	$params['message'] = $message;
}

// If we're attempting to post an album to the admin page
if ($post_page) {
	// Get the page
	$page = facebook_get_admin_page($user);

	if (!$page) {
		register_error(elgg_echo('facebook:error:admin_page'));
		forward(REFERER);
	}

	// Set page access token
	$params['access_token'] = $page['access_token'];
	$batch_params['access_token'] = $params['access_token'];
	
	$post_location = $page['id'];
} else {
	// Just posting to our wall
	$post_location = 'me';
}

// Create the album
try {
	$create_album = $facebook->api("/{$post_location}/albums", 'post', $params);	
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

	$result = facebook_batch_upload_photos($facebook, $photos, $album_url, $batch_params);
	
	if (!$result['error']) {
		if ($post_page) {
			$album->posted_to_facebook_page = TRUE;
		}
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
		if ($post_page) {
			$album->posted_to_facebook_page = TRUE;
		}
		system_message(elgg_echo('facebook:success:albumupload'));
	}
}
forward(REFERER);