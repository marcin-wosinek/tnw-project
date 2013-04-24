<?php

class Kwgldev_View_Helper_BackLink {

    public function backLink ($sUrl, $sText = 'Back') {
		$sContent = '<a class="cButton" href="' . $sUrl . '"><i class="cIconArrowLeft" title="Back"></i> ' . $sText . '</a>';
		return $sContent;
    }

}