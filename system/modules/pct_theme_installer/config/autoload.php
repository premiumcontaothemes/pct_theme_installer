<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package pct_theme_installer
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */
 
/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'PCT',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'PCT\ThemeInstaller' 							=> 'system/modules/pct_theme_installer/PCT/ThemeInstaller.php',
	'PCT\ThemeInstaller\SystemCallbacks'			=> 'system/modules/pct_theme_installer/PCT/ThemeInstaller/SystemCallbacks.php',
));

if(version_compare(VERSION, '3.5','<='))
{
	ClassLoader::addClasses(array
	(
		'PCT\ThemeInstaller\Contao3\BackendInstall'				=> 'system/modules/pct_theme_installer/PCT/ThemeInstaller/Contao3/BackendInstall.php',
		'PCT\ThemeInstaller\Contao3\Installer'					=> 'system/modules/pct_theme_installer/PCT/ThemeInstaller/Contao3/Installer.php',
	));
}

if(version_compare(VERSION, '4.4','>='))
{
	ClassLoader::addClasses(array
	(
		'PCT\ThemeInstaller\Contao4\InstallationController'		=> 'system/modules/pct_theme_installer/PCT/ThemeInstaller/Contao4/InstallationController.php',
	));
}

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_pct_theme_installer'					=> 'system/modules/pct_theme_installer/templates',
	'be_js_pct_theme_installer'					=> 'system/modules/pct_theme_installer/templates',
	'pct_theme_installer_breadcrumb'			=> 'system/modules/pct_theme_installer/templates',
));
