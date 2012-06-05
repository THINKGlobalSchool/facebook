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

$post_confirm = elgg_echo('facebook:label:confirmpost');
$post_submit = elgg_view('input/submit', array(
	'name' => 'post-facebook-submit',
	'id' => 'post-facebook-submit',
	'value' => elgg_echo('facebook:label:post'),
	'class' => 'elgg-button elgg-button-action',
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