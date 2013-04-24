<?php
/**
 * Benchmarking functionality using PEAR Benchmark Timer
 *
 * @author Jayawi Perera jayawiperera@gmail.com
 * @category PHP-Kwgl
 * @package Kwgl_Benchmark
 */
class Kwgl_Benchmark {

	/**
	 *
	 * @var boolean
	 */
	public static $bOn = false;

	/**
	 *
	 * @var Benchmark_Timer
	 * @link http://pear.php.net/package/Benchmark/docs/latest/Benchmark/Benchmark_Timer.html
	 */
	public static $oTimer;

	/**
	 *
	 * @param boolean $bAutoStart
	 * @param boolean $bCheckConfig
	 */
	public function __construct($bAutoStart = true, $bCheckConfig = true) {
		self::initialise($bAutoStart, $bCheckConfig);
	}

	/**
	 *
	 * @param boolean $bAutoStart
	 * @param boolean $bCheckConfig
	 */
	public static function initialise($bAutoStart = true, $bCheckConfig = true) {

		if (self::$bOn) {
			return;
		}

		$bStartBenchmark = false;

		if ($bCheckConfig) {
			$iBenchmark = Kwgl_Config::get(array('mode', 'benchmark', 'active'));
			if ($iBenchmark == 1) {
				$bStartBenchmark = true;
			}
		} else {
			$bStartBenchmark = true;
		}

		if ($bStartBenchmark) {
			require_once 'Benchmark/Timer.php';
			self::$oTimer = new Benchmark_Timer($bAutoStart);
			self::$bOn = true;
		} else {
			self::$oTimer = null;
			self::$bOn = false;
		}
	}

	/**
	 * Close method. Stop timer and display output.
	 *
	 * @return void
	 */
	public static function close () {
		if (self::$bOn) {
			if (!self::isXhr()) {
				self::$oTimer->close();
			}
		}
	}

	/**
	 * Set "Start" marker.
	 * Starts the Timer manually (provided it is initialised).
	 *
	 * @return void
	 */
	public static function start () {
		if (self::$bOn) {
			self::$oTimer->start();
		}
	}

	/**
	 * Set "Stop" marker.
	 * Stops the Timer manually (provided it is initialised).
	 *
	 * @return void
	 */
	public static function stop () {
		if (self::$bOn) {
			self::$oTimer->stop();
		}
	}

	/**
	 * Set a Marker in the Timer
	 *
	 * @param string $sName
	 * @return void
	 */
	public static function setMarker ($sName) {
		if (self::$bOn) {
			self::$oTimer->setMarker($sName);
		}
	}

	/**
	 * Returns the time elapsed betweens two markers.
	 *
	 * @param string $sStart
	 * @param string $sEnd
	 * @return double
	 */
	public static function timeElapsed ($sStart = 'Start', $sEnd = 'Stop') {
		if (self::$bOn) {
			return self::$oTimer->timeElapsed($sStart, $sEnd);
		}
		return null;
	}

	/**
	 * Prints the information returned by getOutput().
	 *
	 * @param boolean $bShowTotal
	 * @param string $sFormat Can be 'auto', 'plain' or 'html'
	 */
	public static function display ($bShowTotal = false, $sFormat = 'auto') {
		if (self::$bOn) {
			if (!self::isXhr()) {
				self::$oTimer->display($bShowTotal, $sFormat);
			}
		}
	}

