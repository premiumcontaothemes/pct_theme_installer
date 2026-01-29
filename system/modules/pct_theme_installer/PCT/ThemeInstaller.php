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
 * Imports 
 */
use Contao\Automator;
use Contao\System;
use Contao\Environment;
use Contao\Database;
use Contao\Backend;
use Contao\Input;
use Contao\Config;
use Contao\Controller;
use Contao\Message;
use Contao\Files;
use Contao\File;
use Contao\StringUtil;
use Contao\Session;
use Contao\BackendTemplate;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\Folder;
use PCT\ThemeInstaller\InstallerHelper;

/**
 * Class file
 * ThemeInstaller
 */
class ThemeInstaller extends \Contao\BackendModule
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
	 */
	protected function compile()
	{
		\error_reporting(E_ERROR | E_PARSE | E_NOTICE);
		
		System::loadLanguageFile('pct_theme_installer');
		System::loadLanguageFile('exception');

		// @var object Session
		$objContainer = System::getContainer();
		$objSession = $objContainer->get('request_stack')->getSession();
		$arrSession = $objSession->get($this->strSession);
		$rootDir = $objContainer->getParameter('kernel.project_dir');
		$full_version = ContaoCoreBundle::getVersion();
		$version = substr( $full_version, 0, strrpos($full_version, '.') );
		
		$objDatabase = Database::getInstance();
		$arrErrors = array();
		$arrParams = array();
		$objLicense = $arrSession['license'] ?? null;
		if( isset($arrSession['license']) && \is_string($arrSession['license']) && empty($arrSession['license']) === false)
		{
			$objLicense = \json_decode($arrSession['license']);
		}
		
		// template vars
		$strForm = 'pct_theme_installer';
		$this->Template->status = '';
		$this->Template->action = Environment::get('request');
		$this->Template->formId = $strForm;
		$this->Template->content = '';
		$this->Template->breadcrumb = $this->getBreadcrumb(Input::get('status'), Input::get('step'));
		$this->Template->href = $this->getReferer(true);
		$this->Template->title = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
		$this->Template->button = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->resetUrl = Backend::addToUrl('status=reset');
		$this->Template->messages = Message::generate();
		$this->Template->label_key = isset($GLOBALS['TL_LANG']['pct_theme_installer']['label_key']) ?: 'License / Order number';
		$this->Template->label_email = isset($GLOBALS['TL_LANG']['pct_theme_installer']['label_email']) ?: 'Order email address';
		$this->Template->placeholder_license = '';
		$this->Template->placeholder_email = '';
		$this->Template->label_submit = isset($GLOBALS['TL_LANG']['pct_theme_installer']['label_submit']) ?: 'Submit';
		$this->Template->value_submit = isset($GLOBALS['TL_LANG']['pct_theme_installer']['value_submit']) ?: 'submit';
		$this->Template->file_written_response = 'file_written';
		$this->Template->file_target_directory = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'];
		$this->Template->ajax_action = 'theme_installer_loading'; // just a simple action status message
		$this->Template->test_license = $GLOBALS['PCT_THEME_INSTALLER']['test_license'];
		$this->Template->license = $objLicense;

		$blnAjax = false;
		if(Input::get('action') != '' && Environment::get('isAjaxRequest'))
		{
			$blnAjax = true;
		}
		$this->Template->ajax_running = $blnAjax;

		

//! status : SESSION_LOST


		if(empty($objLicense) && !in_array(Input::get('status'),array('welcome','reset','error','version_conflict')))
		{
			$this->Template->status = 'SESSION_LOST';
			$this->Template->content = $GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['session_lost'];
			$this->Template->breadcrumb = '';

			// redirect to the beginning
			$this->redirect( Backend::addToUrl('status=reset') );

			return;
		}

		// the theme or module name of this lizence
		$this->strTheme = isset($objLicense->name) ?? $objLicense->file->name ?? '';
		if( isset($objLicense->file) && $objLicense->file->name)
		{
			$this->strTheme = basename($objLicense->file->name,'.zip');
			$this->Template->theme = $this->strTheme;
		}


//! status : VERSION_CONFLICT


		$blnAllowed = false;
		if( version_compare($version, '5.2','>=') )
		{
			$blnAllowed = true;
		}

		// support current LTS 4.9, 4.12
		if(Input::get('status') != 'version_conflict' && $blnAllowed === false)
		{
			$this->redirect( Backend::addToUrl('status=version_conflict',true,array('step','action')) );
		}
		
		if(Input::get('status') == 'version_conflict')
		{
			$this->Template->status = 'VERSION_CONFLICT';
			$this->Template->errors = array($GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['version_conflict'] ?: 'Please use the LTS version 4.9');
			return;
		}

	
//! status : COMPLETED


		if(Input::get('status') == 'completed')
		{
			$arrSession = $objSession->get('PCT_THEME_INSTALLER');
			// redirect to contao login
			$url = StringUtil::decodeEntities( Environment::get('base').'contao/login?installation_completed=1&theme='.Input::get('theme').'&sql='.$arrSession['sql']);
			
			$this->redirect($url);

			return;
		}


//! status : RESET


		// clear the session on status reset
		if(Input::get('status') == 'reset' || Input::get('status') == '')
		{
			$objLicense = null;
			$objSession->remove('pct_theme_installer');

			// redirect to the beginning
			$this->redirect( Backend::addToUrl('status=welcome',true,array('step')) );
		}


//! status : THEME ALREADY INSTALLED


        $bundles = array_keys( System::getContainer()->getParameter('kernel.bundles'));
        if( Input::get('status') != 'error' && ( \in_array('pct_theme_settings',$bundles) || \in_array('pct_theme_templates',$bundles) ) )
        {
            $this->Template->status = 'ALREADY_INSTALLED';

            // log
            $objContainer->get('monolog.logger.contao.error')->info('Theme Installer: Theme already installed');

            // track error              
            $arrSession['errors'] = array($GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['already_installed']?? 'Theme already installed');
            $objSession->set($this->strSession,$arrSession);

            // redirect
            $this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );
        }


