<?php
/**
 * Facebook Open Graph Header
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

// Get entity previously stored in session
$entity = $_SESSION['fb_og_entity'];

$title = $entity->title ? $entity->title : $entity->name;
$url = $entity->getURL();

// Default image
$default_image = elgg_get_site_url() . 'mod/facebook/graphics/spot-fb-icon.png';

// Trigger a plugin hook to allow plugins to set their own open graph image
$image = elgg_trigger_plugin_hook('opengraph:image', 'facebook', array('entity' => $entity), $default_image);

$site_name = elgg_get_site_entity()->title;
$appId = elgg_get_plugin_setting('app_id', 'facebook');

?>
<meta property="og:title" content="<?php echo $title; ?>" />
<meta property="og:type" content="article" />
<meta property="og:url" content="<?php echo $url; ?>" />
<meta property="og:image" content="<?php echo $image; ?>" />
<meta property="og:site_name" content="<?php echo $site_name; ?>" />
<meta property="fb:admins" content="<?php echo $appId; ?>" />