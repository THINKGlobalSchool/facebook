<?php
/**
 * Facebook Settings View
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$photo = get_entity($vars['image_guid']);

if ($photo && $photo->canEdit()) {
	elgg_load_js('lightbox');
	elgg_load_css('lightbox');

	$post_label = elgg_echo('facebook:label:postphoto');

	$form = elgg_view('forms/facebook/uploadphoto', $vars);

	echo <<<HTML
	<li id="publish-image"><a class='elgg-lightbox' href="#facebook-photo-post">$post_label</a></li>
	<div style="display: none;">
		<div id="facebook-photo-post" class="facebook-post-container">
			$form
		</div>
	</div>
HTML;
}