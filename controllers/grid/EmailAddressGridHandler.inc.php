<?php
/**
 * @file plugins/generic/vgWort/controllers/grid/PixelTagGridHandler.inc.php
 *
 * Copyright (c) 2018 Center for Digital Systems (CeDiS), Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class PixelTagGridHandler
 * @ingroup plugins_generic_vgWort
 *
 * @brief The pixel tags listing.
 */

//import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.emailAddress.classes.EmailAddressDAO');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class EmailAddressGridHandler extends GridHandler {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
			array(
				'emailAddressTab'
			)
		);
	}	
	
	/**
	 * Show pixel tags listing
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */

	function emailAddressTab($args, $request) {
		
$myfile = 'test.txt';
$newContentCF5344 = print_r("test", true);
$contentCF2343 = file_get_contents($myfile);
$contentCF2343 .= "\n in emailAddressTab: " . $newContentCF5344;
file_put_contents($myfile, $contentCF2343);	
		
		$emailAddressPlugin = PluginRegistry::getPlugin('generic', EMAILADDRESS_PLUGIN_NAME);
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('test', "das ist ein Test");
		
		
		$emailAddressDAO = new EmailAddressDAO;
		$context = $request->getContext();

		$userGroups = $emailAddressDAO->getUserGroups($context->getId(),$context->getPrimaryLocale());

		$emptyCheckboxes = array();
		for ($i=0; $i<sizeof($userGroups);$i++) {
			$emptyCheckboxes[] = false;
		}

		//$templateMgr->assign('userRoles', $userRoles); // necessary for the backend sidenavi to appear
		//$templateMgr->assign('pageTitle', 'plugins.generic.title.groupMail');
		$templateMgr->assign('userGroups', $userGroups);
		//$templateMgr->assign('baseUrl', $request->getBaseUrl());
		$templateMgr->assign('postOr', $emptyCheckboxes);
		$templateMgr->assign('postAnd', $emptyCheckboxes);
		$templateMgr->assign('postNot', $emptyCheckboxes);
		$templateMgr->assign('getUsernames',true);
		$templateMgr->assign('getEmails',true);
		$templateMgr->assign('getEmails',true);
		$templateMgr->assign('results',null);		
		
		
		return $templateMgr->fetchJson($emailAddressPlugin->getTemplateResource('emailAddress.tpl'));
	}


/*
	function getGroupMailResults($args, $request) {

		$authorizedUserGroups = array(ROLE_ID_SITE_ADMIN,ROLE_ID_MANAGER);
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		// only for press managers and admins 
		$groupMailDAO = new GroupMailDAO;
		$user = $request->getUser();
		$userId = $user->getId();
		$userGroups = $groupMailDAO->getUserRoles($userId);
		if (!in_array('Press Manager',$userGroups)&&!in_array('Site Admin',$userGroups)) {
			$request->redirect(null, 'index');
		}

		$press = $request->getPress();
		$context = $request->getContext();

		$userGroups = $groupMailDAO->getUserGroups($context->getId(),$press->getPrimaryLocale());


		$keysUserGroups = array_keys($userGroups);		

		$saveToFile = isset($_REQUEST['buttonSaveToFile']);
		$showResults = isset($_REQUEST['buttonShowResults']);
		$getUsernames = $_POST['getUsernames'];
		$getEmails = $_POST['getEmails'];

		$or = array();
		$and = array();
		$not = array();
		$postOr = array();
		$postAnd = array();
		$postNot = array();	
				
		for ($i=0; $i<sizeof($userGroups);$i++) {
			if (isset($_POST['OR'.$keysUserGroups[$i]])) {$postOr[$i] = $_POST['OR'.$keysUserGroups[$i]];}
			if (isset($_POST['AND'.$keysUserGroups[$i]])) {$postAnd[$i] = $_POST['AND'.$keysUserGroups[$i]];}
			if (isset($_POST['NOT'.$keysUserGroups[$i]])) {$postNot[$i] = $_POST['NOT'.$keysUserGroups[$i]];}

			if (isset($_POST['OR'.$keysUserGroups[$i]])) {
				$or[] = $keysUserGroups[$i];
			} 
			if (isset($_POST['AND'.$keysUserGroups[$i]])) {   
				$and[] = $keysUserGroups[$i];
			}
			if (isset($_POST['NOT'.$keysUserGroups[$i]])) {   
				$not[] = $keysUserGroups[$i];
			}
		}

		$emailsAnd = array();
		if (sizeof($and)>0) {		

			$query = "";
			$pos0 = true;
			for ($i=0; $i<sizeof($and);$i++) {
				if ($pos0) {
					$query = $query . "(select user_id from user_user_groups where user_group_id=".$and[$i].") ";
					$pos0=false;
				} else {
					$query =  " (select user_id from user_user_groups where user_group_id=".$and[$i]." and user_id in " . $query . ")";
				}
			}
			$query = "SELECT first_name, last_name, email from users where user_id IN " . $query . ";";

			$res = $groupMailDAO->getEmailsByGroup($query);	
			if ($res) {
				$emailsAnd = $res;
			}
		}

		$emailsOr = array();
		if (sizeof($or)>0) {
			$emailsOr  = $groupMailDAO->getEmailsByGroup('SELECT first_name, last_name, email FROM users WHERE user_id IN (SELECT user_id FROM user_user_groups WHERE user_group_id IN ('.implode(",",$or).'));');
		}
		$emailsNot = array();
		if (sizeof($not)>0) {
			$emailsNot  = $groupMailDAO->getEmailsByGroup('SELECT first_name, last_name, email FROM users WHERE user_id IN (SELECT user_id FROM user_user_groups WHERE user_group_id IN ('.implode(",",$not).'));');
		}

		$results = array_unique(array_diff(array_merge($emailsOr,$emailsAnd),$emailsNot));

		if ($showResults) {
			$userGroups = $groupMailDAO->getUserGroups($context->getId(),$press->getPrimaryLocale());
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('pageTitle', 'plugins.generic.title.groupMail');
			$templateMgr->assign('userGroups', $userGroups);
			$templateMgr->assign('results', $results);
			$templateMgr->assign('postOr', $postOr);
			$templateMgr->assign('postAnd', $postAnd);
			$templateMgr->assign('postNot', $postNot);
			$templateMgr->assign('getUsernames', $getUsernames);
			$templateMgr->assign('getEmails', $getEmails);	
			$templateMgr->assign('baseUrl', $request->getBaseUrl());
			$templateMgr->assign('userRoles', $userRoles); // necessary for the backend sidenavi to appear	
			$groupMailPlugin = PluginRegistry::getPlugin('generic', GROUPMAIL_PLUGIN_NAME);
			//$templateMgr->display($groupMailPlugin->getTemplatePath().'/groupMail.tpl');
			$templateMgr->display('templates/groupMail.tpl');

		} elseif ($saveToFile) {

			$output = "Results: \n";
			if ($results && ($getUsernames||$getEmails)) {

			 	while ($username = current($results)) {
					if ($getUsernames) {
						$output = $output . $username . " ";		
					}
					if ($getEmails) {
						$output = $output . key($results);
					}
					next($results);
					$output = $output . "\n";
				}
			}
			
			$filename = 'groupMailResult.txt';
			ob_end_clean();
			header("Content-Type: text/plain");
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Length: " . strlen($output));
			echo $output;
		}
	}
*/

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));

		return parent::authorize($request, $args, $roleAssignments);
	}

}

?>
