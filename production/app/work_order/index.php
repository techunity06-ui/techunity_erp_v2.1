<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
error_reporting(E_ALL);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_WORK_ORDER_SLUG_VIEW,PRODUCTION_WORK_ORDER_SLUG_CREATE,PRODUCTION_WORK_ORDER_SLUG_UPDATE,PRODUCTION_WORK_ORDER_SLUG_DELETE,PRODUCTION_WORK_ORDER_SLUG_PRINT
]);		


							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}else {
			$POST = bulk_filter($dbcon,$_GET);
		}

		$company_config = getCompanyConfiguration($dbcon);
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		$is_store_approval = $company_config['store_approval'];
		if(brp_strtolower($POST['mode']) == "fetch") {
			$getspecialConfiguration=getspecialConfiguration($dbcon);
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];

			$wher = '';
			if($_SESSION['user_type']!=2){
			//$wher=" and `sep`.`user_id`='".$_SESSION['user_id']."'";
			}
			
			$finish_status = $POST['workorder_status'];
		

			/*$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$where_db = check_branch('estimate', $branch_id);
			$where.="  and sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'".$where_db;
*/
			if($_SESSION['user_type'] != '2'){
				//$where_db = " and sep.branch_id=".$_SESSION['branch_id'];
			}

			if(!empty($POST['branch_id'])){
				$where_db = " and sep.branch_id=".$POST['branch_id'];
			}

		  $appData = array();
		  $i=1;
	
		  $aColumns = array('str.doc_no','sep.sp_id','sep.po_req_no','so.sales_order_no','so.sales_order_date','p.product_name','branc.branch_name','sep.priority_status','p.product_icode','l.l_name','sep.sp_status','sep.company_id','sep.finish_status','sep.vendor_id','sep.product_id','sep.po_req_date','sep.rp_req_qty','sep.rp_po_qty','sep.in_process_qty_main','sep.bom_costing_id','sep.sales_order_trn_id','sep.shortclose_qty','sep.store_order_id','sep.packing_status');

		  $sIndexColumn = "sep.sp_id";

		  // $isWhere = array("sp_status != 2 and sep.company_id='".$_SESSION['company_id']."'".$wher." and sep.finish_status = ".$finish_status.$where_db);

		  $isWhere = array("workorder_type = 0 and sp_status != 2 and sep.company_id='".$_SESSION['company_id']."'".$wher." and sep.finish_status = ".$finish_status.$where_db);

		  $sTable = "tbl_set_main_process as sep";			
		  $isJOIN = array(
		  	'left join tbl_ledger as l ON sep.vendor_id = l.l_id',
		  	'left join product_mst as p ON sep.product_id = p.product_id',
		  	'left join tbl_store_order_min_max as str ON str.order_id = sep.store_order_id',
		  	'left join tbl_sales_ordertrn as so_trn ON so_trn.sales_ordertrn_id = sep.sales_order_trn_id',
		  	'left join tbl_sales_order as so ON so_trn.sales_order_id = so.sales_order_id',
		  	'left join branch_mst as branc on branc.branch_id=sep.branch_id '
		  );
		  $hOrder = "sep.sp_id desc";
		//$hGroupby = array("quot.inquiry_id");
		  /*END*/
		  $having_clause = '';
		include($include."pagging.php");
		 
		  $id=1;
		  foreach($sqlReturn as $row) {
		  	$row_data = array();
				/*if($row['sp_status']=='1'){
					$stage = 'Planning Done';
				}else{
					$stage = 'Partially Planning ';
				}*/

				$stage = check_workorder_stag_status($dbcon,$row['sp_id']);
				if($row['l_name']==''){
					$lname = '--';
				}
				else{
					$lname = $row['l_name'];
				}
				
				$wquery="select rp_id, rp_req_type, reject_status,finish_used_qty from tbl_request_product where main_request = 1 and sp_id=".$row['sp_id']; 
				$wresult=$dbcon->query($wquery);
				$wrow=brp_mysqli_fetch_assoc($wresult);

				$reject_qty = 0;
				$complete_qty = 0;

				if($wrow['reject_status'] != '' && $wrow['reject_status'] > 0){
					$reject_qty = $wrow['reject_status'];
				}
				if($wrow['finish_used_qty'] != '' && $wrow['finish_used_qty'] > 0){
					$complete_qty = $wrow['finish_used_qty'];
				}

				$po_req_date='';
				
				if($row['po_req_date']=='0000-00-00' || $row['po_req_date']=='' || $row['po_req_date']=='1970-01-01'){
					$po_req_date= date('d M, Y');
				}else{
					$po_req_date= date('d M, Y',strtotime($row['po_req_date']));
				}

				$short_close_qty = 0;
				if(!empty($row['shortclose_qty'])){
					$short_close_qty = $row['shortclose_qty'];
				}

				$workorder_type = "";

				if($wrow['rp_req_type'] == 'direct'){
					$workorder_type = "Direct Workorder";
				}else if($wrow['rp_req_type'] == 'sales_order' && $row['store_order_id'] > 0){
					$workorder_type = "Store Request Order";
				}else if($wrow['rp_req_type'] == 'min_max'){
					$workorder_type = "Min Max Planning";
				}else if($wrow['rp_req_type'] == 'sales_order' && $row['sales_order_trn_id'] > 0){
					$workorder_type = "Salesorder";
				}else if($wrow['rp_req_type'] == 'sales_order' && $row['sales_order_trn_id'] == 0 && $row['store_order_id'] == 0){
					$workorder_type = "Reject Product Agains Workorder";
				}else{
					$workorder_type = "Workorder";
				}

				// min_max
				// direct
				// work_order
				// short_close
				// job_card
				// store_request_order

		  		$row_data[] = $row['po_req_no'];
		  		$row_data[] = date('d M, Y',strtotime($row['po_req_date']));
		  		$row_data[] = $row['doc_no'];
				$row_data[] = ($row['sales_order_trn_id'] > 0) ? $row['sales_order_no'] : "";
		  		$row_data[] = ($row['sales_order_trn_id'] > 0) ? date('d M, Y',strtotime($row['sales_order_date'])) : '';
		  		// $row_data[] = $row['product_icode'];
				$row_data[] = $row['product_name'] . " -- (". $row['product_icode'] .")";
		  		$row_data[] = $workorder_type;  
		  		$row_data[] = $row['rp_req_qty'];
		  		$row_data[] = $complete_qty;
		  		$row_data[] = number_format($row['rp_req_qty'] - $complete_qty,4);
		  		$row_data[] = $reject_qty;
		  		$row_data[] = $short_close_qty;
		  		$row_data[] = $stage;
		  		$row_data[] = $row['priority_status'];
		  		if($company_config['customer_show_in_production']){
			  		$row_data[] = $lname;
			  	}
		  		$row_data[] = $row['branch_name'];	
		  		$print_btn = '';
		  		$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 11 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['sp_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
					}
				}

				if(!in_array(PRODUCTION_WORK_ORDER_SLUG_PRINT,$bulkAccessArray)){
					$print_btn = "";
				}
				$btn_bom_costing = "";

				$btn_priority ='<a class="btn btn-xs btn-lock" data-original-title="Change priority" data-toggle="tooltip" onclick="open_priority_alert('.$row['sp_id'].');"><i class="fa fa-retweet"></i> Change Priority</a>';

				if($row['bom_costing_id'] == 0){
					$btn_bom_costing ='<a class="btn btn-xs btn-warning" data-original-title="Allocate BOM Costing" data-toggle="tooltip" onclick="assign_bom_costing('.$row['sp_id'].');"><i class="fa fa-money"></i></a>';
				}

				
				$costing_print = '<a class="btn btn-xs btn-danger" data-original-title="Workorder Costing" data-toggle="tooltip" href="'.ROOT.REPORT_ROOT.'workorder_costing/'.$row['sp_id'].'"><i class="fa fa-print"></i></a>';

		  		$edit ="";
		  		$delete ="";

				$chk_req_qry = "select count(rp_id) as child_requested from tbl_request_product where status = 0 and main_request = 0 and sp_id = " .$row['sp_id'];

				$chk_req_res = $dbcon->query($chk_req_qry);
				$chk_req_row = brp_mysqli_fetch_assoc($chk_req_res);
				$delete="";
				$btn_wo_print_data = "";

				if($getspecialConfiguration['libra_engineering_permission'] == '1'){
					$btn_wo_print_data = '<a class="btn btn-xs btn-primary"  data-original-title="Print" data-toggle="tooltip"  href="'.ROOT.PRODUCTION_ROOT.'libra_workorder_print_filed_add/'.$row['sp_id'].'"><i class="fa fa-plus-square"></i></a>';	
				}	

				if(in_array(PRODUCTION_WORK_ORDER_SLUG_DELETE,$bulkAccessArray)){
					if($chk_req_row['child_requested'] == 0){
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_workorder('.$row['sp_id'].')"><i class="fa fa-trash-o"></i> Delete</button>'; 
					}
				}


				$btn_packing = "";
				$btn_packing_print  = "";
				if($getspecialConfiguration['creative_fastners_permission'] == '1' && $finish_status == '1' && $row['packing_status'] == '0'){
					$btn_packing = '<a style="margin:5px" class="btn btn-xs btn-primary" data-original-title="Workorder Packing" data-toggle="tooltip" href="'.ROOT.PRODUCTION_ROOT.'workorder_packing/'.$row['sp_id'].'"><i class="fa fa-archive"></i> Packing</a>';

				}
				if($getspecialConfiguration['creative_fastners_permission'] == '1' && $finish_status == '1'){
					$btn_packing_print = '<a style="margin:5px" class="btn btn-xs btn-primary" data-original-title="Workorder Packing Print" data-toggle="tooltip" href="'.ROOT.PRODUCTION_ROOT.'workorderpackingprint/'.$row['sp_id'].'"><i class="fa fa-archive"></i> Packing Print</a>';
				}


				if(in_array(PRODUCTION_WORK_ORDER_SLUG_UPDATE,$bulkAccessArray)){
     				$edit = '<a class="btn btn-xs btn-warning"  data-original-title="Edit" data-toggle="tooltip"  href="'.ROOT.PRODUCTION_ROOT.'edit_workorder/'.$row['sp_id'].'"><i class="fa fa-pencil"></i></a>';			
    			}

				$view_attc='<button class="btn btn-xs btn-info" data-original-title="View Attchament" data-toggle="tooltip" data-placement="top" onClick="show_workorder_image('.$row['sp_id'].')"><i class="fa fa-eye"></i></button>';
				
				$short_close='<a onclick="workorder_shortclose('.$row['sp_id'].')" class="btn btn-xs btn-danger" data-original-title="Short Close Workorder" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i> Short Close</a>';

				$live_tracking_btn = "";
				if(in_array(PRODUCTION_WORK_ORDER_SLUG_PRINT,$bulkAccessArray)){
					$live_tracking_btn = '<a class="btn btn-xs btn-primary" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'work_order_new_print/'.$row['sp_id'].'">Live Production Report</a>';
				}

				$row_data[] = $edit.'
		  		<!--<a class="btn btn-xs btn-warning" data-original-title="Work Order Details" data-toggle="tooltip" onclick="get_item_information('.$row['sp_id'].','.$row['product_id'].','.$row['vendor_id'].');"><i class="fa fa-eye"></i></a>
		  		<a class="btn btn-xs btn-warning" data-original-title="Reports" data-toggle="tooltip" onclick="reports();"><i class="fa fa-eye"></i></a>
		  		<a class="btn btn-xs btn-warning" data-original-title="Notes" data-toggle="tooltip"  onclick="notes();"><i class="fa fa-eye"></i></a>
		  		<a class="btn btn-xs btn-warning" data-original-title="Login Details" data-toggle="tooltip" onclick="get_vendor_details('.$row['sp_id'].');"><i class="fa fa-eye"></i></a>-->
		  		<a class="btn btn-xs btn-success" data-original-title="Workorder Approve" data-toggle="tooltip" href="workorder_permission/'.$row['sp_id'].'"><i class="fa fa-exclamation-triangle"></i></a>&nbsp;'.$live_tracking_btn.' '.$print_btn.'
		  		'.$btn_bom_costing.' '.$short_close.'  '.$costing_print . '  '.$delete . '  '.$view_attc. '  '.$btn_packing.'  '.$btn_packing_print . '  '.$btn_wo_print_data . ' ' . $btn_priority;

		  	$appData[] = $row_data; 
		  	$id++;
		  }
		  $output['aaData'] = $appData;
		  echo json_encode( $output );
		}
		else if(brp_strtolower($POST['mode']) == "add") {
		}
		else if(strtolower($POST['mode']) == "delete_image") {
			$id	= $POST['id'];

			$image_de = "select * from tbl_workorder_attachments where attach_id=".$id;
			$result = $dbcon->query($image_de);
			$row = brp_mysqli_fetch_array($result);

			unlink($row['file_path'].$row['file_name']);

			$sql = "UPDATE tbl_workorder_attachments SET status = 2 WHERE attach_id='".$id."' ";	
			$updatetrancationid = $dbcon->query($sql);		
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}		
		else if(brp_strtolower($POST['mode']) == "view_workorder_image") {
			$work_order_id = $POST['work_order_id'];

			$qry="SELECT * FROM `tbl_workorder_attachments` Where status = 0 and `company_id`='".$_SESSION['company_id']."' AND sp_id = " . $work_order_id;

				$result=$dbcon->query($qry);

				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">Image Name</th>
								<th class="text-center" width="25%">View</th>
								<th class="text-center" width="25%">Action</th>
							</tr>';
							
							//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{

					$exts = array('gif', 'png', 'jpg'); 
					if(in_array(end(explode('.', $rel['file_name'])), $exts)){

						$filetype = '<a href="'.ROOT.'view/upload/workorder_attachmen/'.$rel["file_name"].'" target="_blank"><img src="'.ROOT.'view/upload/workorder_attachmen/'.$rel["file_name"].'" class="img-thumbnail" width="70" height="70"></a>';
					}else{
						$filetype = '<a href="'.ROOT.'view/upload/workorder_attachmen/'.$rel["file_name"].'" target="_blank">Download File</a>';
					}	
					
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$rel['image_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>

						<td style="vertical-align:top;" class="text-center">
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_image('.$rel['attach_id'].','.$work_order_id.');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="4" style="text-align:center">NO DATA FOUND</td></tr>';
			}

		}
		else if(brp_strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		
		else if(brp_strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
		}

		else if(brp_strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
		else if(brp_strtolower($POST['mode'])== "get_po_login")
		{
			$id = $POST['id']; // as table id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `po`.`sp_status` as stage FROM `tbl_set_main_process` as po left join `users` as u ON  `po`.`user_id` = `u`.`user_id` left join `users` as mu ON  `po`.`muser_id` = `mu`.`user_id`  Where `po`.`sp_id`='".$id."' and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=brp_mysqli_fetch_assoc($vrow);
				
			if($rel['stage']=='1'){
			 	$stage = 'Approved';
			 }else{
			 	$stage = 'Pending';
			 }
					
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Login History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.(($rel["cdate"]!='' && $rel['cdate']!="1970-01-01" && $rel['cdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["cdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.(($rel["mdate"]!='' && $rel['mdate']!="1970-01-01" && $rel['mdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["mdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved By </span>: </p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: </p>
                             </div>
                             <div class="bio-row">
                                 <p><span> Stage </span>: '.$stage.'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}

		else if(brp_strtolower($POST['mode'])== "get_item_selected_information")
		{
			$id = $POST['id'];
			$product_id = $POST['product_id'];
			$vendor_id = $POST['vendor_id'];


			$sql = "SELECT sep.*,`l`.`l_name`, `pm`.`product_type`, `pm`.`product_name`,`pm`.`product_desc` FROM `tbl_set_main_process` as sep 
				    left join tbl_ledger as l ON `sep`.`vendor_id` = `l`.`l_id` 
				    left join product_mst as pm ON `sep`.`product_id` = `pm`.`product_id` 
				    WHERE  `sep`.`sp_id`='".$id."' AND `sep`.`company_id`='".$_SESSION['company_id']."'";
			$rel=$dbcon->query($sql);
			$result=brp_mysqli_fetch_assoc($rel);

			if($result['sp_status']=='0'){
				$status='Pending';
			}else{
				$status='Approved';
			}

			$arr['po_req_no'] = $result['po_req_no'];
			$arr['po_req_date'] = date('d-m-Y', strtotime($result['po_req_date']));
			$arr['so_no'] = 'NA';
			$arr['so_date'] = date('d-m-Y');
			$arr['status'] = $status;
			$arr['vender_id'] =  $result['l_name'];
			$arr['vendor_po_number'] = $result['po_no'];
			$arr['vender_po_date'] = date('d-m-Y', strtotime($result['po_date']));
			$arr['product_type'] = get_pro_type_name($result['product_type']);
			$arr['product_id'] = $result['product_name'];
			$arr['item_description'] = $result['product_desc'];
			$arr['order_start_date'] = date('d-m-Y');
			$arr['order_delivery_date'] = date('d-m-Y');
			$arr['ds_number'] = 'NA';
			$arr['bom_no'] = $result['bom_no'];
			$arr['bom_id'] = $result['bom_id'];
			$arr['order_qty'] = $result['rp_req_qty'];
			$arr['remark'] = '0';
			$arr['report']= '<a class="btn btn-primary" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'work_order_new_print/'.$result['sp_id'].'">Live Production Report</a>';
			
			$arr['vendorId'] = $vendor_id;
			echo json_encode($arr);
		}
		else if(brp_strtolower($POST['mode']) == "get_bom_costing") {
			$sp_id = $POST['sp_id'];

			$sql = "SELECT * from tbl_set_main_process WHERE  sp_id =" . $sp_id;
			$rel=$dbcon->query($sql);
			$result=brp_mysqli_fetch_assoc($rel);

			echo get_bom_costing($dbcon,$result['product_id'],$result['bom_id'],'');
		}

		else if(brp_strtolower($POST['mode']) == "bom_costing_assign") {
			
			$info['bom_costing_id'] = $POST['bom_costing_id'];	
			$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$POST['sp_id'] , $dbcon);

			if($updateid){
				$arr['msg'] = "1";
			}else{
				$arr['msg'] = "0";
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "delete") {
		   	$info['sp_status']	= 2;
		   	$info1['status']	= 2;
		   	$info2['p_status']	= 2;
		   	$sp_id = $POST['eid'];

		   	$updateestimateid=update_record('tbl_set_main_process', $info,"sp_id=".$POST['eid'] , $dbcon);	
		   	$updatetrancationid=update_record('tbl_request_product', $info1,"sp_id=".$POST['eid'] , $dbcon);
		   	

		   	$qry = "SELECT sales_order_trn_id,(SELECT GROUP_CONCAT(rp_id) FROM tbl_request_product where sp_id = ".$sp_id.") as rp_id FROM tbl_set_main_process WHERE sp_id = " . $sp_id;
		   	$res = $dbcon->query($qry);
		   	$row = brp_mysqli_fetch_assoc($res);

		   	if(!empty($row['sales_order_trn_id'])){
		   		$so_info['sales_order_production_status']	= 2;
				$updateestimateid=update_record('tbl_sales_order_production_trn', $so_info,"request_id in (".$row['rp_id'].") OR sales_ordertrn_id = " . $row['sales_order_trn_id'], $dbcon);		
		   	}
			$updatetrancationid=update_record('tbl_allocate_process', $info2,"p_ref_id in(".$row['rp_id'].")" , $dbcon);			
			$info3['status']	= 2;		   	
			$updateestimateid=update_record('wip_stock_allocate', $info3,"rp_id in (".$row['rp_id'].")", $dbcon);	
		   	
		   	if($updateestimateid)
		   		echo "1";	
		   	else
		   		echo "0";			
	   }
	   else if(strtolower($POST['mode']) == "workorder_shortclose") {
		$sp_id = $POST['sp_id'];

		$qry =  "SELECT * from tbl_request_product where main_request = 1 and sp_id = " . $sp_id;
		$row = brp_mysqli_fetch_assoc($dbcon->query($qry));

		$short_close_qty = $row['rp_req_qty'] - $row['finish_used_qty'];
		
		$info['workorder_short_close']	= 1;
		$info['finish_status']	= 1;

		$info1['workorder_short_close']	= 1;
		$info1['finish_status']	= 1;

		$info['shortclose_qty']	= $short_close_qty;
		$info2['shortclose_qty']	= $short_close_qty;
		$info2['finish_used_qty']	= $row['rp_req_qty'];
		

		$update_id=update_record('tbl_set_main_process', $info,"sp_id =".$sp_id, $dbcon);	
		$update_id1=update_record('tbl_request_product', $info1,"sp_id =".$sp_id, $dbcon);	
		$update_id2=update_record('tbl_request_product', $info2," main_request = 1 and sp_id =".$sp_id, $dbcon);


		$rp_id = $row['rp_id'];	

		$chk_qry = "SELECT * FROM tbl_request_product WHERE status = 0 AND perent_id = " . $row['rp_id'];	
		$chk_res = $dbcon->query($chk_qry);
		while($rp_row = brp_mysqli_fetch_assoc($chk_res)){
			$having_child = check_having_child_product($dbcon,$rp_row['rp_id']);

	
			if($having_child == '0'){

				$ap_qry = "SELECT p_id FROM tbl_allocate_process WHERE  p_status in (0,1) and p_ref_id = " . $rp_id;
				$ap_result = $dbcon->query($ap_qry);

				while ($ap_row = brp_mysqli_fetch_assoc($ap_result)) {

					$apinfo['p_status'] = 3;
					$update_id222=update_record('tbl_allocate_process', $apinfo," p_id =".$ap_row['p_id'], $dbcon);
					
					$query_dstock = "select i.*,(cast(i.base_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(base_stock),0) as bstock from tbl_reserve_stock where stock_status != 2 and request_id =" .$rp_row['rp_id']. " and stock_flage = 2 and p_id=".$ap_row['p_id'] ." and stock_id = i.stock_id),0)) as pending_base_stock,(cast(i.convert_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(convert_stock),0) as cstock from tbl_reserve_stock where stock_status != 2 and stock_flage = 2 and request_id =" .$rp_row['rp_id']. "  and p_id=".$ap_row['p_id'] ." and stock_id = i.stock_id),0)) as pending_conv_stock from tbl_reserve_stock as i left join tbl_stock_trn as st on st.stock_id = i.stock_id where i.stock_status != 2 and i.stock_flage=1  and i.request_id =" .$rp_row['rp_id']. " and i.product_id=".$rp_row['rp_pid']." and i.p_id=".$ap_row['p_id'];	
					$result_dstock=$dbcon->query($query_dstock);
					while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
						$pending_base_stock=$row_dstock['pending_base_stock'];	
						$pending_conv_stock=$row_dstock['pending_conv_stock'];	

						$reserve_id = $row_dstock['reserve_id'];
						$stock_id = $row_dstock['stock_id'];
						$arr_reserve_stock = [];
						foreach($row_dstock as $key => $value){
							$arr_reserve_stock[$key] = $value;
						}

						unset($arr_reserve_stock['reserve_id']);
						unset($arr_reserve_stock['pending_base_stock']);
						unset($arr_reserve_stock['pending_conv_stock']);
						
						$arr_reserve_stock['cdate'] = date("Y-m-d H:i:s");
						
						$arr_reserve_stock['base_stock'] = $pending_base_stock;
						$arr_reserve_stock['convert_stock'] = $pending_conv_stock;	
						$arr_reserve_stock['approve_base_stock'] = 0;
						$arr_reserve_stock['approve_convert_stock'] = 0;
						$arr_reserve_stock['used_base_stock'] = 0;
						$arr_reserve_stock['used_convert_stock'] = 0;
						$arr_reserve_stock['stock_flage'] = 2;	
						$arr_reserve_stock['perent_id'] = $reserve_id;	
						
						$new_rs_id=add_record('tbl_reserve_stock', $arr_reserve_stock, $dbcon);

						$res_upd_st['used_base_stock'] = $row_dstock['used_base_stock'] + $pending_base_stock;
						$res_upd_st['used_convert_stock'] = $row_dstock['used_convert_stock'] +  $pending_conv_stock;

						$update_id3=update_record('tbl_reserve_stock', $res_upd_st,"reserve_id =".$reserve_id, $dbcon);

						$st_qry = "SELECT * FROM tbl_stock_trn WHERE stock_id = " . $stock_id;
						$st_res = $dbcon->query($st_qry);

						$st_row = brp_mysqli_fetch_assoc($st_res);

						$st_upd_stk['used_base_stock'] = $st_row['used_base_stock'] - $pending_base_stock;
						$st_upd_stk['used_convert_stock'] = $st_row['used_convert_stock'] -  $pending_conv_stock;

						$update_id3=update_record('tbl_stock_trn', $st_upd_stk,"stock_id =".$stock_id, $dbcon);


					}
				}
			}
		}

		if($update_id){
			echo "1";	
		}
		else{
			echo "0";	
		}
	}else if(strtolower($POST['mode']) == "get_wo_po_tracking_date") {
		$rp_id = $POST['rp_id'];
	   	$mode_type = strtolower($POST['mode_type']);
	   	$purchaseordertrn_id = $POST['purchaseordertrn_id'];
	   	$product_id = $POST['product_id'];
	   	$qc = $POST['qc'];

	   	  $str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
			    <tr>
			    <th class="text-center" width="10%">Sr.No.</th>
			    <th class="text-center" width="30%">Reference No</th>
			    <th class="text-center" width="30%">Stages</th>
			    <th class="text-center" width="15%"> Qty</th>
			     <th class="text-center" width="15%"> Date</th>  
			    </tr>';
		$color = "";	    
		if($mode_type == 'indent_approve_pending'){
			$query = "select bom_trn.rp_po_qty as qty,bom_trn.rp_po_base_qty as base_qty,bom_trn.process_unit as base_unit, bom_trn.purchase_unit as conv_unit,rp_req_date as cdate, indent_no as ref_no from tbl_request_product as bom_trn 
    					where status=0 and rp_id=" . $rp_id;
    		$color = "red";
    		$text = 'Indent Approved Pending QTY';
		}else if($mode_type == 'indent_approved'){
			$query = "select approve_qty as qty,approve_base_qty as base_qty,bom_trn.approve_unit as conv_unit, bom_trn.approve_base_unit as base_unit,approve_date as cdate,approve_no as ref_no from approve_indent as bom_trn 
    					where approve_indent_status=0 and rp_id=" . $rp_id;
    		$color = "green";
    		$text = 'Indent Approved QTY';
		}else if($mode_type == 'po_pending'){
			$query = "select bom_trn.product_qty as qty, bom_trn.purchaseordertrn_id, bom_trn.cdate, bom_trn.product_base_qty as base_qty, bom_trn.base_unit_id as base_unit, bom_trn.unit_id as conv_unit,rp.indent_no as ref_no from tbl_purchasetrntemp as bom_trn 
				left join tbl_request_product as rp on rp.rp_id = bom_trn.po_ref_id
			    where bom_trn.purchaseordertrn_status=0 and bom_trn.po_ref_id=" . $rp_id;
			$color = "red";
    		$text = 'Purchase order Pending QTY';  
		}
		else if($mode_type == 'purchase_order'){
			  
			$color = "green";
    		$text = 'Purchased QTY';  

		    $query = "select bom_trn.used_qty as qty,bom_trn.cdate, bom_trn.used_base_qty as base_qty, bom_trn.base_unit, bom_trn.conv_unit, po.purchaseorder_no as ref_no from tbl_purchaseorder_req_trn as bom_trn 
		    	LEFT JOIN tbl_purchaseordertrn AS ptrn ON ptrn.purchaseordertrn_id = bom_trn.purchaseordertrn_id
		    	LEFT JOIN tbl_purchaseorder AS po ON ptrn.purchaseorder_id = po.purchaseorder_id
			    where bom_trn.purchaseordertrn_req_status=0 and bom_trn.req_id=" . $purchaseordertrn_id;
		}else if($mode_type == 'inward_pending'){
			$query = "select bom_trn.used_qty as qty,bom_trn.purchaseordertrn_id, trn.product_id, bom_trn.cdate, bom_trn.base_unit, bom_trn.conv_unit, used_base_qty as base_qty, po.purchaseorder_no as ref_no  from 
					    tbl_purchaseorder_req_trn  as bom_trn 
					    left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = bom_trn.purchaseordertrn_id
					    LEFT JOIN tbl_purchaseorder AS po ON trn.purchaseorder_id = po.purchaseorder_id
					    where trn.purchaseordertrn_status=0 and bom_trn.purchaseordertrn_req_status = 0 and bom_trn.rp_id=" . $rp_id;

    		$color = "red";
    		$text = 'Inward Pending QTY';  

		}else if($mode_type == 'grn_inward'){
			 $query = "select bom_trn.product_conv_qty as qty, bom_trn.product_qty as base_qty, bom_trn.cdate, bom_trn.product_base_unit as base_unit, bom_trn.product_conv_unit as conv_unit,grn.grn_no as ref_no from tbl_grn_sub_trn as bom_trn 
			LEFT JOIN tbl_grn_trn as trn ON trn.grn_trn_id = bom_trn.grn_trn_id
			LEFT JOIN tbl_grn as grn ON grn.grn_id = trn.grn_id
  					  where bom_trn.status=0 and bom_trn.purchaseordertrn_id in(" . $purchaseordertrn_id . ") and bom_trn.rp_id = " . $rp_id . " and bom_trn.product_id=" . $product_id;

    		$color = "green";
    		$text = 'GRN Inward QTY';  

		}else if($mode_type == 'qc_pending'){
		 $query = "select bom_trn.product_conv_qty as qty, bom_trn.product_qty as base_qty, bom_trn.cdate, bom_trn.unit_id as base_unit, bom_trn.product_conv_unit as conv_unit, grn.grn_no as ref_no from tbl_grn_trn as bom_trn 
			        left join tbl_grn as grn on grn.grn_id=bom_trn.grn_id
			        where bom_trn.grn_trn_status=0 and bom_trn.product_qc=0 and grn.ref_type=2 and po_ref_id=" . $rp_id;

    		$color = "red";
    		$text = 'QC Pending QTY';  

		}else if($mode_type == 'qc_accepted'){
			 $query = "select bom_trn.accept_qty as qty,qc.qc_no as ref_no,bom_trn.cdate,bom_trn.qc_unit as unit_id  from tbl_qc_process_trn as bom_trn 
			 LEFT JOIN tbl_qc as qc on qc.qc_id = bom_trn.qc_id
        where bom_trn.qc_process_status=0 and p_ref_id=" . $rp_id;

    		$color = "green";
    		$text = 'QC ACCEPTED QTY';  

		}else if($mode_type == 'qc_rejected'){
			 $query = "select bom_trn.reject_qty as qty,qc.qc_no as ref_no,bom_trn.cdate,bom_trn.qc_unit as unit_id from tbl_qc_process_trn as bom_trn 
			  LEFT JOIN tbl_qc as qc on qc.qc_id = bom_trn.qc_id
        where bom_trn.qc_process_status=0 and p_ref_id=" . $rp_id;

    		$color = "green";
    		$text = 'QC REJECTED QTY';  

		}else if($mode_type == 'qc_reprocess'){
			 $query = "select bom_trn.reprocess_qty as qty,qc.qc_no as ref_no,bom_trn.cdate,bom_trn.qc_unit as unit_id  from tbl_qc_process_trn as bom_trn 
			  LEFT JOIN tbl_qc as qc on qc.qc_id = bom_trn.qc_id
        where bom_trn.qc_process_status=0 and p_ref_id=" . $rp_id;

    		$color = "green";
    		$text = 'QC REPROCESS QTY';  

		}else if($mode_type == 'store_pending'){
			if($qc == '1'){
				$query = "select batch.accept_qty as qty,batch.cdate,batch.batch_unit as unit_id, grn.grn_no as ref_no from tbl_batch_data as batch 
    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0 
    left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0 
    left join tbl_grn as grn on trn.grn_id = grn.grn_id  
    where  batch.qc_status = 1 and stock_approval_status = 0 and strn.rp_id=" . $rp_id;
			}else{
				$query = "select strn.product_conv_qty as qty,batch.cdate,,batch.batch_unit as unit_id, grn.grn_no as ref_no from tbl_batch_data as batch 
    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0 
    left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0 
    left join tbl_grn as grn on trn.grn_id = grn.grn_id  
    where  batch.qc_status = 1 and stock_approval_status = 0 and strn.rp_id=" . $rp_id;
			}
			 
    		$color = "red";
    		$text = 'Store Approval Pending QTY';  

		}else if($mode_type == 'store_accept'){
			 if($qc == '1'){

				$query = "select batch.accept_qty as qty,batch.cdate,batch.batch_unit as unit_id, grn.grn_no as ref_no  from tbl_batch_data as batch 
			    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0 
			    left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0 
			    left join tbl_grn as grn on trn.grn_id = grn.grn_id  
			    where  batch.qc_status = 1 and stock_approval_status = 1 and strn.rp_id=" . $rp_id;
			}else{
				$query = "select strn.product_conv_qty as qty,batch.cdate,batch.batch_unit as unit_id, grn.grn_no as ref_no from tbl_batch_data as batch 
			    left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id and trn.grn_trn_status = 0 
			    left join tbl_grn_sub_trn as strn on trn.grn_trn_id = strn.grn_trn_id and strn.status = 0 
			    left join tbl_grn as grn on trn.grn_id = grn.grn_id  
			    where  batch.qc_status = 1 and stock_approval_status = 1 and strn.rp_id=" . $rp_id;
			}

    		$color = "green";
    		$text = 'Store Approved QTY';  

		}

		
		$result = $dbcon->query($query);

		$cnt = brp_mysqli_num_rows($result);

		$x = 1;
		if($cnt > 0){
			while($row = brp_mysqli_fetch_assoc($result)){
				$str .= '<tr style="color:'.$color.'">
 				       <td>'.$x.'</td>	
 				       <td>'.$row['ref_no'].'</td>	
 				       <td>'.$text.'</td>';

 				       if($mode_type == 'qc_accepted' || $mode_type == 'qc_rejected' || $mode_type == 'qc_reprocess' || $mode_type == 'store_pending' || $mode_type == 'store_accept'){
 				       		$str .='<td>'. $row['qty'] . '  '. getunitname($dbcon,$row['unit_id']) . '</td>';
 				       }else{
 				       		$str .='<td>' .  $row['base_qty'] . '  ' . getunitname($dbcon,$row['base_unit']) . '</br>'. $row['qty'] . '  '. getunitname($dbcon,$row['conv_unit']) . '</td>';
 				       }
        			   

        			   $str .='<td> ' .  date('d/m/Y', strtotime($row['cdate']))  . ' </td>  
        			</tr>';
        		$x++;	
			}
		}else{
			$str .= "<tr>
				<th  class='text-center' colspan='4'> No Any Data Found.</th>
			</tr>";
		}

		echo $str;

	}else if(strtolower($POST['mode']) == "get_wo_production_tracking_date") {

		$process_id = $POST['process_id'];
		$product_id = $POST['product_id'];
		$rp_id = $POST['rp_id'];
		$p_id = $POST['p_id'];
		$priority = $POST['priority'];
		$mode_type = strtolower($POST['mode_type']);
		$qc = $POST['qc'];
		$grn_trn_id = $POST['grn_trn_id'];

		$pr_q = "select product_base_unit,product_conv_unit from product_mst where product_id = " . $product_id;
   		 $pr_rw = brp_mysqli_fetch_assoc($dbcon->query($pr_q));

	    $base_unit_name = getunitname($dbcon,$pr_rw['product_base_unit']);
	    $conv_unit_name = getunitname($dbcon,$pr_rw['product_conv_unit']);

		$process_name = get_process_name($dbcon, $process_id);

		  $str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
			    <tr>
			    <th class="text-center" width="10%">Sr.No.</th>
			    <th class="text-center" width="30%">Reference No</th>
			    <th class="text-center" width="30%">Stages</th>
			    <th class="text-center" width="15%"> Qty</th>
			     <th class="text-center" width="15%"> Date</th>  
			    </tr>';
		$color = "";	    
		if($mode_type == 'batch_pending'){
			$query ="select ap.p_id as allocate_id,ap.cdate,rp.job_card_no as ref_no from tbl_allocate_process as ap
			left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
        where ap.batch_process_start_time = 1 and  ap.batch_no ='' and  ap.process_id=" . $process_id . "  and ap.company_id=" . $_SESSION['company_id'] . " and ap.p_status IN(0,1) and ap.p_product_id = " . $product_id . " and ap.p_ref_id = " . $rp_id . " and ap.process_priority=".$priority." and ap.pr_process_type= 1 and ap.p_id in(".$p_id.")";

    		$color = "red";
    		$text = 'Batch Create Pending QTY';
		}else if($mode_type == 'jobwork_pending'){
			$query = "SELECT ap.p_id as allocate_id,ap.cdate,rp.job_card_no as ref_no FROM tbl_allocate_process as ap 
			left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
			where  ap.pr_process_type='2' and ap.p_status in(0,1) and ap.company_id=1 and  ap.process_id = " . $process_id . " and ap.p_product_id = " . $product_id . " and ap.p_id(".$p_id.") and ap.p_ref_id = " . $rp_id;

			$color = "red";
    		$text = 'Jobwork Pending QTY';
		}else if($mode_type == 'jobwork_request_pending'){
			$query = "SELECT strn.product_base_qty as qty,strn.cdate as cdate,job.job_work_no as ref_no FROM tbl_job_work as job
        left join tbl_job_work_trn as trn on trn.job_work_id = job.job_work_id
        left join tbl_job_work_sub_trn as strn on strn.job_work_trn_id = trn.job_work_trn_id
        where ( 1 AND job.job_work_type = 2 and job.grn_complete_status = 0 and job.job_work_status = 0 and job.request_status = 0 and job.company_id= " . $_SESSION['company_id'] . " and p_id in (" . $p_id . ") and rp_id = " . $rp_id . ")";

			$color = "red";
    		$text = 'Jobwork Request Pending QTY';
		}
		else if($mode_type == 'store_request_pending'){

			if ($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0)
	        {
        	    $where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
   		    }
			$query =  "select ap.p_id as allocate_id,ap.cdate,rp.job_card_no as ref_no  from tbl_allocate_process as ap
			left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
        where  ap.process_id=" . $process_id . " and ap.p_product_id =" . $product_id . " and ap.process_id = " . $process_id . " and ap.p_ref_id = " . $rp_id . " and ap.company_id=" . $_SESSION['company_id'] . $where . " and ap.p_status IN(0,1) and ap.pr_process_type  = 1 and ap.p_id in(".$p_id.")";
        
        	$color = "red";
    		$text = 'Store Request Pending QTY';
		}
		else if($mode_type == 'store_release_pending'){
			$query =  "select IFNULL(base_qty,0) as qty,IFNULL(release_qty,0) as total_release_qty,ap.cdate,rp.job_card_no as ref_no from tbl_store_request as tsr
        left join tbl_allocate_process as ap on tsr.p_id=ap.p_id
        left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
        where tsr.store_request_status = 0 and ap.p_id in( " . $p_id . ") and ap.p_product_id = " . $product_id . " and ap.process_id = " . $process_id . " and tsr.company_id=" . $_SESSION['company_id'];

        $color = "red";
    		$text = 'Store Release Pending QTY';
		}else if($mode_type == 'store_release'){
			$query =  "select IFNULL(release_qty,0) as qty,ap.cdate,rp.job_card_no as ref_no from tbl_store_request as tsr
        left join tbl_allocate_process as ap on tsr.p_id=ap.p_id
        left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
        where tsr.store_request_status = 0 and ap.p_id in( " . $p_id . ") and ap.p_product_id = " . $product_id . " and ap.process_id = " . $process_id . " and tsr.company_id=" . $_SESSION['company_id'];

        	$color = "green";
    		$text = 'Store Released QTY';
		}
		else if($mode_type == 'jobwork_chalan_pending'){
			$query = "SELECT job.job_work_id,strn.product_base_qty as cdate,job.job_work_date,job.job_work_no as ref_no FROM tbl_job_work as job
            left join tbl_job_work_trn as trn on trn.job_work_id = job.job_work_id
            left join tbl_job_work_sub_trn as strn on strn.job_work_trn_id = trn.job_work_trn_id
            where ( 1 AND job.job_work_type = 2 and job.grn_complete_status = 0 and job.job_work_status = 0 and strn.request_status = 1 and strn.release_status = 1 and job.chalan_status = 0 and job.company_id= " . $_SESSION['company_id'] . " and p_id in(" . $p_id . ") and rp_id = " . $rp_id . ")";

         	$color = "red";
    		$text = 'Jobwork Chalan Pending QTY';   
		}
		else if($mode_type == 'jobwork_grn_pending'){
			$query = "select s_trn.product_base_qty as qty,job.job_work_date as cdate,l.l_name,job.job_work_no as ref_no  from tbl_job_work as job
                left join tbl_ledger as l on l.l_id=job.vender_id
                left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
                left join tbl_job_work_sub_trn as s_trn on job_trn.job_work_trn_id=s_trn.job_work_trn_id
                where job.grn_complete_status=0 and job_trn.grn_complete_status=0 and job.job_work_type=2
                and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job.company_id=1 and
                p_id in(" . $p_id . ") and rp_id = " . $rp_id;


            $color = "red";
    		$text = 'Jobwork GRN Pending QTY';
		}else if($mode_type == 'jobwork_grn'){
			$query = "select grn.product_qty as qty,job.job_work_date as cdate,l.l_name,job.job_work_no as ref_no   from tbl_job_work as job
                left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
                left join tbl_ledger as l on l.l_id=job.vender_id
                left join tbl_job_work_sub_trn as s_trn on job_trn.job_work_trn_id=s_trn.job_work_trn_id
                left join tbl_grn_sub_trn as grn on grn.job_work_sub_trn_id=s_trn.job_work_sub_trn_id
                where job.grn_complete_status=1 and job_trn.grn_complete_status=1 and job.job_work_type=2
                and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job.company_id=1 and
                s_trn.p_id in(" . $p_id . ") and grn.rp_id = " . $rp_id ;


            $color = "green";
    		$text = 'Jobwork GRN Inwarded QTY';
    	}else if($mode_type == 'pending_start'){
    		 $where = "";
                if ($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0)
                {
                    $where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
                }
                $query = "select ap.p_id as allocate_id,ap.cdate,rp.job_card_no as ref_no from tbl_allocate_process as ap
                LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id
                where ap.process_id=" . $process_id . " and ap.p_product_id = " . $product_id . " and  ap.p_ref_id = " . $rp_id . " and ap.company_id=" . $_SESSION['company_id'] . $where . " and ap.pr_process_type = 1 and ap.p_status IN(0,1) and ap.p_id in(".$p_id.")";
                $color = "red";
    		$text = 'Production Start Pending QTY';

		}else if($mode_type == 'pending_end'){
    		 $where = "";
                if ($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0)
                {
                    $where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
                }
                $query = "select ap.p_id as allocate_id,ap.cdate,rp.job_card_no as ref_no from tbl_allocate_process as ap
                LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id
                where ap.process_id=" . $process_id . " and ap.p_product_id = " . $product_id . " and  ap.p_ref_id = " . $rp_id . " and ap.company_id=" . $_SESSION['company_id'] . $where . " and ap.p_status IN(0,1) and ap.p_id in(".$p_id.")";
                $color = "red";
    			$text = 'Production End Pending QTY';
		}else if($mode_type == 'qc_pending'){
			$query = "select IFNULL(b.batch_qty,0) as qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch
			left join tbl_grn as g on b.grn_id = g.grn_id
        where batch.qc_status = 0 and reprocess_qc = 0 and batch.process_id = " . $process_id ." and grn_trn_id in(" . $grn_trn_id . ")";
        	$color = "red";
    		$text = 'QC Pending QTY';

		}else if($mode_type == 'qc_accept'){
			$query = "select IFNULL(b.accept_qty,0) as qty,batch.cdate,g.grn_no as ref_no
			left join tbl_grn as g on b.grn_id = g.grn_id
                    from tbl_batch_data as batch where batch.qc_status = 1 and batch.product_id = " . $product_id . " and  batch.process_id = " . $process_id . " and grn_trn_id in(" . $grn_trn_id . ")";
             $color = "green";
    		$text = 'QC ACCEPTED QTY';       
		}else if($mode_type == 'qc_reject'){
			$query = "select IFNULL(b.reject_qty,0) as qty,batch.cdate,g.grn_no as ref_no
			left join tbl_grn as g on b.grn_id = g.grn_id
                    from tbl_batch_data as batch where batch.qc_status = 1 and batch.product_id = " . $product_id . " and  batch.process_id = " . $process_id . " and grn_trn_id in(" . $grn_trn_id . ")";
            $color = "green";
    		$text = 'QC REJECTED QTY';        
		}else if($mode_type == 'qc_reprocess'){
			$query = "select IFNULL(b.reprocess_qty,0) as qty,batch.cdate,g.grn_no as ref_no
			left join tbl_grn as g on b.grn_id = g.grn_id
                    from tbl_batch_data as batch where batch.qc_status = 1 and batch.product_id = " . $product_id . " and  batch.process_id = " . $process_id . " and grn_trn_id in(" . $grn_trn_id . ")";
            $color = "green";
    		$text = 'QC REPROCESS QTY';  
		}else if($mode_type == 'reprocess_start_pending'){
			$query = "select (IFNULL(pen_qty,0) - IFNULL(start_qty,0)) as qty cdate,rp.job_card_no as ref_no  from 
			LEFT JOIN tbl_request_product as rp rp.rp_id = ap.p_ref_id
			tbl_allocate_re_process where process_id='$process_id' and p_product_id = " . $product_id . " and p_ref_id = " . $rp_id . " and company_id=" . $_SESSION['company_id'] . " and pr_process_type=1 and pt_alloc_id in (" . $p_id . ")";
			$color = "red";
    		$text = 'REPROCESS START PENDING QTY';
		}else if($mode_type == 'reprocess_end_pending'){
			$query = "select IFNULL(start_qty,0) as qty, ap.cdate,rp.job_card_no as ref_no  from tbl_allocate_re_process 
			LEFT JOIN tbl_request_product as rp rp.rp_id = ap.p_ref_id
			where process_id='$process_id' and p_product_id = " . $product_id . " and p_ref_id = " . $rp_id . " and company_id=" . $_SESSION['company_id'] . " and pr_process_type=1 and pt_alloc_id in (" . $p_id . ")";
			$color = "red";
    		$text = 'REPROCESS END PENDING QTY';
		}else if($mode_type == 'reprocess_qc_pending'){
			$query = "select IFNULL(batch_qty,0) as batch_qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch
			left join tbl_grn as g on batch.grn_id = g.grn_id
                    where batch.qc_status = 0 and reprocess_qc = 1 and  batch.product_id = " . $product_id . "  and batch.process_id = " . $process_id . " and grn_trn_id in(" . $grn_trn_id . ")";
                    $color = "red";
    		$text = 'REPROCESS QC PENDING QTY';
		}else if($mode_type == 'reprocess_qc_accept'){
			$query = "select IFNULL(sum(accept_qty),0) as qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch 
			left join tbl_grn as g on batch.grn_id = g.grn_id
			where batch.qc_status = 1 and reprocess_qc = 1 and  batch.product_id = " . $product_id . "  and batch.process_id = " . $process_id  . " and grn_trn_id in(" . $grn_trn_id . ")";
			$color = "green";
    		$text = 'REPROCESS QC ACCEPTED QTY';
		}else if($mode_type == 'reprocess_qc_reject'){
			$query = "select IFNULL(sum(reject_qty),0) as qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch 
			left join tbl_grn as g on batch.grn_id = g.grn_id
			where batch.qc_status = 1 and reprocess_qc = 1 and  batch.product_id = " . $product_id . "  and batch.process_id = " . $process_id  . " and grn_trn_id in(" . $grn_trn_id . ")";
			$color = "green";
			$text = 'REPROCESS QC REJECTED QTY';
		}else if($mode_type == 'reprocess_qc_reprocess'){
			$query = "select IFNULL(sum(reprocess_qty),0) as qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch 
			left join tbl_grn as g on batch.grn_id = g.grn_id
			where batch.qc_status = 1 and reprocess_qc = 1 and  batch.product_id = " . $product_id . "  and batch.process_id = " . $process_id  . " and grn_trn_id in(" . $grn_trn_id . ")";
			$text = 'REPROCESS QC REPROCESS QTY';
			$color = "green";

		}
		else if($mode_type == 'store_approval_pending'){
			$query = "select IFNULL(accept_qty,0) as qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch
			left join tbl_grn as g on batch.grn_id = g.grn_id
                    where batch.qc_status = 1 and  reprocess_qc = 0 and  stock_approval_status = 0  and batch.process_id = " . $process_id ." and grn_trn_id in(" . $grn_trn_id . ")";
             $text = 'Store Approval Pending QTY';
			$color = "red";       
		}else if($mode_type == 'store_accepted'){
			$query = "select IFNULL(accept_qty,0) as qty,batch.cdate,g.grn_no as ref_no from tbl_batch_data as batch
				left join tbl_grn as g on batch.grn_id = g.grn_id
                where batch.qc_status = 1 and stock_approval_status = 1 and batch.product_id = " . $product_id . " and batch.process_id = " . $process_id . " and grn_trn_id in(" . $grn_trn_id . ")";
            $text = 'Store Approved QTY';
			$color = "green";       
		}
// echo $query;
		$result = $dbcon->query($query);
		$cnt = brp_mysqli_num_rows($result);

		$x = 1;
		if($cnt > 0){
			while($row = brp_mysqli_fetch_assoc($result)){
				if($mode_type == 'batch_pending'){
					$qty = store_production_start_count_using_p_id($dbcon, $row['allocate_id']);
				}else if($mode_type == 'store_request_pending'){
					$qty = store_production_start_count_using_p_id($dbcon, $row['allocate_id']);
				}else if($mode_type == 'store_release_pending'){
					$qty = $row['qty'] - $row['total_release_qty'];
				}else if($mode_type == 'pending_start'){
					$qty = production_store_wise_start_count_using_p_id($dbcon, $row['allocate_id'], $is_store_approval);
				}else if($mode_type == 'pending_end'){
					$qty = production_end_count_using_p_id($dbcon, $row['allocate_id']);
				}
				else{
					$qty = $row['qty'];
				}
				$vendor = "";

				if($mode_type == 'jobwork_grn_pending' || $mode_type == 'jobwork_grn'){
					$vendor = '</br>Vendor Name : '. $row['l_name'];
				}

				$conv_qty = convert_stock($dbcon, $qty, $product_id, 'conv_unit');

				$str .= '<tr style="color:'.$color.'">
 				       <td>'.$x.'</td>	
 				       <td>'.$row['ref_no'].'</td>	
 				       <td>'.$process_name.' '.$text. '  '. $vendor .'</td>
        			   <td>' .  $qty . '  ' . $base_unit_name . '</br>'. $conv_qty . '  '. $conv_unit_name . '</td>
        			   <td> ' .  date('d/m/Y', strtotime($row['cdate']))  . ' </td>  
        			</tr>';
        		$x++;	
			}
		}else{
			$str .= "<tr>
				<th  class='text-center' colspan='4'> No Any Data Found.</th>
			</tr>";
		}

		echo $str;


	}
	else if(strtolower($POST['mode']) == "change_workorder_priority") {
		$sp_id = $POST['sp_id'];
		$priority = $POST['priority'];

		$info['priority_status'] = $priority;
		$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$sp_id, $dbcon);	
		$updateid1=update_record('tbl_request_product', $info,"sp_id=".$sp_id, $dbcon);	

		echo '1';
	}
	else if(strtolower($POST['mode']) == "track_product_detail") {
	   		$rp_id = $POST['rp_id'];
	   		$product_id = $POST['product_id'];
	   		$arr['product_name'] = get_product_name($dbcon, $product_id);

	   		$qry = "select rp.*,pro.product_name,u.unit_name as base_unit_name,cunit.unit_name as convert_unit_name, pro.product_icode from tbl_request_product as rp  
	   		left join product_mst as pro on pro.product_id = rp.rp_pid
	   		left join unit_mst as u on u.unitid=pro.product_base_unit
			left join unit_mst as cunit on cunit.unitid=pro.product_conv_unit
	   		where rp.status != 2 and rp.perent_id = " . $rp_id;
	   		$result = $dbcon->query($qry);
	   		$cnt = brp_mysqli_num_rows($result);
	   		$html = "";

	   		if($cnt > 0){
				$html .= '<table class="display table table-bordered table-striped" id="">
						<th>SR No.</th>
						<th>Product Name</th>
						<th>Request Qty</th>
						<th>Reserve Qty</th>
						<th>Pending Qty</th>
						<th>Deduct Qty</th>
						<th>Total Stock</th>
						<th>Total Reserve Stock</th>
						';	   			
	   		}
	   		while($row = brp_mysqli_fetch_assoc($result)){
	   			
	   			$req_qty = number_format($row['rp_req_qty'],5,".","");
	   			$req_conv_qty =  convert_stock($dbcon,$req_qty,$row['rp_pid'],"conv_unit");
	   			$req_conv_qty = number_format($req_conv_qty,5,".","");

	   			$p_qry = "select p_id from tbl_allocate_process where p_status !=2 and p_ref_id = " . $rp_id;
	   			$p_row= brp_mysqli_fetch_assoc($dbcon->query($p_qry));

	   			$res_qry = "select IFNULL(sum(base_stock),0) total_reserve_stock,IFNULL(sum(convert_stock),0) total_reserve_convert_stock from tbl_reserve_stock where stock_flage =1 and ref_name !='store_release' and  stock_status !=2 and request_id = " . $row['rp_id'];
	   			$res_row= brp_mysqli_fetch_assoc($dbcon->query($res_qry));

	   			$res_ded_qry = "select IFNULL(sum(base_stock),0) total_reserve_stock,IFNULL(sum(convert_stock),0) total_reserve_convert_stock from tbl_reserve_stock where stock_flage =2 and ref_name !='store_release' and stock_status !=2 and request_id = " . $row['rp_id'];
	   			$res_ded_row= brp_mysqli_fetch_assoc($dbcon->query($res_ded_qry));
	   			
	   			$reserve_stock = 0;
	   			$reserve_conv_stock = 0;
	   			$reserve_deduct_stock = 0;
	   			$reserve_deduct_conv_stock = 0;

	   			if($res_row['total_reserve_stock'] !="" && $res_row['total_reserve_stock'] > 0){
	   				$reserve_stock = number_format($res_row['total_reserve_stock'],5,".","");
	   			}
	   			if($res_row['total_reserve_convert_stock'] !="" && $res_row['total_reserve_convert_stock'] > 0){
	   				$reserve_conv_stock = number_format($res_row['total_reserve_convert_stock'],5,".","");
	   			}

	   			if($res_ded_row['total_reserve_stock'] !="" && $res_ded_row['total_reserve_stock'] > 0){
	   				$reserve_deduct_stock = number_format($res_ded_row['total_reserve_stock'],5,".","");
	   			}
	   			if($res_ded_row['total_reserve_convert_stock'] !="" && $res_ded_row['total_reserve_convert_stock'] > 0){
	   				$reserve_deduct_conv_stock = number_format($res_ded_row['total_reserve_stock'],5,".","");
	   			}

	   			$pending_qty = $req_qty - $reserve_stock;
	   			$pending_conv_qty = $req_conv_qty - $reserve_conv_stock;

	   			$current_stock = get_current_stock_new($dbcon,$row['rp_pid'],$row['process_unit']);
	   			$current_conv_stock =  convert_stock($dbcon,$current_stock,$row['rp_pid'],"conv_unit");
	   			$current_stock = number_format($current_stock,5,".","");
	   			$current_conv_stock = number_format($current_conv_stock,5,".","");

	   			$rstock=reserve_stock($dbcon,$row['rp_pid'],$row['process_unit'],'','','','','','');
	   			$rstock_conv =  convert_stock($dbcon,$rstock,$row['rp_pid'],"conv_unit");
	   			$rstock = number_format($rstock,5,".","");
	   			$rstock_conv = number_format($rstock_conv,5,".","");
	   			$cls = "";
	   			if($row['status'] == '3'){
	   				$cls = " class='text-danger'";
	   			}
	   			$html .="<tr".$cls.">
	   				<td>".$row['sr_no']."</td>
	   				<td>".$row['product_name'] . ' -- ('.$row['product_icode'].')' ."</td>
	   				<td>".$req_qty ." - " . $row['base_unit_name'] ."</br>" . $req_conv_qty ." - ". $row['convert_unit_name'] ."</td>
	   				<td>".$reserve_stock ." - " . $row['base_unit_name'] ."</br>" . $reserve_conv_stock ." - ". $row['convert_unit_name'] ."</td>
	   				<td>".$pending_qty ." - " . $row['base_unit_name'] ."</br>" . $pending_conv_qty ." - ". $row['convert_unit_name'] ."</td>
	   				<td>".$reserve_deduct_stock ." - " . $row['base_unit_name'] ."</br>" . $reserve_deduct_conv_stock ." - ". $row['convert_unit_name'] ."</td>
	   				<td>".$current_stock ." - " . $row['base_unit_name'] ."</br>" . $current_conv_stock ." - ". $row['convert_unit_name'] ."</td>
	   				<td>".$rstock ." - " . $row['base_unit_name'] ."</br>" . $rstock_conv ." - ". $row['convert_unit_name'] ."</td>
	   			</tr>";
	   		}

	   		$arr['data'] = $html;
	   		echo json_encode($arr);
	   }
    }
}


function check_workorder_stag_status($dbcon,$sp_id){
	$status = "";

	$qry = "SELECT finish_status,workorder_short_close FROM tbl_set_main_process WHERE sp_id = " .$sp_id;
	$result = $dbcon->query($qry);
	$row = brp_mysqli_fetch_assoc($result);

	if($row['workorder_short_close'] == '1'){
		$status = "Workorder Shortclose";
	}else if($row['finish_status'] == '1'){
		$status = "Workorder Completed";
	}else{
		$qry1 = "SELECT count(rp_id) as pending_request FROM tbl_request_product WHERE status = 3 AND sp_id = " .$sp_id;
		$result1 = $dbcon->query($qry1);
		$row1 = brp_mysqli_fetch_assoc($result1);		

		if($row1['pending_request'] > 0){
			$status = "Partially Planning";
		}else{
			$status = "Planning Complete";
		}
	}

	return $status;
}

function check_having_child_product($dbcon,$rp_id){

	$qry = "select count(rp_id) as child from tbl_request_product where status != 2 and perent_id = " . $rp_id;
	$result=$dbcon->query($qry);
	$res=brp_mysqli_fetch_assoc($result);

	if($res['child'] > 0){
		return 1;
	}else{
		return 0;
	}
}

?>