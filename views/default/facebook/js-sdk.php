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

$appId = elgg_get_plugin_setting('app_id', 'facebook');

$channel_url = elgg_get_site_url() . "mods/facebook/views/default/js/facebook/channel.html";

?>
<script>
	$(function() {	
		$('body').prepend('<div id="fb-root"></div>');
	
		window.fbAsyncInit = function() {
			FB.init({
				appId      : '<?php echo $appId; ?>', // App ID
				channelUrl : '<?php echo $channel_url; ?>', // Channel File @TODO
				status     : true, // check login status
				cookie     : true, // enable cookies to allow the server to access the session
				xfbml      : true  // parse XFBML
				});

				elgg.trigger_hook('ready', 'facebook');	
		};

		
	  	// Load the SDK Asynchronously
		(function(d){
			var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "//connect.facebook.net/en_US/all.js";
			ref.parentNode.insertBefore(js, ref);
		}(document));
	});
</script>