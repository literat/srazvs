<?php

namespace App\Services\Skautis;

use Skautis\User;

/**
 * User service
 */
class UserService extends SkautisService
{

	/**
	 * @return \Skautis\User
	 */
	public function getUser(): User
	{
		return $this->getSkautis()->getUser();
	}

	/**
	 * Returns Role ID of logged user
	 *
	 * @param   void
	 * @return  type
	 */
	public function getRoleId()
	{
		return $this->getSkautis()->getRoleId();
	}


	/**
	 * Returns all Skautis roles
	 *
	 * @param   bool   $activeOnly  only active roles
	 * @return  array               all roles of logged user
	 */
	public function getAllSkautisRoles($activeOnly = true)
	{
		return $this->getSkautis()
			->user
			->UserRoleAll([
				'ID_User'  => $this->getUserDetail()->ID,
				'IsActive' => $activeOnly,
			]);
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
			$res = $this->save($id, $this->getSkautis()->user->UserDetail());
		}
		return $res;
	}


	/**
	 * Changes the loggeed user Skautis role
	 *
	 * @param   ID_Role  $id
	 * @return  void
	 */
	public function updateSkautisRole($id)
	{
		$skautis = $this->getSkautis();

		$unitId = $this->getSkautis()
			->user
			->LoginUpdate([
				'ID_UserRole' => $id,
				'ID'          => $skautis->getToken(),
			]);

		if ($unitId) {
			$skautis->setRoleId($id);
			$skautis->setUnitId($unitId->ID_Unit);
		}
	}


	/**
	 * Returns complete list of information about logged user
	 *
	 * @param   void
	 * @return  type
	 */
	public function getPersonalDetail($personId = null)
	{
		if(!$personId) {
			$personId = $this->getUserDetail()->ID_Person;
		}

		return $this->getSkautis()->org->personDetail((["ID" => $personId]));
	}

	/**
	 * Returns complete list of information about logged user unit
	 *
	 * @param   void
	 * @return  type
	 */
	public function getParentUnitDetail($unitId)
	{
		return $this->getSkautis()->org->unitAll((["ID_UnitChild" => $unitId]));
	}

	/**
	 * Returns complete list of information about logged user unit
	 *
	 * @param   void
	 * @return  type
	 */
	public function getUnitDetail($unitId)
	{
		return $this->getSkautis()->org->unitDetail((["ID" => $unitId]));
	}

	/**
	 * Returns complete list of information about logged user unit
	 *
	 * @param   void
	 * @return  type
	 */
	public function getPersonUnitDetail($personId)
	{
		$membership = $this->getSkautis()
			->org
			->membershipAllPerson(([
				'ID_Person'         => $personId,
				'ID_MembershipType' => 'radne'
			]));

		return $membership->MembershipAllOutput;
	}

	/**
	 * Check if login session is still valid
	 *
	 * @param   void
	 * @return  type
	 */
	public function isLoggedIn()
	{
		return $this->getSkautis()->getUser()->isLoggedIn();
	}


	/**
	 * Resets login data
	 *
	 * @param   void
	 * @return  void
	 */
	public function resetLoginData()
	{
		$this->getSkautis()->resetLoginData();
	}


	/**
	 * Verify action
	 *
	 * @param   type  $table      např. ID_EventGeneral, NULL = oveření nad celou tabulkou
	 * @param   type  $id         id ověřované akce - např EV_EventGeneral_UPDATE
	 * @param   type  $ID_Action  tabulka v DB skautisu
	 * @return  BOOL|stdClass|array
	 */
	public function actionVerify($table, $id = null, $actionId = null)
	{
		$res = $this->getSkautis()->user->ActionVerify([
			'ID'        => $id,
			'ID_Table'  => $table,
			'ID_Action' => $actionId,
		]);

		// returns boolean if certain function for verifying is set
		if ($actionId !== null) {
			if ($res instanceof stdClass) {
				return false;
			}
			if (is_array($res)) {
				return true;
			}
		}
		if (is_array($res)) {
			$tmp = [];
			foreach ($res as $v) {
				$tmp[$v->ID] = $v;
			}
			return $tmp;
		}
		return $res;
	}

}
