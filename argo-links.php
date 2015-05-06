<?php
/**
  * @package Argo_Links
  * @version 0.01
  */
/*
Plugin Name: Argo Links
Plugin URI: https://github.com/argoproject/argo-links
Description: The Argo Links Plugin
Author: Project Argo, Mission Data
Version: 1.00
Author URI:
License: GPLv2
*/


require_once('argo-link-roundups.php');
require_once('argo-links-widget.php');
require_once('argo-links-class.php');


/* Initialize the plugin using it's init() function */
ArgoLinkRoundups::init();
ArgoLinks::init();
