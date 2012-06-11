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
	'facebook:label:postwall' => 'Post to your Facebook wall',
	'facebook:label:admin_page_postwall' => 'Post to THINK Global School wall',
	'facebook:label:actionrequired' => 'Action Required',
	'facebook:label:login' => 'Login',
	'facebook:label:settings' => 'Settings',
	'facebook:label:postphoto' => 'Post to Facebook',
	'facebook:label:postalbum' => 'Post album on Facebook',
	'facebook:label:confirmpost' => 'Post this image to Facebook?',
	'facebook:label:post' => 'Post',
	'facebook:label:uploadingalbum' => 'Uploading Album to Facebook...',
	'facebook:label:sharefacebook' => 'Share on Facebook',
	'facebook:label:sharemessage' => 'Check out this link from Spot!',
	'facebook:label:sharepermissions' => 'In order to share this item on Facebook, you must set it\'s access level to public.',
	'facebook:label:updateandshare' => 'Update access and share',
	'facebook:label:cancel' => 'Cancel',
	'facebook:label:admin_page' => 'Post to page (page admins can post directly to this page)',
	'facebook:label:whichwall' => 'Which wall do you want to post this to?',
	'facebook:label:postpagewall' => 'Post to THINK Global School wall?',
	'facebook:label:yourwall' => 'Your wall',
	'facebook:label:pagewall' => 'THINK Global School wall',
	'facebook:label:admin_page_role' => 'Page post role',

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
	'facebook:error:invalidphoto' => 'Invalid Photo',
	'facebook:error:invalidalbum' => 'Invalid Album',
	'facebook:error:invalidentity' => 'Invalid Entity',
	'facebook:error:photoupload' => 'There was an error posting your photo to Facebook: %s',
	'facebook:error:albumupload' => 'There wan an error posting your album to Facebook: %s',
	'facebook:error:share' => 'There was an error sharing the item on Facebook: %s',
	'facebook:error:updateaccess' => 'There was an error updating the entities access',
	'facebook:error:admin_page' => 'Could not access admin page',
	
	'facebook:success:connectedaccount' => 'Your account has been connected to Facebook!',
	'facebook:success:disconnectedaccount' => 'Your account has been disconnected from Facebook',
	'facebook:success:usersettings' => 'Facebook settings updated',
	'facebook:success:set_token' => 'Updated Facebook Access!',
	'facebook:success:photoupload' => 'Photo posted to Facebook!',
	'facebook:success:albumupload' => 'Album posted to Facebook!',
	'facebook:success:share' => 'Item shared!',
	'facebook:success:updateaccess' => 'Successfully updated access!',
	
	'facebook:error:accesstoken' => 'The access token associated with your connected Facebook account has expired. Please click login below to update your access.',

	// River

	// Notifications

	// Other content
);

add_translation('en',$english);