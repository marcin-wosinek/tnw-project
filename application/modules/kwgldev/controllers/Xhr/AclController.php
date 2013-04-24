<?php

class Kwgldev_Xhr_AclController extends Kwgl_Controller_Action {

	public function childrenAction () {

		if (in_array('type', $this->_aParameterKey) && in_array('parent', $this->_aParameterKey)) {
			$sType = $this->_aParameter['type'];
			$sParent = $this->_aParameter['parent'];

			$aChildren = array();
			$aStructure = Kwgl_Utility_Mvc::getStructure();

			switch ($sType) {
				case 'module':
					$sModuleName = $sParent;
					if (isset($aStructure[$sModuleName])) {
						$aUnfilteredChildren = $aStructure[$sModuleName];

						foreach ($aUnfilteredChildren as $sControllerName => $aControllerDetails) {
							$aChildren[strtolower($sControllerName)] = ucfirst($sControllerName);
						}

					}
					break;
				case 'controller':
					$aParent = explode('-', $sParent);
					$sModuleName = $aParent[0];
					$sControllerName = $aParent[1];

					if (isset($aStructure[$sModuleName][$sControllerName])) {
						$aUnfilteredChildren = $aStructure[$sModuleName][$sControllerName];

						foreach ($aUnfilteredChildren as $sActionName => $aActionDetails) {
							$aChildren[strtolower($sActionName)] = ucfirst($sActionName);
						}

					}

					break;
			}

			if (empty($aChildren)) {
				$this->setAjaxError('Inadequate Data Provided.');
			} else {
				$aContent = $aChildren;
				$this->setAjaxResponse($aContent);
			}

		} else {
			$this->setAjaxError('Inadequate Data Provided.');
		}
	}
}