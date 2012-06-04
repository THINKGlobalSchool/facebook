<?php
/**
 * Facebook Integration Account Admin Page
 *
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$options = array(
	'type' => 'user',
	'limit' => 0,
	'metadata_name' => 'facebook_account_connected',
	'metadata_value' => TRUE,
);

$fb_users = elgg_get_entities_from_metadata($options);

$content = "
	<table class='elgg-table'>
		<tr>
			<th>User</th>
			<th>Token</th>
			<th>Expires</th>
		</tr>";
		
		
foreach ($fb_users as $user) {
	$content .= "<tr>";

	$user_link = elgg_view('output/url', array(
		'text' => $user->name,
		'hred' => $user->getURL(),
	));
	
	$token = $user->facebook_access_token;
	$expires = $user->facebook_access_token_expires;
	
	$content .= "<td>{$user_link}</td>";
	$content .= "<td>{$token}</td>";
	$content .= "<td>{$expires}</td>";
	$content .= "</tr>";
}

$content .=	"</table>";

echo $content;