<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "add") {
			if($_POST['token'] == $_SESSION['token']) {
			
							$info['title']		= $POST['title'];
							$info['address']	= $POST['address'];
							$info['cstno']		= $POST['cstno'];
							$info['cst_date']	= date('Y-m-d',strtotime($POST['cst_date']));
							$info['gstno']		= $POST['gstno'];
							$info['gst_date']	= date('Y-m-d',strtotime($POST['gst_date']));
							if(!empty($_FILES['logo']['tmp_name'])) {
							$info['logo']		= upload_image($_FILES);
							};
							if(!empty($_FILES['f_logo']['tmp_name'])) {
							$info['f_logo']		= upload_image1($_FILES);
							};
							$info['cdate']		= 	date("Y-m-d H:i:s");
							$info['user_id']	= $_SESSION['user_id'];
							$inserid=add_record('tbl_setting', $info, $dbcon);
					if($inserid)
			 			echo "1";
					else
						echo "0";
				}
			
		}		
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
			{			
							$info['start_no']		= $POST['start_no'];
							$info['invoice_format']	=$POST['invoice_format'];
							$info['formate']		= $POST['formate'];
							$info['cdate']		= date("Y-m-d H:i:s");
							$updateid=update_record('tbl_invoiceserise', $info,"invoiceserice_id=".$POST['eid'] , $dbcon);
				
				
				if($updateid)
					echo "update";
				else
					echo "0".$dbcon->error;
				
			}
		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
function upload_image($FILES)
{
	$rand=rand(0,9999);
	if(!empty($FILES['logo']['tmp_name'])) {
					list($width, $height, $type, $attr) = getimagesize($FILES['logo']['tmp_name']);
					if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
						$allowedExts = array("gif", "jpeg", "jpg", "png");
						$temp = explode(".", $FILES["logo"]["name"]);
						$extension = strtolower(end($temp));
						if (in_array($extension, $allowedExts)) {
							$File = "header".$rand.".jpg";
							$tmp_name = $FILES["logo"]["tmp_name"];
							move_uploaded_file($tmp_name,LOGO_A.$File);
							smart_resize_image(LOGO_A.$File,794,210);
							
							
						}
				}
		return  $File;				
	}
	
}
function upload_image1($FILES)
{
	$rand=rand(0,9999);
	if(!empty($FILES['f_logo']['tmp_name'])) {
					list($width, $height, $type, $attr) = getimagesize($FILES['f_logo']['tmp_name']);
					if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
						$allowedExts = array("gif", "jpeg", "jpg", "png");
						$temp = explode(".", $FILES["f_logo"]["name"]);
						$extension = strtolower(end($temp));
						if (in_array($extension, $allowedExts)) {
							$File = "footer".$rand.".jpg";
							$tmp_name = $FILES["f_logo"]["tmp_name"];
							move_uploaded_file($tmp_name,LOGO_A.$File);
							smart_resize_image(LOGO_A.$File,600,100);
							copy(PRODUCT_UPIMG.$File,LOGO_A."thumb//".$File);
							smart_resize_image(LOGO_A."thumb//".$File,600,100);
							
						}
				}
		return  $File;				
	}
	
}

?>
