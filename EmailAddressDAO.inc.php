<?php

/**
 * @file plugins/generic/groupMail/GroupMailDAO.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.  
 *
 * @class GroupMailDAO
 *
 */

class EmailAddressDAO extends DAO {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	function getUserGroups($contextId, $locale) {

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
	
	function getUserInfosByIDs($userIDs, $locale) {
	
		if (!empty($userIDs)) {
			$query = "select users.username as username, users.disabled as disabled, us1.setting_value as first, us2.setting_value as last, users.email as email ".
					"from users left join user_settings as us1 ".
					"on users.user_id=us1.user_id left join user_settings as us2 on us1.user_id=us2.user_id ".
					"where us1.setting_name='givenName' and us2.setting_name='familyName' ".
					"and us1.locale='".$locale."' and us2.locale='".$locale."' ".
					"and users.user_id in(".implode(",", $userIDs).")";

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

	function getUserInfosByGroupsAND($groupIDs, $locale) {

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
			return $this->getUserInfosByIDs($userIDs, $locale);
		} else {
			return array();
		}
		
		
	}

	function getUserInfosByGroupsOR($groupIDs, $locale) {
		
		if (!empty($groupIDs)) {		
			$result = $this->retrieve("select user_id from user_user_groups where user_group_id in (".implode(',', $groupIDs).")");
			
			$userIDs = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$userIDs[] = $this->convertFromDB($row['user_id'],null); 
				$result->MoveNext();
			}
			$result->Close();
			return $this->getUserInfosByIDs($userIDs, $locale);
		} else {
			return array();
		}
	}	
}

?>