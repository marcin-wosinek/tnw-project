<?php
/**
 * Tries to find the partial index defined by the needle in the haystack
 *
 * @param array $aHaystack
 * @param string $sNeedle
 * @return boolean
 */
function doesPartialIndexExist ($aHaystack, $sNeedle) {
	// Fetch the Keys
	$aHaystackKeys = array_keys($aHaystack);
	foreach ($aHaystackKeys as $mKey) {
		$mResult = stripos($mKey, $sNeedle);
		if ($mResult !== false) {
			return true;
		}
	}

	return false;
}

/**
 * Returns the array constructed by finding the partial index defined by the needle in the haystack
 *
 * @param array $aHaystack
 * @param string $sNeedle
 * @return array
 */
function getPartialIndexArray ($aHaystack, $sNeedle) {
	$aResult = array();

	foreach ($aHaystack as $mKey => $sValue) {
		$mResult = stripos($mKey, $sNeedle);

		if ($mResult !== false) {
			$sNewKey = str_replace($sNeedle, '', $mKey);
			$aResult[$sNewKey] = $sValue;
		}
	}

	return $aResult;
}

$sContactAdministrator = "We could not contact the administrator of this site. If the issue is recurring, you may contact the administrator by mailing admin@server.net";
$sFromAddress = "no-reply@server.net";

$bManualAdministratorContact = false;

// Send a mail to the Mailing List
$sConfigurationFileName = 'application.ini';
$sConfigurationFilePath = '../application/configs/' . $sConfigurationFileName;
$aConfigurationAll = parse_ini_file($sConfigurationFilePath, true);

$sBaseSection = 'general';
$sApplicationEnvironment = getenv('APPLICATION_ENV');

if ($sApplicationEnvironment == '') {
	$sSection = $sBaseSection;
} else {
	$sSection = $sApplicationEnvironment . ' : ' . $sBaseSection;
}

$aMailingList = array();
if (isset($aConfigurationAll[$sSection])) {
	$aRelevantConfiguration = $aConfigurationAll[$sSection];
	if (doesPartialIndexExist($aRelevantConfiguration, 'mode.debug.mail.list')) {
		$aMailingList = getPartialIndexArray($aRelevantConfiguration, 'mode.debug.mail.list.');
	} else {
		if ($sSection != $sBaseSection) {
			$aRelevantConfiguration = $aConfigurationAll[$sBaseSection];
			if (doesPartialIndexExist($aRelevantConfiguration, 'mode.debug.mail.list')) {
				$aMailingList = getPartialIndexArray($aRelevantConfiguration, 'mode.debug.mail.list.');
			}
		}
	}
} else {
	if ($sSection != $sBaseSection) {
		$aRelevantConfiguration = $aConfigurationAll[$sBaseSection];
		if (doesPartialIndexExist($aRelevantConfiguration, 'mode.debug.mail.list')) {
			$aMailingList = getPartialIndexArray($aRelevantConfiguration, 'mode.debug.mail.list.');
		}
	}
}

if (empty($aMailingList)) {
	$bManualAdministratorContact = true;
} else {
	$sSubject = "Database is down";
	$sMessage = "Database is down at " . $_SERVER['HTTP_HOST'];
	$sHeaders = "From: " . $sFromAddress;
	foreach ($aMailingList as $sName => $sAddress) {
		$bStatus = @mail($sAddress, $sSubject, $sMessage, $sHeaders);
		$bStatus = true;
		if (!$bStatus) {
			$bManualAdministratorContact = true;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
	<meta name="robots" content="noindex,nofollow,noarchive" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Site is experiencing Technical Difficulties</title>
	<link href="/css/site.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
	<h1>Sorry</h1>
	<div>
		<p>The site is experiencing some technical difficulties.</p>
		<p>Please check back later. Our apologies for any inconvenience caused.</p>
		<p>Click <a href='/' title='Home'>here</a> to go back to the Home page.</p>
		<?php if ($bManualAdministratorContact) { ?><br><br><p><?php echo $sContactAdministrator; ?></p><?php } ?>
	</div>
</body>
</html>