<?php

/**
 * @file controllers/statistics/ReportGeneratorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReportGeneratorHandler
 * @ingroup controllers_statistics
 *
 * @brief Handle requests for report generator functions.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class EmailAddressHandler extends Handler {
	/**
	 * Constructor
	 **/
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SITE_ADMIN),
			array('fetchEmailAddress', 'saveEmailAddress'));			
	}

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	* Fetch form to generate custom reports.
	* @param $args array
	* @param $request Request
	 * @return JSONMessage JSON object
	*/
	function fetchEmailAddress($args, $request) {
				
		$this->setupTemplate($request);
		$emailAddressForm = $this->_getEmailAddressForm($request);
		//$emailAddressForm->initData();
	
		
		$formContent = $emailAddressForm->fetch($request);

		
		$json = new JSONMessage(true);
		if ($request->getUserVar('refreshForm')) {
			$json->setEvent('refreshForm', $formContent);
		} else {
			$json->setContent($formContent);
		}		
		
		return $json;
	}

	/**
	 * Save form to generate custom reports.
	 * @param $args array
	 * @param $request Request
	 * 
	 */
	function saveEmailAddress($args, $request) {
		
		$emailAddressForm = $this->_getEmailAddressForm($request);
		$emailAddressForm->readInputData();
	
		$router = $request->getRouter();
		$context = $router->getContext($request);
		$emailAddressDAO = new emailAddressDAO();
		$userGroups = $emailAddressDAO->getUserGroups($context->getId(),$context->getPrimaryLocale());
	
		$data=$emailAddressForm->_data;
					
		$and = array(); $or = array(); $not = array();
		foreach ($userGroups as $key => $value) {

			if ($data["AND".$key]) {
				$and[] = $key;
			} 
			if ($data["OR".$key]) {
				$or[] = $key;
			} 
			if ($data["NOT".$key]) {
				$not[] = $key;
			} 
		}
				
		$resOR  = array();
		if (!empty($or)) {$resOR = $emailAddressDAO->getUserIDsByGroupOR($or);} 
		$resNOT = array();
		if (!empty($not)) {$resNOT = $emailAddressDAO->getUserIDsByGroupOR($not);}
		$resAND  = array();
		if (!empty($and)) {$resAND = $emailAddressDAO->getUserIDsByGroupAND($and);}
		
		$resALL = array_unique(array_diff(array_merge($resOR,$resAND),$resNOT));

		$output = "";
		foreach ($resALL as $key => $value) {
			$output = $output.$key .",". $value."\n";
		}
		

		$filename = 'emailAddresses.txt';
		header("Content-Type: text/plain");
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Length: " . strlen($output));
		echo $output;

	
		
		//header("Location: http://ojs-test.cedis.fu-berlin.de/omp-cf-1/index.php/testpress1/management/tools");
		
	}	
		
/*
$myfile = 'test.txt';
$newContentCF5344 = print_r($resOR, true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n resOR: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );

$myfile = 'test.txt';
$newContentCF5344 = print_r($resNOT, true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n resNOT: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );	

$myfile = 'test.txt';
$newContentCF5344 = print_r($resAND, true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n resAND: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );	
		
		
$results = array_unique(array_diff(array_merge($resOR,$resAND),$resNOT));
		
$myfile = 'test.txt';
$newContentCF5344 = print_r($results, true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n results: " . $newContentCF5344 ;
file_put_contents($myfile, $contentCF2343 );		
	*/	
		

		
		

	
	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate($request) {
		parent::setupTemplate($request);
	}


	/**
	 * Get report generator form object.
	 * @return ReportGeneratorForm
	 */
	function &_getEmailAddressForm($request) {
	
		import('plugins.generic.emailAddress.classes.form.EmailAddressForm');
		$emailAddressForm = new EmailAddressForm();

		return $emailAddressForm;
	}
}


