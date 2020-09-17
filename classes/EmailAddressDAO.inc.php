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
	
	
	function getUserIDsByGroupAND($groupIDs) {
		
		$userIDs = array();
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

			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$userIDs[] = $this->convertFromDB($row['user_id'],null); 
				$result->MoveNext();
			}
			$result->Close();
		
		} else {
			return array();
		}	
			
		if (!empty($userIDs)) {
			$query = "select user_settings.setting_value as first, us.setting_value as last, users.email as email from users left join user_settings ".
					 "on users.user_id=user_settings.user_id left join user_settings as us on user_settings.user_id=us.user_id ".
					 "where user_settings.setting_name='familyName' and  us.setting_name='givenName' and users.user_id in ". 
					 "(".implode(",", $userIDs).")";

			$result = $this->retrieve($query);
			$data = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$data[$this->convertFromDB($row['email'],null)] = $this->convertFromDB($row['first'],null)." ".$this->convertFromDB($row['last'],null); 
				$result->MoveNext();
			}
			$result->Close();
			return $data;			
			
		} else {
			return array();
		}	
	}
		

	function getUserIDsByGroupOR($groupIds) {
		$groupIdsQuery = "(".implode(",", $groupIds).")";
		$result = $this->retrieve("select user_id from user_user_groups where user_group_id in ".$groupIdsQuery);
		
		$userIDs = array();
		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$userIDs[] = $this->convertFromDB($row['user_id'],null); 
			$result->MoveNext();
		}
		$result->Close();
		
		if (!empty($userIDs)) {
			$query = "select user_settings.setting_value as first, us.setting_value as last, users.email as email from users left join user_settings ".
					 "on users.user_id=user_settings.user_id left join user_settings as us on user_settings.user_id=us.user_id ".
					 "where user_settings.setting_name='familyName' and  us.setting_name='givenName' and users.user_id in ". 
					 "(".implode(",", $userIDs).")";

			$result = $this->retrieve($query);
			$data = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$data[$this->convertFromDB($row['email'],null)] = $this->convertFromDB($row['first'],null)." ".$this->convertFromDB($row['last'],null); 
				$result->MoveNext();
			}
			$result->Close();
			return $data;			
			
		} else {
			return array();
		}	
	}
	
	function getEmailsByGroup($query) {

		$result = $this->retrieve($query);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$emails = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$emails[$this->convertFromDB($row['email'],null)] = $this->convertFromDB($row['first_name'],null) . " " . $this->convertFromDB($row['last_name'],null);		 
				$result->MoveNext();
			}
			$result->Close();
			return $emails;
		}
	}

	function getUserRoles($userId) {
		$result = $this->retrieve(
			'select setting_value from user_group_settings where setting_name = "name" and locale="en_US" and
			 user_group_id in (select user_group_id from user_user_groups where user_id = '.$userId.')');
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$userGroups = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$userGroups[] = $this->convertFromDB($row['setting_value'],null);
				$result->MoveNext();
			}
			$result->Close();
			return $userGroups;
		}	
	}
}

?>
