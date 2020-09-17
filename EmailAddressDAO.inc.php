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
