<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//include_once("../common_send_email.php");

include("../get_ind_data.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "fetch") {
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
		
		$appData = array();
		$i=1;
		$aColumns = array('inq.i_id','inq.QUERY_ID','inq.DATE_RE','inq.PRODUCT_NAME','inq.GLUSR_USR_COMPANYNAME','inq.ENQ_CITY','inq.MOBILE_ALT','inq.EMAIL_ALT','inq.SENDEREMAIL','inq.ENQ_STATE','inq.SENDERNAME','inq.MOB','inq.MOBILE_ALT','inq.ENQ_MESSAGE','inq.i_status','inq.inquiry_id','pro.product_name as proname','sta.state_name','city.city_name','inq.user_ids');
		$sIndexColumn = "inq.i_id";
		$isWhere = array("inq.i_status = 0 ".$where);
		$sTable = "tbl_indiamart_data as inq";
		$isJOIN = array('left join product_mst as pro on pro.product_id=inq.product_id','left join state_mst as sta on sta.stateid=inq.stateid','left join city_mst as city on city.cityid=inq.cityid');
		$hOrder = "inq.DATE_RE desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['QUERY_ID'];
			$row_data[] = date('d M, Y',strtotime($row['DATE_RE']));
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
			if($edit_btn_per) {
				//$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'inquiry_ind/'.$row['i_id'].'"><i class="fa fa-pencil"></i></a>';
				$view_hist_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit " data-toggle="tooltip" data-placement="top" onClick="open_update('.$row['i_id'].')"><i class="fa fa-pencil"></i></button>';
			}
			if($delete_btn_per) {
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
		$info_cust['product_id']			= $POST['product_id'];
		$info_cust['stateid']				= $POST['stateid'];
		$info_cust['cityid']				= $POST['cityid'];
		$info_cust['cdate']					= date("Y-m-d H:i:s");
		$info_cust['user_ids']= $POST['assign_user_ids']; //implode(",",array_filter($POST['assign_user_ids']));
		
		
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
	else if(strtolower($POST['mode']) == "load_indiamart") 
	{
		$s_date=explode(' - ',$POST['date']);
		if($POST['type']=="1"){
			$ldate=date('d-M-Y',strtotime($s_date[0]));
			$sdate=date('d-M-Y',strtotime($s_date[1]));
		}else{
			$ldate=date("d-M-Y");
			//$ldate=date("d-m-Y");
			$sdate = date('d-M-Y', strtotime($ldate . " - 2 day"));
			//var_dump("12");
		}
		
		
			
		$query="select * from tbl_indiamart_api where i_status=0";
		$result=$dbcon->query($query);
		while($rel=mysqli_fetch_assoc($result))
		{
			$api_key=$rel['api_key'];
			$mob_no=$rel['mobile_no'];
			$sms_resp = get_indiamart_data($dbcon,$sdate,$ldate,$api_key,$mob_no);
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
				//if($vali==1){			
					$query_inq="select * from tbl_indiamart_data where QUERY_ID='".$data1[$i]['QUERY_ID']."'";
					$result_inq=$dbcon->query($query_inq);
					$rel_inq=mysqli_fetch_assoc($result_inq);
					
					if($rel_inq['QUERY_ID']=="")
					{
						$info_cust['api_key']				= $api_key;
						$info_cust['mobile_no']				= $mob_no;
						$info_cust['RN']					= $data1[$i]['RN'];
						$info_cust['QUERY_ID']				= $data1[$i]['QUERY_ID'];
						$info_cust['QTYPE']					= $data1[$i]['QTYPE'];
						$info_cust['SENDERNAME']			= $data1[$i]['SENDERNAME'];
						
						if(!empty($data1[$i]['SENDEREMAIL'])){
							$info_cust['SENDEREMAIL']	= $data1[$i]['SENDEREMAIL'];
						}else{
							$info_cust['SENDEREMAIL']	= $data1[$i]['EMAIL_ALT'];
						}
						$info_cust['SUBJECT']				= $data1[$i]['SUBJECT'];
						$info_cust['DATE_RE']				= date('Y-m-d',strtotime($data1[$i]['DATE_RE']));
						$info_cust['DATE_R']				= $data1[$i]['DATE_R'];
						$info_cust['DATE_TIME_RE']			= $data1[$i]['DATE_TIME_RE'];
						
						if(!empty($data1[$i]['GLUSR_USR_COMPANYNAME'])){
							$info_cust['GLUSR_USR_COMPANYNAME']	= $data1[$i]['GLUSR_USR_COMPANYNAME'];
						}else{
							$info_cust['GLUSR_USR_COMPANYNAME']	= $data1[$i]['SENDERNAME'];
						}
						$info_cust['READ_STATUS']			= $data1[$i]['READ_STATUS'];
						$info_cust['SENDER_GLUSR_USR_ID']	= $data1[$i]['SENDER_GLUSR_USR_ID'];
						
						if(!empty($data1[$i]['MOB'])){
							$info_cust['MOB']				= $data1[$i]['MOB'];
						}else{
							$info_cust['MOB']				= $data1[$i]['MOBILE_ALT'];
						}
						$info_cust['COUNTRY_FLAG']			= $data1[$i]['COUNTRY_FLAG'];
						$info_cust['QUERY_MODID']			= $data1[$i]['QUERY_MODID'];
						$info_cust['LOG_TIME']				= $data1[$i]['LOG_TIME'];
						$info_cust['QUERY_MODREFID']		= $data1[$i]['QUERY_MODREFID'];
						$info_cust['DIR_QUERY_MODREF_TYPE']	= $data1[$i]['DIR_QUERY_MODREF_TYPE'];
						$info_cust['ORG_SENDER_GLUSR_ID']	= $data1[$i]['ORG_SENDER_GLUSR_ID'];
						$info_cust['ENQ_MESSAGE']			= $data1[$i]['ENQ_MESSAGE'];
						$info_cust['ENQ_ADDRESS']			= $data1[$i]['ENQ_ADDRESS'];
						$info_cust['ENQ_CALL_DURATION']		= $data1[$i]['ENQ_CALL_DURATION'];
						$info_cust['ENQ_RECEIVER_MOB']		= $data1[$i]['ENQ_RECEIVER_MOB'];
						
						$query_inq1="select * from state_mst where state_name='".$data1[$i]['ENQ_STATE']."'";
						$result_inq1=$dbcon->query($query_inq1);
						$rel_inq1=mysqli_fetch_assoc($result_inq1);
						$info_cust['ENQ_STATE']				= $data1[$i]['ENQ_STATE'];
						$info_cust['ENQ_CITY']				= $data1[$i]['ENQ_CITY'];
						if(!empty($rel_inq1['stateid'])){
							$info_cust['stateid']			= $rel_inq1['stateid'];	
							
							$query_inq2="select * from city_mst where city_name='".$data1[$i]['ENQ_CITY']."'";
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
						
						$info_cust['PRODUCT_NAME']			= $data1[$i]['PRODUCT_NAME'];
						$rel_inq3['product_id']="";
						$query_inq3="select * from product_mst where product_name='".$data1[$i]['PRODUCT_NAME']."'";
						$result_inq3=$dbcon->query($query_inq3);
						$rel_inq3=mysqli_fetch_assoc($result_inq3);
						
						if(!empty($rel_inq3['product_id'])){
							$info_cust['product_id']		= $rel_inq3['product_id'];
						}else{
							$info_cust['product_id']		= 0;
						}
						
						$info_cust['COUNTRY_ISO']			= $data1[$i]['COUNTRY_ISO'];
						$info_cust['EMAIL_ALT']				= $data1[$i]['EMAIL_ALT'];
						$info_cust['MOBILE_ALT']			= $data1[$i]['MOBILE_ALT'];
						$info_cust['PHONE']					= $data1[$i]['PHONE'];
						$info_cust['PHONE_ALT']				= $data1[$i]['PHONE_ALT'];
						$info_cust['IM_MEMBER_SINCE']		= $data1[$i]['IM_MEMBER_SINCE'];
						$info_cust['TOTAL_COUNT']			= $data1[$i]['TOTAL_COUNT'];
						
						$ins_inquiry_id=add_record('tbl_indiamart_data', $info_cust, $dbcon);
			
						
					}
				//}
				$i++;
			}
		}
	}else if(strtolower($POST['mode']) == "preedit") 
	{
		$query="select * from tbl_indiamart_data where i_id=".$POST['i_id'];
		$result=$dbcon->query($query);
		$rel=mysqli_fetch_assoc($result);
		
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
			
			$s=implode(",",$POST['cust_id']);
			//var_dump($s);
			$query_main="select * from tbl_indiamart_data where i_id in (".$s.") and i_status=0";
			$result_main=$dbcon->query($query_main);
			$i=0;
			while($rel_main=mysqli_fetch_assoc($result_main)){
				if(!empty($rel_main['GLUSR_USR_COMPANYNAME']) && $rel_main['GLUSR_USR_COMPANYNAME']!="null"){
					$vali=1;
				}else if(!empty($rel_main['SENDERNAME']) && $rel_main['SENDERNAME']!="null"){
					$vali=1;
				}else{
					$vali=0;
				}
			
				if($vali=="1"){			
					$query_inq="select * from tbl_inquiry where indiamart_id='".$rel_main['QUERY_ID']."' and inquiry_status=0";
					$result_inq=$dbcon->query($query_inq);
					$rel_inq=mysqli_fetch_assoc($result_inq);
					
					if($rel_inq['inquiry_id']=="")
					{
						$asing_user=explode(",",$rel_main['user_ids']);
						$show_user_ids			=show_user_ids($dbcon,$asing_user);
					
						$query="select * from tbl_customer where cust_name='".$rel_main['GLUSR_USR_COMPANYNAME']."' and cust_status=0";
						$result=$dbcon->query($query);
						$rel=mysqli_fetch_assoc($result);
						if($rel['cust_id']=="")
						{
							$info_cust['party_type']	= 0;
							if(!empty($rel_main['GLUSR_USR_COMPANYNAME']) && $rel_main['GLUSR_USR_COMPANYNAME']!="null"){
								$info_cust['cust_name']	= stripslashes($rel_main['GLUSR_USR_COMPANYNAME']);
							}else{
								$info_cust['cust_name']	= stripslashes($rel_main['SENDERNAME']);
							}
							$info_cust['cust_creator']	= $_SESSION['user_id'];
							//$info_cust['cust_code']		= $POST['cust_code'];//Generate New Code
							//$info_cust['cust_code_series']= $POST['cust_code_series'];//Generate New Code
							$info_cust['cust_cat']		= 1;
							$info_cust['cust_desc']		= 1;
							$info_cust['cust_ind']		= 1;
							$info_cust['cust_type']		= 1;
							$info_cust['cust_source']	= 1;
							//$info_cust['cust_gst']		= $POST['cust_gst'];
							$info_cust['cust_mobile']	= $rel_main['MOB'];
							$info_cust['cust_email']	= strtolower($rel_main['SENDEREMAIL']);
							//$info_cust['cust_assign_user']= implode(",",$POST['cust_assign_user']);
							
							$info_cust['cdate']			= date("Y-m-d H:i:s");
							$info_cust['user_id']		= $_SESSION['user_id'];
							$info_cust['company_id']			= $_SESSION['company_id'];
							
							$inserid_cust=add_record('tbl_customer', $info_cust, $dbcon);
							$cust_id=$inserid_cust;
							
							$infoadd['c_add_location']	= $rel_main['ENQ_ADDRESS'];
							//$infoadd['c_add_street']	= $POST['c_add_street'];
							$infoadd['c_add_country']	= 101;
						if($rel_main['stateid']==0){
							$query_st="select * from state_mst where state_name='".$rel_main['ENQ_STATE']."' and state_status=0";
							$result_st=$dbcon->query($query_st);
							$rel_st=mysqli_fetch_assoc($result_st);
							if($rel_st['stateid']==""){
								$info_st['state_name']		= $rel_main['ENQ_STATE'];
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
							$state_id=$rel_main['stateid'];
						}
							$infoadd['c_add_state']		= $state_id;
						if($rel_main['cityid']==0){
							$query_ct="select * from city_mst where city_name='".$rel_main['ENQ_CITY']."' and city_status=0";
							$result_ct=$dbcon->query($query_ct);
							$rel_ct=mysqli_fetch_assoc($result_ct);
							if($rel_ct['cityid']==""){
								$info_ct['city_name']		= $rel_main['ENQ_CITY'];
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
							$city_id=$rel_main['cityid'];
						}
							
							$infoadd['c_add_city']		= $city_id;
							//$infoadd['c_add_zip']		= $POST['c_add_zip'];
							$infoadd['cust_id']			= $cust_id;
							$infoadd['cdate']			= date("Y-m-d H:i:s");
							$infoadd['user_id']			= $_SESSION['user_id'];
							$infoadd['company_id']		= $_SESSION['company_id'];
							$inseraddid=add_record('tbl_cust_address',$infoadd,$dbcon);
							
							
							$infoper['c_con_fname']		=$rel_main['SENDERNAME'];
							//$infoper['c_con_lname']		=$POST['con_last'];
							$infoper['c_con_email']		=$rel_main['ENQ_ADDRESS'];
							$infoper['c_con_mobile']	=$rel_main['MOB'];
							$infoper['c_con_phone']		=$rel_main['PHONE'];
							//$infoper['c_con_job']		=$POST['con_job'];
							$infoper['cust_id']			=$cust_id;
							
							$infoper['cdate']			= date("Y-m-d H:i:s");
							$infoper['user_id']			= $_SESSION['user_id'];
							$infoper['company_id']		= $_SESSION['company_id'];
							
							$con_per=add_record('tbl_cust_contact',$infoper,$dbcon);
							
						}else{
							$cust_id=$rel['cust_id'];
						}
						if($rel_main['product_id']==0){
                                                    $query_pro="select * from product_mst where product_name='".$rel_main['PRODUCT_NAME']."' and product_status=0";
                                                    $result_pro=$dbcon->query($query_pro);
                                                    $rel_pro=mysqli_fetch_assoc($result_pro);
                                                    if($rel_pro['product_id']==""){
                                                            $info_pro['product_type']	= 2;
                                                            $info_pro['product_name'] 	= $rel_main['PRODUCT_NAME'];
                                                            $info_pro['product_gst'] 	= "excluding";
                                                            $info_pro['cdate'] 	   	= date('Y-m-d');
                                                            $info_pro['user_id'] 	   	= $_SESSION['user_id'];
                                                            $info_pro['company_id'] 	= $_SESSION['company_id'];
                                                            $inserid_pro=add_record('product_mst', $info_pro, $dbcon);
                                                            $product_id=$inserid_pro;
                                                            //$category_id=1;
                                                    }else{
                                                            $product_id=$rel_pro['product_id'];
                                                            //$category_id=$rel_pro['category_id'];
                                                    }
                                                }else{
                                                        $product_id=$rel_main['product_id'];
                                                }
						$info['show_user_ids']          = $show_user_ids;
						$info['indiamart_id']           = $rel_main['QUERY_ID'];
						$info['inquiry_no']             = $rel_main['QUERY_ID'];
						$info['inquiry_date']           = date('Y-m-d',strtotime($rel_main['DATE_TIME_RE']));
						$info['cust_id']		= $cust_id;
						$info['c_con_id']		= $con_per;
						$info['inquiry_name']           = $rel_main['PRODUCT_NAME'].'@'.$rel_main['GLUSR_USR_COMPANYNAME'];
						$info['closing_date']           = date('Y-m-d',strtotime($rel_main['DATE_TIME_RE']));
						$info['t_id']			= $POST['t_id'];
						$info['opp_id']			= 5;
						$info['stage_prob']		= 10;
						$info['sales_stage_id']         = 4;
						$info['inquiry_type_id']        = 9;
						$info['rb_id']			= 6;
						$info['inquiry_cat_id']         = 10;
						$info['currency_id']            = 68;
						
						$info['g_total']		= $rel_pro['product_sale_rate'];
						$info['inq_desc']		= stripcslashes(text_rnremove($rel_main['ENQ_MESSAGE']));
						$info['inq_comp_desc']          = stripcslashes(text_rnremove($rel_main['ENQ_MESSAGE']));
						
						$info['create_date']            = date('Y-m-d H:i:s');
						$info['cdate']			= date("Y-m-d H:i:s");
						$info['mdate']			= date("Y-m-d H:i:s");
                                                $info['assign_user_inq_ids']    = $rel_main['user_ids'];
						$info['user_id']		= $rel_main['user_ids']; //$_SESSION['user_id'];
						$info['company_id']		= $_SESSION['company_id'];
						
						$info['won_user_id']		=0;
						$info['inquiry_status']		=0;
						$info['t_id']			=0;
					//	var_dump($info);
						$ins_inquiry_id=add_record('tbl_inquiry', $info, $dbcon);
			
					$infotsk['show_user_ids']		= $show_user_ids;
					$infotsk['task_type_id']		= 16;
					$infotsk['task_rel_id']			= 5;//Fixed Type Inquiry
					$infotsk['inquiry_id']			= $ins_inquiry_id;
					//$infotsk['assign_user_ids']		= implode(",",array_filter($_SESSION['user_id']));
					//$infotsk['assign_user_ids']		= $_SESSION['user_id'];
					$infotsk['assign_user_ids']		= $rel_main['user_ids'];
					//$infotsk['assign_user_ids']= implode(",",array_filter($rel_main['user_ids']));
					$infotsk['task_priority_id']            = 1;
					$infotsk['task_due_date']		= date('Y-m-d H:i:s',strtotime($rel_main['DATE_TIME_RE']));
					$infotsk['task_alert_id']		= 2;
					$infotsk['alert_date_time']		= date('Y-m-d H:i:s',strtotime($rel_main['DATE_TIME_RE']));
					
					$infotsk['task_remark']		= stripcslashes(text_rnremove($rel_main['ENQ_MESSAGE']));
					$infotsk['create_date']		= date('Y-m-d H:i:s');
					$infotsk['entry_type']		= 1;//Fixed Task Type
					$infotsk['cdate']			= date("Y-m-d H:i:s");
					$infotsk['user_id']			= $_SESSION['user_id'];
					$infotsk['company_id']		= $_SESSION['company_id'];
					$ins_task_id=add_record('tbl_task', $infotsk, $dbcon);
							
						$info1['product_id']	= $product_id;
						$info1['product_qty']	= 1;
						$info1['product_rate']	= $rel_pro['product_sale_rate'];
						$info1['product_amount']= $rel_pro['product_sale_rate'];
						$info1['user_id']	= $_SESSION['user_id'];
						$info1['company_id']	= $_SESSION['company_id'];
						$table='tbl_inquiry_trn';$tableid='inquiry_trn_id';
						$info1['inquiry_id']	= $ins_inquiry_id;
						$info1['user_id']	= $_SESSION['user_id'];
						$inserid11=add_record($table, $info1, $dbcon);
						
						
						$info1111['inquiry_id']=$ins_inquiry_id;	
						$updateid=update_record('tbl_indiamart_data', $info1111, "i_id=".$rel_main['i_id'] , $dbcon);
						//echo $i;
						$i++;
					}
				}
			}
			
		
		}
	


?>