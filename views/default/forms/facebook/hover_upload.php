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

$post_submit = elgg_view('output/url', array(
	'name' => 'post-facebook-submit',
	'id' => 'post-facebook-submit',
	'text' => elgg_echo('facebook:label:postphoto'),
	'class' => 'elgg-button elgg-button-action',
));

$photo_guid = elgg_view('input/hidden', array(
	'name' => 'post-photo-guid',
	'class' => 'post-photo-guid',
	'value' => $vars['image_guid'],
));

$content = <<<HTML
	<div class="facebook-post-menu-hover">
		<div class="facebook-hover-container">
			<div class="facebook-post-container">
				<center>
					$post_submit
				</center>
				$photo_guid
			</div>
		</div>
	</div>
HTML;

echo $content;