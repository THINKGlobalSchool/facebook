<?php
/**
 * Facebook Hover Upload Album Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

elgg_load_js('lightbox');

$album_guid = $vars['album_guid'];

if (elgg_get_logged_in_user_entity()->facebook_can_post_to_admin_page) {
	$post_admin = ' facebook-post-admin-page';
	$href = elgg_get_site_url() . 'ajax/view/forms/facebook/wall?album_guid=' . $album_guid;
} else {
	$href = '#facebook-post-album-' . $album_guid;
}

$post_submit = elgg_view('output/url', array(
	'name' => 'post-album-facebook-submit',
	'href' => $href,
	'text' => elgg_echo('facebook:label:postalbum'),
	'id' => $album_guid,
	'class' => 'elgg-button elgg-button-action post-album-facebook-submit facebook-upload-lightbox' . $post_admin,
));

$album_guid_input = elgg_view('input/hidden', array(
	'name' => 'post-album-guid',
	'class' => 'post-album-guid',
	'value' => $album_guid,
));

$content = <<<HTML
	<div class="facebook-post-album-menu-hover">
		<div class="facebook-album-hover-container">
			<div class="facebook-post-album-container">
				<center>
					$post_submit
				</center>
				$album_guid_input
			</div>
		</div>
	</div>
	<div style="display: none;">
		<div class="facebook-post-album-lightbox" id="facebook-post-album-$album_guid">
		</div>
	</div>
HTML;

echo $content;