;
/**
 * Common messages class
 *
 * @author Udantha Pathirana <udanthaya@gmail.com>
 *
 */
(function($){

	/**
	 * Append message operations to Kwgl
	 * @param msg messages class
	 */
	Kwgl.msg = {

		/**
		 * templates to be used for messages
		 * id		: Id value of the object
		 * listClass: class to be appended for the list
		 * template : add the styles you want to add around %BODY% (add this to append body text) of the message.
		 */
		_templates: {
			error: {
				id: 'iKwglErMsgContainer',
				listClass: 'cErMsgList',
				template: '%BODY%'
			},
			success: {
				id: 'iOkMsgContainer',
				listClass: 'cOkMsgList',
				template: '%BODY%'
			}
		},

		/**
		 * format input and get messages
		 */
		_getMessageList: function(sMessages){

			//normalize
			var aMessages = [],
				msgs = (sMessages==undefined || sMessages==null) ? '' : sMessages;

			//build messages
			if(typeof msgs == 'string' || typeof msgs == 'number'){
				aMessages = [msgs];
			}else{
				aMessages = msgs;
			}

			//build message list
			var html = '';
			for(var i in aMessages){
				html += '<li>'+ aMessages[i] +'</li>';
			}

			return html;

		},

		/**
		 * create message
		 */
		_createMessage: function(sTemplateKey, sMessages, sTitle, aConf){

			var self = this;
			//validate
			if(self._templates[sTemplateKey]==undefined){
				throw ('Kwgl: Message Template not defined.');
			}

			//get matching template and relavant objects
			var	oTemplate = self._templates[sTemplateKey],
				oBox = $('#'+ oTemplate.id),
				html = self._getMessageList(sMessages),
				oDefConf = {
					buttons: {
						'OK': function(){
							$(this).dialog('close');
						}
					},
					autoOpen: false,
					closeOnEscape: false,
					draggable: false,
					resizable: false,
					minHeight: 0,
					modal: true,
					width: 400,
					title: sTitle
				};

			//create if not initialized
			if(oBox.length==0){
				//replace body
				var template = oTemplate.template.replace('%BODY%', '<ul class="'+ oTemplate.listClass +'"></ul>');
				//create ui dialog
				oBox = $('<div id="'+ oTemplate.id +'">'+ template +'</div>');
				oBox.appendTo('body').dialog( $.extend({}, oDefConf, aConf) );
			}
			//box is initialized but if title or configurations passed again
			else if(sTitle!=undefined || aConf!=undefined) {
				oBox.dialog('option', $.extend({}, oDefConf, aConf))
			}

			//append messages if already open
			if(oBox.dialog('isOpen')){
				oBox.find('ul:first').append(html);
			}
			//else add list and open
			else{
				oBox.find('ul:first').html(html);
				oBox.dialog('open');
			}

			return oBox;

		},

		/**
		 * create new alert message type
		 *
		 * @param sKey name of the method to append to class
		 * @param oConf (optional) configuration object - <br>{	<br>id: 'id', <br>listClass: 'listClassName', <br>template: '%BODY%' <br>}
		 */
		createTemplate: function(sKey, oConf){

			var self = this,
				oDefConf = {
					id: (new Date()).getTime(),
					listClass: 'cOkMsgList',
					template: '%BODY%'
				};

			//check if method already exists
			if(this.hasOwnProperty(sKey) && !this._templates.hasOwnProperty(sKey)){
				throw 'Kwgl: Method "'+ sKey +'" already exists in Kwgl.msg class';
			}

			//append template to class variable
			this._templates[sKey] = $.extend({}, oDefConf, oConf);
			//attach new method for sKey to the message object
			this[sKey] = function(msgs, sTitle, aConf){

				return self._createMessage(sKey, msgs, sTitle, aConf);

			};

		},

		/**
		 * method to open common error popup and display passed on messages
		 *
		 * @param msgs mixed message to be show in popup
		 * @param sTitle string title of the message
		 * @param aConf object configurations for dialog
		 * @return jQuery jquery ui dialog object
		 */
		error: function(msgs, sTitle, aConf){

			return this._createMessage('error', msgs, sTitle, aConf);

		},

		/**
		 * shows ok message with given text
		 *
		 * @param msgs mixed message to be show in popup
		 * @param sTitle string title of the message
		 * @param aConf object configurations for dialog
		 * @return jQuery jquery ui dialog object
		 */
		success: function(msgs, sTitle, aConf){

			return this._createMessage('success', msgs, sTitle, aConf);

		},

		/**
		 * creates a popup on the fly
		 *
		 * @param sBody string body of the popup
		 * @param aConf object configurations passed to dialog
		 * @param bPersistant boolean (optional) wheather to distroy the object on close or keep it, default = false
		 * @return jQuery jquery ui dialog object
		 */
		popup: function(sBody, aConf, bPersistant){

			var settings = {
				autoOpen: true,
				title	: '',
				draggable: false,
				resizable: false,
				minHeight: 0,
				modal: true,
				width: 400
			},
			opts = $.extend({}, settings, aConf);

			//attach distroy methods to the object
			var tmpClose = opts.close;
			opts.close = function(event, ui){

				if($.isFunction(tmpClose)){
					tmpClose(event, ui);
				}
				//destroy on close if not persistant
				if(bPersistant !== true){
					$(this).dialog( "destroy" ).remove();
				}

			}

			//create popup and open it
			return $('<div>'+ sBody +'</div>').appendTo('body').dialog(opts);

		}

	};

})(jQuery);