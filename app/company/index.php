<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include_once("../../include/common_functions.php");
//error_reporting(E_ALL);
//include("../../config/session.php");
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
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('company_id', 'company_name','address','bank_name','pan_no','com_status', 'user_id');
			$sIndexColumn = "company_id";
			$isWhere = array("com_status = 0");
			$sTable = "tbl_company as com";			
			$isJOIN = array();
			$hOrder = "com.company_id DESC";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['address'];
				$row_data[] = $row['bank_name'];
				$row_data[] = $row['pan_no'];
				
                                $edit_btn = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="companyedit/'.$row['company_id'].'"><i class="fa fa-pencil"></i></a>';
				$pref_btn = '<a class="btn btn-xs btn-warning" data-original-title="Setting" data-toggle="tooltip" data-placement="top" href="company_pref/'.$row['company_id'].'"> <i class="fa fa-cogs"></i></a>';
				$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['company_id'].')"><i class="fa fa-trash-o"></i></button>';
				
                                $row_data[]= $edit_btn.'&nbsp;'.$delete_btn.'&nbsp;'.$pref_btn;
				$appData[] = $row_data;
				$id++;
				}
			
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
					$query="select * from users where active=0 and user_mail='".$_POST['username']."'";
					$rel=mysqli_fetch_assoc($dbcon->query($query));
					if(!empty($rel))
					{
						echo "-1";
						exit;
					}
					$info['cmp_unique_id']		= $POST['cmp_unique_id'];
					$infousr['user_name'] = $infousr['user_company'] =	$info['company_name']= $POST['company_name'];
					$infousr['user_address'] = $info['address']	= stripcslashes($POST['address']);
					$infousr['user_mail'] = $_POST['username'];
					$infousr['user_key'] = md5($_POST['password']);
					$infousr['user_type'] = "2";
					$info['bank_name']	= $POST['bank_name'];
					$info['ac_no']		= $POST['ac_no'];
					$info['ifcs']		= $POST['ifcs'];
					$info['branch_name']= $POST['branch_name'];
					$info['currency_id']  = $POST['currency_id']; 
					$info['pan_no']		= $POST['pan_no'];
						if(!empty($_FILES['logo']['tmp_name'])) {
							$info['logo']	= upload_image($_FILES);
						}
						/*if(!empty($_FILES['f_logo']['tmp_name'])) {
						$q="select * from tbl_setting where id=".$POST['eid'];
						$row=mysqli_fetch_assoc($dbcon->query($q));
						$file=$row['f_logo'];
						unlink(LOGO_A.$file);
						unlink(LOGO_A."thumb//".$file);
						$info['f_logo']	= upload_image1($_FILES);
						}*/
						$info['cdate']		= date("Y-m-d H:i:s");
						$info['user_id']	= $_SESSION['user_id'];			
					$inserid=add_record('tbl_company', $info, $dbcon);
					$infousr['user_company'] 	= $POST['company_name']; 
					$infousr['user_rid']  	= $inserid;
					$infousr['company_id']  = $inserid;
					$infousr['question_id']= $POST['question_id'];
					$infousr['answer']= $POST['give_answer'];

					
					$insertuserid=add_record('users', $infousr, $dbcon);
					if($inserid){
						
			 			echo "1";
					}
					else{
						echo "0";
					}
  for($k=0;$k<=4;$k++){
	  if($k==0){
		$invoice_type="TAX INVOICE";
	$format_value="";  
	$type_id="37";
	  }else if($k==1){
	$invoice_type="RETAIL INVOICE";
	$format_value="";
	$type_id="37";
	  }
	  else if($k==2){
	$invoice_type="PURCHASE ORDER";
	$format_value="";
	$type_id="2";
	  }else if($k==3){
	$invoice_type="QUOTATION";
	$format_value="";
	$type_id="3";
	  }else if($k==4){
	$invoice_type="EXPORT INVOICE";
	$format_value="Exp";
	$type_id="37";
	  }
	$infousr1['invoice_type'] 	= $invoice_type;
	$infousr1['taxinvoice_start'] 	= "0";
	$infousr1['invoice_format'] 	= "3";
	$infousr1['type_id'] 	= $type_id;
	$infousr1['format_value'] 	= $format_value;
	$infousr1['end_format_value'] 	= "/18-19";
	$infousr1['deletable'] 	= "1";
	$infousr1['status'] 	= "0";
	$infousr1['cdate'] 	=date('Y-m-d H:i:s');
	$infousr1['user_id'] 	= $insertuserid;
	$infousr1['usertype_id'] 	= "2";
	$infousr1['company_id'] 	= $inserid;
	$insertuserid1=add_record('tbl_invoicetype',$infousr1, $dbcon);
 // var_dump($insertuserid1);
}
for($p=0;$p<=11;$p++){
   if($p==0){
	   $taxname="CGST 2.5%";
	    $taxrate="2.5";
		$formulacreate="3";
   }else if($p==1){
	   $taxname="SGST 2.5%";
	   $taxrate="2.5";
	   $formulacreate="1";
	   $formulaname="CGST 2.5%*SGST 2.5%";
   }else if($p==2){
	   $taxname="IGST 5%";
	   $taxrate="5";
	    $formulacreate="2";
		
   }else if($p==3){
	   $taxname="CGST 6%";
	   $taxrate="6";
	   $formulacreate="3";
   }else if($p==4){
	   $taxname="SGST 6%";
	   $taxrate="6";
	    $formulacreate="1";
		$formulaname="CGST 6%*SGST 6%";
   }else if($p==5){
	   $taxname="IGST 12%";
	   $taxrate="12";
	    $formulacreate="2";
	}else if($p==6){
	   $taxname="CGST 9%";
	   $taxrate="9";
	   $formulacreate="3"; 
   }else if($p==7){
	   $taxname="SGST 9%";
	   $taxrate="9";
	    $formulacreate="1";
		$formulaname="CGST 9%*SGST 9%";
   }else if($p==8){
	   $taxname="IGST 18%";
	   $taxrate="18";
	    $formulacreate="2";
   }else if($p==9){
	   $taxname="CGST 14%";
	    $taxrate="14";
		$formulacreate="3"; 
   }else if($p==10){
	   $taxname="SGST 14%";
	    $taxrate="14";
		 $formulacreate="1";
		 $formulaname="CGST 14%*SGST 14%";
   }else if($p==11){
	   $taxname="IGST 28%";
	    $taxrate="28";
		 $formulacreate="2";
   }
   $infotax['tax_name'] 	= $taxname;
   $infotax['tax_value'] 	= $taxrate;
   $infotax['cdate'] 	= date('Y-m-d H:i:s');
   $infotax['tax_status'] 	= "0";
   $infotax['user_id'] 	= $insertuserid;
   $infotax['usertype_id'] 	= "2";
   $infotax['company_id'] 	= $inserid;
   $inserttax=add_record('tbl_tax',$infotax, $dbcon);
   if($formulacreate=="1"){
	   $inserttax2=($inserttax-1);
	   $infoformu['formula_name'] 	= $formulaname;
	    $infoformu['tax_id'] 	= ($inserttax.",".$inserttax2);
		 $infoformu['formula_status'] 	= "0";
		  $infoformu['cdate'] 	= date('Y-m-d H:i:s');
		   $infoformu['user_id'] 	= $insertuserid;
		    $infoformu['usertype_id'] 	= "2";
			 $infoformu['company_id'] 	= $inserid;
    $insertformula=add_record('formula_mst',$infoformu, $dbcon);
   }
   if($formulacreate=="2"){
	    $infoformu['formula_name'] 	= $taxname;
	    $infoformu['tax_id'] 	= $inserttax;
		 $infoformu['formula_status'] 	= "0";
		  $infoformu['cdate'] 	= date('Y-m-d H:i:s');
		   $infoformu['user_id'] 	= $insertuserid;
		    $infoformu['usertype_id'] 	= "2";
			 $infoformu['company_id'] 	= $inserid;
	   $insertformula=add_record('formula_mst',$infoformu, $dbcon);
   }
}
					
		}		
		else if(strtolower($POST['mode']) == "edit") {
						
						$infousr['user_name'] = $infousr['user_company'] =	$info['company_name']= $POST['company_name'];
						$infousr['user_address'] = $info['address']	= stripcslashes($POST['address']);
						if(!empty($_POST['password']))
						{
							$infousr['user_key'] = md5($_POST['password']);
						}
						if(!empty($_POST['username']))
						{
							$infousr['user_mail'] = $_POST['username'];
						}
							$info['cmp_unique_id']		= $POST['cmp_unique_id'];
							$info['bank_name']	= $POST['bank_name'];
							$info['ac_no']		= $POST['ac_no'];
							$info['ifcs']		= $POST['ifcs'];
							$info['branch_name']= $POST['branch_name'];
							$info['pan_no']		= $POST['pan_no'];
							if(!empty($_FILES['logo']['tmp_name'])) {
							$q="select * from tbl_company where company_id=".$POST['eid'];
							$row=mysqli_fetch_assoc($dbcon->query($q));
							$file=$row['logo'];
							unlink(LOGO_A.$file);
							unlink(LOGO_A."thumb//".$file);
							$info['logo']	= upload_image($_FILES);
							}
							/*if(!empty($_FILES['f_logo']['tmp_name'])) {
							$q="select * from tbl_setting where id=".$POST['eid'];
							$row=mysqli_fetch_assoc($dbcon->query($q));
							$file=$row['f_logo'];
							unlink(LOGO_A.$file);
							unlink(LOGO_A."thumb//".$file);
							$info['f_logo']	= upload_image1($_FILES);
							}*/
							$info['cdate']		= date("Y-m-d H:i:s");
							$info['user_id']	= $_SESSION['user_id'];	
							$info['currency_id']  = $POST['currency_id']; 		
							$updateid=update_record('tbl_company', $info,"company_id=".$POST['eid'] , $dbcon);
							//	$infousr['user_rid']  = $inserid;
						$infousr['question_id']= $POST['question_id'];
						$infousr['answer']= $POST['give_answer'];
						$updateid1=update_record('users', $infousr,"employee_id = 0 AND company_id=".$POST['eid'] , $dbcon);
					
				
				if($updateid || $updateid1)
					echo "update";
				else
					echo "0".$dbcon->error;
			
			
		}
		else if(strtolower($POST['mode']) == "delete") {
				$q="select * from tbl_company where company_id=".$POST['eid'];
				$row=mysqli_fetch_assoc($dbcon->query($q));
				$file=$row['logo'];
				unlink(LOGO_A.$file);
				unlink(LOGO_A."thumb//".$file);
				$info['com_status'] =2;		
				$updateid=update_record('tbl_company', $info,"company_id=".$POST['eid'] , $dbcon);
				$info1['active'] =2;		
				
				$updateid=update_record('users', $info1,"user_rid=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
		}
		else if(strtolower($POST['mode']) == "forgot_pass") {
						$question = $_POST['question'];
						$answer = $_POST['answer'];
						$pwd = stripslashes($answer);
						$question = $dbcon->real_escape_string($question);
						$answer	= $dbcon->real_escape_string($answer);
						$question = md5($question);
						$answer = md5($answer);

				   $sql = "SELECT `user_id`, `user_name`, `question_id`,`user_type`, `user_phone`, `user_company`, `user_country`,`user_stat`,  `user_rid`, `user_tmst`, `user_date`, `setup`, `payment_status`,datediff (CURDATE(),user_tmst) as datedif,print_align,`company_id` FROM `users` WHERE `question_id` = '$question' AND `answer` = '$answer' and user_type=".$_POST['usertype']." and company_id=".$_POST['companyid'];
					$result=$dbcon->query($sql);
						if(!$result = $dbcon->query($sql)){
						$arr['msg']='-1';
					}
					 if($result->num_rows==1)
					{
							$row = $result->fetch_assoc();
						
						 	$_SESSION['current_location'] = "";//"{$geoplugin->city}";
							$_SESSION['LOGGED_IN'] = true;
							$_SESSION['title'] = TITLE;
							$_SESSION['domain'] = DOMAIN;
							$_SESSION['user_id'] = $row['user_id'];
							$_SESSION['company_id'] = $row['company_id'];
							$_SESSION['company_name'] = $row['user_name'];
							$_SESSION['user_name'] = ucwords(strtolower($row['user_name']));
							$_SESSION['user_type'] = $row['user_type'];
							$_SESSION['user_company'] = $row['user_company']; 
							$arr['user_id']= $row['user_id'];
							$arr['msg']='1'; //login done
							
					}
					else
					{
						$arr['msg'] ='-1';
					}
					echo json_encode($arr);
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
							smart_resize_image(LOGO_A.$File,792,201);
							
							
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
							smart_resize_image(LOGO_A.$File,600,130);
						}
				}
		return  $File;				
	}
	
}

?>
