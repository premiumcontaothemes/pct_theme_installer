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
	 * Template for the breadcrumb
	 * @var string
	 */
	protected $strTemplateBreadcrumb = 'pct_theme_installer_breadcrumb';
	 
	/**
	 * The name of the theme
	 * @var string
	 */
	protected $strTheme = '';
	
	/**
	 * The session name
	 * @var string
	 */
	protected $strSession = 'pct_theme_installer';


	/**
	 * Generate the module
	 *
	 * @throws \Exception
	 */
	protected function compile()
	{
		\System::loadLanguageFile('pct_theme_installer');
		\System::loadLanguageFile('exception');

		// @var object Session
		$objSession = \Session::getInstance();
		if(version_compare(VERSION, '4','>='))
		{
			$objSession = \System::getContainer()->get('session');
		}
		
		$objDatabase = \Database::getInstance();
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
		$this->Template->breadcrumb = $this->getBreadcrumb(\Input::get('status'), \Input::get('step'));
		$this->Template->href = $this->getReferer(true);
		$this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
		$this->Template->button = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->resetUrl = \Backend::addToUrl('status=reset');
		$this->Template->messages = \Message::generate();
		$this->Template->label_key = $GLOBALS['TL_LANG']['pct_theme_installer']['label_key'] ?: 'License / Order number';
		$this->Template->label_email = $GLOBALS['TL_LANG']['pct_theme_installer']['label_email'] ?: 'Order email address';
		$this->Template->placeholder_license = '';
		$this->Template->placeholder_email = '';
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
		
		
//! status : SESSION_LOST
		if(empty($objLicense) && !in_array(\Input::get('status'),array('welcome','reset')))
		{
			$this->Template->status = 'SESSION_LOST';
			$this->Template->content = $GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['session_lost'];
			$this->Template->breadcrumb = '';
			
			// redirect to the beginning
			$this->redirect( \Backend::addToUrl('status=reset',true) );
			
			return;
		}
	
		// the theme or module name of this lizence
		$this->strTheme = $objLicense->name ?: $objLicense->file->name ?: '';
		if($objLicense->file->name)
		{
			$this->strTheme = basename($objLicense->file->name,'.zip');
		}

		
//! status : WELCOME
		
		
		if(\Input::get('status') == 'welcome' && !$_POST)
		{
			$this->Template->status = 'WELCOME';
			$this->Template->breadcrumb = '';	
			return;
		}


//! status : RESET


		// clear the session on status reset
		if(\Input::get('status') == 'reset' || \Input::get('status') == '')
		{
			$objLicense = null;
			$objSession->remove('pct_theme_installer');

			// redirect to the beginning
			$this->redirect( \Backend::addToUrl('status=welcome',true,array('step')) );
		}


//! status : COMPLETE (probably never been called)

				
		if(\Input::get('status') == 'complete')
		{
			$this->Template->status = 'COMPLETE';
			return;
		}


//! status : ACCESS_DENIED


		if($objLicense->status == 'ACCESS_DENIED' || \Input::get('status') == 'access_denied')
		{
			$this->Template->status = 'ACCESS_DENIED';
			
			return;
		}

		
//! status: INSTALLATION | no step -> reset

		
		if(\Input::get('status') == 'installation' && \Input::get('step') == '')
		{
			// redirect to the beginning
			$this->redirect( \Backend::addToUrl('status=reset',true,array('step')) );
		}


//! status: INSTALLATION | STEP 1.0: Unpack the zip

	
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
			
			// check the file size
			#if($objFile->__get('size') < 30000)
			#{
			#	// log that file is too small
			#	\System::log('The file '.$objFile->path.' is too small. Please retry or contact us.',__METHOD__,TL_ERROR);
			#	
			#	$this->redirect( \Backend::addToUrl('status=reset',true,array('step')) );
			#	return;
			#}
			
			// the target folder to extract to
			$strTargetDir = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.basename($arrSession['file'], ".zip").'_zip';
			
			if(\Input::get('action') == 'run')
			{
				// extract zip
				$objZip = new \ZipArchive;
				if($objZip->open(TL_ROOT.'/'.$objFile->path) === true && !$arrSession['unzipped'])
				{
					$objZip->extractTo(TL_ROOT.'/'.$strTargetDir);
					$objZip->close();

					// flag that the zip file has been extracted					
					$arrSession['unzipped'] = true;
					$objSession->set($this->strSession,$arrSession);
					
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
//! status: INSTALLATION | STEP 2.0: Copy files
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'copy_files')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'COPY_FILES';
			$this->Template->num_step = 2;
			
			// the target folder to extract to
			$strTargetDir = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.basename($arrSession['file'], ".zip").'_zip';
			$strFolder = $strTargetDir.'/'.basename($arrSession['file'], ".zip");
			
			if(\Input::get('action') == 'run' && is_dir(TL_ROOT.'/'.$strFolder))
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
//! status: INSTALLATION | STEP 3.0 : Clear internal caches
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'clear_cache')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'CLEAR_CACHE';
			
			if(\Input::get('action') == 'run')
			{
				// clear internal cache of Contao 3.5
				if(version_compare(VERSION, '3.5','<=') && (boolean)\Config::get('bypassCache') === false)
				{
					$objAutomator = new \Contao\Automator;
					$objAutomator->purgeInternalCache();
					
					die('Internal cache cleared');
				}
				// clear Symphony cache of Contao 4.4
				else if(version_compare(VERSION, '4.4','>='))
				{
					$objContainer = \System::getContainer();
					$strCacheDir = \StringUtil::stripRootDir($objContainer->getParameter('kernel.cache_dir'));
					$strRootDir = $objContainer->getParameter('kernel.project_dir');
					$strWebDir = $objContainer->getParameter('contao.web_dir');
					$arrBundles = $objContainer->getParameter('kernel.bundles');
					
					// @var object Contao\Automator
					$objAutomator = new \Contao\Automator;
					// generate symlinks to /assets, /files, /system
					$objAutomator->generateSymlinks();
					// generate bundles symlinks
					$objSymlink = new \Contao\CoreBundle\Util\SymlinkUtil;
					$arrBundles = array('calendar','comments','core','faq','news','newsletter');
					foreach($arrBundles as $bundle)
					{
						$from = $strRootDir.'/vendor/contao/'.$bundle.'-bundle';
						$to = $strWebDir.'/bundles/contao'.$bundle;
						$objSymlink::symlink($from, $to,$strRootDir);
					}
					
					// clear the internal cache
					$objAutomator->purgeInternalCache();
					// rebuild the internal cache
					$objAutomator->generateInternalCache();
					// purge the whole folder
					\Files::getInstance()->rrdir($strCacheDir,true);
					
					// try to rebuild the symphony cache
					$objInstallationController = new \PCT\ThemeInstaller\InstallationController;
					$objInstallationController->call('purgeSymfonyCache');
					$objInstallationController->call('warmUpSymfonyCache');
					
					die('Symlinks created and Symphony cache cleared');
				}
			}
			
			return;
		}		
		
