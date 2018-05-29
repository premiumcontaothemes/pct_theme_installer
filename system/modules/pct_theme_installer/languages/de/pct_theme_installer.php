<?php 

/**
 * Contao 
 * German translation file 
 * 
 * copyright  Tim Gatzky 2018 
 * author     Tim Gatzky <info@tim-gatzky.de>
 * Translator:  
 * 
 * This file was created automatically be the Contao extension repository translation module.
 * Do not edit this file manually. Contact the author or translator for this module to establish 
 * permanent text corrections which are update-safe. 
 */
 
/**
 * Errors
 */
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['label_key'] 			= 'Lizenzschlüssel / Bestellnummer';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['label_email'] 			= 'Bestell-E-Mail-Adresse';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['submit_license'] 		= 'Lizenz abfragen';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['submit_install'] 		= 'Installation starten';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['sql_template_info'] 	= 'SQL-Template-Datei: %s';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['input_email'][0] 		= 'Bestell-E-Mail-Adresse';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['input_email'][1] 		= 'Geben Sie die E-Mail Adresse Ihres Kundenaccounts an.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['input_license'][0] 		= 'Lizenzschlüssel / Bestellnummer';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['input_license'][1] 		= 'Die Bestellnummer finden Sie auf Ihrer Rechnung oder in Ihrem Kundenaccount.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['label_status']			= 'Status: %s';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['label_step']			= 'Schritt: %s';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['button_reset'][0]		= 'Neustart'; // linktext
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['button_reset'][1]		= 'Die Installation erneut beginnen'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['domainRegistrationError']			= 'Abweichende Lizenznehmer-Domain (%s) zu anfragender Domain (%s).<br>Bitte updaten Sie Ihre Lizenzregistrierung in Ihrem Kundenbereich.'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['sql_template_headline']				= 'Bereit zum Import der Demo-Webseite.'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['sql_template_subheadline']			= 'Hinweis: Bestehende Daten gehen verloren. Ihr Benutzerprofil bleibt erhalten.'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['sql_template_info']					= 'Der Import der Demo-Webseite (SQL-Template) '; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['button_sql_template_import']		= 'Import ausführen'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['choose_product_info']				= 'Bitte wählen Sie das Product aus, das installiert werden soll.'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['product_select_blankOption']		= 'Produkt wählen...'; // title
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['button_systemlog'][0] 				= 'Gehe zu Systemlog...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['welcome_info'] 						= 'Tragen Sie Ihre Bestellnummer und Bestell-E-Mail-Adresse ein und klicken Sie "Lizenz abfragen". Anschließend können Sie den 1-Click-Installer starten. Das Theme Eclipse wird automatisch vom Premium Contao Themes Server auf Ihren Server geladen und anschließend installiert.';		
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['sql_template_import_info'] 			= 'Der SQL-Import kann einige Minuten in Anspruch nehmen. Nach erfolgreichem Import werden Sie zum Contao-Login weitergeleitet.';		
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['access_denied'] 					= 'Meldung: %s';		
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['errors'] 							= 'Fehler: %s';		
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['TEMPLATE']['not_supported'] 					= 'Der Installer unterstützt die folgenden Produkte: %s';		


/**
 * Breadcrumb
 */
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['welcome'][0] 							= 'Start';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['welcome'][1] 							= 'Hier beginnt alles';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['validation'][0]						= 'Lizenzabfrage';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['validation'][1] 						= "Ihre Lizenz wird überprüft.";
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['accepted'][0] 						= 'Lizenz valide';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['accepted'][1] 						= 'Ihre Lizenz ist validiert.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['not_accepted'][0] 					= 'Lizenz nicht valide';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['not_accepted'][1] 					= 'Ihre Lizenz konnte nicht validiert werden.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['loading'][0] 							= 'Lade Daten';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['loading'][1] 							= 'Die Daten werden geladen';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.unzip'][0] 				= 'Daten entpacken';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.unzip'][1]				= 'Die Daten werden entpackt'; 
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.copy_files'][0] 			= 'Daten kopieren';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.copy_files'][1] 			= 'Die Daten werden an Ihre Zielverzeichnisse verteilt.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.clear_cache'][0] 		= 'Internen Cache leeren';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.clear_cache'][1] 		= 'Contaos internen Cache leeren.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.db_update_modules'][0] 	= 'Datenbank-Update';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.db_update_modules'][1] 	= 'Die Datenbank wird mit den Modul-Informationen geupdated.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_import'][0]	= 'Installations-Template Import ausführen...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_import'][1]	= 'Installation des SQL-Installations-Templates der Demo-Daten';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_wait'][0] 	= 'Installations-Template importieren...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BREADCRUMB']['installation.sql_template_wait'][1] 	= 'Installations-Template importieren...';


/**
 * Status
 */
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['welcome'] = '';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['validation'] = 'Lizenzabfrage';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['accepted'] = 'Lizenz valide';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['not_accepted'] = 'Lizenz nicht valide';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['not_supported'] = 'Dieses Produkt wird nicht unterstützt.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['choose_product'] = 'Produkt wählen...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['loading'] = 'Lade Daten';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['session_lost'] = 'Lizenz-Session abgelaufen';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['installation.unzip'] = 'Daten entpacken';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['installation.copy_files'] = 'Daten kopieren';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['installation.clear_cache'] = 'Internen Cache leeren';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['installation.db_update_modules'] = 'Datenbank-Update';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['STATUS']['installation.sql_template_import'] = 'Import Demo-Daten (SQL)';


/**
 * Ajax infotexts
 */
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['validation'] = 'Lizenz wird abgefragt...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['loading'] = 'Datei "%s" wird geladen...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['sql_template_import'] = 'Installations-Template wird importiert...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['clear_cache'] = 'Cache wird geleert...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['copy_files'] = 'Dateien werden kopiert...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['unzip'] = 'Dateien werden entpackt...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['AJAX_INFO']['db_update_modules'] = 'Datenbank wird aktualisiert...';


/**
 * Backend descriptions
 */
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['welcome'] 							= 'Willkommen im Theme-Installer';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['ready'] 								= 'Bereit zur Installation';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['loading'] 							= 'Ihre Lizenz ist bestätigt. Die Daten werden geladen...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['choose_product'] 					= 'Ihre Lizenz beinhalte mehr als ein Produkt.<br>Bitte wählen sie das zu installierende Produkt aus.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['error'] 								= 'Während der Installation sind Fehler aufgetreten.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['file_not_exists'] 					= 'Datei existiert nicht mehr';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['access_denied'] 						= 'Die Lizenz konnte nicht validiert werden.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['not_supported'] 						= 'Das gewählte Produkt wird nicht unterstützt.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['session_lost'] 						= 'Die Lizenz-Session ist abgelaufen. Bitte starten Sie die Installation erneut.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['installation.unzip'] 				= 'Das Daten-Zip wird entpackt...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['installation.copy_files'] 			= 'Die Dateien werden an ihren Zielort kopiert...';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['installation.clear_cache'] 			= 'Contaos interner Cache bzw. Symphony-Cache wird mit den neuen Informationen erstellen.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['installation.db_update_modules'] 	= 'Die Datenbank wird mit den neuen Modulinformationen aktualisiert.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['installation.sql_template_wait'] 	= 'Die Demo-Webseite (Installations-Template) ist bereit installiert zu werden.';
$GLOBALS['TL_LANG']['PCT_THEME_INSTALLER']['BACKEND_DESCRIPTION']['installation.sql_template_import'] 	= 'Das Installations-Template wird importiert.';

