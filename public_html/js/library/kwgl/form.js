/*
 * @author Udantha Pathirana <udanthaya@gmail.com>
 *
 * plugin to handle ajax form submission
 */

;
(function($){

	/**
	 * Form submit plugin
	 */
	$.fn.ajaxSubmit = function(options) {

		return this.each(function() {
			var that = $(this);
			/**
			 * if submit is already attached to the object
			 * Perform reload, etc. defined tasks
			 */
			var storedPgObject = $.data(that[0], 'AJAX_FORM_DATA');

			if(storedPgObject){
				if(typeof options == 'string'){
					//reload table
					if(options==='destroy'){
						//operations for distroy.
						storedPgObject.destroy();
						$.removeData(that[0], 'AJAX_FORM_DATA');
					}
				}
			}
			/**
			 * create form submit object and attach to the DOM object
			 */
			else{
				var oFrm = new KwglForm();
				oFrm.init(options, that);
				//save object
				$.data(that[0], 'AJAX_FORM_DATA', oFrm);

			}

		});

	};

	var KwglForm = function(){

		var settings = {
			url             : null, //backend action url, if null sends to action defined in form
			validation		: '', //custom validation string for all objects
			form			: '',
			error           : false, //error function
			loadingScreen	: true, //ajax loading screen
			context			: null, //ajax loading context
			customPara		: function(){
				return ''
			}, //custom parameters to send to database
			customIsValid	: function(){
				return true;
			}, //custom validation function
			success         : function(){}, //handle ajax response
			complete		: function(){}, //on request complete
			listErrorClass	: 'errors',
			elementErrorClass: 'cErrorInput',
			disableInlineMessages: false //if true, disables messages shown beside input elements
		}, oForm;

		this.init = function(opt, oSource){

			settings = $.extend({}, settings, opt);
			oForm = oSource;

			//set form object
			settings.form = oForm;
			//overwrite response handler
			settings.success = function(response){

				var oInputs = oForm.find(':input'),
				/**
				 * function to append error messages to elements
				 */
				appendErrors = function(oElement, aErrors){

					var sErrors = '';
					if(oElement.length>0){
						sErrors += '<ul class="'+ settings.listErrorClass +'">';
						//go through and append errors
						for(var sErName in aErrors){
							sErrors += '<li>'+ aErrors[sErName] +'</li>';
						}
						sErrors += '</ul>';
						//append error after element
						oElement.after(sErrors);
						//add inline error class
						oElement.addClass(settings.elementErrorClass);
					}

				};

				//remove all the error messages
				oInputs.siblings('ul.'+ settings.listErrorClass).remove();
				oInputs.removeClass(settings.elementErrorClass);

				//check if response is valid
				if(response.__FormResponse===true){
					//call user defined response handler
					if($.isFunction(opt.success)){
						opt.success.call(settings.form, response);
					}
				}
				//else call error function
				else{
					if($.isFunction(opt.error)){
						opt.error.call(settings.form, response);
					}
				}

				//set input values from backend and errors if exists
				oInputs.each(function(){

					var oThis = $(this),
					sName = oThis.attr('name');
					//check if error exists, if so show it and append inline errors if settings permitted
					if(response.__FormResponse.hasOwnProperty( sName ) && (settings.disableInlineMessages!==true)){
						//append errors
						appendErrors(oThis, response.__FormResponse[sName]);
					}
					//append values from backend
					if(response.__FormResponseValues.hasOwnProperty( sName )){
						oThis.val(response.__FormResponseValues[sName]);
					}

				});

			};
			
			//set url
			if(settings.url===null){
				var url = oForm.attr('action');
				if (url) {
					// clean url (don't include hash vaue)
					url = (url.match(/^([^#]+)/)||[])[1];
				}
				settings.url = url || window.location.href || '';
			}

			//attach event handlers to form
			oForm.bind('submit.ajaxFormSubmit', function(event){
				event.preventDefault();

				//save form
				Kwgl.xhr.submitForm(settings);

			});

		};
		
		/**
		 * destroys the form submit objects and clears properties
		 */
		this.destroy = function() {
			
			oForm.unbind('.ajaxFormSubmit');
			
		}

	};

	/**
     * clear element values.
     */
	$.fn.clearForm = function() {
		return this.each(function() {
			$('input,select,textarea', this).each(function(){
				var t = this.type, tag = this.tagName.toLowerCase();
				if (t == 'text' || t == 'password' || tag == 'textarea')
					this.value = '';
				else if (t == 'checkbox' || t == 'radio')
					this.checked = false;
				else if (tag == 'select')
					this.selectedIndex = -1;
			});
		});
	};

})(jQuery);
