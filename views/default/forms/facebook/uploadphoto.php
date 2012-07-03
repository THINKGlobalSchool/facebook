<?php
/**
 * Facebook Upload Photo Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$image_guid = $vars['image_guid'];
$image = get_entity($image_guid);

if (elgg_get_logged_in_user_entity()->facebook_can_post_to_admin_page) {
	$post_admin = ' facebook-post-admin-page';
	$href = elgg_get_site_url() . 'ajax/view/forms/facebook/wall?photo_guid=' . $image_guid;
}

if ($image->posted_to_facebook_page) {
	$repost = ' facebook-repost';
}

$post_confirm = elgg_echo('facebook:label:confirmpost');
$post_submit = elgg_view('output/url', array(
	'name' => 'post-facebook-submit',
	'text' => elgg_echo('facebook:label:post'),
	'href' => $href,
	'class' => 'post-facebook-submit elgg-button elgg-button-action' . $post_admin . $repost,
));

$photo_guid = elgg_view('input/hidden', array(
	'name' => 'post-photo-guid',
	'class' => 'post-photo-guid',
	'value' => $vars['image_guid'],
));

echo <<<HTML
	<label>$post_confirm</label><br /><br />
	<center>$post_submit</center>
	$photo_guid
HTML;
?>