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
	 * The path to the file
	 * @var string
	 */
	protected $strFile = '';
	

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
		$arrParams = array();
		$objLicense = $arrSession['license'] ? json_decode($arrSession['license']) : null;
		// template vars		
		$strForm = 'pct_theme_installer';
		$this->Template->status = '';
		$this->Template->action = \Environment::getInstance()->request;
		$this->Template->formId = $strForm;
		$this->Template->content = '';
		$this->Template->href = $this->getReferer(true);
		$this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
		$this->Template->button = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->messages = \Message::generate();
		$this->Template->label_key = $GLOBALS['TL_LANG']['pct_theme_installer']['label_key'] ?: 'License / Order number';
		$this->Template->label_email = $GLOBALS['TL_LANG']['pct_theme_installer']['label_email'] ?: 'Order email address';
		$this->Template->placeholder_key = '123';
		$this->Template->placeholder_email = 'email@email.com';
		$this->Template->label_submit = $GLOBALS['TL_LANG']['pct_theme_installer']['label_submit'];
		$this->Template->value_submit = $GLOBALS['TL_LANG']['pct_theme_installer']['value_submit'];
		$this->Template->file_written_response = 'file_written';
		$this->Template->file_target_directory = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'];
		$this->Template->ajax_action = 'theme_installer_loading'; // just a simple action status message
		if(\Input::get('action') == $this->Template->ajax_action && \Environment::get('isAjaxRequest'))
		{
			$this->Template->ajax_running = true;
		}
		
				
//! status : RESET


		// clear the session on status reset
		if(strtolower( \Input::get('status') ?: '' ) == 'reset')
		{
			$objLicense = null;
			$objSession->remove('pct_theme_installer');
			
			// redirect to the beginning
			$this->redirect( \Backend::addToUrl('',true,array('status')) );
		}

		
//! status: FILE_LOADED ... FILE_CREATED


		// file loaded
		if($arrSession['status'] == 'FILE_CREATED' && !empty($arrSession['file']))
		{
			// check fi file still exists
			if(!file_exists(TL_ROOT.'/'.$arrSession['file']))
			{
				$this->Template->status = 'FILE_NOT_EXISTS';
				
				// redirect to welcome
				#$this->redirect( \Environment::getInstance()->request.'&status=clear' );
			}
			
			$this->Template->status = 'FILE_EXISTS';
			
			$objFile = new \File($arrSession['file']);
			$this->Template->file = $objFile;
			
			// set file path
			$this->strFile = $objFile->path;
			
			// beginn installation
			#$this->install($this->strFile);
			
			return;
		}
		

//! status: VALIDATION: Fetch the license information 
		
				
		if(\Input::post('key') != '' && \Input::post('email') != '' && \Input::post('FORM_SUBMIT') == $strForm)
		{
			$this->Template->status = 'VALIDATION';
			
			$arrParams = array
			(
				'key' => \Input::post('key'),
				'email' => \Input::post('email')
			);
			$strRequest = $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'?'.http_build_query($arrParams);
		
			// validate the license
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_URL, $strRequest);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			$strResponse = curl_exec($curl);
			curl_close($curl);
			unset($curl);
			
			$objLicense = json_decode($strResponse);
			
			// store the api response in the session
			$arrSession['status'] = $objLicense->status;
			$arrSession['license'] = $strResponse;
			$objSession->set('pct_theme_installer',$arrSession);
			
			// flush post and make session active
			$this->reload();
		}
		
		
//! status: LICENSE = OK -> LOADING... and FILE CREATION
		
		
		// if all went good and the license etc. is all valid, we get an secured hash and download will be available
		if($objLicense->status == 'OK' && !empty($objLicense->hash))
		{
			$this->Template->status = 'ACCEPTED';
			$this->Template->license = $objLicense;
			
			// coming from ajax request
			if(\Input::get('action') == $this->Template->ajax_action)
			{
				$arrParams['email'] = $objLicense->email;
				$arrParams['key'] = $objLicense->key;
				$arrParams['hash'] = $objLicense->hash;
				$arrParams['sendToAjax'] = 1;
				$strFileRequest = $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'?'.http_build_query($arrParams);
				
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_URL, $strFileRequest);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				$strFileResponse = curl_exec($curl);
				curl_close($curl);
				unset($curl);
			
				// response is a json object and not the file content
				$_test = json_decode($strFileResponse);
					
				if(json_last_error() === JSON_ERROR_NONE)
				{
					$objResponse = json_decode($strFileResponse);
					$arrErrors[] = $objResponse->error;
					// log
					//\System::log('Theme Installer: '. $objResponse->error,__METHOD__,TL_ERROR);
				}
				elseif(!empty($strFileResponse))
				{
					$objFile = new \File($GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.$objLicense->file->name);
					$objFile->write( $strFileResponse );
					$objFile->close();
					
					$arrSession['status'] = 'FILE_CREATED';
					$arrSession['file'] = $objFile->path;
					$objSession->set('pct_theme_installer',$arrSession);
				
					// tell ajax that the file has been written
					die($this->Template->file_written_response);
					
					#// flush post and make session active
					#$this->reload();
				}
			}
		}
			
	}


	/**
	 * Install a demo
	 * @param string Name of the demo
	 * @param integer Optional the ID of the contao theme record to install frontend modules in
	 * @return integer Id of the new root page created or -1 for an error
	 */
	public function install()
	{
		$objFile = new \File( $this->strFile );
	}
}