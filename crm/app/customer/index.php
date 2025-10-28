<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check paermission for customer add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	        CUSTOMER_PARTY_MASTER_SLUG_UPDATE,
	        CUSTOMER_PARTY_MASTER_SLUG_DELETE
	    ]);
		$companyConfiguration = getCompanyConfiguration($dbcon);
        $enable_assing_user = $companyConfiguration['enable_assing_user'];
		$enable_post_crm = $companyConfiguration['enable_post_crm'];

	    $branch_id = $POST['branch_id'];
		if($_SESSION['user_type']!=2){ 
		    if ($enable_assing_user == 1)
            {
    		    $where=" and FIND_IN_SET($_SESSION[user_id],cust.cust_assign_user)";
    		    // $where=" and cust.cust_assign_user IN ($_SESSION[user_id])";
            }
		}

		if($POST['party_type']){
			$where.=' and cust.party_type='.$POST['party_type'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('cust_id', 'cc.cc_name', 'party_type','mdetail.mcd_name', 'cust_name', 'cust_email', 'cust_mobile','post_crm_yes_no', 'cust_gst', 'cust_status','cust.cdate','cust.user_id','cust.cust_owner','cust.ledger_id');
		$sIndexColumn = "cust_id";
		$isWhere = array("cust_status = 0 ".$where." and cust.company_id in (0,$_SESSION[company_id])");
		$sTable = " tbl_customer as cust";			
		$isJOIN = array('left join  tbl_customer_category as cc on cc.cc_id=cust.cust_cat','left join tbl_master_category_detail as mdetail on mdetail.mcd_id=cust.cust_type');
		$hOrder = "cust.cust_id desc";
		$hGroupby = array("cust.cust_id");
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$users = getUserDetailById($dbcon,$row['user_id']);
			$owners = getUserDetailById($dbcon,$row['cust_owner']);
			$ledger_id=$row["ledger_id"];
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cc_name'];
			$row_data[] = $row['mcd_name'];
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['cust_email'];
			$row_data[] = $row['cust_mobile'];
			$row_data[] = $row['cust_gst'];
			$row_data[] = $users['user_name'];
			$row_data[] = $owners['user_name'];

			if(empty($ledger_id)){
				$copy_led_btn=' <button class="btn btn-xs btn-danger" data-original-title="Copy Ledger" data-toggle="tooltip" data-placement="top" onClick="copy_led('.$row['cust_id'].')">Genrate Ledger</button>'; 
			}else{
				$copy_led_btn="";
			}
			if($enable_post_crm == 1) 
			{ 
				if($row['post_crm_yes_no'] == 0)
				{
					$row_data[] = "Yes";
				}
				else
				{
					$row_data[] ="No";
				}
			}
			$edit_btn=''; $delete_btn=''; 
			
			$view_cust_btn='<a class="btn btn-xs btn-info" data-original-title="View Customer" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'customer_view/'.$row['cust_id'].'"><i class="fa fa-eye"></i></a>';
			
			if(in_array(CUSTOMER_PARTY_MASTER_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'customeraddedit/'.$row['cust_id'].'"><i class="fa fa-pencil"></i></a>'; 
			}
			if(in_array(CUSTOMER_PARTY_MASTER_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust('.$row['cust_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			
			$row_data[] = $printcheckbox.' '.$edit_btn.' '.$delete_btn.' '.$copy_led_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		/*echo "<pre>"; print_r($output);echo "</pre>";exit;*/
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add" || strtolower($POST['cust_mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$companyConfiguration=getCompanyConfiguration($dbcon);
		$enable_assing_user=$companyConfiguration['enable_assing_user'];

		$tr = $dbcon -> query("SELECT `cust_id`,`cust_name`,`cust_status`,`cust_gst`,`cust_mobile` FROM `tbl_customer` WHERE `cust_name` = '".$POST['cust_name']."' and cust_gst = '".$POST['cust_gst']."' and cust_mobile = '".$POST['cust_mobile']."' and  cust_status=0 and company_id=".$_SESSION['company_id'] );
		if($tr->num_rows > 0) {
			$row['res']='-1';
		}
		else {
			$info['party_type']		= $POST['party_type'];
			$info['cust_name']		= $POST['cust_name'];
			$info['cust_creator']	= $_SESSION['user_id'];
			$info['cust_code']		= $POST['cust_code'];//Generate New Code
			$info['cust_code_series']= $POST['cust_code_series'];//Generate New Code
			$info['cust_cat']		= $POST['cust_cat'];
			$info['cust_desc']		= $POST['cust_desc'];
			$info['cust_ind']		= $POST['cust_ind'];
			$info['cust_type']		= $POST['cust_type'];
			$info['cust_source']	= $POST['cust_source'];
			$info['cust_gst']		= $POST['cust_gst'];
			$info['cust_iec']		= $POST['cust_iec'];
			$info['cust_pan']		= $POST['cust_pan'];
			$info['t_id']		= $POST['t_id'];
			$info['cust_mobile']	= $POST['cust_mobile'];
			$info['cust_email']		= $POST['cust_email'];
			$info['post_crm_yes_no']		= $POST['post_crm_yes_no'];
			$info['account_terms']		= $POST['account_terms'];
			$info['account_credit_limit']		= $POST['account_credit_limit'];
			$info['account_credit_days']		= $POST['account_credit_days'];
			$info['annual_budget'] = $POST['annual_consume'];
			if($enable_assing_user==1){
				$info['cust_assign_user']= trim(check_crm_find_in_set_new($dbcon,$POST['cust_owner'],1),",");
				$info['cust_owner']		= $POST['cust_owner'];
			}else{
				$info['cust_assign_user']= trim(check_crm_find_in_set_new($dbcon,$_SESSION['user_id'],1),",");
				$info['cust_owner']		= $_SESSION['user_id'];
			}
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$inserid=add_record('tbl_customer', $info, $dbcon, $branch_id);
			
			$row['res']='';
			
			if($inserid)
			{
				foreach ($POST['tc_id_dom'] as $key => $name) {
			   		$infotrm['tc_id']			= $POST['tc_id_dom'][$key];
			   		$infotrm['tc_for']			= 0;
			   		$infotrm['tc_priority']		= $POST['tc_priority_dom'][$key];
			   		$infotrm['tc_details']		= $_POST['tc_details_dom'][$key];
			   		$infotrm['ledger_id']		= $inserid;
			   		$infotrm['cdate']			= date("Y-m-d H:i:s");
			   		$infotrm['user_id']			= $_SESSION['user_id'];
			   		$infotrm['company_id']		= $_SESSION['company_id'];
			   		if(in_array($POST['tc_id_dom'][$key],$POST['disp_term_flag_dom'])){
			   			$insertrmid=add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
			   		}
			   	}
			   	
			   	foreach ($POST['tc_id_exp'] as $key => $name) {
			   		$infotrm['tc_id']			= $POST['tc_id_exp'][$key];
			   		$infotrm['tc_for']			= 1;
			   		$infotrm['tc_priority']		= $POST['tc_priority_exp'][$key];
			   		$infotrm['tc_details']		= $_POST['tc_details_exp'][$key];
			   		$infotrm['ledger_id']		= $inserid;
			   		$infotrm['cdate']			= date("Y-m-d H:i:s");
			   		$infotrm['user_id']			= $_SESSION['user_id'];
			   		$infotrm['company_id']		= $_SESSION['company_id'];
			   		if(in_array($POST['tc_id_exp'][$key],$POST['disp_term_flag_exp'])){
			   			$insertrmid=add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
			   		}
			   	}

				//Insert LOG
				$log_entry=common_log_entry($dbcon,"cust_add",1,"tbl_customer",$inserid);
		
				$dbcon->query("update tbl_cust_address set cust_id=".$inserid." where cust_id='0' and user_id=".$_SESSION['user_id']);
				
				$dbcon->query("update tbl_cust_contact set cust_id=".$inserid." where cust_id='0' and user_id=".$_SESSION['user_id']);
				
				$dbcon->query("update tbl_cust_existing set cust_id=".$inserid." where cust_id='0' and user_id=".$_SESSION['user_id']);
                                
                $dbcon->query("update tbl_cust_relation set cust_id=".$inserid." where cust_id='0' and user_id=".$_SESSION['user_id']);

                $dbcon->query("update tbl_party_consignee set cust_ref_id=".$inserid." where cust_ref_id='0' and user_id=".$_SESSION['user_id']);

                $dbcon->query("update tbl_cust_competitor set comp_cust_id=".$inserid." where comp_cust_id='0' and isdelete='0' and user_id=".$_SESSION['user_id']);

                $dbcon->query("update tbl_cust_dispatch set transporter_cust_id=".$inserid." where transporter_cust_id='0' and isdelete='0' and user_id=".$_SESSION['user_id']);

                $dbcon->query("update tbl_cust_forecast_pr set forecast_cust_id=".$inserid." where forecast_cust_id='0' and isdelete='0' and user_id=".$_SESSION['user_id']);

                $dbcon->query("update tbl_ledger_attach_doc set cust_id=".$inserid.",led_attach_status=0 where ref_type='1' and led_attach_status=3 and user_id=".$_SESSION['user_id']);
             	//$dbcon->query("update tbl_cust_forecast_pr set forecast_cust_id=".$inserid." where forecast_cust_id='0' and isdelete='0' and user_id=".$_SESSION['user_id']);

                //value wise forecast entry

                	$financial_year=get_financial_year_new($dbcon); 

		   			$start_date = date("m",strtotime($financial_year['financial_start_date']));
		   			$end_date = date("m",strtotime($financial_year['financial_end_date']));

		   			$start_year= date("Y",strtotime($financial_year['financial_start_date']));
		   			$end_year = date("Y",strtotime($financial_year['financial_end_date']));


		   			   //price list monthly entry 
	                for($j=0;$j<12;$j++)
	                {

	                	$month1 = $start_date+$j;
	   					if($month1 > 12)
	   					{
	   						$month1 = $month1 - 12;
	   						$start_year_new1 = $start_year+1;
	   					}
	   					else
	   					{
	   						$start_year_new1 = $start_year;
	   					}

	                	$info12['customer_id'] = $inserid;
	                	$info12['cust_price_month'] = $POST['price_month_name'.$month1];
	                	$info12['cust_price_year'] = $POST['price_year_name'.$start_year_new1];
	                	$info12['cust_price_version_id'] = $POST['price_list_version'.$month1];

	                	
	                	add_record('tbl_cust_price_list', $info12, $dbcon, '');

	                }
	                
		   			//forecast entry
		   			for($i=0;$i<12;$i++)
		   			{
		   				
	   					$month = $start_date+$i;
	   					if($month > 12)
	   					{
	   						$month = $month - 12;
	   						$start_year_new = $start_year+1;
	   					}
	   					else
	   					{
	   						$start_year_new = $start_year;
	   					}

		                // 	$info11['month_cust_id'] = $inserid;
		                // 	$info11['month_name'] = $POST['month_name'];
		                // 	$info11['year_name'] = $POST['year_name'];
		                // 	$info11['month_value'] = $POST['month_value'];
	                	$info_month['forecast_month'] = $month;
						$info_month['forecast_amount_pr'] = $POST['month_value'.$month];
						$info_month['forecast_month'] = $month;
						$info_month['forecast_year'] = $start_year_new;
						$info_month['forecast_cust_id'] = $inserid;
						$info_month['forecast_type'] = 1;
						$info_month['cdate'] = date("Y-m-d H:i:s A");
						$info_month['company_id'] = $_SESSION['company_id'];
						$info_month['user_id'] = $_SESSION['user_id'];
						$info_month['usertype_id'] = $_SESSION['usertype_id'];

	                	$inserid_month=add_record('tbl_cust_forecast_pr', $info_month, $dbcon, '');

	               }

				if(strtolower($POST['cust_model'])=="model")
				{
					$query="select * from tbl_customer where cust_id=".$inserid;
					$rel=mysqli_fetch_assoc($dbcon->query($query));		
					$row = $rel;
					$row['res']="2"; 
					
					// Add Address Data if Entry from Modal
					// if($POST['c_add_address']){
						$infoadd['c_add_address']	= $_POST['c_add_address'];
						$infoadd['c_add_zip']		= $POST['c_pincode'];
						$infoadd['c_add_country']	= $POST['c_add_country'];
						$infoadd['c_add_state']		= $POST['c_add_state'];
						$infoadd['c_add_city']		= $POST['c_add_city'];
						$infoadd['c_addr_defult']   = 1;
						$infoadd['cust_id']			= $inserid;
						$infoadd['cdate']			= date("Y-m-d H:i:s");
						$infoadd['user_id']			= $_SESSION['user_id'];
						$infoadd['company_id']		= $_SESSION['company_id'];
						$inseraddid=add_record('tbl_cust_address', $infoadd, $dbcon, $branch_id);
					// }
					
				}
				else
				{
					$row['res'] ="1";
				}
			}
			else
			{
				$row['res'] ="0";
			}
			
		}
		echo json_encode($row);	
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$companyConfiguration=getCompanyConfiguration($dbcon);
		$enable_assing_user=$companyConfiguration['enable_assing_user'];

		$info['party_type']		= $POST['party_type'];
		$info['cust_name']		= $POST['cust_name'];
		$info['cust_creator']	= $_SESSION['user_id'];
		$info['cust_code']		= $POST['cust_code'];//Generate New Code
		$info['cust_code_series']= $POST['cust_code_series'];//Generate New Code
		$info['cust_cat']		= $POST['cust_cat'];
		$info['cust_desc']		= $POST['cust_desc'];
		$info['cust_ind']		= $POST['cust_ind'];
		$info['cust_type']		= $POST['cust_type'];
		$info['cust_source']	= $POST['cust_source'];
		$info['cust_gst']		= $POST['cust_gst'];
		$info['cust_iec']		= $POST['cust_iec'];
		$info['cust_pan']		= $POST['cust_pan'];
		$info['t_id']			= $POST['t_id'];
		$info['cust_mobile']	= $POST['cust_mobile'];
		$info['cust_email']		= strtolower($POST['cust_email']);
		$info['post_crm_yes_no']		= $POST['post_crm_yes_no'];
		if($enable_assing_user==1){
			$info['cust_assign_user']= check_crm_find_in_set_new($dbcon,$POST['cust_owner'],1);
			$info['cust_owner']		= $POST['cust_owner'];
		}else{
			$info['cust_assign_user']= check_crm_find_in_set_new($dbcon,$POST['cust_owner'],1);
			$info['cust_owner']		= $POST['cust_owner'];
		}
		$info['birth_date']		= date('Y-m-d',strtotime($POST['birth_date']));
		$info['anniversary_date'] = date('Y-m-d',strtotime($POST['anniversary_date']));
		$info['relation']		= $_POST['relation'];
		$info['gender']			= $POST['gender'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$info['account_terms']		= $POST['account_terms'];
		$info['account_credit_limit']		= $POST['account_credit_limit'];
		$info['account_credit_days']		= $POST['account_credit_days'];
		
		$updateid=update_record('tbl_customer', $info,"cust_id=".$POST['eid'] , $dbcon, $branch_id);
		$row['res']='';

		$party_detail = get_party_detail($dbcon,$POST['eid']);
		$deltrmid=delete_record('tbl_customer_term_trn',"cust_id=".$POST['eid'], $dbcon, $branch_id);
		foreach ($POST['tc_id_dom'] as $key => $name) {
	   		$infotrm['tc_id']			= $POST['tc_id_dom'][$key];
	   		$infotrm['tc_for']			= 0;
	   		$infotrm['tc_priority']		= $POST['tc_priority_dom'][$key];
	   		$infotrm['tc_details']		= $_POST['tc_details_dom'][$key];
	   		$infotrm['cust_id']			= $POST['eid'];
	   		$infotrm['ledger_id']		= $party_detail['ledger_id'];
	   		$infotrm['cdate']			= date("Y-m-d H:i:s");
	   		$infotrm['user_id']			= $_SESSION['user_id'];
	   		$infotrm['company_id']		= $_SESSION['company_id'];
	   		if(in_array($POST['tc_id_dom'][$key],$POST['disp_term_flag_dom'])){
	   			$insertrmid=add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
	   		}
	   	}

	   	foreach ($POST['tc_id_exp'] as $key => $name) {
	   		$infotrm['tc_id']			= $POST['tc_id_exp'][$key];
	   		$infotrm['tc_for']			= 1;
	   		$infotrm['tc_priority']		= $POST['tc_priority_exp'][$key];
	   		$infotrm['tc_details']		= $_POST['tc_details_exp'][$key];
	   		$infotrm['cust_id']			= $POST['eid'];
	   		$infotrm['ledger_id']		= $party_detail['ledger_id'];
	   		$infotrm['cdate']			= date("Y-m-d H:i:s");
	   		$infotrm['user_id']			= $_SESSION['user_id'];
	   		$infotrm['company_id']		= $_SESSION['company_id'];
	   		if(in_array($POST['tc_id_exp'][$key],$POST['disp_term_flag_exp'])){
	   			$insertrmid=add_record('tbl_customer_term_trn', $infotrm, $dbcon, $branch_id);
	   		}
	   	}

		if($updateid)
		{
			$financial_year=get_financial_year_new($dbcon); 

   			$start_date = date("m",strtotime($financial_year['financial_start_date']));
   			$end_date = date("m",strtotime($financial_year['financial_end_date']));

   			$start_year= date("Y",strtotime($financial_year['financial_start_date']));
   			$end_year = date("Y",strtotime($financial_year['financial_end_date']));

   			$info_month1['isdelete']=1;

	    	//$inserid_month=add_record('tbl_cust_forecast_pr', $info_month, $dbcon, '');

	    	$updateid=update_record('tbl_cust_forecast_pr',$info_month1,"forecast_type='1' and forecast_cust_id=".$POST['eid'],$dbcon);

			for($i=0;$i<12;$i++)
			{
				
				$month = $start_date+$i;
				if($month > 12)
				{
					$month = $month - 12;
					$start_year_new = $start_year+1;
				}
				else
				{
					$start_year_new = $start_year;
				}		  

		    	$info_month['forecast_month'] = $month;
				$info_month['forecast_amount_pr'] = $POST['month_value'.$month];
				$info_month['forecast_month'] = $month;
				$info_month['forecast_year'] = $start_year_new;
				$info_month['forecast_cust_id'] = $POST['eid'];
				$info_month['forecast_type'] = 1;
				$info_month['cdate'] = date("Y-m-d H:i:s A");
				$info_month['company_id'] = $_SESSION['company_id'];
				$info_month['user_id'] = $_SESSION['user_id'];
				$info_month['usertype_id'] = $_SESSION['usertype_id'];

				$inseraddid=add_record('tbl_cust_forecast_pr', $info_month, $dbcon,'');

	   		 }

	   		  //price list monthly entry 
            for($j=0;$j<12;$j++)
            {

            	$month1 = $start_date+$j;
					if($month1 > 12)
					{
						$month1 = $month1 - 12;
						$start_year_new1 = $start_year+1;
					}
					else
					{
						$start_year_new1 = $start_year;
					}

            	$info12['customer_id'] = $POST['eid'];
            	$info12['cust_price_month'] = $POST['price_month_name'.$month1];
            	$info12['cust_price_year'] = $POST['price_year_name'.$start_year_new1];
            	$info12['cust_price_version_id'] = $POST['price_list_version'.$month1];

            	
            	add_record('tbl_cust_price_list', $info12, $dbcon, '');

            }


			$row['res']='update';
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"cust_add",2,"tbl_customer",$POST['eid']);
		}
		else
		{
			$row['res']='0';
		}
		echo json_encode($row);
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		//check Entry Record in TRN tables
		$chk_arr[]=array("inquiry_id","tbl_inquiry","inquiry_status=0 and cust_id=".$POST['eid']);
		$chk_arr[]=array("task_id","tbl_task","task_status!=2 and cust_id=".$POST['eid']);
		$chk_resp=check_delete_trn($dbcon,$chk_arr);
		if($chk_resp){
			echo '-1';
		}
		else{
			$info['cust_status']		= 2;
			$updateid=update_record('tbl_customer', $info,"cust_id=".$POST['eid'] , $dbcon);
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"cust_add",3,"tbl_customer",$POST['eid']);				
			
			if($updateid)
				echo "1";	
			else
				echo "0";	
		}
	}
	else if(strtolower($POST['mode']) == "print_cust_label") {
		
		if($POST['cust_id'][0]=='ON'){ unset($POST['cust_id'][0]);}//unset first element if select all
		$_COOKIE['label_cust']=$_SESSION['label']['cust']=implode(",",$POST['cust_id']);
		
		setcookie("label_cust", $_COOKIE['label_cust'], time() + (86400 * 30), "/"); // 86400 = 1 day
		$row['status']=0;
		if(!empty($_COOKIE['label_cust']))
		{
			$row['status']=1;
		}
		echo json_encode($row);	
	}
	else if(strtolower($POST['mode']) == "load_state") {
		$countryid=$POST['id'];				
		$stateid=$POST['stateid'];				
		echo get_state($dbcon,$stateid,$countryid);
	}
	else if(strtolower($POST['mode']) == "load_city") {
		$cityid=$POST['id'];				
		echo $str=getcity($dbcon,$cityid,'');
	}
	else if(strtolower($POST['mode']) == "check_data"){
		$row[] ='';
		if(!empty($_FILES['excel_file']['tmp_name']))
		{
			$file_name = $_FILES['excel_file']['name'];
			$err = $_FILES["excel_file"]["tmp_name"];
			$exts = array('csv'); 
			if(in_array(end(explode('.', $file_name)), $exts))
			{
				move_uploaded_file($err,CUSTOMER_UPING.$file_name);
				$handle = fopen(CUSTOMER_UPING.$file_name, "r");
				$row = check_data($file_name,$dbcon);
			}
			else
			{
				$row['res'] = "-1";
			}
		}
		else
		$row['res'] ='0';
		echo json_encode($row);
	}
	else if(strtolower($POST['mode']) == "import_data"){
		if(!empty($_FILES['excel_file']['tmp_name']))
		{
			$file_name = $_FILES['excel_file']['name'];
			$err = $_FILES["excel_file"]["tmp_name"];
			move_uploaded_file($err,CUSTOMER_UPING.$file_name);
			$handle = fopen(CUSTOMER_UPING.$file_name, "r");
			($data = fgetcsv($handle,","));//get field rows
			$i=1;$error_array=array();
			while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
			{
				$error='';
				if(!empty($data['0']))
				{
					
					$info['company_name']=$data['0'];
					$info['cust_name']=$data['1'];
					$info['cust_address']=$data['2'];
					$qstate="SELECT `stateid`,`state_name` FROM `state_mst` WHERE state_status=0 and `state_name` ='".$data['3']."'";
					$tr_state = mysqli_fetch_array($dbcon -> query($qstate));
					if(!empty($tr_state))
					{
						$info['stateid']=$tr_state['stateid'];
					}				
					else
					{
						$error='State Name Not Found';
						array_push($error_array,1);
					}				
					$qcity="SELECT `cityid`,`city_name` FROM `city_mst` WHERE city_status=0 and `city_name` ='".$data['4']."'";
					$tr_city = mysqli_fetch_array($dbcon -> query($qcity));
					if(!empty($tr_city))
					{
						$info['cityid']=$tr_city['cityid'];
					}				
					else
					{
						$error='City Name Not Found';
						array_push($error_array,1);
					}				
					$info['opening_balance']=$data['5'];
					if($data['6']=="Cr")
					{
						$info['balance_typeid']=1;
					}
					else if($data['6']=="Dr")
					{
						$info['balance_typeid']=2;
					}
					else if(!empty($data['7']))
					{
						$error='Please Mention Cr/Dr';
						array_push($error_array,1);
					}
					$info['cust_mobile']=$data['7'];
					$info['cust_email']=strtolower($data['8']);
					$info['cust_pincode']=$data['9'];
					$info['gst_no']=$data['10'];
					$info['pan_no']=$data['11'];
					
					$qcity="SELECT `countryid`,`country_name` FROM `country_mst` WHERE country_status=0 and `country_name` ='".$data['12']."'";
					$tr_city = mysqli_fetch_array($dbcon -> query($qcity));
					if(!empty($tr_city)) {
						$info['countryid']=$tr_city['countryid'];
					}
					else {
						$error='Country Name Not Found';
						array_push($error_array,1);
					}
					$info['party_type']=$data['13'];
					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['usertype_id']	= $_SESSION['user_type'];
					$info['company_id']		= $_SESSION['company_id'];
					
					$q="SELECT `cust_name`,`company_name` FROM `tbl_customer` WHERE cust_status=0 and `company_id` ='".$_SESSION['company_id']."' and `company_name` ='".$info['company_name']."' ";
					$tr = $dbcon -> query($q);
					$cnt=mysqli_num_rows($tr);
					if($cnt>0 ) {
						$error='Company Already Added';
						array_push($error_array,1);
					}
					else if(!empty($error))
					{
						$err='error';
						array_push($error_array,1);
					}
					else
					{
						add_record('tbl_customer', $info, $dbcon);
					}
					
				}
				else
				{
					$error='Blank Row';
					array_push($error_array,1);
				}
				if(!empty($error))
				{
					
					$info1['line_num']=$i;
					$info1['error']=$error;
					$info1['company_id']=$_SESSION['company_id'];
					add_record('cust_tempdata', $info1, $dbcon);
				}
				$i++;	
			}
			if(in_array(1,$error_array))
			{
				$result['res']='5';
			}
			else
			{
				$result['res']='4';
			}	
			fclose($handle);//close file reading
			
		}
		else
		{$result['res']='0';}
		echo  json_encode($result);
	}
	else if(strtolower($POST['mode']) == "show_importedcustdata") {
		$temp_custqry='select * from cust_tempdata where company_id='.$_SESSION['company_id'];
		$temp_result=$dbcon->query($temp_custqry);
		if(mysqli_num_rows($temp_result)>0)
		{
			echo '<table  class="display table table-bordered table-striped">
			<tr>
			<td>Line Number</td>
			<td>Error</td>
			</tr>';
			
			
			while($temp_rel=mysqli_fetch_assoc($temp_result))
			{
				echo '<tr>';
				echo '<td>'.$temp_rel['line_num'].'</td>'; 
				echo '<td>'.$temp_rel['error'].'</td>'; 
				echo '</tr>';
			}
			echo '</table>';
		}
	} 
	else if(strtolower($POST['mode']) == "get_price_form_price_list") {
		
		$version_id = $POST['version_id'];
		$product_id = $POST['product_id'];

		$price = get_price_from_price_list($dbcon,$version_id,$product_id);

		echo json_encode($price);
	}
	else if(strtolower($POST['mode']) == "add_forecast_pr") {

		$forecast_pr_product_id = $POST['forecast_pr_product_id'];
		$price_list_version_pr_id = $POST['price_list_version_pr_id'];
		$forecast_amount_pr = $POST['forecast_amount_pr'];

		$info['forecast_pr_product_id'] = $forecast_pr_product_id;
		$info['price_list_version_pr_id'] = $price_list_version_pr_id;
		$info['forecast_amount_pr'] = $forecast_amount_pr;
		$info['forecast_pro_qty'] = $POST['forecast_pro_qty'];
		$info['forecast_pro_total'] = $POST['forecast_pro_total'];
		$info['forecast_cust_id'] = $POST['eid'];

		$info['cdate'] = date("Y-m-d H:i:s A");
		$info['company_id'] = $_SESSION['company_id'];
		$info['user_id'] = $_SESSION['user_id'];
		$info['usertype_id'] = $_SESSION['usertype_id'];

		if($POST['edit_id_fpr']=='')
		{
			$insertid=add_record('tbl_cust_forecast_pr',$info,$dbcon,'');

			if($insertid)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		else
		{
			$where=" forecast_pr_id='$POST[edit_id_fpr]'";
			$insertid = update_record('tbl_cust_forecast_pr',$info,$where,$dbcon, '');
			//echo $insertid;exit;
			if($insertid)
			{
				echo "3";
			}
			else
			{
				echo "0";
			}
		}

	}
	
	else if(strtolower($POST['mode']) == "add_forecast_pr_month") {

		$forecast_month = $POST['forecast_month'];
		$forecast_month_amount_pr = $POST['forecast_month_amount_pr'];
		$info['forecast_month'] = $forecast_month;
		$info['forecast_amount_pr'] = $forecast_month_amount_pr;
		$info['forecast_cust_id'] = $POST['eid'];
		$info['forecast_type'] = 1;
		$info['cdate'] = date("Y-m-d H:i:s A");
		$info['company_id'] = $_SESSION['company_id'];
		$info['user_id'] = $_SESSION['user_id'];
		$info['usertype_id'] = $_SESSION['usertype_id'];

		if($POST['edit_id_fpr_month']=='')
		{
			$insertid=add_record('tbl_cust_forecast_pr',$info,$dbcon,'');

			if($insertid)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		else
		{
			$where=" forecast_pr_id='$POST[edit_id_fpr_month]'";
			$insertid = update_record('tbl_cust_forecast_pr',$info,$where,$dbcon, '');
			//echo $insertid;exit;
			if($insertid)
			{
				echo "3";
			}
			else
			{
				echo "0";
			}
		}

	}


	else if(strtolower($POST['mode']) == "get_forecast_pr") {

		$eid = $POST['eid'];
		//echo $eid;exit;
		$str="";

		$str.="<table class='table table-bordered table-hover'>";

		$str.="<tr>
			<th>#</th>
			<th>Product</th>
			<th>Price List Version</th>
			<th>Price</th>
			<th>Qty</th>
			<th>Amount</th>
		</tr>";

		$sel = $dbcon->query("select fpr.*,pr.product_name,pl.price_list_version 
			from tbl_cust_forecast_pr as fpr 
			left join product_mst as pr on pr.product_id=fpr.forecast_pr_product_id 
			left join tbl_price_list as pl on pl.price_list_id = fpr.price_list_version_pr_id
			where fpr.forecast_cust_id='$eid' and fpr.isdelete='0' and fpr.forecast_type='0'");
		$cnt=1;
		while($r = brp_mysqli_fetch_assoc($sel))
		{
			$str.="<tr>

				<th>".$cnt."</th>
				<th>".$r['product_name']."</th>
				<th>".$r['price_list_version']."</th>
				<th>".$r['forecast_amount_pr']."</th>
				<th>".$r['forecast_pro_qty']."</th>
				<th>".$r['forecast_pro_total']."</th>
				<th>
					<button type='button' class='btn btn-round btn-success btn-xs' onclick='edit_forecast_pr(".$r['forecast_pr_id'].",\"tbl_cust_forecast_pr\",\"forecast_pr_id\");' id='fieldedit".$cnt."'><i class='fa fa-pencil'></i></button>

					<button type='button' class='btn btn-round btn-danger btn-xs' onclick='delete_forecast_pr(".$r['forecast_pr_id'].",\"tbl_cust_forecast_pr\",\"forecast_pr_id\");' id='fieldedit".$cnt."'><i class='fa fa-trash-o'></i></button>

				</th>

			</tr>";

			$cnt++;
		}

		$str.="</table>";

		echo $str;
	}


	else if(strtolower($POST['mode']) == "get_forecast_pr_month") {

		$eid = $POST['eid'];

		$str="";

		$str.="<table class='table table-bordered table-hover'>";

		$str.="<tr>
			<th>#</th>
			<th>Month</th>
			<th>Price</th>
		</tr>";

		$sel = $dbcon->query("select *
			from tbl_cust_forecast_pr
			where forecast_cust_id='$eid' and isdelete='0' and forecast_type='1'");
		$cnt=1;
		while($r = brp_mysqli_fetch_assoc($sel))
		{
			$str.="<tr>

				<th>".$cnt."</th>
				<th>".date("F",mktime(0,0,0,$r['forecast_month'],10))."</th>
				<th>".$r['forecast_amount_pr']."</th>
				<th>
					<button type='button' class='btn btn-round btn-success btn-xs' onclick='edit_forecast_pr_month(".$r['forecast_pr_id'].",\"tbl_cust_forecast_pr\",\"forecast_pr_id\");' id='fieldedit".$cnt."'><i class='fa fa-pencil'></i></button>

					<button type='button' class='btn btn-round btn-danger btn-xs' onclick='delete_forecast_pr_month(".$r['forecast_pr_id'].",\"tbl_cust_forecast_pr\",\"forecast_pr_id\");' id='fieldedit".$cnt."'><i class='fa fa-trash-o'></i></button>

				</th>

			</tr>";

			$cnt++;
		}

		$str.="</table>";

		echo $str;
	}

	//Address Details
	


	else if(strtolower($POST['mode']) == "add_cust_address") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		
		if($POST['edit_id']==''){
			$where = ' cust_id=0 and user_id='.$_SESSION['user_id'];
		}else{
			$where = ' cust_id='.$POST['cust_id'];
		}
		
		$info['c_add_address']	=$_POST['c_add_address'];
		$info['c_add_zip']		=$_POST['c_pincode'];
		$info['c_add_country']	=$POST['c_add_country'];
		$info['c_add_state']	=$POST['c_add_state'];
		$info['c_add_city']		=$POST['c_add_city'];
		$info['cust_id']		=$POST['cust_id'];
		
		$def_sel = "select * from tbl_cust_address where ".$where." and c_addr_defult=1";
		$def_ex=$dbcon->query($def_sel);
		if(mysqli_num_rows($def_ex)>0){
			if($POST['c_addr_defult'] == 1){
				$info_def['c_addr_defult'] = 0;
				update_record('tbl_cust_address',$info_def,$where,$dbcon, $branch_id);
			}
			$info['c_addr_defult']	=$POST['c_addr_defult'];
		}else{
			$info['c_addr_defult'] =1; 
		}
		
	//	$info['edit_id']		=$POST['edit_id'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		if($POST['edit_id']=='')
		{
			$inserid=add_record('tbl_cust_address', $info, $dbcon, $branch_id);
		}
		else
		{
			$inserid=update_record('tbl_cust_address',$info,"c_add_id=".$POST['edit_id'],$dbcon, $branch_id);
		}
		
		if($inserid)
		{
			echo "1";
		}
		else{
			echo "0";
		}
	}
	else if(strtolower($POST['mode']) == "show_cust_address") {
		$where = '';
		$cust_id=$POST['cust_id'];
		if(empty($POST['cust_id'])){
			$where = " and per.user_id='$_SESSION[user_id]' and company_id = ".$_SESSION['company_id'];
		}
		
		$sel=$dbcon->query("select per.*,country_name,state_name,city_name from tbl_cust_address as per
		left join country_mst as country on country.countryid=per.c_add_country
		left join state_mst as state on state.stateid=per.c_add_state
		left join city_mst as city on city.cityid=per.c_add_city
		where per.cust_id='$cust_id' $where ");
		$i=0;
		while($row=mysqli_fetch_assoc($sel))
		{
			echo '<tr>';
			echo '<td>'.$row['c_add_address'].'</td>';
			echo '<td>'.$row['c_add_zip'].'</td>';
			echo '<td>'.$row['country_name'].'</td>';
			echo '<td>'.$row['state_name'].'</td>';
			echo '<td>'.$row['city_name'].'</td>';
			if($row['c_addr_defult'] ==1){
				echo '<td style="color:green"><i>Default</i></td>';
			}else{
				echo '<td style="color:red"><i>Primary</i></td>';
			}
			echo '<td>
				
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_serial('.$row['c_add_id'].',\'tbl_cust_address\',\'c_add_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
				
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_serial('.$row['c_add_id'].',\'tbl_cust_address\',\'c_add_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				
			</td>';
			echo '</tr>';
			$i++;
		}
		echo '<input type="hidden" name="addre" id="addre" value="'.$i.'" />';
		
	}
	else if(strtolower($POST['mode']) == "view_cust_address") {
		$cust_id=$POST['cust_id'];$str='';
		
		$sel=$dbcon->query("select per.*,country_name,state_name,city_name from tbl_cust_address as per
		left join country_mst as country on country.countryid=per.c_add_country
		left join state_mst as state on state.stateid=per.c_add_state
		left join city_mst as city on city.cityid=per.c_add_city
		where per.cust_id=".$cust_id);
		$str.='<table class="display table table-bordered table-striped">
			<thead>
				<tr>
					<th>Sr No.</th>
					<th>Address</th>
					<th>Pincode</th>
					<th>Country</th>
					<th>State</th>
					<th>City</th>
					<th>Action</th>
				</tr>
			</thead>
		';
		if(mysqli_num_rows($sel)){
			$k=1;
			while($row=mysqli_fetch_assoc($sel))
			{
				$str.= '<tr>';
				$str.= '<td>'.$k.'</td>';
				$str.= '<td>'.$row['c_add_address'].'</td>';
				$str.= '<td>'.$row['c_add_zip'].'</td>';
				$str.= '<td>'.$row['country_name'].'</td>';
				$str.= '<td>'.$row['state_name'].'</td>';
				$str.= '<td>'.$row['city_name'].'</td>';
				
				$prep_add=$row['c_add_address'].' '.$row['city_name'].'-'.$row['c_add_zip'].', '.$row['state_name'].', '.$row['country_name'];
				$str.= '<td><button type="button" class="btn btn-primary" onclick="copy_address(\''.$prep_add.'\')">COPY</button></td>';
				$str.= '</tr>';
				$k++;
			}
		}
		else{
			$str.='<tr><td colspan="8">NO DATA FOUND!!!</td></tr>';
		}
		$str.='</table>';
		$resp['resp_html']=$str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "preedit_serial")
	{
		$q = $dbcon -> query("SELECT *  FROM ".$_POST['table']." WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	
	else if(strtolower($POST['mode'])== "delete_data_serial")
	{
		$row=array();
		
		delete_record($_POST['table'],$_POST['whereid']."=".$POST['eid'],$dbcon);
		//$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

		$row['res']="1";
		
		echo json_encode($row);
	}
	
	//Contact Details
	
	else if(strtolower($POST['mode']) == "add_cust_contact") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$info['c_con_fname']=$POST['con_first'];
		$info['c_con_lname']=$POST['con_last'];
		$info['c_con_email']=$POST['com_email'];
		$info['isd_id']		=$POST['con_isd_id'];
		$info['c_con_mobile']=$POST['con_mobile'];
		$info['c_con_phone']=$POST['con_phone'];
		$info['c_con_job']	=$POST['con_job'];
		if(strtolower($POST['cust_person_model'])=='model'){
			$info['cust_id']=$POST['cust_ref_id'];
		}
		else{
			$info['cust_id']=$POST['cust_id'];
		}
		
	//	$info['edit_id']=$POST['edit_id'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		
		if($POST['edit_con_id']=='')
		{
			$inserid=add_record('tbl_cust_contact', $info, $dbcon, $branch_id);
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"add_cust_contact",1,"tbl_cust_contact",$inserid);
		}
		else
		{
			$inserid=update_record('tbl_cust_contact',$info,"c_con_id=".$POST['edit_con_id'],$dbcon, $branch_id);
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"add_cust_contact",2,"tbl_cust_contact",$POST['edit_con_id']);
		}
		
		if($inserid)
		{
			if(strtolower($POST['cust_person_model'])=='model'){
				echo $inserid;
			}
			else{
				echo "1";
			}
		}
		else{
			echo "0";
		}
	}
	
	else if(strtolower($POST['mode']) == "show_cust_contact") {
		$where = '';
		$cust_id=$POST['cust_id'];
		if(empty($POST['cust_id'])){
			$where = " and user_id='$_SESSION[user_id]' and company_id = ".$_SESSION['company_id'];
		}
		
		$sel=$dbcon->query("select * from tbl_cust_contact where cust_id='$cust_id' $where ");
		
		while($row=mysqli_fetch_assoc($sel))
		{
			$isd_code  ='';
			if(!empty($row['isd_id'])){
				$isd_data = get_isd_data_mst($dbcon,$row['isd_id']);
				$isd_code = '+'.$isd_data['phonecode'].'-';
			}	
			echo '<tr>';
			echo '<td style="white-space: nowrap;">'.$row['c_con_fname'].'</td>';
			echo '<td style="white-space: nowrap;">'.$row['c_con_lname'].'</td>';
			echo '<td style="white-space: nowrap;">'.$row['c_con_email'].'</td>';
			echo '<td colspan="2" style="white-space: nowrap;">'.$isd_code.' '.$row['c_con_mobile'].'</td>';
			echo '<td style="white-space: nowrap;">'.$row['c_con_phone'].'</td>';
			echo '<td style="white-space: nowrap;">'.$row['c_con_job'].'</td>';
			echo '<td>
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_contact('.$row['c_con_id'].',\'tbl_cust_contact\',\'c_con_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
				
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_contact('.$row['c_con_id'].',\'tbl_cust_contact\',\'c_con_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				
			</td>';
			echo '</tr>';
		}
		
	}
	
	else if(strtolower($POST['mode'])== "preedit_contact")
	{
		$q = $dbcon -> query("SELECT *  FROM ".$_POST['table']." WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	
	else if(strtolower($POST['mode'])== "delete_data_contact")
	{
		$row=array();
		
		delete_record($_POST['table'],$_POST['whereid']."=".$POST['eid'],$dbcon);
		//$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"add_cust_contact",3,"tbl_cust_contact",$POST['eid']);
			
		$row['res']="1";
		
		echo json_encode($row);
	}
	
	//Customer Exist Details
	
	else if(strtolower($POST['mode']) == "add_cust_exist") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$info['c_ext_type']=$POST['ext_type'];
		$info['c_ext_product']=$POST['ext_product'];
		$info['c_ext_remark']=$POST['ext_remark'];
		
		$info['cust_id']=$POST['cust_id'];
	//	$info['edit_id']=$POST['edit_id'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		
		if($POST['edit_id']=='')
		{
			$inserid=add_record('tbl_cust_existing', $info, $dbcon, $branch_id);
		}
		else
		{
			$inserid=update_record('tbl_cust_existing',$info,"c_ext_id=".$POST['edit_id'],$dbcon, $branch_id);
		}
		
		if($inserid)
		{
			echo "1";
		}
		else{
			echo "0";
		}
	}
	
	else if(strtolower($POST['mode']) == "show_cust_exist") {
		
		$cust_id=$POST['cust_id'];
		
		$sel=$dbcon->query("select e.*,p.product_name from tbl_cust_existing as e left join tbl_product as p on p.product_id=e.c_ext_product where e.cust_id='$cust_id' and e.user_id='$_SESSION[user_id]'");
		
		while($row=mysqli_fetch_assoc($sel))
		{
			echo '<tr>';
			echo '<td>'.$row['c_ext_type'].'</td>';
			echo '<td>'.$row['product_name'].'</td>';
			echo '<td>'.$row['c_ext_remark'].'</td>';
			
			echo '<td>
				
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_exist('.$row['c_ext_id'].',\'tbl_cust_existing\',\'c_ext_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
				
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_exist('.$row['c_ext_id'].',\'tbl_cust_existing\',\'c_ext_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				
			</td>';
			echo '</tr>';
		}
		
	}
	
	else if(strtolower($POST['mode'])== "preedit_exist")
	{
		$q = $dbcon -> query("SELECT *  FROM ".$_POST['table']." WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	
	else if(strtolower($POST['mode'])== "delete_data_exist")
	{
		$row=array();
		
		delete_record($_POST['table'],$_POST['whereid']."=".$POST['eid'],$dbcon);
		//$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

		$row['res']="1";
		
		echo json_encode($row);
	}
	
	else if(strtolower($POST['mode'])== "load_country")
	{
		$country=$POST['country'];
	
		echo get_country($dbcon,$country);
	}
	else if(strtolower($POST['mode'])== "load_product")
	{
		$pid=$POST['pid'];
	
		echo getproduct($dbcon,$pid);
	}
	else if(strtolower($POST['mode'])== "load_cust_person"){
		$resp['html_resp'] = get_cust_contactperson($dbcon,"",$POST['cust_id']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "preview_cust_person"){
		$str='';
		$str.='<table class="display table table-bordered table-striped">
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Mobile</th>
					<th>Phone</th>
					<th>Job Title</th>
				</tr>';
				
		$per_qry="select * from tbl_cust_contact where c_con_status=0 and cust_id=".$POST['cust_id'];	
		$per_qry_rs=$dbcon->query($per_qry);
		if(mysqli_num_rows($per_qry_rs)){
			while($per_rel=mysqli_fetch_assoc($per_qry_rs)){
				$str.='<tr>
						<td>'.$per_rel['c_con_fname'].'</td>
						<td>'.$per_rel['c_con_lname'].'</td>
						<td>'.strtolower($per_rel['c_con_email']).'</td>
						<td>'.$per_rel['c_con_mobile'].'</td>
						<td>'.$per_rel['c_con_phone'].'</td>
						<td>'.$per_rel['c_con_job'].'</td>
					</tr>';
			}
		}
		else{
			$str.='<tr>
					<td colspan="6" class="text-center">Contact Person Not Found !!!</td>
				</tr>';
		}
		
		$str.='</table>';
		
		$resp['html_resp'] = $str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "preview_cust_dtls") {
		$cust_id=$POST['cust_id'];$str='';
		$cust_qry="select cust.* from tbl_customer as cust
		where cust.cust_id=".$cust_id;
		$cust_rel=mysqli_fetch_assoc($dbcon->query($cust_qry));
		
		$sel=$dbcon->query("select per.*,country_name,state_name,city_name 
                    from tbl_cust_address as per
		left join country_mst as country on country.countryid=per.c_add_country
		left join state_mst as state on state.stateid=per.c_add_state
		left join city_mst as city on city.cityid=per.c_add_city
		where per.cust_id='$cust_id'");
                //$result = brp_mysqli_query($dbcon,$parent_qry);
                $address_data = brp_mysqli_fetch_all($sel,MYSQLI_ASSOC);	
                
                $prep_add=$row['c_add_address'].' '.$row['city_name'].', '.$row['state_name'].', '.$row['country_name'].' - '.$row['c_add_zip'];
		
		$str.='<table class="display table table-bordered table-striped">
			<tr>
				<td colspan="4"><strong>Company Name:</strong> '.$cust_rel['cust_name'].'</td>
			</tr>
			<tr>
				<td colspan="2"><strong>Mobile:</strong> '.$cust_rel['cust_mobile'].'</td>
				<td colspan="2"><strong>Email:</strong> '.$cust_rel['cust_email'].'</td>
			</tr>
			<tr>
				<td colspan="4"><strong>Address:</strong> <br/>';
                            if($address_data){
                                $str .= '<table class="display table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                <th>City</th>
                                                <th>State</th>
                                                <th>Country</th>
                                                <th>ZIP code</th>
                                            </tr>
                                        </thead>';
                                    
                                foreach($address_data as $address){
                                    $addr = $address['c_add_address'];
                                    $str .= '<tr>
                                            <td>'.$addr.'</td>
                                            <td>'.$address['city_name'].'</td>
                                            <td>'.$address['state_name'].'</td>
                                            <td>'.$address['country_name'].'</td>
                                            <td>'.$address['c_add_zip'].'</td>
                                        </tr>';
                                }
                                $str .= '</table>';
                            } else {
                                echo 'No Address Found';
                            }
                $str.= '</td>
                    </tr>';
		$str.='</table>';
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "check_csv_data") {
		$row[] ='';
		if(!empty($_FILES['import_file']['tmp_name']))
		{
			$rand=rand(0,99999);
			$file_name = "cust_check_import".$rand.".csv";
			$ftmp = $_FILES["import_file"]["tmp_name"];
			move_uploaded_file($ftmp,CUSTOMER_UPING.$file_name);
			$row = check_data($file_name,$dbcon);
			unlink(CUSTOMER_UPING.$file_name);
			
		}
		else
			$row['res'] ='0';
		
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "import_csv_data") {
	
	if(!empty($_FILES['import_file']['tmp_name']))
	{
		$rand=rand(0,99999);
		$file_name = "cust_import".$rand.".csv";
		$err = $_FILES["import_file"]["tmp_name"];
		move_uploaded_file($err,CUSTOMER_UPING.$file_name);
		$handle = fopen(CUSTOMER_UPING.$file_name, "r");
		$i=1;$error_array=array();
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
		{
			$error=''; 
			if($data['0']){
				$tr = $dbcon -> query("SELECT cust_id FROM tbl_customer WHERE cust_name = '".trim($data['0'])."' and  cust_status=0 and company_id=".$_SESSION['company_id']);
				$num_rws=mysqli_num_rows($tr);
			}
			
			if($data['0'] && $num_rws=='0'){
				
				$info['party_type']			= $POST['party_type'];
				$info['cust_name']			= trim($data['0']);
				$info['cust_code']			= get_customer_code($dbcon);//Generate New Code
				$info['cust_code_series']	= get_customer_code_series($dbcon);
				
				//Party Category
				if($data['3']){
					$get_cat_qry="select cc_id from tbl_customer_category where cc_status=0 and cc_name='".trim($data['3'])."'";
					$get_cat_rel=mysqli_fetch_assoc($dbcon->query($get_cat_qry));
					$info['cust_cat']		= $get_cat_rel['cc_id'];
				}
				
				//Party Industry
				if($data['4']){
					$get_ind_qry="select ci_id from tbl_customer_industry where ci_status=0 and ci_name='".trim($data['4'])."'";
					$get_ind_rel=mysqli_fetch_assoc($dbcon->query($get_ind_qry));
					$info['cust_ind']		= $get_ind_rel['ci_id'];
				}
				
				//Party Source
				if($data['5']){
					$get_src_qry="select rb_id from tbl_refer_by where rb_status=0 and rb_name='".trim($data['5'])."'";
					$get_src_rel=mysqli_fetch_assoc($dbcon->query($get_src_qry));
					$info['cust_source']		= $get_src_rel['rb_id'];
				}
				
				$info['cust_gst']			= trim($data['6']);
				$info['cust_mobile']		= trim($data['7']);
				$info['cust_email']			= strtolower(trim($data['8']));
				$info['cust_desc']			= (trim($data['15']));
				
				$info['cust_creator']		= $_SESSION['user_id'];
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$inserid=add_record('tbl_customer', $info, $dbcon, $branch_id);
				
				//Add Contact Person
				if($data['1']){
					$infoper['c_con_fname']	= trim($data['1']);
					$infoper['c_con_lname']	= trim($data['2']);
					$infoper['cust_id']		= $inserid;
					$infoper['cdate']		= date("Y-m-d H:i:s");
					$infoper['user_id']		= $_SESSION['user_id'];
					$infoper['company_id']	= $_SESSION['company_id'];
					$infoper['branch_id']	= $_SESSION['branch_id'];
					$inserperid=add_record('tbl_cust_contact',$infoper ,$dbcon, $branch_id);
				}
				
				//Add Cust Address
				if($data['9']){
					$infoadd['c_add_location']	= trim($data['9']);
					$infoadd['c_add_street']	= trim($data['10']);
					
					//Get Country
					if($data['11']){
						$get_con_qry="select countryid from country_mst where country_status=0 and country_name='".trim($data['11'])."'";
						$get_con_rel=mysqli_fetch_assoc($dbcon->query($get_con_qry));
						$infoadd['c_add_country']		= $get_con_rel['countryid'];
					}
					//Get State
					if($data['12']){
						$get_sts_qry="select stateid from state_mst where state_status=0 and state_name='".trim($data['12'])."'";
						$get_sts_rel=mysqli_fetch_assoc($dbcon->query($get_sts_qry));
						$infoadd['c_add_state']		= $get_sts_rel['stateid'];
					}
					//Get City
					if($data['13']){
						$get_city_qry="select cityid from city_mst where city_status=0 and city_name='".trim($data['13'])."'";
						$get_city_rel=mysqli_fetch_assoc($dbcon->query($get_city_qry));
						$infoadd['c_add_city']		= $get_city_rel['cityid'];
					}
					
					$infoadd['c_add_zip']		= trim($data['14']);
					$infoadd['cust_id']		= $inserid;
					$infoadd['cdate']			= date("Y-m-d H:i:s");
					$infoadd['user_id']		= $_SESSION['user_id'];
					$infoadd['company_id']		= $_SESSION['company_id'];
					$inserid=add_record('tbl_cust_address', $infoadd, $dbcon, $branch_id);
				}
				
				
				$i++;
			}
		}
			
		$result['res']='1';
		fclose($handle);//close file reading
		unlink(CUSTOMER_UPING.$file_name);
	}
	else
	{ $result['res']='0'; }
	
		echo  json_encode($result);
		
	}
        else if(strtolower($POST['mode']) == "add_cust_relation") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$relation['relation']   = $POST['relation'];
		$relation['gender']     = $POST['gender'];
		$relation['birth_date'] = date("Y-m-d", strtotime($POST['birth_date']));
		$relation['anniversary_date'] = date("Y-m-d", strtotime($POST['anniversary_date']));
                $relation['status']	= 0;
		$relation['user_id']	= $_SESSION['user_id'];
		$relation['company_id']	= $_SESSION['company_id'];
                
		if($POST['edit_id']){
                    $relation['updated_at']	= date("Y-m-d H:i:s");
                    $insert_id = update_record('tbl_cust_relation',$relation,"relation_id=".$POST['edit_id'],$dbcon, $branch_id);
		}
		else{
                    $relation['cust_id']	= $POST['cust_id'];
                    $relation['created_at']	= date("Y-m-d H:i:s");
                    $insert_id = add_record('tbl_cust_relation', $relation, $dbcon, $branch_id);
		}
		
		if($insert_id)
		{
			echo "1";
		}
		else{
			echo "0";
		}
	}
	else if(strtolower($POST['mode']) == "show_cust_relation") {
		$where = '';
		if(empty($POST['cust_id'])){
			$where = " and relation.user_id='$_SESSION[user_id]' AND relation.company_id=".$_SESSION['company_id'];
		}

		$cust_id = $POST['cust_id'];
		
		$sel = $dbcon->query("select relation.* 
                    FROM tbl_cust_relation as relation
                    WHERE relation.cust_id='".$cust_id."' $where" );
		$i=0;
		while($row=mysqli_fetch_assoc($sel))
		{
                    $gender = array('Male','Female','Others');
                    
			echo '<tr>';
			echo '<td>'.$row['relation'].'</td>';
			echo '<td>'.$gender[$row['gender']].'</td>';
			echo '<td>'.date("d-M-Y", strtotime($row['birth_date'])).'</td>';
			echo '<td>'.date("d-M-Y", strtotime($row['anniversary_date'])).'</td>';
			echo '<td>
                            <button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_realtion('.$row['relation_id'].');" id="edit_relation_'.$row['relation_id'].'"><i class="fa fa-pencil"></i></button>
                            <button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_realtion('.$row['relation_id'].');" id="delete_relation_'.$row['relation_id'].'"><i class="fa fa-times"></i></button>
			</td>';
			echo '</tr>';
			$i++;
		}
		echo '<input type="hidden" name="relation_id" id="relation_id" value="'.$i.'" />';
		
	}
        else if(strtolower($POST['mode']) == "preedit_relation") {
		
		$relation_id = $POST['relation_id'];
		
		$query = $dbcon -> query("SELECT * FROM tbl_cust_relation WHERE relation_id = ".$relation_id);
		$data = $query->fetch_assoc();
		
                $relation = array();
                $relation['relation'] = $data['relation'];
                $relation['gender'] = $data['gender'];
                $relation['birth_date'] = date("d-m-Y", strtotime($data['birth_date']));
                $relation['anniversary_date'] = date("d-m-Y", strtotime($data['anniversary_date']));
                $relation['relation_id'] = $data['relation_id'];
                
		echo json_encode($relation);
	}
        else if(strtolower($POST['mode']) == "delete_relation") {
		
		$relation_id = $POST['relation_id'];
		
                if($relation_id){
                    $deleted = delete_record('tbl_cust_relation',"relation_id=".$relation_id,$dbcon);
                }
                
                if($deleted){
			echo "1";
		} else {
			echo "0";
		}
	}

	else if(strtolower($POST['mode']) == "show_consignee_details") {
		$where = '';
		if(empty($POST['cust_id'])){
			$where = " and per.user_id='$_SESSION[user_id]' and company_id = ".$_SESSION['company_id'];
		}
		$cust_id=$POST['cust_id'];
		
		$sel=$dbcon->query("select per.*,country.country_name,state.state_name,city.city_name from tbl_party_consignee as per
		left join country_mst as country on country.countryid=per.countryid
		left join state_mst as state on state.stateid=per.stateid
		left join city_mst as city on city.cityid=per.cityid
		where per.cust_ref_id='$cust_id' $where ");
		$i=0;
		while($row=mysqli_fetch_assoc($sel))
		{
			echo '<tr>';
			echo '<td>'.$row['company_name'].'</td>';
			echo '<td>'.$row['cust_name'].'</td>';
			echo '<td>'.$row['cust_mobile'].'</td>';
			echo '<td>'.$row['cust_email'].'</td>';
			echo '<td>'.$row['cust_address'].'</td>';
			echo '<td>'.$row['country_name'].'</td>';
			echo '<td>'.$row['state_name'].'</td>';
			echo '<td>'.$row['city_name'].'</td>';
			echo '<td>'.$row['gst_no'].'</td>';
			echo '<td>
				
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_consignee('.$row['cust_id'].',\'tbl_party_consignee\',\'cust_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
				
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_consignee('.$row['cust_id'].',\'tbl_party_consignee\',\'cust_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				
			</td>';
			echo '</tr>';
			$i++;
		}
		echo '<input type="hidden" name="consi" id="consi" value="'.$i.'" />';
		
	}else if(strtolower($POST['mode']) == "add_consignee") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$consignee['company_name']  = $POST['comp_name'];
        $consignee['cust_name']     = $POST['con_name'];
        $consignee['cust_mobile']   = $POST['con_mobile'];
        $consignee['cust_email']    = $POST['con_email'];
        $consignee['cust_address']  = stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['con_address']));//nl2br($POST['con_address']);
        $consignee['countryid']     = $POST['country_consinee_id'];
        $consignee['stateid']       = $POST['state_consinee_id'];
        $consignee['cityid']        = $POST['city_consinee_id'];
        $consignee['gst_no']        = $POST['gst_consinee_no'];
        $consignee['cust_ref_id']   = $POST['cust_id'];
        $consignee['user_id']       = $_SESSION['user_id'];
        
		$consignee['company_id']	= $_SESSION['company_id'];
                
		if($POST['edit_id']){
            $consignee['cdate']  	= date("Y-m-d h:i:s");
            $insert_id = update_record('tbl_party_consignee',$consignee,"cust_id=".$POST['edit_id'],$dbcon, $branch_id);
		}
		else{
            // $consignee['cust_id']	= $POST['cust_id'];
            $consignee['cdate']     = date("Y-m-d h:i:s");
            $insert_id = add_record('tbl_party_consignee', $consignee, $dbcon, $branch_id);
		}
		
		if($insert_id)
		{
			echo "1";
		}
		else{
			echo "0";
		}
	}else if(strtolower($POST['mode'])== "preedit_consignee")
	{
		$q = $dbcon -> query("SELECT *  FROM ".$_POST['table']." WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_consignee"){
            $deleteid=delete_record('tbl_party_consignee', "cust_id=".$POST['eid'], $dbcon);
            
			if($deleteid)	
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
    }
    else if(strtolower($POST['mode'])== "add_cust_dispatch"){

    	$transporter_name = $POST['transporter_name'];
    	$transporter_add = $POST['transporter_add'];
    	$transporter_type = $POST['transporter_type'];
    	$transporter_contact = $POST['transporter_contact'];
    	$edit_dispatch_id = $POST['edit_dispatch_id'];
    	$eid = $POST['eid'];

    	$info['transporter_name'] = $transporter_name;
    	$info['transporter_add'] = $transporter_add;
    	$info['transporter_type'] = $transporter_type;
    	$info['transporter_contact'] = $transporter_contact;
    	$info['transporter_cust_id'] = $eid;
    	$info['cdate'] = date("Y-m-d H:i:s A");
    	$info['user_id'] = $_SESSION['user_id'];
    	$info['company_id'] = $_SESSION['company_id'];
		

    	if($edit_dispatch_id > 0)
    	{
    		//echo $edit_dispatch_id;exit;
    		$updateid=update_record('tbl_cust_dispatch', $info,"transporter_dispatch_id=".$edit_dispatch_id , $dbcon, '');

    		//echo $updateid;exit;

    		if($updateid)
			{
				echo "3";
			}
			else{
				echo "0";
			}

    	}
    	else
    	{
			$insert_id = add_record('tbl_cust_dispatch', $info, $dbcon,'');


			if($insert_id)
			{
				echo "1";
			}
			else{
				echo "0";
			}
		}


    }
    else if(strtolower($POST['mode'])== "edit_cust_dispatch"){

    	$eid = $POST['eid'];

    	$sel = $dbcon->query("select * from tbl_cust_dispatch where transporter_dispatch_id='$eid'");
    	$row = brp_mysqli_fetch_assoc($sel);
    	echo json_encode($row);

    }
    else if(strtolower($POST['mode'])== "load_cust_dispatch"){

    	$eid = $POST['eid'];

    	$str="";

    	$str.="<table class='table table-bordered table-hover'>";

    	$str.="<tr>

    		<th>#</th>
    		<th>Transporter Name</th>
    		<th>Transporter Address</th>
    		<th>Transporter Contact</th>
    		<th>Transporter Type</th>
    		<th>Action</th>
    	</tr>";

    	$sel = $dbcon->query("select * from tbl_cust_dispatch where transporter_cust_id='$eid' and isdelete='0'");
    	$cnt=1;
    	while($row=brp_mysqli_fetch_assoc($sel))
    	{

    		if($row['transporter_type']=='1')
    		{
    			$trans_type = "Road <i class='fa fa-road'></i>";
    		}
    		else if($row['transporter_type']=='2')
    		{
    			$trans_type = "Rail <i class='fa fa-train'></i>";
    		}
    		else if($row['transporter_type']=='3')
    		{
    			$trans_type = "Air <i class='fa fa-plane'></i>";
    		}
    		else if($row['transporter_type']=='4')
    		{
    			$trans_type = "Bus <i class='fa fa-bus'></i>";
    		}

			$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top"  onClick="edit_cust_dispatch('.$row['transporter_dispatch_id'].')"><i class="fa fa-pencil"></i></a>'; 

			
			$delete_btn=' <a class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust_dispatch('.$row['transporter_dispatch_id'].')"><i class="fa fa-trash-o"></i></a>'; 
			

    		$str.="<tr>

    			<th>".$cnt."</th>
    			<td>".$row['transporter_name']."</td>
    			<td>".$row['transporter_add']."</td>
    			<td>".$row['transporter_contact']."</td>
    			<td>".$trans_type."</td>
    			<td>
    				<a>".$edit_btn." ".$delete_btn."</a>
    			</td>
    		</tr>";

    		$cnt++;

    	}

    	$str.="</table>";

    	echo $str;
    }
    else if(strtolower($POST['mode'])== "delete_cust_dispatch"){

    	$did = $POST['did'];

    	$info['isdelete'] = 1;

    	$updateid=update_record('tbl_cust_dispatch', $info,"transporter_dispatch_id=".$did , $dbcon, '');

    	if($updateid)
    	{
    		echo "1";
    	}
    	else{
    		echo "0";
    	}
    }
    else if(strtolower($POST['mode'])== "add_competitor"){

    	$comp_name = $POST['comp_name'];
    	$comp_add = $POST['comp_add'];
    	$comp_email = $POST['comp_email'];
    	$comp_mobile = $POST['comp_mobile'];
    	$edit_comp_id = $POST['edit_comp_id'];

    	$info['comp_name'] = $comp_name;
    	$info['comp_add'] = $comp_add;
    	$info['comp_email'] = $comp_email;
    	$info['comp_mobile'] = $comp_mobile;
		$info['cdate'] = date("Y-m-d H:i:s A");
    	$info['user_id'] = $_SESSION['user_id'];
    	$info['company_id'] = $_SESSION['company_id'];
    	$info['comp_cust_id'] = $POST['eid'];

    	if($edit_comp_id > 0)
    	{
    		//echo $edit_dispatch_id;exit;
    		$updateid=update_record('tbl_cust_competitor', $info,"comp_id=".$edit_comp_id , $dbcon, '');

    		//echo $updateid;exit;

    		if($updateid)
			{
				echo "3";
			}
			else{
				echo "0";
			}

    	}
    	else
    	{
			$insert_id = add_record('tbl_cust_competitor', $info, $dbcon,'');


			if($insert_id)
			{
				echo "1";
			}
			else{
				echo "0";
			}
		}


    }
    else if(strtolower($POST['mode'])== "load_cust_competitor"){

    	$eid = $POST['eid'];

    	$str="";

    	$str.="<table class='table table-bordered table-hover table-striped'>";

    		$str.="<tr>

    			<th>#</th>
    			<th>Name</th>
    			<th>Address</th>
    			<th>Email</th>
    			<th>Mobile</th>
    			<th>Action</th>

    		</tr>";

    		$sel = $dbcon->query("select * from tbl_cust_competitor where comp_cust_id='$eid' and isdelete='0'");
    		$cnt=1;
	    	while($row = brp_mysqli_fetch_assoc($sel))
	    	{

	    		$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top"  onClick="edit_cust_competitor('.$row['comp_id'].')"><i class="fa fa-pencil"></i></a>'; 

			
				$delete_btn=' <a class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cust_competitor('.$row['comp_id'].')"><i class="fa fa-trash-o"></i></a>'; 


				$add_btn=' <a class="btn btn-xs btn-success" data-original-title="Add Product" data-toggle="tooltip" data-placement="top" onClick="add_comp_product('.$row['comp_id'].')"><i class="fa fa-plus"></i></a>'; 

	    		$str.="<tr>

	    			<th>".$cnt."</th>
	    			<th>".$row['comp_name']."</th>
	    			<th>".$row['comp_add']."</th>
	    			<th>".$row['comp_email']."</th>
	    			<th>".$row['comp_mobile']."</th>
	    			<th>".$edit_btn." ".$delete_btn." ".$add_btn."</th>

	    		</tr>";

	    		$cnt++;
	    	}

    	$str.="</table>";

    	echo $str;
    }
    else if(strtolower($POST['mode'])== "edit_cust_competitor"){

    	$eid = $POST['eid'];

    	$sel = $dbcon->query("select * from tbl_cust_competitor where comp_id='$eid'");
    	$row = brp_mysqli_fetch_assoc($sel);
    	echo json_encode($row);

    }
    else if(strtolower($POST['mode'])== "delete_cust_competitor"){

    	$did = $POST['did'];

    	$info['isdelete'] = 1;

    	$updateid=update_record('tbl_cust_competitor', $info,"comp_id=".$did , $dbcon, '');

    	if($updateid)
    	{
    		echo "1";
    	}
    	else{
    		echo "0";
    	}
    }
    else if(strtolower($POST['mode'])== "load_product_typeiwse")
	{
		echo get_product($dbcon,"",$POST['type_id']);
	}
	else if(strtolower($POST['mode'])== "load_comp_product")
	{
		$comp_id = $POST['comp_id'];

		$sel = $dbcon->query("select comp.*,pro.product_name,pt.product_type_name 
			from tbl_cust_comp_product as comp 
			left join product_mst as pro on pro.product_id = comp.comp_product_id
			left join pro_ms_product_type as pt on pt.product_type_id = comp.comp_product_type_sel
			where isdelete='0' and cust_comp_id='$comp_id'");

		$str="<table class='table table-bordered table-hover table-striped'>
			<tr>
				<th>#</th>
				<th>Product Type</th>
				<th>Product Name</th>
				<th>Product Price</th>
				<th>Remark</th>
				<th>Action</th>
			</tr>";

		if(brp_mysqli_num_rows($sel)>0)
		{
			$cnt=1;
			while($row=brp_mysqli_fetch_assoc($sel)){

				$edit = "<a class='btn btn-success btn-sm' onclick='edit_comp_product(".$row['cust_comp_product_id'].")'><i class='fa fa-pencil'></i></a>";

				$delete = "<a class='btn btn-danger btn-sm' onclick='delete_comp_product(".$row['cust_comp_product_id'].",".$row['cust_comp_id'].")'><i class='fa fa-trash-o'></i></a>";

				$str.="<tr>
					<th>".$cnt."</th>
					<th>".$row['product_type_name']."</th>
					<th>".$row['product_name']."</th>
					<th>".$row['comp_product_price']."</th>
					<th>".$row['comp_prudct_remark']."</th>
					<th>".$edit." ".$delete."</th>
				</tr>";	

				$cnt++;

			}
		}
		else
		{
			$str.="<tr>
				<th colspan='5'>No Data Found</th>
			</tr>";
		}

		echo $str;


	}

	else if(strtolower($POST['mode'])== "edit_comp_product")
	{
		$eid = $POST['eid'];

		$sel = $dbcon->query("select * from tbl_cust_comp_product where cust_comp_product_id='$eid'");
		$row = brp_mysqli_fetch_assoc($sel);

		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "delete_comp_product")
	{
		$did = $POST['did'];

		$info['isdelete']=1;

		$updateid=update_record('tbl_cust_comp_product', $info,"cust_comp_product_id=".$did , $dbcon, '');

		if($updateid)
		{
			echo "1";
		}
		else
		{
			echo "0";
		}

	}
	else if(strtolower($POST['mode'])== "add_comp_modal_produdct")
	{
		$comp_product_type_sel = $POST['comp_product_type_sel'];
		$comp_product_id = $POST['comp_product_id'];
		$comp_product_price = $POST['comp_product_price'];
		$comp_prudct_remark = $POST['comp_prudct_remark'];
		$comp_id = $POST['comp_id'];
		$cust_comp_product_id = $POST['cust_comp_product_id'];

		$info['comp_product_type_sel'] = $comp_product_type_sel;
		$info['cust_comp_id'] = $comp_id;
		$info['comp_product_id'] = $comp_product_id;
		$info['comp_product_price'] = $comp_product_price;
		$info['comp_prudct_remark'] = $comp_prudct_remark;
		$info['cdate'] = date("Y-m-d H:i:s A");
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];

		if($cust_comp_product_id > 0)
		{
			$updateid=update_record("tbl_cust_comp_product",$info," cust_comp_product_id=".$POST['cust_comp_product_id'] ,$dbcon);

			if($updateid)
			{
				echo "3";
			}
			else
			{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_cust_comp_product', $info, $dbcon, $branch_id);

			if($inserid)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
	}
	else if(strtolower($POST['mode'])== "preedit_forecast")
	{
		$q = $dbcon -> query("SELECT *  FROM ".$_POST['table']." WHERE ".$_POST['whereid']." = '$POST[id]'");
		$r = $q->fetch_assoc();
		
		echo json_encode($r);
	}
	else if(strtolower($POST['permission_mode'])== "add"){
		$info['crm_partymst_cust_name'] = $POST['crm_partymst_cust_name'];
		$info['crm_partymst_cust_mobile'] = $POST['crm_partymst_cust_mobile'];
		$info['crm_partymst_cust_email'] = $POST['crm_partymst_cust_email'];
		$info['crm_partymst_cust_gst'] = $POST['crm_partymst_cust_gst'];
		$info['crm_partymst_cust_iec'] = $POST['crm_partymst_cust_iec'];
		$info['crm_partymst_cust_pan'] = $POST['crm_partymst_cust_pan'];
		$info['crm_partymst_cust_cat'] = $POST['crm_partymst_cust_cat'];
		$info['crm_partymst_cust_type'] = $POST['crm_partymst_cust_type'];
		$info['crm_partymst_cust_ind'] = $POST['crm_partymst_cust_ind'];
		$info['crm_partymst_cust_source'] = $POST['crm_partymst_cust_source'];
		$info['crm_partymst_t_id'] = $POST['crm_partymst_t_id'];
		$info['crm_partymst_c_add_address'] = $POST['crm_partymst_c_add_address'];
		$info['crm_partymst_c_add_country'] = $POST['crm_partymst_c_add_country'];
		$info['crm_partymst_c_add_state'] = $POST['crm_partymst_c_add_state'];
		$info['crm_partymst_c_add_city'] = $POST['crm_partymst_c_add_city'];
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];

		if(!empty($POST['permission_id']))
		{
			$updateid=update_record("tbl_page_permission",$info," permission_id=".$POST['permission_id'] ,$dbcon);
		}
		else
		{
			$updateid=add_record('tbl_page_permission', $info, $dbcon, $branch_id);
		}
		if($updateid)
		{
			if($POST['permission_modal']=='model'){
				$row['res'] = "update";
			}else{
				$row['res'] = "2";
			}
		}
		else
		{
			$row['res'] = "0";
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "add_ledger_doc_field") {
	    /*var_dump($_POST);
	    var_dump($_FILES);*/
	    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	    $info1['ref_type']				= 1;
	    $info1['led_doc_name']   		= $POST['led_doc_name'];
	    $info1['led_attch_file']		= upload_attch_file($_FILES);
	    $info1['user_id']				= $_SESSION['user_id'];
	    $info1['company_id']			= $_SESSION['company_id'];

	    $table='tbl_ledger_attach_doc';$tableid='led_attach_id';
	    if(!empty($POST['l_id'])) {
	        $info1['cust_id']	= $POST['l_id'];
	    }
	    else{
	        $info1['led_attach_status']	= 3;
	    }

	    $inserid=add_record($table, $info1, $dbcon, $branch_id);

	    if($inserid){
	        echo "1";
	    }else{
	        echo "0";
	    }
	}
	else if(strtolower($POST['mode'])== "show_led_attach_data") {
	    $chkmode=$POST['modee'];
		$bulkAccessArray = is_array($bulkAccessArray) ? $bulkAccessArray : [];
		$delete_btn_per = in_array(CUSTOMER_PARTY_MASTER_SLUG_DELETE, $bulkAccessArray);

	    if($POST['l_id']){
	        $query="select mst.* from tbl_ledger_attach_doc as mst 
	        where mst.led_attach_status=0 and mst.cust_id=".$POST['l_id'];
	    }
	    else{
	        $query="select mst.* from tbl_ledger_attach_doc as mst 
	        where mst.led_attach_status=3 and mst.ref_type=1 and mst.user_id=".$_SESSION['user_id'];
	    }
	    $result=$dbcon->query($query);
	    echo '<table class="display table table-bordered table-striped">
	    <tr>
	    <th width="60%" class="text-center">Document Name</th>
	    <th width="30%" class="text-center">Attached Document</th>';
	    
        echo'<th width="10%" class="text-center">Action</th>';
	   
	    echo'</tr>
	    <tbody>';
	    if(mysqli_num_rows($result)>0)
	    {
	        $i=1;
	        while($rel=mysqli_fetch_assoc($result))
	        {
	            echo '<tr> 
	            <td style="vertical-align:top;">
	            <strong>'.$rel['led_doc_name'].'</strong>
	            </td>
	            <td style="vertical-align:top;" class="text-center">
	            <a href="'.ROOT.LED_ATTACH_VWING.$rel['led_attch_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
	            </td>';
	            
                echo '<td style="vertical-align:top"> 
	                    <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_led_attach_data('.$rel['led_attach_id'].')">X</button>
	                    </td>';
	            
	            echo '</tr>';
	            $i++;
	        }
	    }
	    else{
	        echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
	    }

	    echo '</tbody>
	    </table>';
	}
	else if(strtolower($POST['mode'])== "delete_led_attach_data") {
	    $row=array();
	    $del_attch_qry="select led_attch_file from tbl_ledger_attach_doc where led_attach_id=".$POST['led_attach_id'];
	    $del_attch_rel=mysqli_fetch_assoc($dbcon->query($del_attch_qry));

	    /*var_dump('..//'.LED_ATTACH_UPING.$del_attch_rel['led_attch_file']);*/
	    unlink('..//'.LED_ATTACH_UPING.$del_attch_rel['led_attch_file']);

	    $info['led_attach_status']=2;	
	    $updateid=update_record('tbl_ledger_attach_doc', $info, "led_attach_id=".$POST['led_attach_id'] , $dbcon);

	    if($updateid)
	        $row['res']="1";
	    else
	        $row['res']="0";
	    echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "check_mobile_no")
	{
		if(strtolower($POST['form_mode'])=='edit'){
			$where = " and cust_id !=".$POST['customer_id'];
		}
		$query2="SELECT cust_mobile FROM tbl_customer WHERE cust_mobile='".$POST['mobile_no']."' and cust_status =0".$where;
		//echo $query2;exit;
		$rows2=brp_mysqli_fetch_assoc($dbcon->query($query2));
		if(!empty($rows2['cust_mobile'])){
			$row['error'] = 'Customer Mobile is already exists, please try another one';
		}
		echo json_encode($row);

	}
	else if(strtolower($POST['mode'])== "copy_led"){
		//var_dump($POST['cust_id']);
		$cust_id=copy_ledger_cust($dbcon,"",$POST['cust_id']);
		if(empty($cust_id)){
			echo 0;
		}else{
			echo 1;
		}
	}
	else if(strtolower($POST['mode']) == "load_typeswise_terms_dom") {
		$quot_type=$POST['quot_type'];
		$cust_id=$POST['cust_id'];
		$str='';
		$str.='<table class="display table table-bordered table-striped">
		<thead>
		<tr>
		<th width="5%" class="text-center">
		<input type="checkbox" class="check_all_terms_dom" style="height: 20px;width: 20px;" id="check_all_terms_dom" name="check_all_terms_dom" onClick="terms_check_all_dom(this);">
		</th>
		<th width="25%" class="text-center">Term Name</th>
		<th width="5%" class="text-center">Priority</th>
		<th width="65%" class="text-center">Term And Condition</th>				  
		</tr>
		</thead>
		<tbody>';
		
		//Get All Terms
		$terms_qry="select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(".$quot_type.",tc_for) order by tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);$t=1;
		while($terms_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$tc_priority=$terms_rel['tc_priority'];
			$tc_details=$terms_rel['tc_details'];
			if($cust_id){
				$quot_term_qry="select * from tbl_customer_term_trn where customer_terms_trn_status=0 and cust_id=".$cust_id." and tc_for=".$quot_type." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
			}

			$str.='<tr>
			<td width="5%" class="text-center">
			<input type="checkbox" class="terms_checkbox_dom" style="height: 20px;width: 20px;" id="disp_term_flag_dom'.$t.'" name="disp_term_flag_dom[]" value="'.$terms_rel['tc_id'].'" '.(($terms_rel['tc_id']==$quot_term_rel['tc_id'])?'checked':'').'>
			<input type="hidden" id="tc_id_dom'.$t.'" name="tc_id_dom[]" value="'.$terms_rel['tc_id'].'">
			</td>
			<td>'.$terms_rel['tc_name'].'</td>
			<td>
			<input type="number" class="form-control" min="0" id="tc_priority_dom'.$t.'" name="tc_priority_dom[]" value="'.$tc_priority.'">
			</td>';
			if($terms_rel['tc_allow']){
				$str .= '<td>
				<textarea class="form-control" id="tc_details_dom'.$t.'" name="tc_details_dom[]">'.$tc_details.'</textarea>
				</td>';
			} else {
				$str .= '<td>
				<textarea class="form-control" id="tc_details_dom'.$t.'" name="tc_details_dom[]" readonly>'.$tc_details.'</textarea>
				</td>';
			}
			$str .= '</tr>';

			$t++;
		}	  

		$str.='</tbody> 
		</table>';	  

		$resp['resp_html']=$str;
		echo json_encode($resp);
	}

	else if(strtolower($POST['mode']) == "load_typeswise_terms_exp") {
		$quot_type=$POST['quot_type'];
		$cust_id=$POST['cust_id'];
		$str='';
		$str.='<table class="display table table-bordered table-striped">
		<thead>
		<tr>
		<th width="5%" class="text-center">
		<input type="checkbox" class="check_all_terms_exp" style="height: 20px;width: 20px;" id="check_all_terms_exp" name="check_all_terms_exp" onClick="terms_check_all_exp(this);">
		</th>
		<th width="25%" class="text-center">Term Name</th>
		<th width="5%" class="text-center">Priority</th>
		<th width="65%" class="text-center">Term And Condition</th>				  
		</tr>
		</thead>
		<tbody>';
		
		//Get All Terms
		$terms_qry="select * from tbl_terms_condition where tc_status=0 and tc_category=1 and find_in_set(".$quot_type.",tc_for) order by tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);$t=1;
		while($terms_rel=mysqli_fetch_assoc($terms_qry_rs)){
			$tc_priority=$terms_rel['tc_priority'];
			$tc_details=$terms_rel['tc_details'];
			if($cust_id){
				$quot_term_qry="select * from tbl_customer_term_trn where customer_terms_trn_status=0 and cust_id=".$cust_id." and tc_for=".$quot_type." and tc_id=".$terms_rel['tc_id']."";
				$quot_term_rel=mysqli_fetch_assoc($dbcon->query($quot_term_qry));
				if($quot_term_rel['tc_priority']){
					$tc_priority=$quot_term_rel['tc_priority'];
				}
				if($quot_term_rel['tc_details']){
					$tc_details=$quot_term_rel['tc_details'];
				}
			}

			$str.='<tr>
			<td width="5%" class="text-center">
			<input type="checkbox" class="terms_checkbox_exp" style="height: 20px;width: 20px;" id="disp_term_flag_exp'.$t.'" name="disp_term_flag_exp[]" value="'.$terms_rel['tc_id'].'" '.(($terms_rel['tc_id']==$quot_term_rel['tc_id'])?'checked':'').'>
			<input type="hidden" id="tc_id_exp'.$t.'" name="tc_id_exp[]" value="'.$terms_rel['tc_id'].'">
			</td>
			<td>'.$terms_rel['tc_name'].'</td>
			<td>
			<input type="number" class="form-control" min="0" id="tc_priority_exp'.$t.'" name="tc_priority_exp[]" value="'.$tc_priority.'">
			</td>';
			if($terms_rel['tc_allow']){
				$str .= '<td>
				<textarea class="form-control" id="tc_details_exp'.$t.'" name="tc_details_exp[]">'.$tc_details.'</textarea>
				</td>';
			} else {
				$str .= '<td>
				<textarea class="form-control" id="tc_details_exp'.$t.'" name="tc_details_exp[]" readonly>'.$tc_details.'</textarea>
				</td>';
			}
			$str .= '</tr>';

			$t++;
		}	  

		$str.='</tbody> 
		</table>';	  

		$resp['resp_html']=$str;
		echo json_encode($resp);
	}


function check_data($filename,$dbcon)
{
	$error=array();
	$arr = explode(".", $filename);
	$fp = fopen(CUSTOMER_UPING.$filename, 'r');
	$frow = fgetcsv($fp);
	$frow =array_map('trim', $frow);
	if(count($frow)==16) // Define column count Here
	{
		$msg='';
		if ($frow[0] !== 'Company Name' || $frow[1] !== 'First Name' || $frow[2] !== 'Last Name' || $frow[3] !== 'Party Category' || $frow[4] !== 'Party Industry' || $frow[5] !== 'Source' || $frow[6] !== 'Gst No' || $frow[7] !== 'Mobile'  || $frow[8] !== 'E-mail' || $frow[9] !== 'Location' || $frow[10] !== 'Street' || $frow[11] !== 'Country' || $frow[12] !== 'State' || $frow[13] !== 'City' || $frow[14] !== 'Postal Code' || $frow[15] !== 'Notes' ) {
			$error['res']="2";
		}
		else {
			$error['res']="1";
		}
	}
	else
	{
		$error['res']="2";
	}
	return $error;
}
function generate_party_code($dbcon,$party_type){
	$sel_qry="select max(cust_id) as max_cust_id from tbl_customer";
	$sel_rel=mysqli_fetch_assoc($dbcon->query($sel_qry));
	$id = intval($sel_rel['max_cust_id'])+1;
	if($party_type=='1'){
		$pref='C';
	}
	else if($party_type=='2'){
		$pref='V';
	}
	else if($party_type=='3'){
		$pref='JV';
	}
	else{
		$pref='B';
	}
	$party_code = $pref.str_pad($id,4,"0",STR_PAD_LEFT);
	return $party_code;
}

function upload_attch_file($FILES){
    $rand=rand(0,99999999);
    if(!empty($FILES['led_attch_file']['tmp_name'])) {
        $temp = explode(".", $FILES["led_attch_file"]["name"]);
        $extension = strtolower(end($temp));
        $File = "led_attch_".$rand.".".$extension;
        $tmp_name = $FILES["led_attch_file"]["tmp_name"];
        move_uploaded_file($tmp_name,'..//'.LED_ATTACH_UPING.$File);

        return  $File;				
    }
}	

?>