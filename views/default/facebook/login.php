<?php
/**
 * Facebook Login
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2014
 * @link http://www.thinkglobalschool.com/
 * 
 */

$url = facebook_get_authorize_url();

$login_label = elgg_echo('facebook:label:facebooklogin');

?>
<hr class='facebook-hr' />
<center>
	<div class='facebook-login-or'><?php echo $login_label; ?></div>
	<a class='btn-auth btn-facebook' href="<?php echo $url;?>">Facebook</a>
</center>

