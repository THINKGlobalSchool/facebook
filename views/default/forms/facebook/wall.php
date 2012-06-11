<?php
/**
 * Facebook Wall Form (for page posting)
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$photo_guid = get_input('photo_guid', FALSE);
$album_guid = get_input('album_guid', FALSE);

if ($photo_guid) {
	$photo = get_entity($photo_guid);
	$container_class = 'facebook-post-container';
	$submit_class = 'post-facebook-submit';
	$entity_guid_class = 'post-photo-guid';
	$entity_guid = $photo_guid;
	$href = '#';
} else if ($album_guid) {
	$album = get_entity($album_guid);
	$container_class = 'facebook-post-album-container';
	$submit_class = 'post-album-facebook-submit';
	$entity_guid_class = 'post-album-guid';
	$entity_guid = $album_guid;
	$container_id = 'facebook-post-page-' . $album_guid;
	$href = "#{$container_id}";
}

$entity = get_entity($entity_guid);

// If we own this, allow posting to connected wall
if ($entity->canEdit()) {
	$wall_label = elgg_echo('facebook:label:whichwall');
	$wall_submit = elgg_view('output/url', array(
		'text' => elgg_echo('facebook:label:yourwall'),
		'id' => $entity_guid,
		'href' => $href,
		'class' => "elgg-button elgg-button-submit $submit_class",
	));

	$page_submit_text = elgg_echo('facebook:label:pagewall');
} else {
	$wall_label = elgg_echo('facebook:label:postpagewall');
	$page_submit_text = elgg_echo('facebook:label:post');
}

$page_submit = elgg_view('output/url', array(
	'text' => $page_submit_text,
	'id' => $entity_guid,
	'href' => $href,
	'class' => "elgg-button elgg-button-submit $submit_class post-page",
));

$entity_guid = elgg_view('input/hidden', array(
	'name' => 'post-entity-guid',
	'class' => $entity_guid_class,
	'value' => $entity_guid,
));

$content = <<<HTML
	<div id="$container_id">
	<div>
		$wall_label
	</div>
	<div  class='$container_class facebook-wall-foot'>
		<center>$wall_submit $page_submit</center>
		$entity_guid
	</div>
HTML;

echo $content;