	/**
	 *
	 * @param boolean $bShowTotal
	 */
	public static function displayStyled ($bShowTotal = false) {
		// If Timer has not been initialised, exit
		if (!self::$bOn) {
			return;
		}

		// If Request is an AJAX Request, exit
		if (self::isXhr()) {
			return;
		}

		$sFinalDisplay = '';
		$sDisplay = '';
		$sBreak = "\n";
		$sTab = "\t";
		$aProfiling = self::getProfiling();

		if (!empty($aProfiling)) {
			$iEntries = count($aProfiling);
			$sDisplay .= $sBreak;
			$sHeader = '<table width="950" style="margin: 0 auto; padding: 0; border-collapse: separate; border-spacing: 2px; font-family: Courier;">' . $sBreak
					. $sTab . '<tr style="background-color: #DDDDDD;">' . $sBreak
					. $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">#</th>' . $sBreak
					. $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">Marker</th>' . $sBreak
					. $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">Time</th>' . $sBreak
					. $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">Execution (s)</th>' . $sBreak
					. $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">%</th>' . $sBreak;
			if ($bShowTotal) {
				$sHeader .= $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">Elapsed (s)</th>' . $sBreak
						. $sTab . '<th width="" style="padding: 0.25%; font-weight: bold;">%</th>' . $sBreak;
			}
			$sHeader .= '</tr>' . $sBreak;

			$sBody = '';
			$iCounter = 1;
			$fTotal = self::timeElapsed();
			foreach ($aProfiling as $aEntry) {
				$fExecutionPercentage = (($aEntry['diff'] * 100) / $fTotal);
				$sFormattedExecutionPercentage = number_format($fExecutionPercentage, 2, '.', '') . "%";

				if ($fExecutionPercentage >= 75) {
					$sBackgroundColour = "background-color: #FF6666;";
				} elseif ($fExecutionPercentage >= 50) {
					$sBackgroundColour = "background-color: #FFCC99;";
				} elseif ($fExecutionPercentage >= 25) {
					$sBackgroundColour = "background-color: #FFFFCC;";
				} else {
					$sBackgroundColour = "background-color: #CCFFCC;";
				}

				$sBodyRow = '<tr style="' . $sBackgroundColour . '">' . $sBreak
						. $sTab . '<td width="" style="padding: 0.25%;">' . $iCounter . '</td>' . $sBreak
						. $sTab . '<td width="" style="padding: 0.25%;">' . $aEntry['name'] . '</td>' . $sBreak
						. $sTab . '<td width="" style="padding: 0.25%; text-align: center;">' . $aEntry['time'] . '</td>' . $sBreak
						. $sTab . '<td width="" style="padding: 0.25% 2.5%; text-align: right;">' . $aEntry['diff'] . '</td>' . $sBreak
						. $sTab . '<td width="" style="padding: 0.25% 2.5%; text-align: right;">' . $sFormattedExecutionPercentage . '</td>' . $sBreak;

				if ($bShowTotal) {
					$fElapsedPercentage = (($aEntry['total'] * 100) / $fTotal);
					$sFormattedElapsedPercentage = number_format($fElapsedPercentage, 2, '.', '') . "%";
					$sBodyRow .= $sTab . '<td width="" style="padding: 0.25% 2.5%; text-align: right;">' . $aEntry['total'] . '</td>' . $sBreak
							. $sTab . '<td width="" style="padding: 0.25% 2.5%; text-align: right;">' . $sFormattedElapsedPercentage . '</td>' . $sBreak;
				}

				$sBodyRow .= '</tr>' . $sBreak;
				$sBody .= $sBodyRow;
				$iCounter++;
			}

			$sFooter = '<tr style="background-color: #CCCCCC;">' . $sBreak
					. $sTab . '<td width="" style="padding: 0.25%; font-weight: bold; text-align: right;">' . $iEntries . '</td>' . $sBreak
					. $sTab . '<td width="" style="padding: 0.25%; font-weight: bold;">Total</td>' . $sBreak
					. $sTab . '<td width="" style="padding: 0.25%; font-weight: bold;">&nbsp;</td>' . $sBreak
					. $sTab . '<td width="" style="padding: 0.25% 2.5%; font-weight: bold; text-align: right;">' . $fTotal . '</td>' . $sBreak
					. $sTab . '<td width="" style="padding: 0.25% 2.5%; font-weight: bold; text-align: right;">100.00%</td>' . $sBreak;
			if ($bShowTotal) {
				$sFooter .= $sTab . '<td width="" style="padding: 0.25%; font-weight: bold; text-align: center;">-</td>' . $sBreak
					. $sTab . '<td width="" style="padding: 0.25%; font-weight: bold; text-align: center;">-</td>' . $sBreak;
			}

			$sFooter .= '</tr>' . $sBreak . '</table>';

			$sDisplay .= $sHeader . $sBody . $sFooter;
			$sFinalDisplay = '<div id="iDivBenchmarkContainer" class="cDivBenchmarkContainer" style="border: 1px solid #000000; margin: 20px auto; width: 960px; padding: 5px 0;">' . $sDisplay . '</div>';
		}
		echo $sFinalDisplay;
	}

	/**
	 * Return formatted profiling information.
	 *
	 * @param boolean $bShowTotal
	 * @param string $sFormat Can be 'auto', 'plain' or 'html'
	 * @return string
	 */
	public static function getOutput ($bShowTotal = false, $sFormat = 'auto') {
		if (self::$bOn) {
			return self::$oTimer->getOutput($bShowTotal, $sFormat);
		}
		return null;
	}

	/**
	 * Returns profiling information.
	 * @return array
	 */
	public static function getProfiling () {
		if (self::$bOn) {
			return self::$oTimer->getProfiling();
		}
		return null;
	}

	/**
	 * Checks if ths current Request is an AJAX Request
	 * @return boolean
	 */
	public static function isXhr () {
		$oRequest = Zend_Controller_Front::getInstance()->getRequest();
		if ($oRequest->isXmlHttpRequest()) {
			return true;
		}
		return false;
	}

}