<?php

UserDataExportPlugin
 * @file plugins/importexport/userData/EmailAddressExportPlugin.inc.php
 *
 * Copyright (c) 2020 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UserDataExportPlugin 
 * @ingroup plugins_importexport_userData
 *
 * @brief User data export plugin
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
				$users = $userDataDAO->getUsers($context->getId()); //to do:ggf. nur user mit enrollment in der Zeitschrift?
				$userSettings = $userDataDAO->getUserSettings($context->getId(),$primaryLocale);

				// get main data from table 'users'
				$usersData=array();
				foreach ($users as $user) {
					$userData = array();
					$userData['user_id'] = $userDataDAO->strip($user['user_id']);
					$userData['username'] = $userDataDAO->strip($user['username']);
					$userData['email'] = $userDataDAO->strip($user['email']);					
					$userData['date_registered'] = $userDataDAO->strip($user['date_registered']);
					$userData['date_validated'] = $userDataDAO->strip($user['date_validated']);
					$userData['disabled'] = $userDataDAO->strip($user['disabled']);
					$userData['disabled_reason'] = $userDataDAO->strip($user['disabled_reason']);						
					$usersData[$user['user_id']] = $userData;
				}
				// get all user settings (only primary locale)
				//$distinctSettingNames = $userDataDAO->getDistinctSettingNames($primaryLocale);
				//$distinctSettingNames
	
		
				// get data from table 'user_settings'
				$count = 0;
				foreach ($users as $user) {
					$userID = $user['user_id'];
					$usersData[$userID] = array_merge($usersData[$userID],$userSettings[$userID]);
				}
				
			
				/*
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
				}*/
				
				// get rest of the data from table 'users'
				foreach ($users as $user) {
					$userID = $user['user_id'];
					$usersData[$userID]['url']=$userDataDAO->strip($user['url']);
					$usersData[$userID]['phone']=$userDataDAO->strip($user['phone']);
					$usersData[$userID]['mailing_address']=$userDataDAO->strip($user['mailing_address']);
					$usersData[$userID]['billing_address']=$userDataDAO->strip($user['billing_address']);
					$usersData[$userID]['country']=$userDataDAO->strip($user['country']);
					//$usersData[$userID]['gossip']=$user['gossip'];
					//$usersData[$userID]['date_last_email']=$user['date_last_email'];
					//$usersData[$userID]['date_last_login']=$user['date_last_login'];
					$usersData[$userID]['must_change_password']=$userDataDAO->strip($user['must_change_password']);
					//$usersData[$userID]['auth_id']=$user['auth_id'];
					//$usersData[$userID]['auth_str']=$user['auth_str'];
					//$usersData[$userID]['inline_help']=$user['inline_help'];					
				}     				
				
				// prepare output
				$fields = array('user_id','username','email','date_registered','date_validated','disabled','disabled_reason','familyName','givenName','affiliation','biography','preferredPublicName','signature','url','phone','mailing_address','billing_address','country','must_change_password');
				$header = "";
				$first = true;
				foreach ($usersData as $userData) {
					foreach ($fields as $field) {
						if ($first) {
							$header = $header . $field ."\t";							
						}
						$output = $output . $userData[$field] ."\t";
					}
					$first=false;
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
