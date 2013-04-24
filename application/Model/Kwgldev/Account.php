<?php
/**
 *
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category KwglDev
 * @package Model
 * @subpackage Kwgldev_Mail
 */
class Model_Kwgldev_Account extends Kwgl_Model {

	/**
	 *
	 * @return array
	 */
	public function listAccounts () {

		$aContent = array();

		$oDaoAccount = Kwgl_Db_Table::factory('System_Account'); /* @var $oDaoAccount Dao_System_Account */
		$aAccountListing = $oDaoAccount->fetchList(null, null, array('username ASC'));

		$iAmount = 25;

		if (in_array('page', $this->_aParameterKey)) {
			$sRequestPage = $this->_aParameter['page'];
		} else {
			$sRequestPage = 1;
		}
		$iPage = (int)$sRequestPage;

		$oAccountListingPaginator = Zend_Paginator::factory($aAccountListing);
		$oAccountListingPaginator->setItemCountPerPage($iAmount);
		$oAccountListingPaginator->setCurrentPageNumber($iPage);

		$aContent['listing'] = $oAccountListingPaginator;


		$oDaoRole = Kwgl_Db_Table::factory('System_Role'); /* @var $oDaoRole Dao_System_Role */
		$aRoleListing = $oDaoRole->getRoleNamesWithId();
		$aContent['role-listing'] = $aRoleListing;

		return $aContent;

	}

	public function listAccountsQuery () {

		$oQuery = new Zend_Db_Select(Zend_Registry::get(DB));
		$oQuery->from(array('sa' => Kwgl_Db_Table::name()->System_Account), array('id', 'username', 'email'));
		$oQuery->join(array('sr' => KWgl_Db_Table::name()->System_Role), 'sr.id = sa.id_sys_role', array('role_name' => 'name'));

		return $oQuery;
	}

	public function createAccount () {

		$aContent = array();

		$oDaoAccount = Kwgl_Db_Table::factory('System_Account'); /* @var $oDaoAccount Dao_System_Account */
		$oForm = new Form_Kwgldev_SysAccount(Form_Kwgldev_SysAccount::CONTEXT_CREATE, null, null);

		if ($this->_oRequest->isPost()) {
			if ($oForm->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oForm->getValues();

				$aInsertData = array();
				$aInsertData['id_sys_role'] = $aFormValues['selectRole'];
				$aInsertData['email'] = $aFormValues['textEmail'];
				$aInsertData['username'] = $aFormValues['textUsername'];
				$aInsertData['password'] = sha1($aFormValues['passwordPassword'], true);
				$oDaoAccount->insert($aInsertData);

				$oForm =  new Form_Kwgldev_SysAccount(Form_Kwgldev_SysAccount::CONTEXT_CREATE, null, null);

				Model_Kwgldev_Response::addResponse(
					'Account with Username "' . $aFormValues['textUsername'] . '" has been created.',
					Model_Kwgldev_Response::STATUS_SUCCESS);
			} else {
				Model_Kwgldev_Response::addResponse(
					'There were errors in your form submission. Please correct them and re-submit.',
					Model_Kwgldev_Response::STATUS_ERROR);
			}
		}

		$aContent['form'] = $oForm;

		return $aContent;

	}

	public function updateAccount () {

		$aContent = array();

		$oDaoAccount = Kwgl_Db_Table::factory('System_Account'); /* @var $oDaoAccount Dao_System_Account */

		if (!in_array('id', $this->_aParameterKey)) {
			$aContent['redirect'] = '/kwgldev/account/';
			return $aContent;
		}

		$sId = $this->_aParameter['id'];
		$aAccountDetail = $oDaoAccount->fetchDetail(null, array('id = ?' => $sId));
		if (empty($aAccountDetail)) {
			$aContent['redirect'] = '/kwgldev/account/';
			return $aContent;
		}

		$oForm = new Form_Kwgldev_SysAccount(Form_Kwgldev_SysAccount::CONTEXT_UPDATE, null, array('account_id' => $sId));
		if ($this->_oRequest->isPost()) {
			if ($oForm->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oForm->getValues();

				$aUpdateData = array();
				$aUpdateData['id_sys_role'] = $aFormValues['selectRole'];
				$aUpdateData['email'] = $aFormValues['textEmail'];
				$aUpdateData['username'] = $aFormValues['textUsername'];
				if (isset($aFormValues['passwordPassword'])) {
					$aUpdateData['password'] = sha1($aFormValues['passwordPassword'], true);
				}
				$oDaoAccount->update($aUpdateData, array('id = ?' => $sId));

				Model_Kwgldev_Response::addResponse(
					'Account details has been updated.',
					Model_Kwgldev_Response::STATUS_SUCCESS);

			} else {
				Model_Kwgldev_Response::addResponse(
					'There were errors in your form submission. Please correct them and re-submit.',
					Model_Kwgldev_Response::STATUS_ERROR);
			}
		} else {
			$aPopulateData = array();
			$aPopulateData['selectRole'] = $aAccountDetail['id_sys_role'];
			$aPopulateData['textEmail'] = $aAccountDetail['email'];
			$aPopulateData['textUsername'] = $aAccountDetail['username'];
			$oForm->populate($aPopulateData);
		}

		$aContent['form'] = $oForm;

		return $aContent;

	}

	public function deleteAccount () {

		$aContent = array();

		$oDaoAccount = Kwgl_Db_Table::factory('System_Account'); /* @var $oDaoAccount Dao_System_Account */

		if (!in_array('id', $this->_aParameterKey)) {
			$aContent['redirect'] = '/kwgldev/account/';
			return $aContent;
		}

		$sId = $this->_aParameter['id'];
		$aAccountDetail = $oDaoAccount->fetchDetail(null, array('id = ?' => $sId));
		if (empty($aAccountDetail)) {
			$aContent['redirect'] = '/kwgldev/account/';
			return $aContent;
		}

		$oForm = new Form_Kwgldev_SysAccount(Form_Kwgldev_SysAccount::CONTEXT_DELETE, null, array('account_id' => $sId));
		if ($this->_oRequest->isPost()) {
			if ($oForm->isValid($this->_oRequest->getPost())) {
				$oDaoAccount->delete(array('id = ?' => $sId));

				$aContent['redirect'] = '/kwgldev/account/';
				return $aContent;
			} else {
				Model_Kwgldev_Response::addResponse(
					'There were errors in your form submission. Please correct them and re-submit.',
					Model_Kwgldev_Response::STATUS_ERROR);
			}
		} else {
			$aPopulateData = array();
			$aPopulateData['selectRole'] = $aAccountDetail['id_sys_role'];
			$aPopulateData['textEmail'] = $aAccountDetail['email'];
			$aPopulateData['textUsername'] = $aAccountDetail['username'];
			$oForm->populate($aPopulateData);
		}

		$aContent['form'] = $oForm;

		return $aContent;
	}

}