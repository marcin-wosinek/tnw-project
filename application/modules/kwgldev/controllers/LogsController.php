<?php
/**
 *
 */
class Kwgldev_LogsController extends Kwgl_Controller_Action {

	/**
	 *
	 */
	public function init () {
		parent::init();

		$this->view->headLink()->appendStylesheet('/min/?g=cssBase');
		$this->view->headLink()->appendStylesheet('/css/library/bootstrap/bootstrap.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/style.css');
		$this->view->headLink()->appendStylesheet('/css/kwgldev/log.css');

		$this->view->headScript()->appendFile('/min/?g=jsCore');
		$this->view->headScript()->appendFile('/js/library/bootstrap.js');
	}

	/**
	 *
	 */
	public function indexAction () {

		$aYearLevelIgnoreList = array(
			'.',
			'..',
			'.htaccess',
			'style',
		);

		$aMonthLevelIgnoreList = array(
			'.',
			'..',
		);

		$aDayLevelIgnoreList = array(
			'.',
			'..',
		);

		$aLogLevelIgnoreList = array(
			'.',
			'..',
		);

		$aLogFileList = array();

		// See if the logs folder exists
		$sLogsDirectoryPath = Kwgl_Config::get(array('paths', 'public', 'directory')) . '/logs/';

		$rDirectory = opendir($sLogsDirectoryPath);

		while (false !== ($sYearLevelEntry = readdir($rDirectory))) {

			if (in_array($sYearLevelEntry, $aYearLevelIgnoreList)) {
				// Ignore
			} else {
				$sYearDirectoryPath =  $sLogsDirectoryPath . $sYearLevelEntry . '/';
				$bIsYearDirectory = is_dir($sYearDirectoryPath);

				if ($bIsYearDirectory) {

					$rYearDirectory = opendir($sYearDirectoryPath);

					while (false !== ($sMonthLevelEntry = readdir($rYearDirectory))) {

						if (in_array($sMonthLevelEntry, $aMonthLevelIgnoreList)) {
							// Ignore
						} else {
							$sMonthDirectoryPath = $sYearDirectoryPath . $sMonthLevelEntry . '/';
							$bIsMonthDirectory = is_dir($sMonthDirectoryPath);

							if ($bIsMonthDirectory) {

								$rMonthDirectory = opendir($sMonthDirectoryPath);

								while (false !== ($sDayLevelEntry = readdir($rMonthDirectory))) {

									if (in_array($sDayLevelEntry, $aDayLevelIgnoreList)) {
										// Ignore
									} else {
										$sDayDirectoryPath = $sMonthDirectoryPath . $sDayLevelEntry . '/';
										$bIsDayDirectory = is_dir($sDayDirectoryPath);

										if ($bIsDayDirectory) {

											$rDayDirectory = opendir($sDayDirectoryPath);

											while (false !== ($sLogEntry = readdir($rDayDirectory))) {

												if (in_array($sLogEntry, $aLogLevelIgnoreList)) {
													// Ignore
												} else {
														$aLogFileList[$sYearLevelEntry][$sMonthLevelEntry][$sDayLevelEntry][] = $sLogEntry;



												} // if (in_array($sLogEntry, $aLogLevelIgnoreList))

											} // while (false !== ($sLogEntry = readdir($rDayDirectory)))

										} // if ($bIsDayDirectory)



									} // if (in_array($sDayLevelEntry, $aDayLevelIgnoreList))

								} // while (false !== ($sMonthLevelEntry = readdir($rYearDirectory)))

							} // if ($bIsMonthDirectory)

						} // if (in_array($sMonthLevelEntry, $aMonthLevelIgnoreList))

					} // while (false !== ($sMonthLevelEntry = readdir($rYearDirectory)))

				} // if ($bIsYearDirectory)

			} // if (in_array($sYearLevelEntry, $aYearLevelIgnoreList))

		} // while (false !== ($sYearLevelEntry = readdir($rDirectory)))

//		Zend_Debug::dump($aLogFileList);

		if (count($aLogFileList) == 0) {
			Model_Kwgldev_Response::addResponse(
					'No Log Files have been found.',
					Model_Kwgldev_Response::STATUS_INFORMATION);
		}

		$aContent['log-file-list'] =  $aLogFileList;
		$this->view->aContent = $aContent;
	}

}