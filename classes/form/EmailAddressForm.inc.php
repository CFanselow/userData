<?php

/**
 * @file controllers/statistics/form/PKPReportGeneratorForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPReportGeneratorForm
 * @ingroup controllers_statistics_form
 * @see Form
 *
 * @brief Base form class to generate custom statistics reports.
 */

import('lib.pkp.classes.form.Form');
import('plugins.generic.emailAddress.classes.EmailAddressDAO');

class EmailAddressForm extends Form {

	/* @var $_metricType string */
	private $_rghrh;

	/**
	 * Constructor.
	 * @param $columns array Report column names.
	 * @param $optionalColumns array Report column names that are optional.
	 * @param $objects array Object types.
	 * @param $fileTypes array File types.
	 * @param $metricType string The default report metric type.
	 * @param $defaultReportTemplates array Default report templates that
	 * defines columns and filters selections. The key for each array
	 * item is expected to be a localized key that describes the
	 * report Template.
	 * @param $reportTemplateIndex int (optional) Current report template index
	 * from the passed default report templates array.
	 */
	function __construct() {
		$emailAddressPlugin = PluginRegistry::getPlugin('generic', EMAILADDRESS_PLUGIN_NAME);
		parent::__construct($emailAddressPlugin->getTemplateResource('emailAddressForm.tpl'));

			//$this->_columns = $columns;

		//$this->addCheck(new FormValidatorArray($this, 'columns', 'required', 'manager.statistics.reports.form.columnsRequired'));
		//$this->addCheck(new FormValidatorPost($this));
		//$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$router = $request->getRouter();
		$context = $router->getContext($request);
		
		$emailAddressDAO = new emailAddressDAO();
		$userGroups = $emailAddressDAO->getUserGroups($context->getId(),$context->getPrimaryLocale());

		$this->setData('userGroups', $userGroups);		
		return parent::fetch($request, $template, $display);
	}

	/**
	 * Assign user-submitted data to form.
	 */
	function readInputData() {
		
		$request = Application::getRequest();
		$router = $request->getRouter();
		$context = $router->getContext($request);
		$emailAddressDAO = new emailAddressDAO();
		$userGroups = $emailAddressDAO->getUserGroups($context->getId(),$context->getPrimaryLocale());
		
		$userVars = array();
		foreach ($userGroups as $key => $value) {
			$userVars[]= "OR".$key;
			$userVars[]= "AND".$key;
			$userVars[]= "NOT".$key;			
		}		
		$this->readUserVars($userVars);
		return parent::readInputData();
	}

	/**
	 * @see Form::execute()
	 */
	function execute(...$functionArgs) {		
	}


	//
	// Protected methods.
	//
	/**
	 * Return which assoc types represents file objects.
	 * @return array
	 */
	function getFileAssocTypes() {
		return 1;
	}
}


