<?php
/**
 *
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category KwglDev
 * @package Model
 * @subpackage Kwgldev_Mail
 */
class Model_Kwgldev_Mail extends Kwgl_Model {

	/**
	 *
	 * @var string
	 */
	protected $_sMailTemplateClass = null;

	/**
	 *
	 * @var Kwgl_Db_Table
	 */
	protected $_oDaoMailTemplates = null;

	/**
	 *
	 * @return boolean
	 */
	protected function _doesMailTemplateClassExist () {
		$sMailTemplateClass = Kwgl_Config::get(array('mail', 'template', 'class'));
		if (is_null($sMailTemplateClass)) {
			return false;
		}

		try {
			$this->_oDaoMailTemplates = Kwgl_Db_Table::factory($sMailTemplateClass); /* @var $this->_oDaoMailTemplates $sMailTemplateClass */
		} catch (Exception $oException) {
			return false;
		}

		$this->_sMailTemplateClass = $sMailTemplateClass;
		return true;
	}

	/**
	 *
	 * @return array
	 */
	public function listTemplates () {

		$aContent = array();

		if (!$this->_doesMailTemplateClassExist()) {

			Model_Kwgldev_Response::addResponse(
					'A Mail Template Class (for DAO) has not been specified in the Configuration (application.ini). No Mail Templates to view.',
					Model_Kwgldev_Response::STATUS_WARNING);

			return $aContent;
		}

		$aTemplateListing = $this->_oDaoMailTemplates->fetchAll(null, 'name ASC');
		$aContent['listing'] = $aTemplateListing;

		if (count($aTemplateListing) == 0) {
			Model_Kwgldev_Response::addResponse(
					'No Mail Templates have been defined.',
					Model_Kwgldev_Response::STATUS_INFORMATION);
		}

		return $aContent;

	}

	public function createTemplate () {

		$aContent = array();

		if (!$this->_doesMailTemplateClassExist()) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		$oForm = new Form_Kwgldev_MailTemplates(Form_Kwgldev_MailTemplates::CONTEXT_CREATE, null, null);

		if ($this->_oRequest->isPost()) {
			if ($oForm->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oForm->getValues();

				$aInsertData = array();
				$aInsertData['name'] = $aFormValues['textName'];
				$aInsertData['subject'] = $aFormValues['textSubject'];
				$aInsertData['body'] = $aFormValues['textBody'];
				$this->_oDaoMailTemplates->insert($aInsertData);

				$oForm = new Form_Kwgldev_MailTemplates(Form_Kwgldev_MailTemplates::CONTEXT_CREATE, null, null);

				Model_Kwgldev_Response::addResponse(
					'Mail Template "' . $aFormValues['textName'] . '" has been created.',
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

	public function updateTemplate () {

		$aContent = array();

		if (!$this->_doesMailTemplateClassExist()) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		if (!in_array('id', $this->_aParameterKey)) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		$sId = $this->_aParameter['id'];
		$aMailTemplateDetail = $this->_oDaoMailTemplates->fetchDetail(null, array('id = ?' => $sId));
		if (empty($aMailTemplateDetail)) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		$oForm = new Form_Kwgldev_MailTemplates(Form_Kwgldev_MailTemplates::CONTEXT_UPDATE, null, array('template_id' => $sId));
		if ($this->_oRequest->isPost()) {
			if ($oForm->isValid($this->_oRequest->getPost())) {
				$aFormValues = $oForm->getValues();

				$aUpdateData = array();
				$aUpdateData['name'] = $aFormValues['textName'];
				$aUpdateData['subject'] = $aFormValues['textSubject'];
				$aUpdateData['body'] = $aFormValues['textBody'];
				$this->_oDaoMailTemplates->update($aUpdateData, array('id = ?' => $sId));

				Model_Kwgldev_Response::addResponse(
					'Mail Template "' . $aFormValues['textName'] . '" has been updated.',
					Model_Kwgldev_Response::STATUS_SUCCESS);

			} else {
				Model_Kwgldev_Response::addResponse(
					'There were errors in your form submission. Please correct them and re-submit.',
					Model_Kwgldev_Response::STATUS_ERROR);
			}
		} else {
			$aPopulateData = array();
			$aPopulateData['textName'] = $aMailTemplateDetail['name'];
			$aPopulateData['textSubject'] = $aMailTemplateDetail['subject'];
			$aPopulateData['textBody'] = $aMailTemplateDetail['body'];
			$oForm->populate($aPopulateData);
		}

		$aContent['form'] = $oForm;

		return $aContent;

	}

	public function deleteTemplate () {

		$aContent = array();

		if (!$this->_doesMailTemplateClassExist()) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		if (!in_array('id', $this->_aParameterKey)) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		$sId = $this->_aParameter['id'];
		$aMailTemplateDetail = $this->_oDaoMailTemplates->fetchDetail(null, array('id = ?' => $sId));
		if (empty($aMailTemplateDetail)) {
			$aContent['redirect'] = '/kwgldev/mail/';
			return $aContent;
		}

		$oForm = new Form_Kwgldev_MailTemplates(Form_Kwgldev_MailTemplates::CONTEXT_DELETE, null, array('template_id' => $sId));
		if ($this->_oRequest->isPost()) {
			if ($oForm->isValid($this->_oRequest->getPost())) {
				$this->_oDaoMailTemplates->delete(array('id = ?' => $sId));

				$aContent['redirect'] = '/kwgldev/mail/';
				return $aContent;
			} else {
				Model_Kwgldev_Response::addResponse(
					'There were errors in your form submission. Please correct them and re-submit.',
					Model_Kwgldev_Response::STATUS_ERROR);
			}
		} else {
			$aPopulateData = array();
			$aPopulateData['textName'] = $aMailTemplateDetail['name'];
			$aPopulateData['textSubject'] = $aMailTemplateDetail['subject'];
			$aPopulateData['textBody'] = $aMailTemplateDetail['body'];
			$oForm->populate($aPopulateData);
		}

		$aContent['form'] = $oForm;

		return $aContent;
	}

	public function manageTemplates () {

		$aContent = array();
		$aResponse = array();


		$aContent['response'] = $aResponse;

		return $aContent;

	}

}