<?php
/**
 * Facebook Hover Upload Photo Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$photo_guid = $vars['image_guid'];
$photo = get_entity($photo_guid);

if (elgg_get_logged_in_user_entity()->facebook_can_post_to_admin_page) {
	$post_admin = ' facebook-post-admin-page';
	$href = elgg_get_site_url() . 'ajax/view/forms/facebook/wall?photo_guid=' . $photo_guid;
}

if ($photo->posted_to_facebook_page) {
	$repost = ' facebook-repost';
}

$post_submit = elgg_view('output/url', array(
	'name' => 'post-facebook-submit',
	'text' => elgg_echo('facebook:label:postphoto'),
	'href' => $href,
	'class' => 'elgg-button elgg-button-action post-facebook-submit' . $post_admin . $repost,
));

$photo_guid_input = elgg_view('input/hidden', array(
	'name' => 'post-photo-guid',
	'class' => 'post-photo-guid',
	'value' => $photo_guid,
));

$content = <<<HTML
	<div class="facebook-post-menu-hover" id='facebook-post-menu-hover-$photo_guid'>
		<div class="facebook-hover-container">
			<div class="facebook-post-container">
				<center>
					$post_submit
				</center>
				$photo_guid_input
			</div>
		</div>
	</div>
HTML;

echo $content;