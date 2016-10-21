<?php

/**
 * User service
 */
class UserService extends BaseService
{

	/**
	 * Returns Role ID of logged user
	 *
	 * @param   void
	 * @return  type
	 */
	public function getRoleId()
	{
		return $this->skautis->getRoleId();
	}


	/**
	 * Returns all SkautIS roles
	 *
	 * @param   bool   $activeOnly  only active roles
	 * @return  array               all roles of logged user
	 */
	public function getAllSkautISRoles($activeOnly = true)
	{
		return $this->skautis->user->UserRoleAll(array("ID_User" => $this->getUserDetail()->ID, "IsActive" => $activeOnly));
	}


	/**
	 * Gets user detail
	 *
	 * @param   void
	 * @return  res
	 */
	public function getUserDetail()
	{
		$id = __FUNCTION__;
		// cache by the request
		if (!($res = $this->load($id))) {
			$res = $this->save($id, $this->skautis->user->UserDetail());
		}
		return $res;
	}


	/**
	 * Changes the loggeed user SkautIS role
	 *
	 * @param   ID_Role  $id
	 * @return  void
	 */
	public function updateSkautISRole($id)
	{
		$unitId = $this->skautis->user->LoginUpdate(array("ID_UserRole" => $id, "ID" => $this->skautis->getToken()));
		if ($unitId) {
			$this->skautis->setRoleId($id);
			$this->skautis->setUnitId($unitId->ID_Unit);
		}
	}


	/**
	 * Returns complete list of information abour logged user
	 *
	 * @param   void
	 * @return  type
	 */
	public function getPersonalDetail()
	{
		$user = $this->getUserDetail();
		$person = $this->skautis->org->personDetail((array("ID" => $user->ID_Person)));
		return $person;
	}


	/**
	 * Check if login session is still valid
	 *
	 * @param   void
	 * @return  type
	 */
	public function isLoggedIn()
	{
		return $this->skautis->isLoggedIn();
	}


	/**
	 * Resets login data
	 *
	 * @param   void
	 * @return  void
	 */
	public function resetLoginData()
	{
		$this->skautis->resetLoginData();
	}


	/**
	 * Verify action
	 *
	 * @param   type  $table      např. ID_EventGeneral, NULL = oveření nad celou tabulkou
	 * @param   type  $id         id ověřované akce - např EV_EventGeneral_UPDATE
	 * @param   type  $ID_Action  tabulka v DB skautisu
	 * @return  BOOL|stdClass|array
	 */
	public function actionVerify($table, $id = NULL, $ID_Action = NULL)
	{
		$res = $this->skautis->user->ActionVerify(array(
			"ID" => $id,
			"ID_Table" => $table,
			"ID_Action" => $ID_Action,
		));

		// returns boolean if certain function for verifying is set
		if ($ID_Action !== NULL) {
			if ($res instanceof stdClass) {
				return false;
			}
			if (is_array($res)) {
				return true;
			}
		}
		if (is_array($res)) {
			$tmp = array();
			foreach ($res as $v) {
				$tmp[$v->ID] = $v;
			}
			return $tmp;
		}
		return $res;
	}

}
