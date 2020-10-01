<?php

/**
 * @file plugins/importexport/emailAddress/EmailAddressExportPlugin.inc.php
 *
 * Copyright (c) 2020 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class EmailAddressExportPlugin 
 * @ingroup plugins_importexport_emailAddress
 *
 * @brief Email Address export plugin
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');
import('plugins.importexport.userData.UserDataDAO');

class UserDataExportPlugin extends ImportExportPlugin {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return $success;
		if ($success && $this->getEnabled()) {
			$this->addLocaleData();
		}
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'UserDataExportPlugin';
	}

	/**
	 * Get the display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.importexport.userData.displayName');
	}

	/**
	 * Get the display description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.importexport.userData.description');
	}

	/**
	 * @copydoc ImportExportPlugin::getPluginSettingsPrefix()
	 */
	function getPluginSettingsPrefix() {
		return 'userData';
	}

	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$context = $request->getContext();
		parent::display($args, $request);
		$templateMgr->assign('plugin', $this);

		$userDataDAO = new userDataDAO();
		$userGroups = $userDataDAO->getUserGroups($context->getId(),$context->getPrimaryLocale());
		$primaryLocale = $context->getPrimaryLocale();		
		switch (array_shift($args)) {
			case 'index':
			case '':
				$templateMgr->assign('userGroups', $userGroups);				
				$templateMgr->display($this->getTemplateResource('index.tpl'));
				break;
			case 'exportAllData':			
				$users = $userDataDAO->getUsers($context->getId());
				$userSettings = $userDataDAO->getUserSettings($context->getId());

				// get main data from table 'users'
				$usersData=array();
				foreach ($users as $user) {
					$userData = array();
					$userData['user_id'] = $user['user_id'];
					$userData['username'] = $user['username'];
					$userData['email'] = $user['email'];					
					$userData['date_registered'] = $user['date_registered'];
					$userData['date_validated'] = $user['date_validated'];
					$userData['disabled'] = $user['disabled'];
					$userData['disabled_reason'] = $user['disabled_reason'];						
					$usersData[$user['user_id']]=$userData;
				}
				// get all user settings (only primary locale)
				$userSettingItems = array();
				foreach ($userSettings as $userSetting) {
					$locale = $userSetting['locale'];
					if (empty($locale)||$locale==$primaryLocale) {
						$userSettingItems[] =  $userSetting['setting_name'];
					}
				}
		
				// get data from table 'user_settings'
				foreach ($users as $user) {
					$userID = $user['user_id'];
					foreach ($userSettingItems as $userSettingItem) {
						$userSettingsValue ="";
						foreach ($userSettings as $userSetting) {
							$userSettingName = $userSetting['setting_name'];							
							if ($userSettingName==$userSettingItem && $userID==$userSetting['user_id'] && $primaryLocale==$userSetting['locale'] ) {
								$userSettingsValue = $userSetting['setting_value'];						
							}	
						}
						$usersData[$userID][$userSettingItem]=$userSettingsValue;	
					}
				}
				
				// get rest of the data from table 'users'
				foreach ($users as $user) {
					$userID = $user['user_id'];
					$usersData[$userID]['url']=$user['url'];
					$usersData[$userID]['phone']=$user['phone'];
					$usersData[$userID]['mailing_address']=$user['mailing_address'];
					$usersData[$userID]['billing_address']=$user['billing_address'];
					$usersData[$userID]['country']=$user['country'];
					//$usersData[$userID]['gossip']=$user['gossip'];
					//$usersData[$userID]['date_last_email']=$user['date_last_email'];
					//$usersData[$userID]['date_last_login']=$user['date_last_login'];
					$usersData[$userID]['must_change_password']=$user['must_change_password'];
					//$usersData[$userID]['auth_id']=$user['auth_id'];
					//$usersData[$userID]['auth_str']=$user['auth_str'];
					//$usersData[$userID]['inline_help']=$user['inline_help'];					
				}     				
				
				// prepare output
				$header = "";
				$output = "";
				$firstUser = true;
				foreach ($usersData as $userData) {
					foreach ($userData as $name=>$value) {
						if ($firstUser) {
							$header = $header .$name.",";
						}
						$output = $output . $value .","; 
					}
					$firstUser = false;
					$header = substr($header, 0, -1)."\n";
					$output = substr($output, 0, -1)."\n";					
				}		

				// save file
				$output = $header . $output;
				import('lib.pkp.classes.file.FileManager');
				$fileManager = new FileManager();
				$exportFileName = $this->getExportFileName($this->getExportPath(), 'exportAll', $context, '.csv');			
				$fileManager->writeFile($exportFileName,$output);
				$fileManager->downloadByPath($exportFileName);
				$fileManager->deleteByPath($exportFileName);			
				break;
			case 'exportSelection':
				$or  = array();
				$and = array();
				$not = array();
				$dateRegistered = null;
				$useDateRegistered = $request->getUserVar('useDateRegistered');
				if ($useDateRegistered) {
					$dateRegistered = $request->getUserVar('dateRegistered');					
				}
				
				foreach ($userGroups as $key => $value) {
					if ($request->getUserVar('OR'.$key)) {
						$or[] = $key;
					} 
					if ($request->getUserVar('AND'.$key)) {
						$and[] = $key;
					} 
					if ($request->getUserVar('NOT'.$key)) {
						$not[] = $key;
					} 
				}
				$resOR  = array();
				if (!empty($or)) {$resOR = $userDataDAO->getUserInfosByGroupsOR($or,$primaryLocale,$dateRegistered);} 
				$resAND = array();
				if (!empty($and)) {$resAND = $userDataDAO->getUserInfosByGroupsAND($and,$primaryLocale,$dateRegistered);}
				$resNOT = array();
				if (!empty($not)) {$resNOT = $userDataDAO->getUserInfosByGroupsOR($not,$primaryLocale,$dateRegistered);}
				
				$resALL = array_unique(array_diff(array_merge($resOR,$resAND),$resNOT));

				$output = "email, username, firstName, lastName, disabled\n";
				foreach ($resALL as $key => $value) {
					$output = $output.$key .",". $value."\n";
				}

				import('lib.pkp.classes.file.FileManager');
				$fileManager = new FileManager();
				$exportFileName = $this->getExportFileName($this->getExportPath(), 'export', $context, '.csv');			
				$fileManager->writeFile($exportFileName, $output);
				$fileManager->downloadByPath($exportFileName);
				$fileManager->deleteByPath($exportFileName);					
				break;
			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
	}

	/**
	 * @copydoc ImportExportPlugin::executeCLI($scriptName, $args)
	 */
	function executeCLI($scriptName, &$args) {
		fatalError('Not implemented.');
	}

	/**
	 * @copydoc ImportExportPlugin::usage
	 */
	function usage($scriptName) {
		fatalError('Not implemented.');
	}
}
