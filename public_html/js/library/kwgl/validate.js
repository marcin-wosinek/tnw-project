/**
 * @auther Udantha Diyanath Pathirana
 *
 * this extended object is used to validate inputs
 * appending attribute 'validations' with validation strings will validate it
 */

(function($){
	$.fn.validate = function (chk){
		var ok = true;
		chk = (chk)? chk : '';

		//class settings
		var settings = {
			validationAttr : 'validations', //validation attribute in the object
			valMsgAttr		: 'valmessage', //message attribute name in the object to show if an error
			inputErrorClass : 'input_error', //class to add to the object if an error
			inlineMsgClass : 'inline_error_message' //class for the error message
		};

		this.each(function () {
			var o = $(this);
			var er = false, validations = [], validateString = '';
			var value = $.trim(o.val());

			//check for inline validation attributes
			var valAttr = o.attr(settings.validationAttr);
			//if validation attribute exists append validations
			if(valAttr){
				validateString = $.trim(valAttr + ' ' + chk);
			}
			//attribute doesnt exist so do the default validation
			else{
				validateString = chk;
			}

			//split string to validation parts
			validations = validateString.split(' ');

			//loop through and validate each
			for(var i=0; i<validations.length; i++){
				var validation = $.trim(validations[i]);
				//check validation
				var result = checkValidations(validation, value);
				if(result){
					er = true;
				}
			}

			if(er){
				o.addClass(settings.inputErrorClass);
				ok = false;
				//add message if attr exists
				var errorMsg = o.attr(settings.valMsgAttr);
				if(errorMsg){
					if(o.siblings('span.' + settings.inlineMsgClass).length<1){
						$('<span class="' + settings.inlineMsgClass + '" style="display: none;">* '+ errorMsg +'</span>')
							.insertAfter(o)
							.slideDown('slow');
					}
				}
			}else{
				o.removeClass(settings.inputErrorClass);
				o.siblings('span.' + settings.inlineMsgClass).remove();
			}
		});

		/**
		 * check validations
		 */
		function checkValidations(func, val){
			var response = false;
			switch (func){
				//check for empty values
				case 'empty' :
					response = isEmpty(val);
					break;
				//check email
				case 'email' :
					response = !validEmail(val);
					break;
				//check phone
				case 'phone' :
					response = !validPhone(val);
					break;
				//check Date
				case 'date' :
					response = !validDate(val);
					break;
				//check Date
				case 'amount' :
					response = !validAmount(val);
					break;
				//check if number
				case 'number' :
					response = invalidNumber(val);
					break;
			}

			//local validation functions
			function isEmpty(val){
				return (val === '');
			}
			function validEmail(value){
				//dont check if value is empty
				if(value=='') return true;

				var email = /^[^@]+@[^@.]+\.[^@]*\w\w$/  ;
				return (email.test(value));
			}
			function validPhone(value){
				//dont check if value is empty
				if(value=='') return true;

				var phone = "0123456789";
				var ok = true;
				for(var i = 0; (i < value.length && ok == true); i++){
					var ch = value.charAt(i);
					if(phone.indexOf(ch) == -1) ok = false;
				}
				return ok;
			}
			function validDate(value){
				//dont check if value is empty
				if(value=='') return true;

				var DateRe = /^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/;

				if(value.search(DateRe) == -1)
					return false;
				else
					return true;
			}
			function validAmount(val){
				//dont check if value is empty
				if(val=='') return true;

				var cur = /^-?\d{1,3}(,\d{3})*(\.\d{1,2})?$/;
				var anum=/(^-?\d+$)|(^-?\d+\.\d+$)/;
				var ret = false;
				if(val.indexOf(",")>-1)
					ret = cur.test(val);
				else
					ret = anum.test(val);

				return ret;
			}
			function invalidNumber(val){
				//dont check if value is empty
				if(val=='') return false;

				return isNaN(val);
			}

			//return result
			return response;
		}

		//return whole result
		return ok;
	};
})(jQuery);