/* 
 * Class to handle translations on front end
 * 
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * 
 */
;
(function($){
	
	/**
	 * attach translation properties to kwgl
	 */
	Kwgl.translator = {
	
		/*
		 * variable to hold translations
		 */
		aTranslations: {},
		
		/**
		 * set translations array. Should be called before get method gets called
		 * 
		 * @param aTranslations translations as key value pairs - { label: 'translated word in chosen language', .... }
		 */
		set: function(aTranslations){
			
			this.aTranslations = aTranslations;
			
		},
		
		/**
		 * get translated value of a string
		 * 
		 * @param sLabel word to get the translation
		 * @return string translated value
		 */
		get: function(sLabel){
        
			var self = this,
				sValue = '';
            
			sLabel = $.trim(sLabel);
        
			//check if label exisits
			if(self.aTranslations[sLabel] != undefined){
				sValue = self.aTranslations[sLabel];
			}
			//else breakdown to words and search
			else{
				var aLabels = sLabel.split(' ');
				//get individual translation
				for(var key in aLabels){
                
					var sThisLabel = aLabels[key],
					aSeperators = [','],
					sUnsupportedLabel = '';
					//check if there are separator characters that disturb the translation
					for(var i=0; i<aSeperators.length; i++){
						//remove if seperator exists
						if(sThisLabel.indexOf(aSeperators[i]) !== -1){
							sUnsupportedLabel = sThisLabel.replace(aSeperators[i], '', 'gi');
						}else{
							sUnsupportedLabel = sThisLabel;
						}
					}
                
					sUnsupportedLabel = sUnsupportedLabel.toLowerCase();
					//check again if label exists
					sValue += (self.aTranslations[sUnsupportedLabel] != undefined)? self.aTranslations[sUnsupportedLabel] : sThisLabel;
                
					//append removed seperators
					for(var ii=0; ii<aSeperators.length; ii++){
						//append if seperator exists
						if(sThisLabel.indexOf(aSeperators[ii]) !== -1){
							sValue += aSeperators[ii];
						}
					}
                
					sValue += ' ';
				}
			}
        
			return sValue;
        
		}

	};
	
})(jQuery);
