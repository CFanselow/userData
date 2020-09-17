<?php

/**
 * @file plugins/generic/emailAddress/EmailAddressPlugin.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Copyright (c) 2020 Freie Universität Berlin
 *
 * @class EmailAddressPlugin
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class EmailAddressPlugin extends GenericPlugin {

	function register($category, $path, $contextId = null) {		
		if (parent::register($category, $path,$contextId)) {
			$this->addLocaleData();
			
			if ($this->getEnabled($contextId)) {
				HookRegistry::register('Templates::Management::Settings::tools', array($this, 'emailAddressTab'));				
				HookRegistry::register('LoadComponentHandler', array($this, 'setupHandler'));				
			}
			return true;
		}
		return false;
	}

	function emailAddressTab($hookName, $params) {	
		$smarty =& $params[1];
		$output =& $params[2];
		$output .= $smarty->fetch($this->getTemplateResource('toolsNavLink.tpl'));		
		return false;
	}		
	
	/**
	 * Set up handler
	 */	
	function setupHandler($hookName, $params) {	
		$component =& $params[0];	
		if ($component == 'plugins.generic.emailAddress.EmailAddressHandler') {
			define('EMAILADDRESS_PLUGIN_NAME', $this->getName());
			return true;
		}
		return false;
	}	
	
	function getDisplayName() {
		return __('plugins.generic.emailAddress.displayName');
	}

	function getDescription() {
		return __('plugins.generic.emailAddress.description');
	}

}

?>
