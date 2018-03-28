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

// Logic: STATUS.STEP
$GLOBALS['PCT_THEME_INSTALLER']['status'] = array
(
	'WELCOME',
	'RESET',
	'VALIDATION',
	'ACCEPTED',
	'FILE_EXISTS',
	'FILE_NOT_EXISTS',
	'INSTALLATION.UNZIP',
	'INSTALLATION.COPY_FILES',
	'INSTALLATION.CLEAR_CACHE',
	'INSTALLATION.DB_UPDATE_MODULES',
	'INSTALLATION.SQL_TEMPLATE_WAIT',
	'INSTALLATION.SQL_TEMPLATE_IMPORT',
);

$GLOBALS['PCT_THEME_INSTALLER']['breadcrumb_steps'] = array
(
	'WELCOME' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['welcome'],
		'href'	=> 'status=welcome',
	),
	'VALIDATION' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['validation'],
		'href'	=> 'status=validation',
	),
	'ACCEPTED' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['accepted'],
		'href'	=> 'status=accepted',
		'protected'	=> true
	),
	'NOT_ACCEPTED' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['not_accepted'],
		'href'	=> 'status=not_accepted',
		'protected'	=> true
	),
	'LOADING' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['loading'],
		'href'	=> 'status=loading',
		'protected'	=> true
	),
	'INSTALLATION.UNZIP' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.unzip'],
		'href'	=> 'status=installation&step=unzip',
	),
	'INSTALLATION.COPY_FILES' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.copy_files'],
		'href'	=> 'status=installation&step=copy_files',
	),
	'INSTALLATION.CLEAR_CACHE' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.clear_cache'],
		'href'	=> 'status=installation&step=clear_cache',
	),
	'INSTALLATION.DB_UPDATE_MODULES' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.db_update_modules'],
		'href'	=> 'status=installation&step=db_update_modules',
	),
	'INSTALLATION.SQL_TEMPLATE_WAIT' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_wait'],
		'href'	=> 'status=installation&step=sql_template_wait',
	),
	#'INSTALLATION.SQL_TEMPLATE_IMPORT' => array
	#(
	#	'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_import'],
	#	'href'	=> 'status=installation&step=sql_template_import',
	#)
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