//! status: INSTALLATION | STEP 4.0 : DB Update for modules
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'db_update_modules')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'DB_UPDATE_MODULES';
			$this->Template->num_step = 3.1;
			
			$arrErrors = array();
			
			try
			{
				// Contao 3.5
				if(version_compare(VERSION, '3.5','<='))
				{
					// @var object \Contao\Database\Installer
					$objInstaller = new \PCT\ThemeInstaller\Installer_35;
					// let Contao generate the database update form
					#$strSqlForm = $objInstaller->generateSqlForm() ?: '';
					// @var object \PCT\ThemeInstaller\BackendInstall to simulate the install tool
					#$objBackendInstall = new \PCT\ThemeInstaller\BackendInstall;
					$objDatabase = \Database::getInstance();
					
					// compile sql
					$arrSQL = array_diff_key($objInstaller->call('compileCommands'),array('DELETE','DROP','ALTER_DROP'));
					if(!empty($arrSQL) && is_array($arrSQL)) 
					{
			    		$this->Template->sql = $arrSQL;
			    		
			    		$arrStatements = array();
			    	  	foreach($arrSQL as $operation => $sql)
			         	{
				         	// never run operations
				         	if(in_array($operation, array('DELETE','DROP','ALTER_DROP')))
				         	{
					         	continue;
				         	}
				         	
				         	foreach($sql as $statement)
				         	{
					        	$objDatabase->prepare($statement)->execute();
				        	}
			        	}
				    }
				}
				// Contao 4.4 >=
				else if(version_compare(VERSION, '4.4','>='))
				{
					// @var object \PCT\ThemeInstaller\InstallationController
					#$objInstaller = new \PCT\ThemeInstaller\InstallationController;
					$objContainer = \System::getContainer();
					$objInstaller = $objContainer->get('contao.installer');
					// compile sql
					$arrSQL = $objInstaller->getCommands();
					if(!empty($arrSQL) && is_array($arrSQL)) 
					{
			    	  	foreach($arrSQL as $operation => $sql)
			         	{
				         	// never run operations
				         	if(in_array($operation, array('DELETE','DROP','ALTER_DROP')))
				         	{
					         	continue;
				         	}

				         	foreach($sql as $hash => $statement)
				         	{
					        	$objInstaller->execCommand($hash);
				        	}
			        	}
				    }
				}			
			}
			catch(\Exception $e)
			{
				$arrErrors[] = $e->getMessage();
			}
			
			if(count($arrErrors) > 0)
			{
				\System::log('Database update returned errors: '.implode(', ', $arrErrors));
				
				$this->Template->errors = implode(', ', $arrErrors);
			}
			
						
			return;
		}
