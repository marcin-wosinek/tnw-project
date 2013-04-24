/**
 * Class to handle ajax
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 * @package Js.Kwgl
 */

;(function($){

	/**
	 * append ajax related methods to kwgl
	 */
	Kwgl.xhr = {

		/**
		 * function to show ajax related error messages
		 */
		_showError: function(error){
			//show error message
			if(Kwgl.msg){
				Kwgl.msg.error(error);
			}else{
				alert(error);
			}
		},

		/**
		 * function to submit data to the server
		 *
		 * @param uri string - URL to send data to
		 * @param dataArray mixed - Data to be sent to the server. It is converted to a query string, if not already a string. Object must be Key/Value pairs.
		 * @param successCallback function - callback function to execute on a succesfull result
		 * @param settings Object - Json object with key value pairs to override
		 * @return jQuery XMLHttpRequest handler object
		 */
		send : function (uri, dataArray, successCallback, settings){
			/**
			 * ============ variable declerations ============
			 */
			//private variables
			var self = this,
				loadingC = null,
				loaderTimeout,
				//loader types
				loaderTypes = {
					'default': '<div class="ajax_loader_screen"></div>',
					'small': '<div class="min_ajax_loader_screen"><div class="loading_msg">Loading.....</div></div>'
				},
				//overwridable settings
				ajaxSettings = {
					type			: 'POST',
					dataType		: 'json',
					cache           : false,
					async           : true,
					complete		: function(){} ,//on request complete
					error           : false,
					loadingScreen	: false, //true if loading screen is on
					startLoaderAfter: 20, //miliseconds to start the loader
					getLoader		: function(){
						return $(loaderTypes['default']);
					}, //function to get the loading screen jquery object
					context			: null //ajax loading context
				};

			//overwride settings
			var newSettings = $.extend({}, ajaxSettings, settings);

			/**
			 * ============ declaire local functions ============
			 */
			//start loading screen
			var startLoader = function(){
				var css = {
					top: 0,
					left: 0,
					width: '100%',
					height: '100%',
					position: 'fixed',
					zIndex: 900
				}; //default position of the loader
				//create loader
				if($.isFunction(newSettings.getLoader)){
					//call the function and get the loader
					loadingC = newSettings.getLoader();
				}
				//it's atring. get the type from the array'
				else{
					//if type exists create object form it
					if(newSettings.getLoader in loaderTypes){
						loadingC = $(loaderTypes[newSettings.getLoader]);
					}
					//else create the default object
					else{
						loadingC = $(loaderTypes['default']);
					}
				}

				//make sure the loader is jquery object
				loadingC = loadingC.jquery ? loadingC : $(loadingC);

				//if context is given position loader on top of it
				if(newSettings.context != null){
					newSettings.context = newSettings.context.jquery ? newSettings.context : $(newSettings.context); //make sure it is jquery

					var cPos = newSettings.context.offset();
					var cWidth = newSettings.context.outerWidth(true);
					var cHeight = newSettings.context.outerHeight(true);
					//set positions on top of the context
					css = {
						top: cPos.top,
						left: cPos.left,
						width: cWidth,
						height: cHeight,
						position: 'absolute'
					};
				}

				//set styles to position
				loadingC.css(css);
				$('body').append(loadingC);
			};
			//stop loading screen
			var stopLoader = function(){
				if(loadingC && loadingC.jquery){
					loadingC.remove();
				}
			};

			/**
			 * ============ functionalities ============
			 */
			//if loader is on
			if(newSettings.loadingScreen===true){
				//start loading screen after a timeout
				loaderTimeout = setTimeout(startLoader, newSettings.startLoaderAfter);
			}

			//send ajax request to the backend
			var oConf = $.extend({}, newSettings, {
				url: uri,
				data: dataArray,
				success : function (serverData, textStatus, jqXHR){
					try{
						//if the response has errors from backend
						if(serverData.error){
							//call error
							if(newSettings.error){
								newSettings.error(serverData.error);
							}else{
								self._showError(serverData.error);
							}
						}
						//else if the response is succesfull call the success callback
						else if(serverData.response){
							successCallback(serverData.response, textStatus, jqXHR);
						}
						//pass error if data type is json and not properly formatted
						else if(newSettings.dataType == 'json'){
							self._showError('Data from backend not formatted properly.')
						}
						//else pass the unformated response to callback
						else{
							successCallback(serverData, textStatus, jqXHR);
						}

					}catch(e){
						self._showError('Loading Error : '+ e);
					}
				},
				error : function (XMLHttpRequest, textStatus, errorThrown) {
					try{
						//if callback is supplied call it
						if(newSettings.error){
							newSettings.error(errorThrown, XMLHttpRequest, textStatus);
						}
						//else set the default error popup
						else{
							self._showError('Error : '+ errorThrown)
						}

					}catch(e){
						self._showError('Loading Error : '+ e);
					}
				},
				complete : function(){
					//remove loader
					if(newSettings.loadingScreen){
						//stop timeout
						clearTimeout(loaderTimeout);
						//hide loader
						stopLoader();
					}
					//call complete function
					$.isFunction(newSettings.complete) && newSettings.complete();
				}
			});

			//set configurations and execute ajax
			return $.ajax(oConf);
		},

		/**
		 * save form to a db
		 */
		submitForm : function(opts){
			var self = this;
			//main settings
			var settings = {
				url             : '',
				validation		: '', //custom validation string for all objects
				form			: '',
				loadingScreen	: true, //ajax loading screen
				context			: null, //ajax loading context
				customPara		: function(){
					return ''
					}, //custom parameters to send to database
				customIsValid	: function(){
					return true;
				}, //custom validation function
				success         : function(){}, //handle ajax response
				error           : false,
				complete		: function(){} //on request complete
			};

			//extend custom settings with existing
			settings = $.extend({}, settings, opts);

			var form = $(settings.form), errors = [], inputs = self._sanitizeInputs(form.find(':input'));
			//object to pass error messages
			var Errors = function(){};
			Errors.prototype.setError = function(error){
				errors.push(error);
			}

			//execute custom validator function
			settings.customIsValid.call(new Errors(), inputs);

			//run validations for inputs using validator plugin
			if($.prototype.hasOwnProperty('validate') && !inputs.validate(settings.validation)){
				errors.push('There are errors in the form. Please check hilighted fields.');
			}

			//handle form errors
			if(errors.length>0){
				if($.isFunction(settings.error)){
					settings.error(errors);
				}else{
					self._showError(errors);
				}
				return false;
			}

			//attach custom parameters
			var cPara = '&' + settings.customPara(inputs);

			//submit form
			self._submitFormToServer(settings.url, form, cPara, settings.success, settings, inputs);
		},

		/**
		 * ============= helper functions =================
		 */
		_sanitizeInputs : function(inputs){
			var objs = $([]);

			for(var i=0, l=inputs.length; i<l; i++){
				var input = inputs[i],
				$input = $(input),
				tagname = input.tagName.toLowerCase(),
				type = $input.attr('type');

				type = type ? type.toLowerCase() : '';
				//if the object is a check box or radio button
				if(tagname=='input' && (type=='checkbox' || type=='radio')){
					//append only if checked
					if($input.is(':checked')){
						objs = objs.add($input);
					}
				}else{
					objs = objs.add($input);
				}
			}

			return objs;
		},

		/**
		 * method to build query string from intputs and send to the server
		 */
		_submitFormToServer : function (uri, form, data, successCallback, settings, inputs){
			var para = data,
			self = this;

			inputs = inputs ? inputs : $(form).find(':input');

			inputs.each(function() {
				var el = $(this),
				val = encodeURIComponent($.trim(el.val()));

				if(el.attr('type') == 'checkbox' && el.attr('checked'))
					para += '&' + el.attr('name') + '=' + val;

				else if(el.attr('type') != 'checkbox')
					para += '&' + el.attr('name') + '=' + val;
			});
			//send data to the server
			self.send(uri, para, successCallback, settings);
		}
	};

})(jQuery);