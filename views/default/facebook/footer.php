<?php
/**
 * Facebook Footer
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$show_dialog = elgg_view('output/url', array(
	'text' => 'sadasds',
	'href' => '#',
	'class' => 'elgg-lightbox',
	'id' => 'facebook-dialog-trigger',
));


$login_button = elgg_view('input/button', array(
	'name' => 'facebook_login',
	'value' => elgg_echo('facebook:label:login'),
	'href' => "#",
	'class' => 'elgg-button elgg-button-submit facebook-button facebook-login-button',
));

$settings_button = elgg_view('output/url', array(
	'name' => 'facebook_settings',
	'id' => 'facebook-settings-button',
	'text' => elgg_echo('facebook:label:settings'),
	'href' => elgg_get_site_url() . 'facebook/settings?cfb=0',
	'class' => 'elgg-button elgg-button-submit facebook-button',
));

$action_required = elgg_echo('facebook:label:actionrequired');

$content = <<<HTML
	$show_dialog
	<div style='display: none;'>
		<div class='facebook-local-dialog' id='facebook-login-dialog'>
			<h3>$action_required</h3>
			<span class='facebook-message'></span>
			<span class='facebook-buttons'>$login_button $settings_button</span>
		</div>
	</div>
HTML;

echo $content;