<?php

/**
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 */
class Kwgl_Validate_VerifyCurrentPassword extends Zend_Validate_Db_RecordExists {

	/**
	 *
	 * @param mixed $mValue
	 * @return boolean
	 */
	public function isValid ($mValue) {

		$mValue = trim($mValue);
		$mValue = sha1($mValue, true);

        return parent::isValid($mValue);

	}

}