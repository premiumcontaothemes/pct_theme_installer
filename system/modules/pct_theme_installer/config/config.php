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

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\System;

/**
 * Constants
 */
define('PCT_THEME_INSTALLER', '3.0.1');
define('PCT_THEME_INSTALLER_PATH','system/modules/pct_theme_installer');

if( version_compare(ContaoCoreBundle::getVersion(),'5.0','>=') )
{
	$rootDir = System::getContainer()->getParameter('kernel.project_dir');
	include( $rootDir.'/'.PCT_THEME_INSTALLER_PATH.'/config/autoload.php' );
}

/**
 * Globals
 */
$GLOBALS['PCT_THEME_INSTALLER']['api_url'] = 'https://api.premium-contao-themes.com';
$GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'] = 'system/tmp/pct_theme_installer';

if(!isset($GLOBALS['PCT_THEME_INSTALLER']['test_license']))
{
	$GLOBALS['PCT_THEME_INSTALLER']['test_license'] = array();
}

$GLOBALS['PCT_THEME_INSTALLER']['THEMES']['eclipseX'] = array
(
	'label'	=> 'EclipseX',
	'zip_folder' => 'eclipseX_zip',
	'mandatory' => array('upload'), // mandatory zip content on first level
	'sql_templates' => array
	(
		'4.13' => 'eclipsex_contao_4_13.sql',
		'5.3' => 'eclipsex_contao_5_3.sql'
	),
);
$GLOBALS['PCT_THEME_INSTALLER']['THEMES']['eclipseX_cc'] = array
(
	'label'	=> 'EclipseX + CustomCatalog Pro',
	'isCustomCatalog' => true,
	'zip_folder' => 'eclipseX_cc_zip',
	'mandatory' => array('upload'), // mandatory zip content on first level
	'sql_templates' => array
	(
		'4.13' => 'eclipsex_cc_contao_4_13.sql',
		'5.3' => 'eclipsex_cc_contao_5_3.sql'
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
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['welcome'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['welcome'][1],
		'href'	=> 'status=welcome',
		'protected'	=> true
	),
	'VALIDATION' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['validation'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['validation'][1],
		'href'	=> 'status=validation',
		'protected'	=> true
	),
	'ACCEPTED' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['accepted'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['accepted'][1],
		'href'	=> 'status=accepted',
		'protected'	=> true
	),
	'NOT_ACCEPTED' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['not_accepted'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['not_accepted'][1],
		'href'	=> 'status=not_accepted',
		'protected'	=> true
	),
	'LOADING' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['loading'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['loading'][1],
		'href'	=> 'status=loading',
	),
	'INSTALLATION.UNZIP' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.unzip'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.unzip'][1],
		'href'	=> 'status=installation&step=unzip',
	),
	'INSTALLATION.COPY_FILES' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.copy_files'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.copy_files'][1],
		'href'	=> 'status=installation&step=copy_files',
	),
	'INSTALLATION.CLEAR_CACHE' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.clear_cache'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.clear_cache'][1],
		'href'	=> 'status=installation&step=clear_cache',
	),
	'INSTALLATION.DB_UPDATE_MODULES' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.db_update_modules'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.db_update_modules'][1],
		'href'	=> 'status=installation&step=db_update_modules',
	),
	'INSTALLATION.SQL_TEMPLATE_WAIT' => array
	(
		'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_wait'][0],
		'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_wait'][1],
		'href'	=> 'status=installation&step=sql_template_wait',
		'protected'	=> true
	),
	'INSTALLATION.SQL_TEMPLATE_IMPORT' => array
	(
	   'label'	=> &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_import'][0],
	   'description' => &$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_import'][1],
	   'href'	=> 'status=installation&step=sql_template_import',
	)
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
$GLOBALS['TL_HOOKS']['parseTemplate'][] = array('PCT\ThemeInstaller\SystemCallbacks','injectScripts');
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('PCT\ThemeInstaller\SystemCallbacks','installationCompletedStatus');
