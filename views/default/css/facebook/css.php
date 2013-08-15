<?php
/**
 * Facebook CSS
 * 
 * @package Facebook Integration
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2012
 * @link http://www.thinkglobalschool.com/
 * 
 */
?>
/*<style>*/

#facebook-dialog-trigger {
	display: none;
}

.facebook-local-dialog {
	width: 300px;
}

span.facebook-message {
	margin: 5px 0 10px 0;
	display: block;
	color: #555555;
}

.facebook-buttons {
	margin-left: auto;
	margin-right: auto;
	display: block;
}

/* Photo Hover menu */
.tp-post-facebook {
	position: relative;
}

.tp-post-facebook:hover .facebook-post-menu-hover {
	display: block;
}

.facebook-post-menu-hover {
	display: none;
	height: auto;
	z-index: 10000;
	position: absolute;
	width: 161px;
	bottom: 0;
}

.facebook-hover-container {
	color: #FFF;
}

.facebook-hover-container .facebook-post-container {
	padding-top: 5px;
	padding-bottom: 5px;
	background-color: rgba(0,0,0,.7);
	margin-left: 4px;
	margin-right: 4px;
	margin-bottom: 8px;
}

.facebook-hover-container .facebook-post-container.elgg-ajax-loader {
	background-color: #FFFFFF;
}

.facebook-hover-container .facebook-post-container label {
	color: #FFFFFF;
}

/* Album Hover menu */
.tp-post-facebook:hover .facebook-post-album-menu-hover {
	display: block;
}

.facebook-post-album-menu-hover {
	display: none;
	height: auto;
	z-index: 10000;
	position: absolute;
	width: 161px;
	top: 0;
}

.facebook-album-hover-container {
	color: #FFF;
}

.facebook-album-hover-container .facebook-post-album-container {
	padding-top: 5px;
	padding-bottom: 5px;
	background-color: rgba(0,0,0,.7);
	margin-left: 4px;
	margin-right: 4px;
	margin-top: 4px;
}

.facebook-album-hover-container .facebook-post-album-container label {
	color: #FFFFFF;
}

.facebook-album-hover-container .post-album-facebook-submit {
	font-size: 80%;
}

/* Album Upload Lightbox */
.facebook-post-album-lightbox {
	width: 250px;
}

.facebook-album-upload-error {
	color: #666666;
	font-style: italic;
}

/* Tweak wire display */
.elgg-form-thewire-add .elgg-input-radio {
	margin-left: 3px;
}

/* Wall choice form */
.facebook-wall-foot {
	margin-top: 6px;
}

/* Wire input */
.facebook-wire-input {
	margin-top: 8px;
}

/* Login Page */
div.facebook-login-or {
	font-weight: bold;
	font-size: 13px;
	margin-bottom: 7px;
	color: #555555;
}

hr.facebook-hr {
    border: 0;
    height: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

/** Entity Menu Icon **/
.elgg-menu-item-share-on-facebook,
.elgg-menu-item-post-album-to-facebook,
.elgg-menu-item-post-photo-to-facebook {
	background: transparent url(<?php echo elgg_get_site_url(); ?>mod/facebook/graphics/f_icon.png) no-repeat left !important;
}

/*</style>*/