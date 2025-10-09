<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ORDER_ACCEPTANCE_SLUG_LIST,
	ORDER_ACCEPTANCE_SLUG_READ,
	ORDER_ACCEPTANCE_SLUG_CREATE,
	ORDER_ACCEPTANCE_SLUG_EDIT,
	ORDER_ACCEPTANCE_SLUG_APPROVE,
	ORDER_ACCEPTANCE_SLUG_DELETE,
	ORDER_ACCEPTANCE_SLUG_PRINT
]);

if(!in_array(ORDER_ACCEPTANCE_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
		//$branch_id = $POST['branch_id'];	
	$where='';
	$approve_status = ($POST['ac_status']==3) ? 1 : 3;
	$where.=" and estimate.order_accept_status=".$POST['ac_status']." and approve_status =".$approve_status;
	if($POST['ac_status']==3){
		$where.=" and estimate.revise_status = 0";
	}
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$where_db = check_branch('estimate', $branch_id);
	$where.="  and sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'".$where_db;

	if($branch_id){
		$where .= check_branch('estimate',$branch_id);
	}
	$ser = trim(check_crm_find_in_set($dbcon,$_SESSION['user_id'],0),",");
	$where.= " AND estimate.user_id IN (".$ser.")";
	// $where.="  and sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'";

	$appData = array();
	$i=1;
	$aColumns = array('sales_order_id','sales_order_no','sales_order_date','cust.l_name as company_name','city.city_name','g_total','sales_order_status','estimate.cdate','estimate.user_id', 'invoice_status','estimate.approve_status','estimate.order_accept_status','estimate.revise_status','users.user_name','estimate.short_close_status');
	$sIndexColumn = "sales_order_id";
	$isWhere = array("sales_order_status = 0 and estimate.company_id IN (0,$_SESSION[company_id])".$where);
	$sTable = "tbl_sales_order as estimate";			
	$isJOIN = array('left join tbl_ledger cust on estimate.cust_id=cust.l_id','left join city_mst city on cust.cityid=city.cityid','left join users as users on users.user_id=estimate.user_id');
	$hOrder = "estimate.sales_order_id desc";
	$having_clause='';
	include($incPath.'pagging.php');
	$id=1;
	foreach($sqlReturn as $row) {
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		$row_data 	= array();
		$row_data[] = $id;
		$row_data[] = $row['sales_order_no'];
		$row_data[] = date('d M, Y',strtotime($row['sales_order_date']));
		$row_data[] = $row['company_name'];
		$row_data[] = $row['city_name'];
		$row_data[] = $row['g_total'];


		if($row['order_accept_status']==1){
			$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
		}else if($row['order_accept_status']==0){
			$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title="Approve Pending" data-toggle="tooltip" data-placement="top">Approve Pending </button>';
		}else{
			$disapproved = get_oa_disapproved_reason($dbcon,'tbl_oa_aprv_log','approve_remark',$row['sales_order_id'],'approve_status','0','oa_aprv_log_id');
			if(empty($disapproved)){
				$disapproved = get_so_disapproved_reason($dbcon,'tbl_quot_po_aprv_log','approve_remark',$row['sales_order_id'],'approve_status','0','quot_aprv_log_id');
			}
			$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="'.$disapproved.'" data-toggle="tooltip" data-placement="top">Disapproved</button>';
		}
		$invoicestatus='';$delete='';$edit='';$po_apprv_btn='';$sales_order_print='';

		if(in_array(ORDER_ACCEPTANCE_SLUG_PRINT,$bulkAccessArray)){
			$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
			$rels=mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",",$rels['print_permission']);
			if($getspecialConfiguration['elcon_permission'] ==1){
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type IN (3,9,13) AND approve_status = 1 AND status = 0 ORDER BY priority");
			}else{
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type IN (3,9) AND approve_status = 1 AND status = 0 ORDER BY priority");
			}
			while($res = mysqli_fetch_assoc($sql)){
				if(in_array($res['id'],$menu_show_permissions)) {
					$sales_order_print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['sales_order_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
				}
			}
		}

		if(in_array(ORDER_ACCEPTANCE_SLUG_APPROVE,$bulkAccessArray)){
			$po_apprv_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve/Reject Order Accept." data-toggle="tooltip" data-placement="top" onClick="open_po_approv_payment('.$row['sales_order_id'].',\''.$row["sales_order_no"].'\')"><i class="fa fa-exclamation-triangle"></i></button>';
		}

		$query="select (select IFNULL(sum(product_qty),0) as qty from tbl_sales_ordertrn as sosub  where sales_ordertrn_status=0 and sosub.sales_order_id=so.sales_order_id ) as soqty,(select IFNULL(sum(product_qty),0) as qty  from tbl_invoice as chall left join tbl_invoicetrn as chtrn on chtrn.invoice_id=chall.invoice_id where invoice_status=0 and chtrn.trancation_status=0 and chall.sales_order_id=so.sales_order_id) as invqty from tbl_sales_order as so where sales_order_status=0   and so.sales_order_id=".$row['sales_order_id']." and company_id=".$_SESSION['company_id'];
		$rs_dispatch=$dbcon->query($query);
		$rel=mysqli_fetch_assoc($rs_dispatch);

		if($row["invoice_status"]=="1")
		{
			$invoicestatus='<a class="btn btn-xs btn-primary" data-original-title="Invoice Done" data-toggle="tooltip" data-placement="top" href="Javascript:;">
			<i class="fa fa-thumb-up">Invoice Done</i>
			</a>';
			if($row['short_close_status']==1){
				$short_close = '<button class="btn btn-xs btn-danger" data-original-title="SO Short Closed" data-toggle="tooltip" data-placement="top">SO Short Closed</button>';
			}else{
				$short_close='';
			}
			$po_apprv_btn = '';
		}
		else
		{
			// if(in_array(ORDER_ACCEPTANCE_SLUG_DELETE,$bulkAccessArray)){
			// 	$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sales_order('.$row['sales_order_id'].')"><i class="fa fa-trash-o"></i></button>';
			// }
			// if(in_array(ORDER_ACCEPTANCE_SLUG_EDIT,$bulkAccessArray)){
			// 	$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'salesorderedit/'.$row['sales_order_id'].'"><i class="fa fa-pencil"></i></a>';
			// }

			$stagestatus='<a class="btn btn-xs btn-success" data-original-title="Stage" data-toggle="tooltip" data-placement="top" href="'.ROOT.'sales_order_stage/'.$row['sales_order_id'].'">
			Stage Process
			</a>';
		}

		$query1="SELECT sum(work_order_qty) as perm from tbl_sales_ordertrn as so where sales_ordertrn_status=0 and so.sales_order_id=".$row['sales_order_id']." and branch_id=".$_SESSION['company_id'];
		$rs_dispatch1=$dbcon->query($query1);
		$rel1=mysqli_fetch_assoc($rs_dispatch1);

		if($rel1['perm']>0){
			$po_apprv_btn="";
		}
		$revise = '';$so_emend='';
		if($row['order_accept_status']==3){
			$revise = '';
			$po_apprv_btn = '';
			$sales_order_print = '';
			$so_emend = '<a class="btn btn-xs btn-info" data-original-title="Sales Order Amend" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'sales_order_emend/'.$row['sales_order_id'].'"><i class="fa fa-repeat"></i></a>';
		}
		if($row['revise_status']==1){
			$revise = '<button type="button" class="btn btn-xs btn-primary" data-original-title="SO Revised" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i></button>';
			$po_apprv_btn = '';
			$sales_order_print = '';
			$so_emend='';
		}
		$stagestatus="";
		$short_close ="";
		$row_data[] = $row['user_name'];
		$row_data[] = $sales_order_print.' '.$invoicestatus.' '.$po_apprv_btn.' '.$so_emend.' '.$revise.' '.$short_close;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_po_hist_datatable") {
	$where='';
	if($POST['sales_order_id']){
		$where.="  and log.so_id=".$POST['sales_order_id'];
	}

	$appData = array();
	$i=1;
	$aColumns = array('log.oa_aprv_log_id','log.so_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
	$sIndexColumn = "log.oa_aprv_log_id";
	$isWhere = array("log.so_aprv_log_status = 0 ".$where." ");
	$sTable = "tbl_oa_aprv_log as log";
	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
	$hOrder = "log.oa_aprv_log_id desc";
	include($incPath.'pagging.php');
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['user_name'];

		if($row['approve_status']=='1'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
		}
		else{
			$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
		}
		$delete_btn = '';
		if(in_array(ORDER_ACCEPTANCE_SLUG_DELETE,$bulkAccessArray) && $row['sr']==1){
			$delete_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_approve_log('.$POST['sales_order_id'].','.$row['oa_aprv_log_id'].','.$row['approve_status'].',2)"><i class="fa fa-trash-o"></i></button>';
		}

		$row_data[] = nl2br($row['approve_remark']);
		$row_data[] = date("d-M-Y h:i A",strtotime($row['created_at']));
		$row_data[] = $delete_btn;

		$appData[] = $row_data;
		$id++;
			//print_r($row_data);
	}
	$output['aaData'] = $appData;
		//print_r($output);
	echo json_encode( $output );
	}// Dimple Panchal : Start

	else if(strtolower($POST['mode']) == "add_po_apprv_hists") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		// check if user has already Approved or Rejected Quotation
		$check_hist_qry = "selec log.oa_aprv_log_id, usr.user_name, log.approve_status, log.approve_remark, log.created_at, log.user_id 
		FROM tbl_oa_aprv_log as log left join users as usr on usr.user_id=log.user_id 
		where log.so_aprv_log_status=0 and log.so_id=".$POST['sales_order_id']." and log.user_id = ".$_SESSION['user_id']."
		order by log.oa_aprv_log_id desc limit 1";
		$result = brp_mysqli_query($dbcon,$check_hist_qry);
		$history_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

		if($history_data[0]['approve_status'] !== $POST['approve_status']) {
			$info1['approve_remark']	= $POST['approve_remark'];
			$info1['approve_status']	= $POST['approve_status'];
			$info1['so_id']             = $POST['sales_order_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];

			$insert_id=add_record("tbl_oa_aprv_log", $info1, $dbcon);

			if($insert_id){
				$q = "select pro.bom_required,trn.sales_ordertrn_id from tbl_sales_order as so 
				left join tbl_sales_ordertrn as trn on trn.sales_order_id=so.sales_order_id
				left join product_mst as pro on pro.product_id = trn.product_id
				where so.sales_order_id=".$POST['sales_order_id'];				
				$re = brp_mysqli_query($dbcon,$q);
				while($row = mysqli_fetch_array($re)){
					if($POST['approve_status'] == 1){
						if($row['bom_required']==1){
							$infop['bom_status'] = 0;
						}else{
							$infop['bom_status'] = 1;
						}
					}else{
						$infop['bom_status'] = 0;
					}
					$updateid=update_record('tbl_sales_ordertrn', $infop,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon, $branch_id);
				}
				if($POST['approve_status'] == 1){
					$infoso['order_accept_status'] = $POST['approve_status'];
				}else{
					$infoso['order_accept_status'] = 3;
					$infoso['approve_status'] = 1;
				}
				$updateid=update_record('tbl_sales_order', $infoso,"sales_order_id=".$POST['sales_order_id'] , $dbcon, $branch_id);

				$infoquot['po_approve_status'] = 3;
				$updateid=update_record('tbl_quotation', $infoquot,"sales_order_id=".$POST['sales_order_id'] , $dbcon, $branch_id);
			}
			echo TRUE;
		} else {
			echo FALSE;
		}
	}else if(strtolower($POST['mode']) == "load_party_po_dtl") {
		$qt_qry="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no,led.l_id,led.credit_limit,led.credit_days from tbl_sales_order as qt
		left join tbl_ledger as led on led.l_id=qt.cust_id
		left join country_mst as country on country.countryid=led.countryid
		left join state_mst as state on state.stateid=led.stateid
		left join city_mst as city on city.cityid=led.cityid
		where qt.sales_order_id=".$POST['sales_order_id'];
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_qry));

		$str='';$stri='';
		$str.='<div class="form-group">
		<table class="display table table-bordered table-striped">
		<tr>
		<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['company_name'].'</td>
		<td><strong>Contact No.:</strong> '.$qt_rel['cust_mobile'].'</td>
		</tr>
		<tr>
		<td colspan="2"><strong>Address:</strong> '.$qt_rel['m_address'].'</td>
		<td><strong>GST No.:</strong> '.$qt_rel['gst_no'].'</td>
		</tr>
		<tr>
		<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
		<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
		<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
		</tr>
		<tr>
		<td><strong>Sales order No:</strong> '.$qt_rel['sales_order_no'].'</td>
		<td><strong>Sales Order Date:</strong> '.date("d-M-Y",strtotime($qt_rel["sales_order_date"])).'</td>
		<td><strong>Sales Order Amount:</strong> '.$qt_rel['g_total'].'</td>
		</tr>';
		$str.='</table></div>
		<hr/>
		';
		// var_dump($str);
		$query="SELECT mst.*,sales_ordertrn_id,if(mst.project_wise=0,(SELECT product_name FROM product_mst as pro WHERE pro.product_id=mst.product_id) ,(SELECT project_name FROM tbl_project_assign as proj WHERE proj.project_assign_id=mst.product_id)) as product_name,cat.unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode FROM tbl_sales_ordertrn as mst 
		left join unit_mst as cat on cat.unitid=mst.unit_id 
		left join product_mst as product on product.product_id=mst.product_id  
		WHERE sales_ordertrn_status=0 and sales_order_id=".$POST['sales_order_id'];

		$result=$dbcon->query($query);
		$stri = ' <div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
		<tr id="field">
		<th class="text-center"width="25%">Product Name</th>
		<th class="text-center"width="8%">HSN Code</th>
		<th class="text-center"width="8%">Qty</th>
		<th class="text-center"width="10%">Rate <span class="currency_icon"></span></th>
		<th class="text-center"width="8%">Discount <span class="currency_icon"></span></th>
		<th class="text-center" width="8%">Tax Details <span class="currency_icon"></span></th>
		<th class="text-center"width="12%">Amount <span class="currency_icon"></span></th>
		</tr>';
		$i=1;
		while($rel=mysqli_fetch_assoc($result))
		{
			$cgst_tax="";				
			$sgst_tax="";				
			$igst_tax="";	
			if($rel['unit_id']===$rel['rate_unit']){
				$sqty=$rel['product_qty'];
			}else{
				$sqty=$rel['product_conv_qty'];
			}
			if($rel['cgst_tax_per']!=0)
			{
				$cgst_tax="<Strong>CGST (".$rel['cgst_tax_per'].") : </strong>".$rel['cgst_tax_rate']."<br>";
			}
			if($rel['sgst_tax_per']!=0)
			{
				$sgst_tax="<Strong>SGST (".$rel['sgst_tax_per'].") : </strong>".$rel['sgst_tax_rate']."<br>";
			}

			if($rel['igst_tax_per']!=0)
			{
				$igst_tax="<Strong>IGST (".$rel['igst_tax_per'].") : </strong>".$rel['igst_tax_rate']."<br>";
			}

			$stri.= '<tr id="fieldtr'.$id.'" >
			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			</td>
			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['product_hsn_code'])){
				$stri.= '-';
			}else{
				$stri.= $rel['product_hsn_code'];
			}
			$stri.='</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			'.$rel['product_qty'].' '.$rel['unit_name'].'
			</td>
			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
			'.$rel['product_rate'].'
			</td>
			<td data-label="DISCOUNT" style="vertical-align:top" class="text-right">
			'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
			</td>

			<td>'.$cgst_tax.''.$sgst_tax.''.$igst_tax.'</td>
			<td data-label="AMOUNT" style="vertical-align:top" class="text-right">
			'.$rel['product_amount'].'
			</td>	
			</tr>';
			$i++;
		}
		$stri.= '</table>			 
		</div>
		<hr/>';
		$qt_rel['mod_so_comp_div_sec'] = $str;
		$qt_rel['mod_so_pro_div_sec'] = $stri;
		echo json_encode($qt_rel);
	}else if(strtolower($POST['mode']) == "delete_approve_log") {
		if($POST['type']==1){
			$info['quot_aprv_log_status'] = 2;
			$table = 'tbl_quot_po_aprv_log';
			$table_id = 'quot_aprv_log_id';
			$status = 'quot_aprv_log_status';
			$so_id = 'sales_order_id';
		}else{
			$info['so_aprv_log_status'] = 2;
			$table = 'tbl_oa_aprv_log';
			$table_id = 'oa_aprv_log_id';
			$status = 'so_aprv_log_status';
			$so_id = 'so_id';
		}	
		$updateid = update_record($table,$info,$table_id."=".$POST['approve_id'],$dbcon);
		$chkcnt = $dbcon->query("SELECT * FROM ".$table." WHERE ".$status."=0 AND ".$so_id."=".$POST['sales_order_id']." ORDER BY ".$table_id." DESC LIMIT 1");
		if(brp_mysqli_num_rows($chkcnt)>0){
			$getcnt = brp_mysqli_fetch_assoc($chkcnt);
			if($getcnt['approve_status']==0){
				$infotrn['order_accept_status'] = 3;
				$infotrn['approve_status'] = 1;
			}else{
				if($POST['type']==1){
					$infotrn['order_accept_status'] = 0;
					$infotrn['approve_status'] = 3;
				}else{
					$infotrn['order_accept_status'] = 1;
					$infotrn['approve_status'] = 3;
				}
			}
		}else{
			if($POST['type']==1){
				$infotrn['order_accept_status'] = 0;
				$infotrn['approve_status'] = 0;
			}else{
				$infotrn['order_accept_status'] = 0;
				$infotrn['approve_status'] = 3;
			}
		}
		$updateides = update_record('tbl_sales_order',$infotrn," sales_order_id=".$POST['sales_order_id'],$dbcon);
		if($updateid){
			echo "1";
		}else{
			echo "0";
		}
	}
?>