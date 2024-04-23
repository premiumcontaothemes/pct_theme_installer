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
 
// path relative from composer directory
$path = \Contao\System::getContainer()->getParameter('kernel.project_dir').'/vendor/composer/../../system/modules/pct_theme_installer';


/**
 * Register the classes
 */
$classMap = array
(
	'PCT\ThemeInstaller' 							=> $path.'/PCT/ThemeInstaller.php',
	'PCT\ThemeInstaller\SystemCallbacks'			=> $path.'/PCT/ThemeInstaller/SystemCallbacks.php',
	'PCT\ThemeInstaller\InstallerHelper'			=> $path.'/PCT/ThemeInstaller/InstallerHelper.php',
);

$loader = new \Composer\Autoload\ClassLoader();
$loader->addClassMap($classMap);
$loader->register();


/**
 * Register the templates
 */
\Contao\TemplateLoader::addFiles(array
(
	'be_pct_theme_installer'					=> 'system/modules/pct_theme_installer/templates',
	'be_js_pct_theme_installer'					=> 'system/modules/pct_theme_installer/templates',
	'pct_theme_installer_breadcrumb'			=> 'system/modules/pct_theme_installer/templates',
));
