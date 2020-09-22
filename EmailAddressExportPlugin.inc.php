<?php

/**
 * @file plugins/importexport/onix30/EmailAddressExportPlugin .inc.php
 *
 * Copyright (c) 2020 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class EmailAddressExportPlugin 
 * @ingroup plugins_importexport_onix30
 *
 * @brief ONIX 3.0 XML import/export plugin
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');
import('plugins.importexport.emailAddress.EmailAddressDAO');

class EmailAddressExportPlugin extends ImportExportPlugin {
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
		return 'EmailAddressExportPlugin';
	}

	/**
	 * Get the display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.importexport.emailAddress.displayName');
	}

	/**
	 * Get the display description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.importexport.emailAddress.description');
	}

	/**
	 * @copydoc ImportExportPlugin::getPluginSettingsPrefix()
	 */
	function getPluginSettingsPrefix() {
		return 'emailAddress';
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

		$emailAddressDAO = new emailAddressDAO();
		$userGroups = $emailAddressDAO->getUserGroups($context->getId(),$context->getPrimaryLocale());		
		switch (array_shift($args)) {
			case 'index':
			case '':
				$checkboxes = array();
				foreach ($userGroups as $userGroup) {
					$checkboxes[] = false;
				}
				$templateMgr->assign('userGroups', $userGroups);
				$templateMgr->assign('postOr', $checkboxes);
				$templateMgr->assign('postAnd', $checkboxes);
				$templateMgr->assign('postNot', $checkboxes);
				$templateMgr->display($this->getTemplateResource('index.tpl'));
				break;
			case 'export':			
				$or  = array();
				$and = array();
				$not = array();
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
				$primaryLocale = $context->getPrimaryLocale();
				$resOR  = array();
				if (!empty($or)) {$resOR = $emailAddressDAO->getUserInfosByGroupsOR($or,$primaryLocale);} 
				$resAND = array();
				if (!empty($and)) {$resAND = $emailAddressDAO->getUserInfosByGroupsAND($and,$primaryLocale);}
				$resNOT = array();
				if (!empty($not)) {$resNOT = $emailAddressDAO->getUserInfosByGroupsOR($not,$primaryLocale);}
				
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
