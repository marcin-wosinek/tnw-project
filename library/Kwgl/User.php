<?php
/**
 * Provides utility-based logged in user functionality
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_User
 */
class Kwgl_User {

	/**
	 * Static instance
	 *
	 * @var Zend_Auth
	 */
	protected static $_oZendAuthInstance = null;

	/**
	 * Static Identity
	 *
	 * @var array
	 */
//	protected static $_aIdentity = null;
	protected static $_aIdentity = false;

	/**
	 * Static User Details
	 *
	 * @var array
	 */
	protected static $_aUserDetail = null;

	/**
	 * Static Role Details
	 *
	 * @var array
	 */
	protected static $_aRoleDetail = null;

	/**
	 * Static Extended Details
	 *
	 * @var array
	 */
	protected static $_aExtendedDetail = null;

	/**
	 * Static Authenticate Options
	 *
	 * @var array
	 */
	protected static $_aOptions = null;

	/**
	 * Singleton pattern implementation makes new unavailable.
	 *
	 * @return void
	 */
	protected function __construct() {

	}

	/**
	 * Initialises the static members used in the class for use later on to retrieve User and Role details.
	 *
	 * @param boolean $bFresh To indicate if static data stored should be re-initialised to ensure they are current
	 */
	protected static function initialise ($bFresh = false) {
		// Get the Singleton Object if it has not yet been assigned or if a 'fresh' one is required
		if (is_null(self::$_oZendAuthInstance) || $bFresh) {
			self::$_oZendAuthInstance = Zend_Auth::getInstance();
		}

		// Fetch the Identity Data if it has not yet been assigned or if a 'fresh' one is required
//		if (is_null(self::$_aIdentity) || $bFresh) {
		if (self::isIdentityNotSet() || $bFresh) {
			self::$_aIdentity = self::$_oZendAuthInstance->getIdentity();
		}

		// Check if Options have been initialised properly
		if (is_null(self::$_aOptions) || $bFresh) {
			self::$_aOptions = Kwgl_Config::get('auth');
		}

		// If User Details have not yet been populated, fetch the Data and populate it or if a 'fresh' one is required
		if (is_null(self::$_aUserDetail) || $bFresh) {
			if (!empty(self::$_aIdentity)) {
				self::$_aUserDetail = array();
				$mWhereCondition = array(
					self::$_aOptions['account']['table']['primary'] . ' = ?' => self::$_aIdentity[self::$_aOptions['session']['table']['reference']['account']],
				);

				$oDaoAccount = Kwgl_Db_Table::factory(self::$_aOptions['account']['table']['class']);
				$aAccountDetail = $oDaoAccount->fetchRow($mWhereCondition);
				if (!empty($aAccountDetail)) {
					self::$_aUserDetail = $aAccountDetail;
				}
			}
		}
	}

	/**
	 * Returns the Zend Auth Identity
	 *
	 * @param string $sField To indicate if a specified field from the Identity should be returned instead
	 * @param boolean $bFresh To indicate is static data should be re-initialised to ensure they are current
	 * @return array|mixed
	 */
	public static function getIdentity ($sField = null, $bFresh = false) {

		self::initialise($bFresh);
		$mDetail = null;

		if (!self::isIdentityNotSet()) {
			if (is_null($sField)) {
				$mDetail = self::$_aIdentity;
			} else {
				if (isset(self::$_aIdentity[$sField])) {
					$mDetail = self::$_aIdentity[$sField];
				}
			}
		}

		return $mDetail;

	}

	/**
	 * Returns the User's Account Details or a specific field from the Account Details.
	 *
	 * @param string $sField To indicate if a specific field from the User Details should be returned instead.
	 * @param boolean $bFresh To indicate if static data stored should be re-initialised to ensure they are current
	 * @return Zend_Db_Table_Row_Abstract|string
	 */
	public static function get ($sField = null, $bFresh = false) {
		self::initialise($bFresh);
		$mDetail = null;

		if (!is_null(self::$_aUserDetail)) {
			if (is_null($sField)) {
				$mDetail = self::$_aUserDetail;
			} else {
				if (isset(self::$_aUserDetail[$sField])) {
					$mDetail = self::$_aUserDetail[$sField];
				}
			}
		}

		return $mDetail;
	}

