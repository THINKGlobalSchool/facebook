<?php
/**
 * Facebook Share Item Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

// Get entity
$entity_guid = get_input('entity_guid');

$entity = get_entity($entity_guid);

if (elgg_instanceof($entity, 'object')) {
	// Build params for facebook post
	$params = array(
		'link' => $entity->getURL(),
		'caption' => ' ',
		'message' => elgg_echo('facebook:label:sharemessage'),
	);

	// Get entity title/name
	$name = $entity->title ? $entity->title : $entity->name;

	if (!empty($name)) {
		$params['name'] = $name;
	}

	// Entity description
	$description = facebook_decode_text(elgg_get_excerpt($entity->description));

	if (!empty($description)) {
		$params['description'] = $description;
	}
	
	$default_image = elgg_get_site_url() . 'mod/facebook/graphics/spot-fb-icon.png';

	// Trigger a plugin hook to allow plugins to set their own image
	$params['picture'] = elgg_trigger_plugin_hook('opengraph:image', 'facebook', array('entity' => $entity), $default_image);

	// Make the post
	$result = facebook_make_post($params, 'me');

	// If we have an error, display it
	if ($result['error']) {
		register_error(elgg_echo('facebook:error:share', array($result['error'])));
	} else {
		// All good
		system_message(elgg_echo('facebook:success:share'));
	}
} else {
	register_error(elgg_echo('facebook:error:invalidentity'));
}
forward(REFERER);
