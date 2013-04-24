<?php
/**
 * Kwgl Library
 *
 * Main class for table objects. Wraps around zend db table and provides methods to create table objects on the fly
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Db
 */
class Kwgl_Db_Table extends Zend_Db_Table_Abstract {

	/**
	 * variable to store database configurations
	 * @var array
	 */
	private static $oDbConfig = array();

	/**
	 * contains database table names
	 * @var stdObject
	 */
	private static $oDbNames = null;

	/**
	 * stores the last executed query through custom methods
	 * @var Zend_Db_Table_Select
	 */
	private static $oLastCustomQuery = null;

	/**
	 * Constructor
	 *
	 * set table's properties based on what's defined in ini
	 *
	 * @param string $sTableNamespace
	 * @throws Exception
	 */
	public function __construct($sTableNamespace) {

		//get configs based on namespace
		if (!array_key_exists($sTableNamespace, self::$oDbConfig)) {
			throw new Exception("Table namespace ($sTableNamespace) doesnt exist in database configuration");
		}

		$aTableConf = self::$oDbConfig[$sTableNamespace];

		//filter database configurations
		$this->_filterConfig($aTableConf);

		//set configurations
		parent::__construct($aTableConf);

		// 2012-06-28 : Jayawi Perera
		// Added Database Metadata Caching
		if (Kwgl_Config::get(array('mode', 'cache', 'dbmetadata')) == 1) {
			$oCacheManager = Kwgl_Cache::getManager();
			$oDatabaseMetadataCache = $oCacheManager->getCache('dbmetadata');
			Zend_Db_Table_Abstract::setDefaultMetadataCache($oDatabaseMetadataCache);
		}

	}

	/**
	 * filter database configurations
	 *
	 * @param array &$aConfig
	 */
	private function _filterConfig(&$aConfig) {

		//set row class to default if not defined in ini
		if(!isset ($aConfig['rowClass']))
			$aConfig['rowClass'] = 'Kwgl_Db_Table_Row_Abstract';

	}

	/**
	 * method to set db config data from the ini.
	 * should be called in bootstrap
	 *
	 * @param array|object|string $oConfig
	 * @throws Exception
	 */
	public static function setDbConfig($oConfig) {

		//if object, convert into array
		if ($oConfig instanceof Zend_Config_Ini) {
			self::$oDbConfig = $oConfig->toArray();
		}
		elseif (is_array($oConfig)) {
			self::$oDbConfig = $oConfig;
		}
		elseif(is_string($oConfig)) {
			//try to load the file
			$suffix = strtolower(pathinfo($oConfig, PATHINFO_EXTENSION));
			//get object based on file extension
			switch ($suffix) {
				case 'ini':
					$config = new Zend_Config_Ini($oConfig);
					break;

				case 'xml':
					$config = new Zend_Config_Xml($oConfig);
					break;

				case 'json':
					$config = new Zend_Config_Json($oConfig);
					break;

				case 'yaml':
					$config = new Zend_Config_Yaml($oConfig);
					break;

				case 'php':
				case 'inc':
					$config = include $oConfig;
					if (!is_array($config)) {
						throw new Exception('Invalid configuration file provided; PHP file does not return array value');
					}
					return $config;
					break;

				default:
					throw new Exception('Invalid configuration file provided; unknown config type');
			}
			//save as an array
			self::$oDbConfig = $config->toArray();
		}
		else{
			throw new Exception('Invalid database configuration provided');
		}

		//set table names
		$oStd = new stdClass();
		foreach(self::$oDbConfig as $sNamespace=> $aTableData){
			$oStd->{$sNamespace} = $aTableData['name'];
		}
		self::$oDbNames = $oStd;

	}

	/**
	 * get table name of the provided namespace
	 * @param type $sNameSpace
	 * @return type
	 */
	public static function name() {

		return self::$oDbNames;

	}

	/**
	 *
	 * @param string $sTableNamespace
	 * @return Kwgl_Db_Table
	 */
	public static function factory($sTableNamespace) {
		//append dao to get the real classname
		$sClassName = 'Dao_' . $sTableNamespace;

		//check if the class exists (including auto loader)
		if(class_exists($sClassName, false) || self::_autoload($sClassName)){
			//create instance and return
			return new $sClassName($sTableNamespace);
		}
		else {
			//create default class with parameters
			return new self($sTableNamespace);
		}

	}

