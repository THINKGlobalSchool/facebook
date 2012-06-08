<?php
/**
 * Facebook Open Graph Injection
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
	
	elgg_extend_view('page/elements/head', 'facebook/head');
	$_SESSION['fb_og_entity'] = $entity;

	echo <<<JAVASCRIPT
		<script type='text/javascript'>
			$('.elgg-sidebar').prepend('<div class="fb-like" data-href="$url" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div><br /><br />');

		</script>
JAVASCRIPT;
}