//! status: INSTALLATION | STEP 5.0 : SQL_TEMPLATE_WAIT : Wait for user input
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'sql_template_wait')		
		{
			// get the template by contao version
			$strTemplate = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][$this->strTheme]['sql_templates'][VERSION];
			
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'SQL_TEMPLATE_WAIT';
			$this->Template->sql_template_info = sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['sql_template_info'],$strTemplate);
			return;
		}
//! status: INSTALLATION | STEP 6.0 : SQL_TEMPLATE_IMPORT : Import the sql file
		else if(\Input::get('status') == 'installation' && \Input::get('step') == 'sql_template_import')		
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'SQL_TEMPLATE_IMPORT';
			// get the template by contao version
			$strTemplate = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][$this->strTheme]['sql_templates'][VERSION];
			
			if(empty($strTemplate))
			{
				$this->Template->error = $GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['sql_not_found'];
				return;
			}
			
			// create a tmp copy
			$strTmpTemplate = 'tmp_'.$strTemplate;
			$strOrigTemplate = $strTemplate;
			if(\Files::getInstance()->copy('templates/'.$strTemplate,'templates/tmp_'.$strTemplate))
			{
				$file = fopen(TL_ROOT.'/templates/tmp_'.$strTemplate,'r');
				
				$str = '';
				while(!feof($file))
				{
					$line = fgets($file);
					if(strlen(strpos($line, 'INSERT INTO `tl_user`')) > 0)
					{
						continue;
					}
					
					$str .= $line;
				}
				fclose($file);
				unset($file);
				
				// fetch tl_user information
				$objUsers = $objDatabase->prepare("SELECT * FROM tl_user")->execute();
				while($objUsers->next())
				{
					$str .= $objDatabase->prepare("INSERT INTO `tl_user` %s")->set( $objUsers->row() )->__get('query') . "\n";
				}

				$objFile = new \File('templates/tmp_'.$strTemplate);
				$objFile->write($str);
				$objFile->close();
				
				unset($str);
			
				$strTemplate = $strTmpTemplate;
			}
			
			// Eclipse + CustomCatalog sqls
			$strTemplate = 'pct_eclipse_cc.sql';
			$strFileCC = TL_ROOT.'/'.$GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/eclipse_cc_zip/eclipse_cc/'.$strTemplate;
			if(\Input::get('action') == 'run' && $this->strTheme == 'eclipse_cc' && file_exists($strFileCC))
			{
				$file = file($strFileCC);
				
				// CREATE
				$sql = preg_grep('/^CREATE /', $file);
				foreach($sql as $query)
				{
					// skip tables that already exist
					if(preg_match('/`(.*?)\`/', $query,$result))
					{
						if($objDatabase->tableExists($result[1]) === true)
						{
							continue;
						}
					}
					else {continue;}
				}
				
				// TRUNCATE and INSERT	
				$skipTables = array('tl_user','tl_user_group','tl_session','tl_repository_installs','tl_repository_instfiles','tl_undo','tl_log');
				$sql = preg_grep('/^INSERT /', $file);
				
				$truncated = array();
				foreach($sql as $query)
				{
					// skip tables that already exist
					if(preg_match('/`(.*?)\`/', $query,$result))
					{
						$t = $result[1];
						
						// truncate table and insert
						if($objDatabase->tableExists($t) === true && !in_array($t, $skipTables))
						{
							try
							{
								if(!in_array($t, $truncated))
								{
									// truncate the table
									$objDatabase->execute('TRUNCATE TABLE '.$t);
									// mark as truncated
									$truncated[] = $t;
								}
							
								// refill the table
								$objDatabase->query($query);
							}
							catch(\Exception $e)
							{
								$arrErrors[] = $e->getMessage();
							}
						}
					}
					else {continue;}
				}
				
				unset($skipTables);
				unset($file);
				unset($sql);
				unset($truncated);
				
				if(!empty($arrErrors))
				{
					\System::log('Theme installation finished with errors: '.implode(', ', $arrErrors),__METHOD__,TL_ERROR);
					$this->redirect( \Backend::addToUrl('status=error',true,array('step','action')) );
				}
				
				// mark as being completed
				$_SESSION['PCT_THEME_INSTALLER']['completed'] = true;
				$_SESSION['PCT_THEME_INSTALLER']['license']['name'] = $objLicense->name;
				$_SESSION['PCT_THEME_INSTALLER']['sql'] = $strOrigTemplate;
				
				// log out
				$objUser = \BackendUser::getInstance();
				$objUser->logout();
				
				return;
			}
			
			if(\Input::get('action') == 'run')
			{
				if(version_compare(VERSION, '3.5','<='))
				{
					// @var object \PCT\ThemeInstaller\BackendInstall to simulate the install tool
					$objBackendInstall = new \PCT\ThemeInstaller\BackendInstall;
					// simulate user form submit
					\Input::setPost('template',$strTemplate);
					\Input::setPost('FORM_SUBMIT','tl_tutorial');
					// let the install tool import the sql templates
					try
					{
						// mark as being completed
						$_SESSION['PCT_THEME_INSTALLER']['completed'] = true;
						$_SESSION['PCT_THEME_INSTALLER']['license']['name'] = $objLicense->name;
						$_SESSION['PCT_THEME_INSTALLER']['sql'] = $strOrigTemplate;
						
						$objBackendInstall->call('importExampleWebsite');
					}
					catch(\Exception $e)
					{
						unset($_SESSION['PCT_THEME_INSTALLER']);
					}
				}
				else if(version_compare(VERSION, '4.4','>='))
				{
					$objContainer = \System::getContainer();
					$objInstall = $objContainer->get('contao.install_tool');
					// let the install tool import the sql templates
					try
					{
						// mark as being completed
						$_SESSION['PCT_THEME_INSTALLER']['completed'] = true;
						$_SESSION['PCT_THEME_INSTALLER']['license']['name'] = $objLicense->name;
						$_SESSION['PCT_THEME_INSTALLER']['sql'] = $strOrigTemplate;
						
						$objInstall->importTemplate($strTemplate);
						$objInstall->persistConfig('exampleWebsite', time());
					}
					catch(\Exception $e)
					{
						unset($_SESSION['PCT_THEME_INSTALLER']);
					}
				}
			}
			
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
				'key' 		=> \Input::post('license'),
				'email' 	=> \Input::post('email'),
				'domain'	=> \Environment::get('url'),
			);
			
			if(\Input::post('product') != '')
			{
				$arrParams['product'] = \Input::post('product');
			}
			
			$strRequest = $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'/api.php?'.http_build_query($arrParams);
			
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
			$this->redirect( \Backend::addToUrl('status=ready',true) );
		}


