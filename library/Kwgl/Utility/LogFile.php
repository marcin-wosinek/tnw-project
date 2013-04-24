<?php

/**
 * Log File related functionality
 *
 * @author Jayawi Perera <jayawiperera@gmail.com>
 * @category PHP-Kwgl
 * @package Kwgl_Utility
 */
class Kwgl_Utility_LogFile {


	/**
	 * Returns the new Path based on the current date
	 * Note: Log Files are placed in directory hierarchy based on the Year, Month and Date
	 *
	 * @author Jayawi Perera <jayawiperera@gmail.com>
	 * @param string $sLogFileBasePath
	 * @return string
	 */
	public static function getLogFilePath ($sLogFileBasePath) {

		$sDateYear = date('Y');
		$sDateMonth = date('m');
		$sDateDay = date('d');
		$sBasePathLocation = dirname($sLogFileBasePath);
		$sBasePathFileName = basename($sLogFileBasePath);

		$sYearPath = $sBasePathLocation . '/' . $sDateYear . '/';

		$bIsDirectory = is_dir($sYearPath);
		if ($bIsDirectory === false) {
			mkdir($sYearPath);
		}

		$sMonthPath = $sYearPath . $sDateMonth . '/';

		$bIsDirectory = is_dir($sMonthPath);
		if ($bIsDirectory === false) {
			mkdir($sMonthPath);
		}

		$sDatePath = $sMonthPath . $sDateDay . '/';

		$bIsDirectory = is_dir($sDatePath);
		if ($bIsDirectory === false) {
			mkdir($sDatePath);
		}

		$sLogFileLocation = $sDatePath . $sBasePathFileName;

		$bIsFile = is_file($sLogFileLocation);
		if ($bIsFile === false) {
			$rFileHandle = fopen($sLogFileLocation, 'a+');
			fclose($rFileHandle);
		}

		return $sLogFileLocation;
    }

}