<?php
/**
 * Facebook Tidypics Album View Extension
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$user = elgg_get_logged_in_user_entity();

// This is gross, and will need to be updated when tidypics gets better
// this is how we can determine if we're in tidypics album gallery mode
if ((elgg_get_context() == "search" || !$vars['full_view']) 
	&& get_input('search_viewtype') == "gallery" 
	&& !elgg_in_context('ajaxmodule')
	&& ($album->canEdit() || $user->facebook_can_post_to_admin_page))
{
	echo elgg_view('forms/facebook/hover_upload_album', array('album_guid' => $album->guid)); 
}