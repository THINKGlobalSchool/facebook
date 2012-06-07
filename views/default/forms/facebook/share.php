<?php
/**
 * Facebook Share Item Permissions Form
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$entity_guid = elgg_extract('entity_guid', $vars);

$share_permissions_label = elgg_echo('facebook:label:sharepermissions');

$share_submit = elgg_view('input/submit', array(
	'value' => elgg_echo('facebook:label:updateandshare'),
	'name' => 'update_and_share',
	'class' => 'facebook-update-share elgg-button elgg-button-submit',
));

$cancel_submit = elgg_view('input/submit', array(
	'value' => elgg_echo('facebook:label:cancel'),
	'name' => 'cancel_share',
	'class' => 'facebook-cancel-share elgg-button elgg-button-submit',
));

$entity_guid = elgg_view('input/hidden', array(
	'name' => 'share_entity_guid',
	'class' => 'facebook-share-entity-guid',
	'value' => $entity_guid,
));

$content = <<<HTML
	<div>
		$share_permissions_label
	</div>
	<div class='share-foot'>
		$share_submit $cancel_submit
		$entity_guid
	</div>
HTML;

echo $content;