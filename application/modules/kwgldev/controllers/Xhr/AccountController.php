<?php
/**
 *
 */
class Kwgldev_Xhr_AccountController extends Kwgl_Controller_Action {

	public function listingAction () {

		$oAccount = new Model_Kwgldev_Account();
		$oSelect = $oAccount->listAccountsQuery();

		$this->buildPagination($oSelect);

	}

}