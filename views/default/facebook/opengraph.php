<?php
/**
 * Facebook Open Graph
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$entity = elgg_extract('entity', $vars);

if (elgg_instanceof($entity, 'object')) {
	$title = $entity->title ? $entity->title : $entity->name;
	$url = $entity->getURL();
	$image = elgg_get_site_url() . 'mod/facebook/graphics/spot-fb-icon.png';
	$site_name = elgg_get_site_entity()->title;
	$appId = elgg_get_plugin_setting('app_id', 'facebook');

	echo <<<JAVASCRIPT
		<script type='text/javascript'>
			var head = document.getElementsByTagName('head')[0];

			var meta = document.createElement('meta');
			meta.setAttribute('property', 'og:title');
			meta.setAttribute('content', '$title');
			head.appendChild(meta);

			meta = document.createElement('meta');
			meta.setAttribute('property', 'og:type');
			meta.setAttribute('content', 'article');
			head.appendChild(meta);

			meta = document.createElement('meta');
			meta.setAttribute('property', 'og:url');
			meta.setAttribute('content', '$url');
			head.appendChild(meta);

			meta = document.createElement('meta');
			meta.setAttribute('property', 'og:image');
			meta.setAttribute('content', '$image');
			head.appendChild(meta);

			meta = document.createElement('meta');
			meta.setAttribute('property', 'og:site_name');
			meta.setAttribute('content', '$site_name');
			head.appendChild(meta);

			meta = document.createElement('meta');
			meta.setAttribute('property', 'fb:admins');
			meta.setAttribute('content', '$appId');
			head.appendChild(meta);
			
			$('.elgg-sidebar').prepend('<div class="fb-like" data-href="$url" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div><br /><br />');

		</script>
JAVASCRIPT;
}