<?php
/**
 * Facebook Settings View
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$connect_info = elgg_echo('facebook:label:connectinfo');
$content = "<div><label>$connect_info</label><br /><br /></div>";

// Check if the user has connected their account
if (!$vars['user']->facebook_account_connected || !$vars['user']->facebook_access_token) {
	// Not connected, display form
	$content .= elgg_view_form('facebook/connect', $vars);
} else {
	// Connected
	try {
		$facebook = facebook_get_client();

		$user_account = $facebook->api('/me');

		$module_title = elgg_echo('facebook:label:connectedaccountdetails');
	
		$name_label = elgg_echo('facebook:label:accountname');
		$link_label = elgg_echo('facebook:label:accountlink');

		$account_id = $user_account['id'];
		$account_name = $user_account['name'];
		$account_link = elgg_view('output/url', array(
			'text' => $user_account['link'],
			'href' => $user_account['link'],
			'target' => '_blank',
		));
	
		$module_content = <<<HTML
			<table class='elgg-table'>
				<tr>
					<td rowspan='2'>
						<center><img src="https://graph.facebook.com/{$account_id}/picture" /></center>
					</td>
					<td>
						<label>$name_label</label>: $account_name
					</td>
				</tr>
				<tr>
					<td>
						<label>$link_label</label>: $account_link
					</td>
				</tr>
			</table><br />
HTML;

		// Disconnect form
		$module_content .= elgg_view_form('facebook/disconnect', $vars);
	
		$content .= elgg_view_module('featured', $module_title, $module_content);
	
		$content .= elgg_view_form('facebook/usersettings', $vars);
	
	} catch (FacebookApiException $e) {
		$login_button = elgg_view('input/button', array(
			'name' => 'facebook_login',
			'value' => elgg_echo('facebook:label:login'),
			'href' => "#",
			'class' => 'elgg-button elgg-button-submit facebook-button facebook-login-button',
		));
		
		$content .= "<p>" . elgg_echo('facebook:error:accesstoken') . "</p><p>{$login_button}</p>";		
		$content .= elgg_view_form('facebook/disconnect', $vars);
	}
}

echo $content;
