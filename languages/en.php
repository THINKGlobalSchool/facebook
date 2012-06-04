<?php
/**
 * Facebook Integration English Language Translation
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

$english = array(
	// Generic
	
	// Admin section titles
	'admin:facebook' => 'Facebook',
	'admin:facebook:account_connections' => 'Accounts',
	
	// Page titles 

	// Labels
	'facebook:label:appid' => 'Facebook App ID',
	'facebook:label:appsecret' => 'Facebook App Secret',
	'facebook:label:facebooksettings' => 'Facebook Settings',
	'facebook:label:connectaccount' => 'Connect account to Facebook',
	'facebook:label:disconnectaccount' => 'Disconnect account from Facebook',
	'facebook:label:connectinfo' => 'Manage and update your account\'s connection to Facebook',
	'facebook:label:connectedaccountdetails' => 'Connected Account Details',
	'facebook:label:accountname' => 'Account Name',
	'facebook:label:accountlink' => 'Account Link',
	'facebook:label:autopostwire' => 'Automatically submit mini-posts as status updates',
	'facebook:label:actionrequired' => 'Action Required',
	'facebook:label:login' => 'Login',
	'facebook:label:settings' => 'Settings',

	// Messages
	'facebook:error:invalidstate' => 'Invalid State',
	'facebook:error:accounterror' => 'There was an error retrieving your account information',
	'facebook:error:disconnectingaccount' => 'There was an error disconnecting your account from Facebook',
	'facebook:error:disconnectingaccountuser' => 'There was an error disconnecting your account from Facebook, could not retrieve user info',
	'facebook:error:connectaccount' => 'There was an error connecting your account to Facebook.',
	'facebook:error:set_token' => 'There was an error updating Facebook access: Invalid user or access token',
	'facebook:error:checktoken' => 'Could not reach facebook token check endpoint',
	'facebook:error:usermismatch' => 'The facebook account you logged in with is not the same account you connected to Spot. Disconnect the existing account first, or try logging in again.',
	'facebook:error:statuspost' => 'There was an error posting to Facebook: %s',
	
	'facebook:success:connectedaccount' => 'Your account has been connected to Facebook!',
	'facebook:success:disconnectedaccount' => 'Your account has been disconnected from Facebook',
	'facebook:success:usersettings' => 'Facebook settings updated',
	'facebook:success:set_token' => 'Updated Facebook Access!',
	
	'facebook:error:accesstoken' => 'The access token associated with your connected Facebook account has expired. Please click login below to update your access.',

	// River

	// Notifications

	// Other content
);

add_translation('en',$english);