	/**
	 * Returns the User's Role Details or a specific field from the Role Details. Returns Role Details pertaining to the
	 * 'guest' role if the User is not logged in.
	 *
	 * @param string $sField To indicate if a specific field from the User Details should be returned instead.
	 * @param boolean $bFresh To indicate if static data stored should be re-initialised to ensure they are current
	 * @return Zend_Db_Table_Row_Abstract|string
	 */
	public static function getRole ($sField = null, $bFresh = false) {
		self::initialise($bFresh);
		$mDetail = null;

		if (is_null(self::$_aRoleDetail)) {
			self::$_aRoleDetail = array();
			$sRoleField = self::$_aOptions['account']['table']['reference']['role'];
			$iRoleId = self::get($sRoleField);

			if (isset($iRoleId)) {
				$mWhereCondition = array(
					self::$_aOptions['role']['table']['primary'] . ' = ?' => self::$_aUserDetail[(self::$_aOptions['account']['table']['reference']['role'])],
				);
				$oDaoRole = Kwgl_Db_Table::factory(self::$_aOptions['role']['table']['class']);
				$aRoleDetail = $oDaoRole->fetchRow($mWhereCondition);
				if (!empty($aRoleDetail)) {
					self::$_aRoleDetail = $aRoleDetail;
				}
			} else {
				$mWhereCondition = array(
					self::$_aOptions['role']['table']['primary'] . ' = ?' => self::$_aOptions['role']['default']['id'],
				);
				$oDaoRole = Kwgl_Db_Table::factory(self::$_aOptions['role']['table']['class']);
				$aRoleDetail = $oDaoRole->fetchRow($mWhereCondition);
				if (!empty($aRoleDetail)) {
					self::$_aRoleDetail = $aRoleDetail;
				}
			}
		}

		if (!is_null(self::$_aRoleDetail)) {
			if (is_null($sField)) {
				$mDetail = self::$_aRoleDetail;
			} else {
				if (isset(self::$_aRoleDetail[$sField])) {
					$mDetail = self::$_aRoleDetail[$sField];
				}
			}
		}

		return $mDetail;
	}

	/**
	 * Returns Extended Data stored in non-system tables using Sys_Account's  id
	 *
	 * @param array|mixed $aDataStore Table Namespace as defined in database.ini. Format is array('table' => 'tableNamespace', 'column' => 'fieldName').
	 * @param string $sField
	 * @param boolean $bFresh
	 * @return type
	 */
	public static function getExtended ($mDataStore, $sField = null, $bFresh = false) {
		self::initialise($bFresh);
		$mDetail = null;

		$sDefaultIdentifierColumn = 'id_sys_account';

		if (!is_null(self::$_aUserDetail)) {

			$iUserId = self::$_aUserDetail['id'];

			// Organise Data Store Information
			if (is_string($mDataStore)) {
				$aDataStore = array('table' => $mDataStore, 'column' => $sDefaultIdentifierColumn);
			} else {
				if (is_array($mDataStore)) {
					if (count($mDataStore) < 1) {
						// Throw Exception
						throw new Exception("Valid Data Store References not given to fetch Extended User Data.");
					}

					if (isset($mDataStore['table']) && isset($mDataStore['column'])) {
						$aDataStore = $mDataStore;
					} else {
						if (isset($mDataStore['table'])) {
							$aDataStore = array('table' => $mDataStore['table']);
							if (count($mDataStore) == 1) {
								$aDataStore['column'] = $sDefaultIdentifierColumn;
							} else {
								unset($mDataStore['table']);
								$aDataStore['column'] = array_shift($mDataStore);
							}
						} else {
							$aTemporaryDataStore = array_values($mDataStore);
							$aDataStore = array('table' => $aTemporaryDataStore[0]);
							if (isset($aTemporaryDataStore[1])) {
								$aDataStore['column'] = $aTemporaryDataStore[1];
							} else {
								$aDataStore['column'] = $sDefaultIdentifierColumn;
							}
						}
					}
				} else {
					// Throw Exception
					throw new Exception("Valid Data Store References not given to fetch Extended User Data.");
				}
			}

			// Check if Data Store information has already been retrieved
			if (is_null(self::$_aExtendedDetail)) {
				$bFetchData = true;
			} else {
				if (isset(self::$_aExtendedDetail[$aDataStore['table']])) {
					if ($bFresh) {
						$bFetchData = true;
					} else {
						$bFetchData = false;
					}
				} else {
					$bFetchData = true;
				}
			}

			// Get Information to return
			if ($bFetchData) {
				// Create DAO to interact with the Data Store
				$oDaoExtended = Kwgl_Db_Table::factory($aDataStore['table']);
				if (!is_null($oDaoExtended)) {
					$sCondition = $aDataStore['column'] . ' = ?';
					self::$_aExtendedDetail[$aDataStore['table']] = $oDaoExtended->fetchDetail(null, array($sCondition => $iUserId));
				}
			}

			// Prepare requested information for returning
			if (is_null($sField)) {
				$mDetail = self::$_aExtendedDetail[$aDataStore['table']];
			} elseif (!empty(self::$_aExtendedDetail[$aDataStore['table']])) {
				if (isset(self::$_aExtendedDetail[$aDataStore['table']][$sField])) {
					$mDetail = self::$_aExtendedDetail[$aDataStore['table']][$sField];
				}
			}

		}

		return $mDetail;
	}

	/**
	 * Returns true if there is a Valid User Logged In and false otherwise
	 *
	 * @param boolean $bFresh
	 * @return boolean
	 */
	public static function isLoggedIn ($bFresh = false) {
		$bLoggedIn = false;

		self::initialise($bFresh);
		if (!is_null(self::$_aUserDetail)) {
			$bLoggedIn = true;
		}

		return $bLoggedIn;
	}


	public static function isIdentityNotSet () {

		if (self::$_aIdentity === false) {
			return true;
		} else {
			return false;
		}

	}
}