//! status: CHOOSE_PRODUCT, waiting for user to choose the product

		if(\Input::get('status') == 'choose_product' && $objLicense->status == 'OK')
		{
			$this->Template->status = 'CHOOSE_PRODUCT';
			$this->Template->license = $objLicense;
			$this->Template->breadcrumb = '';
			
			// registration error
			if($objLicense->registration->hasError)
			{
				$this->Template->hasRegistrationError = true;
			}
			
			return;
		}		



//! status: READY, waiting for installation GO

		
		if(\Input::get('status') == 'ready' && $objLicense->status == 'OK')
		{
			$this->Template->status = 'READY';
			$this->Template->license = $objLicense;
			
			// registration error
			if($objLicense->registration->hasError)
			{
				$this->Template->hasRegistrationError = true;
			}
			
			// has more than one product to choose
			if(!empty($objLicense->products))
			{
				$this->redirect( \Backend::addToUrl('status=choose_product',true) );
			}
			
			if(\Input::post('install') != '' && \Input::post('FORM_SUBMIT') == $strForm)
			{
				$this->redirect( \Backend::addToUrl('status=loading',true) );
			}
			
			return;
		}		


//! status: LICENSE = OK -> LOADING... and FILE CREATION


		// if all went good and the license etc. is all valid, we get an secured hash and download will be available
		if(\Input::get('status') == 'loading' && $objLicense->status == 'OK' && !empty($objLicense->hash))
		{
			$this->Template->status = 'LOADING';
			$this->Template->license = $objLicense;
			
			// coming from ajax request
			if(\Input::get('action') == 'run')
			{
				$arrParams['email'] = $objLicense->email;
				$arrParams['key'] = $objLicense->key;
				$arrParams['hash'] = $objLicense->hash;
				$arrParams['domain'] = $objLicense->domain;
				$arrParams['sendToAjax'] = 1;
				$arrParams['product'] = $objLicense->file->id;
				
				$strFileRequest = $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'/api.php?'.http_build_query($arrParams);

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
			
			return;
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
	
	
	/**
	 * Generate a breadcrumb
	 */
	public function getBreadcrumb($strStatus='',$strStep='')
	{
		$strCurrent = $strStatus.($strStep != '' ? '.'.$strStep : '');
		
		$arrItems = array();
		$i = 0;
		
		$objSession = \Session::getInstance();
		$arrSession = $objSession->get($this->strSession);
		
		// store the processed steps
		if(!is_array($arrSession['BREADCRUMB']['completed']))
		{
			$arrSession['BREADCRUMB']['completed'] = array();
		}
		
		foreach($GLOBALS['PCT_THEME_INSTALLER']['breadcrumb_steps'] as $k => $data)
		{
			$status = strtolower($k);
			
			// css class
			$class = array('item',$status);
			if($data['protected'])
			{
				$class[] = 'hidden';
			}
			
			($i%2 == 0 ? $class[] = 'even' : $class[] = 'odd');
			($i == 0 ? $class[] = 'first' : '');
			($i == count($GLOBALS['PCT_THEME_INSTALLER']['breadcrumb_steps']) - 1 ? $class[] = 'last' : '');
			
			if(!$data['label'])
			{
				$data['label'] = $k;
			}
			
			// title
			if(!$data['title'])
			{
				$data['title'] = $data['label'];
			}
			
			// active
			if($strCurrent == $status)
			{
				$data['isActive'] = true;
				$class[] = 'tl_green';
				$class[] = 'active';
				
				$arrSession['BREADCRUMB']['completed'][$k] = true;
			}
			
			// completed
			if($arrSession['BREADCRUMB']['completed'][$k] === true && $strCurrent != $status)
			{
				$data['completed'] = true;
				$class[] = 'completed';
			}
			
			// sill waiting
			if(!$data['isActive'] && !$data['completed'])
			{
				$data['pending'] = true;
				$class[] = 'pending';
			}
			
			$data['href'] = \Controller::addToUrl($data['href'].'&rt='.REQUEST_TOKEN,true,array('step'));
			$data['class'] = implode(' ', array_unique($class));
			
			$arrItems[ $k ] = $data;
			
			$i++;
		}
		
		// update session
		$objSession->set($this->strSession,$arrSession);
		
		// @var object
		$objTemplate = new \BackendTemplate($this->strTemplateBreadcrumb);
		$objTemplate->items = $arrItems;
		
		return $objTemplate->parse();
	}
}