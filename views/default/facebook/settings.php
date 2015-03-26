<?php
/**
 * Facebook Settings View
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.org	/
 * 
 */

use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

$connect_info = elgg_echo('facebook:label:connectinfo');
$content = "<div><label>$connect_info</label><br /><br /></div>";

// Check if the user has connected their account
if (!$vars['user']->facebook_account_connected || !$vars['user']->facebook_access_token) {
	// Not connected, display form
	$content .= elgg_view_form('facebook/connect', $vars);
} else {
		$session = facebook_get_session_from_user($vars['user']);

		try {
			$fb_user = (new FacebookRequest(
				$session, 'GET', '/me'
			))->execute()->getGraphObject(GraphUser::className());

		} catch (FacebookRequestException $e) {
			// The Graph API returned an error

			// Check if this is an authorization exception, which is FINE, just need to handle it
			if (get_class($e) != 'Facebook\FacebookAuthorizationException') {
				register_error($e->getMessage()); // Other request error..
			}
		} catch (Exception $e) {
			// Some other error occurred
			register_error($e->getMessage());
		}

		if ($fb_user) {

			$module_title = elgg_echo('facebook:label:connectedaccountdetails');
		
			$name_label = elgg_echo('facebook:label:accountname');
			$link_label = elgg_echo('facebook:label:accountlink');

			$account_id = $fb_user->getId();
			$account_name = $fb_user->getName();
			$account_link = elgg_view('output/url', array(
				'text' => $fb_user->getLink(),
				'href' => $fb_user->getLink(),
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


		} else {
			$login_button = elgg_view('input/button', array(
				'name' => 'facebook_login',
				'value' => elgg_echo('facebook:label:login'),
				'href' => "#",
				'class' => 'elgg-button elgg-button-submit facebook-button facebook-login-button',
			));
			
			$content .= "<p>" . elgg_echo('facebook:error:accesstoken') . "</p><p>{$login_button}</p>";		
		}
}

echo $content;
