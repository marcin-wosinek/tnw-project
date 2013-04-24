<?php
/**
 * Description of Ajax
 *
 * Kwgl_Ajax::setError("myerror");
 * Kwgl_Ajax::setResponse("myVar", "Hello world");
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Ajax
 */
class Kwgl_Ajax {

	private static $_aResponseErrors = array();
	private static $_aResponseMessage = array();

	/**
	 * set error messages
	 *
	 * @param string | array $mMessage
	 */
	public static function setError ($mMessage) {
		if (is_array($mMessage)) {
			self::$_aResponseErrors = array_merge(self::$_aResponseErrors, $mMessage);
		} else {
			self::$_aResponseErrors[] = $mMessage;
		}
	}

	/**
	 * set response array
	 *
	 * @param string $mKey
	 * @param string $mMessage
	 */
	public static function setResponse ($mKey, $mMessage = null) {
		// If message is not provided, key is the message array
		if (is_null($mMessage)) {
			if (is_array($mKey)) {
				self::$_aResponseMessage = array_merge(self::$_aResponseMessage, $mKey);
			} else {
				self::$_aResponseMessage[] = $mKey;
			}
		} else {
			self::$_aResponseMessage[$mKey] = $mMessage;
		}
	}

	/**
	 * display items
	 */
	public static function display() {
		$aResponse = array();

		if (count(self::$_aResponseMessage) > 0)
			$aResponse['response'] = self::$_aResponseMessage;

		if (count(self::$_aResponseErrors) > 0)
			$aResponse['error'] = self::$_aResponseErrors;

		// Clear Arrays
		self::$_aResponseMessage = array();
		self::$_aResponseErrors = array();

		// Output Result
		return Zend_Json::encode($aResponse);
	}

	/**
	 * Checks if there is anything to dispatch
	 *
	 * @return boolean
	 */
	public static function hasResult() {
		return (count(self::$_aResponseMessage) > 0 || count(self::$_aResponseErrors) > 0);
	}

	/**
	 * Checks if there are errors
	 *
	 * @return boolean true if errors to be output
	 */
	public static function hasError() {
		return (count(self::$_aResponseErrors) > 0);
	}

	public function hasResponse() {
		return (count(self::$_aResponseMessage) > 0);
	}

}