//! status : NOT_SUPPORTED

		
		if( $objLicense !== null && $objLicense->status == 'NOT_SUPPORTED' && Input::get('status') != 'not_supported')
		{
			// redirect to the not supported page
			$this->redirect( Backend::addToUrl('status=not_supported',true,array('step')) );
		}
		
		if(Input::get('status') == 'not_supported')
		{
			$this->Template->status = 'NOT_SUPPORTED';
			return;
		}


//! status : ERROR


		if(Input::get('status') == 'error')
		{
			$this->Template->status = 'ERROR';
			$this->Template->breadcrumb = '';
			$this->Template->errors = $arrSession['errors'];
			return;
		}


//! status : WELCOME


		if(Input::get('status') == 'welcome' && !$_POST)
		{
			$this->Template->status = 'WELCOME';
			$this->Template->breadcrumb = '';
			return;
		}


//! status : COMPLETE (probably never been called)


		if(Input::get('status') == 'complete')
		{
			$this->Template->status = 'COMPLETE';
			return;
		}


//! status : ACCESS_DENIED


		if($objLicense->status == 'ACCESS_DENIED' || Input::get('status') == 'access_denied')
		{
			$this->Template->status = 'ACCESS_DENIED';
			
			return;
		}


//! status: INSTALLATION | no step -> reset


		if(Input::get('status') == 'installation' && Input::get('step') == '')
		{
			// redirect to the beginning
			$this->redirect( Backend::addToUrl('status=reset',true,array('step')) );
		}


		//! status: INSTALLATION | STEP 1.0: Unpack the zip


		if(Input::get('status') == 'installation' && Input::get('step') == 'unzip')
		{
			// check if file still exists
			if(empty($arrSession['file']) || !file_exists($rootDir.'/'.$arrSession['file']))
			{
				$this->Template->status = 'FILE_NOT_EXISTS';

				// log
				$objContainer->get('monolog.logger.contao.error')->info('Theme Installer: File not found');

				// track error				
				$arrSession['errors'] = array('File not found');
				$objSession->set($this->strSession,$arrSession);

				// redirect
				$this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );

				return;
			}

			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'UNZIP';
			
			$objFile = new File($arrSession['file'],true);
			$this->Template->file = $objFile;

			// check the file size
			#if($objFile->__get('size') < 30000)
			#{
			# // log that file is too small
			# System::log('The file '.$objFile->path.' is too small. Please retry or contact us.',__METHOD__,TL_ERROR);
			#
			# $this->redirect( Backend::addToUrl('status=reset',true,array('step')) );
			# return;
			#}

			// the target folder to extract to
			$strTargetDir = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.basename($arrSession['file'], ".zip").'_zip';

			if(Input::get('action') == 'run')
			{
				// extract zip
				$objZip = new \ZipArchive;
				if($objZip->open($rootDir.'/'.$objFile->path) === true && !$arrSession['unzipped'])
				{
					$objZip->extractTo($rootDir.'/'.$strTargetDir);
					$objZip->close();

					// flag that the zip file has been extracted
					$arrSession['unzipped'] = true;
					$objSession->set($this->strSession,$arrSession);

					// ajax done
					die('Zip extracted to: '.$strTargetDir);
				}
				// zip already extracted
				elseif($arrSession['unzipped'] && is_dir($rootDir.'/'.$strTargetDir))
				{
					// ajax done
					die('Zip extracted to: '.$strTargetDir);
				}
				// extraction failed
				else
				{
					$objContainer->get('monolog.logger.contao.error')->info( sprintf($GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['unzip_error'],$arrSession['file']) );
				}

				// redirect to the beginning
				#$this->redirect( Backend::addToUrl('status=installation&step=copy_files') );
			}

			return;
		}
		//! status: INSTALLATION | STEP 2.0: Copy files
		else if(Input::get('status') == 'installation' && Input::get('step') == 'copy_files')
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'COPY_FILES';
			
			// the target folder to extract to
			$strTargetDir = $GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.basename($arrSession['file'], ".zip").'_zip';
			$strFolder = $strTargetDir; #$strTargetDir.'/'.basename($arrSession['file'], ".zip");

			if(Input::get('action') == 'run' && is_dir($rootDir.'/'.$strFolder))
			{
				// backup an existing customize.css
				$blnCustomizeCss = false;
				if(file_exists($rootDir.'/'.Config::get('uploadPath').'/cto_layout/css/customize.css'))
				{
					if( Files::getInstance()->copy(Config::get('uploadPath').'/cto_layout/css/customize.css',$GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/customize.css') )
					{
						$blnCustomizeCss = true;
					}
				}

				$objFiles = Files::getInstance();
				$arrIgnore = array('.ds_store');

				// folder to copy
				$arrFolders = Folder::scan($rootDir.'/'.$strFolder.'/upload');

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
						$strDestination = Config::get('uploadPath') ?: 'files';
					}
					
					if($objFiles->rcopy($strSource,$strDestination) !== true)
					{
						$arrErrors[] = 'Copy "'.$strSource.'" to "'.$strDestination.'" failed';
					}
				}

				// reinstall the customize.css
				if($blnCustomizeCss)
				{
					Files::getInstance()->copy($GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/customize.css',Config::get('uploadPath') ?: 'files'.'/cto_layout/css/customize.css');
				}
				
				// log errors
				if(count($arrErrors) > 0)
				{
					$objContainer->get('monolog.logger.contao.error')->info('Theme Installer: Copy files: '.implode(', ', $arrErrors));

					// track error				
					$arrSession['errors'] = $arrErrors;
					$objSession->set($this->strSession,$arrSession);
					if(!$blnAjax)
					{
						$this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );
					}
					else
					{
						die('Theme Installer: Copy files: '.implode(', ', $arrErrors));
					}
				}
				// no errors
				else
				{
					// write log
					$objContainer->get('monolog.logger.contao.error')->info( sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['copy_files_completed'],$arrSession['file']) );

					// ajax done
					if($blnAjax)
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
		else if(Input::get('status') == 'installation' && Input::get('step') == 'clear_cache')
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'CLEAR_CACHE';

			if(Input::get('action') == 'run')
			{
				// clear internal cache of Contao 4.4
				$objContainer = System::getContainer();
				$strCacheDir = StringUtil::stripRootDir($objContainer->getParameter('kernel.cache_dir'));
				$strRootDir = $objContainer->getParameter('kernel.project_dir');
				$strWebDir = $objContainer->getParameter('contao.web_dir');
				$arrBundles = $objContainer->getParameter('kernel.bundles');
				
				// @var object Contao\Automator
				$objAutomator = new Automator;
				// generate symlinks to /assets, /files, /system
				$objAutomator->generateSymlinks();
				
				// generate bundles symlinks
				#$objSymlink = new \Contao\CoreBundle\Util\SymlinkUtil;
				#$arrBundles = array('calendar','comments','core','faq','news','newsletter');
				#foreach($arrBundles as $bundle)
				#{
				#	$from = $strRootDir.'/vendor/contao/'.$bundle.'-bundle/src/Resources/public';
				#	$to = $strWebDir.'/bundles/contao'.$bundle;
				#	$objSymlink::symlink($from, $to,$strRootDir);
				#}
				
				// purge the whole folder
				Files::getInstance()->rrdir($strCacheDir,true);
				
				die('Symlinks created and Symphony cache cleared');
			
			}

			return;
		}

		//! status: INSTALLATION | STEP 4.0 : DB Update for modules
		else if(Input::get('status') == 'installation' && Input::get('step') == 'db_update_modules')
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'DB_UPDATE_MODULES';
			$arrErrors = array();
			try
			{
				$objInstallHelper = new InstallerHelper;
				$arrSQL = $objInstallHelper->sqlCompileCommandsCallback(array());

				if(!empty($arrSQL) && is_array($arrSQL))
				{
					foreach($arrSQL as $operation => $sql)
					{
						// never run operations
						if(in_array($operation, array('DELETE','DROP','ALTER_DROP','ALTER_CHANGE')))
						{
							continue;
						}
						
						foreach($sql as $statement)
						{
							if( \strpos($statement,'ADD KEY') || \strpos($statement,'ADD UNIQUE KEY') )
							{
								continue;
							}	
							// track the statements executed
							$arrStatements[] = $statement;
							// execute
							$this->Database->query( $statement );
						}
					}
				}		
			}
			catch(\Exception $e)
			{
				$arrErrors[] = $e->getMessage();
			}
						
			// log errors and redirect
			if(count($arrErrors) > 0)
			{
				$objContainer->get('monolog.logger.contao.error')->info( 'Theme Installer: Database update returned errors: '.implode(', ', $arrErrors) );

				// track error				
				$arrSession['errors'] = $arrErrors;
				$objSession->set($this->strSession,$arrSession);

				$this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );
			}

			return;
		}
		//! status: INSTALLATION | STEP 5.0 : SQL_TEMPLATE_WAIT : Wait for user input
		else if(Input::get('status') == 'installation' && Input::get('step') == 'sql_template_wait')
		{
			// get the template by contao version
			$strTemplate = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][$this->strTheme]['sql_templates'][$version];

			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'SQL_TEMPLATE_WAIT';
			$this->Template->sql_template_info = sprintf($GLOBALS['TL_LANG']['pct_theme_installer']['sql_template_info'],$strTemplate);
			
			// when not in "update" mode, continue sql template installation
			if(Input::get('mode') == 'install' || Input::get('mode') == '')
			{
				$this->redirect( Backend::addToUrl('status=installation&step=sql_template_import') );
			}
			
			return;
		}
		//! status: INSTALLATION | STEP 6.0 : SQL_TEMPLATE_IMPORT : Import the sql file
		else if(Input::get('status') == 'installation' && Input::get('step') == 'sql_template_import')
		{
			$this->Template->status = 'INSTALLATION';
			$this->Template->step = 'SQL_TEMPLATE_IMPORT';
			// get the template by contao version
			$strTemplate = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][$this->strTheme]['sql_templates'][$version];
			
			if(empty($strTemplate))
			{
				$this->Template->error = $GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['sql_not_found'];
				return;
			}
			// create a tmp copy
			$strOrigTemplate = $strTemplate;
			$blnIsCustomCatalog = (boolean)$GLOBALS['PCT_THEME_INSTALLER']['THEMES'][$this->strTheme]['isCustomCatalog'];
			
			$this->Template->sqlFile = $strOrigTemplate;
			
			// @author Leo Feyer
			$objDatabase->query("SET AUTOCOMMIT = 0");

			// Eclipse + CustomCatalog sqls
			$strZipFolder = $GLOBALS['PCT_THEME_INSTALLER']['THEMES'][$this->strTheme]['zip_folder'];
			$strFileCC = $rootDir.'/'.$GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.$strZipFolder.'/'.$strTemplate;
			if(Input::get('action') == 'run' && $blnIsCustomCatalog === true && file_exists($strFileCC))
			{
				$skipTables = array('tl_user','tl_user_group','tl_member','tl_member_group','tl_session','tl_repository_installs','tl_repository_instfiles','tl_undo','tl_log','tl_version');
				
				$objFile = fopen($strFileCC,'r');
				
				// find multiline CREATE, ALTER statements
				$create_sql = array();
				$alter_sql = array();
				if($objFile)
				{
					$create_table = '';
					$alter_table = '';

					while(!feof($objFile))
					{
						$line = fgets($objFile);

						// CREATE
						if(strpos($line, 'CREATE TABLE') !== false)
						{
							if(preg_match('/`(.*?)\`/', $line,$result))
							{
								$create_table = $result[1];
							}
						}
						// ALTER
						if(strpos($line, 'ALTER TABLE') !== false)
						{
							if(preg_match('/`(.*?)\`/', $line,$result))
							{
								$alter_table = $result[1];
							}
						}

						if(strlen($create_table) > 0)
						{
							$create_sql[$create_table] .= trim($line);
						}

						if(strlen($alter_table) > 0)
						{
							$alter_sql[$alter_table] .= trim($line);
						}

						if(strpos($line, 'CHARSET=utf8') !== false && strlen($create_table) > 0)
						{
							$create_table = '';
						}
						if(strpos($line, ';') !== false && strlen($alter_table) > 0)
						{
							$alter_table = '';
						}
					}
					fclose($objFile);
					unset($create_table);
					unset($alter_table);
				}
				
				try
				{
					// DROP tables that will be created anyways
					foreach(array_keys($create_sql) as $table)
					{
						if($objDatabase->tableExists($table,null,true) === true && !in_array($table, $skipTables))
						{
							$objDatabase->query('DROP TABLE '.$table);
						}
					}

					// CREATE tables
					foreach($create_sql as $table => $query)
					{
						if($objDatabase->tableExists($table,null,true) === false && !in_array($table, $skipTables))
						{
							$objDatabase->query($query);
						}
					}

					// ALTER tables
					foreach($alter_sql as $table => $query)
					{
						if($objDatabase->tableExists($table,null,true) === false || in_array($table, $skipTables))
						{
							continue;
						}

						foreach(array_filter(explode(';', $query)) as $q)
						{
							$objDatabase->query($q.';');
						}
					}
					unset($create_sql);
					unset($alter_sql);

					// TRUNCATE and INSERT
					$sql = preg_grep('/^INSERT /', file($strFileCC) );

					// TRUNCATE and INSERT
					$truncated = array();
					foreach($sql as $query)
					{
						if(preg_match('/`(.*?)\`/', $query,$result))
						{
							if($objDatabase->tableExists( $result[1],null,true ) === true && !in_array($result[1], $truncated) && !in_array($result[1], $skipTables))
							{
								$objDatabase->query('TRUNCATE TABLE '.$result[1]);
								$truncated[] = $result[1];
							}

							if($objDatabase->tableExists( $result[1],null,true ) === true && !in_array($result[1], $skipTables))
							{
								$objDatabase->query($query);
							}
						}
					}

					// set ssl
					$objDatabase->prepare("UPDATE tl_page %s WHERE type='root'")->set( array('useSSL'=> Environment::get('ssl') ) )->execute();
				
				}
				catch(\Exception $e)
				{
					$arrErrors[] = $e->getMessage();
				}

				unset($skipTables);
				unset($objFile);
				unset($sql);
				unset($truncated);

				// @author Leo Feyer
				$objDatabase->query("SET AUTOCOMMIT = 1");

				if(!empty($arrErrors))
				{
					$objContainer->get('monolog.logger.contao.error')->info( 'Theme installation finished with errors: '.implode(', ', $arrErrors) );

					// track error				
					$arrSession['errors'] = $arrErrors;
					$objSession->set($this->strSession,$arrSession);
					
					$this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );
				}

				// mark as being completed
				$objSession->set('PCT_THEME_INSTALLER',array('completed'=>true,'theme'=>$this->strTheme,'sql'=>$strOrigTemplate));
				
				// log out
				#$objUser = \BackendUser::getInstance();
				#$objUser->logout();

				// redirect to contao login if not from ajax
				if(!Environment::get('isAjaxRequest'))
				{
					$url = StringUtil::decodeEntities( Environment::get('base').'contao?completed=1&theme='.$this->strTheme.'&sql='.$strOrigTemplate );
					$this->redirect($url);
				}

				return;
			}

			if(Input::get('action') == 'run')
			{
				// mark as being completed
				$arrSession = array('completed'=>true,'theme'=>$this->strTheme,'sql'=>$strOrigTemplate);
				$objSession->set('PCT_THEME_INSTALLER',$arrSession);		
				
				// fetch tl_user information
				$objUsers = $objDatabase->prepare("SELECT * FROM tl_user")->execute();
				
				$objInstallHelper = new InstallerHelper;
				$objInstallHelper->importTemplate($strTemplate);
				#$objInstall->persistConfig('exampleWebsite', time());
				
				// recreate tl_user
				while($objUsers->next())
				{
					$objDatabase->prepare("INSERT INTO `tl_user` %s")->set( $objUsers->row() )->execute();
				}
							
				// set ssl
				$objDatabase->prepare("UPDATE tl_page %s WHERE type='root' OR type='rootFallback'")->set( array('useSSL'=> Environment::get('ssl') ) )->execute();
		
				if(!Environment::get('isAjaxRequest'))
				{
					$url = StringUtil::decodeEntities( Environment::get('base').'contao?completed=1&theme='.$this->strTheme.'&sql='.$strOrigTemplate );
					$this->redirect($url);
				}
				
			}

			return;
		}


