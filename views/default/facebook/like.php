<?php
/**
 * Facebook Like Button
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$current_page_url = current_page_url();

$content = <<<HTML
	<div class="fb-like" data-href="$current_page_url" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false">
	</div>
HTML;

echo $content;