	/**
	 * auto load dao class if doesn't exists
	 *
	 * @param string $sClassName
	 * @return boolean
	 */
	private static function _autoload($sClassName) {

		//build the class path
		$sFile = ROOT_DIR . DIRECTORY_SEPARATOR . 'application/Model/' . str_replace('_', DIRECTORY_SEPARATOR, $sClassName) . '.php';
		//check if file exists
		if(file_exists($sFile)){
			@include_once $sFile;
			return true;
		}
		else{
			return false;
		}

	}

	/**
	 * function to get formatted select object
	 *
	 * @param array $aColumns an array of column names ex:- array('col1', 'col2')
	 * @param array $aConditions an array of SQL WHERE clauses. ex:- array('col = ?' => $val)
	 * @param mixed $aOrder The column(s) and direction to order by.
	 * @param array $aLimit an array with count and offeset. ex:- array( 10 - (The number of rows to return) , 0 - (Start returning after this many rows) )
	 * @return Zend_Db_Table_Select
	 */
	public function getFormatedSelect(array $aColumns = null, array $aConditions = null, array $aOrder = null, array $aLimit = null) {

		$oSelect = $this->select();

		// prepare the table(s) plus columns
        if(!is_array($aColumns)){
            $aColumns = array("*");
        }

		$oSelect->from($this, $aColumns);

		// prepare the conditions
        if(is_array($aConditions)){
			//loop through and set conditions
            foreach($aConditions AS $column => $value){
                $oSelect->where($column, $value);
            }
        }

        // prepare the order
        if(is_array($aOrder)){
			$oSelect->order($aOrder);
		}

        // prepare the limit
        if(is_array($aLimit)){
			$iCount = 0;
			$iOffset = 0;

			//check if count and offset isset
			if (isset($aLimit[0])) {
				$iCount = $aLimit[0];

				if (isset($aLimit[1])) {
					$iOffset = $aLimit[1];
				}
			}

			if ($iCount != 0)
				$oSelect->limit($iCount, $iOffset);
        }

		//save select object in last saved query
		self::$oLastCustomQuery = $oSelect;

		return $oSelect;

	}


	/**
	 * get table listing based on supplied conditions
	 *
	 * @param array $aColumns an array of column names			ex:- array('col1', 'col2')
	 * @param array $aConditions an array of SQL WHERE clauses. ex:- array('col = ?' => $val)
	 * @param mixed $aOrder The column(s) and direction to order by.
	 * @param array $aLimit an array with count and offeset.	ex:- array( 10 - (The number of rows to return) , 0 - (Start returning after this many rows) )
	 * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
	 */
	public function fetchList(array $aColumns = null, array $aConditions = null, array $aOrder = null, array $aLimit = null) {

		return $this->fetchAll( $this->getFormatedSelect($aColumns, $aConditions, $aOrder, $aLimit) );

	}

	/**
	 * get single row based on supplied conditions
	 *
	 * @param array $aColumns an array of column names ex:- array('col1', 'col2')
	 * @param array $aConditions an array of SQL WHERE clauses. ex:- array('col = ?' => $val)
	 * @param mixed $aOrder The column(s) and direction to order by.
	 * @return Zend_Db_Table_Row_Abstract current element from the collection
	 */
	public function fetchDetail(array $aColumns = null, array $aConditions = null, array $aOrder = null) {

		//return the first row
		return $this->fetchRow( $this->getFormatedSelect($aColumns, $aConditions, $aOrder) );

	}

	/**
	 * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
	 *
	 * @param array $aColumns an array of column names ex:- array('col1', 'col2')
	 * @param array $aConditions an array of SQL WHERE clauses. ex:- array('col = ?' => $val)
	 * @param mixed $aOrder The column(s) and direction to order by.
	 * @param array $aLimit an array with count and offeset. ex:- array( 10 - (The number of rows to return) , 0 - (Start returning after this many rows) )
	 * @return array
	 */
	public function fetchPairs(array $aColumns, array $aConditions = null, array $aOrder = null, array $aLimit = null) {

		//return result as pairs
		return $this->getAdapter()->fetchPairs( $this->getFormatedSelect($aColumns, $aConditions, $aOrder, $aLimit) );

	}

	/**
	 * get the last executed custom query's select object
	 *
	 * @return Zend_Db_Table_Select
	 */
	public static function getLastCustomQuery() {

		return self::$oLastCustomQuery;

	}

	/**
	 * Short cut to zend default adapter quoteInto method.
	 *
	 * @param string $sKey
	 * @param mixed $sValue
	 * @param string $sType
	 * @param integer $iCount
	 * @return string An SQL-safe quoted value placed into the original text.
	 */
	public static function quoteInto($sKey, $sValue, $sType = null, $iCount = null) {

		return self::getDefaultAdapter()->quoteInto($sKey, $sValue, $sType, $iCount);

	}

}