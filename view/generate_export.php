<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../config/security.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");


	$delimiter = ",";
	$f = fopen('php://memory', 'w');

	if ($_REQUEST['mode'] == "crm_daily_report") {

		$filename = "daily_report_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Description','Date','User Name');

		fputcsv($f, $fields, $delimiter);

		if ($_REQUEST['start_date'] && $_REQUEST['end_date']) {
			$_SESSION['start'] = $start_date = $_REQUEST['start_date'];
			$_SESSION['end'] = $end_date = $_REQUEST['end_date'];
		} else if (isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])
		) {
			$start_date = $_SESSION['summary_start_date'];
			$end_date = $_SESSION['summary_end_date'];
		} else {
			$start_date = date('1-m-Y');
			$end_date = date("d-m-Y");
		}

		$where = '';
		if (!empty($start_date) && !empty($end_date)) {
			$where .= "  drt.date BETWEEN '" . date('Y-m-d', strtotime($start_date)) . "' AND '" . date('Y-m-d', strtotime($end_date)) . "'";

		}
		$appData = array();
		$i = 1;
		$aColumns = array('drt.r_id', 'drt.user_id', 'u.user_name', 'drt.date', 'drt.description');
		$date = date_create($row['date']);

		$sIndexColumn = "drt.r_id";

		if ($_REQUEST['uid']) {
			$user_ids = $_REQUEST['uid'];
		} else {
			$user_ids = check_user_chein($dbcon,$_SESSION['user_id'],1);		
		}
		$where .= " and drt.user_id IN ($user_ids)";

		$isWhere = array($where . " AND status = 0");
		

		$sTable = "daily_report as drt";
		$isJOIN = array("LEFT JOIN users AS u ON u.user_id = drt.user_id");
		$hOrder = "drt.r_id ";
		include('../include/pagging.php');

		$appData = array();
		$id = 1;
		foreach ($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$cleanContent = stripcslashes(mysqli_real_escape_string($dbcon, $row['description']));
			$row_data[] = strip_tags(remove_special_char($cleanContent));
			$row_data[] = date("d-m-Y", strtotime($row['date']));
			$row_data[] = $row['user_name'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

	} else if ($_REQUEST['mode'] == "purchase_order_list") {

		$filename = "purchase_order_list_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','PO No','PO Date','Vendor Name','Branch Name','City','Grand Total','Username','Approval Status');

		fputcsv($f, $fields, $delimiter);

		$s_date = explode(' - ', $_REQUEST['date']);

		$where = '';
		$where .= " and po_type_status=1";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($_REQUEST['branch_id']) && $_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('po', $branch_id);
		$where .= " $where_db ";

		$where_company = check_company('po');

		$where .= " $where_company";

		$vender_id = "";
		$filt_status = $_REQUEST['filt_status'];

		if(!empty($_REQUEST['vender_id']) && $_REQUEST['vender_id'] > 0){
			$where .= " and po.vender_id = " . $_REQUEST['vender_id'];
		}

		if($filt_status != "" && $filt_status > 0){

			if($filt_status == 1){
				$where .= " and po_approval_status = 0";
			}

			if($filt_status == 2){
				$where .= " and po_approval_status = 1";
			}

			if($filt_status == 3){
				$where .= " and po_approval_status = 1 and po_aproove_finance = 0";
			}

			if($filt_status == 4){
				$where .= " and po_approval_status = 1 and revise_status = 1";
			}
			if($filt_status == 5){
				$where .= " and aproove_status = 0";
			}
			if($filt_status == 6){
				$where .= " and short_close_status=0 and aproove_status=1";
			}
		}

		$where .= "  and purchaseorder_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND purchaseorder_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";
		$appData = array();
		$i = 1;
		$aColumns = array('po.purchaseorder_id','lpsc.short_close_status','lpsc.aproove_status', 'purchaseorder_no', 'l.l_name', 'city.city_name', 'bms.branch_name', 'purchaseorder_date', 'g_total', 'paid_amount', 'status', 'purchase_status', 'quot_type', 'po.cdate', 'po.userid', 'po.po_type_status', 'po.po_req_status', 'po_approval_status', 'po.branch_id', 'po.revise_status', 'us.user_name');
		$sIndexColumn = "po.purchaseorder_id";
		$isWhere = array("status = 0" . $where);
		$sTable = "tbl_purchaseorder as po";
		$isJOIN = array('left join tbl_ledger as l on po.vender_id=l.l_id', 
		'left join  tbl_log_po_short_close as lpsc on lpsc.po_id=po.purchaseorder_id', 
		'left join  city_mst city on l.cityid=city.cityid', 
		'left join branch_mst as bms on bms.branch_id=po.branch_id', 'left join users as us on us.user_id=po.userid');
		$hOrder = "po.purchaseorder_id desc";
		include('../include/pagging.php');

		$id = 1;
		$g_total_sum = 0;
		foreach ($sqlReturn as $row) {
			$row_data = array();

			$query = "select sum(g_total) as total_purchase,sum((select sum(product_amount) from tbl_purchaseordertrn as ptr where ptr.purchaseorder_id = po.purchaseorder_id and ptr.purchaseordertrn_status=0 )) as taxable_amt from tbl_purchaseorder as po	where  po.status=0 and po.purchaseorder_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND po.purchaseorder_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";

			$result = $dbcon->query($query);
			$res = brp_mysqli_fetch_array($result);

			$row_data[] = $id;
			$row_data[] = $row['purchaseorder_no'];
			$row_data[] = date('d M, Y', strtotime($row['purchaseorder_date']));
			$row_data[] = $row["l_name"];

			if ($row['branch_id'] == 10000) {
				$row_data[] = 'All Branch';
			} else if ($row['branch_id'] == 0) {
				$row_data[] = '';
			} else {
				$row_data[] = $row['branch_name'];
			}
			$row_data[] = $row['city_name'];
			$row_data[] = round($row['g_total']);
			$g_total_sum = $g_total_sum + round($row['g_total']);
			$row_data[] = $row['user_name'];

			if ($row['po_approval_status'] == '3') {
				$row_data[] = 'Finance Pending';
			} else if ($row['po_approval_status'] == '1') {
				$row_data[] = 'Approved';
			} else if ($row['po_approval_status'] == '0') {
				$row_data[] = 'Approved Pending';
			} else if ($row['po_approval_status'] == '2') {
				$row_data[] = 'Disapproved';
			} else {
				$row_data[] = 'Finance Disapproved';
			}
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

		$row_data = array('','','','','','',$g_total_sum,'');
		$lineData = $row_data;
		fputcsv($f, $lineData, $delimiter);	

	} else if ($_REQUEST['mode'] == "proforma_list") {

		$filename = "proforma_list_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Invoice Type','Invoice No','Invoice Date','Company Name','City','Grand Total','Approval Status','Username');

		fputcsv($f, $fields, $delimiter);

		$s_date=explode(' - ',$_REQUEST['date']);
		$where='';
		if(!empty($_REQUEST['type_id']))
		{
			$where .=" and invoice.invoicetype_id=".$_REQUEST['type_id'];
		}
		$where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		if($_SESSION['user_type']!='2'){
			$user_id=$_SESSION['user_id'];
			$fis=check_crm_find_in_set($dbcon,$user_id,1);
			$where.=' and invoice.user_id in ('.$fis.')';
		}
		$appData = array();
		$i=1;
		$aColumns = array('invoice_id','invoice_no','cust.l_name','cas.cust_name','city.city_name','invoice_date','invoice.performa_invoice_type','invoicetype.invoice_type','g_total','paid_amount','invoice_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag','ca.c_add_city','invoice.approve_status','users.user_name');
		$sIndexColumn = "invoice_id";
		$isWhere = array("invoice_status = 0 and invoice.company_id IN (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_proforma_invoice as invoice";			
		$isJOIN = array('left join  tbl_ledger cust on invoice.cust_id=cust.l_id','left join  city_mst city on cust.cityid=city.cityid','left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id','left join tbl_customer cas on invoice.cust_id=cas.cust_id','left join tbl_cust_address ca on ca.cust_id=invoice.cust_id','left join users as users on users.user_id=invoice.user_id');
		$hOrder = "invoice.invoice_id desc";
		include('../include/pagging.php');

		$g_total_sum = 0;
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			if($row['performa_invoice_type']=='1'){
				$row['invoice_type'] = 'Quotation';
			}else if($row['performa_invoice_type']=='2'){
				$row['invoice_type'] = 'Sales Order';
			}else{
				$row['invoice_type'] = 'Direct Invoice';
			}
			$row_data[] = $id;
			$row_data[] = $row['invoice_type'];
			$row_data[] = $row['invoice_no'];
			$row_data[] = date('d M, Y',strtotime($row['invoice_date']));
			if($row['performa_invoice_type']=='1'){
				$row_data[] = $row['cust_name'];
			}else{
				$row_data[] = $row['l_name'];
			}
			if($row['performa_invoice_type']=='1'){
				$citysql = $dbcon->query("select city_name from city_mst WHERE cityid = ".$row['c_add_city']);
				$city_address = brp_mysqli_fetch_assoc($citysql);
				$row_data[] = $city_address['city_name'];
			}else{
				$row_data[] = $row['city_name'];
			}

			$row_data[] = $row['g_total'];
			$g_total_sum = $g_total_sum + $row['g_total'];  
			if($row['approve_status']==0){
				$row_data[] = 'Approved';
			} else {
				$row_data[] = 'Pending';
			}
			$row_data[] = $row['user_name'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

		$row_data = array('','','','','','',$g_total_sum,'');
		$lineData = $row_data;
		fputcsv($f, $lineData, $delimiter);	

	} else if ($_REQUEST['mode'] == "sales_order_list") {

		$filename = "sales_order_list_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Sales Order No','Sales Order Date','Customer','City','Grand Total','Po No','Po Date','Approval Status','Jobwork Type','Username');

		fputcsv($f, $fields, $delimiter);

		$s_date=explode(' - ',$_REQUEST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$where='';
		
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		$companyConfiguration=getCompanyConfiguration($dbcon);
		if($companyConfiguration['branch_wise_manage']==1){
			$branch_id = ($_SESSION['user_type'] == '2' && isset($_REQUEST['branch_id']) && $_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : $_SESSION['branch_id'];
		}else{
			$branch_id =$companyConfiguration['default_branch_id'];
		}
		$where_db = check_branch('estimate', $branch_id);
		$where.="  and sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'".$where_db;

		if( $_REQUEST['jobwork_type'] != ""){
			$where .= " and jobwork_type = " . $_REQUEST['jobwork_type'];
		}

		if( $_REQUEST['user_id'] != ""){
			$where .= " and estimate.user_id = " . $_REQUEST['user_id'];
		}else{
			if($_SESSION['user_type']!=2){
				if($companyConfiguration['crm_sales_order_user_selecation']==1){
					$usertype = explode(",",$companyConfiguration['crm_sales_order_user_type_selecation']);
					if(in_array($_SESSION['user_type'], $usertype)){
					// $where .= "";
					}else{
						$where .= " and estimate.user_id = " . $_SESSION['user_id'];
					}
				}
			}
		}
		
		$stat_where="";
		if($_REQUEST['so_status']==8){
			//Approve Pending
			$stat_where=" and estimate.approve_status=0";
		}else if($_REQUEST['so_status']==1){
			//Approve Done
			$stat_where=" and estimate.approve_status=3";

		}else if($_REQUESTT['so_status']==2){
			//Disapprove
			$stat_where=" and estimate.approve_status not in (3,0)";
		}else if($_REQUEST['so_status']==3){
			//Order Accept Pending
			$stat_where=" and estimate.order_accept_status=0";
		}else if($_REQUEST['so_status']==4){
			//Order Accept Done
			$stat_where=" and estimate.order_accept_status=1";
		}else if($_REQUEST['so_status']==5){
			//Invoice Pending
			$stat_where=" and estimate.invoice_status=0";
		}else if($_REQUEST['so_status']==6){
			//Invoice Done
			$stat_where=" and estimate.invoice_status=1 and estimate.short_close_status=0";
		}else if($_REQUEST['so_status']==7){
			//Short Close
			$stat_where=" and estimate.short_close_status=1";
		}
		
		
		$appData = array();
		$i=1;
		$aColumns = array('estimate.sales_order_no','estimate.g_total','estimate.sales_order_date','cust.l_name','city.city_name','estimate.po_no','estimate.po_date','estimate.sales_order_status','estimate.cdate','estimate.user_id', 'estimate.invoice_status','estimate.approve_status','quot.inquiry_id','branch.branch_name','estimate.branch_id','estimate.jobwork_type','estimate.sales_order_id','estimate.revise_status','estimate.order_accept_status','users.user_name','estimate.short_close_status','SUM(sotrn.product_amount) as total');
		$sIndexColumn = "estimate.sales_order_id";
		$isWhere = array("estimate.sales_order_status = 0 and estimate.company_id IN (0,$_SESSION[company_id])".$where.$stat_where);
		$sTable = "tbl_sales_order as estimate";			
		$isJOIN = array(
			'left join tbl_ledger cust on estimate.cust_id=cust.l_id',
			'left join city_mst city on cust.cityid=city.cityid',
			'left join tbl_quotation quot on quot.quotation_id=estimate.quotation_id',
			'left join branch_mst as branch on branch.branch_id=estimate.branch_id',
			'left join users as users on users.user_id=estimate.user_id',
			'left join tbl_task as tsk on tsk.inquiry_id=quot.inquiry_id and tsk.stage_prob=100',
			'left join tbl_sales_ordertrn as sotrn on sotrn.sales_order_id=estimate.sales_order_id',
		);
		$hOrder = "estimate.sales_order_id desc";
		$hGroupby = array("estimate.sales_order_id");
		include('../include/pagging.php');

		$g_total_sum = 0;
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $row['sales_order_no'];
			$row_data[] = date('d M, Y',strtotime($row['sales_order_date']));
			$row_data[] = $row['l_name'];
			$row_data[] = $row['city_name'];
			$row_data[] = $row['g_total'];
			$g_total_sum = $g_total_sum + $row['g_total'];  
			$row_data[] = $row['po_no'];
			$row_data[] = date('d-m-Y',strtotime($row['po_date']));
			if($row['order_accept_status']==0){
				$oa = 'Order Accept Pending';
			}else if($row['order_accept_status']==1){
				$oa = 'Order Accepted';
			}
			if($row['approve_status']==3){
				if ($oa) {
					$row_data[] ='Approved , '.$oa;	
				} else {
					$row_data[] ='Approved';
				}				
			}else if($row['approve_status']==0){
				$row_data[] ='Approve Pending';
			}else{
				$row_data[] ='Disapproved';
			}
			// $row_data[]=$row['approve_status'];
			if($companyConfiguration['outside_jobwork']){
				if($row['jobwork_type'] == '0'){
					$row_data[] ='Normal';
				}else{
					$row_data[] ='Outside Jobwork';
				}
			}

			if($companyConfiguration['branch_wise_manage']==1){
				$row_data[] = $row['branch_name'];
			}
			$row_data[] = $row['user_name'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

		$row_data = array('','','','','',$g_total_sum,'','');
		$lineData = $row_data;
		fputcsv($f, $lineData, $delimiter);	

	} else if ($_REQUEST['mode'] == "quotation_list") {

		$filename = "quotation_list_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Quotation No','Quotation Date','Customer','Inquiry','Stage','City','Amount','Owner','Assign User','Approval');

		fputcsv($f, $fields, $delimiter);

		$cur_user_id = $_SESSION['user_id'];
		$cur_user = getUserDetailById($dbcon, $cur_user_id);
		$send_email_flag = false;
		if (!empty($cur_user['common_email_id'])) {
			$send_email_flag = true;
		}

		$s_date=explode(' - ',$_REQUEST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$set="select company_name from tbl_company as comp where company_id=".$_SESSION['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		
		$where='';
		$branch_id = $_SESSION['branch_id'];
		if($branch_id){
			$where .= check_branch('quot',$branch_id);
		}
		
		$where.="  and quot.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

		$user_id=$_SESSION['user_id'];
		$fis=check_crm_find_in_set($dbcon,$user_id,1);
		$where.=' and tsk.assign_user_ids in ('.$fis.')';

		if($_REQUEST['approve_status']!=""){
			$where.=" and quot.approve_status=".$_REQUEST['approve_status'];
		}

		if(!empty($_REQUEST['stage_id'])){
			$where.= " AND inq.opp_id =".$_REQUEST['stage_id'];
		}

		$i=1;
		$aColumns = array('quot.quotation_id', 'quot.quotation_no','city.city_name', 'quot.quotation_date', 'cust.cust_name','cust.cust_email','cust.cust_mobile','quot.quot_header', 'inq.inquiry_no', 'quot.quot_subject', 'usr.user_name', 'quot.quotation_status','quot.start_quotation_id','quot.cdate','quot.revise_status','quot.prev_quotation_id','quot.approve_status','quot.cust_id','quot.company_id','inq.stage_prob','tsk.assign_user_ids','stage.opp_stage','stage.opp_color','quot.g_total','quot.inquiry_id', 'quot.sales_order_status','quot.currency_id');
		$sIndexColumn = "quot.quotation_id";
		$isWhere = array("quot.quotation_status = 0 and quot.revise_status=0 and quot.company_id in (0,$_SESSION[company_id])".$where.check_user_inq('quot'));
		$sTable = "tbl_quotation as quot";
		$isJOIN = array('left join tbl_customer as cust on cust.cust_id=quot.cust_id', 
			'left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id', 
			'left join users as usr on usr.user_id=inq.user_id',
			'left join tbl_task as tsk on tsk.inquiry_id=inq.inquiry_id',
			'left join tbl_cust_address as addr on addr.cust_id=cust.cust_id', 
			'left join state_mst as state on state.stateid=addr.c_add_state',
			'left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id',
			'left join city_mst as city on city.cityid=addr.c_add_city');
		$hOrder = "quot.quotation_id desc";
		$hGroupby = array("quot.quotation_id");
		include('../include/pagging.php');

		$appData = array();
		$id=1;
		$g_total_sum = 0;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $row['quotation_no'];
			$row_data[] = date('d M, Y',strtotime($row['quotation_date']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['inquiry_no'];
			
			$row_data[] = $row['opp_stage'].' ('.$row['stage_prob'].'%)';
			$row_data[] = $row['city_name'];
			$row_data[] = $row['g_total'];
			$g_total_sum = $g_total_sum + $row['g_total'];
			if($getspecialConfiguration['durva_permission'] ==1 )
			{
				$row_data[] = $row['quot_subject'];
			}
			$row_data[] = $row['user_name'];
				
	
			$query_i="select GROUP_CONCAT(DISTINCT mst.user_name SEPARATOR ',<br/>') as asinguser from users as mst
			where mst.user_id in (".$row['assign_user_ids'].")";
			$result_i=$dbcon->query($query_i);
			$rel_i=mysqli_fetch_assoc($result_i);
	
			$row_data[] = $rel_i['asinguser'];
	
			if($row['approve_status']=='1'){
				$row_data[] = 'Authorized';
			} else if($row['approve_status']=='2'){
				$row_data[] = 'Rejected';
			} else {
				$row_data[] = 'Pending';
			}
			$appData[] = $row_data;
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

		$row_data = array('','','','','','','',$g_total_sum,'','');
		$lineData = $row_data;
		fputcsv($f, $lineData, $delimiter);	
		
	} else if ($_REQUEST['mode'] == "crm_daily_report") {

		$filename = "daily_report_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Description','Date','User Name');

		fputcsv($f, $fields, $delimiter);

		if ($_REQUEST['start_date'] && $_REQUEST['end_date']) {
			$_SESSION['start'] = $start_date = $_REQUEST['start_date'];
			$_SESSION['end'] = $end_date = $_REQUEST['end_date'];
		} else if (isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])
		) {
			$start_date = $_SESSION['summary_start_date'];
			$end_date = $_SESSION['summary_end_date'];
		} else {
			$start_date = date('1-m-Y');
			$end_date = date("d-m-Y");
		}

		$where = '';
		if (!empty($start_date) && !empty($end_date)) {
			$where .= "  AND drt.date BETWEEN '" . date('Y-m-d', strtotime($start_date)) . "' AND '" . date('Y-m-d', strtotime($end_date)) . "'";

		}
		$appData = array();
		$i = 1;
		$aColumns = array('drt.r_id', 'drt.user_id', 'u.user_name', 'drt.date', 'drt.description');
		$date = date_create($row['date']);

		$sIndexColumn = "drt.r_id";

		if ($_REQUEST['uid']) {
			$isWhere = array("u.user_id =" . $_REQUEST['uid'] . ' AND ' . $where);
		} else {
			$isWhere = array($where);
		}

		$sTable = "daily_report as drt";
		$isJOIN = array("LEFT JOIN users AS u ON u.user_id = drt.user_id");
		$hOrder = "drt.r_id ";
		include('../include/pagging.php');

		$appData = array();
		$id = 1;
		foreach ($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$cleanContent = stripcslashes(mysqli_real_escape_string($dbcon, $row['description']));
			$row_data[] = strip_tags(remove_special_char($cleanContent));
			$row_data[] = date("d-m-Y", strtotime($row['date']));
			$row_data[] = $row['user_name'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}
	} else if ($_REQUEST['mode'] == "crm_inquiry_not_followup_report") {

		$filename = "inquiry_not_followup_report_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Create Date','Owner User','Assign Users','Inquiry No','Company Name','Address','City','State','Contact Person Name','Contact Number','Email','Customer Type','Stage','Source of Inquiry','Product','Last Follow Up Date');

		fputcsv($f, $fields, $delimiter);

		$date  = $_REQUEST['date'];
		$where='';
		
		$where.=" and DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='" . date('Y-m-d', strtotime($date)) . "'";

		if($_REQUEST['country_id']){
			$where.=' and cadd.c_add_country='.$_REQUEST['country_id'];
		}

		if($_REQUEST['state_id']){
			$where.=' and cadd.c_add_state='.$_REQUEST['state_id'];
		}

		if($_REQUEST['city_id']){
			$where.=' and cadd.c_add_city='.$_REQUEST['city_id'];
		}

		if($_REQUEST['stage_id']){
			$where.=' and inq.opp_id='.$_REQUEST['stage_id'];
		}

		if($_REQUEST['assign_user_id']){
			$where.=' and task.user_id='.$_REQUEST['assign_user_id'];
		}

		$count_que = 'select count(task.task_id) as cnt_task from tbl_task as task 

		left join (SELECT opp_id, cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,c_con_id from tbl_inquiry
			where inquiry_status=0 ) as inq on inq.inquiry_id=task.inquiry_id 
			left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_addr_defult=1
			left join country_mst as coun on coun.countryid = cadd.c_add_country
			left join state_mst as state on state.stateid=cadd.c_add_state
			left join city_mst as city on city.cityid=cadd.c_add_city
			left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id
			where task.task_status=0 and task.entry_type=1 and task.company_id in (0,'.$_SESSION['company_id']. ') and task.task_type_id=16 and task.task_type_id !="'.GENERAL_TASK_TYPE.'"'.$where;

		$result_cnt = $dbcon->query($count_que);
		$row_cnt = brp_mysqli_fetch_array($result_cnt);

		if ($_REQUEST['row'] == 0 || empty($_REQUEST['row'])) {
			$rowperpage = 30;
		} else {
			$rowperpage = $_REQUEST['row'] + 30;
		}
		
		$query = 'SELECT task.task_id, task.task_rel_id,task.create_date,task.cdate, task.task_name, task.task_completion_date, task.task_due_date, tea.t_name, inq.inquiry_no, qt_aprv.quotation_no, inq.inquiry_name, inq.inquiry_date, cust.cust_name, cust.cust_mobile, per.c_con_fname, row.task_rel_name, state.state_name, city.city_name, task.task_due_date, task.task_remark, usr.user_name, task.task_rel_id, task.assign_user_ids, task.inquiry_id, task.task_type_id, task.task_status, task.entry_type, task.alert_date_time, type.mcd_name as type_name, task.user_id, task.task_priority_id, task_sub.mcd_name as task_sub_name, stage.opp_stage, stage.opp_probability, qt_aprv.approve_status, qt_aprv.quotation_id, type.mcd_id, mcd.mcd_name, inq.closing_date, cadd.c_add_address,inq.c_con_id,cu_con.c_con_fname,cu_con.c_con_lname,cu_con.c_con_mobile,cu_con.c_con_email,rf.rb_name as source, IF((select count(cust_id) from tbl_inquiry where inquiry_status=0 and cust_id=inq.c_id)>1, "Existing Customer", "New Customer") as ctype,group_concat(product_name SEPARATOR ",") as product_name

		FROM tbl_task as task 
		left join users as usr on usr.user_id=task.user_id 
		left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id 
		left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id 
		left join task_rel_mst as row on row.task_rel_id=task.task_rel_id 
		left join (SELECT opp_id, cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,c_con_id from tbl_inquiry
			where inquiry_status=0
		) as inq on inq.inquiry_id=task.inquiry_id 
		left join (SELECT inquiry_id,approve_status,quotation_id,quotation_no FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id 
		left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id 
		left join product_mst as pro on pro.product_id  = tr.product_id 
		left join tbl_customer as cust on cust.cust_id=inq.c_id
		left join tbl_cust_contact as cu_con on cu_con.c_con_id = inq.c_con_id 
		left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_addr_defult=1
		left join country_mst as coun on coun.countryid = cadd.c_add_country
		left join state_mst as state on state.stateid=cadd.c_add_state
		left join city_mst as city on city.cityid=cadd.c_add_city
		left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id
		left join tbl_cust_contact as per on per.c_con_id=task.c_con_id 
		left join tbl_refer_by as rf on rf.rb_id=cust.cust_source
		left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type 
		left join territory_mst as tea on tea.t_id=cust.t_id

		where task.task_status=0 and task.entry_type=1 and task.company_id in (0,'.$_SESSION['company_id']. ') and task.task_type_id=16 and task.task_type_id !="'.GENERAL_TASK_TYPE.'" '.$where.' Group by task.task_id ORDER BY task.task_id desc  limit 0,'.$rowperpage;
		$result = $dbcon->query($query);

		$id=1;
		while($row = $result->fetch_assoc()){
			$task_completion_date = "";
			$task_due_date = "";
			if($row['task_completion_date']!="1970-01-01 00:00:00" && $row['task_completion_date']!="0000-00-00 00:00:00"){
				$task_completion_date=date('d-M-Y h:i A',strtotime($row['task_completion_date']));
			}
			if($task_rel['task_due_date']!="1970-01-01 00:00:00" && $row['task_due_date']!="0000-00-00 00:00:00"){
				$task_due_date=date('d-M-Y h:i A',strtotime($row['task_due_date']));
			}

			$row_data = array();
			$row_data[] = $id;
			$row_data[] = date('d-m-Y',strtotime($row['create_date']));
			$row_data[] = $row['user_name'];
			$row_data[] = getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']);
			$row_data[] = $row['inquiry_no'];
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['c_add_address'];
			$row_data[] = $row['city_name'];
			$row_data[] = $row['state_name'];
			$row_data[] = $row['c_con_fname'];
			$row_data[] = $row['c_con_mobile'];
			$row_data[] = strtolower($row['c_con_email']);
			$row_data[] = $row['ctype'];
			$row_data[] = $row['opp_stage'];
			$row_data[] = $row['source'];
			$row_data[] = $row['product_name'];
			$row_data[] = $task_due_date;
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
	   }


	} else if ($_REQUEST['mode'] == "crm_inquiry_leads_by_months") {

		$filename = "inquiry_leads_by_months_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Inquiry Date','Company Name','Address','Executive','Stage','Sales Stage','Source','Closing Date');

		fputcsv($f, $fields, $delimiter);

		$start_date = $_REQUEST['source_id'];
		$end_date = date("Y-m-t", strtotime($start_date));

		$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.inquiry_date >= '".date('Y-m-d',strtotime($start_date))."' AND e.inquiry_date <= '".date('Y-m-d',strtotime($end_date))."'";

		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_date','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','mc1.mcd_name as inquiry_type','cust.cust_name','country.country_name','state.state_name','city.city_name','cadd.c_add_address','us.user_name as lead_owner','rf.rb_name','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
						"left join tbl_customer as cust on cust.cust_id=e.cust_id",
						"left join tbl_cust_address as cadd on cadd.cust_id=e.cust_id and cadd.c_add_status=0 and cadd.c_addr_defult=1",
						"left join city_mst as city on city.cityid=cadd.c_add_city",
						"left join state_mst as state on state.stateid=cadd.c_add_state",
						"left join country_mst as country on country.countryid=cadd.c_add_country",
						"left join users as us on us.user_id=e.user_id",
						"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
						"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id","left join tbl_refer_by as rf on rf.rb_id=cust.cust_source",
						"left join tbl_master_category_detail as mc1 on mc1.mcd_id=e.inquiry_type_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y',strtotime($row['inquiry_date']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['c_add_address']." ,".$row['city_name']." ,".$row['state_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['rb_name'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}
	} else if ($_REQUEST['mode'] == "gstr1_format_report") {

		$filename = "gstr1_format_report_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Name of the Receipient','GSTIN No','State Name','SO','Invoice No','Invoice Date','Invoice Value','Invoice HSN/SAC','Goods/Service','Taxanble Value','QTY','UNIT','IGST Rate','IGST Amt','CGST Rate','CGST Amt','SGST Rate','SGST Amt','Reverse Charge','Payment Reciept Type');

		fputcsv($f, $fields, $delimiter);


		$s_date=explode(' - ',$_REQUEST['date']);

		$where ="  AND DATE(inv.invoice_date) >= '".date('Y-m-d',strtotime($s_date[0]))."' AND  DATE(inv.invoice_date) <= '".date('Y-m-d',strtotime($s_date[1]))."'";

		$appData = array();
		$i=1;
		$aColumns = array( 'invtrn.invoice_id','led.l_name','invtrn.currency_id','invtrn.product_hsn_code','invtrn.taxable_value', 'invtrn.cgst_tax_per','invtrn.sgst_tax_rate','invtrn.cgst_tax_rate','invtrn.cgst_tax_rate_conv','invtrn.igst_tax_rate','invtrn.igst_tax_rate_conv','invtrn.sgst_tax_per','invtrn.product_qty','invtrn.sgst_tax_rate','invtrn.sgst_tax_rate_conv','invtrn.igst_tax_per', 'invtrn.trancation_status', 'inv.invoice_no', 'inv.invoice_date', 'inv.currency_rate', 'inv.g_total as invoice_value', 'led.gst_no', 'led.stateid', 'stmst.state_name', 'rec.payment_type', 'product.product_name','product.product_icode','product.product_alias_name','product.product_type','product.product_base_unit', 'cat.unit_name as base_unit','ccat.unit_name as conv_unit','rcat.unit_name as rat_unit', 'cate.cat_name', 'pcat.cat_name as pcat_name');
		$sIndexColumn = "invtrn.invoice_id";
		$isWhere = array("invtrn.trancation_status=0 and invtrn.invoice_id != 0". $where);
		$sTable = "tbl_invoicetrn as invtrn";
		$isJOIN = array(
			'left join tbl_invoice as inv on inv.invoice_id = invtrn.invoice_id',
			'left join tbl_receipt as rec on rec.invoice_id = invtrn.invoice_id',
			'left join tbl_ledger as led on led.l_id=inv.cust_id', 
			'left join tbl_ledger as led_con on led_con.l_id=inv.consignee_id', 
			'left join state_mst as stmst on stmst.stateid=led.stateid', 
			'left join state_mst as stmst_con on stmst_con.stateid=led_con.stateid', 
			'left join unit_mst as cat on cat.unitid = invtrn.unit_id', 
			'left join unit_mst as ccat on ccat.unitid = invtrn.conv_unit_id', 
			'left join unit_mst as rcat on rcat.unitid = invtrn.rate_unit', 
			'left join product_mst as product on product.product_id = invtrn.product_id', 
			'left join tbl_drawing as dr on dr.drawing_id = product.drawing_id', 
			'left join tbl_category as cate on cate.cat_id = product.product_category', 
			'left join tbl_category as pcat on pcat.cat_id = product.parent_category');
		$hOrder = "invtrn.invoice_id desc";
		include('../include/pagging.php');

		$id=1;
		$taxable_value_cnt_tot = 0;
		$amount_tot = 0;
		$qty_tot = 0;
		$igst_amt_tot = 0;
		$cgst_amt_tot = 0;
		$sgst_amt_tot = 0;
		$reverse_charge_tot = 0;

		foreach($sqlReturn as $row) {
			if(!empty($row['currency_id'])){
				$currency=getcurrencydetail($dbcon,$row['currency_id']);
			}else{
				$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
			}

			$amount = $row['invoice_value'] * $row['currency_rate'];

			$cgst_rate="";				
			$cgst_amt="";				
			$sgst_rate="";				
			$sgst_amt="";				
			$igst_rate="";				
			$igst_amt="";				

			$taxable_value_cnt = $amount - $row['taxable_value'];
			$amount_tot = $amount_tot + $amount;
			$taxable_value_cnt_tot += $taxable_value_cnt;
			$qty_tot = $qty_tot + $row['product_qty'];
			$reverse_charge_tot += $row['reverse_charge'];

			if($row['cgst_tax_per']!=0)
			{
				$cgst_rate = $row['cgst_tax_per'];
				$cgst_amt1 = (($row['currency_id']==$_SESSION['currency_id']) ? $row['cgst_tax_rate'] : $row['cgst_tax_rate_conv']);
				$cgst_amt_tot += $cgst_amt1;
				$cgst_amt = number_format($cgst_amt1,2);
			}

			if($row['sgst_tax_per']!=0)
			{
				$sgst_rate = $row['sgst_tax_per'];
				$sgst_amt1 = (($row['currency_id']==$_SESSION['currency_id']) ? $row['sgst_tax_rate'] : $row['sgst_tax_rate_conv']);
				$sgst_amt_tot += $sgst_amt1;
				$sgst_amt = number_format($sgst_amt1,2);
			}

			if($row['igst_tax_per']!=0)
			{
				
				$igst_rate = $row['igst_tax_per'];
				$igst_amt1 = (($row['currency_id']==$_SESSION['currency_id']) ? $row['igst_tax_rate'] : $row['igst_tax_rate_conv']);
				$igst_amt_tot += $igst_amt1;
				$igst_amt = number_format($igst_amt1,2);
			}

			$payment_type = "";
			if (isset($row['payment_type'])) {
				$payment_type = $row['payment_type'] == 0 ? 'Regular' : 'PDC';
			}

			

			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $row['l_name'];
			$row_data[] = $row['gst_no'];
			$row_data[] = $row['state_name'];
			$row_data[] = $row['enable_consignee'] ==  1 ? $row['state_name'] : $row['con_state_name'];
			$row_data[] = $row['invoice_no'];
			$row_data[] = date("d/m/Y", strtotime($row['invoice_date']));
			$row_data[] = $amount;
			$row_data[] = $row['product_hsn_code'];
			$row_data[] = $row['product_type'] == 8 ? "Service" : "Goods";
			$row_data[] = number_format($taxable_value_cnt,2);
			$row_data[] = $row['product_qty'];
			$row_data[] = $row['base_unit'];
			$row_data[] = $igst_rate;
			$row_data[] = $igst_amt;
			$row_data[] = $cgst_rate;
			$row_data[] = $cgst_amt;
			$row_data[] = $sgst_rate;
			$row_data[] = $sgst_amt;
			$row_data[] = number_format($row['reverse_charge'],2);
			$row_data[] = $payment_type;
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

		$row_data = array();
		$row_data = array('','Total','','','','','',number_format($amount_tot,2),'','',number_format($taxable_value_cnt_tot,2),$qty_tot,'','',number_format($igst_amt_tot,2),'',number_format($cgst_amt_tot,2),'',number_format($sgst_amt_tot,2),number_format($reverse_charge_tot,2),'');
		$lineData = $row_data;
		fputcsv($f, $lineData, $delimiter);	

	} else if ($_REQUEST['mode'] == "inventory_opening_stock") {

		$filename = "opening_stock".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Branch','Location','Product','Opening Stock Qty','Closing Stock Qty','Base Rate','Convert Rate');

		fputcsv($f, $fields, $delimiter);

		$where='';
		if($_REQUEST['branch_id']!=''){
			$where.=" and osm.branch_id=".$_REQUEST['branch_id'];
		}
		if($_REQUEST['product_id']!=''){
			$where.=" and osm.product_id=".$_REQUEST['product_id'];
		}
		if($_REQUEST['location_id']!=''){
			$where.=" and osm.location_id=".$_REQUEST['location_id'];
		}

		$appData = array();
		$i=1;
		$aColumns = array('osm.opening_stock_id','branch_name','gd_name','product_name','batch_no','opening_stock_qty','closing_stock','product_name','product_icode','approve_status','approve_status','osm.product_id','osm.branch_id','osm.opening_stock_unit','osm.base_rate','osm.conv_rate');
		$sIndexColumn = "osm.opening_stock_id";
		$isWhere = array("osm.status = 0 and osm.approve_status in (".$_REQUEST['approve_status'].")".$where);
		$isJOIN = array('left join product_mst as pmst on pmst.product_id=osm.product_id','left join mst_godown as location on osm.location_id =location.gd_id','left join branch_mst as bran on bran.branch_id=osm.branch_id');
		$sTable = "opening_stock_mst osm";			
		
		$hOrder = "osm.opening_stock_id desc";
		include('../include/pagging.php');
		$appData = array();
		
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$opening_stock_id = $row["opening_stock_id"];
			$row_data[] = $row["sr"];
			$row_data[] = $row["branch_name"];
			$row_data[] = $row["gd_name"];
			$row_data[] = $row["product_name"].' -- ('.$row['product_icode'].')';
			if($companyConfiguration['batch_wise_stock'] == '1'){
				$row_data[] = $row["batch_no"];
			}
			$row_data[] = $row["opening_stock_qty"];
			$row_data[] = $row["closing_stock"];
			$row_data[] = $row["base_rate"];
			$row_data[] = $row["conv_rate"];
						
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);		
			$id++;
		}

	} else if ($_REQUEST['mode'] == "administrator_master_hsn") {

		$filename = "master_hsn".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','HSN Code','HSN Description','Tax Category');

		fputcsv($f, $fields, $delimiter);

		$i=1;
		$aColumns = array('qcparam.hsn_id', 'qcparam.hsn_code','t.tax_cat_name', 'qcparam.hsn_desc','qcparam.hsn_status','qcparam.user_id','qcparam.is_deletable','qcparam.sale_gst');
		$sIndexColumn = "qcparam.hsn_id";
		$isWhere = array("qcparam.hsn_status = 0 and qcparam.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "mst_hsn_code as qcparam";			
		$isJOIN = array("left join tbl_tax_category as t on t.tax_cat_id=qcparam.sale_gst");
		$hOrder = "qcparam.hsn_id desc";
		include('../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['hsn_code'];
			$row_data[] = $row['hsn_desc'];
			$row_data[] = $row['tax_cat_name'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);		
			$id++;
		}
	} else if ($_REQUEST['mode'] == "administrator_master_process") {

		$filename = "master_process".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Process Name','Process Type','Dashboard Priority');

		fputcsv($f, $fields, $delimiter);

		$where='';

		$branch_id = $_REQUEST['branch_id'];	    
	    if($branch_id != '1000'){
	        $where .= check_branch('zmst',$branch_id);
	    }

		$i=1;
		$aColumns = array('zmst.process_id', 'zmst.process_name','zmst.process_type','pt.process_type_name','zmst.dashbord_priority','zmst.cdate', 'zmst.process_status', 'zmst.user_id');
		$sIndexColumn = "zmst.process_id";
		$isWhere = array("zmst.process_status = 0 and zmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "process_mst as zmst";			
		$isJOIN = array('inner join process_type_mst as pt on pt.process_type_id=zmst.process_type');
		$hOrder = "zmst.process_id desc";
		include('../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['process_name']; 
			$row_data[] = $row['process_type_name']; 
			$row_data[] = $row['dashbord_priority']; 

			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);			
			$id++;
		}

	} else if ($_REQUEST['mode'] == "administrator_master_category") {

		$filename = "master_category".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Category Name','Parent Category Name');

		fputcsv($f, $fields, $delimiter);

		$where='';

		//branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($_REQUEST['branch_id']) && $_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : $_SESSION['branch_id'];

		$where_db = check_branch('tblcat', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('tblcat');

		$where.=" $where_company";

		$where.=" $where_user";

		$i=1;
		$aColumns = array('tblcat.cat_id', 'tblcat.cat_name','tblcat.cat_pid', 'tblcat.cat_status','tblcat.user_id');
		$sIndexColumn = "tblcat.cat_id";
		$isWhere = array("tblcat.cat_status = 0 and tblcat.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_category as tblcat";			
		$isJOIN = array();
		$hOrder = "tblcat.cat_id desc";
		include('../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cat_name'];
			if($row['cat_pid'] == 0){
				$row_data[] = 'PRIMARY';
			}else{
				$row_data[] = get_category_by_id($dbcon,$row['cat_pid']);
			}
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}

	} else if ($_REQUEST['mode'] == "administrator_master_godown") {

		$filename = "master_godown".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Godown Name','Parent Godown Name','Address');

		fputcsv($f, $fields, $delimiter);

		$branch_id = $_REQUEST['branch_id'];

		$where='';
		if($branch_id != '1000'){
			$where .= ' AND branch_id = ' . $branch_id;
		}

		$qry = "SELECT	*	FROM mst_godown where g_status = 0 AND company_id = ". $_SESSION['company_id'] . $where;
		$result=$dbcon->query($qry);
		
		$godown = array(
			'godowns' => array(),
			'parent_godown' => array()
		);

		while ($row = brp_mysqli_fetch_assoc($result)) {
			//creates entry into godowns array with current category id ie. $godowns['godowns'][1]
			$godown['godowns'][$row['gd_id']] = $row;
			
			//creates entry into parent_godown array. parent_godown array contains a list of all godowns with children
			$godown['parent_godown'][$row['p_gd_id']][] = $row['gd_id'];
		}

		$appData = array();
		$id=1;
		foreach($godown['godowns'] as $row) {
			$row_data = array();

			$parent_name = "";
			if ($row['p_gd_id'] != 0) {
				$parent_name = $godown['godowns'][$row['p_gd_id']]['gd_name'];
			}

			$row_data[] = $id;
			$row_data[] = $row['gd_name'];
			$row_data[] = $parent_name;
			$row_data[] = $row['gd_address'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "crm_master_category") {

		$filename = "master_category_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Name','Type','Priority');

		fputcsv($f, $fields, $delimiter);

		$where="";
		$cat = $_REQUEST['master_cat'];
		if($cat != '') {
			$where.=" and mcd_cat_id='$cat'";
		}			

		$appData = array();
		$i=1;
		$aColumns = array('c.mc_name','m.mcd_name','priority','m.mcd_status','m.user_id','c.mc_id','m.mcd_cat_id','m.mcd_id','m.company_id');
		$sIndexColumn = "m.mcd_cat_id";
		$isWhere = array("m.mcd_status = 0 ".$where."  and m.company_id in (0,$_SESSION[company_id])");
		$sTable = "tbl_master_category_detail as m";			
		$isJOIN = array('left join  tbl_master_category as c on c.mc_id=m.mcd_cat_id');
		$hOrder = "m.mcd_cat_id desc";
		include('../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['mcd_name'];
			$row_data[] = $row['mc_name'];
			$row_data[] = $row['priority'];			
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "report_stock") {

		$company_config = getCompanyConfiguration($dbcon);		
		$production_pro_search = $company_config['production_pro_search'];
		$pro_search=explode(",", $production_pro_search);

		$s_date=explode(' - ',$_REQUEST['date']);
		
		$filename = "report_stock_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Product Name','Base Stock','Convert Stock','Reserve Base Stock','Reserve Convert Stock','Free Base Stock','Free Convert Stock','Base Rate','Convert Rate','Customer Base Stock','Customer Convert Stock');

		fputcsv($f, $fields, $delimiter);

		$where = "";
		$product_id = mysqli_real_escape_string($dbcon,$_REQUEST['product_id']);
		$product_type = $_REQUEST['product_type'];
		$product_category = $_REQUEST['product_category'];

		if($product_id!='')
		{
			$where="and pro.product_id='$product_id'";
		}

		if($product_category!='')
		{
			$where .="and pro.product_category='$product_category'";
		}

		if($product_type!='')
		{
			$where .="and pro.product_type='$product_type'";
		}
				
		$appData = array();
		$i=1;
		$aColumns = array('pro.product_icode, dr.drawing_number, pro.product_id, pro.product_base_unit, un.unit_name, 
				c_un.unit_name AS conv_unit_name, pro.product_name, pro.product_status, pro.product_conv_unit, pro.batch_wise_stock_manage, 
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.base_unit = pro.product_base_unit AND qc.customer_id = 0 THEN qc.base_stock ELSE 0 END), 0) AS base_stock_add,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.base_unit = pro.product_base_unit AND qc.customer_id = 0 THEN qc.base_stock ELSE 0 END), 0) AS base_stock_minus,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.customer_id = 0 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS con_stock_add,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.customer_id = 0 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS con_stock_minus,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.customer_id = 0 AND qc.convert_unit = pro.product_conv_unit THEN qc.convert_stock ELSE 0 END), 0) AS convert_stock_add1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.customer_id = 0 AND qc.convert_unit = pro.product_conv_unit THEN qc.convert_stock ELSE 0 END), 0) AS convert_stock_minus1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.customer_id = 0 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS base_stock_add1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.customer_id = 0 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit THEN qc.convert_stock ELSE 0 END), 0) AS base_stock_minus1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.base_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.base_stock ELSE 0 END), 0) AS cust_base_stock_add,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.base_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.base_stock ELSE 0 END), 0) AS cust_base_stock_minus,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_con_stock_add,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.base_unit != qc.convert_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_con_stock_minus,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.convert_unit = pro.product_conv_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_convert_stock_add1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.convert_unit = pro.product_conv_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_convert_stock_minus1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 1 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_base_stock_add1,
    			IFNULL(SUM(CASE WHEN qc.stock_flage = 2 AND qc.convert_unit != qc.base_unit AND qc.convert_unit = pro.product_base_unit AND qc.customer_id != 0 AND qc.company_id = 1 THEN qc.convert_stock ELSE 0 END), 0) AS cust_base_stock_minus1,
				COALESCE((
					SELECT base_rate
					FROM tbl_stock_trn
					WHERE product_id = pro.product_id
						AND stock_flage = 1
						AND stock_status != 2
						AND ref_name = "tbl_grn_trn"
					ORDER BY stock_id DESC
					LIMIT 1
				), (
					SELECT base_rate
					FROM tbl_stock_trn
					WHERE product_id = pro.product_id
						AND stock_flage = 1
						AND stock_status != 2
						AND ref_name = "opening_stock"
					ORDER BY stock_id DESC
					LIMIT 1
				)) AS last_base_rate,
				COALESCE((
					SELECT conv_rate
					FROM tbl_stock_trn
					WHERE product_id = pro.product_id
						AND stock_flage = 1
						AND stock_status != 2
						AND ref_name = "tbl_grn_trn"
					ORDER BY stock_id DESC
					LIMIT 1
				), (
					SELECT conv_rate
					FROM tbl_stock_trn
					WHERE product_id = pro.product_id
						AND stock_flage = 1
						AND stock_status != 2
						AND ref_name = "opening_stock"
					ORDER BY stock_id DESC
					LIMIT 1
				)) AS last_conv_rate');
		$sIndexColumn = "pro.product_id";
		$isWhere = array("pro.product_status = 0 and qc.stock_status !=2 ".$where);
		$sTable = "product_mst as pro";			
		$isJOIN = array("LEFT JOIN unit_mst AS un ON un.unitid = pro.product_base_unit
						LEFT JOIN unit_mst AS c_un ON c_un.unitid = pro.product_conv_unit
						LEFT JOIN tbl_drawing AS dr ON dr.drawing_id = pro.drawing_id
						LEFT JOIN tbl_stock_trn AS qc ON qc.product_id = pro.product_id AND qc.company_id = " . $_SESSION['company_id']);
		$hOrder = "pro.product_name";
		$hGroupby = array("pro.product_id");
		include('../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$drawing_number = "";
			$item_code = "";
			if(in_array('drawing',$pro_search)){
				$drawing_number = " -- (".$row['drawing_number'].")";
			}
			if(in_array('item',$pro_search)){
				$item_code = " -- (".$row['product_icode'].")";
			}
		
			$stock=($row['base_stock_add']+$row['con_stock_add'])-($row['base_stock_minus']+$row['con_stock_minus']);

			$conv_stock=($row['convert_stock_add1']+$row['base_stock_add1'])-($row['convert_stock_minus1']+$row['base_stock_minus1']);

			$cust_stock=($row['cust_base_stock_add']+$row['cust_con_stock_add'])-($row['cust_base_stock_minus']+$row['cust_con_stock_minus']);

			$cust_conv_stock=($row['cust_convert_stock_add1']+$row['cust_base_stock_add1'])-($row['cust_convert_stock_minus1']+$row['cust_base_stock_minus1']);
		

			$res_stock = reserve_stock_new($dbcon,$row['product_id'],$row['product_base_unit']);
			$res_conv_stock = reserve_stock_new($dbcon,$row['product_id'],$row['product_conv_unit']);

			$free_base_stock = $stock - $res_stock;
			$free_conv_stock = $conv_stock - $res_conv_stock;

			$base_rate = 0;
			$conv_rate = 0;

			if(!empty($row['last_purchase_rate']) && $row['last_purchase_rate'] > 0){
				$base_rate = $row['last_purchase_rate'];
				$conv_rate = $row['last_purchase_conv_rate'];
			}else if(!empty($row['last_base_rate']) && $row['last_base_rate'] > 0){
				$base_rate = $row['last_base_rate'];
				$conv_rate = $row['last_conv_rate'];
			}

			$btn_batch = "";
			if($row['batch_wise_stock_manage'] == '1'){
				$btn_batch = '<a class="btn btn-info" target="_blank" href="'.ROOT.REPORT_ROOT.'batch_stock/'.$row['product_id'].'">Batch Wise Report</a>';					
			}
		
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
		
			$row_data[] = $stock.' '.$row['unit_name']; 
			$row_data[] = $conv_stock. ' '.$row['conv_unit_name'] ; 
		
			$row_data[] = $res_stock .' '.$row['unit_name']; 
			$row_data[] = $res_conv_stock. ' '.$row['conv_unit_name']; 
					
			$row_data[] = $free_base_stock.' '.$row['unit_name']; 
			$row_data[] = $free_conv_stock. ' '.$row['conv_unit_name']; 

			$row_data[] = $base_rate; 
			$row_data[] = $conv_rate; 
		
			$row_data[] = $cust_stock.' '.$row['unit_name']; 
			$row_data[] = $cust_conv_stock.' '.$row['conv_unit_name']; 
			$row_data[] = $btn_batch; 
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}

	} else if ($_REQUEST['mode'] == "sales_stage") {

		$s_date=explode(' - ',$_REQUEST['date']);
		$sales_stage = $_REQUEST['source_id'];

		$filename = "report_leads_by_sales_stage_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Generation Date Time','Company Name','Contact Name','Mobile','Email','Source of Inquiry','Oppurtunity Name','Lead Owner','Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		$va = " and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1";
	
		if($sales_stage){
			$va .= " and e.sales_stage_id='".$sales_stage."'";
		}

		$va .=" and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";

		$appData = array();
		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as oppurtunity_name', 'con.c_con_fname', 'con.c_con_lname', 'con.c_con_email', 'con.c_con_mobile','source.rb_name', 'mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
			"left join tbl_customer as cust on cust.cust_id=e.cust_id",
			"left join tbl_cust_contact as con on con.c_con_id = e.c_con_id",
			"left join tbl_refer_by as source on source.rb_id = cust.cust_source",
			"left join users as us on us.user_id=e.user_id",
			"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
			"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');
		$appData = array();

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['c_con_fname'].' '.$row['c_con_lname'];
			$row_data[] = $row['c_con_mobile'];
			$row_data[] = $row['c_con_email'];
			$row_data[] = $row['rb_name'];
			$row_data[] = $row['oppurtunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}

	} else if ($_REQUEST['mode'] == "excecutive") {

		$s_date=explode(' - ',$_REQUEST['date']);
		$user_id=$_REQUEST['source_id'];
		$soure=explode(",",$_REQUEST['source_id']);
		$sour=implode(",",$soure);

		$filename = "report_leads_by_excecutive_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Generation Date Time','Company Name','Oppurtunity Name','Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);
		
		$va='';
		if($sour){
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and us.user_id=$user_id";
		}else{
			$va=" and e.inquiry_status=5";
		}
		
		$va .=" and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	
		$appData = array();
		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
						"left join tbl_customer as cust on cust.cust_id=e.cust_id",
						"left join users as us on us.user_id=e.user_id",
						"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
						"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y',strtotime($row['cdate']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['oppurtunity_name'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
		// stages
	} else if ($_REQUEST['mode'] == "stages") {

		$s_date=explode(' - ',$_REQUEST['date']);
		$stage=$_REQUEST['stage'];	
		$user_id = $_REQUEST['user_id'];
		$soure=explode(",",$_REQUEST['source_id']);
		$sour=implode(",",$soure);

		$filename = "report_leads_by_stages_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Generation Date Time','Company Name','Oppurtunity Name', 'Lead Owner' ,'Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);
		
		if($sour){
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.opp_id in (".$sour.")";
		}else{
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1";
		}
		if($stage){
			$va=" and op.opp_stage='".$stage."'";
		}

		if($user_id){
			$va.=" and e.user_id=".$user_id;
		}

		$va .=" and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
						"left join tbl_customer as cust on cust.cust_id=e.cust_id",
						"left join users as us on us.user_id=e.user_id",
						"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
						"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');
		
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['oppurtunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "probablity") {

		$s_date=explode(' - ',$_REQUEST['date']);
		$soure=explode(",",$_REQUEST['source_id']);
		$sour=implode(",",$soure);

		$filename = "report_leads_by_probablity_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Generation Date Time','Company Name','Oppurtunity Name', 'Lead Owner' ,'Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		if($sour){
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.opp_id in (".$sour.")";
		}else{
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 ";
		}
		
		$va .=" and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
		$appData = array();
		
		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as opportunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
						"left join tbl_customer as cust on cust.cust_id=e.cust_id",
						"left join users as us on us.user_id=e.user_id",
						"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
						"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['opportunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "closing_dates") {
		
		$filename = "report_leads_by_closing_dates_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Generation Date Time','Company Name','Oppurtunity Name', 'Lead Owner' ,'Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		if($_REQUEST['start_date']){
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.closing_date >= '".date('Y-m-d',strtotime($_REQUEST['start_date']))."' AND e.closing_date <= '".date('Y-m-d',strtotime($_REQUEST['end_date']))."'";
		}else{
			$va=" and e.inquiry_status=5";
		}

		$i=1;
		$aColumns = array(' DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','mc1.mcd_name as inquiry_type','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
						"left join tbl_customer as cust on cust.cust_id=e.cust_id",
						"left join users as us on us.user_id=e.user_id",
						"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
						"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id",
						"left join tbl_master_category_detail as mc1 on mc1.mcd_id=e.inquiry_type_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');
		
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['oppurtunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "months") {
		
		$filename = "report_leads_by_months_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Inquiry Type','Company Name','Oppurtunity Name', 'Executive' ,'Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		$start_date=$_REQUEST['source_id'];
		$end_date=date("Y-m-t", strtotime($start_date));

		$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.inquiry_date >= '".date('Y-m-d',strtotime($start_date))."' AND e.inquiry_date <= '".date('Y-m-d',strtotime($end_date))."'";

		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','mc1.mcd_name as inquiry_type','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
						"left join tbl_customer as cust on cust.cust_id=e.cust_id",
						"left join users as us on us.user_id=e.user_id",
						"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
						"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id",
						"left join tbl_master_category_detail as mc1 on mc1.mcd_id=e.inquiry_type_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');
	
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['inquiry_type'];
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['oppurtunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "new_vs_existing") {
		
		$filename = "report_new_vs_existing_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Company Name','Inquiry Type','Oppurtunity Name','Lead Owner','Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		$source_id = $_REQUEST['source_id'];
		$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1";

		if($source_id){
			$va .= " and e.inquiry_type_id = ".$source_id;
		}
		$appData = array();
	//'mc.mcd_name as sales_stage'
		$i=1;
		$aColumns = array('DISTINCT e.cdate','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','mc1.mcd_name as inquiry_type','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
			"left join tbl_customer as cust on cust.cust_id=e.cust_id",
			"left join users as us on us.user_id=e.user_id",
			"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
			"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id",
			"left join tbl_master_category_detail as mc1 on mc1.mcd_id=e.inquiry_type_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['inquiry_type'];
			$row_data[] = $row['oppurtunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "source") {
		
		$filename = "report_leads_by_source".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Source Type','Lead Generation Date','Company Name','Oppurtunity Name','Lead Owner','Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		$where = "";
		$s_date=explode(' - ',$_REQUEST['date']);
		if($_REQUEST['source_id'] != "" && $_REQUEST['source_id'] != 'null'){
			$sour=$_REQUEST['source_id'];
			$where .= " and e.rb_id in (".$sour.")";
		}

		$where .= " and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";

		$i=1;
		$aColumns = array('e.cdate','e.inquiry_name as opportunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id','e.opp_id','comp.company_name','rf.rb_name');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.inquiry_status=0 AND e.company_id IN (0,$_SESSION[company_id])".$where);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_customer as cust on cust.cust_id=e.cust_id",
			"left join users as us on us.user_id=e.user_id",
			"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
			"left join tbl_company as comp on comp.company_id=e.company_id",
			"left join tbl_refer_by as rf on rf.rb_id=e.rb_id",
			"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['rb_name'];
			$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
			$row_data[] = $row['company_name'];
			$row_data[] = $row['cust_name'].$row['opportunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "stage_funnel") {
		
		$filename = "report_quotes_stage_funnel".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Lead Source Type','Lead Generation Date','Company Name','Oppurtunity Name','Lead Owner','Stage','Sales Stage','Probablity','Closing Date');

		fputcsv($f, $fields, $delimiter);

		$where = "";
		$_SESSION['start_date'] = $_REQUEST['start_date'];
		$_SESSION['end_date'] = $_REQUEST['end_date'];
		$user_ids = $_REQUEST['user_id'];
		// $opp_id = $_REQUEST['opp_id'];

		// if($opp_id){
		// 	$where .= "and e.opp_id = ".$opp_id;
		// }
		if($user_ids){	
			$va=" and DATE_FORMAT(e.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($_REQUEST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($_REQUEST['end_date']))."'AS DATE) and e.inquiry_status=0 and e.user_id in (".$user_ids.") $where";
		}
		
		$i=1;
		$aColumns = array('e.cdate','e.inquiry_name as opportunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id','e.opp_id','comp.company_name','rf.rb_name');
		$sIndexColumn = "e.inquiry_id";
		$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
		$sTable = " tbl_inquiry as e";			
		$isJOIN = array("left join tbl_customer as cust on cust.cust_id=e.cust_id",
			"left join users as us on us.user_id=e.user_id",
			"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
			"left join tbl_company as comp on comp.company_id=e.company_id",
			"left join tbl_refer_by as rf on rf.rb_id=e.rb_id",
			"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
		$hOrder = "e.inquiry_id";
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['rb_name'];
			$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
			$row_data[] = $row['company_name'];
			$row_data[] = $row['cust_name'].$row['opportunity_name'];
			$row_data[] = $row['lead_owner'];
			$row_data[] = $row['stage'];
			$row_data[] = $row['sales_stage'];
			$row_data[] = $row['probablity'];
			$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "party_list") {
		
		$filename = "report_industry_wise_party_list".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Company Name','Party Industry','Zone','Address','Country','State','City','Person Name','Mobile No','Email ID','Custmer Rating');

		fputcsv($f, $fields, $delimiter);

		$cust_ind_id=$_REQUEST['cust_ind'];
		$t_id=$_REQUEST['t_id'];
		$countryid = $_REQUEST['country'];
		$stateid = $_REQUEST['state'];
		$cityid = $_REQUEST['city'];

		$where='';
		if($cust_ind_id){
			$where.=' and cindu.ci_id='.$cust_ind_id;
		}
		if($t_id){
			$where.=' and cust.t_id='.$t_id;
		}
		
		if($countryid !=''){
			$where.=' and custadd.c_add_country='.$countryid;
		}

		if($stateid !=''){
			$where.=' and custadd.c_add_state='.$stateid;
		}

		if($cityid !=''){
			$where.=' and custadd.c_add_city='.$cityid;
		}

		$i=1;
		$aColumns = array('cust.cust_id', 'cindu.ci_name', 'tere.t_name','customcon.c_con_fname', 'customcon.c_con_lname', 'comp.company_name', 'custadd.c_add_address', 'country.country_name', 'state.state_name', 'city.city_name', 'cc.cc_name', 'cust.party_type', 'cust.cust_name', 'cust.cust_email', 'cust.cust_mobile', 'cust.cust_gst', 'cust.cust_status','cust.cdate','cust.user_id');
		$sIndexColumn = "cust.cust_id";
		$isWhere = array("cust.cust_status = 0 AND cust.company_id IN (0,$_SESSION[company_id])".$where);
		$sTable = " tbl_customer as cust";			
		$isJOIN = array("left join tbl_customer_industry as cindu on cindu.ci_id=cust.cust_ind",
			"left join tbl_customer_category as cc on cc.cc_id=cust.cust_cat",
			"left join tbl_company as comp on comp.company_id = cust.company_id",
			"left join tbl_cust_address as custadd on custadd.cust_id=cust.cust_id and custadd.c_addr_defult=1",
			"left join country_mst as country on country.countryid=custadd.c_add_country",
			"left join state_mst as state on state.stateid=custadd.c_add_state",
			"left join city_mst as city on city.cityid=custadd.c_add_city",
			"left join tbl_cust_contact as customcon on customcon.cust_id=cust.cust_id",
			"left join territory_mst as tere on tere.t_id=cust.t_id");
		$hOrder = "cust.cust_id desc";
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['ci_name'];
			$row_data[] = $row['t_name'];
			$row_data[] = $row['c_add_address'];
			$row_data[] = $row['country_name'];
			$row_data[] = $row['state_name'];
			$row_data[] = $row['city_name'];
			$row_data[] = $row['c_con_fname'].' '.$row['c_con_lname'];
			$row_data[] = $row['cust_mobile'];
			$row_data[] = $row['cust_email'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "pending_task") {
		
		$filename = "report_pending_task".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Task Name','Inquiry No','Inquiry Date','Customer Name','Stage','State / City','Territory','Last Followup Date','Next Followup Date','Remark','User');

		fputcsv($f, $fields, $delimiter);

		$where = '';
		$s_date=explode(' - ',$_REQUEST['rep_date']);
		
		if ($_REQUEST['fil_task_type_id']) {
			$where.= ' and task.task_type_id=' . $_REQUEST['fil_task_type_id'];
		}
		if ($_REQUEST['t_id']) {
			$where.= ' and cust.t_id=' . $_REQUEST['t_id'];
		}
		if ($_REQUEST['cust_id']) {
			$where.= ' and inq.c_id=' . $_REQUEST['cust_id'];
		}
		if ($_REQUEST['state_id']) {
			$where.= ' and cadd.c_add_state=' . $_REQUEST['state_id'];
		}
		if ($_REQUEST['city_id']) {
			$where.= ' and cadd.c_add_city=' . $_REQUEST['city_id'];
		}
		if ($_REQUEST['user_id']) {
			$where.= ' and task.user_id=' . $_REQUEST['user_id'];
		}
		if ($_REQUEST['stage_id']) {
			$where.= ' and task.opp_id=' . $_REQUEST['stage_id'];
		}
		$where.="  and task.task_due_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND task.task_due_date <= '".date('Y-m-d',strtotime($s_date[1]))."' AND task.task_type_id !=".GENERAL_TASK_TYPE;
		$i = 1;
		$aColumns = array('task.task_id', 'task.task_rel_id', 'task.task_name','inq.inquiry_no', 'inq.inquiry_name', 'inq.inquiry_date', 'cust.cust_name', 'per.c_con_fname', 'state.state_name', 'city.city_name', 'task.task_due_date', 'task.task_remark', 'usr.user_name', 'task.assign_user_ids', 'task.inquiry_id', 'task.task_type_id', 'task.task_status', 'task.entry_type', 'task.task_completion_date', 'type.mcd_name as type_name', 'task.user_id', 'task.task_priority_id','tea.t_name', 'stage.opp_stage', 'stage.opp_probability','stage.opp_color','task.cdate','inq.c_id','cadd.c_add_state','cadd.c_add_city');
		$sIndexColumn = "task.task_id";
		$isWhere = array("task.task_status = 0 and task.entry_type=1 and task.company_id = $_SESSION[company_id]" . $where);
		$sTable = "tbl_task as task";
		$isJOIN = array('left join users as usr on usr.user_id=task.user_id',
			'left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
			'left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id',
			'left join task_rel_mst as row on row.task_rel_id=task.task_rel_id',
			'left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id',
			'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
			'left join tbl_customer as cust on cust.cust_id=inq.c_id',
			'left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
			'left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
			'left join tbl_opportunity_mst as stage on stage.opp_id=task.opp_id',
			'left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_add_status=0',
			'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id',
			'left join territory_mst as tea on tea.t_id=cust.t_id');
		$hOrder = "task.task_id DESC";
		$hGroupby = array("task.task_id");
		include('../include/pagging.php');

		$id = 1;
		foreach ($sqlReturn as $row) {
			$bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';
			if ($row['task_rel_id'] == '5') {//Inquiry
				$rel_name = $row['cust_name'];
			} else if ($row['task_rel_id'] == '4') { // Company
				$rel_name = $row['cust_name'];
			} else if ($row['task_rel_id'] == '3') {//Person
				$rel_name = $row['c_con_fname'];
			} else {
				$rel_name = $row['task_name'];
			}
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['type_name'];
			$row_data[] = $row['inquiry_no'];
			$row_data[] = date("d-M-Y", strtotime($row['inquiry_date']));
			$row_data[] = $rel_name;
			$row_data[] = $row['opp_stage'] . '(' . $row['opp_probability'] . '%)';
			$row_data[] = $row['state_name'].' - '.$row['city_name'];
			$row_data[] = $row['t_name'];
			$row_data[] = date("d-M-Y", strtotime($row['cdate'])) . ' ' . date("h:i A", strtotime($row['cdate']));
			$row_data[] = date("d-M-Y", strtotime($row['task_due_date'])) . ' ' . date("h:i A", strtotime($row['task_due_date']));
			$row_data[] = nl2br($row['task_remark']);
			$row_data[] = $row['user_name'];
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "delay_followup") {
		
		$filename = "report_delay_followup".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Type','Name','Mobile No','Product Name','Stage','State / City','Due Date','Remark','Owner User','Assign Users','Delay Time','Status','Last Updated By');

		fputcsv($f, $fields, $delimiter);

		$where = '';
		$s_date=explode(' - ',$_REQUEST['rep_date']);
		
		$where.="  and task.task_due_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND task.task_due_date <= '".date('Y-m-d',strtotime($s_date[1]))."' AND task.task_type_id !=".GENERAL_TASK_TYPE;
		
		if(!empty($_REQUEST['user_id'])){
			$where.=" and task.user_id = ".$_REQUEST['user_id'];
		}

		$aColumns = array('task.task_id', 'task.task_rel_id', 'task.task_name','inq.inquiry_no', 'inq.inquiry_name', 'inq.inquiry_date', 'cust.cust_name', 'per.c_con_fname', 'state.state_name', 'city.city_name', 'task.task_due_date', 'task.task_remark', 'usr.user_name', 'task.assign_user_ids', 'task.inquiry_id', 'task.task_type_id', 'task.task_status', 'task.entry_type', 'task.task_completion_date', 'type.mcd_name as type_name', 'task.user_id', 'task.task_priority_id','if(tr.project_wise=0,(SELECT group_concat(pro.product_name SEPARATOR ",<br>") FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as pro_name', 'stage.opp_stage', 'stage.opp_probability','cust.cust_mobile','stage.opp_color','task.cdate');
		$sIndexColumn = "task.task_id";
		$isWhere = array("task.task_status!=2 and task.entry_type=1 and task.company_id in (0,$_SESSION[company_id])" . $where);
		$sTable = "tbl_task as task";
		$isJOIN = array('left join users as usr on usr.user_id=task.user_id',
			'left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
			'left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id',
			'left join task_rel_mst as row on row.task_rel_id=task.task_rel_id',
			'left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id',
			'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
			'left join tbl_customer as cust on cust.cust_id=inq.c_id',
			'left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
			'left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
			'left join tbl_opportunity_mst as stage on stage.opp_id=task.opp_id',
			'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id');
		$hOrder = "task.task_id DESC";
		include('../include/pagging.php');

		$id = 1;
		foreach ($sqlReturn as $row) {
			$bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';
			if ($row['task_rel_id'] == '5') {//Inquiry
				$rel_name = $row['cust_name'] . ' ' . $row['inquiry_name'] . ' ' . $row['inquiry_no'];
			} else if ($row['task_rel_id'] == '4') { // Company
				$rel_name = $row['cust_name'];
			} else if ($row['task_rel_id'] == '3') {//Person
				$rel_name = $row['c_con_fname'];
			} else {
				$rel_name = $row['task_name'];
			}
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['type_name'];
			$row_data[] = $rel_name;
			$row_data[] = $row['cust_mobile'];
			$row_data[] = $row['pro_name'];
			$row_data[] = $row['opp_stage'] . $row['opp_probability'] ? ' (' . $row['opp_probability'] . '%)' : "";
			$row_data[] = $row['state_name'].' - '.$row['city_name'];
			$row_data[] = date("d-M-Y", strtotime($row['task_due_date'])) . ' ' . date("h:i A", strtotime($row['task_due_date']));
			$row_data[] = nl2br($row['task_remark']);
			$row_data[] = $row['user_name'];
			$row_data[] = getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']);
			if ($row['task_status'] == '1') {
				$tsk_due_time = strtotime($row['task_due_date']);
				$cur_time = strtotime($row['task_completion_date']);
				$tsk_type = '';
				$earlier = new DateTime($row['task_due_date']);
				$later = new DateTime($row['task_completion_date']);

				$abs_diff = $later->diff($earlier)->format("%a days");
				$row_data[] = $abs_diff;
				$row_data[] = 'Completed ' . $tsk_type;
			}else{
				$tsk_due_time = strtotime($row['task_due_date']);
				$cur_time = strtotime(date('Y-m-d h:i:s'));
				$tsk_type = '';
				if ($tsk_due_time < $cur_time) {
					$tsk_type = "(Delayed)";
				}

				$earlier = new DateTime($row['task_due_date']);
				$later = new DateTime(date('Y-m-d h:i:s'));

				$abs_diff = $later->diff($earlier)->format("%a days");
				$row_data[] = $abs_diff;
				$row_data[] = 'Pending ' . $tsk_type;
			}
			$row_data[] = $row['user_name']. ' Updated on '.date('d M, Y',strtotime($row['cdate'])).' by '.date('h:i A',strtotime($row['cdate']));
			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	} else if ($_REQUEST['mode'] == "inquiry_list") {
		
		$filename = "inquiry_list".date('d-M-Y').".csv";
		
		//set column headers
		$fields = $keys;
		$fields = array('SR No.','Inquiry Date','Inquiry No','Company','Mobile No','Customer Type','Source','Address','City / State / Country','Product','Stage','Owner','Assign User','Last Updated');

		fputcsv($f, $fields, $delimiter);

		if($_REQUEST['start_date'] && $_REQUEST['end_date']){
			$start_date = $_REQUEST['start_date'];
			$end_date = $_REQUEST['end_date'];
		} else if(isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) 
			&& isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])){
			$start_date = $_SESSION['summary_start_date'];
			$end_date = $_SESSION['summary_end_date'];
		} else {
			$start_date = date('1-m-Y');
			$end_date = date("d-m-Y");
		} 

		$branch_id = $_REQUEST['branch_id'];
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		$where='';
		$stage_where = ' and task.task_status = 0';
		$stage_flag = TRUE;
		if(isset($_REQUEST['stage_id']) && !empty($_REQUEST['stage_id'])){
			$where .= " AND inq.opp_id =".$_REQUEST['stage_id'];
			$stage_flag = FALSE;
			if($_REQUEST['stage_id']=='12' || $_REQUEST['stage_id']=='13'){
				$stage_where = '';
			}
		}

		if(isset($_REQUEST['sales_stage_id']) && !empty($_REQUEST['sales_stage_id'])){
			$where .= " AND inq.sales_stage_id IN(".$_REQUEST['sales_stage_id'].") ";
			$stage_flag = FALSE;
		}

		if(isset($_REQUEST['sales_stage_cat_id']) && !empty($_REQUEST['sales_stage_cat_id'])){
			$where .= " AND inq.inquiry_cat_id IN(".$_REQUEST['sales_stage_cat_id'].") ";
			$stage_flag = FALSE;
		}

		if(isset($_REQUEST['source_id']) && !empty($_REQUEST['source_id'])){
			$where .= " AND cust.cust_source IN(".$_REQUEST['source_id'].") ";
			$stage_flag = FALSE;
		}

		if(isset($_REQUEST['assign_user_id']) && !empty($_REQUEST['assign_user_id'])){
			$where .= " AND inq.user_id = '".$_REQUEST['assign_user_id']."'";
			$stage_flag = FALSE;
		}

		if(isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])){
			$where .= " AND inq.owner_user_id = ".$_REQUEST['user_id'];
			$stage_flag = FALSE;
		}

		if(!empty($start_date) && !empty($end_date)){
			$where.="  AND inq.inquiry_date BETWEEN '".date('Y-m-d',strtotime($start_date))."' AND '".date('Y-m-d',strtotime($end_date))."'";
		}
		if(isset($_REQUEST['country_id']) && !empty($_REQUEST['country_id'])){
			$where .= " AND cadd.c_add_country = ".$_REQUEST['country_id'];
			$stage_flag = FALSE;
		}
		if(isset($_REQUEST['state_id']) && !empty($_REQUEST['state_id']) && $_REQUEST['state_id'] != 'null'){
			$where .= " AND cadd.c_add_state = ".$_REQUEST['state_id'];
			$stage_flag = FALSE;
		}

		if(isset($_REQUEST['city_id']) && !empty($_REQUEST['city_id'])){
			$where .= " AND cadd.c_add_city = ".$_REQUEST['city_id'];
			$stage_flag = FALSE;
		}

		if($stage_flag){
			$stage_where = " AND inq.opp_id NOT IN(12,13)";
		}
		if($_SESSION['user_type']!=2){ 
			$where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
		}

		if (!empty($_SESSION['objection_month'])) {
			$where .= " AND objection_flag=1 AND MONTH(inq.inquiry_date) = MONTH(STR_TO_DATE('".$_SESSION['objection_month']."','%M'))"; 
		}

		if(isset($_REQUEST['product_id']) && !empty($_REQUEST['product_id'])){
			$where .= " AND pro.product_id = ".$_REQUEST['product_id'];
		}

		if(isset($_REQUEST['category_id']) && !empty($_REQUEST['category_id'])){
			$where .= " AND pro.product_category = ".$_REQUEST['category_id'];
		}

		$appData = array();
		$i=1;
		$aColumns = array('inq.inquiry_id','inq.owner_user_id','usr.user_name','owner_usr.user_name as owner', 'inq.inquiry_no', 'inq.inquiry_date', 'city.city_name', 'inq.inquiry_name', 'cust.cust_name','cust.cust_mobile', 'per.c_con_fname','per.c_con_mobile', 'stage.opp_stage','stage.opp_color','inq.stage_prob', 'inq.inquiry_status','task.cdate','inq.mdate','inq.cust_id','inq.g_total','inq.company_id','updated_user.user_name as updated_by','tr.project_wise','mcd.mcd_name','state.state_name','country.country_name','cadd.c_add_state','cadd.c_add_country','cadd.c_add_address','inq.won_user_id','source.rb_name','cust.cust_source');
		$sIndexColumn = "inq.inquiry_id";
		$isWhere = array("inq.inquiry_status = 0  and inq.company_id in (0,$_SESSION[company_id]) ".$stage_where.$where);
		$sTable = "tbl_inquiry as inq";
		$isJOIN = array(
			'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
			'left join product_mst as pro on pro.product_id=tr.product_id',
			'left join tbl_task as task on task.inquiry_id=inq.inquiry_id',
			'left join tbl_customer as cust on cust.cust_id=inq.cust_id',
			'left join tbl_cust_contact as per on per.c_con_id=inq.c_con_id', 
			'left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id', 
			'left join users as usr on usr.user_id=inq.user_id',
			'left join users as owner_usr on owner_usr.user_id=inq.owner_user_id',
			'left join users as updated_user on updated_user.user_id=inq.updated_by_userid',
			'left join tbl_cust_address as cadd on cadd.cust_id=inq.cust_id and cadd.c_add_status=0 and c_addr_defult=1',
			'left join tbl_refer_by as source on source.rb_id=cust.cust_source',
			'left join city_mst as city on city.cityid=cadd.c_add_city',
			'left join state_mst as state on state.stateid=cadd.c_add_state',
			'left join country_mst as country on country.countryid=cadd.c_add_country',
			'left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type');
		$hOrder = "inq.cdate desc";
		$hGroupby = array("inq.inquiry_id");
		include('../include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			if($row['project_wise'] == 0){
				$query_pro = 'select group_concat(pro.product_name SEPARATOR ",<br>") as pro_name FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id='.$row['inquiry_id'];
			} else {
				$query_pro = 'select group_concat(proj.project_name) as pro_name from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id='.$row['inquiry_id'];
			}
		
			$rel = brp_mysqli_fetch_array($dbcon->query($query_pro));

			$bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';
			$row_data[] = date('d M, Y',strtotime($row['inquiry_date']));
			
			$row_data[] = $row['inquiry_no'].' '.$row['COUNT(task.task_id)'];
			$row_data[] = $row['cust_name'].' '.$row['inquiry_name'];
			$row_data[] = $row['cust_mobile'];
			$row_data[] = $row['mcd_name'];
			$row_data[] = $row['rb_name'];
			$row_data[] = $row['c_add_address'];
			$row_data[] = $row['city_name'].' '.$row['state_name'].' '.$row['country_name'];
			$row_data[] = $rel['pro_name'];
			$row_data[] = $row['opp_stage'];

			
			$row_data[] = $row['owner'];

			$row_data[] = $row['user_name'];
			$row_data[] = $row['updated_by'].' updated on '.date('d M, Y',strtotime($row['cdate'])).' by '.date('h:i A',strtotime($row['cdate']));

			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);
			$id++;
		}
	}
	
	//move back to beginning of file
	fseek($f, 0);
	
	//set headers to download file rather than displayed
	// header('Content-Type: text/csv');
	// header('Content-Disposition: attachment; filename="' . $filename . '";');
	$now = gmdate("D, d M Y H:i:s");
	header("Expires: ".date('D M d Y H:i:s O'));
	header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	header("Last-Modified: ".$now." GMT");
	
	// force download  
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	
	// disposition / encoding on response body
	header("Content-Disposition: attachment;filename=".$filename."");
	header("Content-Transfer-Encoding: binary");
	
	//output all remaining data on a file pointer
	fpassthru($f);
	exit;	

function getTaskAssignNameCommaSeparated($dbcon, $assign_user_ids)
{

	$strVal = '';
	$qry = 'SELECT tsk.task_id, GROUP_CONCAT(userdata.user_name) AS valuesdata FROM tbl_task tsk JOIN users AS userdata ON FIND_IN_SET(userdata.user_id, "' . $assign_user_ids . '") GROUP BY tsk.task_id';
		$qry_rel = mysqli_fetch_assoc($dbcon->query($qry));

		if ($qry_rel) {
			$strVal = $qry_rel['valuesdata'];
		}
		return $strVal;
}

function remove_special_char($originalString) {

	// Convert encoding to UTF-8
	$htmlContent = html_entity_decode($originalString);
	
	// Remove special characters from HTML content
	$cleanedContent = preg_replace('/[^\x20-\x7E]/u', '', $htmlContent);
	
	return $cleanedContent;
}
?>