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
		$this->Template->resetUrl = \Backend::addToUrl('status=reset');
		$this->Template->messages = \Message::generate();
		$this->Template->label_key = $GLOBALS['TL_LANG']['pct_theme_installer']['label_key'] ?: 'License / Order number';
		$this->Template->label_email = $GLOBALS['TL_LANG']['pct_theme_installer']['label_email'] ?: 'Order email address';
		$this->Template->placeholder_license = '123';
		$this->Template->placeholder_email = 'email@email.com';
		$this->Template->label_submit = $GLOBALS['TL_LANG']['pct_theme_installer']['label_submit'];
		$this->Template->value_submit = $GLOBALS['TL_LANG']['pct_theme_installer']['value_submit'];
		$this->Template->file_written_response = 'file_written';
		$this->Template->file_target_directory = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'];
		$this->Template->ajax_action = 'theme_installer_loading'; // just a simple action status message
		
		$blnAjax = false;
		if(\Input::get('action') != '')
		{
			$blnAjax = true;
		}
		$this->Template->ajax_running = $blnAjax;
			
		
		$strName = $objLicense->name ?: $objLicense->file->name ?: '';
		
//! status : WELCOME
		
		
		if(\Input::get('status') == 'welcome' && !$_POST)
		{
			$this->Template->status = 'WELCOME';
			
			return;
		}


//! status : RESET


		// clear the session on status reset
		if(\Input::get('status') == 'reset' || \Input::get('status') == '')
		{
			$objLicense = null;
			$objSession->remove('pct_theme_installer');

			// redirect to the beginning
			$this->redirect( \Backend::addToUrl('status=welcome') );
		}


//! status: INSTALLATION | STEP 1: Unpack the zip
	
	
		if(\Input::get('status') == 'installation' && \Input::get('step') == 'unzip')
		{
			// check if file still exists
			if(empty($arrSession['file']) || !file_exists(TL_ROOT.'/'.$arrSession['file']))
			{
				$this->Template->status = 'FILE_NOT_EXISTS';

				// redirect to welcome
				#$this->redirect( \Environment::getInstance()->request.'&status=clear' );

				return;
			}
			
			$this->Template->ajax_action = 'unzip';
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'UNZIP';
			$this->Template->num_step = 1;
			
			$objFile = new \File($arrSession['file'],true);
			$this->Template->file = $objFile;
			
			// the target folder to extract to
			$strTargetDir = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.basename($arrSession['file'], ".zip").'_zip';
			
			if(\Input::get('action') == 'unzip')
			{
				// extract zip
				$objZip = new \ZipArchive;
				if($objZip->open(TL_ROOT.'/'.$objFile->path) === true && !$arrSession['unzipped'])
				{
					$objZip->extractTo(TL_ROOT.'/'.$strTargetDir);
					$objZip->close();

					// flag that the zip file has been extracted					
					$arrSession['unzipped'] = true;
					$objSession->set('pct_theme_installer',$arrSession);
					
					// ajax done
					die('Zip extracted to: '.$strTargetDir);
				} 
				// zip already extracted
				elseif($arrSession['unzipped'] && is_dir(TL_ROOT.'/'.$strTargetDir))
				{
					// ajax done
					die('Zip extracted to: '.$strTargetDir);
				}
				// extraction failed
				else
				{
					$log = sprintf($GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['unzip_error'],$arrSession['file']);
					\System::log($log,__METHOD__,TL_ERROR);
				}
				
				// redirect to the beginning
				#$this->redirect( \Backend::addToUrl('status=installation&step=copy_files') );
			}
			
			return;
		}