//! status: FILE_LOADED ... FILE_CREATED


		// file loaded
		if( isset($arrSession['status']) && $arrSession['status'] == 'FILE_CREATED' && !empty($arrSession['file']))
		{
			// check if file still exists
			if(!file_exists($rootDir.'/'.$arrSession['file']))
			{
				$this->Template->status = 'FILE_NOT_EXISTS';

				// log
				$objContainer->get('monolog.logger.contao.error')->info('Theme Installer: File not found or file could not be created');

				// track error				
				$arrSession['errors'] = array('File not found or file could not be created');
				$objSession->set($this->strSession,$arrSession);
				
				// redirect
				$this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );

				return;
			}


			$this->Template->status = 'FILE_EXISTS';

			$objFile = new File($arrSession['file'],true);
			$this->Template->file = $objFile;

			// set file path
			$this->strFile = $objFile->path;

			// redirect to step: 1 (unzipping) of the installation
			$this->redirect( Backend::addToUrl('status=installation') );
		}


//! status: VALIDATION: Fetch the license information


		if(Input::post('license') != '' && Input::post('email') != '' && Input::post('FORM_SUBMIT') == $strForm)
		{
			$this->Template->status = 'VALIDATION';

			$arrParams = array
			(
				'key'   => trim(Input::post('license')),
				'email'  => trim(Input::post('email')),
				'domain' => Environment::get('url'),
				'caller' => 'installer'
			);

			if(Input::post('product') != '')
			{
				$arrParams['product'] = Input::post('product');
			}

			$strRequest = html_entity_decode(  $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'/installer_api.php?'.http_build_query($arrParams) );
			
			// validate the license
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_URL, $strRequest);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		
			$strResponse = curl_exec($curl);
			curl_close($curl);
			unset($curl);

			$objLicense = json_decode($strResponse);

			// store the api response in the session
			$arrSession['status'] = $objLicense->status;
			$arrSession['license'] = $strResponse;
			$objSession->set($this->strSession,$arrSession);
			
			// flush post and make session active
			// redirect to the beginning
			$this->redirect( Backend::addToUrl('status=ready',true) );
		}


