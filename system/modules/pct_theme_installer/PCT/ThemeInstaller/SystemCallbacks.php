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
 * Class file
 * SystemCallbacks
 */
class SystemCallbacks extends \System
{
	/**
	 * Installation completed when contao quits back to the login screen
	 * 
	 * Called from [initializeSystem] Hook
	 */
	public function installationCompletedStatus()
	{
		if(TL_MODE != 'BE' || \Environment::get('isAjaxRequest'))
		{
			return;
		}
		
		$objUser = \BackendUser::getInstance();
		if((int)$objUser->id > 0)
		{
			return;
		}
		
		// load language files
		\System::loadLanguageFile('default');
			
		$intOffset = 3600;
		
		// session still exists and the sql template has been imported within the time range
		if($_SESSION['PCT_THEME_INSTALLER']['completed'] && \Config::get('exampleWebsite') <= time() + $intOffset)
		{
			// get the license object
			$strName = $_SESSION['PCT_THEME_INSTALLER']['license']['name'];
			$strTemplate = $_SESSION['PCT_THEME_INSTALLER']['sql'];
			
			// clear the url from the referer and redirect with installation information
			if(\Input::get('referer') != '')
			{
				$this->redirect( \Controller::addToUrl('&installation_completed='.$strName.'&sql='.$strTemplate,false,array('referer','rt','ref')) );
			}	
		}
		// write a backend information message with the installation information
		else if(\Input::get('installation_completed') != '' && \Config::get('exampleWebsite') <= time() + $intOffset)
		{
			// add backend message
			\Message::addInfo( sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['completeStatusMessage'],\Input::get('installation_completed')) );
			
			// remove the tmp.SQL file
			$strTemplate = \Input::get('sql');
			if(file_exists(TL_ROOT.'/templates/tmp_'.$strTemplate))
			{
				\Files::getInstance()->delete('templates/tmp_'.$strTemplate);
			}
			
			$this->redirect( \Controller::addToUrl('',false,array('installation_completed','sql')) );
		}
	}
	

}