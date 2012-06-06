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
	// When clicking 'post album to facebook' entity menu item, hide the action menu
	$('.post-album-facebook-submit').live('click', function() {
		$('.tgstheme-entity-menu-actions').hide();
	});

	
	// Init special modal facebook lightboxes
	$(".facebook-upload-lightbox").fancybox({
		'modal': true,
	});
	
	// Delegate click handler for login button
	$(document).delegate('.facebook-login-button', 'click', elgg.facebook.loginClick);
	
	// Delegate click handler for facebook photo publish
	$('#post-facebook-submit').live('click', elgg.facebook.postPhoto); // @todo make this a class
	
	// Delegate click handler for facebook photo publish
	$('.post-album-facebook-submit').live('click', elgg.facebook.postAlbum);
	
	// Inject a class into each tidypics gallery item and move hover menu to parent
	$('.facebook-post-menu-hover').each(function() {
		$(this).parent().find('.tidypics_album_images').addClass('tp-post-facebook');
		$(this).appendTo($(this).parent().find('.tp-post-facebook'));
	});
	
	// Show/Hide photo hover menu
	$('.tp-post-facebook').hover(elgg.facebook.showPhotoHover, function() {
			var $fbhovermenu = $(this).find('img').data('fb-hovermenu');
			if ($fbhovermenu) {
				$fbhovermenu.fadeOut();
			}		
	});

	// Fix for hover menu when lighbox link is clicked
	$('.tp-post-facebook a.tidypics-lightbox').live('click', function() {
		$('.facebook-post-menu-hover').fadeOut();
	});

	// Inject a class into each tidypics album gallery item and move hover menu to parent
	$('.facebook-post-album-menu-hover').each(function() {
		$(this).prev().addClass('tp-post-album-facebook');
		$(this).appendTo($(this).prev());
	});
	
	// Show/Hide photo hover menu
	$('.tp-post-album-facebook').hover(elgg.facebook.showAlbumHover, function() {
			var $fbhovermenu = $(this).find('img').data('fb-album-hovermenu');
			if ($fbhovermenu) {
				$fbhovermenu.fadeOut();
			}		
	});
	
	// Fix for hover menu when lighbox link is clicked
	$('.tp-post-album-facebook a.facebook-upload-lightbox').live('click', function() {
		$('.facebook-post-album-menu-hover').hide();
	});
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

/**	
 * Post a photo to facebook
 */ 
elgg.facebook.postPhoto = function(event) {
	var _this = $(this);
	var container = _this.closest('.facebook-post-container');
	var photo_guid = container.find('.post-photo-guid').val();
	
	container.addClass('elgg-ajax-loader');
	container.html("<span>&nbsp;</span>");
	
	elgg.action('facebook/uploadphoto', {
		data: {
			photo_guid: photo_guid
		},
		success: function(data) {
			if (data.status == -1) {
				container.removeClass('elgg-ajax-loader');
				container.html('error: ' + data.system_messages.error);
			} else {
				container.removeClass('elgg-ajax-loader');
				container.html('<center><label>Success!</label></center>');
			}
		}
	});
	event.preventDefault();
}

/**	
 * Post an album to facebook
 */ 
elgg.facebook.postAlbum = function(event) {	
	var _this = $(this);

	// Lightbox
	var lightbox = $($(this).attr('href'));
	
	var title = elgg.echo('facebook:label:uploadingalbum');
	
	// Set lightbox content
	lightbox.html("<center><h3>" + title + "</h3></center><br /><div class='elgg-ajax-loader'></div>");
	
	var album_guid = $(this).attr('id');
	
	// Post album!
	elgg.action('facebook/uploadalbum', {
		data: {
			album_guid: album_guid,
		},
		success: function(data) {
			lightbox.find('.elgg-ajax-loader').remove();
			if (data.status == -1) {
				lightbox.append('<p class="facebook-album-upload-error">Error: ' + data.system_messages.error + '</p>');
				lightbox.append("<br /><a href='#' class='elgg-button elgg-button-submit'  onClick='parent.jQuery.fancybox.close();'>Close</a>")
			} else {
				lightbox.append('<center><label>Success!</label></center>');
				$.fancybox.close();
			}
		}
	});
	event.preventDefault();
}

/**
 * Show the post hover in tidypics gallery mode
 */
elgg.facebook.showPhotoHover = function(event) {

	$image = $(this).find('img');
	
	var $fbhovermenu = $image.data('fb-hovermenu') || null;


	if (!$fbhovermenu) {
		var $fbhovermenu = $image.closest('.tp-post-facebook').find(".facebook-post-menu-hover");
		$image.data('fb-hovermenu', $fbhovermenu);
	}

	$fbhovermenu.css("width", $image.width() + 'px').fadeIn('fast').position({
		my: "left top",
		at: "left top",
		of: $image
	}).appendTo($image.closest('.tp-post-facebook'));
}

/**
 * Show the post hover in tidypics gallery mode
 */
elgg.facebook.showAlbumHover = function(event) {
	$image = $(this).find('img');
	
	var $fbhovermenu = $image.data('fb-album-hovermenu') || null;

	if (!$fbhovermenu) {
		var $fbhovermenu = $image.closest('.tp-post-album-facebook').find(".facebook-post-album-menu-hover");
		$image.data('fb-album-hovermenu', $fbhovermenu);
	}

	$fbhovermenu.css("width", $image.width() + 'px').fadeIn('fast').position({
		my: "left top",
		at: "left top",
		of: $image
	}).appendTo($image.closest('.tp-post-album-facebook'));
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