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
$GLOBALS['PCT_THEME_INSTALLER']['base_url'] = 'http://pct-theme-installer.tim-gatzky.de';
$GLOBALS['PCT_THEME_INSTALLER']['api_url'] = 'http://pct-theme-installer.tim-gatzky.de/api.php';
$GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'] = 'system/tmp/pct_theme_installer';
$GLOBALS['PCT_THEME_INSTALLER']['THEMES']['eclipse'] = array
(
	'mandatory' => array('upload','eclipse contao 3_5.cto','eclipse contao 4_4.cto'), // mandatory zip content on first level
	'sql_templates' => array
	(
		'3.5' => 'eclipse_contao_3_5.sql',
		'4.4' => 'eclipse_contao_4_4.sql'
	),
);

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

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseTemplate'][] = array('PCT\ThemeInstaller','injectScripts');