//! status: INSTALLATION | STEP 2: Copy files
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'copy_files')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'COPY_FILES';
			$this->Template->num_step = 2;
			
			// the target folder to extract to
			$strTargetDir = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.basename($arrSession['file'], ".zip").'_zip';
			$strFolder = $strTargetDir.'/'.basename($arrSession['file'], ".zip");
			
			if(\Input::get('action') == 'copy_files' && is_dir(TL_ROOT.'/'.$strFolder))
			{
				$scan = scandir(TL_ROOT.'/'.$strFolder);
				
				// check for consistancy of the folder. If the unziped folder does not contain the mandatory files, quit
				if( count(array_intersect($scan, $GLOBALS['PCT_THEME_INSTALLER']['THEMES']['eclipse']['mandatory'])) != count(array_intersect($scan, $GLOBALS['PCT_THEME_INSTALLER']['THEMES']['eclipse']['mandatory'])) )
				{
					$log = sprintf($GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['zip_content_error'],implode(', ', $GLOBALS['PCT_THEME_INSTALLER']['THEMES']['eclipse']['mandatory']));
					\System::log($log,__METHOD__,TL_ERROR);
					
					// ajax done
					die('Content of the extracted file in '.$strFolder.' does not match the mandatory content');
				}
				
				$objFiles = \Files::getInstance();
				$arrIgnore = array('.ds_store');
				
				// folder to copy
				$arrFolders = scan(TL_ROOT.'/'.$strFolder.'/upload');
				
				foreach($arrFolders as $f)
				{
					if(in_array(strtolower($f), $arrIgnore))
					{
						continue;
					}
					
					//-- copy the /upload/files/ folder
					$strSource = $strFolder.'/upload/'.$f;
					$strDestination = $f;
					if($f == 'files')
					{
						$strDestination = \Config::get('uploadPath');
					}
					
					if($objFiles->rcopy($strSource,$strDestination) !== true)
					{
						$arrErrors[] = 'Copy '.$strSource.' to '.$strDestination.' failed';
					}
				}
				
				// log errors
				if(count($arrErrors) > 0)
				{
					
				}
				// no errors
				else
				{
					// write log
					\System::log( sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['copy_files_completed'],$arrSession['file']),__METHOD__,TL_CRON);	
					
					// ajax done
					if($blnAjax && \Environment::get('isAjaxRequest'))
					{
						die('Coping files completed');
					}
				}
			}
			else
			{
				#die('Zip folder '.$strTargetDir.'/'.$strFolder.' does not exist or is not a directory');
			}
			
			return ;
		}
//! status: INSTALLATION | STEP 3.1: SQL template: Wait for user input
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'sql_template_wait')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'SQL_TEMPLATE_WAIT';
			$this->Template->action = 'WAIT';
			$this->Template->num_step = 3.1;
			
			return;
		}
//! status: INSTALLATION | STEP 3.2: SQL template: Confirmed ready for import
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'sql_template_confirmed')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'SQL_TEMPLATE';
			$this->Template->action = 'WAIT';
			$this->Template->num_step = 3.1;
			
			return;
		}


//! status: FILE_LOADED ... FILE_CREATED


		// file loaded
		if($arrSession['status'] == 'FILE_CREATED' && !empty($arrSession['file']))
		{
			// check if file still exists
			if(!file_exists(TL_ROOT.'/'.$arrSession['file']))
			{
				$this->Template->status = 'FILE_NOT_EXISTS';

				// redirect to welcome
				#$this->redirect( \Environment::getInstance()->request.'&status=clear' );

				return;
			}

			
			$this->Template->status = 'FILE_EXISTS';

			$objFile = new \File($arrSession['file'],true);
			$this->Template->file = $objFile;

			// set file path
			$this->strFile = $objFile->path;
			
			// redirect to step: 1 (unzipping) of the installation
			$this->redirect( \Backend::addToUrl('status=installation') );
		}


//! status: VALIDATION: Fetch the license information


		if(\Input::post('license') != '' && \Input::post('email') != '' && \Input::post('FORM_SUBMIT') == $strForm)
		{
			$this->Template->status = 'VALIDATION';

			$arrParams = array
			(
				'license' => \Input::post('license'),
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
			// redirect to the beginning
			$this->redirect( \Backend::addToUrl('status=loading',true) );

			#$this->reload();
		}


//! status: LICENSE = OK -> LOADING... and FILE CREATION


		// if all went good and the license etc. is all valid, we get an secured hash and download will be available
		if(\Input::get('status') == 'loading' && $objLicense->status == 'OK' && !empty($objLicense->hash))
		{
			$this->Template->status = 'ACCEPTED';
			$this->Template->license = $objLicense;
			
			// coming from ajax request
			if(\Input::get('action') == 'loading')
			{
				$arrParams['email'] = $objLicense->email;
				$arrParams['license'] = $objLicense->license;
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
	 * Inject javascript templates in the backend page
	 * @param object
	 * 
	 * Called from [parseTemplate] Hook
	 */
	public function injectScripts($objTemplate)
	{
		if(TL_MODE == 'BE' && $objTemplate->getName() == 'be_main')
		{
			$objScripts = new \BackendTemplate('be_js_pct_theme_installer');
			
			$arrTexts = array
			(
				'hallo' => 'welt',
			);
			$objScripts->texts = json_encode($arrTexts);
			$objTemplate->javascripts .= $objScripts->parse();
		}
	}

}