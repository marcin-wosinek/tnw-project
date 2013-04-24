/**
 * @auther Udantha Diyanath Pathirana <udanthaya@gmail.com>
 * @author Jayawi Perera <jayawiperera@gmail.com>
 *
 * This class is for table pagination
 * - Dependancies
 *		Ajax
 *		jsViews
 *
 * Usage:
 *		$('#tableId').paginate({
 *			paginate_body_container: $('tbody'),
 *			etc
 *		});
 *
 * After initializing the object as above;
 *	- reloading table data can be done with $('#tableId').paginate('reload')
 *	- replacing some of initialized parameters can be done with $('#tableId').paginate({paginate_body_container: $('somethingelse')});
 */

/**
 * Create jQuery extended Object for Pagination
 * using the Pagination Class
 */
;
(function($){

	$.fn.paginate = function (oPassedSettings) {

		return this.each(function() {

			var oPaginateSelector = $(this);

			var oStoredPaginateData = $.data(oPaginateSelector[0], 'PAGINATION_DATA');

			if (oStoredPaginateData) {
				/**
				 * If pagination is already attached to the element,
				 * then carry out specificed task (reload, etc)
				 */

				var sPassedOptionsType = typeof oPassedSettings;

				switch (sPassedOptionsType) {
					case 'string':

						switch (oPassedSettings) {
							case 'reload':
								// Reload Table
								oStoredPaginateData.obj.filterData();
								break;
						}

						break;
					case 'object':
						//re initialize settings and load table
						oStoredPaginateData.obj.reInit(oPassedSettings);
						break;
				}

			} else {
				/**
				 * else (if pagination is not attached),
				 * attach it
				 */

				var oCombinedSettings = $.extend({}, oPassedSettings, {
					paginate_container: oPaginateSelector
				});

				// Validate Required Options
				if (oCombinedSettings.paginate_ajax_url) {

					var oPaginate = new Pagination();

					oPaginate.init(oCombinedSettings); // Instantiate

					// Add the paginate object to the element
					$.data(oPaginateSelector[0], 'PAGINATION_DATA', {
						obj : oPaginate
					});
				}
			}

		});

	}

	var Pagination = function() {

		// Object Settings
		var oSettings = {
			// Table Object or Container Object
			paginate_container						: null,
			// Table Body Object or Container Object where Paginate Information Rows are shown in
			paginate_body_container					: null,
			// Table Foot Object or Container Object where Pagination Links will be shown in
			paginate_pagination_container			: null,
			// Template to use for Pagination Rendering
			paginate_pagination_template_selector	: 'iTemplateBasePagination',

			paginate_link_class						: 'cPaginationPaginateLink',

			paginate_link_class_active				: 'cPaginationPaginateLinkActive',
			// Table Head Object or Container Object where Filtering Inputs will be shown in
			paginate_filter_container				: null,
			// Table Head Object or Container Object where Sort elements will be shown in
			paginate_sort_container					: null,
			// Class given to elements that are used to invoke sorting
			paginate_sort_class						: 'cPaginationSortable',
			// Class given to element that is currently being used in sorting
			paginate_sort_class_active				: 'cPaginationSortableActive',
			// Class given to parent element the element that is currently being used in sorting
			paginate_sort_class_active_parent		: 'cPaginationSortableActiveParent',
			// Class given to sortable elements when in ascending order
			paginate_sort_class_ascending			: 'cPaginationSortableAsc',
			// Class given to sortable elements when in descending order
			paginate_sort_class_descending			: 'cPaginationSortableDesc',
			// Zend_Paginator Scrolling style (Allowed values: all, elastic, jumping, sliding
			paginate_scrolling_style				: 'sliding',
			// Number of rows in a page
			paginate_rows_per_page					: 50,
			// Number of pages to be shown in pagination link container
			paginate_page_range						: 10,
			// Page to show on initial load
			paginate_page_start						: 1,
			// Full URL to retrieve data from backend
			paginate_ajax_url						: "",
			// ?
			ajax									: {
				loadingScreen: true,
				// null will make the context to pagination body
				context: null,
				// miliseconds to start the loader
				startLoaderAfter: 350
			},
			// Function to build the customised parameters to pass through AJAX
			buildParameter							: function() {
				return "";
			},
			// Function to render the Table Body
			setBody									: function() {
				return "";
			},
			// Function to be executed before pagination loads
			beforeLoad								: function() {
			},
			// Function to be executed after pagination loads
			afterLoad								: function() {
			}
		};

		var oSelf = this;

		var oSortableElements = null; // Collection of elements that can be used to sort

		var oFilterElements = null; // Collection of elements that are used for filtering

		/**
		 * Initialisation function executed after object creation
		 */
		this.init = function(oPassedSettings) {

			$.extend(true, oSettings, oPassedSettings);

			// Bind Filter Actions
			if (oSettings.paginate_filter_container) {

				var oTimeOut;

				// 'Cache' Filter Elements
				oFilterElements = $(oSettings.paginate_filter_container).find(':input');

				oFilterElements.each(function() {
					var sElementTag = this.tagName.toLowerCase();
					var oCurrentElement = $(this);

					// Bind Filter Events
					switch (sElementTag) {
						case 'input':
							// If tag is an input field, attach to 'keyup'
							oCurrentElement.bind('keyup', function(event){
								// Clear timeout to prevent unnecessary requests
								clearTimeout(oTimeOut);
								oTimeOut = setTimeout($.proxy(oSelf, "filterData"), 200);
							});
							//.keyup( $.proxy(self, "filterData") );
							break;
						case 'select':
							// If tag is a select field, attach to 'onchange'
							oCurrentElement.bind('change', function(){
								// Clear timeout to prevent unnecessary requests
								clearTimeout(oTimeOut);
								oTimeOut = setTimeout($.proxy(oSelf, "filterData"), 200);
							});
							break;
					}

				});
			}

			// Initialise Sorting
			this.initSort();

			// Load First Page
			this.loadData(oSettings.paginate_page_start);
		};

		/**
		 * Function that can be used to replace parsed settings after the object has been created
		 * Parsed Settings Map will replace existing Settings
		 */
		this.reInit = function(oNewPassedSettings){
			$.extend(true, oSettings, oNewPassedSettings);

			// Reload data-set
			this.filterData();
		};













		/**
		 * Function to filter table data
		 * Triggered by Filter Input fields bound to Pagination
		 */
		this.filterData = function(oEvent) {
			var aFilterType = [];
			var aFilterColumn = [];
			var aFilterClause = [];
			var aFilterFunction = [];
			var aFilterValue = [];
			var iPageToGoTo = 0;
			var bNoLoadingScreen = false;
			var sSortParameters = null;

			var iTemporaryStart = null;

			// If Event is specified, get Start Page Value from Event Target
			if (oEvent) {

				// Handle Event Target
				var oEventTarget = $(oEvent.target);
				if (oEventTarget.hasClass(oSettings.paginate_link_class)) {
					iTemporaryStart = oEventTarget.attr('page_to_go_to');

				} else {
					var oNewEventTarget = oEventTarget.parents('.' + oSettings.paginate_link_class);

					if (oNewEventTarget.length > 0) {
						iTemporaryStart = oNewEventTarget.attr('page_to_go_to');
					}

				}

			}

			if (iTemporaryStart != null) {

				iPageToGoTo = iTemporaryStart;

			} else {
				if ((iTemporaryStart = $(oSettings.paginate_pagination_container).find('.' + oSettings.paginate_link_class_active).attr('page_to_go_to'))) {
					// If not, then use Current Page as Start Page Value
					iPageToGoTo = iTemporaryStart;
				} else {
					iPageToGoTo = 1;
				}
			}

			// Get Filter Columns
			if (oSettings.paginate_filter_container) {
				var iFilterCounter = 0;

				oFilterElements.each(function() {
					var sFilterElementTag = this.tagName.toLowerCase();
					var oCurrentFilterElement = $(this);

					switch (sFilterElementTag) {
						case 'input':
						case 'select':

							var sFilterType = oCurrentFilterElement.attr('clause_type');
							var sFilterColumn = oCurrentFilterElement.attr('filter_column');
							var sFilterClause = oCurrentFilterElement.attr('filter_clause');
							var sFilterFunction = oCurrentFilterElement.attr('filter_function');

							if (oCurrentFilterElement.val() !== '' && sFilterColumn) {
								aFilterType[iFilterCounter] = sFilterType;
								aFilterColumn[iFilterCounter] = sFilterColumn;
								aFilterClause[iFilterCounter] = sFilterClause;
								aFilterFunction[iFilterCounter] = sFilterFunction;
								aFilterValue[iFilterCounter] = oCurrentFilterElement.val();

								iFilterCounter++;
							}

							break;
					}

				});
			}

			sSortParameters = this._generateSortParameters();

			this.loadData(iPageToGoTo, aFilterColumn, aFilterClause, aFilterFunction, aFilterValue, sSortParameters, bNoLoadingScreen);
		}

		/**
		 * function to load table data
		 */
		this.loadData = function(iStart, aFilterColumn, aFilterClause, aFilterFunction, aFilterValue, sSortParameters, bNoLoadingScreen) {

			var ajaxSettings = oSettings.ajax; //local copy of ajax settings
			//normalize ajax settings
			if(ajaxSettings.context==null){
				ajaxSettings.context = oSettings.paginate_body_container;
			}

			//set loading screen
			if (bNoLoadingScreen === true) {
				ajaxSettings.loadingScreen = false;
			}

			//execute custom functions before load
			var bValidLoad = oSettings.beforeLoad();

			//check if the loading should be stopped
			if (bValidLoad !== false) {
				aFilterColumn = (aFilterColumn) ? aFilterColumn : [];
				aFilterClause = (aFilterClause) ? aFilterClause : [];
				aFilterFunction = (aFilterFunction) ? aFilterFunction : [];
				aFilterValue = (aFilterValue) ? aFilterValue : [];

				var sPostParameters = "";

				// Add Filter Conditions to the Parameter to be Posted
				if (aFilterColumn.length>0) {
					for (var mFilterKey in aFilterColumn) {
						sPostParameters += '&clause_column_name[' + mFilterKey + ']='+ aFilterColumn[mFilterKey];
						sPostParameters += '&clause_type[' + mFilterKey + ']=' + (aFilterClause[mFilterKey] ? aFilterClause[mFilterKey] : 'where');
						sPostParameters += '&clause_comparison_function[' + mFilterKey + ']=' + (aFilterFunction[mFilterKey] ? aFilterFunction[mFilterKey] : 'LIKE');
						sPostParameters += '&clause_column_value[' + mFilterKey + ']=' + aFilterValue[mFilterKey];
					}
				}

				sSortParameters = this._generateSortParameters();

				//attach sorting parameters
				if (sSortParameters) {
					sPostParameters += sSortParameters
				}

				var para = "";
				para = oSettings.buildParameter(); //add to parameter from customized function
				para += '&page_current='+ iStart +'&page_item_count=' + oSettings.paginate_rows_per_page + sPostParameters + '&page_range='+ oSettings.paginate_page_range + '&page_scrolling_style=' + oSettings.paginate_scrolling_style;

				//send request to backend
				Kwgl.xhr.send(oSettings.paginate_ajax_url, para, function(response){

					oSelf.processLoadData.call(oSelf, response, iStart);
				} , ajaxSettings);
			}

		};

		/**
		 * this handles ajax request and populate table head and body
		 */
		this.processLoadData = function (data, start) {
			/**
			 * get pagination and data arrays
			 * - workaround for unmapped array
			 */
			var oDataSet;
			var oMetaData;

			try {

				if ('__paginationData' in data) {
					//if key exists in first array
					oDataSet = data['__paginationData'];
					oMetaData = data['__paginationMetaData'];
				} else {
					//else go through array
					for (var k in data) {
						if('__paginationData' in data[k]){
							oDataSet = data[k]['__paginationData'];
							oMetaData = data[k]['__paginationMetaData'];
							break;
						}
					}
				}
			} catch(e) {

			}

			//set the body to load data
			var tbody = (oSettings.paginate_body_container)? oSettings.paginate_body_container : $(oSettings.paginate_container).find('tbody:first');
			//tbody.hide();
			var tableString = oSettings.setBody(oDataSet, oMetaData);// get the tbody part
			//clear body content
			tbody.empty();
			//append body
			tbody.append(tableString);

			this.renderPagination(oMetaData);

			//call custom function after pagination
			oSettings.afterLoad();
		};

		/**
		 * Generate Pagination Links
		 */
		this.renderPagination = function (oMetaData) {

			oSettings.paginate_pagination_container.find('.' + oSettings.paginate_link_class).off('click');

			var sPaginationTemplate = oSettings.paginate_pagination_template_selector;
			if (sPaginationTemplate != null) {

				if (oSettings.paginate_pagination_container) {
					oSettings.paginate_pagination_container.each(function(){
						$(this).html($(sPaginationTemplate).render(oMetaData));
					});

					oSettings.paginate_pagination_container.find('.' + oSettings.paginate_link_class).click( $.proxy(this, "filterData") );
				}

			}

		};

		/**
		 * this handles binding sorting to the sorting elements
		 */
		this.initSort = function () {

			if (oSettings.paginate_sort_container) {
				oSettings.paginate_sort_container = $(oSettings.paginate_sort_container);

				oSortableElements = oSettings.paginate_sort_container.find('.' + oSettings.paginate_sort_class); // 'Cache' Sorting Elements

				// Attach Sorting to on-click
				oSortableElements.bind('click', {}, function(e){
					var oCurrentSortableElement = $(this);
					var sSortOrder = oCurrentSortableElement.attr('paginate_sort_order');

					// Remove current sorting class
					oSortableElements.filter('.' + oSettings.paginate_sort_class_active).removeClass(oSettings.paginate_sort_class_active);
					oSettings.paginate_sort_container.find('.' + oSettings.paginate_sort_class_active_parent).removeClass(oSettings.paginate_sort_class_active_parent); //removes class from selected parents

					// Set Element (and parent) as currently active
					oCurrentSortableElement.addClass(oSettings.paginate_sort_class_active);
					oCurrentSortableElement.parent().addClass(oSettings.paginate_sort_class_active_parent); //add selected class to the parent node

					// Remove sort (direction) attribute from all the sortable elements
					oSortableElements.removeAttr('paginate_sort_order');

					// Remove both sort (direction) classes to revert to default state
					oSortableElements.removeClass(oSettings.paginate_sort_class_ascending).removeClass(oSettings.paginate_sort_class_descending);

					// Set sort (direction) classes and sort (direction) attribute
					switch (sSortOrder) {
						case 'ASC':
							oCurrentSortableElement.attr('paginate_sort_order', 'DESC');
							oCurrentSortableElement.addClass(oSettings.paginate_sort_class_descending);
							break;
						case 'DESC':
							oCurrentSortableElement.attr('paginate_sort_order', 'ASC');
							oCurrentSortableElement.addClass(oSettings.paginate_sort_class_ascending);
							break;
						default:
							oCurrentSortableElement.attr('paginate_sort_order', 'ASC');
							oCurrentSortableElement.addClass(oSettings.paginate_sort_class_ascending);
							break;
					}

					// call pagination
					oSelf.filterData.call(oSelf);
				});
			}
		};

		/**
		 * Generates Parameters to be sent to the AJAX URL based on the Sort criteria
		 */
		this._generateSortParameters = function () {

			var sSortParameters = '';

			if (oSettings.paginate_sort_container) {

				var oCurrentSortElement = oSortableElements.filter('.' + oSettings.paginate_sort_class_active);
				var sSortColumn = oCurrentSortElement.attr('paginate_sort_column');
				var sSortOrder = oCurrentSortElement.attr('paginate_sort_order');
				var sSortMode = oCurrentSortElement.attr('paginate_sort_mode');

				switch (sSortMode) {
					case 'none':
						break;

					case 'one-way':

						var sSortOverride = oCurrentSortElement.attr('paginate_sort_one_way_order');

						if (sSortColumn) {
							switch (sSortOverride) {
								case 'ASC':
									// Order Ascending
									sSortParameters = '&sort_column='+ sSortColumn +'&sort_order=ASC';
									break;
								case 'DESC':
									// Order Descending
									sSortParameters = '&sort_column='+ sSortColumn +'&sort_order=DESC';
									break;
							}
						}

						break;

					case 'both-ways':
					default:

						if (sSortColumn) {
							switch (sSortOrder) {
								case 'ASC':
									// Order Ascending
									sSortParameters = '&sort_column='+ sSortColumn +'&sort_order=ASC';
									break;
								case 'DESC':
									// Order Descending
									sSortParameters = '&sort_column='+ sSortColumn +'&sort_order=DESC';
									break;
							}
						}

						break;

				}

			}

			return sSortParameters;

		};

	}
})(jQuery);