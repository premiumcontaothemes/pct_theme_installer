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
namespace PCT;

/**
 * Class file
 * ThemeInstaller
 */
class ThemeInstaller extends \BackendModule
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_pct_theme_installer';


	/**
	 * Generate the module
	 *
	 * @throws \Exception
	 */
	protected function compile()
	{
		\System::loadLanguageFile('pct_theme_installer');

		// @var object Session
		$objSession = \Session::getInstance();
		$arrSession = $objSession->get('pct_theme_installer');
		
		$arrErrors = array();
		$arrParams = array
		(
			'key' => 'super'
		);

		$strRequest = $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'?'.http_build_query($arrParams);
		
		$this->Template->api_url = $strApi_url;
		$this->Template->content = '';
		$this->Template->href = $this->getReferer(true);
		$this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
		$this->Template->button = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->messages = \Message::generate();

		// validate the license
		$_curl = curl_init();
		curl_setopt($_curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($_curl, CURLOPT_URL, $strRequest);
		curl_setopt($_curl, CURLOPT_HEADER, 0);
		$strResponse = curl_exec($_curl);
		curl_exec($_curl);
		curl_close($_curl);

		$objResponse = json_decode($strResponse);
		
		$this->Template->content = $strResponse;
		
		// if all went good and the license etc. is all valid we get an secured hash and download will be available
		if($objResponse->status == 'OK' && !empty($objResponse->hash))
		{
			$arrParams['hash'] = $objResponse->hash;
			$strFileRequest = $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'?'.http_build_query($arrParams);
			
			$_curl = curl_init();
			curl_setopt($_curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($_curl, CURLOPT_URL, $strFileRequest);
			curl_setopt($_curl, CURLOPT_HEADER, 0);
			$strFileResponse = curl_exec($_curl);
			curl_exec($_curl);
			curl_close($_curl);
			
			// response is a json object and not the file content
			$_test = json_decode($strFileResponse);
			if(json_last_error() === JSON_ERROR_NONE)
			{
				throw new \Exception('--- STOP ---');
				$objResponse = json_decode($strFileResponse);
				$arrErrors[] = $objResponse->error;
				// log
				//\System::log('Theme Installer: '. $objResponse->error,__METHOD__,TL_ERROR);
			}
			elseif(!empty($strFileResponse))
			{
				$objFile = new \File($objResponse->file->name);
				$objFile->write( $strFileResponse );
				$objFile->close();
				
				$this->Template->content .= '<p>File created: '.$objFile->path.'</p>';
		
			}
			
		}
	}


	/**
	 * Install a demo
	 * @param string Name of the demo
	 * @param integer Optional the ID of the contao theme record to install frontend modules in
	 * @return integer Id of the new root page created or -1 for an error
	 */
	public function install($strTheme,$intContaoTheme=0)
	{

	}
}