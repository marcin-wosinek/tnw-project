<?php
/**
 * Authenticate Class for handling all Login / Logout / Session related functionality
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Authenticate
 */
class Kwgl_Authenticate {

	/**
	 * Store the Authentication Configuration
	 * @var array
	 */
	private static $aAuthConfig = array();

	/**
	 * Holds a Zend_Auth Instance
	 * @var Zend_Auth
	 */
	public static $_oAuthenticate = null;

	/**
	 * Indicates whether the Authenticate class has been initialised
	 * @var boolean
	 */
	public static $_bOn = false;


	public function __construct () {
		self::initialise();
	}

	public static function setAuthConfig ($mConfig) {
		if ($mConfig instanceof Zend_Config_Ini) {
			self::$aAuthConfig = $mConfig->toArray();
		} elseif (is_array($mConfig)) {
			self::$aAuthConfig = $mConfig;
		} elseif(is_string($mConfig)) {
			//try to load the file
			$sSuffix = strtolower(pathinfo($mConfig, PATHINFO_EXTENSION));
			//get object based on file extension
			switch ($sSuffix) {
				case 'ini':
					$oConfig = new Zend_Config_Ini($mConfig);
					break;
				case 'xml':
					$oConfig = new Zend_Config_Xml($mConfig);
					break;
				case 'json':
					$oConfig = new Zend_Config_Json($mConfig);
					break;
				case 'yaml':
					$oConfig = new Zend_Config_Yaml($mConfig);
					break;
				case 'php':
				case 'inc':
					$oConfig = include $mConfig;
					if (!is_array($oConfig)) {
						throw new Exception('Invalid configuration file provided; PHP file does not return array value');
					}
					return $oConfig;
					break;
				default:
					throw new Exception('Invalid configuration file provided; unknown config type');
			}
			//save as an array
			self::$aAuthConfig = $oConfig->toArray();
		} else{
			throw new Exception('Invalid database configuration provided');
		}
	}

	/**
	 * Initialise the Zend Auth Object
	 */
	public static function initialise () {
		self::$_oAuthenticate = Zend_Auth::getInstance();
		self::$_oAuthenticate->setStorage(new Kwgl_Auth_Storage_Db());

		self::$_bOn = true;
	}

	/**
	 * Carry out Login for given Username / Password and optional conditions
	 * Auto Login - ?
	 *
	 * @param string $sIdentity
	 * @param string $sCredential
	 * @param boolean $bAutoLogin
	 * @param array $aWhereClause
	 * @return boolean
	 */
	public static function login ($sIdentity, $sCredential, $bAutoLogin = false, $aWhereClause = null) {
		if ($bAutoLogin) {
			$iRememberMeDuration = self::$aAuthConfig['remember']['duration'];
			Zend_Session::rememberMe($iRememberMeDuration);
		} else {
			Zend_Session::forgetMe();
		}

        if (self::$_bOn) {
			// Clear the Identity since you are trying to login
			//Zend_Auth::getInstance()->clearIdentity();

			if (isset(self::$aAuthConfig['account']['identifier'])) {
				$sIdentityField = self::$aAuthConfig['account']['identifier'];
			} else {
				$sIdentityField = 'username';
			}

			if (isset(self::$aAuthConfig['account']['credential'])) {
				$sCredentialField = self::$aAuthConfig['account']['credential'];
			} else {
				$sCredentialField = 'password';
			}

			if (isset(self::$aAuthConfig['account']['table']['name'])) {
				$sAuthenticatingTable = self::$aAuthConfig['account']['table']['name'];
			} else {
				$sAuthenticatingTable = 'sys_account';
			}

			$sCombinedClause = '';
			if (!empty($aWhereClause)) {
				// Combine Where Conditions
				foreach ($aWhereClause as $sCondition) {
					$sCombinedClause .= ' AND (' . $sCondition . ')';
				}
			}

			$oDb = Zend_Registry::get(DB);
			$oAuthenticateAdapter = new Zend_Auth_Adapter_DbTable($oDb);
			$oAuthenticateAdapter->setTableName($sAuthenticatingTable);

			$oAuthenticateAdapter->setIdentityColumn($sIdentityField);
			$oAuthenticateAdapter->setIdentity($sIdentity);

			$oAuthenticateAdapter->setCredentialColumn($sCredentialField);
			$oAuthenticateAdapter->setCredentialTreatment('UNHEX(SHA1(?))' . $sCombinedClause);
			$oAuthenticateAdapter->setCredential($sCredential);

			$oAuthenticateResult = self::$_oAuthenticate->authenticate($oAuthenticateAdapter);

			// Process Result from Authentication Attempt
			switch ($oAuthenticateResult->getCode()) {
				case Zend_Auth_Result::SUCCESS:
					// Log the User In
					$oAuthenticateStorageData = $oAuthenticateAdapter->getResultRowObject(array($sIdentityField));
					self::$_oAuthenticate->getStorage()->write($oAuthenticateStorageData);
					$bStatus = true;
					break;
				case Zend_Auth_Result::FAILURE:
				case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
				case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
				default:
					$bStatus = false;
					break;
			}

			return $bStatus;
		} else {
			self::initialise();
			return self::login($sIdentity, $sCredential, $bAutoLogin);
		}
	}

	/**
	 * Carry out Logout for any existing Zend Auth instances / identities
	 * @return boolean
	 */
	public static function logout () {
		if (self::$_bOn) {
			self::$_oAuthenticate->clearIdentity();
			return true;
		} else {
			self::initialise();
			return self::logout();
		}
	}

	public static function verifySession () {
		// Check flags on Session Verification
		$bBrowserCheck = (boolean)self::$aAuthConfig['session']['verify']['browser'];
		$bIpCheck = (boolean)self::$aAuthConfig['session']['verify']['ip'];

		// Exit if none of the checks are to be run
		if (!$bBrowserCheck && !$bIpCheck) {
			return true;
		}

		//$aIdentity = Zend_Auth::getInstance()->getIdentity();
		$aIdentity = Kwgl_User::getIdentity();

		if ($bBrowserCheck) {
			$sUserAgentHash = md5($_SERVER['HTTP_USER_AGENT']);
			if ($sUserAgentHash != $aIdentity['user_agent_hash']) {
				// User Agent (Browser) has changed, verification failed
				return false;
			}
		}

		if ($bIpCheck) {
			$sRequestHostname = Kwgl_Utility_Ip::getIp();
			$aRequestHostname = explode('.', $sRequestHostname);

			$sSessionHostname = $aIdentity['hostname'];
			$aSessionHostname = explode('.', $sSessionHostname);

			if ($aRequestHostname[0] == $aSessionHostname[0]
					&&
				$aRequestHostname[1] == $aSessionHostname[1]
					&&
				$aRequestHostname[2] == $aSessionHostname[2]) {
				// First three sections of IP Valid
			} else {
				// First three sections of IP Invalid, verification failed
				return false;
			}
		}

		return true;
	}

}