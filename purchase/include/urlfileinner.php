<?php
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';

// Define paths if not already defined
if (!defined('COMMON_FUNCTION_PATH')) {
    define('COMMON_FUNCTION_PATH', $path . 'include/common_functions/');
}

// Include required files
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");

// Include common functions
$commonFunctions = array(
    'common_functions.php',
    'finance_common_functions.php',
    'common_sub_functions.php',
    'common_production_functions.php'
);

foreach ($commonFunctions as $file) {
    $filePath = COMMON_FUNCTION_PATH . $file;
    if (file_exists($filePath)) {
        include_once($filePath);
    }
}

// Try to include store wise function file if exists
$storeWiseFunctionPath = COMMON_FUNCTION_PATH . 'common_production_store_wise_function.php';
if (file_exists($storeWiseFunctionPath)) {
    include_once($storeWiseFunctionPath);
}

include_once($path."crm/app/send_quotation.php");

if(empty($_SESSION['company_id'])){
		die();
	}
?>