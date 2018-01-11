<?php 
	include_once('../../../../assets/php/sschecker.php'); 
?>
<!DOCTYPE html>

<html lang="en">
<head>
	
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
	
	<!-- ----------------------------------------------------- BEGIN - hisdb ONLY -------------------------------------------------- -->
	<!-- Web Fonts -->
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600&amp;subset=cyrillic,latin">
	
	<!-- CSS Global Compulsory -->
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="../../../../assets/css/hisdb/style.css">

	
	<!-- hisdb - CSS Implementing Plugins -->
	
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/animate.css">	
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/line-icons/line-icons.css">
	<!--link rel="stylesheet" href="../../../../assets/plugins/font-awesome-4.4.0/css/font-awesome.min.css"-->
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/ladda-buttons/css/custom-lada-btn.css">
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/hover-effects/css/custom-hover-effects.css">
	
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/datatables/css/jquery.dataTables.css">
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/bootstrap-table/bootstrap-table.min.css">
	
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/sky-forms-pro/skyforms/css/sky-forms.css">
	<link rel="stylesheet" href="../../../../assets/plugins/hisdb/sky-forms-pro/skyforms/custom/custom-sky-forms.css">
	<!-- hisdb - CSS Theme -->
	<link rel="stylesheet" href="../../../../assets/css/hisdb/theme-colors/blue.css" id="style_color">
	<link rel="stylesheet" href="../../../../assets/css/hisdb/theme-skins/dark.css">

	<!-- hisdb - CSS Customization -->	
	<link rel="stylesheet" href="../../../../assets/css/hisdb/custom.css">
	
	<!-- ----------------------------------------------------- END - hisdb ONLY -------------------------------------------------- -->
	
    <!--style>
		.formclass{
			background-color:#f5f5f5;
			padding:0px 10px 10px;
			margin:5px 0;
			-moz-border-radius:16px;
			-webkit-border-radius:16px;
			border-radius:16px;
		}
		
		.ScolClass{
			float:left;
			margin-top:5px;
		}
		.StextClass{
			position: relative;
			padding-left: 65px;
			margin-top: 25px;
			padding-right: 60%;
		}
		.row{
			padding:5px;
			margin:5px;
		}
		.pointer {
			cursor: pointer;
		}
		.wrap {
		    word-wrap: break-word;
		    white-space: normal !important;
		}
		.ui-th-column{
			word-wrap: break-word;
			white-space: normal !important;
			vertical-align: top !important;
		}
		.radio-inline+.radio-inline {
			margin-left: 0;
		}
		.alert.alert-warning{
			float: left !important;
			margin-bottom: 0 !important;
			width: 80% !important;
			padding: 10px !important;
		}
		.radio-inline {
			margin-right: 10px;
		}
		::-webkit-scrollbar{
		  width: 10px;  /* for vertical scrollbars */
		  height: 10px; /* for horizontal scrollbars */
		}
		::-webkit-scrollbar-track{
		  background: rgba(0, 0, 0, 0.1);
		}
		::-webkit-scrollbar-thumb{
		  background: rgba(0, 0, 0, 0.5);
		}
		.box-shadow--2dp {
			box-shadow: 0 2px 2px 0 rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .2), 0 1px 5px 0 rgba(0, 0, 0, .12)
		}
		.box-shadow--3dp {
			box-shadow: 0 3px 4px 0 rgba(0, 0, 0, .14), 0 3px 3px -2px rgba(0, 0, 0, .2), 0 1px 8px 0 rgba(0, 0, 0, .12)
		}
		.box-shadow--4dp {
			box-shadow: 0 4px 5px 0 rgba(0, 0, 0, .14), 0 1px 10px 0 rgba(0, 0, 0, .12), 0 2px 4px -1px rgba(0, 0, 0, .2)
		}
		.box-shadow--6dp {
			box-shadow: 0 6px 10px 0 rgba(0, 0, 0, .14), 0 1px 18px 0 rgba(0, 0, 0, .12), 0 3px 5px -1px rgba(0, 0, 0, .2)
		}
		.box-shadow--8dp {
			box-shadow: 0 8px 10px 1px rgba(0, 0, 0, .14), 0 3px 14px 2px rgba(0, 0, 0, .12), 0 5px 5px -3px rgba(0, 0, 0, .2)
		}
		.box-shadow--16dp {
			box-shadow: 0 16px 24px 2px rgba(0, 0, 0, .14), 0 6px 30px 5px rgba(0, 0, 0, .12), 0 8px 10px -5px rgba(0, 0, 0, .2)
		}
		.ui-search-toolbar {
			background: rgba(0,0,0,0.05);
		}
		.ui-search-toolbar input[type='text']{
			height:25px;
		}
		.minuspad-15{
			padding-left: 0px !important;
			padding-right: 0px !important;
		}
 	</style-->	
    <title></title>
</head>

<body class="header-fixed">
	<div class="wrapper">