<?php
session_start(); //start session
$AJAX = true;
include("../get_ind_data.php");

include('../../include/urlfileinner.php');
$incPath = $path.'include/';
include($incPath.'common_send_email.php');

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for india mart data module
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	INDIA_MART_DATA_SLUG_EDIT_INQUIRY,
	        INDIA_MART_DATA_SLUG_DELETE_INQUIRY
	    ]);

		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where='';
		$where.="  and DATE_RE >= '".date('Y-m-d',strtotime($s_date[0]))."' AND DATE_RE <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
	/* 	if($POST['child_usr_id']){
			$where.=" and inq.user_id=".$POST['child_usr_id'];
		}
		else{
			$where.=check_user('inq');
		} */
		
		if(!empty($POST['source_id'])){
			$where.= "and source_id=".$POST['source_id'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('inq.i_id','ref.rb_name','inq.QUERY_ID','inq.DATE_RE','inq.SENDERNAME','inq.DATE_TIME_RE','inq.PRODUCT_NAME','inq.GLUSR_USR_COMPANYNAME','inq.ENQ_CITY','inq.MOBILE_ALT','inq.EMAIL_ALT','inq.SENDEREMAIL','inq.ENQ_STATE','inq.SENDERNAME','inq.MOB','inq.MOBILE_ALT','inq.ENQ_MESSAGE','inq.i_status','inq.inquiry_id','pro.product_name as proname','sta.state_name','city.city_name','inq.user_ids');
		$sIndexColumn = "inq.i_id";
		$isWhere = array("inq.i_status = 0 and inq.company_id IN (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_indiamart_data as inq";
		$isJOIN = array('left join product_mst as pro on pro.product_id=inq.product_id','left join state_mst as sta on sta.stateid=inq.stateid','left join city_mst as city on city.cityid=inq.cityid','left join tbl_refer_by as ref on ref.rb_id=inq.source_id');
		$hOrder = "inq.DATE_TIME_RE desc";
		include($incPath.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['rb_name'];
			$row_data[] = $row['QUERY_ID'];
			$row_data[] = date('d M, Y',strtotime($row['DATE_TIME_RE']));
			$row_data[] = date('h:m:i',strtotime($row['DATE_TIME_RE']));
			$row_data[] = $row['SENDERNAME'];
			if(!empty($row['proname'])){
				$row_data[] = $row['proname'];
				$pro=1;
			}else{
				$row_data[] = $row['PRODUCT_NAME']."<br/> <span style='color:red'>Not Add</span>";
				$pro=0;
			}
			if(!empty($row['GLUSR_USR_COMPANYNAME'])){
				$row_data[] = $row['GLUSR_USR_COMPANYNAME'];
			}else{
				$row_data[] = $row['SENDERNAME'];
			}
			if(!empty($row['city_name'])){
				$row_data[] = $row['city_name'];
				$cit=1;
			}else{
				$row_data[] = $row['ENQ_CITY']."<br/> <span style='color:red'>Not Add</span>";
				$cit=0;
			}
			if(!empty($row['state_name'])){
				$row_data[] = $row['state_name'];
				$sta=1;
			}else{
				$row_data[] = $row['ENQ_STATE']."<br/> <span style='color:red'>Not Add</span>";
				$sta=0;
			}
			
			if(!empty($row['MOB'])){
				$row_data[] = $row['MOB'];
			}else{
				$row_data[] = $row['MOBILE_ALT'];
			}
			if(!empty($row['SENDEREMAIL'])){
				$row_data[] = $row['SENDEREMAIL'];
			}else{
				$row_data[] = $row['EMAIL_ALT'];
			}
			if(!empty($row['user_ids'])){
				$query="select GROUP_CONCAT(user_name
        SEPARATOR '<br/>- ') as user from users as trn 
						where trn.active=0 and trn.user_id in (".$row['user_ids'].")";
				$result=$dbcon->query($query);
				$rel=mysqli_fetch_assoc($result);
				$row_data[] = $rel['user'];
				$all=1;
			}else{
				$row_data[] = "<span style='color:red'>Not Allocate</span>";
				$all=0;
			}
			
			$per=$sta+$cit+$pro+$all;
			$row_data[] = $row['ENQ_MESSAGE'];
			
			
			$edit='';$delete='';$view_hist_btn='';$send_email='';
			if(in_array(INDIA_MART_DATA_SLUG_EDIT_INQUIRY,$bulkAccessArray)){
				$view_hist_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit " data-toggle="tooltip" data-placement="top" onClick="open_update('.$row['i_id'].')"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(INDIA_MART_DATA_SLUG_DELETE_INQUIRY,$bulkAccessArray)){
				$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inquiry('.$row['i_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			if($per==4){
				$printcheckbox='<input type="checkbox" class="form-control" style="width:26px;" id="allchk'.$row["i_id"].'" name="chk" value="'.$row["i_id"].'">'; 
			}else{
				$printcheckbox='';
			}
			
			
			//$view_inq='<a class="btn btn-xs btn-primary" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top" href="'.ROOT.'inquiry_view/'.$row['i_id'].'"><i class="fa fa-eye"></i></a>';
			//$send_email='<button class="btn btn-xs btn-primary" data-original-title="Send Email" data-toggle="tooltip" data-placement="top" onClick="open_inq_email('.$row['inquiry_id'].','.$row['cust_id'].')"><i class="fa fa-envelope"></i></button>'; 
			
			if($row['inquiry_id']==0){
				$row_data[] = $printcheckbox.' '.$view_hist_btn.' '.$delete;
			}else{
				$row_data[] ='<button class="btn btn-info" data-original-title="Done " data-toggle="tooltip" data-placement="top">Inquiry Done</button>';
			}
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$info_cust['SENDERNAME']			= $POST['SENDERNAME'];
		$info_cust['SENDEREMAIL']			= $POST['SENDEREMAIL'];
		$info_cust['GLUSR_USR_COMPANYNAME']	= $POST['GLUSR_USR_COMPANYNAME'];
		$info_cust['MOB']					= $POST['MOB'];
		$info_cust['ENQ_ADDRESS']			= $POST['ENQ_ADDRESS'];
		$info_cust['ENQ_CITY']				= $POST['ENQ_CITY'];
		$info_cust['ENQ_STATE']				= $POST['ENQ_STATE'];
		$info_cust['PRODUCT_NAME']			= $POST['PRODUCT_NAME'];
		$info_cust['ENQ_MESSAGE']			= $_POST['ENQ_MESSAGE'];
		$info_cust['cat_id']				= $POST['cat_id'];
		$info_cust['product_id']			= $POST['product_id'];
		$info_cust['parent_cat_id']			= $POST['parent_cat_id'];
		$info_cust['stateid']				= $POST['stateid'];
		$info_cust['cityid']				= $POST['cityid'];
		$info_cust['branch_id']				= $POST['branch_id'];
		$info_cust['company_id']			= $_SESSION['company_id'];
		$info_cust['cdate']					= date("Y-m-d H:i:s");
		$info_cust['user_ids']				= $POST['assign_user_ids']; 
		$info_cust['cust_owner']			= $POST['cust_owner']; 
		//implode(",",array_filter($POST['assign_user_ids']));

        // Amish Soni Start 19-01-2021
        $crm_auto_mail = '';
        $companySettings = getCompanySettings($dbcon);
        if($companySettings) {
            $crm_auto_mail = $companySettings['crm_auto_mail'];
        }
        $showTemplate = ($crm_auto_mail == 'No');

        if($showTemplate) {
            $info_cust['email_template_id'] = (isset($POST['email_template_id']) && $POST['email_template_id'])
                ? $POST['email_template_id'] : null;
        }
        // Amish Soni End 19-01-2021
		
		//var_dump($info_cust);
		$updateid=update_record('tbl_indiamart_data', $info_cust, "i_id=".$POST['i_id'], $dbcon);
		
		if($updateid){	
			$arr['msg']="1";
		}
		else{
			$arr['msg']=0;
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['i_status']	= 2;
		$updateid=update_record('tbl_indiamart_data', $info, "i_id=".$POST['i_id'], $dbcon);
		
		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "load_indiamart"){
		$s_date=explode(' - ',$POST['date']);
// 		if($POST['type']=="1"){
// 			$sdate=date('d-M-YH:i:s',strtotime($s_date[0]));
// 			$ldate=date('d-M-YH:i:s',strtotime($s_date[1]));
// 		}else{
// 		}
			$ldate=date('d-M-Y23:59:59',strtotime($s_date[1]));
			//$ldate=date("d-m-Y");
			$sdate = date('d-M-Y00:00:00', strtotime($ldate . " - 6 day"));
			//var_dump("12");

			$info1111['i_status']=2;	
			$updateid=update_record('tbl_indiamart_data', $info1111, "QUERY_ID=0 || QUERY_ID=''" , $dbcon);
			
		$query="select * from tbl_indiamart_api where i_status=0 and company_id = '".$_SESSION['company_id']."'";
		$result=$dbcon->query($query);
		while($rel=mysqli_fetch_assoc($result))
		{
			$api_key=$rel['api_key'];
			$mob_no=$rel['mobile_no'];
			$sms_resp = get_indiamart_data($dbcon,$sdate,$ldate,$api_key,$mob_no);
			$data2=json_decode($sms_resp, true);
			// var_dump($sms_resp);
			// var_dump($data2);
			$data1 = $data2['RESPONSE'];
			$i=0;
			foreach ($data1 as $key => $name) 
			{	
				if(!empty($data1[$i]['SENDER_NAME']) && $data1[$i]['SENDER_NAME']!="null"){
					$vali=1;
				}else{
					$vali=0;
				}
				//if($vali==1){			
					$query_inq="select * from tbl_indiamart_data where QUERY_ID='".$data1[$i]['UNIQUE_QUERY_ID']."'";
					$result_inq=$dbcon->query($query_inq);
					$rel_inq=mysqli_fetch_assoc($result_inq);
					
					if($rel_inq['QUERY_ID']=="")
					{
						$info_cust['source_id']				= $rel['source_id'];
						$info_cust['api_key']				= $api_key;
						$info_cust['mobile_no']				= $mob_no;
						$info_cust['RN']				= $i+1;
						$info_cust['QUERY_ID']				= $data1[$i]['UNIQUE_QUERY_ID'];
						$info_cust['QTYPE']				= $data1[$i]['QUERY_TYPE'];
						$info_cust['SENDERNAME']			= $data1[$i]['SENDER_NAME'];
						
						if(!empty($data1[$i]['SENDER_EMAIL'])){
							$info_cust['SENDEREMAIL']	= $data1[$i]['SENDER_EMAIL'];
						}else{
							$info_cust['SENDEREMAIL']	= $data1[$i]['SENDER_EMAIL_ALT'];
						}
						$info_cust['SUBJECT']				= $data1[$i]['SUBJECT'];
						$info_cust['DATE_RE']				= date('Y-m-d',strtotime($data1[$i]['QUERY_TIME']));
						$info_cust['DATE_R']				= $data1[$i]['QUERY_TIME'];
						$info_cust['DATE_TIME_RE']			= $data1[$i]['QUERY_TIME'];
						$info_cust['DATE_TIME_CURRENT_UPDATE']          = date('Y-m-d H:i:s', strtotime($info_cust['QUERY_TIME']));
						
				 		if(!empty($data1[$i]['SENDER_COMPANY'])){
				 			$info_cust['GLUSR_USR_COMPANYNAME']	= $data1[$i]['SENDER_COMPANY'];
				 		}else{
				 			$info_cust['GLUSR_USR_COMPANYNAME']	= $data1[$i]['SENDERNAME'];
						}
				// 		$info_cust['READ_STATUS']			= $data1[$i]['READ_STATUS'];
				// 		$info_cust['SENDER_GLUSR_USR_ID']	= $data1[$i]['SENDER_GLUSR_USR_ID'];
						
						if(!empty($data1[$i]['SENDER_MOBILE'])){
							$info_cust['MOB']				= $data1[$i]['SENDER_MOBILE'];
						}else{
							$info_cust['MOB']				= $data1[$i]['SENDER_MOBILE_ALT'];
						}
				// 		$info_cust['COUNTRY_FLAG']			= $data1[$i]['COUNTRY_FLAG'];
				// 		$info_cust['QUERY_MODID']			= $data1[$i]['QUERY_MODID'];
				// 		$info_cust['LOG_TIME']				= $data1[$i]['LOG_TIME'];
				// 		$info_cust['QUERY_MODREFID']		= $data1[$i]['QUERY_MODREFID'];
				// 		$info_cust['DIR_QUERY_MODREF_TYPE']	= $data1[$i]['DIR_QUERY_MODREF_TYPE'];
				// 		$info_cust['ORG_SENDER_GLUSR_ID']	= $data1[$i]['ORG_SENDER_GLUSR_ID'];
						$info_cust['ENQ_MESSAGE']			= $data1[$i]['QUERY_MESSAGE'];
						$info_cust['ENQ_ADDRESS']			= $data1[$i]['SENDER_ADDRESS'];
						$info_cust['ENQ_CALL_DURATION']		= $data1[$i]['CALL_DURATION'];
						$info_cust['ENQ_RECEIVER_MOB']		= $data1[$i]['RECEIVER_MOBILE'];
						
						$info_cust['ENQ_STATE']				= $data1[$i]['SENDER_STATE'];
						$info_cust['ENQ_CITY']				= $data1[$i]['SENDER_CITY'];
							
						$info_cust['stateid']=0;
						$info_cust['cityid']=0;
						$info_cust['user_ids']=0;
						if(!empty($data1[$i]['ENQ_STATE'])){
							$query_inq1="select * from state_mst where LOWER(state_name)=LOWER('".$data1[$i]['SENDER_STATE']."')";
							$result_inq1=$dbcon->query($query_inq1);
							$rel_inq1=mysqli_fetch_assoc($result_inq1);
							if(!empty($rel_inq1['stateid'])){
								$info_cust['stateid']			= $rel_inq1['stateid'];	
								
								if(!empty($data1[$i]['ENQ_CITY'])){
									$query_inq2="select * from city_mst where LOWER(city_name)=LOWER('".$data1[$i]['SENDER_CITY']."')";
									$result_inq2=$dbcon->query($query_inq2);
									$rel_inq2=mysqli_fetch_assoc($result_inq2);
									if(!empty($rel_inq2['cityid'])){
										$info_cust['cityid']		= $rel_inq2['cityid'];
									}
								}
								$c_state=$info_cust['stateid'];
								$c_city=$info_cust['cityid'];
								
								
								//get Users by city
								$user_qry="select group_concat(user_id) as ter_users from users where active=0 and user_type in(8,9) and find_in_set('".$c_city."',alloc_cityid)";
								$user_rel=mysqli_fetch_assoc($dbcon->query($user_qry));
								$user_ids=$user_rel['ter_users'];
								
								if(!$user_ids){//get Users by State if not found by city
									$user_qry="select group_concat(user_id) as ter_users from users where active=0 and user_type in(8,9) and find_in_set('".$c_state."',alloc_stateid)";
									$user_rel=mysqli_fetch_assoc($dbcon->query($user_qry));
									$user_ids=$user_rel['ter_users'];
								}
								$info_cust['user_ids']			= $_SESSION['user_id'];
							}
						}
						
						
						$info_cust['PRODUCT_NAME']			= $data1[$i]['QUERY_PRODUCT_NAME'];
						$rel_inq3['product_id']="";
						$query_inq3="select * from product_mst where product_name='".$data1[$i]['QUERY_PRODUCT_NAME']."'";
						$result_inq3=$dbcon->query($query_inq3);
						$rel_inq3=mysqli_fetch_assoc($result_inq3);
						
						if(!empty($rel_inq3['product_id'])){
							$info_cust['product_id']		= $rel_inq3['product_id'];
						}else{
							$info_cust['product_id']		= 0;
						}


						$rel_inq3['product_id']="";
						$query_inq3="select * from product_mst where product_name='".$data1[$i]['QUERY_PRODUCT_NAME']."'";
						$result_inq3=$dbcon->query($query_inq3);
						$rel_inq3=mysqli_fetch_assoc($result_inq3);



						
						$info_cust['COUNTRY_ISO']			= $data1[$i]['SENDER_COUNTRY_ISO'];
						$info_cust['EMAIL_ALT']				= $data1[$i]['SENDER_EMAIL_ALT'];
						$info_cust['MOBILE_ALT']			= $data1[$i]['SENDER_MOBILE_ALT'];
						$info_cust['PHONE']					= $data1[$i]['SENDER_MOBILE'];
						$info_cust['PHONE_ALT']				= $data1[$i]['SENDER_MOBILE_ALT'];
				// 		$info_cust['IM_MEMBER_SINCE']		= $data1[$i]['IM_MEMBER_SINCE'];
						$info_cust['TOTAL_COUNT']			= $data2['TOTAL_RECORDS'];
						$info_cust['company_id']			= $_SESSION['company_id'];
						$info_cust['cdate']			= date("Y-m-d h:i:s");
						
						$ins_inquiry_id=add_record('tbl_indiamart_data', $info_cust, $dbcon);
					}
				//}
				$i++;
			}
		}
	}else if(strtolower($POST['mode']) == "preedit"){
		$query="select * from tbl_indiamart_data where i_id=".$POST['i_id'];
		$result=$dbcon->query($query);
		$rel=mysqli_fetch_assoc($result);
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$crm_user_type=$companyConfiguration['crm_user_type'];
		$enable_assing_user=$companyConfiguration['enable_assing_user'];

		$rel['enable_assing_user'] = $enable_assing_user;
		$query1="select * from product_mst where product_status=0 and product_name='".$rel['PRODUCT_NAME']."'";
		$result1=$dbcon->query($query1);
		$rel1=mysqli_fetch_assoc($result1);
		if($rel1['product_id']!=""){
			$rel['prostatus']=1;
		}else{
			$rel['prostatus']=0;
		}
		echo json_encode($rel);
	}
	else if(strtolower($POST['mode']) == "print_cust_label") {
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$crm_user_type=$companyConfiguration['crm_user_type'];
	$enable_assing_user=$companyConfiguration['enable_assing_user'];		
	//echo '<pre>';print_r($POST); exit;
	$s=implode(",",$POST['cust_id']);
	//var_dump($s);
	$query_main="select * from tbl_indiamart_data where i_id in (".$s.") and i_status=0";
	$result_main=$dbcon->query($query_main);
	$i=0;
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
	while($indiamart_data = mysqli_fetch_assoc($result_main)){
		// check if inquiry already exist with indiamart_id
		$query_inq="select * from tbl_inquiry where indiamart_id='".$indiamart_data['QUERY_ID']."' and inquiry_status=0";
		$result_inq=$dbcon->query($query_inq);
							
		if(!empty($indiamart_data['GLUSR_USR_COMPANYNAME']) && $indiamart_data['GLUSR_USR_COMPANYNAME']!="null"){
				$vali=1;
		}else if(!empty($indiamart_data['SENDERNAME']) && $indiamart_data['SENDERNAME']!="null"){
				$vali=1;
		}else{
				$vali=0;
		}

		if($vali=="1"){			
			$query_inq="select * from tbl_inquiry where indiamart_id='".$indiamart_data['QUERY_ID']."' and inquiry_status=0";
			$result_inq=$dbcon->query($query_inq);
			$rel_inq=mysqli_fetch_assoc($result_inq);
		
			if($rel_inq['inquiry_id']=="")
			{
				$asing_user=explode(",",$indiamart_data['user_ids']);
				$show_user_ids			=show_user_ids($dbcon,$asing_user);
			
				$query="select * from tbl_customer where cust_name='".$indiamart_data['GLUSR_USR_COMPANYNAME']."' and cust_status=0";
				$result=$dbcon->query($query);
				$rel=mysqli_fetch_assoc($result);
				$rel['cust_id']="";
				//indiamart to inquiry load time new cust entry evry inquiry time
				//hardi code  10-02-2022 
				if($rel['cust_id']=="")
				{
					$info_cust['party_type']	= 0;
					if(!empty($indiamart_data['GLUSR_USR_COMPANYNAME']) && $indiamart_data['GLUSR_USR_COMPANYNAME']!="null"){
						$info_cust['cust_name']	= stripslashes($indiamart_data['GLUSR_USR_COMPANYNAME']);
					}else{
						$info_cust['cust_name']	= stripslashes($indiamart_data['SENDERNAME']);
					}
					$info_cust['cust_creator']	= $_SESSION['user_id'];
					//$info_cust['cust_code']		= $POST['cust_code'];//Generate New Code
					//$info_cust['cust_code_series']= $POST['cust_code_series'];//Generate New Code
					//pathik code commit -- 17-05-2023
					/*$info_cust['cust_cat']		= 1;
					$info_cust['cust_desc']		= 1;
					$info_cust['cust_ind']		= 1;
					$info_cust['cust_type']		= 1;*/
					//pathik code commit -- 17-05-2023
					$info_cust['cust_source']	= $indiamart_data['source_id'];
					//$info_cust['cust_gst']		= $POST['cust_gst'];
					$info_cust['cust_mobile']	= $indiamart_data['MOB'];
					$info_cust['cust_email']	= strtolower($indiamart_data['SENDEREMAIL']);
					//$info_cust['cust_assign_user']= implode(",",$POST['cust_assign_user']);
					
					if($enable_assing_user==1){
						$info_cust['cust_assign_user']= check_crm_find_in_set_new($dbcon,$indiamart_data['cust_owner'],1);
						$info_cust['cust_owner']		= $indiamart_data['cust_owner'];
					}else{
						$info_cust['cust_assign_user']= check_crm_find_in_set_new($dbcon,$indiamart_data['cust_owner'],1);
						$info_cust['cust_owner']		= $indiamart_data['cust_owner'];
					}
					
					$info_cust['cdate']			= date("Y-m-d H:i:s");
					$info_cust['user_id']		= $_SESSION['user_id'];
					$info_cust['company_id']	= $_SESSION['company_id'];
					
					/* Code BY Umair: 06/07/2021
                        Comment: Fetch branch id od users
                        START
                     */
                    $users_qr="select * from users where user_id='".$indiamart_data['user_ids']."'";
                    $user_result=$dbcon->query($users_qr);
                    $user_rel=mysqli_fetch_assoc($user_result);
                    $info_cust['branch_id']     = $user_rel['branch_id'];
                    /* END */

					$inserid_cust=add_record('tbl_customer', $info_cust, $dbcon);
					$cust_id=$inserid_cust;
					
					$infoadd['c_add_address']	= $indiamart_data['ENQ_ADDRESS'];
					//$infoadd['c_add_street']	= $POST['c_add_street'];
					$infoadd['c_add_country']	= 101;
					if($indiamart_data['stateid']==0){
						$query_st="select * from state_mst where state_name='".$indiamart_data['ENQ_STATE']."' and state_status=0";
						$result_st=$dbcon->query($query_st);
						$rel_st=mysqli_fetch_assoc($result_st);
						if($rel_st['stateid']==""){
							$info_st['state_name']		= $indiamart_data['ENQ_STATE'];
							$info_st['countryid']		= 101;
							$info_st['cdate']			= date("Y-m-d H:i:s");
							$info_st['user_id']			= $_SESSION['user_id'];
							$info_st['usertype_id']		= $_SESSION['user_type'];
							$inserid_st=add_record('state_mst', $info_st, $dbcon);
							$state_id=$inserid_st;
						}else{
							$state_id=$rel_st['stateid'];
						}
					}else{
						$state_id=$indiamart_data['stateid'];
					}
					$infoadd['c_add_state']		= $state_id;
					if($indiamart_data['cityid']==0){
						$query_ct="select * from city_mst where city_name='".$indiamart_data['ENQ_CITY']."' and city_status=0";
						$result_ct=$dbcon->query($query_ct);
						$rel_ct=mysqli_fetch_assoc($result_ct);
						if($rel_ct['cityid']==""){
							$info_ct['city_name']		= $indiamart_data['ENQ_CITY'];
							$info_ct['stateid']			= $state_id;
							$info_ct['cdate']			= date("Y-m-d H:i:s");
							$info_ct['userid']			= $_SESSION['user_id'];
							$info_ct['usertype_id']		= $_SESSION['user_type'];
							$inserid_ct=add_record('city_mst', $info_ct, $dbcon);
							$city_id=$inserid_ct;
						}else{
							$city_id=$rel_ct['cityid'];
						}
					}else{
						$city_id=$indiamart_data['cityid'];
					}
					
					$infoadd['c_add_city']		= $city_id;
					//$infoadd['c_add_zip']		= $POST['c_add_zip'];
					$infoadd['cust_id']			= $cust_id;
					$infoadd['c_addr_defult']	= 1;

					$infoadd['cdate']			= date("Y-m-d H:i:s");
					$infoadd['user_id']			= $_SESSION['user_id'];
					$infoadd['company_id']		= $_SESSION['company_id'];
					$inseraddid=add_record('tbl_cust_address',$infoadd,$dbcon, $info_cust['branch_id']);
					
					
					$infoper['c_con_fname']		=$indiamart_data['SENDERNAME'];
					//$infoper['c_con_lname']		=$POST['con_last'];
					$infoper['c_con_email']		=$indiamart_data['SENDEREMAIL'];
					$infoper['c_con_mobile']	=$indiamart_data['MOB'];
					$infoper['c_con_phone']		=$indiamart_data['PHONE'];
					//$infoper['c_con_job']		=$POST['con_job'];
					$infoper['cust_id']			=$cust_id;
					
					$infoper['cdate']			= date("Y-m-d H:i:s");
					$infoper['user_id']			= $_SESSION['user_id'];
					$infoper['company_id']		= $_SESSION['company_id'];
					
					$con_per=add_record('tbl_cust_contact',$infoper,$dbcon, $info_cust['branch_id']);
					
				} else{
					$cust_id=$rel['cust_id'];
				}
				if($indiamart_data['product_id']==0){
					$query_pro="select * from product_mst where product_name='".$indiamart_data['PRODUCT_NAME']."' and product_status=0";
					$result_pro=$dbcon->query($query_pro);
					$rel_pro=mysqli_fetch_assoc($result_pro);
					if($rel_pro['product_id']==""){
							$info_pro['product_type']	= 2;
							$info_pro['product_name'] 	= $indiamart_data['PRODUCT_NAME'];
							$info_pro['branch_id'] 	= $indiamart_data['branch_id'];
							$info_pro['product_gst'] 	= "excluding";
							$info_pro['cdate'] 	   	= date('Y-m-d');
							$info_pro['user_id'] 	= $_SESSION['user_id'];
							$info_pro['company_id'] 	= $_SESSION['company_id'];
							$inserid_pro=add_record('product_mst', $info_pro, $dbcon);
							$product_id=$inserid_pro;
							//$category_id=1;
					}else{
							$product_id=$rel_pro['product_id'];
							//$category_id=$rel_pro['category_id'];
					}
				}else{
						$product_id=$indiamart_data['product_id'];
				}
				$query = $dbcon -> query("select * from product_mst where product_id='".$product_id."' and product_status=0");
				$product_data = $query->fetch_assoc();
				$queryop = $dbcon -> query("select * from tbl_opportunity_mst where company_id='".$_SESSION['company_id']."' and opp_status=0 order by opp_priority limit 1");
				$opt_data = $queryop->fetch_assoc();
				//echo '<pre>';                                                print_r($product_data); exit;
				$info['show_user_ids']          = $show_user_ids;
				$info['indiamart_id']           = $indiamart_data['QUERY_ID'];
				$info['inquiry_no']             = $indiamart_data['QUERY_ID'];
				$info['inquiry_date']           = date('Y-m-d',strtotime($indiamart_data['DATE_TIME_RE']));
				$info['cust_id']		= $cust_id;
				$info['c_con_id']		= $con_per;
				if($companyConfiguration['inq_name_using_comapany']==1){
					$info['inquiry_name']           = $indiamart_data['PRODUCT_NAME'].'@'.$indiamart_data['GLUSR_USR_COMPANYNAME'];	
				}
				$info['closing_date']           = date('Y-m-d',strtotime($indiamart_data['DATE_TIME_RE']));
				$info['t_id']			= $POST['t_id'];
				$info['opp_id']			= $opt_data['opp_id'];
				$info['stage_prob']		= $opt_data['opp_probability'];
				$info['sales_stage_id']         = 4;
				$info['inquiry_type_id']        = 9;
				$info['rb_id']					= $indiamart_data['source_id'];
				$info['inquiry_cat_id']         = 10;
				$info['currency_id']            = $company_state['currency_id'];
				$info['currency_rate']            = 1;
				
				$info['g_total']		= $product_data['product_sale_rate'];
				$info['inq_desc']		= stripcslashes(text_rnremove($indiamart_data['ENQ_MESSAGE']));
				$info['inq_comp_desc']          = stripcslashes(text_rnremove($indiamart_data['ENQ_MESSAGE']));
				
				$info['create_date']    = date('Y-m-d H:i:s');
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['mdate']			= date("Y-m-d H:i:s");
                                $info['assign_user_inq_ids']    = $indiamart_data['user_ids'];
				$info['user_id']		= $indiamart_data['user_ids']; //$_SESSION['user_id'];
                                $info['owner_user_id']          = $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				
				$info['won_user_id']		=0;
				$info['inquiry_status']		=0;
				$info['t_id']			=0;

				$info['branch_id'] = $indiamart_data['branch_id'];
				/* Code By Umair: 21/06/2021
				 Fetch branch id based on the the user*/
				// $users_query="select branch_id from users where user_id='".$indiamart_data['user_ids']."' ";
				// $users_result=$dbcon->query($users_query);
				// while($users_rel=mysqli_fetch_assoc($users_result))
				// {
				// }
				// END

                // Amish Soni Start 19-01-2021
                $crm_auto_mail = '';
                $companySettings = getCompanySettings($dbcon);
                if($companySettings) {
                    $crm_auto_mail = $companySettings['crm_auto_mail'];
                }
                $showTemplate = ($crm_auto_mail == 'No');

                if($showTemplate) {
                    $info['email_template_id'] = (isset($indiamart_data['email_template_id']) && $indiamart_data['email_template_id'])
                        ? $indiamart_data['email_template_id'] : null;
                }
                // Amish Soni End 19-01-2021

				//var_dump($info);
				$ins_inquiry_id=add_record('tbl_inquiry', $info, $dbcon);
	
				$infotsk['show_user_ids']		= $show_user_ids;
				$infotsk['task_type_id']		= 16;
				$infotsk['task_rel_id']			= 5;//Fixed Type Inquiry
				$infotsk['inquiry_id']			= $ins_inquiry_id;
				//$infotsk['assign_user_ids']		= implode(",",array_filter($_SESSION['user_id']));
				//$infotsk['assign_user_ids']		= $_SESSION['user_id'];
				$infotsk['assign_user_ids']		= $indiamart_data['user_ids'];
				$infotsk['branch_id']		= $indiamart_data['branch_id'];
				//$infotsk['assign_user_ids']= implode(",",array_filter($indiamart_data['user_ids']));
				$infotsk['task_priority_id']            = 1;
				$infotsk['task_due_date']		= date('Y-m-d H:i:s',strtotime($indiamart_data['DATE_TIME_RE']));
				$infotsk['task_alert_id']		= 2;
				$infotsk['alert_date_time']		= date('Y-m-d H:i:s',strtotime($indiamart_data['DATE_TIME_RE']));
				
				$infotsk['task_remark']		= stripcslashes(text_rnremove($indiamart_data['ENQ_MESSAGE']));
				$infotsk['create_date']		= date('Y-m-d H:i:s');
				$infotsk['entry_type']		= 1;//Fixed Task Type
				$infotsk['cdate']			= date("Y-m-d H:i:s");
				$infotsk['user_id']			= $_SESSION['user_id'];
				$infotsk['company_id']		= $_SESSION['company_id'];
				$ins_task_id=add_record('tbl_task', $infotsk, $dbcon);
					
				$info1['product_id']	= $product_id;
				$info1['cat_id']		= $indiamart_data['cat_id'];
				$info1['rcat_id']		= $indiamart_data['parent_cat_id'];
				$info1['product_qty']	= 1;
				$info1['product_desc']  = $product_data['product_desc'];
				$info1['product_rate']	= $product_data['product_sale_rate'];
				$info1['product_rate_conv']	= $product_data['product_sale_rate'];
				$info1['product_amount']= $product_data['product_sale_rate'];
				$info1['product_amount_conv']= $product_data['product_sale_rate'];
				$info1['unitid']	= $product_data['product_base_unit'];
				$info1['user_id']	= $_SESSION['user_id'];
				$info1['company_id']	= $_SESSION['company_id'];
				$table='tbl_inquiry_trn';$tableid='inquiry_trn_id';
				//$info1['inqui_id']	= $_SESSION['user_id'];
				$info1['inquiry_id']	= $ins_inquiry_id;
				$info1['currency_rate']	= 1;
				
				$info1['currency_id']	= $company_state['currency_id'];
				$info1['inquiry_type']	= 1;
				$info1['branch_id'] = $indiamart_data['branch_id'];
				$inserid11=add_record($table, $info1, $dbcon);
				
				
				$info1111['inquiry_id']=$ins_inquiry_id;	
				$updateid=update_record('tbl_indiamart_data', $info1111, "i_id=".$indiamart_data['i_id'] , $dbcon);
				//echo $i;

				// Amish Soni Start 29-12-2020
				$module_id = 2; //CRM Module
                // Amish Soni Start 19-01-2021
                if($showTemplate) {
                    if(isset($indiamart_data['email_template_id']) && $indiamart_data['email_template_id']){
                        $mail_template = getEmailSMSTemplateById($dbcon, $indiamart_data['email_template_id']);
                    }
                } else {
                    $mail_template = getEmailSMSTemplate($dbcon, $module_id, 16, 5);
                }
                // Amish Soni End 19-01-2021

				$cur_user_id = $_SESSION['user_id'];
				$cur_user = getUserDetailById($dbcon, $cur_user_id);
				$customer = getCustDetailById($dbcon, $cust_id);
				$from_email_id = ($cur_user && $cur_user['user_email']) ? $cur_user['user_email'] : ADMIN_EMAIL;
				$to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';
				
				if($mail_template && $to_email_id) {
                    // Amish Soni Start 18-01-2021
					$subject = $mail_template['email_subject'];
					$content = $mail_template['email_content'];

                    $subject = replaceMergeFields($dbcon, $subject, $cust_id, $module_id);
                    $content = replaceMergeFields($dbcon, $content, $cust_id, $module_id);
                    // Amish Soni End 18-01-2021
					final_send_email($from_email_id, $to_email_id, '', '', $subject, $content);
				}
				// Amish Soni End 29-12-2020

				$i++;
			}
			else {
				return false;
			}
		}
	}
}
else if(strtolower($POST['mode']) == "load_product_cat"){
	//var_dump($POST['cat']);
	echo getproduct_typewise($dbcon,"","","",$POST['cat']);
}
else if(strtolower($POST['mode']) == "load_trade_india") 
	{
		$s_date=explode(' - ',$POST['date']);
		if($POST['type']=="1"){
			$sdate=date('Y-m-d',strtotime($s_date[0]));
			$ldate=date('Y-m-d',strtotime($s_date[1]));
		}else{
			$ldate=date('Y-m-d');
			//$ldate=date("d-m-Y");
		//	$sdate = date('Y-m-d', strtotime($ldate . " - 21 day"));
			$sdate = date('Y-m-d');
			//var_dump("12");
		}
		
		
			
		$query="select * from tbl_trade_india_api where i_status=0 and company_id='".$_SESSION['company_id']."'";
		$result=$dbcon->query($query);
		while($rel=mysqli_fetch_assoc($result))
		{
			$trade_india_user_id=$rel['trade_india_user_id'];
			$trade_india_profile_id=$rel['trade_india_profile_id'];
			$trad_india_api_key=$rel['trad_india_api_key'];
			//var_dump($trade_india_user_id);
			//var_dump($trade_india_profile_id);
			//var_dump($trad_india_api_key);
			//$sms_resp = get_indiamart_data($dbcon,$sdate,$ldate,$api_key,$mob_no);
			$sms_resp = get_trade_india_data($dbcon,$sdate,$ldate,$trad_india_api_key,$trade_india_user_id,$trade_india_profile_id);
			$data1=json_decode($sms_resp, true);
			//var_dump($data1);
			$i=0;
			foreach ($data1 as $key => $name) 
			{	
				//var_dump($data1[$i]);
				if(!empty($data1[$i]['GLUSR_USR_COMPANYNAME']) && $data1[$i]['GLUSR_USR_COMPANYNAME']!="null"){
					$vali=1;
				}else if(!empty($data1[$i]['SENDERNAME']) && $data1[$i]['SENDERNAME']!="null"){
					$vali=1;
				}else{
					$vali=0;
				}
				
						//print_r($data1d);
				//if($vali==1){			
					$query_inq="select * from tbl_indiamart_data where QUERY_ID='".$data1[$i]['lead_id']."'";
					$result_inq=$dbcon->query($query_inq);
					$rel_inq=mysqli_fetch_assoc($result_inq);
					
					if($rel_inq['QUERY_ID']=="")
					{
						$acp=$data1[$i]['contact_details'];
						//var_dump($data1d[1]['city']);
						$info_cust['source_id']				= $rel['source_id'];
						$info_cust['api_key']				= $trad_india_api_key;
						$info_cust['mobile_no']				= $trade_india_profile_id;
						$info_cust['RN']				= $data1[$i]['lead_id'];
						$info_cust['QUERY_ID']			= $data1[$i]['lead_id'];
						$info_cust['QTYPE']				= $data1[$i]['inquiry_type'];
						$info_cust['SENDERNAME']		= $data1[$i]['co_name'];
						$info_cust['SENDEREMAIL']	= $data1[$i]['sender_email'];
						
						$info_cust['SUBJECT']				= $data1[$i]['subject'];
						$info_cust['DATE_RE']				= date('Y-m-d',strtotime($data1[$i]['posted_on']));
						$info_cust['DATE_R']				= $data1[$i]['posted_on'];
						$info_cust['DATE_TIME_RE']			= $data1[$i]['posted_on'];
						$info_cust['DATE_TIME_CURRENT_UPDATE']          = date('Y-m-d H:i:s', strtotime($info_cust['posted_on']));
						$info_cust['GLUSR_USR_COMPANYNAME']	= $data1[$i]['co_name'];
						
						$info_cust['READ_STATUS']			= $data1[$i]['view_status'];
						$info_cust['SENDER_GLUSR_USR_ID']	= $data1[$i]['sender_uid'];
						$info_cust['MOB']				= $acp['contact_number'];
						
						$info_cust['LOG_TIME']				= $data1[$i]['generated_time'];
						$info_cust['ENQ_MESSAGE']			= $data1[$i]['description'];
						$info_cust['ENQ_ADDRESS']			= $acp['address'];
						$info_cust['ENQ_RECEIVER_MOB']		= $acp['contact_number'];
						
						$query_inq1="select * from state_mst where LOWER(state_name)=LOWER('".$acp['state']."')";
						$result_inq1=$dbcon->query($query_inq1);
						$rel_inq1=mysqli_fetch_assoc($result_inq1);
						$info_cust['ENQ_STATE']				= $acp['state'];
						$info_cust['ENQ_CITY']				= $acp['city'];
						if(!empty($rel_inq1['stateid'])){
							$info_cust['stateid']			= $rel_inq1['stateid'];	
							
							$query_inq2="select * from city_mst where LOWER(city_name)=LOWER('".$acp['city']."')";
							$result_inq2=$dbcon->query($query_inq2);
							$rel_inq2=mysqli_fetch_assoc($result_inq2);
							if(!empty($rel_inq2['cityid'])){
								$info_cust['cityid']		= $rel_inq2['cityid'];
							}
							
							$c_state=$info_cust['stateid'];
							$c_city=$info_cust['cityid'];
							
							
							//get Users by city
							$user_qry="select group_concat(user_id) as ter_users from users where active=0 and user_type in(8,9) and find_in_set('".$c_city."',alloc_cityid)";
							$user_rel=mysqli_fetch_assoc($dbcon->query($user_qry));
							$user_ids=$user_rel['ter_users'];
							
							if(!$user_ids){//get Users by State if not found by city
								$user_qry="select group_concat(user_id) as ter_users from users where active=0 and user_type in(8,9) and find_in_set('".$c_state."',alloc_stateid)";
								$user_rel=mysqli_fetch_assoc($dbcon->query($user_qry));
								$user_ids=$user_rel['ter_users'];
							}
							$info_cust['user_ids']			= $user_ids;
						}
						
						$info_cust['PRODUCT_NAME']			= $data1[$i]['product_name'];
						$rel_inq3['product_id']="";
						$query_inq3="select * from product_mst where product_name='".$data1[$i]['product_name']."'";
						$result_inq3=$dbcon->query($query_inq3);
						$rel_inq3=mysqli_fetch_assoc($result_inq3);
						
						if(!empty($rel_inq3['product_id'])){
							$info_cust['product_id']		= $rel_inq3['product_id'];
						}else{
							$info_cust['product_id']		= 0;
						}
						
						$info_cust['EMAIL_ALT']				= $data1[$i]['sender_email'];
						$info_cust['MOBILE_ALT']			= $data1[$i]['sender_mobile'];
						$info_cust['PHONE']					= $data1[$i]['sender_mobile'];
						$info_cust['PHONE_ALT']				= $data1[$i]['sender_mobile'];
						$info_cust['company_id']			= $_SESSION['company_id'];
						//var_dump($info_cust);
						$ins_inquiry_id=add_record('tbl_indiamart_data', $info_cust, $dbcon);
			
						
					}
				//}
				$i++;
			}
		}
	}
	


?>