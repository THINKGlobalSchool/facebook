<?php
/**
 * Facebook Head (Metatags)
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */
$appId = elgg_get_plugin_setting('app_id', 'facebook');
$image = elgg_get_site_url() . 'mod/facebook/graphics/spot-fb-icon.png';
$current_page_url = current_page_url();
?>
<meta property="og:title" content="THINK Spot" />
<meta property="og:type" content="article" />
<meta property="og:url" content="<?php echo $current_page_url; ?>" />
<meta property="og:image" content="<?php echo $image; ?>" />
<meta property="og:site_name" content="THINK Spot" />
<meta property="fb:admins" content="<?php echo $appId; ?>" />