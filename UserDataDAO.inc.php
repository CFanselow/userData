<?php

/**
 * @file plugins/importexport/userData/UserDataDAO.inc.php
 *
 * Copyright (c) 2016-2010 Language Science Press
 * Copyright (c) 2020 Freie Universität Berlin 
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.  
 *
 * @class UserDataDAO
 *
 */

class UserDataDAO extends DAO {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	function getEnrolledUsersByContext($contextId) {
		$result = $this->retrieve(
			'SELECT * FROM users WHERE user_id in (SELECT user_id FROM user_user_groups WHERE user_group_id in (SELECT user_group_id FROM user_groups WHERE context_id='.$contextId.'));'
		);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$users = array();
			while (!$result->EOF) {
				$users[] = $result->getRowAssoc(false);
				$result->MoveNext();
			}
			$result->Close();
			return $users;
		}		
	}

	function strip($string) {
		return str_replace("\t"," ", str_replace("\r"," ", str_replace("\n"," ", strip_tags($string))));
	}
	
	function getUserSettings($primaryLocale) {
		$userSettings = array();
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='familyName' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);			
			$userSettings[$this->convertFromDB($row['user_id'],null)]['familyName'] = $this->convertFromDB($row['setting_value'],null);
			$result->MoveNext();
		}
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='givenName' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userSettings[$this->convertFromDB($row['user_id'],null)]['givenName'] = $this->convertFromDB($row['setting_value'],null);
			$result->MoveNext();
		}
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='affiliation' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userSettings[$this->convertFromDB($row['user_id'],null)]['affiliation'] = $this->strip($this->convertFromDB($row['setting_value'],null));
			$result->MoveNext();
		}		
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='biography' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userSettings[$this->convertFromDB($row['user_id'],null)]['biography'] = $this->strip($this->convertFromDB($row['setting_value'],null));
			$result->MoveNext();
		}				
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='orcid' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userSettings[$this->convertFromDB($row['user_id'],null)]['orcid'] = $this->convertFromDB($row['setting_value'],null);
			$result->MoveNext();
		}
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='preferredPublicName' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userSettings[$this->convertFromDB($row['user_id'],null)]['preferredPublicName'] = $this->convertFromDB($row['setting_value'],null);
			$result->MoveNext();
		}			
		$result = $this->retrieve(
			"SELECT user_id,setting_value FROM user_settings WHERE setting_name='signature' and (locale='' or locale='".$primaryLocale."')"
		);
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userSettings[$this->convertFromDB($row['user_id'],null)]['signature'] = $this->strip($this->convertFromDB($row['setting_value'],null));

			$result->MoveNext();
		}			
		$result->Close();
		return $userSettings;
	}	

	// get pairs: user_group_id/name in primary locale
	function getUserGroupsByContext($contextId, $locale) {

		$result = $this->retrieve(
			'SELECT s.user_group_id,s.setting_value FROM user_group_settings s LEFT JOIN user_groups u ON (s.user_group_id=u.user_group_id) WHERE u.context_id='.$contextId.' AND s.locale="'.$locale.'" AND s.setting_name="name"'
		);
		
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$userGroups = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$userGroups[$this->convertFromDB($row['user_group_id'],null)] = $this->convertFromDB($row['setting_value'],null); 
				$result->MoveNext();
			}
			$result->Close();
			return $userGroups;
		}
	}
	
	function getUserInfosByIDs($userIDs, $locale, $dateRegistered) {
	
		if (!empty($userIDs)) {
			$query = "select users.username as username, users.disabled as disabled, us1.setting_value as first, us2.setting_value as last, users.email as email ".
					"from users left join user_settings as us1 ".
					"on users.user_id=us1.user_id left join user_settings as us2 on us1.user_id=us2.user_id ".
					"where us1.setting_name='givenName' and us2.setting_name='familyName' ".
					"and us1.locale='".$locale."' and us2.locale='".$locale."' ".
					"and users.user_id in(".implode(",", $userIDs).")";
			if (!empty($dateRegistered)) {
				$query = $query . " and users.date_registered > '" . $dateRegistered."'";
			}

			$result = $this->retrieve($query);
			$data = array();
		
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$data[$this->convertFromDB($row['email'],null)] = 	$this->convertFromDB($row['username'],null).",".
																	$this->convertFromDB($row['first'],null).",".
																	$this->convertFromDB($row['last'],null).",".
																	$this->convertFromDB($row['disabled'],null);																	
				$result->MoveNext();
			}
			$result->Close();
			return $data;

		} else {
			return array();
		}
	}

	function getUserInfosByGroupsAND($groupIDs, $locale, $dateRegistered) {

		if (!empty($groupIDs)) {	
			$query = "";
			$pos0 = true;
			for ($i=0; $i<sizeof($groupIDs);$i++) {
				if ($pos0) {
					$query = $query . "(select user_id from user_user_groups where user_group_id=".$groupIDs[$i].") ";
					$pos0=false;
				} else {
					$query =  " (select user_id from user_user_groups where user_group_id=".$groupIDs[$i]." and user_id in " . $query . ")";
				}
			}
			$query = "SELECT user_id from users where user_id IN " . $query . ";";
			$result = $this->retrieve($query);
			
			$userIDs = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$userIDs[] = $this->convertFromDB($row['user_id'],null);
				$result->MoveNext();
			}
			$result->Close();
			return $this->getUserInfosByIDs($userIDs, $locale, $dateRegistered);
		} else {
			return array();
		}
	}

	function getUserInfosByGroupsOR($groupIDs, $locale, $dateRegistered) {
		
		if (!empty($groupIDs)) {		
			$result = $this->retrieve("select user_id from user_user_groups where user_group_id in (".implode(',', $groupIDs).")");
			
			$userIDs = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$userIDs[] = $this->convertFromDB($row['user_id'],null); 
				$result->MoveNext();
			}
			$result->Close();
			return $this->getUserInfosByIDs($userIDs, $locale, $dateRegistered);
		} else {
			return array();
		}
	}	
}

?>