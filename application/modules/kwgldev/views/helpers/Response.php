<?php

/**
 * Description of Response
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Kwgldev_View_Helper_Response {

	public function response ($bShowCloseButton = true) {

		$aResponseListing = Model_Kwgldev_Response::getResponseList();

		$sContent = '';

		if (!empty($aResponseListing)) {
			foreach ($aResponseListing as $aResponse) {
				$sStatus = $aResponse['status'];
				$sMessage = $aResponse['message'];
				switch ($sStatus) {
					case 'success':
						$sResponseClass = 'cAlertSuccess';
						break;
					case 'error':
						$sResponseClass = 'cAlertError';
						break;
					case 'warning':
						$sResponseClass = 'cAlertWarning';
						break;
					case 'information':
					default:
						$sResponseClass = 'cAlertInfo';
						break;
				}

				$sContent .= '<div class="cAlert ' . $sResponseClass . '">';
				if ($bShowCloseButton) {
					$sContent .= '<button class="cClose" data-dismiss="alert">×</button>';
				}
				$sContent .= $sMessage . '</div>';
			}
		}

		return $sContent;

	}

}