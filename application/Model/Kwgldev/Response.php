<?php
/**
 * Description of Response
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Model_Kwgldev_Response {

	const STATUS_SUCCESS = 'success';
	const STATUS_ERROR = 'error';
	const STATUS_WARNING = 'warning';
	const STATUS_INFORMATION = 'information';

	protected static $_aResponses = array();

	public static function addResponse ($sMessage, $sStatus) {
		switch ($sStatus) {
			case self::STATUS_SUCCESS:
			case self::STATUS_ERROR:
			case self::STATUS_WARNING:
			case self::STATUS_INFORMATION:
				break;
			default:
				return;
		}

		$aResponseEntry = array(
			'status' => $sStatus,
			'message' => $sMessage,
		);
		self::$_aResponses[] = $aResponseEntry;
	}

	public static function getResponseList () {
		return self::$_aResponses;
	}
}