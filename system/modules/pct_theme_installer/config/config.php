<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2018, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_theme_installer
 */

/**
 * Constants
 */
define('PCT_THEME_INSTALLER', '1.0.');
define('PCT_THEME_INSTALLER_PATH','system/modules/pct_theme_installer');


/**
 * Globals
 */
$GLOBALS['PCT_THEME_INSTALLER']['api_url'] = 'http://pct-theme-installer.tim-gatzky.de/api.php';

/**
 * Register backend page / key
 */
// Eclipse installer
$GLOBALS['BE_MOD']['system']['pct_theme_installer'] = array
(
	'callback'    	=> 'PCT\ThemeInstaller',
	'icon'	 		=> PCT_THEME_INSTALLER_PATH.'/assets/img/icon.jpg',
	'stylesheet' 	=> PCT_THEME_INSTALLER_PATH.'/assets/css/be_styles.css',
);
