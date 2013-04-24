/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

Kwgl.init({
	
	/**
	 * class variable to hold cached list DOM object
	 */
	oList: null,
	
	/**
	 * initialize all the operations
	 */
	init: function() {
		
		var self = this;
		
		//cache objects
		self.oList = $('#iUlList');
		
		//attach ui tabs
		$('#iTabs').tabs();
		
		//bind events to add data
		$('#iAddList').bind('click', function(){
			
			self.addDataToList(); //call add function
			
		});
		//remove event
		self.oList.delegate('a.cDelete', 'click', function(event){
			
			event.preventDefault();
			self.removeDataFromList( $(this) ); //call remove function
			
		});
		
		//initialize pagination
		this.attachPagination();
		
		//initialize form
		this.attachAjaxForm();
		
	},
	
	/**
	 * add data from backend to list
	 */
	addDataToList: function() {
		
		var self = this;
		//activate loading screen and send request
		Kwgl.xhr.send('/xhr_index/ajaxexample/', {data: 'some parameter'}, function(response){
			
			//append data to the list
			self.oList.append('<li>Response : '+ response.data + ' <a href="#" class="cDelete">x</a></li>');

		}, {loadingScreen	: true});
		
	},
	
	/**
	 * remove element from list
	 */
	removeDataFromList: function(oItem) {
		
		var self = this;
		
		//prompt
		Kwgl.msg.popup('Do you really want to delete this?', {
			buttons: {
				'Yes': function() {
					//remove item
					oItem.closest('li').fadeOut('slow', function(){$(this).remove()});
					$(this).dialog('close');//close dialog
				},
				'No': function() {
					$(this).dialog('close');//close dialog
				}
			},
			title: 'Delete'
		})
		
	},
	
	/**
	 * initialize pagination
	 */
	attachPagination: function() {
		
		var oPaginate = $('#iPaginate'),
			oBody = oPaginate.find('tbody'),
			oPageContainer = oPaginate.find('td.cPages'),
			oFilterContainer = oPaginate.find('.cFilters'),
			oSortContainer = oPaginate.find('.cHead'),
			//function to add rows
			setBody = function(aDataset, aMetaData) {
				
				var sHtml = $([]);
				//loop through returned rows
				for(var key in aDataset){
					var aRow = aDataset[key];
					var oTr = $('<tr>\n\
								<td>'+ aRow.id +'</td>\n\
								<td>'+ aRow.email +'</td>\n\
								<td>'+ aRow.username +'</td>\n\
							</tr>');
					
					oTr.data('DATA_OF_THE_ROW', aRow);
					
					sHtml = sHtml.add(oTr);
				}
				//returne built html
				return sHtml;
				
			};
		
		//create pagination
		oPaginate.paginate({
			bodyObj			: oBody,
			headObj			: oPageContainer,
			setBody			: setBody,
			ajaxUrl			: '/xhr_index/pagination/',
			buildParameter	: function(){
				return 'parakey=Value&key2=val2';
			},
			rowsInPage		: 15,
			pageRange       : 10,
			filterSourceObj	: oFilterContainer,
			sortSourceObj	: oSortContainer,
			beforeLoad		: function() {}, //function to execute before pagination loads.
			afterLoad		: function() {},	//function to execute after pagination
			startPage       : 1, //page to load at first
			ajax			: {
				loadingScreen: true,
				context: null, //null will make the context to pagination body
				startLoaderAfter: 350 //miliseconds to start the loader after
			}
		});
		
		//bind reload pagination event to refresh button
		$('#iReloadPagination').click(function(){
			
			oPaginate.paginate('reload');
			
		});
		
	},
	
	/**
	 * initialize ajax form operations
	 */
	attachAjaxForm: function() {
		
		var oForm = $('#ajaxForm form');
		
		//attach form
		oForm.ajaxSubmit({
			url : '/xhr_index/ajaxform/',
			success: function(oResponse) {
				
				//form submitted, show ok message
				Kwgl.msg.success('Form succesfully submited', 'Form Submit');
				
			}
		});
		
	}
	
});
