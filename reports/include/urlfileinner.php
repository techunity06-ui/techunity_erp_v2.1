<?php 

	$path = '../../../';
	$include = '../../../include/';
	$include1 = '../../include/';
	
	include($path."config/config.php");
	include($path."config/session.php");
	include($include."function_database_query.php");
	include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");	
	include_once(COMMON_FUNCTION_INNER_PATH."common_production_functions.php");
	include_once(COMMON_FUNCTION_PATH."common_production_store_wise_function.php");
	include_once(COMMON_FUNCTION_INNER_PATH."common_sub_functions.php");

	if(empty($_SESSION['company_id'])){
		die();
	}

?>