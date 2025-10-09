<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		 
			$appData = array();
			$i=1;
			$aColumns = array('l.l_id', 'l.l_name', 'l.l_group','l.user_id','g.g_name','l.l_form','l.l_status');
			$sIndexColumn = "l_id";
			$isWhere = array("l_status !=2");
			$sTable = " tbl_ledger as l";			
			$isJOIN = array("left join tbl_group as g on g.g_id=l.l_group");
			$hOrder = "l.l_status desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				
				if($row['l_status']=='0')
				{
					$status="<strong style='color:green'>Approved</strong>";
					$change_status="<a class='btn btn-success' onclick='changeStatus(\"".$row['l_id']."\",\"".$row['l_status']."\")'><i class='fa fa-check-square-o'></i></a>";
				} 
				else 
				{  
					$status="<strong style='color:red' >Pending</strong>"; 
					$change_status="<a class='btn btn-danger' onclick='changeStatus(\"".$row['l_id']."\",\"".$row['l_status']."\")'><i class='fa fa-window-close'></i></a>";
				}
				
				//upload documnet only for salary accounts
				if($row['l_group']=='58')
				{
					$upload='<a class="btn btn-success" data-original-title="Upload Document" data-toggle="tooltip" data-placement="top" href="'.ROOT.'upload_document/'.$row['l_id'].'">Upload Documents</a>';
				}
				else
				{
					$upload='';
				}
				
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['g_name'];
				$row_data[] = $status;
				$row_data[] = $upload;
				
				
				$edit_btn=''; $delete_btn=''; 
				if($edit_btn_per){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'ledger_edit/'.$row['l_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				if($delete_btn_per){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_ledger('.$row['l_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
				
				if($row['l_form']=='customer_form')
				{
					$sold_btn='<button class="btn btn-xs btn-primary" data-original-title="Allocate Sale Customer Product" data-toggle="tooltip" data-placement="top" onClick="alloc_sold_pro('.$row['l_id'].');"><i class="fa fa-plus"></i></button>';
				}
				else
				{
					$sold_btn='';
				}
				
				
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$sold_btn ; 
				$row_data[] = $change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "get_open_form") {
			
			$gid=$POST['gid'];
			
			$q=$dbcon->query("select * from tbl_group where g_id='$gid'");
			$row=mysqli_fetch_array($q);
			
			echo $row['form_id'];
			//echo $gid;
		}
		else if(strtolower($POST['mode']) == "add") {
			
			$info['l_name']	= $POST['ledger_name'];
			$info['l_group']		= $POST['ledger_grp'];
			$info['m_name']		= $POST['m_name'];
			$info['m_address']		= $POST['m_address'];
			$info['countryid']			= $POST['countryid'];
			$info['stateid']			= $POST['stateid'];
			$info['cityid']	= $POST['cityid'];
			$info['cust_pincode']		= $POST['cust_pincode'];
			$info['m_pan']	= $POST['m_pan'];
			$info['company_name']		= $POST['company_name'];
			$info['cust_cont_name']	= $POST['cust_cont_name'];
			$info['cust_mobile']	= $POST['cust_mobile'];
			$info['cust_email']	= strtolower($POST['cust_email']);
			$info['cust_website']	= $POST['cust_website'];
			$info['zone_id']	= $POST['zone_id'];
			$info['cust_remark']	= $POST['cust_remark'];
			$info['gst_no']	= $POST['gst_no'];
			$info['party_type']	= $POST['party_type'];
			$info['cust_gst_reg']	= $POST['cust_gst_reg'];
			$info['pay_terms']	= $POST['pay_terms'];
			$info['pay_method']	= $POST['pay_method'];
			$info['bill_type']	= $POST['bill_type'];
			$info['balance_typeid']	= $POST['balance_typeid'];
			$info['acc_type']	= $POST['acc_type'];
			$info['bankid']	= $POST['bankid'];
			$info['branch_name']	= $POST['branch_name'];
			$info['acc_name']	= $POST['acc_name'];
			$info['acc_number']	= $POST['acc_number'];
			$info['acc_chequeno']	= $POST['acc_chequeno'];
			$info['acc_chequeleft']	= $POST['acc_chequeleft'];
			$info['emp_mobile']	= $POST['emp_mobile'];
			$info['emp_email']	= $POST['emp_email'];
			$info['emp_password']	= $POST['emp_password'];
			$info['emp_zone_id']	= $POST['emp_zone_id'];
			$info['emp_user_type']	= $POST['emp_user_type'];
			$info['tax_value']	= $POST['tax_value'];
			$info['branch_id_customer']	= $POST['branch_id_customer'];
			$info['party_sez']		= $POST['party_sez'];
			$info['branch_id_employee']	= $POST['branch_id_emp'];
			$info['l_status']	= '1';
			$info['usertype_terr']	= implode(",",$POST['usertype_terr']);
			$info['alloc_stateid']	= implode(",",$POST['alloc_stateid']);
			$info['alloc_cityid']	= implode(",",$POST['alloc_cityid']);
			$info['report_to_user_type']	= $POST['report_to_user_type'];
			$info['report_to_user_id']	= $POST['report_to_user_id'];
			
			$info['opn_balance']	= $POST['opn_balance'];
			$info['l_form']	= $POST['form_type'];
			
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			
			$tr = $dbcon -> query("SELECT `l_id`,`l_name`,`l_status`,`l_group` FROM `tbl_ledger` WHERE l_status!=2 and `l_name` ='".$POST['ledger_name']."' ");
			if($tr->num_rows > 0) {
				$row['res'] ="-1";
			}
			else
			{
				
			$inserid=add_record('tbl_ledger', $info, $dbcon);
			
			if($inserid){
				
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"ledger_add",1,"tbl_ledger",$inserid);
				
				if($POST['form_type']=='customer_form')
				{
					/* Add Record in customer Person Table Start */
					
					$info1['cust_contact_person_name']			= stripcslashes($POST['cust_cont_name']);
					$info1['cust_contact_person_no']			= $POST['cust_mobile'];
					$info1['cust_contact_person_email']			= strtolower($POST['cust_email']);
					$info1['cust_id']							= $inserid;
					$info1['user_id']							= $_SESSION['user_id'];
					$info1['cust_contact_person_direct_status']	= 1;
					$insercntid=add_record("tbl_cust_contact_person", $info1, $dbcon);
						
					/* Add Record in customer Person Table End */
					
					$dbcon->query("update tbl_customer_bank set b_cust='$inserid' where b_cust='0' and userid='$_SESSION[user_id]'");
					
					$dbcon->query("update tbl_cust_contact_person set cust_id='$inserid' where cust_id='0' and user_id='$_SESSION[user_id]'");
				}
				
				if($POST['form_type']=='emp_form')
				{
					/*Entry in User Table Start*/	
					
					$infousr['user_name']		= $POST['ledger_name']; 
					$infousr['user_mail']		= strtolower($POST['emp_email']); 
					$infousr['user_key']		= md5($_POST['emp_password']);
					$infousr['user_type']		= $POST['emp_user_type'];//Fixed Type Employee
					$infousr['user_country']	= $POST['countryid'];
					$infousr['user_stat']		= $POST['stateid'];
					$infousr['user_city']		= $POST['cityid'];
					$infousr['user_phone']		= $POST['emp_mobile'];
					$infousr['usertype_terr']	= implode(",",$POST['usertype_terr']);
					$infousr['alloc_stateid']	= implode(",",$POST['alloc_stateid']);
					$infousr['alloc_cityid']	= implode(",",$POST['alloc_cityid']);
					$infousr['report_to_user_type']	= $POST['report_to_user_type'];
					$infousr['report_to_user_id']	= $POST['report_to_user_id'];
					$infousr['user_address']	= $_POST['m_address'];
					$infousr['user_rid']		= $_SESSION['user_id'];
					$infousr['company_id']		= $_SESSION['company_id'];
					$infousr['payment_status'] 	= 1;
					$infousr['employee_id'] 	= $inserid;//Employee ID flag check
					$inserusrid=add_record('users', $infousr, $dbcon);
					
					/*Entry in User Table End*/	
				}
				
				$row['res'] ="1";
				
			}
			else{
				$row['res'] ="0";
			}
			
			}
			
			echo json_encode($row);	
		}
		
		else if(strtolower($POST['mode']) == "edit") {
			
			$info['l_name']	= $POST['ledger_name'];
			$info['l_group']		= $POST['ledger_grp'];
			$info['m_name']		= $POST['m_name'];
			$info['m_address']		= $POST['m_address'];
			$info['countryid']			= $POST['countryid'];
			$info['stateid']			= $POST['stateid'];
			$info['cityid']	= $POST['cityid'];
			$info['cust_pincode']		= $POST['cust_pincode'];
			$info['m_pan']	= $POST['m_pan'];
			$info['company_name']		= $POST['company_name'];
			$info['cust_cont_name']	= $POST['cust_cont_name'];
			$info['cust_mobile']	= $POST['cust_mobile'];
			$info['cust_email']	= strtolower($POST['cust_email']);
			$info['cust_website']	= $POST['cust_website'];
			$info['zone_id']	= $POST['zone_id'];
			$info['cust_remark']	= $POST['cust_remark'];
			$info['gst_no']	= $POST['gst_no'];
			$info['party_type']	= $POST['party_type'];
			$info['cust_gst_reg']	= $POST['cust_gst_reg'];
			$info['party_sez']	= $POST['party_sez'];
			$info['pay_terms']	= $POST['pay_terms'];
			$info['pay_method']	= $POST['pay_method'];
			$info['bill_type']	= $POST['bill_type'];
			$info['balance_typeid']	= $POST['balance_typeid'];
			$info['acc_type']	= $POST['acc_type'];
			$info['bankid']	= $POST['bankid'];
			$info['branch_name']	= $POST['branch_name'];
			$info['acc_name']	= $POST['acc_name'];
			$info['acc_number']	= $POST['acc_number'];
			$info['acc_chequeno']	= $POST['acc_chequeno'];
			$info['acc_chequeleft']	= $POST['acc_chequeleft'];
			$info['emp_mobile']	= $POST['emp_mobile'];
			$info['emp_email']	= $POST['emp_email'];
			$info['emp_password']	= $POST['emp_password'];
			$info['emp_zone_id']	= $POST['emp_zone_id'];
			$info['emp_user_type']	= $POST['emp_user_type'];
			$info['usertype_terr']	= implode(",",$POST['usertype_terr']);
			$info['alloc_stateid']	= implode(",",$POST['alloc_stateid']);
			$info['alloc_cityid']	= implode(",",$POST['alloc_cityid']);
			$info['report_to_user_type']	= $POST['report_to_user_type'];
			$info['report_to_user_id']	= $POST['report_to_user_id'];
			
			$info['opn_balance']	= $POST['opn_balance'];
			$info['l_form']	= $POST['form_type'];
			
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			
			$updateid=update_record('tbl_ledger', $info,"l_id=".$POST['ledger_id'] , $dbcon);
			
			$info1['user_name'] 	= $POST['ledger_name'];
			if($POST['emp_password']){
				$info1['user_key']		= md5($_POST['emp_password']);
			}
			$info1['user_mail']		= $POST['emp_email'];
			$info1['usertype_terr']	= implode(",",$POST['usertype_terr']);
			$info1['alloc_stateid']	= implode(",",$POST['alloc_stateid']);
			$info1['alloc_cityid']	= implode(",",$POST['alloc_cityid']);
			$info1['report_to_user_type']	= $POST['report_to_user_type'];
			$info1['report_to_user_id']	= $POST['report_to_user_id'];
			update_record('users', $info1,"user_type!=2 and employee_id=".$POST['ledger_id'] , $dbcon);
			
			if($updateid){
				
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"ledger_add",2,"tbl_ledger",$POST['ledger_id']);
				
				$row['res'] ="3";
				
			}
			else{
				$row['res'] ="0";
			}
			
			echo json_encode($row);	
		}
		
		else if(strtolower($POST['mode']) == "delete") 
		{
			
			$info['l_status']	= 2;
			$updateid=update_record('tbl_ledger', $info,"l_id=".$POST['eid'] , $dbcon);	
			
			//Deactivate Users
			$infusr['active']=2;
			$updateusrid=update_record('users', $infusr,"user_type!=2 and employee_id=".$POST['eid'] , $dbcon);	
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"ledger_add",2,"tbl_ledger",$POST['eid']);
				
			if($updateid)
				echo "1";	
			else
				echo "0";				
		}
		else if(strtolower($POST['mode']) == "change_status") 
		{
			$l_status=$POST['l_status'];
			$lid=$POST['lid'];
			
			if($l_status==0)
			{
				$info['l_status'] = 1;
			}
			else
			{
				$info['l_status'] = 0;
			}
			
			$updateid=update_record('tbl_ledger', $info,"l_id=".$POST['lid'] , $dbcon);		
			
			//Deactivate Users
			$infusr['active']=$info['l_status'];
			$updateusrid=update_record('users', $infusr,"user_type!=2 and employee_id=".$POST['lid'] , $dbcon);		
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"ledger_add",2,"tbl_ledger",$POST['lid']);
			
			if($updateid)
				echo "1";	
			else
				echo "0";	
		}
		else if(strtolower($POST['mode']) == "load_city_all"){
			$alloc_stateid=array_filter($POST['alloc_stateid']);
			$alloc_stateid=implode(",",$alloc_stateid);
			$str=get_city_all($dbcon,"",$alloc_stateid);
			$resp['html_resp']=$str;
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "load_report_to_users"){
			$resp['html_resp']=get_users_typewise($dbcon,""," and user_type=".$POST['report_to_user_type']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "get_branch_by_zone") 
		{
			$zid=$POST['zid'];
			$bid=$POST['bid'];
			$sindex=$POST['sindex'];
			
			echo get_branch_from_zone($dbcon,$zid,$bid,$sindex);
			//echo $zid;
		}
		else if(strtolower($POST['mode']) == "generate_report_ledger") 
		{
			$s_date=explode(' - ',$POST['date']);
			$query="select g.* from tbl_group as g order by g.g_name";
			$qry=$dbcon->query($query);
			
			$cnt=1;$str='';
			while($row=mysqli_fetch_assoc($qry))
			{
				$balance=get_group_ledger_amount($dbcon,$row['g_id'],$s_date['1']);
				if($balance>0){
					$cradit_amo=abs($balance);
					$debit_amount="";
				}else if($balance<0){
					$cradit_amo="";
					$debit_amount=abs($balance);
				}else{
					$cradit_amo="";
					$debit_amount="";
				}
				$str.='<tr>
					
					<th>'.$cnt.'</th>
					<th><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'ledger_detail/'.$row['g_id'].'">'.$row['g_name'].'</a></th>
					<th>'.$cradit_amo.'</th>
					<th>'.$debit_amount.'</th>
				</tr>';
				
				$cnt++;
			}
			
			echo $str;
		}
	
		else if(strtolower($POST['mode']) == "generate_report_ledger_detail") 
		{
			$l_id=$POST['l_id'];
			$s_date=explode(' - ',$POST['date']);
			
			$query="select l.* from tbl_ledger as l where l.l_group='$l_id'";
			$qry=$dbcon->query($query);
			
			$cnt=1;$str='';
			while($row=mysqli_fetch_assoc($qry))
			{
				$balance=get_ledger_amount($dbcon,$row['l_id'],$s_date['1']);
				if($balance>0){
					$cradit_amo=abs($balance);
					$debit_amount="";
				}else if($balance<0){
					$cradit_amo="";
					$debit_amount=abs($balance);
				}else{
					$cradit_amo="";
					$debit_amount="";
				}
				$str.='<tr>
					
					<th>'.$cnt.'</th>
					<th><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'ledger_form/'.$row['l_id'].'">'.$row['l_name'].'</a></th>
					<th>'.$cradit_amo.'</th>
					<th>'.$debit_amount.'</th>
				</tr>';
				
				$cnt++;
			}
			
			echo $str;
		}
		else if(strtolower($POST['mode']) == "ledger_tree") 
		{
			$parentKey = -1;
			//echo $parentKey;
			$sql="select * from tbl_group order by g_name";
			$rs=$dbcon->query($sql);
			$count=mysqli_num_rows($rs);
			
			if($count > 0)
			  {
				  $data = members_Tree($dbcon,$parentKey);
			  }else{
				  $data=["id"=>"0","name"=>"No Members present in list","text"=>"No Members is present in list","nodes"=>[]];
			  }
			  
			  echo json_encode(array_values($data));
			 // print_r($data);
			//echo $count;
		}
		else if(strtolower($POST['mode']) == "check_username") 
		{
			$uname=$POST['uname'];
			
			$sel=$dbcon->query("select emp_email from tbl_ledger where l_status=0 and emp_email='$uname' ");
			$count=mysqli_num_rows($sel);
			
			echo $count;
		}
		else if(strtolower($POST['mode']) == "upload_docs") 
		{
			 $l_id=$POST['l_id'];
			 $docs_id=$POST['docs_id'];
			 
			 $rel=$dbcon->query("select ed_id from tbl_employee_document where ed_lid='$l_id' and ed_doc_type='$docs_id'");
			 $count=mysqli_num_rows($rel);
			 
			 $test = explode('.', $_FILES["file"]["name"]);
			 $ext = end($test);
			 $name = rand(100, 999) . '.' . $ext;
			 $path='../../view/upload/employee_document/';
			 $location = $path . $name;  
			 move_uploaded_file($_FILES["file"]["tmp_name"], $location);
			 
			 $info1['ed_lid']=$l_id;
			 $info1['ed_doc_type']=$docs_id;
			 $info1['ed_path']=$name;
			 $info1['cdate']=date("Y-m-d");
			 $info1['user_id']			= $_SESSION['user_id'];
			 $info1['company_id']			= $_SESSION['company_id'];
			
			 $table='tbl_employee_document';$tableid='ed_id';
			 
			 if($count>0)
			 {
				update_record($table, $info1,"ed_lid='$l_id' and ed_doc_type='$docs_id'", $dbcon);	
				
			 }
			 else
			 {
				 $inserid=add_record($table, $info1, $dbcon);
			 }
			
		}
		else if(strtolower($POST['mode']) == "show_upload_docs") 
		{
			$l_id=$POST['l_id'];
			
			$q="select * from tbl_employee_document where ed_lid='$l_id'";
			
			$str="";
			
			$sel=$dbcon->query($q);
			while($row=mysqli_fetch_array($sel))
			{
				if($row['ed_doc_type']=='1')
				{
					$type='Pan card';
				}
				else if($row['ed_doc_type']=='2')
				{
					$type='Adhar Card Front';
				}
				else if($row['ed_doc_type']=='3')
				{
					$type='Adhar Card Back';
				}
				else
				{
					$type='Passport Size Photo';
				}
				
				$str.="<div class='col-md-3' style='text-align:center;font-size:18px;'>";
				$str.="<strong >".$type."</strong>";
				$str.="<img src='".ROOT.'upload/employee_document/'.$row['ed_path']."' width='100%' height='200' />";
				$str.="</div>";
				
			}
			
			echo $str;
		}	
		else if(strtolower($POST['mode'])== "add_sold_pro_field") {
			$tr = $dbcon -> query("SELECT `cust_sold_pro_id` FROM `tbl_cust_sold_pro` WHERE `cust_id` = '$POST[cust_id]' and `product_id` = '$POST[product_id]' and `sold_pro_srl_no` = '$POST[sold_pro_srl_no]' and cust_sold_pro_status=0 and company_id=".$_SESSION['company_id'] );
			if($tr->num_rows > 0 && !$POST['edit_id']) {
				$row['res']='-1';
			}
			else{
				$info1['cust_id']				= $POST['cust_id'];
				$info1['sold_inv_foc_date']		= date("Y-m-d",strtotime($POST['sold_inv_foc_date']));
				$info1['product_id']			= $POST['product_id'];
				$info1['sold_pro_srl_no']		= $POST['sold_pro_srl_no'];
				$info1['sold_inv_rmrk']			= $_POST['sold_inv_rmrk'];
				$info1['cdate']					= date("Y-m-d H:i:s");
				$info1['user_id']				= $_SESSION['user_id'];
				$info1['company_id']			= $_SESSION['company_id'];
				$table='tbl_cust_sold_pro';$tableid='cust_sold_pro_id';
				
				if(empty($POST['edit_id'])) {
					$inserid=add_record($table, $info1, $dbcon);
				}
				else {
					$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon);	
				}
				$row['res']='1';
			}
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "show_sold_pro") {
		if($POST['cust_id']!=""){
		  $where ="and imst.cust_id =".$POST['cust_id'];
		}
		$appData = array();
		$i=1;
		$aColumns = array('cust_sold_pro_id','sold_inv_no','sold_inv_date','sold_pro_srl_no','sold_inv_rmrk','sold_inv_rate','sold_inv_foc_date','pro.product_name','model.model_name');
		$sIndexColumn = "cust_sold_pro_id";
		$isWhere = array("cust_sold_pro_status=0 ".$where." and imst.company_id in(0,$_SESSION[company_id])");
		$sTable = "tbl_cust_sold_pro as imst";
		$isJOIN = array("left join product_mst as pro on pro.product_id=imst.product_id", "left join model_mst as model on model.model_id=imst.model_id");
		$hOrder = "imst.cust_sold_pro_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			//$row_data[] = $row['sr'];
			
			$row_data[] = $row['product_name'];
			$row_data[] = date("d-m-Y",strtotime($row['sold_inv_foc_date']));
			$row_data[] = $row['sold_pro_srl_no'];
			$row_data[] = $row['sold_inv_rmrk'];
			
			$row_data[] = '<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_sold_pro('.$row['cust_sold_pro_id'].');"><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sold_pro('.$row['cust_sold_pro_id'].')"><i class="fa fa-trash-o"></i></button>';
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "edit_sold_pro") {	
		$q = $dbcon -> query("SELECT * FROM `tbl_cust_sold_pro` WHERE cust_sold_pro_status=0 and `cust_sold_pro_id` = '$POST[cust_sold_pro_id]'");
		$r = $q->fetch_assoc(); 
		$r['model_resp_html'] = get_prowise_model($dbcon,$r['model_id'],$r['product_id']);
		$r['sold_inv_date'] = date("d-m-Y",strtotime($r['sold_inv_date']));
		$r['sold_inv_foc_date'] = date("d-m-Y",strtotime($r['sold_inv_foc_date']));
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "delete_sold_pro") {
		$info['cust_sold_pro_status']='2';
		$updateid=update_record('tbl_cust_sold_pro', $info, "cust_sold_pro_id=".$POST['cust_sold_pro_id'], $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	else if(strtolower($POST['mode']) == "add_bank_name") {
			
			
			$info1['bank_ac']= $POST['bank_ac'];
			$info1['b_name']= $POST['bank_name'];
			$info1['ac_name']= $POST['ac_name'];
			$info1['bank_ifsc']= $POST['bank_ifsc'];
			$info1['bank_open']= $POST['bank_open'];
			$info1['b_cust']= $POST['cust_id'];
			$info1['userid']		= $_SESSION['user_id'];
			
			$info1['cdate'] = date("Y-m-d");
			
			$table='tbl_customer_bank';$tableid='b_id';
		
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		else if(strtolower($POST['mode']) == "load_bank_detail") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,b.bank_name from tbl_customer_bank as mst 
				left join bank_mst as b on b.bankid=mst.b_name
				where mst.b_cust='$_POST[cust_id]' order by b_id Desc";
			}
			else{
				$query="select mst.*,b.bank_name from tbl_customer_bank as mst 
				left join bank_mst as b on b.bankid=mst.b_name
				where mst.b_cust='0' order by b_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					
					<div class="col-md-12 col-xs-11">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th>A/c No</th>
							<th width="5%">Bank Nmae</th>
							<th>A/C Name</th>
							<th>IFSC</th>
							<td>Opening</td>
							<td></td>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['bank_ac'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['bank_name'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['ac_name'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['bank_ifsc'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['bank_open'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_bank('.$rel['b_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_bank('.$rel['b_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="7" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "preedit_bank")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_customer_bank WHERE b_id='$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
			//echo $POST['mode'];
		}
		else if(strtolower($POST['mode'])== "delete_data_bank")
		{
			
			$deleteid=delete_record('tbl_customer_bank', "b_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		//contact person details
		
		else if(strtolower($POST['mode']) == "add_contact_person") {
			
			
			$info1['cust_contact_person_name']= $POST['con_name'];
			$info1['cust_contact_person_no']= $POST['con_mobile'];
			$info1['cust_contact_person_email']= $POST['con_email'];
			$info1['cust_id']= $POST['cust_id'];
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['cdate'] = date("Y-m-d h:i:s");
			
			$table='tbl_cust_contact_person';$tableid='cust_contact_person_id';
		
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_contact_detail") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select * from tbl_cust_contact_person where cust_id='$_POST[cust_id]' and user_id='$_SESSION[user_id]' order by cust_contact_person_id Desc";
			}
			else{
				$query="select * from tbl_cust_contact_person where cust_id='0' and user_id='$_SESSION[user_id]' order by cust_contact_person_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th>Name</th>
							<th width="5%">Mobile</th>
							<th>Email</th>
							<td></td>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['cust_contact_person_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['cust_contact_person_no'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['cust_contact_person_email'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_contact('.$rel['cust_contact_person_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_contact('.$rel['cust_contact_person_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_contact")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_cust_contact_person WHERE cust_contact_person_id='$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
			//echo $POST['mode'];
		}
		else if(strtolower($POST['mode'])== "delete_data_contact")
		{
			
			$deleteid=delete_record('tbl_cust_contact_person', "cust_contact_person_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
	
	
	
	
	
    }
 
}

	  
?>