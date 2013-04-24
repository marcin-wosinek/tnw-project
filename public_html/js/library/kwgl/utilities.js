/*
 * Utility functions
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 *
 */
;(function($){

	Kwgl.util = {

		/**
		 * capitalise first charactor
		 *
		 * @param str string
		 * @return string
		 */
		ucfirst : function (str) {
			var firstLetter = str.substr(0, 1);
			return firstLetter.toUpperCase() + str.substr(1);
		},

		/**
		 * lowercase first charactor
		 *
		 * @param str string
		 * @return string
		 */
		lcfirst : function(str){
			var firstLetter = str.substr(0, 1);
			return firstLetter.toLowerCase() + str.substr(1);
		},

		/**
		 * Extract a Parameter Value from a URL
		 *
		 * @author Boudewijn Overvliet
		 * @author Jayawi Perera <jayawiperera@gmail.com>
		 */
		getParam : function(sUrl, sParameterName, oPassedSettings){

			var aParameter = [];

			if (sUrl == null) {
				sUrl = window.location.href;
			}

			sUrl = str_replace('http://', '', sUrl); // Remove Protocol
			sUrl = str_replace('#', '', sUrl); // Remove #

			var aSplitUrl = sUrl.split('/'); // Separate by value-key separate
			var sDomain = aSplitUrl[0];
			var iUrlPartCount = aSplitUrl.length;

			// Calculate so that iUrlPartCount excludes Domain and any trailing whitespace
			var sLastUrlPart = aSplitUrl[aSplitUrl.length - 1];
			if (sLastUrlPart == '') {
				iUrlPartCount = iUrlPartCount - 2;
			} else {
				iUrlPartCount = iUrlPartCount - 1;
			}

			var oDefaultSettings = {
				modules: ['default', 'kwgldev'],
				sDefaultModuleName: 'default',
				sDefaultControllerName: 'index',
				sDefaultActionName: 'index'
			};
			var oSettings = $.extend({}, oDefaultSettings, oPassedSettings);

			// Check the first URL part to see if it is a Module
			var bFirstUrlPartIsModule = false;
			var sFirstUrlPart = aSplitUrl[1];
			for(var mKey in oSettings.modules){
				if (sFirstUrlPart == oSettings.modules[mKey]) {
					bFirstUrlPartIsModule = true;
				}
			}

			var iControllerIndex = null;
			var iActionIndex = null;
			var iParameterExtractionStart = null;

			// Determine starting points for various key-value pairs
			if (bFirstUrlPartIsModule) {
				aParameter['module'] = aSplitUrl[1];
				iControllerIndex = 2;
				iActionIndex = 3;
				iParameterExtractionStart = 4;
			} else {
				aParameter['module'] = oSettings.sDefaultModuleName;
				iControllerIndex = 1;
				iActionIndex = 2;
				iParameterExtractionStart = 3;
			}

			// Allocate Controller
			if (iUrlPartCount >= iControllerIndex) {
				aParameter['controller'] = aSplitUrl[iControllerIndex];
			} else {
				aParameter['controller'] = oSettings.sDefaultControllerName;
			}

			// Allocate Action
			if (iUrlPartCount >= iActionIndex) {
				aParameter['action'] = aSplitUrl[iActionIndex];
			} else {
				aParameter['action'] = oSettings.sDefaultActionName;
			}

			// Iterate through Parameters and add them to the Parameter array
			for (var iCounter = iParameterExtractionStart; iCounter < iUrlPartCount; iCounter += 2) {
				var sName = aSplitUrl[iCounter];
				var sValue = aSplitUrl[iCounter + 1];
				switch (sName) {
					case 'module':
					case 'controller':
					case 'action':
						// Ensure that standard MVC parameters are not overwritten
						break;
					default:
						aParameter[sName] = sValue;
						break;
				}
			}

			// Return the value, whatever it may be
			return aParameter[sParameterName];

		}

	};

})(jQuery);