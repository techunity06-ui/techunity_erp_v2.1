<?php
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');	
$authenticate = true;    

include("constants.php");
include("core_functions.php");
include("security.php"); 

	/* 
     * PROJECT DETAILS
    */   
    define("TITLE","BRPERP");
  /*  define("DOMAIN","https://www.brperp.com/brp_erp_v15/");
     define("DOMAIN_F","https://www.brperp.com/brp_erp_v15/");
	 define("ROOT","/brp_erp_v15/");
	 define("ROOT_F","/brp_erp_v15/"); */
	// echo $_SERVER["SERVER_ADDR"]; die;
	if($_SERVER["SERVER_ADDR"] == '127.0.0.1')
	{

		define("DOMAIN","http://".$_SERVER["SERVER_ADDR"]."/support_branch/");
		define("DOMAIN_F","http://".$_SERVER["SERVER_ADDR"]."/support_branch/");
		define("ROOT","/support_branch/");
		define("ROOT_F","/support_branch/");

		 /*define("DOMAIN","http://".$_SERVER["SERVER_ADDR"]."/brp_erp/");
		 define("DOMAIN_F","http://".$_SERVER["SERVER_ADDR"]."/brp_erp/");
		 define("ROOT","/brp_erp/");
		 define("ROOT_F","/brp_erp/");*/

	}
	else
	{
		define("DOMAIN","https://www.brperp.com/jade_granite_testing/");
		define("DOMAIN_F","https://www.brperp.com/jade_granite_testing/");
		define("ROOT","/jade_granite_testing/");
		define("ROOT_F","/jade_granite_testing/");
	}
	

    //define("INC","/metr_purchase_sale_soft/");
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

//    define("SUPPORT_URL","http://127.0.0.1/brp_erp/");
	define("SUPPORT_URL","http://bigdatasuite.in/supportapi/");
	define("KEY_URL","http://www.brperp.com/erp_key_generate_soft/");

	/* CHEQUE CONFIG*/
	define("DOMAIN_CHEQUE",DOMAIN."cheque_app/");
	define("ROOT_CHEQUE",ROOT."cheque_app/");
	define("INC",ROOT."cheque_app/");
	define("CHEQUE_IMG","upload//check/");

	//image upload and view path admin side
	define("BACKUP","upload//backup//");
	define("BKP_DAYS",29);
	define("LOGO_A","..//..//..//view//img//logo//");
	define("SIGNATURE","..//..//..//view//upload//signature//");
	define("SIGNATURE_V","view//upload//signature//");
	define("LOGO","view//img//logo//");
	
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

	define("PRE_UPING","..//..//..//view//upload//pre_prod_doc//");
	define("PRE_VWING","view//upload//pre_prod_doc//");

	define("MAIL_ATTACH_ACTUAL","..//view//upload//mail_attach//");
	define("MAIL_ATTACH_UPING","..//..//view//upload//mail_attach//");
	
	define("SETTING_A","..//..//view//upload//quotation_pdf_file//");
	define("SETTING","view//upload//quotation_pdf_file//");
	define("QUO_A","..//..//view//upload//quotation_pdf_file//");
	define("QUO","view//upload//quotation_pdf_file//");
	
	define("EMAILFILE_UPING","..//..//view//upload//email_attachment//");
	define("EMAILFILE_VWING","view//upload//email_attachment//");
	
	/* Maulik Added Purchase root -22-09-2021 */
	define("PURCHASE_ROOT","purchase/");
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

   if($_SERVER["SERVER_ADDR"] == '127.0.0.1')
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
		define("DB","brperp_jade_granite_testing");
		define("DB_USER","brperp_jade_granite_testing");
		define("DB_PASS","India@@@123");
	}

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

    /*Database Connectivity*/
    $dbcon = new mysqli(SERVER,DB_USER,DB_PASS,DB);
    if($dbcon->connect_errno > 0){
    	die('Unable to connect to database server. [' . $dbcon->connect_error . ']');
    }
    if(isset($_SESSION['permission'])) {
    	$permission = unserialize($_SESSION['permission']);
    }	    
    define("ENCRYPTION_KEY", "!@#$%^&*");
    $db_sess="SET SESSION sql_mode=''";
    $db_sess_rs=$dbcon->query($db_sess);
?>