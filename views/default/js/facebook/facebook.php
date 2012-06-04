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
elgg.facebook.permission_scope = "<?php echo facebook_get_scope(); ?>";

elgg.facebook.init = function() {
	// Delegate click handler for login button
	$(document).delegate('.facebook-login-button', 'click', elgg.facebook.loginClick);
}

// FB Init 
elgg.facebook.fb_init = function() {
	// Check if we're preventing the check popup
	if ($.FBQueryString['cfb'] != 0) {
		// Check for token on page load
		elgg.facebook.checkToken();
	}	
}

// Check token
elgg.facebook.checkToken = function() {
	elgg.get(elgg.facebook.check_url, {
		success: function(data) {
			var result = $.parseJSON(data);

			if (result.error) {
				if (result.error.type == 'OAuthException') {
					//console.log(result.error.message);
					var $dialog_link = $('#facebook-dialog-trigger');
					$dialog_link.attr('href', '#facebook-login-dialog');

					$('#facebook-login-dialog > span.facebook-message').html(elgg.echo('facebook:error:accesstoken'));
					$dialog_link.click();

				} else {
					register_error(result.error.message);
				}
			} else {
				// Success
			}
			
		},
		error: function() {
			register_error(elgg.echo('facebook:error:checktoken'));
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
				}, 
				success: function(result) {
					if (result.status != -1) {
						// Success!
						$.fancybox.close();
						location.reload(true);
					} else {
						// Error
						FB.logout(function(response) {
						  // user is now logged out
						});
					}
				}
			});
		} else {
			//console.log('User cancelled login or did not fully authorize.');
		}
	}, 
	{
		scope: elgg.facebook.permission_scope // Scope string
	});
	event.preventDefault();
}

/**	
 * Helper function to grab a variable from a query string
 */
elgg.facebook.getQueryVariableFromString = function(string, variable) { 
  var query = string; 
  var vars = query.split("&"); 
  for (var i=0;i<vars.length;i++) { 
    var pair = vars[i].split("="); 
    if (pair[0] == variable) { 
      return pair[1]; 
    } 
  } 
}
	
elgg.register_hook_handler('init', 'system', elgg.facebook.init);
elgg.register_hook_handler('ready', 'facebook', elgg.facebook.fb_init);


/**
 * Easy peasy jquery plugin for querystring parsing
 */ 
(function($) {
    $.FBQueryString = (function(a) {
        if (a == "") return {};
        var b = {};
        for (var i = 0; i < a.length; ++i)
        {
            var p=a[i].split('=');
            if (p.length != 2) continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(window.location.search.substr(1).split('&'))
})(jQuery);