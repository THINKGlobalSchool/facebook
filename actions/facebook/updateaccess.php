<?php
/**
 * Facebook Update Entity Access Action
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$entity_guid = get_input('entity_guid');

$entity = get_entity($entity_guid);

if (elgg_instanceof($entity, 'object') && $entity->canEdit()) {
	$entity->access_id = ACCESS_PUBLIC;
	$entity->save();

	// Trigger event to perform further processing of entity updates
	elgg_trigger_event('update:access', $entity->type, $entity);
	
	system_message(elgg_echo('facebook:success:updateaccess'));
} else {
	register_error(elgg_echo('facebook:error:updateaccess'));
}
forward(REFERER);