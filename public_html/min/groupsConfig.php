<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 *
 *
 *
 */

$aJsLibrary = array(
//	'jquery'					=> '//js/library/jquery-1.6.4.min.js',
	'jquery'					=> '//js/library/jquery-1.7.2.min.js',
//	'jqueryUi'					=> '//js/library/jquery-ui-1.8.16.custom.min.js',
	'jqueryUi'					=> '//js/library/jquery-ui-1.8.20.custom.min.js',
	'kwglCore'					=> '//js/library/kwgl/core.js',
	'kwglMessage'				=> '//js/library/kwgl/message.js',
	'kwglAjax'					=> '//js/library/kwgl/ajax.js',
	'kwglForm'					=> '//js/library/kwgl/form.js',
	'kwglPagination'			=> '//js/library/kwgl/pagination.js',
	'kwglTranslate'				=> '//js/library/kwgl/translate.js',
	'kwglUtility'				=> '//js/library/kwgl/utilities.js'
);
$aJsPlugins = array(
	'jsViews' => array(
		'jsrender'				=> '//js/library/jsviews/jsrender.js',
		'observable'			=> '//js/library/jsviews/jquery.observable.js',
		'views'					=> '//js/library/jsviews/jquery.views.js',
	),
);
$aCssBase = array(
	'reset'						=> '//css/_base/reset.css',
	'baseGrid-12'				=> '//css/_base/grid-12.css',
	'baseGrid-16'				=> '//css/_base/grid-16.css',
	'baseGrid-24'				=> '//css/_base/grid-24.css',
	'baseGrid-Fraction-Fixed'	=> '//css/_base/grid-fraction-fixed.css',
	'baseGrid-Fraction-Fluid'	=> '//css/_base/grid-fraction-fluid.css',
	'baseStyle'					=> '//css/_base/style.css',
);
$aCssLibrary = array(
//	'jqueryUi'					=> '//css/library/jquery-ui-1.8.16.custom.css',
	'jqueryUi'					=> '//css/library/jquery-ui-1.8.20.custom.css',
	'bootstrap'					=> '//css/library/bootstrap/bootstrap.css',
);

return array(

	'jsCore'		=> $aJsLibrary,
	'jsViews'		=> $aJsPlugins['jsViews'],

	'cssBase'		=> $aCssBase,

//	'cssBaseWithBootstrap' => array_merge($aCssBase, ),

	//default module
	'jsExamples'	=> array('//js/default/ajax-example.js'),
	'cssMain'		=> array($aCssLibrary['jqueryUi'], '//css/default/ui_extender.css', '//css/default/js-examples.css')

);