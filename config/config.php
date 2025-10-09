<?php


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');	
$authenticate = true;    




// Global Error Logger Setup
function global_error_logger($message) {
	$logFile = __DIR__ . '/../error_log.txt';
	$date = date('Y-m-d H:i:s');
	file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
	global_error_logger("Error [$errno]: $errstr in $errfile on line $errline");
});

set_exception_handler(function($exception) {
	global_error_logger("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
});

register_shutdown_function(function() {
	$error = error_get_last();
	if ($error !== NULL) {
		global_error_logger("Fatal Error [{$error['type']}]: {$error['message']} in {$error['file']} on line {$error['line']}");
	}
});



include("constants.php");
include("core_functions.php");
include("security.php");
include("db_config.php");

	/* 
     * PROJECT DETAILS
    */   
  define("TITLE","BRPERP");
  /*if($_SERVER["SERVER_ADDR"] == '127.0.0.1')
	{
	 define("DOMAIN","http://".$_SERVER["SERVER_ADDR"]."/support_branch/");
	 define("DOMAIN_F","http://".$_SERVER["SERVER_ADDR"]."/support_branch/");
	 define("ROOT","/support_branch/");
	 define("ROOT_F","/support_branch/");

	}
	else
	{
		define("DOMAIN","https://www.brperp.com/client_branch/");
		define("DOMAIN_F","https://www.brperp.com/client_branch/");
		define("ROOT","/client_branch/");
		define("ROOT_F","/client_branch/");
	}*/
	

  /*
     * PROJECT DETAILS
    */   
	define("SERVICE_ROOT","service/");
	define("FINANCE_ROOT","finance/");
	define("CRM_ROOT","crm/");
	define("CRM_ROOT_F","crm/");
	define("HRMS_ROOT","hrms/");
	define("HRMS_ROOT_F","hrms/");
	define("SUPPORT_ROOT","support/");
	define("ADMINISTRATION_ROOT","administration/");
	define("PRODUCTION_ROOT","production/");
	define("INVENTORY_ROOT","inventory/");
	define("REPORT_ROOT","reports/");
	define("MAINTENANCE_ROOT","maintenance/");

//    define("SUPPORT_URL","http://127.0.0.1/brp_erp/");
	define("SUPPORT_URL","http://bigdatasuite.in/supportapi/");
	define("KEY_URL","https://techunity.live/erp_key_generate_soft/");

	/* CHEQUE CONFIG*/
	define("DOMAIN_CHEQUE",DOMAIN."cheque_app/");
	define("ROOT_CHEQUE",ROOT."cheque_app/");
	define("INC",ROOT."cheque_app/");
	define("CHEQUE_IMG","upload//check/");

	//image upload and view path admin side
	define("BACKUP","upload//backup//");
	define("BKP_DAYS",29);
	define("LOGO_A","..//..//..//view//img//logo//");
	define("LOGO","view//img//logo//");
	define("SIGNATURE","..//..//..//view//upload//signature//");
	define("SIGNATURE_V","view//upload//signature//");
	
	define("CUSTOMER_UPING","..//..//view//upload//customer_excel//");
	define("CUSTOMER_VWING","view//upload//customer_excel//");
	define("PRO_IMG_UPING","..//..//view//upload//product_images//");
	define("PRO_IMG_VWING","view//upload//product_images//");

	define("PRODUCT_DEMO","view//upload//demo//");
	define("PRODUCT_UPING","..//..//..//view//upload//product_excel//");
	define("PRODUCT_QC_UPING","..//..//..//view//upload//product_qc_parameter_excel//");
	define("PRODUCT_PROCESS_UPING","..//..//..//view//upload//product_process//");

	define("INQ_PRO_IMG_UPING","..//..//view//upload//inq_pro_img//");
	define("INQ_PRO_IMG_VWING","view//upload//inq_pro_img//");
	define("INQ_ATTACH_UPING","..//..//view//upload//inq_attach//");
	define("INQ_ATTACH_VWING","view//upload//inq_attach//");

	define("LED_ATTACH_UPING","..//..//view//upload//led_attach//");
	define("LED_ATTACH_VWING","view//upload//led_attach//");

	define("PRE_UPING","..//..//..//view//upload//pre_prod_doc//");
	define("PRE_VWING","view//upload//pre_prod_doc//");

	define("RECEIPT_FILE_UPING","..//..//..//view//upload//grn_attach_doc//");
	define("RECEIPT_FILE_VWING","view//upload//grn_attach_doc//");

	define("STAGE_TEMPLATE_FILE_UPING","..//..//..//view//upload//stage_template_attach_doc//");
	define("STAGE_TEMPLATE_FILE_VWING","view//upload//stage_template_attach_doc//");

	define("SO_ATTACH_UPING","..//..//..//view//upload//sales_order_attach_doc//");
	define("SO_ATTACH_VIEWING","view//upload//sales_order_attach_doc//");
	
	define("PO_ATTACH_UPING","..//..//..//view//upload//po_attach_doc//");
	define("PO_ATTACH_VIEWING","view//upload//po_attach_doc//");

	define("DISPATCH_ATTACH_UPING","..//..//..//view//upload//dispatch_attach_doc//");
	define("DISPATCH_ATTACH_VIEWING","view//upload//dispatch_attach_doc//");

	define("QC_FILE_UPING","..//..//..//view//upload//qc_attach_doc//");
	define("QC_FILE_VWING","view//upload//qc_attach_doc//");

	define("MAIL_ATTACH_ACTUAL","..//view//upload//mail_attach//");
	define("MAIL_ATTACH_UPING","..//..//view//upload//mail_attach//");
	
	define("SETTING_A","..//..//view//upload//quotation_pdf_file//");
	define("SETTING","view//upload//quotation_pdf_file//");
	define("QUO_A","..//..//view//upload//quotation_pdf_file//");
	define("QUO","view//upload//quotation_pdf_file//");
	
	define("EMAILFILE_UPING","..//..//view//upload//email_attachment//");
	define("EMAILFILE_VWING","view//upload//email_attachment//");

	// For Daily report file attachment
	define("DAILY_REPORT_FILE_UPING","..//..//..//view//upload//daily_report//");
	define("DAILY_REPORT_FILE_VWING","view//upload//daily_report//");

	// For Complaint file attachment
	define("COMPLAINT_FILE_UPING","..//..//..//view//upload//complaint//");
	define("COMPLAINT_FILE_VWING","view//upload//complaint//");

	// For import inquiry products
	define("INQUIRY_PRODUCT_FILE_UPING","..//..//..//view//upload//inquiry_product_import//");
	define("INQUIRY_PRODUCT_FILE_VWING","view//upload//inquiry_product_import//");

	
	/* Maulik Added Purchase root -22-09-2021 */
	define("PURCHASE_ROOT","purchase/");
	define("DISPATCH_ROOT","dispatch/");
	define("IMPORT_ROOT","import/");
	define("PURCHASE_ROOT_F","purchase/");
	/* Maulik End Purchase root -22-09-2021 */
	
	//Company Switch Login Password 
	define('LOGIN_SETTING',"1"); //1 without password : 0 For with Password
	 //define("COMPANY","metR Technology");
	define("C_URL","http://www.metrtechnology.com");
	define("DEVELOPER","");
	define("D_URL","http://www.metrtechnology.com");

	define("CITY","AHMEDABAD");
	define("COUNTRY","INDIA");
	define("CURRENCY","INR");
	define("C_SYMBOL","&#8377;");
	define("SMS_ATTACH_UPING","..//..//view//upload//sms_attach//");

    /*
     *	Database Credentials
     */

  /* if($_SERVER["SERVER_ADDR"] == '127.0.0.1')
	{
		define("SERVER","192.168.1.20");
		// define("SERVER","localhost");
		define("DB","bigdatas_umaboy_erp");
		define("DB_USER","root");
		// define("DB_PASS","");
		define("DB_PASS","AC23z4PbKUY5NpLq"); 
	}
	else
	{
		define("SERVER","localhost");
		define("DB","brperp_client_branch_db");
		define("DB_USER","brperp_client_branch");
		define("DB_PASS","India@@@123");
	}*/

	/*
    * Admin Details
    */
	
	define("ADMIN","Metr Technology");
	define("ADMIN_EMAIL","amish.brp@gmail.com");
	define("SENDMAILKEY","0yrQTOAFxH4JCVS3");

    /*
    * SMTP Details
    */
    define("IS_SMTP","1");
    define("MAIL_HOST","mail.brperp.com");
    define("MAIL_USERNAME","developer@brperp.com");
    define("MAIL_PASSWORD","developer@123");
    define("MAIL_ENCRYPTION","ssl");
    define("MAIL_PORT",465);

    //einv detail
     define("EINV_URL","http://einvlive.webtel.in/v1.03/GenIRN2");
     define("EINV_USERNAME","D25E6189-1CFE-47B3-803D-10CB599E1EE0");
     define("EINV_PASSWORD","21C78FD4-ED9E-4B00-AF1E-D08CAE717D7F");
     define("EINV_CDKEY","1696085");

     //e-WAY detail
     define("EWAY_URL","http://ewayasp.webtel.in/EWayBill/v1.3/GenEWB");
     define("EWAY_USERNAME","AA8AAFB8-22B9-418D-904C-0EC335E90A4D");
     define("EWAY_PASSWORD","F52F46E7-BAD5-48AA-BAB4-F8FD64CC55DB");
     define("EWAY_CDKEY","1550600");

    /*Database Connectivity*/
    /*$dbcon = new mysqli(SERVER,DB_USER,DB_PASS,DB);
    if($dbcon->connect_errno > 0){
    	die('Unable to connect to database server. [' . $dbcon->connect_error . ']');
    }
    if(isset($_SESSION['permission'])) {
    	$permission = unserialize($_SESSION['permission']);
    }	  */  
    define("ENCRYPTION_KEY", "!@#$%^&*");
    $db_sess="SET SESSION sql_mode=''";
    $db_sess_rs=$dbcon->query($db_sess);
?>