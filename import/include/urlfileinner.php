<?php 
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
//error_reporting(E_ALL);
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_production_functions.php");
include_once($path."crm/app/send_quotation.php");
?>