<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @copyright Tim Gatzky 2018, Premium Contao Themes
 * @author  Tim Gatzky <info@tim-gatzky.de>
 * @package  pct_theme_installer
 */

/**
 * Namespace
 */
namespace PCT\ThemeInstaller;

/**
 * Imports 
 */
use Contao\System;
use Contao\Environment;
use Contao\BackendUser;
use Contao\Input;
use Contao\Config;
use Contao\Controller;
use Contao\Message;
use Contao\Files;
use Contao\Session;

/**
 * Class file
 * SystemCallbacks
 */
class SystemCallbacks extends System
{
	/**
	 * Installation completed when contao quits back to the login screen
	 * 
	 * Called from [initializeSystem] Hook
	 */
	public function installationCompletedStatus()
	{
		if(version_compare(VERSION, '4.4', '<') && Config::get('adminEmail') == '')
		{
			return;
		}
		
		if(TL_MODE != 'BE' || Environment::get('isAjaxRequest'))
		{
			return;
		}
		
		$objUser = BackendUser::getInstance();
		if((int)$objUser->id > 0)
		{
			return;
		}
		
		// load language files
		System::loadLanguageFile('default');
		
		$objSession = Session::getInstance();
		if(version_compare(VERSION, '4','>='))
		{
			$objSession = System::getContainer()->get('session');
		}
		
		$arrSession = $objSession->get('PCT_THEME_INSTALLER');
			
		#// session still exists
		#if(!empty($arrSession))
		#{
		#   // remove the session
		#   $objSession->remove('PCT_THEME_INSTALLER');
		#   unset($_SESSION['PCT_THEME_INSTALLER']);
		#   
		#   $strName = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][ $arrSession['theme'] ]['label'] ?: $arrSession['theme'];
		#  
		#   // add backend message
		#   \Message::addInfo( sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['completeStatusMessage'],$strName) );
		#  
		#   // redirect to make backend message appear under Contao lower than 4.4
		#   if(version_compare(VERSION, '4.4','<') && version_compare(VERSION, '3.5','>='))
		#   {
		#	  $url = \Contao\StringUtil::decodeEntities( Controller::addToUrl('completed=1&theme='.$arrSession['theme'].'&sql='.$arrSession['sql'],false,array('referer','rt','ref')) );
		#	  $this->redirect($url);
		#   }
		#   
		#   return;
		#}
		
		if(Input::get('welcome') != '')
		{
			// check if theme data exists
			if(!isset($GLOBALS['PCT_THEME_INSTALLER']['THEMES'][ Input::get('welcome') ]))
			{
				$url = \Contao\StringUtil::decodeEntities( Controller::addToUrl('',false,array('welcome')) );
				$this->redirect($url);
			}
			
			$strName = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][ Input::get('welcome') ]['label'] ?: Input::get('welcome');
			
			// add backend message
			Message::addInfo( sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['completeStatusMessage'],$strName) );
			
			return;
		}
		
		if((int)Input::get('completed') == 1 && Input::get('theme') != '')
		{
			// remove the tmp.SQL file
			$strTemplate = Input::get('sql');
			if(file_exists(TL_ROOT.'/templates/tmp_'.$strTemplate))
			{
				Files::getInstance()->delete('templates/tmp_'.$strTemplate);
			}
			
			#$objSession->set('PCT_THEME_INSTALLER',array('theme'=>Input::get('theme')));
			#$_SESSION['PCT_THEME_INSTALLER']['theme'] = Input::get('theme');
			
			// redirect to a clean login page and make the message appear
			#$url = '';
			#$remove_params = array('referer','rt','ref');
			#if(version_compare(VERSION, '3.5','>=') && version_compare(VERSION, '4.4','<'))
			#{
			#	$remove_params[] = 'completed';
			#	$remove_params[] = 'theme';
			#	$remove_params[] = 'sql';
			#}
			#// contao 4.4 >=
			#else if(version_compare(VERSION, '4.4','>='))
			#{
			#	$remove_params[] = 'completed';
			#	$remove_params[] = 'sql';
			#}
			
			$url = \Contao\StringUtil::decodeEntities( Controller::addToUrl('welcome='.Input::get('theme'),false,array('completed','theme','sql','referer','rt','ref')) );
			$this->redirect($url);
		}
	}
	
	
	/**
	 * Inject javascript templates in the backend page
	 * @param object
	 *
	 * Called from [parseTemplate] Hook
	 */
	public function injectScripts($objTemplate)
	{
		if(TL_MODE == 'BE' && $objTemplate->getName() == 'be_main')
		{
			$objScripts = new \Contao\BackendTemplate('be_js_pct_theme_installer');
			$objTemplate->javascripts .= $objScripts->parse();
		}
	}
}