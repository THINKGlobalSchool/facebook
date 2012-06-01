<?php
/**
 * Facebook JS Library
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */

?>
//<script>

elgg.provide('elgg.facebook');


elgg.facebook.check_url = elgg.get_site_url() + 'facebook/check';
elgg.facebook.set_token_url = elgg.get_site_url() + 'facebook/set_token';

elgg.facebook.init = function() {
	// Delegate click handler for login button
	$(document).delegate('#facebook-login-button', 'click', elgg.facebook.loginClick);
}


// FB Init 
elgg.facebook.fb_init = function() {
	// Check for token on page load
	elgg.facebook.checkToken();
}

// Check token
elgg.facebook.checkToken = function() {
	elgg.get(elgg.facebook.check_url, {
		success: function(data) {
			var result = $.parseJSON(data);

			if (result.error) {
				if (result.error.type == 'OAuthException') {
					console.log(result.error.message);
					var $dialog_link = $('#facebook-dialog-trigger');
					$dialog_link.attr('href', '#facebook-login-dialog');

					$('#facebook-login-dialog > span.facebook-message').html(elgg.echo('facebook:error:accesstoken'));
					$dialog_link.click();

				} else {
					console.log(result.error);
				}
			} else {
				// Success.
			}
			
		},
		error: function() {
			console.log('error');
		}
	});
}

elgg.facebook.loginClick = function(event) {
	FB.login(function(response) {
		if (response.authResponse) {
			var access_token = response.authResponse.accessToken;
			elgg.action('facebook/set_token', {
				data: {
					access_token: access_token,
				}
			});
		} else {
			console.log('User cancelled login or did not fully authorize.');
		}
	});
	event.preventDefault();
}
	
elgg.register_hook_handler('init', 'system', elgg.facebook.init);
elgg.register_hook_handler('ready', 'facebook', elgg.facebook.fb_init);