//! status: CHOOSE_PRODUCT, waiting for user to choose the product


		if(Input::get('status') == 'choose_product' && $objLicense->status == 'OK')
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


		if(Input::get('status') == 'ready' && $objLicense->status == 'OK')
		{
			$this->Template->status = 'READY';
			$this->Template->license = $objLicense;

			// min memory_limit
			if( (int)ini_get('memory_limit') < 512 && (int)ini_get('memory_limit') > 0)
			{
				$this->Template->errors = array( \sprintf($GLOBALS['TL_LANG']['XPT']['pct_theme_installer']['memory_limit'],ini_get('memory_limit')) ?: 'Min. required memory_limit is 512M');
			}

			// registration error
			if($objLicense->registration->hasError)
			{
				$this->Template->hasRegistrationError = true;
			}

			// has more than one product to choose
			if(!empty($objLicense->products))
			{
				$this->redirect( Backend::addToUrl('status=choose_product',true) );
			}

			if(Input::post('install') != '' && Input::post('FORM_SUBMIT') == $strForm)
			{
				$this->redirect( Backend::addToUrl('status=loading',true) );
			}

			return;
		}


//! status: LICENSE = OK -> LOADING... and FILE CREATION


		// if all went good and the license etc. is all valid, we get an secured hash and download will be available
		if(Input::get('status') == 'loading' && $objLicense->status == 'OK' && !empty($objLicense->hash))
		{
			$this->Template->status = 'LOADING';
			$this->Template->license = $objLicense;
			$arrErrors = array();

			// write license file
			$objFile = new File('var/pct_license');
			$objFile->write($objLicense->key);
			$objFile->close();
		
			// coming from ajax request
			if(Input::get('action') == 'run')
			{
				$arrParams['email'] = $objLicense->email;
				$arrParams['key'] = $objLicense->key;
				$arrParams['hash'] = $objLicense->hash;
				$arrParams['domain'] = $objLicense->domain;
				$arrParams['sendToAjax'] = 1;
				$arrParams['product'] = $objLicense->file->id;
				$arrParams['caller'] = 'installer';

				$strFileRequest = html_entity_decode( $GLOBALS['PCT_THEME_INSTALLER']['api_url'].'/installer_api.php?'.http_build_query($arrParams) );
				
				try
				{
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_URL, $strFileRequest);
					curl_setopt($curl, CURLOPT_HEADER, 0);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		
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
						//System::log('Theme Installer: '. $objResponse->error,__METHOD__,TL_ERROR);
					}
					else if(!empty($strFileResponse))
					{
						$objFile = new File($GLOBALS['PCT_THEME_INSTALLER']['tmpFolder'].'/'.$objLicense->file->name);
						$objFile->write( $strFileResponse );
						$objFile->close();

						$arrSession['status'] = 'FILE_CREATED';
						$arrSession['file'] = $objFile->path;
						$objSession->set($this->strSession,$arrSession);

						// tell ajax that the file has been written
						die($this->Template->file_written_response);

						#// flush post and make session active
						#$this->reload();
					}
				}
				catch(\Exception $e)
				{
					$arrErrors[] = $e->getMessage();
				}
			}

			// log errors and redirect to error page
			if(count($arrErrors) > 0)
			{
				$objContainer->get('monolog.logger.contao.error')->info( 'Theme Installer: '.implode(', ', $arrErrors) );

				// track error				
				$arrSession['errors'] = $arrErrors;
				$objSession->set($this->strSession,$arrSession);
				
				$this->redirect( Backend::addToUrl('status=error',true,array('step','action')) );
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
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();
		if($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request) && $objTemplate->getName() == 'be_main')
		{
			$objScripts = new BackendTemplate('be_js_pct_theme_installer');

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

		$objSession = System::getContainer()->get('request_stack')->getSession();
		$arrSession = $objSession->get($this->strSession);
		
		// store the processed steps
		if( !isset($arrSession['BREADCRUMB']['completed']) || !is_array($arrSession['BREADCRUMB']['completed']))
		{
			$arrSession['BREADCRUMB']['completed'] = array();
		}

		foreach($GLOBALS['PCT_THEME_INSTALLER']['breadcrumb_steps'] as $k => $data)
		{
			$status = strtolower($k);

			if( !isset($arrSession['BREADCRUMB']['completed'][$k]) )
			{
				$arrSession['BREADCRUMB']['completed'][$k] = false;
			}


			// css class
			$class = array('item',$status);
			if( isset($data['protected']) )
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
			if( !isset($data['title']) )
			{
				$data['title'] = $data['label'];
			}

			// active
			$data['isActive'] = false;
			if($strCurrent == $status)
			{
				$data['isActive'] = true;
				$class[] = 'tl_green';
				$class[] = 'active';

				$arrSession['BREADCRUMB']['completed'][$k] = true;
			}

			// completed
			$data['completed'] = false;
			if( isset($arrSession['BREADCRUMB']['completed'][$k]) && $arrSession['BREADCRUMB']['completed'][$k] === true && $strCurrent != $status)
			{
				$data['completed'] = true;
				$class[] = 'completed';
			}

			// sill waiting
			if(!isset($data['isActive']) && !isset($data['completed']))
			{
				$data['pending'] = true;
				$class[] = 'pending';
			}

			$strToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
			$data['href'] = Controller::addToUrl($data['href'].'&rt='.$strToken,true,array('step'));
			$data['class'] = implode(' ', array_unique($class));

			$arrItems[ $k ] = $data;

			$i++;
		}

		// update session
		$objSession->set($this->strSession,$arrSession);

		// @var object
		$objTemplate = new BackendTemplate($this->strTemplateBreadcrumb);
		$objTemplate->items = $arrItems;

		return $objTemplate->parse();
	}
}