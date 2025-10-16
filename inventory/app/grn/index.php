<?php
session_start();
$AJAX = true;
	include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INVENTORY_GRN_LIST_SLUG_VIEW,INVENTORY_GRN_LIST_SLUG_CREATE,INVENTORY_GRN_LIST_SLUG_UPDATE,INVENTORY_GRN_LIST_SLUG_DELETE
]);
// error_reporting(E_ALL);
/*echo "<pre>";
print_r($bulkAccessArray);*/

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

$companyConfiguration=getCompanyConfiguration($dbcon);

if(brp_strtolower($POST['mode']) == "fetch") {
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/


		$company_setting_query="select * from tbl_company_configuration";
		//var_dump($query);
		$company_setting_result=$dbcon->query($company_setting_query);
		$company_setting_row=brp_mysqli_fetch_assoc($company_setting_result);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=' '; 
		$where.=" and grn.grn_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND grn.grn_date<='".date('Y-m-d',strtotime($s_date[1]))."'";

		if($POST['grn_against'] != ""){
			$where.=" and grn.ref_type=".$POST['grn_against'];	
		}
		
		
		if($POST['grn_against']=="1"){
			$isJOIN_new = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_job_work as job on job.job_work_id=gstrn.jobwork_id');
			$aColumns_new=array('job.chalan_no as pono');
		}else if($POST['grn_against']=="5"){
			
			$isJOIN_new = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_sales_ordertrn as ptrn on ptrn.sales_ordertrn_id=gtrn.purchaseordertrn_id','left join tbl_sales_order as po on po.sales_order_id=ptrn.sales_order_id');
			$aColumns_new=array('group_concat(DISTINCT po.sales_order_no) as pono');
		}
		
		else{
			$isJOIN_new = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=gtrn.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id');
			$aColumns_new=array('group_concat(DISTINCT po.purchaseorder_no) as pono');
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('grn.grn_id','grn.grn_no','grn.invoice_no', 'grn.grn_date','grn.ref_type', 'cust.l_name', 'grn.grn_status','grn.cdate','grn.user_id','grn.purchaseorder_id');
		$aColumns=array_merge($aColumns,$aColumns_new);
		$sIndexColumn = "grn.grn_id";
		$isWhere = array("grn.grn_status = 0 and grn.company_id = ".$_SESSION['company_id'].$where);
		$sTable = "tbl_grn as grn";
		//$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vender_id','left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=gtrn.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id');
		$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vender_id');
		$isJOIN=array_merge($isJOIN,$isJOIN_new);
		$hOrder = "grn.grn_id desc";
		$hGroupby = array("grn.grn_id");
		$having_clause='';
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			$po_no = $row["pono"];

			if($row['ref_type']=='1'){ 
				$ref_type="OUTSIDE JOBWORK"; 
				$jqry = "SELECT job.chalan_no as pono FROM tbl_grn as grn
						 left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id
						 left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id
						 left join tbl_job_work as job on job.job_work_id=gstrn.jobwork_id where  grn.grn_status = 0 and grn.ref_type=1 and grn.grn_id = ".$row['grn_id'].$where;
				$jrw = brp_mysqli_fetch_assoc($dbcon->query($jqry));
				$po_no = $jrw['pono'];		 

			}else if($row['ref_type']=='4'){ 
				$ref_type="Direct";
			}else if($row['ref_type']=='5'){ 
				$ref_type="Ourside So";
			}else if($row['ref_type']=='3'){ 
			    $ref_type="Inhouse Production";
			}else if($row['ref_type']=='2') {  
				$ref_type="Purchase Order"; 
			}else if($row['ref_type']=='6') {  
				$ref_type="Returnable Chalan"; 
			}else if($row['ref_type']=='7') {  
				$ref_type="Stock Transfer"; 
			}else if($row['ref_type']=='8') {
				$ref_type="Reprocess"; 
			}else {
				$ref_type=""; 
			}
			
			$row_data = array();
			/*$que_po12="select (select count(grn_trn_id) from tbl_grn_trn where product_qc=0 and store_accept = 0 and grn_id=".$row['grn_id'].") as edit_count, (select count(grn_trn_id) from tbl_grn_trn where product_qc=1 OR store_accept = 1 and grn_id=".$row['grn_id'].") as delete_count, grn_id from tbl_grn_trn where grn_id = ".$row['grn_id'];*/

			$editable = 0;
			$deletable = 0;

			/*$que_po12="select grn.*,bt.stock_approval_status,bt.qc_status from tbl_grn_trn as grn left join tbl_batch_data as bt on bt.grn_trn_id = bt.grn_trn_id where grn.grn_trn_status = 0 and grn.grn_id = ".$row['grn_id'];*/

			$que_po12="select bt.stock_approval_status,bt.qc_status,bt.product_id,bt.process_id from tbl_batch_data as bt  where bt.status = 0 and bt.grn_id = ".$row['grn_id'];

			$resi_grn12=$dbcon->query($que_po12);
			$bt_cnt = brp_mysqli_num_rows($resi_grn12);
			while($re12 = brp_mysqli_fetch_assoc($resi_grn12)){
				$process_id = "";

				if($re12['process_id'] != "" || $re12['process_id'] != 0){

					$process_id = 	$re12['process_id'];
				}


				$qc_paramter_info = check_product_qc_paramter($dbcon,$re12['product_id'],$process_id);
				// var_dump($qc_paramter_info);
				if($qc_paramter_info=='1' && $re12['qc_status'] == '1')
				{
					$editable++;
					
				}else if($qc_paramter_info=='0' && $re12['qc_status'] == '1' && $re12['stock_approval_status'] == "1"){
					$editable++;
				}

				if($re12['stock_approval_status'] == "1")
				{
					$deletable++;
				}				
			}
			
			
			if(in_array(INVENTORY_GRN_LIST_SLUG_UPDATE,$bulkAccessArray)){
				if(!empty($row['grn_id'])){
					// $href = ROOT.INVENTORY_ROOT.'grn_edit/'.$row['grn_id'];
					$href = "#";
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.$href.'">'.$row["grn_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.$href.'">'.$row["invoice_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.$href.'">'.date('d M, Y',strtotime($row["grn_date"])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.$href.'">'.$po_no.'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.$href.'">'.$ref_type.'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.$href.'">'.$row["l_name"].'</a>';
				}else{
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["grn_no"].'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["invoice_no"].'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$po_no.'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$ref_type.'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["l_name"].'</a> ';
				}
			}else{
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["grn_no"].'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["invoice_no"].'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$po_no.'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$ref_type.'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["l_name"].'</a> ';
			}
			

			$edit_btn=''; $delete_btn=''; $view='';

			if($row['ref_type'] == '2'){
				if(in_array(INVENTORY_GRN_LIST_SLUG_UPDATE,$bulkAccessArray)){
					// if(!empty($re12['grn_id'])){
					if($editable != $bt_cnt){
						 $edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_edit/'.$row['grn_id'].'"><i class="fa fa-pencil"></i></a>'; 
					}
				}
				if(in_array(INVENTORY_GRN_LIST_SLUG_DELETE,$bulkAccessArray)){
					// if(!empty($re12['grn_id'])){
					if($deletable == 0){
						$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn('.$row['grn_id'].','.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>'; 
					}
				}
			}
			if(in_array(INVENTORY_GRN_LIST_SLUG_VIEW,$bulkAccessArray)){
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
			$rels=brp_mysqli_fetch_assoc($menusql);
			$menu_show_permissions = explode(",",$rels['print_permission']);
			$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 5 AND approve_status = 1 AND status = 0 ORDER BY priority");
			while($res = brp_mysqli_fetch_assoc($sql)){
				if(in_array($res['id'],$menu_show_permissions)) {
					$view.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['grn_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
				}
			}
				//$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_view/'.$row['grn_id'].'"><i class="fa fa-eye"></i></a> ';
			}  
			
			
			if($company_setting_row['grn_sticker_print'] == '1')
			{
				$stricker_print='<a class="btn btn-xs btn-info" data-original-title="Sticker Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_sticker_print/'.$row['grn_id'].'"><i class="fa fa-print"></i></a> ';
				$stricker_print_new='<a class="btn btn-xs btn-info" data-original-title="Sticker Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'grn_sticker_common_print/'.$row['grn_id'].'"><i class="fa fa-barcode"></i></a> ';
			}
			else
			{
				$stricker_print='';
				$stricker_print_new="";
			}
			
			
			$view_attach_doc = '<button class="btn btn-xs btn-info" data-original-title="View Attached Document" data-toggle="tooltip" data-placement="top" onClick="view_attach_document('.$row['grn_id'].',\''.$row['grn_no'].'\')"><i class="fa fa-eye"></i></button>';
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view.''.$stricker_print.' '.$view_attach_doc.' '.$stricker_print_new;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(brp_strtolower($POST['mode']) == "add") 
	{
		// echo "<pre>";
		// print_r($POST);die;

		 $grn_against = $POST['grn_against'];
		//  $job_work_po_trn_id = $POST['job_work_po_trn_id'];
		 $vender_id	= $POST['vender_id'];
		//old purchase code start
		 if($grn_against=='1'){
			$branch_id = $POST['branch_id'];

			$info_grn['grn_no']				= $POST['grn_no'];
			$info_grn['grn_date']			=  date('Y-m-d',strtotime($POST['grn_date']));
			$info_grn['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info_grn['invoice_no']			= $POST['invoice_no'];
			$info_grn['challan_no']			= $POST['challan_no'];
			$info_grn['ref_type']			= $POST['grn_against'];
			$info_grn['vender_id']			= $POST['vender_id'];
			$info_grn['remark']				= $POST['remark'];
			$info_grn['is_conversation']	= $POST['is_conversation'];
			$info_grn['vehicle_no']			= $POST['vehicle_no'];
			$info_grn['mode_dispatch']		= $POST['mode_dispatch'];
			$info_grn['cdate']				= date("Y-m-d H:i:s");
			$info_grn['user_id']			= $_SESSION['user_id'];
			$info_grn['company_id']			= $_SESSION['company_id'];
			
			//echo "<pre>"; primt_r($info_grn);die;

			$grn_id=add_record('tbl_grn',$info_grn, $dbcon,$branch_id);

			if($grn_id){
				update_series_no_using_type_id($dbcon,OUTSIDE_GRN,$_SESSION['company_id'],$branch_id1);

				$grn_qty_st=$POST['grn_qty'];
				for($m=0;$m<count($grn_qty_st);$m++)
				{
					if($POST['grn_qty'][$m]!=0 && $POST['grn_qty'][$m]!="")
					{
						$product_id			= $POST['product_id'][$m];
						$stop_qty			= $POST['grn_qty'][$m];
						$product_base_unit	= $POST['product_base_unit'][$m];
						$stop_conv_qty			= $POST['conv_grn_qty'][$m];
						$product_conv_unit	= $POST['product_conv_unit'][$m];
						
						$process_id			= $POST['process_id'][$m];
						$grn_godown			= $POST['grn_godown'][$m];
						$p_id				= $POST['p_id'][$m];
						$rate_unit			= $POST['grn_rate_unit'][$m];
						$is_reprocess			= $POST['is_reprocess'][$m];

						$pid_array = array();
						$end_qty_array =array();
						
						grn_trn_and_sub_trn_entry($dbcon,$product_id,$grn_id,$stop_qty,$product_base_unit,$process_id,$grn_godown,$p_id,$branch_id,$pid_array,$end_qty_array, $job_work_po_trn_id,$stop_conv_qty,$product_conv_unit,$POST['grn_no'],"","","",$POST['grn_against'],$rate_unit,"","","","","","","","","","","","","","","","",$vender_id);
					}
				}
			}	
		}

		if($grn_against=='2')
		{
			// echo "<pre>"; print_r($info);die;
			$customer_id =  $POST['vender_id'];
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='8' and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['purchaseorder_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];

			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];
			$info['is_conversation']		= $POST['is_conversation'];

			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['branch_id']			= $branch_id;
			//echo "<pre>"; print_r($info);die;
			
			$grn_id=add_record('tbl_grn', $info, $dbcon);
			
			if($grn_id){
				$grn_qty=$POST['grn_qty'];
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){

							if($info['ref_type']==2){
								if(brp_strtolower($POST['qc_type'][$k])=="no"){
									$godown_id=$POST['grn_godown'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								$wwe=get_pro_field($dbcon,$POST['grn_pid'][$k],'minimum_tolerance');
						//$info2['purchaseorder_id']	=$POST['purchaseorder_id'];
								$info2s['purchaseordertrn_id']	=$POST['purchaseordertrn_id'][$k];
								$info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['grn_id']				=$grn_id;
								$info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								$info2s['unit_id']				=$POST['unit_id'][$k];
								$info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								$info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								$info2s['grn_godown']			=$godown_id;
								$info2s['product_qc']			=$product_qc;
								$info2s['tolerance']			=$wwe;
								$info2s['po_ref_id']			=$POST['po_ref_id'][$k];
					//	var_dump($info2['po_ref_id']);
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
								$info2s['ref_type']			= $POST['grn_against'];
								$info2s['rate_unit']		= $POST['grn_rate_unit'][$k];
								
						//var_dump($info2);
								$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon, $branch_id);

								$ptrn=intval($info2s['purchaseordertrn_id']);
								$hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);



						//var_dump($hhhh);
								if($godown_id!=""){
									
								
									
									/* jayesh for setting store approve or not 
									$company_data = getCompanyConfiguration($dbcon, $id = false);
									//echo "<pre>"; print_r($company_data);
									if($company_data['store_approval'] == '0')
									{
										$stock_id=add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id,$customer_id);
									}
									else
									{	
										$store_receive_id = add_store_receive($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id,$customer_id);
									} */
									
									
									//echo "helllo"; die;
									/* jayesh for setting store approve or not */

									if (!empty($info2s['po_ref_id'])) {
										$query_res = "SELECT * FROM tbl_request_product AS req WHERE rp_id IN (" . $info2s['po_ref_id'] . ")";
										$result_res = $dbcon->query($query_res);
									} else {
										$result_res = [];
									}

									$resqty1=$POST['grn_qty'][$k];
									while($row_res=brp_mysqli_fetch_assoc($result_res)){

										$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
										$result_ind=$dbcon->query($query_ind);
										$row_ind=brp_mysqli_fetch_assoc($result_ind);
								//echo $query_ind;
										$reserve_id="";
										$request_id=$row_res['rp_id'];
										$complaint_id="";
										$sales_order_trn_id="";
										
										$used_rese=total_reserve_stock($dbcon,$row_res['rp_pid'],$info2s['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
								//var_dump($used_rese);
										$res_pending=$row_ind['app_qty']-$used_rese;

								//var_dump($res_pending);
								//var_dump($resqty1);
									//add_request_reserve_stock($dbcon,$info2s['po_ref_id'],$info2s['product_qty'],$info2s['unit_id']);
										if($resqty1>=$res_pending){
									//add_request_reserve_stock($dbcon,$request_id,$res_pending,$info2s['unit_id'],$branch_id);

											/*grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id,$branch_id);
*/
											$resqty1=$resqty1-$res_pending;
										}else{
									//add_request_reserve_stock($dbcon,$request_id,$resqty,$info2s['unit_id'],$branch_id);

											/*grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id,$branch_id);*/

											$resqty1=$resqty1-$resqty1;
										}
								//var_dump($resqty1);
									}
								}
								$product_id = $POST['grn_pid_tmp'][$k];
					$rate_unit = $POST['grn_rate_unit'][$k];

					$grn_base_qty = $POST['grn_qty_hide'][$k];
					$grn_base_unit = $POST['unit_id'][$k];

					$grn_conv_qty = $POST['conv_grn_qty_hide'][$k];
					$grn_conv_unit = $POST['conv_unit_id'][$k];

								$product_id = $POST['grn_pid'][$k];
								//var_dump("ww");	
					upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc,"","","",$ptrn); // for update batch no tempory status and add grn_id for multiple batch  
					

				 $qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id . " and  purchaseordertrn_id = " . $ptrn;

					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					//var_dump($remaining_qty);
					$batch_no = "";

					$get_dt_qry="select * from product_mst where product_id =".$product_id;
					$getproduct_res=$dbcon->query($get_dt_qry);
					$getproduct_row=mysqli_fetch_assoc($getproduct_res);

					if($companyConfiguration['batch_wise_stock'] == '1' && $getproduct_row['batch_wise_stock_manage'] == '1') {
						$batch_no = get_batch_no($dbcon,$product_id);
					}
					/*echo "rate unit :" . $rate_unit . "</br>";
					echo "grn_base_qty :" . $grn_base_qty . "</br>";
					echo "grn_base_unit :" . $grn_base_unit . "</br>";
					echo "grn_conv_qty :" . $grn_conv_qty . "</br>";
					echo "grn_conv_unit  :" . $grn_conv_unit  . "</br>";
					echo "remaining_qty  :" . $remaining_qty  . "</br>";*/

					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}

	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					/*echo "batch_qty  :" . $batch_qty  . "</br>";
					echo "batch_conv_qty  :" . $batch_conv_qty  . "</br>";*/

					$mfg_date = date("Y-m-d");
					$exp_date = get_exp_date_by_product($dbcon, $product_id,date("d-m-Y"));
					$batch_info['mfg_date']			= $mfg_date;
					$batch_info['exp_date']			= $exp_date;
						
					$batch_info['grn_id']			= $grn_id;	
					$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					
					$batch_info['order_no']			= $_POST['grn_no'];
					$batch_info['product_id']		= $product_id;
					$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';			
					$batch_info['purchaseordertrn_id']	= $ptrn;			
					
					$batch_info['qc_status']		= $product_qc;
					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']		= $remaining_qty;
					}
					$batch_info['grn_accept_qty']	= $remaining_qty;
					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $POST['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']			= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_qty']			= $batch_conv_qty;
					$batch_info['conv_unit']		= $grn_conv_unit;
					$batch_info['grn_godown']		= $godown_id;


					if($remaining_qty >  0){

							$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
							
								if($batch_gen_id){
									if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
										update_batch_no($dbcon,$product_id);

									}
								}						
							}	
							
							update_po_grn_status($dbcon,$POST['purchaseorder_id'],$ptrn);
						}
					}
				}					
			}


			$grn_qty_tmp=explode(",",$POST['grn_qty_tmp']);
			// $jid=explode(",",$POST['j_job_work_id'][$m]);
			// $jid=explode(",",$POST['grn_qty_tmp']);

				for($k=0;$k<count($grn_qty_tmp);$k++)
				{
					$loop_id=$grn_qty_tmp[$k];
					if($POST['grn_qty_tmp'][$k]!=0){
						if($POST['grn_qty_tmp'][$k]!=""){

							if($info['ref_type']==2){

								$tbl_grn_trn_id= $POST['grn_trn_id_tmp'][$k];
								$product_id = $POST['grn_pid_tmp'][$k];
								// $purchaseordertrn_id = $POST['purchaseordertrn_id_tmp'][$k];

								$upd_grn['grn_id'] = $grn_id;
								$upd_grn['grn_trn_status'] = 0;
								update_record('tbl_grn_trn', $upd_grn,"grn_trn_id=".$tbl_grn_trn_id." and product_id=".$product_id, $dbcon);

								// $hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$purchaseordertrn_id);

								$upd_po_info['status'] = 0;
								$upd_po_info['grn_status'] = 1;
								$upd_po_info['grn_id'] =  $grn_id;
								update_record('tbl_po_item_agains_grn', $upd_po_info,"purchaseorder_id=".$POST['purchaseorder_id']." and grn_trn_id=".$tbl_grn_trn_id, $dbcon);


								update_po_agains_grn_qty($dbcon,$POST['purchaseorder_id'],$tbl_grn_trn_id);

							/*echo	$upd_qry = "update tbl_grn_trn set grn_id = ".$grn_id." grn_trn_status=0 where grn_trn_id=".$tbl_grn_trn_id." and product_id = " .$product_id;
								$dbcon->query($upd_qry);
								die;*/
								if(brp_strtolower($POST['qc_type_tmp'][$k])=="no"){
									$godown_id=$POST['grn_godown_tmp'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								
								

								$ptrn=$info2s['purchaseordertrn_id'];
								// $hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);
						

							$rate_unit = $POST['grn_rate_unit_tmp'][$k];

							$grn_base_qty = $POST['grn_qty_tmp_hide'][$k];
							$grn_base_unit = $POST['unit_id_tmp'][$k];

							$grn_conv_qty = $POST['conv_grn_qty_tmp_hide'][$k];
							$grn_conv_unit = $POST['conv_unit_id_tmp'][$k];

								
					upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc); // for update batch no tempory status and add grn_id for multiple batch  
						
				$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id;
					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					$batch_no = "";
					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$product_id);

					}
					/*echo "rate unit :" . $rate_unit . "</br>";
					echo "grn_base_qty :" . $grn_base_qty . "</br>";
					echo "grn_base_unit :" . $grn_base_unit . "</br>";
					echo "grn_conv_qty :" . $grn_conv_qty . "</br>";
					echo "grn_conv_unit  :" . $grn_conv_unit  . "</br>";
					echo "remaining_qty  :" . $remaining_qty  . "</br>";*/

					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}

	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					/*echo "batch_qty  :" . $batch_qty  . "</br>";
					echo "batch_conv_qty  :" . $batch_conv_qty  . "</br>";*/
					$mfg_date = date("Y-m-d");
					$exp_date = get_exp_date_by_product($dbcon, $product_id,date("d-m-Y"));
					$batch_info['mfg_date']			= $mfg_date;
					$batch_info['exp_date']			= $exp_date;
					$batch_info['purchaseordertrn_id']	= $ptrn;	
					$batch_info['grn_id']			= $grn_id;	
					$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					$batch_info['order_no']			= $_POST['grn_no'];
					$batch_info['product_id']		= $product_id;
					$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';			
					
					$batch_info['qc_status']		= $product_qc;
					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']		= $remaining_qty;
					}
					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $POST['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']			= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_qty']			= $batch_conv_qty;
					$batch_info['conv_unit']		= $grn_conv_unit;
					$batch_info['grn_godown']		= $godown_id;

					

					if($remaining_qty >  0){
							$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
							// var_dump($batch_gen_id);die;
								if($batch_gen_id){
									if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
										update_batch_no($dbcon,$product_id);

									}
								}						
							}
							
							update_po_grn_status($dbcon,$POST['purchaseorder_id'],$ptrn);	
						}
					}
				}					
			}

			update_po_grn_status($dbcon,$POST['purchaseorder_id'],$info2s['purchaseordertrn_id']);

			//$updatetrnid=update_record('tbl_grn_trn', $infotrn,"grn_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			/*Update Data in Trn Table End*/

			/*Hide by Sanat :: 19-11-2021 
				comment :: in tbl_purchaseorder have no any used_grn_status  field so no need this function 
			*/
			// $UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);
		}

		//old purchase code end
		//service grn code 
		if($grn_against=='3'){
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			update_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);
			
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['purchaseorder_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];

			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];
			$info['is_conversation']	= $POST['is_conversation'];

			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$grn_id=add_record('tbl_grn', $info, $dbcon, $branch_id);
			
			if($grn_id){

				$grn_qty=$POST['grn_qty'];
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];

					if($POST['grn_qty'][$k]!=0){

						if($POST['grn_qty'][$k]!=""){

							if($info['ref_type']==3){
								if(brp_strtolower($POST['qc_type'][$k])=="no"){
									$godown_id=$POST['grn_godown'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								$wwe=get_pro_field($dbcon,$POST['grn_pid'][$k],'minimum_tolerance');
						//$info2['purchaseorder_id']	=$POST['purchaseorder_id'];
								$info2s['purchaseordertrn_id']	=$POST['purchaseordertrn_id'][$k];
								$info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['grn_id']				=$grn_id;
								$info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								$info2s['unit_id']				=$POST['unit_id'][$k];
								$info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								$info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								$info2s['grn_godown']			=$godown_id;
								$info2s['product_qc']			=$product_qc;
								$info2s['tolerance']			=$wwe;
								$info2s['po_ref_id']			=$POST['po_ref_id'][$k];
					//	var_dump($info2['po_ref_id']);
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
								$info2s['ref_type']			= $POST['grn_against'];
								$info2s['rate_unit']		= $POST['grn_rate_unit'][$k];
						//var_dump($info2);

								$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon, $branch_id);
								
								$ptrn=$info2s['purchaseordertrn_id'];

								$hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);
						//var_dump($hhhh);
								/*if($godown_id!=""){
									$stock_id=add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id);

									$query_res="select * from tbl_request_product as req where rp_id in (".$info2s['po_ref_id'].")";
									$result_res=$dbcon->query($query_res);
									$resqty1=$POST['grn_qty'][$k];
									while($row_res=brp_mysqli_fetch_assoc($result_res)){

										$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
										$result_ind=$dbcon->query($query_ind);
										$row_ind=brp_mysqli_fetch_assoc($result_ind);
								//echo $query_ind;
										$reserve_id="";
										$request_id=$row_res['rp_id'];
										$complaint_id="";
										$sales_order_trn_id="";

										$used_rese=total_reserve_stock($dbcon,$row_res['rp_pid'],$info2s['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
								//var_dump($used_rese);
										$res_pending=$row_ind['app_qty']-$used_rese;

								//var_dump($res_pending);
								//var_dump($resqty1);
									//add_request_reserve_stock($dbcon,$info2s['po_ref_id'],$info2s['product_qty'],$info2s['unit_id']);
										if($resqty1>=$res_pending){
									//add_request_reserve_stock($dbcon,$request_id,$res_pending,$info2s['unit_id'],$branch_id);

											grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id);

											$resqty1=$resqty1-$res_pending;
										}else{
									//add_request_reserve_stock($dbcon,$request_id,$resqty,$info2s['unit_id'],$branch_id);

											grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id);

											$resqty1=$resqty1-$resqty1;
										}
								//var_dump($resqty1);
									}
								}*/
							}
						}
					}
				}		
			//$updatetrnid=update_record('tbl_grn_trn', $infotrn,"grn_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			/*Update Data in Trn Table End*/
			$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);
		}
		//service grn code end
		//job work to grn new code start 
		
		//job work to grn new code end
		
		/* jayesh outside sales order */
		
		if($grn_against=='5')
		{
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			update_series_no_using_type_id($dbcon,OUT_SO_GRN,$_SESSION['company_id'],$branch_id1);
			
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['purchaseorder_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];

			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];
			$info['is_conversation']	= $POST['is_conversation'];

			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			//echo "<pre>"; print_r($info);die;
			$grn_id=add_record('tbl_grn', $info, $dbcon, $branch_id);
			//echo $grn_id; die;
			//echo "test"; die;
			
			if($grn_id){
				$grn_qty=$POST['grn_qty'];
				//echo "test".$grn_qtya;die;
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){
								if(brp_strtolower($POST['qc_type'][$k])=="no"){
									$godown_id=$POST['grn_godown'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								$wwe=get_pro_field($dbcon,$POST['grn_pid'][$k],'minimum_tolerance');
						//$info2['purchaseorder_id']	=$POST['purchaseorder_id'];
								// $info2s['purchaseordertrn_id']	=$POST['purchaseordertrn_id'][$k];
								// $info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['grn_id']				=$grn_id;
								// $info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								// $info2s['unit_id']				=$POST['unit_id'][$k];
								// $info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								// $info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								$info2s['grn_godown']			=$godown_id;
								$info2s['product_qc']			=$product_qc;
								$info2s['tolerance']			=$wwe;
								// $info2s['po_ref_id']			=$POST['po_ref_id'][$k];
					//	var_dump($info2['po_ref_id']);
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
								$info2s['grn_trn_status']		= 0;
						//var_dump($info2);
								
								// $tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon, $branch_id);

								$tbl_grn_trn_id = $POST['grn_trn_id'][$k];

								update_record('tbl_grn_trn', $info2s,"grn_trn_id = " . $tbl_grn_trn_id, $dbcon);

								// $ptrn=$info2s['purchaseordertrn_id'];
								// $hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);
						//var_dump($hhhh);
						//echo "test"; die;
						//echo $godown_id; die;
								 $customer_id = $POST['vender_id'];


					$rate_unit = $POST['grn_rate_unit'][$k];
					$grn_base_qty = $POST['grn_qty_hide'][$k];
					$grn_base_unit = $POST['unit_id'][$k];

					$grn_conv_qty = $POST['conv_grn_qty_hide'][$k];
					$grn_conv_unit = $POST['conv_unit_id'][$k];

					$product_id = $POST['grn_pid'][$k];
					upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc,$customer_id); // for update batch no tempory status and add grn_id for multiple batch  
						
				$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id;
					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];

					$remaining_qty = 0;
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					$batch_no = "";
					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$product_id);

					}
					
					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}
	   				// echo "===>".$remaining_qty;
		   				$batch_qty=$base_qty;
						$batch_conv_qty=$conv_qty;	

						$mfg_date = date("Y-m-d");
						$exp_date = get_exp_date_by_product($dbcon, $product_id,date("d-m-Y"));
						$batch_info['mfg_date']			= $mfg_date;
						$batch_info['exp_date']			= $exp_date;				
		
						$batch_info['grn_id']			= $grn_id;	
						$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
						$batch_info['batch_no']			= $batch_no;
						$batch_info['batch_qty']		= $remaining_qty;
						$batch_info['order_no']			= $_POST['grn_no'];
						$batch_info['product_id']		= $product_id;
						$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
						$batch_info['batch_type']		= $companyConfiguration['batch_type'];
						$batch_info['production_type']	= '1';			
						$batch_info['status']			= '0';			
						
						$batch_info['qc_status']			= $product_qc;
						if($product_qc==1){
							$batch_info['accept_qty']	= $remaining_qty;
							$batch_info['qc_qty']	= $remaining_qty;
						}else{
							$batch_info['accept_qty']	= "";
							$batch_info['qc_qty']	= "";
						}
						$batch_info['cdate']			= date("Y-m-d H:i:s"); 
						$batch_info['user_id']			= $_SESSION['user_id'];
						$batch_info['company_id']		= $_SESSION['company_id'];	
						$batch_info['branch_id']		= $branch_id;
						$batch_info['batch_unit']		= $rate_unit;
						$batch_info['base_qty']			= $batch_qty;
						$batch_info['base_unit']		= $grn_base_unit;
						$batch_info['conv_qty']			= $batch_conv_qty;
						$batch_info['conv_unit']		= $grn_conv_unit;
						$batch_info['customer_id']		= $customer_id;
						// $batch_info['grn_godown']		= $godown_id;

						$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
							if($batch_gen_id){
								if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
									update_batch_no($dbcon,$product_id);

								}
							}


							/* jayesh for setting store approve or not */
							$company_data = getCompanyConfiguration($dbcon, $id = false);
							if($company_data['store_approval'] == '0')
							{
								$stock_id=add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id,"","",$customer_id,$batch_id,$batch_no);

							}
							else
							{	
								$store_receive_id = add_store_receive($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id,"","",$customer_id,"","");

							}
						}
					}
				}		
			//$updatetrnid=update_record('tbl_grn_trn', $infotrn,"grn_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			/*Update Data in Trn Table End*/
			$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);
		}

		if($grn_against=='6')
		{
			$customer_id =  $POST['vender_id'];
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			update_series_no_using_type_id($dbcon,RET_CHN_GRN,$_SESSION['company_id'],$branch_id1);
			
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['returnable_id']		= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];

			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];
			$info['is_conversation']		= $POST['is_conversation'];
			$info['receive_datetime']		= date('Y-m-d H:i:s',strtotime($POST['receive_datetime']));
			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			//echo "<pre>"; print_r($info);die;
			
			$grn_id=add_record('tbl_grn', $info, $dbcon, $branch_id);
			
			if($grn_id){
				$grn_qty=$POST['grn_qty'];
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){

							if($info['ref_type']==6){
								if(brp_strtolower($POST['qc_type'][$k])=="no"){
									$godown_id=$POST['grn_godown'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								$wwe=get_pro_field($dbcon,$POST['grn_pid'][$k],'minimum_tolerance');
								$info2s['returnable_id']		= $POST['purchaseorder_id'];
								$info2s['returnable_trn_id']	=$POST['purchaseordertrn_id'][$k];
								$info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['grn_id']				=$grn_id;
								$info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								$info2s['unit_id']				=$POST['unit_id'][$k];
								$info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								$info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								$info2s['grn_godown']			=$godown_id;
								$info2s['product_qc']			=$product_qc;
								$info2s['tolerance']			=$wwe;
								$info2s['po_ref_id']			=$POST['po_ref_id'][$k];
					
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
								$info2s['ref_type']			= $POST['grn_against'];
								$info2s['rate_unit']		= $POST['grn_rate_unit'][$k];
								
						
								$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon, $branch_id);

								$ptrn=$info2s['returnable_trn_id'];
								


				   				$info2['product_id']			= $info2s['product_id'];
				   				$info2['grn_trn_id']			= $tbl_grn_trn_id;
				   				$info2['returnable_trn_id']		= $info2s['returnable_trn_id'];
				   				$info2['product_qty']			= $info2s['product_qty'];
				   				$info2['product_base_unit']		= $info2s['unit_id'];
				   				$info2['product_conv_qty']		= $info2s['product_conv_qty'];
				   				$info2['product_conv_unit']		= $info2s['product_conv_unit'];
				   				
				   				$info2['cdate']					= date("Y-m-d H:i:s");
				   				$info2['user_id']				= $_SESSION['user_id'];
				   				$info2['company_id']			= $_SESSION['company_id'];
				   				$info2['branch_id']				= $row['branch_id'];

		   						$grn_trn_sub_id=add_record('tbl_grn_sub_trn', $info2, $dbcon);




						$rate_unit = $POST['grn_rate_unit'][$k];

						$grn_base_qty = $POST['grn_qty_hide'][$k];
						$grn_base_unit = $POST['unit_id'][$k];

						$grn_conv_qty = $POST['conv_grn_qty_hide'][$k];
						$grn_conv_unit = $POST['conv_unit_id'][$k];

					$product_id = $POST['grn_pid'][$k];
					upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc); // for update batch no tempory status and add grn_id for multiple batch  
						
				$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id;
					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					$batch_no = "";
					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$product_id);

					}
				
					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}

	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					$mfg_date = date("Y-m-d");
					$exp_date = get_exp_date_by_product($dbcon, $product_id,date("d-m-Y"));
					$batch_info['mfg_date']			= $mfg_date;
					$batch_info['exp_date']			= $exp_date;
					
					$batch_info['grn_id']			= $grn_id;	
					$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					$batch_info['order_no']			= $_POST['grn_no'];
					$batch_info['product_id']		= $product_id;
					$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';			
					$batch_info['grn_godown']		= $godown_id;
					$batch_info['qc_status']			= $product_qc;
					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']	= $remaining_qty;
					}
					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $_SESSION['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']		= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_qty']		= $batch_conv_qty;
					$batch_info['conv_unit']		= $grn_conv_unit;

					if($remaining_qty >  0){
						$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
						if($batch_gen_id){
							if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
								update_batch_no($dbcon,$product_id);
							}
						}						
					}	

						update_returnable_chalan_status($dbcon,$info['returnable_id'],$info2s['returnable_trn_id']);
							}
						}
					}
				}

				$grn_qty_tmp=$POST['grn_qty_tmp'];


				for($k=0;$k<count($grn_qty_tmp);$k++)
				{
					$loop_id=$grn_qty_tmp[$k];
					if($POST['grn_qty_tmp'][$k]!=0){
						if($POST['grn_qty_tmp'][$k]!=""){

							if($info['ref_type']==6){

								$tbl_grn_trn_id= $POST['grn_trn_id_tmp'][$k];
								$product_id = $POST['grn_pid_tmp'][$k];

								$upd_grn['grn_id'] = $grn_id;
								$upd_grn['grn_trn_status'] = 0;
								update_record('tbl_grn_trn', $upd_grn,"grn_trn_id=".$tbl_grn_trn_id." and product_id=".$product_id, $dbcon);



								$upd_ret_info['status'] = 0;
								$upd_ret_info['grn_status'] = 1;
								$upd_ret_info['grn_id'] =  $grn_id;
								update_record('tbl_returnable_chalan_grn_trn', $upd_ret_info,"grn_trn_id=".$tbl_grn_trn_id." and product_id=".$product_id, $dbcon);


							/*echo	$upd_qry = "update tbl_grn_trn set grn_id = ".$grn_id." grn_trn_status=0 where grn_trn_id=".$tbl_grn_trn_id." and product_id = " .$product_id;
								$dbcon->query($upd_qry);
								die;*/
								if(brp_strtolower($POST['qc_type_tmp'][$k])=="no"){
									$godown_id=$POST['grn_godown_tmp'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								
								

								$ptrn=$info2s['purchaseordertrn_id'];
								// $hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);
						

							$rate_unit = $POST['grn_rate_unit_tmp'][$k];

							$grn_base_qty = $POST['grn_qty_tmp_hide'][$k];
							$grn_base_unit = $POST['unit_id_tmp'][$k];

							$grn_conv_qty = $POST['conv_grn_qty_tmp_hide'][$k];
							$grn_conv_unit = $POST['conv_unit_id_tmp'][$k];

								
					upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc); // for update batch no tempory status and add grn_id for multiple batch  
						
				$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id;
					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					$batch_no = "";
					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$product_id);

					}
					/*echo "rate unit :" . $rate_unit . "</br>";
					echo "grn_base_qty :" . $grn_base_qty . "</br>";
					echo "grn_base_unit :" . $grn_base_unit . "</br>";
					echo "grn_conv_qty :" . $grn_conv_qty . "</br>";
					echo "grn_conv_unit  :" . $grn_conv_unit  . "</br>";
					echo "remaining_qty  :" . $remaining_qty  . "</br>";*/

					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}

	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					/*echo "batch_qty  :" . $batch_qty  . "</br>";
					echo "batch_conv_qty  :" . $batch_conv_qty  . "</br>";*/
					$mfg_date = date("Y-m-d");
					$exp_date = get_exp_date_by_product($dbcon, $product_id,date("d-m-Y"));
					$batch_info['mfg_date']			= $mfg_date;
					$batch_info['exp_date']			= $exp_date;
						
					$batch_info['grn_id']			= $grn_id;	
					$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					$batch_info['order_no']			= $_POST['grn_no'];
					$batch_info['product_id']		= $product_id;
					$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';			
					$batch_info['grn_godown']		= $godown_id;
					$batch_info['qc_status']		= $product_qc;
					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']		= $remaining_qty;
					}
					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $POST['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']			= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_qty']			= $batch_conv_qty;
					$batch_info['conv_unit']		= $grn_conv_unit;

					

					if($remaining_qty >  0){
							$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
							// var_dump($batch_gen_id);die;
								if($batch_gen_id){
									if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
										update_batch_no($dbcon,$product_id);

									}
								}						
							}	

								update_returnable_chalan_status($dbcon,$info['returnable_id'],0);
						}
					}
				}					
			}

			
					
			
			}
			
		}
		if($grn_against=='7'){ // stock transfer

			// var_dump($POST);die;
			$customer_id =  $POST['vender_id'];
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			update_series_no_using_type_id($dbcon,STOCK_TRF_GRN,$_SESSION['company_id'],$branch_id1);
			
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['stock_transfer_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];

			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];
			$info['is_conversation']		= $POST['is_conversation'];

			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['branch_id']			= $branch_id;
			//echo "<pre>"; print_r($info);die;
			
			$grn_id=add_record('tbl_grn', $info, $dbcon);
			
			if($grn_id){
				$grn_qty=$POST['grn_qty'];

				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){

							if($info['ref_type']==7){
								if(brp_strtolower($POST['qc_type'][$k])=="no"){
									$godown_id=$POST['grn_godown'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}

								$wwe=get_pro_field($dbcon,$POST['grn_pid'][$k],'minimum_tolerance');
								$info2s['stock_transfer_id']	=$POST['purchaseorder_id'];
								$info2s['stock_transfer_trn_id']	=$POST['purchaseordertrn_id'][$k];
								$info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['grn_id']				=$grn_id;
								$info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								$info2s['unit_id']				=$POST['unit_id'][$k];
								$info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								$info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								$info2s['grn_godown']			=$godown_id;
								$info2s['product_qc']			=$product_qc;
								$info2s['tolerance']			=$wwe;
								$info2s['po_ref_id']			=$POST['po_ref_id'][$k];
								//	var_dump($info2['po_ref_id']);
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
								$info2s['ref_type']			= $POST['grn_against'];
								$info2s['rate_unit']		= $POST['grn_rate_unit'][$k];
						
								$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon, $branch_id);

								if($tbl_grn_trn_id){

									$info2_sub['product_id'] = $info2s['product_id'];
									$info2_sub['grn_trn_id'] = $tbl_grn_trn_id;
									$info2_sub['stock_transfer_trn_id'] = $info2s['stock_transfer_trn_id'];
									$info2_sub['stock_transfer_id'] = $info2s['stock_transfer_id'];
									$info2_sub['product_base_unit'] = $info2s['unit_id'];
									$info2_sub['product_conv_unit'] = $info2s['product_conv_unit'];
									$info2_sub['cdate'] = date("Y-m-d H:i:s");
									$info2_sub['user_id'] = $_SESSION['user_id'];
									$info2_sub['company_id'] = $_SESSION['company_id'];
									$info2_sub['branch_id'] = $branch_id;
									$info2_sub['product_qty'] = $info2s['product_qty'];
									$info2_sub['product_conv_qty'] = $info2s['product_conv_qty'];
									$tbl_grn_trn_id = add_record('tbl_grn_sub_trn', $info2_sub, $dbcon);

									update_stock_transfer_grn_status($dbcon,$info2s['stock_transfer_id'],$info2s['stock_transfer_trn_id'],$info2s['product_qty'],$info2s['product_conv_qty']);

								}
								
								$product_id = $POST['grn_pid'][$k];
								$rate_unit = $POST['grn_rate_unit'][$k];

								$grn_base_qty = $POST['grn_qty_hide'][$k];
								$grn_base_unit = $POST['unit_id'][$k];

								$grn_conv_qty = $POST['conv_grn_qty_hide'][$k];
								$grn_conv_unit = $POST['conv_unit_id'][$k];

								
								//var_dump("ww");	
								upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc,"",$POST['to_godown_id']); // for update batch no tempory status and add grn_id for multiple batch  
								
								$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id;
								$res12=mysqli_fetch_assoc($dbcon->query($qry12));

								$batch_qty = $res12['qty'];
								if($grn_conv_unit==$rate_unit){
									$remaining_qty = $grn_conv_qty - $batch_qty;
								}else{
									$remaining_qty = $grn_base_qty - $batch_qty;
								}
								//var_dump($remaining_qty);
								$batch_no = "";
								if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
									$batch_no = get_batch_no($dbcon,$product_id);

								}
					

								if($grn_conv_unit==$rate_unit){
									$type="base_unit";
									$conv_qty=$remaining_qty;
									$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
									
								}else{
									$type="conv_unit";
									$base_qty=$remaining_qty;
									$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
									
								}

								$batch_qty=$base_qty;
								$batch_conv_qty=$conv_qty;

							
								$batch_info['grn_id']			= $grn_id;	
								$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
								$batch_info['batch_no']			= $batch_no;
								$batch_info['batch_qty']		= $remaining_qty;
								$batch_info['order_no']			= $_POST['grn_no'];
								$batch_info['product_id']		= $product_id;
								$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
								$batch_info['batch_type']		= $companyConfiguration['batch_type'];
								$batch_info['production_type']	= '1';			
								$batch_info['status']			= '0';			
								$batch_info['to_godown_id']		= $POST['to_godown_id'];			
								$batch_info['grn_godown']		= $godown_id;
								$batch_info['qc_status']		= $product_qc;
								if($product_qc==1){
									$batch_info['accept_qty']	= $remaining_qty;
									$batch_info['qc_qty']		= $remaining_qty;
								}
								$batch_info['cdate']			= date("Y-m-d H:i:s"); 
								$batch_info['user_id']			= $_SESSION['user_id'];
								$batch_info['company_id']		= $_SESSION['company_id'];	
								$batch_info['branch_id']		= $POST['branch_id'];
								$batch_info['batch_unit']		= $rate_unit;
								$batch_info['base_qty']			= $batch_qty;
								$batch_info['base_unit']		= $grn_base_unit;
								$batch_info['conv_qty']			= $batch_conv_qty;
								$batch_info['conv_unit']		= $grn_conv_unit;

								if($remaining_qty >  0){
									$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
									if($batch_gen_id){
										if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
											update_batch_no($dbcon,$product_id);
										}
									}						
								}	
								
							}
						}
					}
				}
			}
		}

		if($grn_against=='8'){
			$branch_id = $POST['branch_id'];

			$info_grn['grn_no']				= $POST['grn_no'];
			$info_grn['grn_date']			=  date('Y-m-d',strtotime($POST['grn_date']));
			$info_grn['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info_grn['invoice_no']			= $POST['invoice_no'];
			$info_grn['challan_no']			= $POST['challan_no'];
			$info_grn['ref_type']			= $POST['grn_against'];
			$info_grn['vender_id']			= $POST['vender_id'];
			$info_grn['remark']				= $POST['remark'];
			$info_grn['is_conversation']	= $POST['is_conversation'];
			$info_grn['vehicle_no']			= $POST['vehicle_no'];
			$info_grn['mode_dispatch']		= $POST['mode_dispatch'];
			$info_grn['cdate']				= date("Y-m-d H:i:s");
			$info_grn['user_id']			= $_SESSION['user_id'];
			$info_grn['company_id']			= $_SESSION['company_id'];
			
			//echo "<pre>"; primt_r($info_grn);die;

			$grn_id=add_record('tbl_grn',$info_grn, $dbcon,$branch_id);

			if($grn_id){
				update_series_no_using_type_id($dbcon,OUTSIDE_GRN,$_SESSION['company_id'],$branch_id1);

				$grn_qty_st=$POST['grn_qty'];
				for($m=0;$m<count($grn_qty_st);$m++)
				{
					if($POST['grn_qty'][$m]!=0 && $POST['grn_qty'][$m]!="")
					{
						$product_id			= $POST['product_id'][$m];
						$stop_qty			= $POST['grn_qty'][$m];
						$product_base_unit	= $POST['product_base_unit'][$m];
						$stop_conv_qty			= $POST['conv_grn_qty'][$m];
						$product_conv_unit	= $POST['product_conv_unit'][$m];
						
						$process_id			= $POST['process_id'][$m];
						$grn_godown			= $POST['grn_godown'][$m];
						$p_id				= $POST['p_id'][$m];
						$rate_unit			= $POST['grn_rate_unit'][$m];
						$is_reprocess			= $POST['is_reprocess'][$m];

						$pid_array = array();
						$end_qty_array =array();
						
						// grn_trn_and_sub_trn_entry($dbcon,$product_id,$grn_id,$stop_qty,$product_base_unit,$process_id,$grn_godown,$p_id,$branch_id,$pid_array,$end_qty_array, $job_work_po_trn_id,$stop_conv_qty,$product_conv_unit,$POST['grn_no'],"","","",$POST['grn_against'],$rate_unit,"","","","","",$is_reprocess);

						$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id,$process_id);
		
						$companyConfiguration = getCompanyConfiguration($dbcon, $id = false);

						$info_grn_trn['product_id']				= $product_id;
						$info_grn_trn['description']			= "";
						$info_grn_trn['grn_id']					= $grn_id;
						$info_grn_trn['product_qty']			= $stop_qty;
						$info_grn_trn['unit_id']				= $product_base_unit;
						$info_grn_trn['product_conv_qty']		= $stop_conv_qty;
						$info_grn_trn['product_conv_unit']		= $product_conv_unit;
						$info_grn_trn['process_id']				= $process_id;
						$info_grn_trn['grn_godown']				= $grn_godown;
						$info_grn_trn['job_work_po_trn_id']		= $job_work_po_trn_id;
						$info_grn_trn['ref_type']				= $grn_against;
						$info_grn_trn['rate_unit']				= $rate_unit;
						/*$info_grn_trn['product_scrap_id']		= $product_scrap_id;
						$info_grn_trn['scrap_unit']				= $scrap_unit;
						$info_grn_trn['scrap_qty']				= $scrap_qty;*/
						
						if($qc_paramter_info=="1"){
							$info_grn_trn['product_qc']			= "0"; // QC pending 
						}
						else{
							$info_grn_trn['product_qc']			= "1"; // QC Done
						}
						
						$info_grn_trn['cdate']					= date("Y-m-d H:i:s");
						$info_grn_trn['user_id']				= $_SESSION['user_id'];
						$info_grn_trn['company_id']				= $_SESSION['company_id'];
						
						$grn_trn_id=add_record('tbl_grn_trn',$info_grn_trn,$dbcon,$branch_id);


						$qry = "select * from product_mst where product_id = " . $product_id;
						$result=$dbcon->query($qry);
						$res=mysqli_fetch_assoc($result);
						
						if($qc_paramter_info=="1"){
							$product_qc	= "0"; // QC pending 
						}
						else{
							$product_qc	=	"1"; // QC Done
						}
					

					/*Added by Sanat :: START  :: 19-11-21 */

					$base_unit = $product_base_unit;

					// $conv_qty = $stop_qty;
					$conv_unit = $res['product_conv_unit'];

					// check product batch stock setting 

					$qry1= "select * from tbl_allocate_re_process where p_id in(" . $p_id .")";
					$result1=$dbcon->query($qry1);
					while($res1=brp_mysqli_fetch_assoc($result1)){
						$batch_qry= "select * from tbl_batch_data where batch_id = " . $res1['batch_id'];
						$batch_result=$dbcon->query($batch_qry);
						$batch_res=brp_mysqli_fetch_assoc($batch_result);

							$allocate_trn_update_qty = 0;
							if($res1['start_qty']>"0" && $stop_qty > "0"){
								if($res1['start_qty']<=$stop_qty){
									//use $row1['pending_qty']
									$allocate_trn_update_qty=$res1['start_qty'];
								}else{
									//use $grn_sub_trn_qty
									$allocate_trn_update_qty=$stop_qty;
								}	
							// echo ">> ".$stop_qty . " <<<";
							$type="conv_unit";
							
							$conv_qty = ($allocate_trn_update_qty/$batch_res['base_qty']) * $batch_res['conv_qty'];

							$batch_info['grn_id']			= $batch_res['grn_id'];	
							$batch_info['grn_trn_id']		= $batch_res['grn_trn_id'];	
							$batch_info['batch_no']			= $batch_res['batch_no'];
							$batch_info['batch_qty']		= $allocate_trn_update_qty;
							$batch_info['order_no']			= $batch_res['grn_no'];
							$batch_info['product_id']		= $product_id;
							$batch_info['grn_date']			= date('Y-m-d',strtotime($batch_res['grn_date']));
							$batch_info['batch_type']		= $companyConfiguration['batch_type'];
							$batch_info['production_type']	= '1';			
							$batch_info['status']			= '0';			
							$batch_info['reprocess_qc']			= '1';			
							$batch_info['grn_godown']		= $grn_godown;
							$batch_info['qc_status']		= $product_qc;
							if($qc_paramter_info==0){
								$batch_info['accept_qty']	= $base_qty;
								$batch_info['qc_qty']		= $base_qty;

							}
							$batch_info['cdate']			= date("Y-m-d H:i:s"); 
							$batch_info['user_id']			= $_SESSION['user_id'];
							$batch_info['company_id']		= $_SESSION['company_id'];	
							$batch_info['branch_id']		= $branch_id;
							$batch_info['batch_unit']		= $product_base_unit;
							$batch_info['base_qty']			= $allocate_trn_update_qty;
							$batch_info['base_unit']		= $base_unit;
							$batch_info['conv_qty']			= $conv_qty;
							$batch_info['conv_unit']		= $conv_unit;
							$batch_info['process_id']		= $process_id;
							$batch_info['p_id']				= $res1['p_id'];
							$batch_info['reprocess_qc_id']	= $reprocess_qc_id;

							$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
							
							// $base_qty;
							$used_conv_qty = 0;

								add_reprocess_start_stop_entry($dbcon,$allocate_trn_update_qty,$res1['p_id'] ,2);
							// $allocate_trn_stop_qty=allocate_reprocess_trn_stop_entry_start_entry_wise($dbcon,$allocate_trn_update_qty,$row['p_id']);
							
								tbl_allocate_reprocess_update_pen_qty($dbcon,$res1['p_id'],$allocate_trn_update_qty);
								
							//allocate process table pen_qty update end
							
							//allocate process pstatus update start
							
								tbl_allocate_reprocess_update_p_status($dbcon,$res1['p_id']);
								
							//allocate process pstatus update start
									$stop_qty=$stop_qty - $allocate_trn_update_qty;
							
							}
						
						}

			$stop_qty = $POST['grn_qty'][$m];			

			$query = "select job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.product_con_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id,job_trn.rate_unit,ap.p_ref_id,job_sub_trn.pr_rate from tbl_job_work_sub_trn as job_sub_trn
					left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
					 left join tbl_allocate_process as ap on ap.p_id=job_sub_trn.p_id
					where job_sub_trn.grn_complete_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.is_reprocess = 1 and job_sub_trn.p_id in (".$p_id.")";
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);
			if($cnt>0){
				
				while($row=brp_mysqli_fetch_array($result))
				{
					$job_work_sub_trn_grn_pending_qty=job_work_sub_trn_grn_pending_qty($dbcon,$row['job_work_sub_trn_id']);
					
					if($job_work_sub_trn_grn_pending_qty!="" && $job_work_sub_trn_grn_pending_qty!="0" && $stop_qty!="0"){
						
						if($job_work_sub_trn_grn_pending_qty<=$stop_qty){
							//use $job_work_sub_trn_grn_pending_qty
							$used_qty=$job_work_sub_trn_grn_pending_qty;
						}else{
							//use $stop_qty
							$used_qty=$stop_qty;
						}
						if($used_qty>"0"){
							$stop_qty=$stop_qty-$used_qty;
							$used_conv_qty = 0;

							if($row['product_base_unit'] != $row['product_conv_unit']){
								$grn_qry = "select * from tbl_grn_trn where grn_trn_id = " . $grn_trn_id;
								$grn_res=$dbcon->query($grn_qry);
								$grn_row=brp_mysqli_fetch_array($grn_res);
								$used_conv_qty=($used_qty/$grn_row['product_qty'])*$grn_row['product_conv_qty'];
							}else{
								$used_conv_qty = $used_qty;
							}
							//tbl_grn_sub_trn entry start
								$info_grn_sub_trn['product_id']				= $row['product_id'];
								$info_grn_sub_trn['grn_trn_id']				= $grn_trn_id;
								$info_grn_sub_trn['jobwork_id']				= $row['job_work_id'];
								$info_grn_sub_trn['job_work_trn_id']		= $row['job_work_trn_id'];
								$info_grn_sub_trn['job_work_sub_trn_id']	= $row['job_work_sub_trn_id'];
								$info_grn_sub_trn['product_qty']			= $used_qty;
								$info_grn_sub_trn['product_base_unit']		= $row['product_base_unit'];
								$info_grn_sub_trn['product_conv_qty']		= $used_conv_qty;
								$info_grn_sub_trn['product_conv_unit']		= $row['product_con_unit'];
								$info_grn_sub_trn['job_work_po_trn_id']		= $job_work_po_trn_id;
								
								$info_grn_sub_trn['purchaseordertrn_id']	= $job_work_po_trn_id;

								$info_grn_sub_trn['product_scrap_id']		= $product_scrap_id;
								$info_grn_sub_trn['scrap_unit']				= $scrap_unit;
								$info_grn_sub_trn['rp_id']				= $row['p_ref_id'];

								$grn_scrap_qty =  $used_qty * $scrap_qty / $stop_qty;
								$info_grn_sub_trn['scrap_qty']				=round_up($grn_scrap_qty,5);
								
								$info_grn_sub_trn['cdate']					= date("Y-m-d H:i:s");
								$info_grn_sub_trn['user_id']				= $_SESSION['user_id'];
								$info_grn_sub_trn['company_id']				= $_SESSION['company_id'];

								$info_grn_sub_trn['product_process_rate']		=$row['pr_rate'];
								$info_grn_sub_trn['product_process_unit']		=$row['product_base_unit'];
								// $info_grn_sub_trn['material_rate']			= $used_qty * $row['pr_rate'];

								$info_grn_sub_trn['total_process_rate']			= $used_qty * $row['pr_rate'];
									
								$mt_rate = convert_rate($dbcon,$row['pr_rate'],$row['product_id'],"conv_unit");
								
								// $info_grn_sub_trn['material_conv_rate']			= $conv_used_qty * $mt_rate;
								$info_grn_sub_trn['total_process_conv_rate']	= $used_conv_qty * $mt_rate;
								
								$info_grn_sub_trn['material_conv_rate']			= $used_conv_qty * $mt_rate;
								$grn_trn_sub_id=add_record('tbl_grn_sub_trn',$info_grn_sub_trn,$dbcon,$branch_id);

								$upd_trn_data['product_process_rate']		=$row['pr_rate'];
								$upd_trn_data['product_process_unit']		=$row['product_base_unit'];

								update_record('tbl_grn_trn', $upd_trn_data,"grn_trn_id=".$grn_trn_id, $dbcon);
							
								grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id']);
							
						}
						
					}
				}
			}

					}
				}
			}	
		}
		
		if($grn_against=='4')
		{
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			update_series_no_using_type_id($dbcon,DIRECT_GRN,$_SESSION['company_id'],$branch_id1);
						
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['purchaseorder_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];

			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];
			$info['is_conversation']	= $POST['is_conversation'];

			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			//echo "<pre>"; print_r($info); die;
			
			$grn_id=add_record('tbl_grn', $info, $dbcon, $branch_id);
			//echo "test";die;
			
			if($grn_id){
				$grn_qty=$POST['grn_qty'];
				/*echo "<pre>"; print_r($grn_qty);
				echo count($grn_qty);*/
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){
							//echo "ttt"; die;

								$tbl_grn_trn_id= $POST['grn_trn_id'][$k];
								$product_id = $POST['grn_pid'][$k];
								$godown_id= $POST['grn_godown'][$k];
								$upd_grn['grn_id'] = $grn_id;
								$upd_grn['grn_trn_status'] = 0;
								update_record('tbl_grn_trn', $upd_grn,"grn_trn_id=".$tbl_grn_trn_id." and product_id=".$product_id." and ref_type = 4", $dbcon);



							if($info['ref_type']==4){
								
								// //var_dump($hhhh);
								// if($godown_id!=""){
									
								// 	/* jayesh for setting store approve or not */
								// 	$company_data = getCompanyConfiguration($dbcon, $id = false);
								// 	if($company_data['store_approval'] == '0')
								// 	{
								// 		$stock_id=add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id);
								// 	}
								// 	else
								// 	{	
								// 		$store_receive_id = add_store_receive($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id);
								// 	}
								// 	/* jayesh for setting store approve or not */

								// 	$query_res="select * from tbl_request_product as req where rp_id in (".$info2s['po_ref_id'].")";
								// 	$result_res=$dbcon->query($query_res);
								// 	$resqty1=$POST['grn_qty'][$k];
								// 	while($row_res=brp_mysqli_fetch_assoc($result_res)){

								// 		$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
								// 		$result_ind=$dbcon->query($query_ind);
								// 		$row_ind=brp_mysqli_fetch_assoc($result_ind);
								// //echo $query_ind;
								// 		$reserve_id="";
								// 		$request_id=$row_res['rp_id'];
								// 		$complaint_id="";
								// 		$sales_order_trn_id="";

								// 		$used_rese=total_reserve_stock($dbcon,$row_res['rp_pid'],$info2s['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
								// //var_dump($used_rese);
								// 		$res_pending=$row_ind['app_qty']-$used_rese;

								// //var_dump($res_pending);
								// //var_dump($resqty1);
								// 	//add_request_reserve_stock($dbcon,$info2s['po_ref_id'],$info2s['product_qty'],$info2s['unit_id']);
								// 		if($resqty1>=$res_pending){
								// 	//add_request_reserve_stock($dbcon,$request_id,$res_pending,$info2s['unit_id'],$branch_id);

								// 			/*grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id);*/

								// 			$resqty1=$resqty1-$res_pending;
								// 		}else{
								// 	//add_request_reserve_stock($dbcon,$request_id,$resqty,$info2s['unit_id'],$branch_id);

								// 			/*grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id)*/;

								// 			$resqty1=$resqty1-$resqty1;
								// 		}
								// //var_dump($resqty1);
								// 	}
								// }


								$rate_unit = $POST['grn_rate_unit'][$k];

					$grn_base_qty = $POST['grn_qty_hide'][$k];
					$grn_base_unit = $POST['unit_id'][$k];

					$grn_conv_qty = $POST['conv_grn_qty_hide'][$k];
					$grn_conv_unit = $POST['conv_unit_id'][$k];

								$product_id = $POST['grn_pid'][$k];
					upadte_batch_data_status($dbcon,$grn_id,$tbl_grn_trn_id,$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc); // for update batch no tempory status and add grn_id for multiple batch  
						
				$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $grn_id;
					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					$batch_no = "";
					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$product_id);

					}
					/*echo "rate unit :" . $rate_unit . "</br>";
					echo "grn_base_qty :" . $grn_base_qty . "</br>";
					echo "grn_base_unit :" . $grn_base_unit . "</br>";
					echo "grn_conv_qty :" . $grn_conv_qty . "</br>";
					echo "grn_conv_unit  :" . $grn_conv_unit  . "</br>";
					echo "remaining_qty  :" . $remaining_qty  . "</br>";*/

					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}

	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					/*echo "batch_qty  :" . $batch_qty  . "</br>";
					echo "batch_conv_qty  :" . $batch_conv_qty  . "</br>";*/

						
					$batch_info['grn_id']			= $grn_id;	
					$batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					$batch_info['order_no']			= $_POST['grn_no'];
					$batch_info['product_id']		= $product_id;
					$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';		

					if(brp_strtolower($POST['qc_type'][$k])=="no"){
						$godown_id=$POST['grn_godown'][$k];
						$product_qc=1;
					}else{
						$godown_id="";
						$product_qc=0;
					}	
					
					$batch_info['qc_status']		= $product_qc;
					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']		= $remaining_qty;
					}
					$batch_info['grn_accept_qty']	= $remaining_qty;
					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $POST['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']			= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_unit']		= $grn_conv_unit;
					$batch_info['conv_qty']			= $batch_conv_qty;
					$batch_info['grn_accept_qty']	= $remaining_qty;
					$batch_info['grn_godown']	= $godown_id;

					if($remaining_qty >  0){

							$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);if($batch_gen_id){
									if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
										update_batch_no($dbcon,$product_id);
									}
								}						
							}
							}
						}
					}
				}		
			//$updatetrnid=update_record('tbl_grn_trn', $infotrn,"grn_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			/*Update Data in Trn Table End*/
			$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);
		}
		
		
		if(!empty($_FILES['grn_file']['tmp_name'][0])) {
			$imgresp = upload_grn_receipt($_FILES,$dbcon,$grn_id);
		}
		if($grn_id){	
			$arr['msg']="1";	
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"grn_add",1,"tbl_grn",$inserpoid);						
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		
		echo json_encode($arr);	
	}
	else if(brp_strtolower($POST['mode']) == "add_old"){
		if($grn_against=='1')
		{
			$grn_qty_st=$POST['grn_qty'];
			for($m=0;$m<count($grn_qty_st);$m++)
			{
				if($POST['grn_qty'][$m]!=0)
				{
					if($POST['grn_qty'][$m]!="")
					{
						$qty = $POST['grn_qty'][$m];
						$j_alloc_process_id = $POST['j_alloc_process_id'][$m];
						if(brp_strtolower($POST['qc_type'][$m])=="no"){
							$godown_id=$POST['grn_godown'][$m];
							$product_qc=1;
						}else{
							$godown_id="";
							$product_qc=0;
						}
						
						$wwe=get_pro_field($dbcon,$POST['grn_pid'][$m],'minimum_tolerance');
						//$info2['purchaseorder_id']	=$POST['purchaseorder_id'];
						$info2s['purchaseordertrn_id']=$POST['purchaseordertrn_id'][$m];
						$info2s['product_id']		=$POST['grn_pid'][$m];
						$info2s['grn_id']			=$inserpoid;
						$info2s['product_qty']		=$POST['grn_qty'][$m];
						$info2s['unit_id']			=$POST['unit_id'][$m];
						$info2s['grn_godown']		=$godown_id;
						$info2s['product_qc']		=$product_qc;
						$info2s['tolerance']		=$wwe;
						$info2s['po_ref_id']		=$POST['po_ref_id'][$m];
					//	var_dump($info2['po_ref_id']);
						$info2s['cdate']			= date("Y-m-d H:i:s");
						$info2s['user_id']			= $_SESSION['user_id'];
						$info2s['company_id']		= $_SESSION['company_id'];

					//pathik start 26-02-2021 3:46	
						$info2s['process_id']		= $POST['j_pr_process_id'][$m];
					//pathik end 26-02-2021 3:46	
						//var_dump($info2);
						$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon,$branch_id);
						
						
						
						// main processs
						$sqty=0;$jpqty=0;$pending_qty=0;
						$a_query="select * from tbl_allocate_process where p_id in (".$j_alloc_process_id.")";
						//var_dump($query);
						$a_result=$dbcon->query($a_query);
						while($a_row=brp_mysqli_fetch_assoc($a_result))
						{

							$query11="select sum(pt_qty) as start_qty from tbl_allocate_process_trn as trn where p_status=0 and pt_alloc_id=".$a_row['p_id'];
							$rel1=brp_mysqli_fetch_assoc($dbcon->query($query11));
							
							$query12="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_alloc_id=".$a_row['p_id'];
							$rel2=brp_mysqli_fetch_assoc($dbcon->query($query12));

							$pending_qty=$rel1['start_qty']-$rel2['end_qty'];
							/* if($qty!=0){
								if($qty!=0){ */
									if($pending_qty>=$qty){
										$sqty=$qty;
										$jpqty=$sqty;

										$info6['process_id']		= $a_row['process_id'];
										$info6['p_start_time']		='';
										$info6['p_end_time']		= date("Y-m-d H:i:s");
										$info6['p_qty']				= $sqty;
										$info6['pen_qty']			='';
										$info6['p_status']			='2';
										$info6['p_ref_id']			= $a_row['p_ref_id'];
										$info6['p_product_id']		= $a_row['p_product_id'];
										$info6['j_alloc_process_id']= $a_row['p_id'];

										$info6['cdate']				= date("Y-m-d H:i:s");
										$info6['user_id']			= $_SESSION['user_id'];
										$info6['company_id']		= $_SESSION['company_id'];


										$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon, $branch_id);

										add_process_stock_new($dbcon,$a_row['p_id'],$sqty,$sqty);


								//pathik start
										$job_qty=$sqty;
										$jid=explode(",",$POST['j_job_work_id'][$m]);
										for($p=0;$p<count($jid);$p++)
										{
											$jobquery11="select sum(j_qty) as start_qty from tbl_jobwork as trn where status=0 and jobwork_id=".$jid[$p];
											$jobrel1=brp_mysqli_fetch_assoc($dbcon->query($jobquery11));

											$jobquery12="select sum(product_qty) as end_qty from tbl_grn_sub_trn as trn where status=0 and jobwork_id=".$jid[$p];
											$jobrel2=brp_mysqli_fetch_assoc($dbcon->query($jobquery12));
											$job_pending_qty=$jobrel1['start_qty']-$jobrel2['end_qty'];

											if($job_qty!=0){
												if($job_qty!=""){
													if($job_qty>=$job_pending_qty){
														$infogtrn['product_id']			= $info2s['product_id'];
														$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;

														$infogtrn['jobwork_id']			= $jid[$p];
														$infogtrn['product_qty']		= $job_pending_qty;

														$infogtrn['cdate']				= date("Y-m-d H:i:s");
														$infogtrn['user_id']			= $_SESSION['user_id'];
														$infogtrn['company_id']			= $_SESSION['company_id'];

														$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);

										//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);

														$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=brp_mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);
											$j_process_qty=$infogtrn['product_qty'];
										//var_dump("1");
										//var_dump($j_process_qty);
											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty order by jobwork_process_id";
										//var_dump($j_que_po);
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=brp_mysqli_fetch_assoc($j_resi_grn)){
											//var_dump("2");
											//var_dump($j_re['pen_qty']);
											//var_dump("3");
											//var_dump($j_process_qty);
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
													//var_dump($j_re['pen_qty']);
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
													//var_dump($j_process_qty);
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
										//die();
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=brp_mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_pending_qty;
										}else{
											$infogtrn['product_id']		= $info2s['product_id'];
											$infogtrn['grn_trn_id']		= $tbl_grn_trn_id;
											
											$infogtrn['jobwork_id']			= $jid[$p];
											$infogtrn['product_qty']	= $job_qty;
											
											$infogtrn['cdate']				= date("Y-m-d H:i:s");
											$infogtrn['user_id']			= $_SESSION['user_id'];
											$infogtrn['company_id']			= $_SESSION['company_id'];
											
											$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);
											
											
											//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);
											
											$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=brp_mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);

											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty";
											$j_process_qty=$infogtrn['product_qty'];
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=brp_mysqli_fetch_assoc($j_resi_grn)){
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=brp_mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_qty;
										}
									}
								}
							}
							if($product_qc==1){	
								$process=get_next_process($dbcon,$a_row['process_id'],$info2s['product_id'],$a_row['p_ref_id'],$a_row['process_priority']);

								$process_pr=json_decode($process);

								$process_id_new=$process_pr->process_id;
								$process_type=$process_pr->process_type;
								$process_priority=$process_pr->process_priority;

								if($process_id_new==0){
									if($godown_id!=""){
										add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$sqty,"1",$branch_id,"","");

										add_request_reserve_stock($dbcon,$a_row['p_ref_id'],$sqty,$info2s['unit_id'],$branch_id);

									}
								}else{
									process_allocate($dbcon,$a_row['p_id'],$process_id_new,$sqty,$a_row['p_ref_id'],"tbl_grn_trn",$info2s['product_id'],$process_type,$info2s['unit_id'],$process_priority,"",$branch_id);

								}
								add_process_stock($dbcon,$a_row['p_id'],$sqty,0,$process_id_new);
								
							}


								//pathik close

							if($a_row['previous_process_id']=="0"){
									/*$grn_qty=$POST['row_product_id'];
									for($k=0;$k<count($grn_qty);$k++)
									{*/

										$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
										WHERE rpro.p_status!=2 AND rpro.p_id in (".$j_alloc_process_id.")";
										$bom_resul=$dbcon->query($bom);
										$bom_rel1=brp_mysqli_fetch_assoc($bom_resul);
										
										$bom1="SELECT rpro.*,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
										left join product_mst as pro on pro.product_id=rpro.rp_pid
										left join unit_mst as bunit on bunit.unitid=rpro.process_unit
										left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
										WHERE rpro.status!=2 AND rpro.perent_id in (".$bom_rel1['views'].") group by rpro.rp_pid" ;
										$bom1_result=$dbcon->query($bom1);
										

										while($bom_rel=brp_mysqli_fetch_assoc($bom1_result)){

											$o_qty=convert_stock($dbcon,$bom_rel["req_qty_one"],$bom_rel['rp_pid'],"base_unit");
											$bom_rel["req_qty_one"]=round($bom_rel["req_qty_one"],6);
											$o_qty=round($o_qty,6);

											$uqty=$o_qty*$sqty;
											$uqty=round($uqty,4);

											$info2['allocate_process_id']	=$j_alloc_process_id;
											$info2['product_id']			=$bom_rel['rp_pid'];
											$info2['unit_id']				=$bom_rel['process_unit'];
											$info2['used_qty']				=$uqty;
											$info2['cdate']					= date("Y-m-d H:i:s");
											$info2['user_id']				= $_SESSION['user_id'];
											$info2['company_id']			= $_SESSION['company_id'];
											
											
											$tbl_grn_trn_id111=add_record('tbl_allocate_process_material',$info2, $dbcon, $branch_id);
											
											$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id111,$info2['used_qty'],$branch_id);
											
											
											//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
											$request_id=find_request_id($dbcon,$a_row['p_ref_id'],$info2['product_id']);
											
											//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
											deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
										}
										
									//}
									}
									$qty=$qty-$sqty;
								}else{
									$sqty=$pending_qty;
									$jpqty=$sqty;

									$info6['process_id']		= $a_row['process_id'];
									$info6['p_start_time']		='';
									$info6['p_end_time']		= date("Y-m-d H:i:s");
									$info6['p_qty']				= $sqty;
									$info6['pen_qty']			= '';
									$info6['p_status']			= '2';
									$info6['p_ref_id']			= $a_row['p_ref_id'];
									$info6['p_product_id']		= $a_row['p_product_id'];
									$info6['j_alloc_process_id']= $a_row['p_id'];

									$info6['cdate']				= date("Y-m-d H:i:s");
									$info6['user_id']			= $_SESSION['user_id'];
									$info6['company_id']		= $_SESSION['company_id'];


									$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon, $branch_id);

									add_process_stock_new($dbcon,$a_row['p_id'],$sqty,$sqty);


								//pathik start
								/* $infogtrn['product_id']			= $info2s['product_id'];
								$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;
								$infogtrn['jobwork_id']			= $POST['j_job_work_id'][$m];
								$infogtrn['product_qty']		= $sqty;
								$infogtrn['cdate']				= date("Y-m-d H:i:s");
								$infogtrn['user_id']			= $_SESSION['user_id'];
								$infogtrn['company_id']			= $_SESSION['company_id'];
								$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon);
								
								close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$sqty); */
								
								$job_qty=$sqty;
								$jid=explode(",",$POST['j_job_work_id'][$m]);
								for($p=0;$p<count($jid);$p++)
								{
									$jobquery11="select sum(j_qty) as start_qty from tbl_jobwork as trn where status=0 and jobwork_id=".$jid[$p];
									$jobrel1=brp_mysqli_fetch_assoc($dbcon->query($jobquery11));

									$jobquery12="select sum(product_qty) as end_qty from tbl_grn_sub_trn as trn where status=0 and jobwork_id=".$jid[$p];
									$jobrel2=brp_mysqli_fetch_assoc($dbcon->query($jobquery12));
									$job_pending_qty=$jobrel1['start_qty']-$jobrel2['end_qty'];

									if($job_qty!=0){
										if($job_qty!=""){
											if($job_qty>=$job_pending_qty){
												$infogtrn['product_id']		= $info2s['product_id'];
												$infogtrn['grn_trn_id']		= $tbl_grn_trn_id;

												$infogtrn['jobwork_id']			= $jid[$p];
												$infogtrn['product_qty']		= $job_pending_qty;

												$infogtrn['cdate']				= date("Y-m-d H:i:s");
												$infogtrn['user_id']			= $_SESSION['user_id'];
												$infogtrn['company_id']			= $_SESSION['company_id'];

												$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);

										//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);

												$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=brp_mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);
											

											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty";
											$j_process_qty=$infogtrn['product_qty'];
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=brp_mysqli_fetch_assoc($j_resi_grn)){
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=brp_mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_pending_qty;
										}else{
											$infogtrn['product_id']		= $info2s['product_id'];
											$infogtrn['grn_trn_id']		= $tbl_grn_trn_id;
											
											$infogtrn['jobwork_id']			= $jid[$p];
											$infogtrn['product_qty']	= $job_qty;
											
											$infogtrn['cdate']				= date("Y-m-d H:i:s");
											$infogtrn['user_id']			= $_SESSION['user_id'];
											$infogtrn['company_id']			= $_SESSION['company_id'];

											$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);
											
											
											//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);
											
											$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=brp_mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);

											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty";
											$j_process_qty=$infogtrn['product_qty'];
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=brp_mysqli_fetch_assoc($j_resi_grn)){
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=brp_mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_qty;
										}
									}
								}
							}


							if($product_qc==1){	
								$process=get_next_process($dbcon,$a_row['process_id'],$info2s['product_id'],$a_row['p_ref_id'],$a_row['process_priority']);

								$process_pr=json_decode($process);

								$process_id_new=$process_pr->process_id;
								$process_type=$process_pr->process_type;
								$process_priority=$process_pr->process_priority;

								if($process_id_new==0){
									if($godown_id!=""){
										add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$sqty,"1",$branch_id,"","");

										add_request_reserve_stock($dbcon,$a_row['p_ref_id'],$sqty,$info2s['unit_id'],$branch_id);

									}
								}else{
									process_allocate($dbcon,$a_row['p_id'],$process_id_new,$sqty,$a_row['p_ref_id'],"tbl_grn_trn",$info2s['product_id'],$process_type,$info2s['unit_id'],$process_priority,"",$branch_id);

								}
								add_process_stock($dbcon,$a_row['p_id'],$sqty,0,$process_id_new);
								
							}

								//pathik close

							if($a_row['previous_process_id']=="0"){
									/*$grn_qty=$POST['row_product_id'];
									for($k=0;$k<count($grn_qty);$k++)
									{*/

										$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
										WHERE rpro.p_status!=2 AND rpro.p_id in (".$j_alloc_process_id.")";
										$bom_resul=$dbcon->query($bom);
										$bom_rel1=brp_mysqli_fetch_assoc($bom_resul);
										
										$bom1="SELECT rpro.*,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
										left join product_mst as pro on pro.product_id=rpro.rp_pid
										left join unit_mst as bunit on bunit.unitid=rpro.process_unit
										left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
										WHERE rpro.status!=2 AND rpro.perent_id in (".$bom_rel1['views'].") group by rpro.rp_pid" ;
										$bom1_result=$dbcon->query($bom1);
										$i=1;

										while($bom_rel=brp_mysqli_fetch_assoc($bom1_result)){

											$o_qty=convert_stock($dbcon,$bom_rel["req_qty_one"],$bom_rel['rp_pid'],"base_unit");
											$bom_rel["req_qty_one"]=round($bom_rel["req_qty_one"],6);
											$o_qty=round($o_qty,6);


											$uqty=$o_qty*$sqty;
											$uqty=round($uqty,4);

											$info2['allocate_process_id']	=$eid;
											$info2['product_id']			=$bom_rel['rp_pid'];
											$info2['unit_id']				=$bom_rel['process_unit'];

											$info2['used_qty']				=$uqty;
											$info2['cdate']					= date("Y-m-d H:i:s");
											$info2['user_id']				= $_SESSION['user_id'];
											$info2['company_id']			= $_SESSION['company_id'];
											

											$tbl_grn_trn_idq=add_record('tbl_allocate_process_material',$info2, $dbcon, $branch_id);
											
											$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_idq,$info2['used_qty'],$branch_id);
											
											//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
											$request_id=find_request_id($dbcon,$a_row['p_ref_id'],$info2['product_id']);
											
											//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
											deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
										}
									//}
									}
									$qty=$qty-$sqty;
								}
							}
						}

					}
				}
			}
		}
		else if(brp_strtolower($POST['mode']) == "edit") {

			// echo "<pre>";
			// print_r($POST);die;
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['gir_no']				= $POST['gir_no'];
			if($POST['gir_date'] != ""){
				$info['gir_date']     	= date("Y-m-d H:i:s",strtotime($POST['gir_date']));
			}
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['remark']				= $_POST['remark']; 
			$info['vehicle_no']			= $POST['vehicle_no'];
			$info['mode_dispatch']		= $POST['mode_dispatch'];

			$info['cdate']			= date("Y-m-d H:i:s"); 
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id']; 
			$updateid=update_record('tbl_grn', $info,"grn_id=".$POST['eid'] , $dbcon);

			$grn_qty=$POST['grn_qty'];
			
			for($k=0;$k<count($grn_qty);$k++)
			{
				$loop_id=$grn_qty[$k];
				$qc_st=$POST['qc_status'][$k];
				$product_id = $POST['grn_pid'][$k];

				$grn_trn_id = $POST['grn_trn_id'][$k];

				$prd_qry = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id = " . $product_id;
				$prd_row = brp_mysqli_fetch_assoc($dbcon->query($prd_qry));

				$batch_wise_manage = $prd_row['batch_wise_stock_manage'];
				
				if(brp_strtolower($POST['qc_type'][$k])=="no"){
					$godown_id=$POST['grn_godown'][$k];
					$product_qc=1;
				}else{
					$godown_id="";
					$product_qc=0;
				}

				$rate_unit = $POST['grn_rate_unit'][$k];

				$grn_base_qty = $POST['grn_qty_hide'][$k];
				$grn_base_unit = $POST['unit_id'][$k];

				$grn_conv_qty = $POST['conv_grn_qty_hide'][$k];
				$grn_conv_unit = $POST['conv_unit_id'][$k];


				
				$info2['product_qty']		=$POST['grn_qty_hide'][$k];
				$info2['product_conv_qty']	=$POST['conv_grn_qty_hide'][$k];
				$info2['grn_godown']		=$godown_id;
				// $info2['unit_id']			=$POST['unit_id'][$k];
				// $info2['product_conv_unit']	=$POST['conv_unit_id'][$k];
				//$info2['product_qc']		=$product_qc;
				
				$info2['cdate']				= date("Y-m-d H:i:s");
				$info2['user_id']			= $_SESSION['user_id'];
				$info2['company_id']		= $_SESSION['company_id'];
				// var_dump($info2);
				//$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2, $dbcon);


				if(isset($POST['batch_total_qty'][$k])){
					
					upadte_batch_data_status($dbcon,$POST['eid'],$POST['grn_trn_id'][$k],$POST['grn_no'],$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc); // for update batch no tempory status and add grn_id for multiple batch  
						
				$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $POST['grn_no']."' and grn_id = " . $POST['eid'];
					$res12=mysqli_fetch_assoc($dbcon->query($qry12));

					$batch_qty = $res12['qty'];
					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty - $batch_qty;
					}else{
						$remaining_qty = $grn_base_qty - $batch_qty;
					}
					$batch_no = "";
					// if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$product_id);

					// }
					/*echo "rate unit :" . $rate_unit . "</br>";
					echo "grn_base_qty :" . $grn_base_qty . "</br>";
					echo "grn_base_unit :" . $grn_base_unit . "</br>";
					echo "grn_conv_qty :" . $grn_conv_qty . "</br>";
					echo "grn_conv_unit  :" . $grn_conv_unit  . "</br>";
					echo "remaining_qty  :" . $remaining_qty  . "</br>";*/

					if($grn_conv_unit==$rate_unit){
	   					$type="base_unit";
	   					$conv_qty=$remaining_qty;
	   					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
	   					
	   				}else{
	   					$type="conv_unit";
	   					$base_qty=$remaining_qty;
						$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
	   					
	   				}

	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					/*echo "batch_qty  :" . $batch_qty  . "</br>";
					echo "batch_conv_qty  :" . $batch_conv_qty  . "</br>";*/

						
					$batch_info['grn_id']			= $POST['eid'];	
					$batch_info['grn_trn_id']		= $POST['grn_trn_id'][$k];	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					$batch_info['order_no']			= $_POST['grn_no'];
					$batch_info['product_id']		= $product_id;
					$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';			
					
					$batch_info['qc_status']			= $product_qc;
					$batch_info['grn_accept_qty'] = $remaining_qty;
					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']	= $remaining_qty;
					}
					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $POST['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']		= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_qty']		= $batch_conv_qty;
					$batch_info['conv_unit']		= $grn_conv_unit;

					

					if($remaining_qty >  0){
							$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
								if($batch_gen_id){
									// if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
										update_batch_no($dbcon,$product_id);

									// }
								}						
							}
				}else{
					$product_id	= $POST['product_id'][$k];

					if($grn_conv_unit==$rate_unit){
						$remaining_qty = $grn_conv_qty;
	   					$type="base_unit";
	   					$conv_qty=$grn_conv_qty;
	   					$base_qty=convert_stock($dbcon,$conv_qty,$product_id,$type);
	   				}else{
	   					$remaining_qty = $grn_base_qty;
	   					$type="conv_unit";
	   					$base_qty=$grn_base_qty;
						$conv_qty = convert_stock($dbcon,$base_qty,$product_id,$type);
	   				}


	   				$batch_qty=$base_qty;
					$batch_conv_qty=$conv_qty;

					$batch_info['batch_qty']	= $remaining_qty;
					$batch_info['base_qty']		= $batch_qty;
					$batch_info['conv_qty']		= $batch_conv_qty;

					if($product_qc==1){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']	= $remaining_qty;
					}


					// var_dump($batch_info);
					
					$batchupdateid=update_record('tbl_batch_data', $batch_info,"grn_trn_id=".$POST['grn_trn_id'][$k] , $dbcon);
				}

				// if($qc_st!=1){
					$updateid1=update_record('tbl_grn_trn', $info2,"grn_trn_id=".$POST['grn_trn_id'][$k] , $dbcon);

					if($POST['grn_against'] == '2'){
						$trn_info2['status'] =2;
					$updateid_11=update_record('tbl_grn_sub_trn', $trn_info2,"grn_trn_id=".$POST['grn_trn_id'][$k], $dbcon);	

					$hhhh=grn_po_sub_trn($dbcon,$POST['grn_trn_id'][$k],$POST['purchaseordertrn_id'][$k]);

					if($grn_conv_unit==$rate_unit){
						$qty = $POST['prev_conv_grn_qty'][$k] - $grn_conv_qty;
	   				}else{
	   					$qty = $POST['prev_grn_qty'][$k] - $grn_base_qty;
	   				}

	   				if($qty > 0){
	   					$dbcon->query("update tbl_purchaseorder set used_status=0 where purchaseorder_id=".$POST['purchaseorder_id']);

						$dbcon->query("update tbl_purchaseordertrn set used_status=0 where purchaseordertrn_id=".$POST['purchaseordertrn_id'][$k]);
						update_po_delivery_date_on_delete_grn($dbcon,$POST['purchaseordertrn_id'][$k],$qty);
	   				}
				
				}
				// }

				
				if($POST['grn_against']==1){
					close_grn_to_process($dbcon,$POST['eid'],$POST['purchaseorder_id'],$info2['product_qty']);

				}

			}

			// $UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);

			if(!empty($_FILES['grn_file']['tmp_name'][0])) {
				$imgresp = upload_grn_receipt($_FILES,$dbcon,$POST['eid']);
			}

			if($updateid){	
				$arr['msg']="1";		
			//Insert LOG
				$log_entry=common_log_entry($dbcon,"grn_add",2,"tbl_grn",$POST['eid']);						
			}
			else{
				$arr['msg']="0";
			}
			$arr['back']=$POST['back'];
			echo json_encode($arr);	


		}
		else if(brp_strtolower($POST['mode']) == "fieldadd") {

			$info1['purchaseorder_id']	= $POST['purchaseorder_id'];
			$info1['pro_entry_date']	= date('Y-m-d',strtotime($POST['pro_entry_date']));
			$info1['pro_mfg_date']		= date('Y-m-d',strtotime($POST['pro_mfg_date']));
			$info1['pro_exp_date']		= date('Y-m-d',strtotime($POST['pro_exp_date']));
			$info1['product_id']		= $POST['product_id'];
			$info1['description']		= $_POST['product_des'];
			$info1['product_qty']		= $POST['product_qty'];
			$info1['unit_id']			= $POST['unit_id']; 
			$info1['branch_id']			= $POST['branch_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['product_rate']		= $POST['product_rate'];
			$info1['formulaid']			= $POST['formulaid'];
			$info1['product_amount']	= $total=($POST['product_rate']*$POST['product_qty']);
			$info1['product_qc']		= $POST['product_qc'];
		//$info=get_product_tax_common($dbcon,$total,$POST['formulaid']);
		//$info1=array_merge($info1,$info);
			$table='tbl_grn_trn';$tableid='grn_trn_id';
			if(!empty($POST['grn_id'])) {
				$info1['grn_id']= $POST['grn_id'];
			}
			else{
				$info1['grn_trn_status']= 3;
			}

			if(empty($POST['edit_id'])) {
				$inserid=add_record($table, $info1, $dbcon);
			}
			else {
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}

		//var_dump($info1);
		}
		else if(brp_strtolower($POST['mode'])== "load_grn_trn_data") {
			
			

			if($POST['grn_id']){
				$query="select mst.*,pro.product_name,cat.unit_name from tbl_grn_trn as mst
				left join product_mst as pro on pro.product_id=mst.product_id
				left join unit_mst as cat on cat.unitid=mst.unit_id  
				where grn_trn_status=0 and mst.grn_id=".$POST['grn_id'];
			}
			else{
				$query="select mst.*,pro.product_name,cat.unit_name from tbl_grn_trn as mst
				left join product_mst as pro on pro.product_id=mst.product_id
				left join unit_mst as cat on cat.unitid=mst.unit_id 
				where grn_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
			}
			$result=$dbcon->query($query);
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$pro_exp_date='';$pro_mfg_date='';
					if($rel['pro_mfg_date']!="1970-01-01" && $rel['pro_mfg_date']!="0000-00-00") {
						$pro_mfg_date=date('d-m-Y',strtotime($rel['pro_mfg_date']));
					}
					if($rel['pro_exp_date']!="1970-01-01" && $rel['pro_exp_date']!="0000-00-00") {
						$pro_exp_date=date('d-m-Y',strtotime($rel['pro_exp_date']));
					}
					echo '<tr> 
					<td style="vertical-align:top;">
					'.$rel['product_name'].'
					'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.nl2br($rel['description']):'').'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$pro_mfg_date.'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$pro_exp_date.'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$rel['product_qty'].'
					</td>
					<td style="vertical-align:top" class="text-center">
					'.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top"> 
					<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_grn_data('.$rel['grn_trn_id'].')"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn_data('.$rel['grn_trn_id'].','.$rel['purchaseorder_id'].')">X</button>
					</td>	
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
		}
		else if(brp_strtolower($POST['mode'])== "preedit") {
			$q = $dbcon -> query("SELECT mst.* FROM tbl_grn_trn as mst WHERE grn_trn_id = '$POST[grn_trn_id]'");
			$r = $q->fetch_assoc();
			$r['pro_entry_date'] = date('d-m-Y',strtotime($r['pro_entry_date']));
			if($r['pro_mfg_date']!="1970-01-01" && $r['pro_mfg_date']!="0000-00-00"){
				$r['pro_mfg_date'] = date('d-m-Y',strtotime($r['pro_mfg_date']));
			}
			else{
				$r['pro_mfg_date']='';
			}
			if($r['pro_exp_date']!="1970-01-01" && $r['pro_exp_date']!="0000-00-00"){
				$r['pro_exp_date'] = date('d-m-Y',strtotime($r['pro_exp_date']));
			}
			else{
				$r['pro_exp_date'] = '';
			}

		//$r['po_html_resp']=get_po_for_grn($dbcon,$r['purchaseorder_id'],'Edit');
			$r['pro_html_resp']=get_po_for_grn_trn($dbcon,$r['purchaseorder_id'],$r['product_id'],'Edit');

			echo json_encode($r);
		}

		else if(brp_strtolower($POST['mode'])== "delete_data") {
			$row=array();
			$info['grn_trn_status']=2;	
			$updateid=update_record('tbl_grn_trn', $info, "grn_trn_id=".$POST['grn_trn_id'], $dbcon);

		//$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);

			if($updateid){
					$query = "select purchaseordertrn_id,rate_unit,product_qty,product_conv_qty, product_conv_unit,unit_id, ref_type,returnable_id,returnable_trn_id,grn_id from tbl_grn_trn where grn_trn_id = " .$POST['grn_trn_id'];
					$result = $dbcon->query($query);

					$row=brp_mysqli_fetch_array($result);

					$upd_po_qry=$dbcon->query("update tbl_grn_sub_trn set status=2 where grn_trn_id=".$POST['grn_trn_id']);

					$upd_batch_qry=$dbcon->query("update tbl_batch_data set status=2 where grn_trn_id=".$POST['grn_trn_id'] . " and grn_id = ". $row['grn_id']);
				
				
				if(isset($POST['purchaseorder_id'])){				
						if($row['ref_type'] == '2') {
							$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_status=0 where purchaseorder_id=".$POST['purchaseorder_id']);
							if($row['purchaseordertrn_id'] !='0' || $row['purchaseordertrn_id'] !=''){
								$upd_po_qry=$dbcon->query("update tbl_purchaseordertrn set used_status=0 where purchaseordertrn_id=".$row['purchaseordertrn_id']);

								$qty = 0;

								if($row['rate_unit'] == $row['product_conv_unit']){
									$qty = $row['product_conv_qty'];
								}else if($row['rate_unit'] == $row['unit_id']){
									$qty = $row['product_qty'];
								}


								update_po_delivery_date_on_delete_grn($dbcon,$row['purchaseordertrn_id'],$qty);
									$upd_po_qry=$dbcon->query("update tbl_grn_sub_trn set status=2 where grn_trn_id=".$POST['grn_trn_id']);
							}
						}
						else if($row['ref_type'] == '6' || $row['ref_type'] == '4' || $row['ref_type'] == '1'  || $row['ref_type'] == '5'){
							
								
								

								if($row['ref_type'] == '6'){
									delete_reverse_entry_returnable_chalan_status($dbcon,$POST['grn_trn_id']);
								}
							
						}
		
				}

				$query1 = "select qc_id from tbl_qc where grn_trn_id = " .$POST['grn_trn_id'];
					$result1 = $dbcon->query($query1);

				while($row1 = brp_mysqli_fetch_assoc($result1)){
					$upd_qc_qry=$dbcon->query("update tbl_qc set qc_status=2 where grn_trn_id=".$POST['grn_trn_id']);
					$upd_qc_qry_trn=$dbcon->query("update tbl_qc_trn set qc_status=2 where qc_id=".$row1['qc_id']);
				}
			
				$row['res']="1";
			}
			else{
				$row['res']="0";
			}
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "delete_grn") {
			$row=array();
			$info['grn_status']=2;
			$updateid=update_record('tbl_grn', $info, "grn_id=".$POST['grn_id'] , $dbcon);

			$qry = "select ref_type from tbl_grn where grn_id = " .$POST['grn_id'];
			$res = $dbcon->query($qry);
			$grn_row=brp_mysqli_fetch_array($res);

			// $upd_po_sts=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);

		//Insert LOG
			$log_entry=common_log_entry($dbcon,"grn_delete",3,"tbl_grn",$POST['grn_id']);	

			if($updateid){

				$query = "select grn_trn_id from tbl_grn_trn where grn_trn_status = 0 and grn_id = " .$POST['grn_id'];
				$result = $dbcon->query($query);
				if($grn_row['ref_type'] == '2'){

					$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_status=0 where purchaseorder_id=".$POST['purchaseorder_id']);
				
				
				while($row=brp_mysqli_fetch_array($result)){
					$query1 = "select purchaseordertrn_id,rate_unit,product_qty,product_conv_qty,product_conv_unit,unit_id from tbl_grn_trn where grn_trn_id = " .$row['grn_trn_id'];
					$result1 = $dbcon->query($query1);
					$row1=brp_mysqli_fetch_array($result1);

					if(brp_mysqli_num_rows($result1) > 0){
						if($row1['purchaseordertrn_id'] !='0' || $row1['purchaseordertrn_id'] !=''){
							$upd_po_qry=$dbcon->query("update tbl_purchaseordertrn set used_status=0 where purchaseordertrn_id=".$row1['purchaseordertrn_id']);

							$qty = 0;

							if($row1['rate_unit'] == $row1['product_conv_unit']){
								$qty = $row1['product_conv_qty'];
							}else if($row1['rate_unit'] == $row1['unit_id']){
								$qty = $row1['product_qty'];
							}

							update_po_delivery_date_on_delete_grn($dbcon,$row1['purchaseordertrn_id'],$qty);
							delete_reverse_entry_po_chalan_status($dbcon,$row['grn_trn_id']);
						}
					}

					$upd_po_qry=$dbcon->query("update tbl_grn_sub_trn set status=2 where grn_trn_id=".$row['grn_trn_id']);

					$upd_batch_qry=$dbcon->query("update tbl_batch_data set status=2 where grn_trn_id=".$row['grn_trn_id'] . " and grn_id = ". $POST['grn_id']);

					$upd_po_qry=$dbcon->query("update tbl_grn_trn set grn_trn_status=2 where grn_id=".$row['grn_trn_id']);
				}

					
				}else if($grn_row['ref_type'] == '3'){
					$query = "select * from tbl_grn_trn where grn_trn_status = 0 and grn_id = " .$POST['grn_id'];
				$result = $dbcon->query($query);
					$grn_id = $POST['grn_id'];
					
					while($row=brp_mysqli_fetch_array($result)){
						
						$upd_po_qry=$dbcon->query("update tbl_grn_trn set grn_trn_status=2 where grn_id=".$row['grn_trn_id']);		

						$upd_po_qry=$dbcon->query("update tbl_grn_sub_trn set status=2 where grn_trn_id=".$row['grn_trn_id']);

						$upd_batch_qry=$dbcon->query("update tbl_batch_data set status=2 where grn_trn_id=".$row['grn_trn_id'] . " and grn_id = ". $POST['grn_id']);

						$trn_qry = "select * from tbl_grn_sub_trn where product_id = " . $row['product_id'] . " and grn_trn_id = " . $row['grn_trn_id'];
						$trn_result = $dbcon->query($trn_qry);

						

						while($row_trn=brp_mysqli_fetch_array($trn_result)){

							$dbcon->query("update tbl_job_work_sub_trn set grn_complete_status=0 where job_work_sub_trn_id=".$row_trn['job_work_sub_trn_id']);
							$dbcon->query("update tbl_job_work_trn set grn_complete_status=0 where job_work_trn_id=".$row_trn['job_work_trn_id']);
							$dbcon->query("update tbl_job_work set grn_complete_status=0 where job_work_id=".$row_trn['jobwork_id']);

							$prc_qry = "select * from tbl_allocate_process_trn where pt_product_id = " . $row_trn['product_id'] . " and grn_trn_sub_id = " . $row_trn['grn_trn_sub_id'];
							$prc_result = $dbcon->query($prc_qry);

							// echo $prc_qry;

							if(brp_mysqli_num_rows($prc_result) > 0){
								$row_prc=brp_mysqli_fetch_array($prc_result);

								$qry_1 = "update tbl_allocate_process_trn set p_status=2 where pt_id = ".$row_prc['pt_id'];
								$dbcon->query($qry_1);

								// echo $qry_1;

								$qry_2 = "update tbl_allocate_process_trn set pt_used_qty=(pt_used_qty - ".$row_prc['pt_qty'].") where pt_id = ".$row_prc['parent_pt_id'];

								$dbcon->query($qry_2);

								// echo $qry_2;

								$qry_3 = "update tbl_allocate_process set p_status = 1, pen_qty=(pen_qty + ".$row_prc['pt_qty'].") where p_id = ".$row_prc['pt_alloc_id'];

								$dbcon->query($qry_3);
								// echo $qry_3;
								$qry_4 = "update tbl_allocate_process_material set status = 2 where grn_trn_sub_id = ".$row_prc['grn_trn_sub_id'];

								$dbcon->query($qry_4);

								// echo $qry_4;

								$process=p_id_wise_find_previous_and_next_process($dbcon,$row_prc['pt_alloc_id']);
								$process_pr=json_decode($process);

								$previous_process_pid=$process_pr->previous_process_pid;

								if($previous_process_pid == '0'){	
									$qry_5 = "update tbl_reserve_stock set stock_status = 2 where stock_flage = 2 and p_id = ".$row_prc['pt_alloc_id']." and grn_trn_sub_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_5);

									// echo $qry_5;

									$qry_6 = "update tbl_stock_trn set stock_status = 2 where stock_flage = 2 and ref_name = 'Grn_sub_trn' and ref_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_6);
									// echo $qry_6;
								}else{
									$qry_5 = "update tbl_process_reserve_stock set stock_status = 2 where stock_flage = 2 and ref_name = 'Grn_sub_trn' and p_id = ".$row_prc['pt_alloc_id']." and ref_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_5);

									// echo $qry_5;

									$qry_6 = "update tbl_process_stock_trn set stock_status = 2 where stock_flage = 2 and ref_name = 'Grn_sub_trn' and ref_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_6);
								}

							}
							

						}

					}
				}else if($grn_row['ref_type'] == '1'){
					$query = "select * from tbl_grn_trn where grn_trn_status = 0 and grn_id = " .$POST['grn_id'];
				$result = $dbcon->query($query);
					$grn_id = $POST['grn_id'];
					
					while($row=brp_mysqli_fetch_array($result)){
						
						$upd_po_qry=$dbcon->query("update tbl_grn_trn set grn_trn_status=2 where grn_id=".$row['grn_trn_id']);		

						$upd_po_qry=$dbcon->query("update tbl_grn_sub_trn set status=2 where grn_trn_id=".$row['grn_trn_id']);

						$upd_batch_qry=$dbcon->query("update tbl_batch_data set status=2 where grn_trn_id=".$row['grn_trn_id'] . " and grn_id = ". $POST['grn_id']);

						$trn_qry = "select * from tbl_grn_sub_trn where product_id = " . $row['product_id'] . " and grn_trn_id = " . $row['grn_trn_id'];
						$trn_result = $dbcon->query($trn_qry);

						

						while($row_trn=brp_mysqli_fetch_array($trn_result)){

							$dbcon->query("update tbl_job_work_sub_trn set grn_complete_status=0 where job_work_sub_trn_id=".$row_trn['job_work_sub_trn_id']);
							$dbcon->query("update tbl_job_work_trn set grn_complete_status=0 where job_work_trn_id=".$row_trn['job_work_trn_id']);
							$dbcon->query("update tbl_job_work set grn_complete_status=0 where job_work_id=".$row_trn['jobwork_id']);

							$prc_qry = "select * from tbl_allocate_process_trn where pt_product_id = " . $row_trn['product_id'] . " and grn_trn_sub_id = " . $row_trn['grn_trn_sub_id'];
							$prc_result = $dbcon->query($prc_qry);

							// echo $prc_qry;

							if(brp_mysqli_num_rows($prc_result) > 0){
								$row_prc=brp_mysqli_fetch_array($prc_result);

								$qry_1 = "update tbl_allocate_process_trn set p_status=2 where pt_id = ".$row_prc['pt_id'];
								$dbcon->query($qry_1);

								// echo $qry_1;

								$qry_2 = "update tbl_allocate_process_trn set pt_used_qty=(pt_used_qty - ".$row_prc['pt_qty'].") where pt_id = ".$row_prc['parent_pt_id'];

								$dbcon->query($qry_2);

								// echo $qry_2;

								$qry_3 = "update tbl_allocate_process set p_status = 1, pen_qty=(pen_qty + ".$row_prc['pt_qty'].") where p_id = ".$row_prc['pt_alloc_id'];

								$dbcon->query($qry_3);
								// echo $qry_3;
								$qry_4 = "update tbl_allocate_process_material set status = 2 where grn_trn_sub_id = ".$row_prc['grn_trn_sub_id'];

								$dbcon->query($qry_4);

								// echo $qry_4;

								$process=p_id_wise_find_previous_and_next_process($dbcon,$row_prc['pt_alloc_id']);
								$process_pr=json_decode($process);

								$previous_process_pid=$process_pr->previous_process_pid;

								if($previous_process_pid == '0'){	
									$qry_5 = "update tbl_reserve_stock set stock_status = 2 where stock_flage = 2 and p_id = ".$row_prc['pt_alloc_id']." and grn_trn_sub_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_5);

									// echo $qry_5;

									$qry_6 = "update tbl_stock_trn set stock_status = 2 where stock_flage = 2 and ref_name = 'Grn_sub_trn' and ref_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_6);
									// echo $qry_6;
								}else{
									$qry_5 = "update tbl_process_reserve_stock set stock_status = 2 where stock_flage = 2 and ref_name = 'Grn_sub_trn' and p_id = ".$row_prc['pt_alloc_id']." and ref_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_5);

									// echo $qry_5;

									$qry_6 = "update tbl_process_stock_trn set stock_status = 2 where stock_flage = 2 and ref_name = 'Grn_sub_trn' and ref_id = ".$row_prc['grn_trn_sub_id'];
									$dbcon->query($qry_6);
								}

							}
							

						}

					}
				}

				else if($grn_row['ref_type'] == '6' || $grn_row['ref_type'] == '4' || $grn_row['ref_type'] == '5'){
					while($row=brp_mysqli_fetch_array($result)){
						
						$upd_po_qry=$dbcon->query("update tbl_grn_sub_trn set status=2 where grn_trn_id=".$row['grn_trn_id']);

					$upd_batch_qry=$dbcon->query("update tbl_batch_data set status=2 where grn_trn_id=".$row['grn_trn_id'] . " and grn_id = ". $POST['grn_id']);

						if($grn_row['ref_type'] == '6'){
							delete_reverse_entry_returnable_chalan_status($dbcon,$row['grn_trn_id']);
						}

					}
					$upd_po_qry=$dbcon->query("update tbl_grn_trn set grn_trn_status=2 where grn_id=".$POST['grn_id']);
				}
				$row['res']="1";

			}
			else{
				$row['res']="0";
			}
			echo json_encode($row);
		} 
		else if(brp_strtolower($POST['mode'])== "load_purhcase_order_data") {

			$order_id = $POST['order_id'];

			if(is_array($order_id)){
				$id=implode(',',$order_id);
			}else{
				$id = $order_id;	
			}

			/*$id=implode(',',$POST['order_id']);
			var_dump($POST['order_id']);*/
			$grn_type=$POST['grn_type'];
			if(empty($POST['eid'])){
				if($grn_type==2)
				{
				//$resp['pro_html'] = get_po_details_for_grn_trn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['pro_html'] = purchase_order_product_for_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['request_id'] ='';
				}
				else if($grn_type==3){
					$resp['pro_html'] = purchase_order_product_for_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['request_id'] ='';	
				}
				else if($grn_type==5){
				
					$resp['pro_html'] = sales_order_product_for_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['request_id'] ='';	
				}
				else if($grn_type==4){
				
					$resp['pro_html'] = direct_order_product_for_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['request_id'] ='';	
				}
				else if($grn_type==6)
				{
					$resp['pro_html'] = returnable_chalan_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['request_id'] ='';	
				}else if($grn_type==7)
				{
					$resp['pro_html'] = stock_transfer_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
					$resp['request_id'] ='';	
				}
				else if($grn_type==8)
				{
					$resp['pro_html'] = reprocess_jobwork_product_for_pending_grn($dbcon,$POST['vender_id'],$POST['order_id']);
					$resp['request_id'] ='';	
				}
				else
				{
					$resp['pro_html'] = job_work_product_for_pending_grn($dbcon,$POST['vender_id'],$POST['order_id']);

				//$resp['pro_html'] = get_jobwork_details_for_grn_trn($dbcon,$id,'',$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['order_id']);
				//$resp['request_id'] = get_request_id_jobwork($dbcon,$id);
					$resp['request_id'] = "";
				}
			}else{
				$resp['pro_html'] = load_grn_edit_data($dbcon,$POST['eid'],$grn_type);
				$resp['request_id'] ='';	
			}

			$vendor_id=get_vender_id($dbcon,$id,$grn_type);
			$resp['vendor_id'] = $vendor_id;
			$resp['vendor_name'] = get_vender_name($dbcon,$vendor_id,$grn_type);

			echo json_encode($resp);
		}
		else if(brp_strtolower($POST['mode'])== "load_po_ven_wise") {
			$resp['pro_html'] = get_po_for_grn($dbcon,'',$POST['vender_id'],'Add');
			echo json_encode($resp);
		}
		else if(brp_strtolower($POST['mode'])== "load_productdetail") {
			$purchaseorder_id=$POST['purchaseorder_id'];
			$product_id=$POST['product_id'];
			$query="select trn.*,main_grn_qty from tbl_purchaseordertrn as trn
			left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status!=2 and chtrn.purchaseorder_id=".$purchaseorder_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
			where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$purchaseorder_id." and trn.product_id=".$product_id;
			$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
			$rel['pending_qty']=floatval($rel['product_qty'])-floatval($rel['main_grn_qty']);

			$product_qc=explode(",",get_pro_field($dbcon,$product_id,'product_setting_check'));
			if(in_array("product_qc",$product_qc))
			{
				$rel['product_qc']=0;
			}
			else
			{
				$rel['product_qc']=1;
			}

			echo json_encode($rel);
		}
		else if(brp_strtolower($POST['mode'])== "load_grn_no") {

			$grn_type = $POST['grn_type']; 
			$row=array();
			if($grn_type == '1'){
				$row['invoiceno'] = load_common_no($dbcon,OUTSIDE_GRN);
			}else if($grn_type == '3'){
				$row['invoiceno'] = load_common_no($dbcon,INHOUSE_GRN);
			}else if($grn_type == '4'){
				$row['invoiceno'] = load_common_no($dbcon,DIRECT_GRN);
			}else if($grn_type == '5'){
				$row['invoiceno'] = load_common_no($dbcon,OUT_SO_GRN);
			}else if($grn_type == '6'){
				$row['invoiceno'] = load_common_no($dbcon,RET_CHN_GRN);
			}else if($grn_type == '7'){
				$row['invoiceno'] = load_common_no($dbcon,STOCK_TRF_GRN);
			}else{
				$query1="select * from tbl_invoicetype where type_id='8' and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
				$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
				$id=$rows['taxinvoice_start'];
				$id=$id+1;
				if($rows['invoice_format']=='2') {
					$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
				}
				else if($rows['invoice_format']=='1') {
					$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
				}
				else if($rows['invoice_format']=='3'){
					$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
				}
				else{
					$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
				}
			}
						
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "delete_attch") {
			$row=array();
			$info['grn_attch_status']=2;	
			$updateid=update_record('tbl_grn_attch', $info, "grn_attch_id=".$POST['grn_attch_id'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "get_order_no") {

			$grn_type=$POST['grn_type'];
			$vender_id=$POST['vender_id'];

			if($grn_type==2)
			{
				$row=get_all_po_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id,'',$grn_type);
			}
			else if($grn_type==3)
			{
				$row=get_all_po_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id,'',$grn_type);
			}
			else if($grn_type==4)
			{
				$row=get_all_so_for_grn($dbcon,$vender_id,'',$grn_type);
			}
			else if($grn_type==5)
			{
				// $row=get_all_so_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id,'',$grn_type);
				$row=get_all_so_for_grn($dbcon,$vender_id,'',$grn_type);

			}
			else if($grn_type==6)
			{
				// $row=get_all_so_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id,'',$grn_type);
				$row=get_all_returnable_for_grn($dbcon,$vender_id,'',$grn_type);

			}
			else
			{
				$row=get_all_jobwork_for_grn($dbcon,$rel['purchaseorder_id'],"","");
			}
			echo $row;
		}
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			$row=array();
			if($POST["type"]=="1"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
				//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 4, ".", "");
				//$ret_qty=$ret_qty;
			//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "batch_model_open")
		{
			$order_no = $POST['grn_no'];	
			$product_id = $POST['product_id'];	
			$main_pending_qty = $POST['main_pending_qty'];			
			$diff_qty = $POST['diff_qty'];
			$batch_unit = $POST['batch_unit'];
			$is_diff_unit = $POST['is_diff_unit'];
			$diff_unit_id = $POST['diff_unit_id'];


			$total_qty = 0;

			$prd_qry = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id = " . $product_id;
			$prd_row = brp_mysqli_fetch_assoc($dbcon->query($prd_qry));


			$readonly_qty = "";
			
			/*$qry="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 3 and product_id=".$product_id." and order_no ='" . $order_no."'";
			$res=mysqli_fetch_assoc($dbcon->query($qry));

			$batch_qty = $res['qty'];
			
			$remaining_qty = $main_pending_qty - $batch_qty;

			$req_qty = $remaining_qty;
			if($remaining_qty > $POST['qty']){
				$req_qty = $POST['qty'];
			}
*/

			$whr = "";

			if(!empty($POST['purchaseordertrn_id']) && $POST['purchaseordertrn_id'] > 0){
				$whr = " and purchaseordertrn_id = " . $POST['purchaseordertrn_id'];
			}
			$qry1="select * from tbl_batch_data where status in (3,0) and product_id=".$product_id." and order_no ='" . $order_no."' and company_id=".$_SESSION['company_id'] ." and user_id = ".$_SESSION['user_id'] . $whr;
			

			$row=$dbcon->query($qry1);
			$cnt=brp_mysqli_num_rows($row);

			$query="select * from product_mst where product_id=".$product_id;
			$count = 1;
			if($cnt > 0){
				$count = $cnt+1;
			}
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			if(empty($POST['trn_id'])){
				
				$str = '<input type="hidden" name="count" id="count" value="'.$count.'" />
				
				<input type="hidden" name="enter_qty" id="enter_qty" value="'.$POST['qty'].'" />
				<input type="hidden" name="enter_diff_qty" id="enter_diff_qty" value="'.$POST['diff_qty'].'" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_batch_table">
				<tr id="field">			
				<th width="15%"  class="text-center" style="vertical-align:center;">Batch No</th>
				<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
				<th width="15%"  class="text-center;" style="vertical-align:center;">Mfg Date</th>
				<th width="15%"  class="text-center;" style="vertical-align:center;">Exp Date</th>';

				if($companyConfiguration['supplier_tc_no'] == '1'){
					$str.='<th width="15%"  class="text-center;" style="vertical-align:center;">Supplier T.C. No </th>';
				}

				$str.='<th width="5%"  class="text-center;" style="vertical-align:center;">Action</th>
				</tr>';
				echo $str;
						$batch_no = "";
						$data_id = "";
						$readonly = "";
						$batch_stock = "0";
						if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
							$batch_stock = "1";
							$batch_no = get_batch_no($dbcon,$product_id);
							$readonly = "readonly";
							$qry2="select * from tbl_invoicetype where status=0 and type_id=30 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			
							$row2=$dbcon->query($qry2);
							$res_batch = brp_mysqli_fetch_assoc($row2);

							echo '<input type="hidden" name="format_value" id="format_value" value="'.$res_batch['format_value'].'" />
							<input type="hidden" name="end_format_value" id="end_format_value" value="'.$res_batch['end_format_value'].'" />
							<input type="hidden" name="taxinvoice_start" id="taxinvoice_start" value="'.$res_batch['taxinvoice_start'].'" />

							';


							/*$data_id = 'data-batch-format_value="'.$res_batch['format_value'].'" data-batch-end_format_value="'.$res_batch['end_format_value'].'" data-batch-start_no="'.$res_batch['taxinvoice_start'].'" readonly';*/
						}
						echo '<input type="hidden" name="batch_stock" id="batch_stock" value="'.$batch_stock.'" />';
						$i=1;
				if($cnt > 0){
					
						while($result=brp_mysqli_fetch_assoc($row))
						{
							$total_qty = $total_qty + $result['batch_qty'];
							$status = "temp";
							if($result['status'] == '0') {
								$status = "";
							}

							$batch_diff_qty = 0;
							$batch_diff_unit_id = 0;

							if($is_diff_unit == '1'){
								if($batch_unit == $result['conv_unit']){
									$batch_diff_qty = $result['base_qty'];
									$batch_diff_unit_id = $result['base_unit'];
								}else{
									$batch_diff_qty = $result['conv_qty'];
									$batch_diff_unit_id = $result['conv_unit'];
								}
							}

							$diff_func_name = "";
							$func_name = ""; 
							
							if($is_diff_unit == '1'){
								if($batch_unit == $prd_row['product_conv_unit']){
									$diff_func_name = " onKeyUp='diff_batch_convert_qty(1,".$i.");'";
									$func_name = " onKeyUp='batch_convert_qty(2,".$i.");'";
								}else{
									$diff_func_name = "onKeyUp='diff_batch_convert_qty(2,".$i.");'";
									$func_name = " onKeyUp='batch_convert_qty(1,".$i.");'";
								}
							}

							echo '<tr id="field_'.$i.'">
							<td   class="text-center" style="vertical-align:center;">';
							if($companyConfiguration['batchno_as_grnno'] == '1'){
							echo $order_no.'/ <input type="text" class="form-control batch_no'.$status.'" id="batchno'.$i.'" name="batch_no[]" placeholder="Batch No" value="'.$result['batch_no'].'">';
							}else{
							echo '<input type="text" class="form-control batch_no'.$status.'" id="batchno'.$i.'" name="batch_no[]" placeholder="Batch No" value="'.$result['batch_no'].'">';
							}
							echo '</td>
							<td	 class="text-center;" style="vertical-align:center;">
							<div style="display:flex;">';

							if($is_diff_unit == '1'){
								echo '<input type="text" class="form-control diff_qty diff_batch_qty'.$status.'" id="diff_qty'.$i.'" name="diff_batch_qty[]"  placeholder="'.$diff_qty.'"  '.$diff_func_name.'  onchange="validate_batch_data();" value="'.$batch_diff_qty.'"/>
							<span style="color: green; margin-left:5px;">'.getunitname($dbcon, $batch_diff_unit_id).'</span>';
							}
							echo'<input type="text" class="form-control qty batch_qty'.$status.'" id="qty'.$i.'" name="batch_qty[]" '.$readonly_qty.' placeholder="'.$POST["qty"].'" '.$func_name.'  onchange="validate_batch_data();" value="'.$result['batch_qty'].'"/>
							<span style="color: green; margin-left:5px;">'.getunitname($dbcon, $result['batch_unit']).'</span>
							</td>
							<td   class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control default-date-picker valid mfg_date'.$status.'" id="mfgdate'.$i.'" name="mfg_date[]" placeholder="Mfg date" onchange="get_exp_date('.$i.');" value="'.date('d-m-Y',strtotime($result['mfg_date'])).'">
							</td>
							<td   class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control exp_date'.$status.'" id="expdate'.$i.'" name="exp_date[]" placeholder="Exp date" readonly value="'.date('d-m-Y',strtotime($result['exp_date'])).'">
							<input type="hidden" class="form-control batch_id'.$status.'" id="batch_id'.$i.'"  name="batch_id[]" readonly value="'.$result['batch_id'].'">
							</td>';
							if($companyConfiguration['supplier_tc_no'] == '1'){
								echo '<td  class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control supplier_tc_no'.$status.'" id="supplier_tc_no'.$i.'" name="supplier_tc_no[]" placeholder="supplier T.C. No"  value="'.$result['supplier_tc_no'].'">
							</td>';
								}
							// if($result['status'] == '0'){
							echo	'<td   class="text-center" style="vertical-align:center;">
							<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_batch_data('.$result['batch_id'].','.$i.')"><i class="fa fa-trash-o"></i></button>
							</td>';
							// }
							
							echo '</tr>';
							$i++;
						}

						if($is_diff_unit == '1'){
								if($batch_unit == $prd_row['product_conv_unit']){
									$diff_func_name = " onKeyUp='diff_batch_convert_qty(1,".$i.");'";
									$func_name = " onKeyUp='batch_convert_qty(2,".$i.");'";
								}else{
									$diff_func_name = "onKeyUp='diff_batch_convert_qty(2,".$i.");'";
									$func_name = " onKeyUp='batch_convert_qty(1,".$i.");'";
								}
							}

							if($POST['qty'] > $total_qty){
						
						echo '<tr id="field_'.$i.'">
								<td   class="text-center" style="vertical-align:center;">';
								if($companyConfiguration['batchno_as_grnno'] == '1'){
								echo $order_no.'/ <input type="text" class="form-control batch_no'.$status.'" id="batchno'.$i.'" name="batch_no[]" placeholder="Batch No" value="'.$batch_no.'">';
								}else{
								echo '<input type="text" class="form-control batch_no'.$status.'" id="batchno'.$i.'" name="batch_no[]" placeholder="Batch No" value="'.$result['batch_no'].'">';
								}
							echo '</td>
							<td	 class="text-center;" style="vertical-align:center;">
							<div style="display:flex;">';
							if($is_diff_unit == '1'){
								echo '<input type="text" class="form-control diff_qty diff_batch_qtytemp'.$status.'" id="diff_qty'.$i.'" name="diff_batch_qty[]" '.$diff_func_name.'  placeholder="'.$diff_qty.'"  onchange="validate_batch_data();" value=""/>
							<span  style="color: green; margin-left:5px;">'.getunitname($dbcon, $diff_unit_id).'</span>';
							}
							echo'<input '.$readonly_qty.' type="text" class="form-control qty batch_qtytemp" id="qty'.$i.'"  name="batch_qty[]" placeholder="'.$POST["qty"].'" '.$func_name.'  onchange="validate_batch_data();"/>
							<span style="color: green; margin-left:5px;">'.getunitname($dbcon, $result['batch_unit']).'</span>
							</td>
							<td   class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control default-date-picker valid mfg_datetemp" id="mfgdate'.$i.'" name="mfg_date[]" placeholder="Mfg date" onchange="get_exp_date('.$i.');" >
							</td>
							<td   class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control exp_datetemp" id="expdate'.$i.'" name="exp_date[]" placeholder="Exp date" readonly>
							</td>';
							if($i!=1){
							echo '<td><button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_batch_data('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button></td>';
						}
							if($companyConfiguration['supplier_tc_no'] == '1'){
								echo '<td  class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control supplier_tc_notemp'.$status.'" id="supplier_tc_no'.$i.'" name="supplier_tc_no[]" placeholder="supplier T.C. No"  value="'.$result['supplier_tc_no'].'">
							</td>';
								}
							'</tr>';
						}
				}else{
					if($is_diff_unit == '1'){
								if($batch_unit == $prd_row['product_conv_unit']){
									$diff_func_name = " onKeyUp='diff_batch_convert_qty(1,".$i.");'";
									$func_name = " onKeyUp='batch_convert_qty(2,".$i.");'";
								}else{
									$diff_func_name = "onKeyUp='diff_batch_convert_qty(2,".$i.");'";
									$func_name = " onKeyUp='batch_convert_qty(1,".$i.");'";
								}
							}



					echo '<tr id="field_'.$i.'">
						<td   class="text-center" style="vertical-align:center;">';
						if($companyConfiguration['batchno_as_grnno'] == '1'){
						echo $order_no.'/ <input type="text" class="form-control batch_notemp'.$status.'" id="batchno'.$i.'" name="batch_no[]" placeholder="Batch No" value="'.$batch_no.'">';
						}else{
						echo '<input type="text" class="form-control batch_notemp'.$status.'" id="batchno'.$i.'" name="batch_no[]" placeholder="Batch No" value="'.$result['batch_no'].'">';
						}
					echo '</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<div style="display:flex;">';
					if($is_diff_unit == '1'){
								echo '<input type="text" class="form-control diff_qty diff_batch_qtytemp'.$status.'" id="diff_qty'.$i.'" name="diff_batch_qty[]" '.$diff_func_name.'  placeholder="'.$diff_qty.'"  onchange="validate_batch_data();" value=""/>
							<span style="color: green; margin-left:5px;">'.getunitname($dbcon, $diff_unit_id).'</span>';
							}
					echo'
					<input type="text" '.$readonly_qty.' style="margin-left:30px;" class="form-control qty batch_qtytemp" '.$func_name.'  id="qty'.$i.'" name="batch_qty[]" placeholder="'.$POST["qty"].'"  onchange="validate_batch_data();"/>
					<span style="color: green; margin-left:10px;">'.getunitname($dbcon, $batch_unit).'</span>
					</div>
					</td>
					<td   class="text-center" style="vertical-align:center;">
					<input type="text" class="form-control default-date-picker valid mfg_datetemp" id="mfgdate1" name="mfg_date[]" placeholder="Mfg date" onchange="get_exp_date(1);" >
					</td>
					<td   class="text-center" style="vertical-align:center;">
					<input type="text" class="form-control exp_datetemp" id="expdate1" name="exp_date[]" placeholder="Exp date" readonly>
					</td>';

					if($companyConfiguration['supplier_tc_no'] == '1'){
						echo '<td  class="text-center" style="vertical-align:center;">
							<input type="text" class="form-control supplier_tc_notemp'.$status.'" id="supplier_tc_no'.$i.'" name="supplier_tc_no[]" placeholder="supplier T.C. No"  value="">
							</td>';
					}
					echo '</tr>';
				}

				echo '</table>';
			}else{
				$qry="SELECT * FROM `tbl_purchaseorder_delivery_date` WHERE po_delivery_date_status=0 and purchaseordertrn_id=".$POST['trn_id']." order by po_delivery_date_id";
				$row=$dbcon->query($qry);
				$cnt=brp_mysqli_num_rows($row);
				if($cnt>0){
					$i=1;
					echo '<input type="hidden" name="count" id="count" value="'.$cnt.'" />
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					<th width="30%"  class="text-center" style="vertical-align:center;">Date</th>
					<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>';
					
					while($tax=brp_mysqli_fetch_assoc($row))
					{
						$date=date('d-m-Y',strtotime($tax['delivery_date']));
						echo '<tr id="field'.$i.'">
											
						<td   class="text-center" style="vertical-align:center;">
						<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'.$i.'" name="delivery_date[]" placeholder="Delivery Date" value="'.$date.'" onkeyup="qty_wise_date_validation('.$i.');" >
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="text" class="form-control delivery_qty" id="delivery_qty'.$i.'" name="delivery_qty[]" placeholder="'.$tax["product_qty"].'" value="'.$tax["product_qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation('.$i.');" />
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="hidden" name="arry_sr[]" id="arry_sr'.$i.'" value="'.$i.'" />
						<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit'.$i.'" value="'.$tax["po_delivery_date_id"].'" />';
						if($i!=1){
							echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
						}
						echo '</td>
						</tr>';
						$i++;
					}
					echo '</table>';
				}else{
					echo '<input type="hidden" name="count" id="count" value="1" />
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
					<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>
					<tr id="field1">
					<td class="text-center" style="vertical-align:center;">
					<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
					</td>
					</tr>
					</table>';
				}
			}
		}
		else if(strtolower($POST['mode'])== "get_exp_date_by_product")
		{
			$product_id = $_POST['product_id'];
			$mfgdate =  $_POST['mfgdate'];
			$exp_date = get_exp_date_by_product($dbcon,$product_id,$mfgdate);

			echo $exp_date;
		}
		else if(strtolower($POST['mode']) == "save_batch_data") 
		{
			// echo "<pre>";print_r($POST);die;
			$batch_no_arr = $POST['batch_no_arr'];
			$batch_qty_arr = $POST['batch_qty_arr'];
			$diff_batch_qty_arr = $POST['diff_batch_qty_arr'];
			$mfg_date_arr = $POST['mfg_date_arr'];
			$exp_date_arr = $POST['exp_date_arr'];
			$batch_id_arr = $POST['batch_id_arr'];
			$supplier_tc_no_arr = $POST['supplier_tc_no_arr'];
			$purchaseordertrn_id  = $POST['purchaseordertrn_id'];

			$batch_no_arr_temp = $POST['batch_no_arr_temp'];
			$batch_qty_arr_temp = $POST['batch_qty_arr_temp'];
			$diff_batch_qty_arr_temp = $POST['diff_batch_qty_arr_temp'];
			$mfg_date_arr_temp = $POST['mfg_date_arr_temp'];
			$exp_date_arr_temp = $POST['exp_date_arr_temp'];
			$supplier_tc_no_arr_temp = $POST['supplier_tc_no_arr_temp'];

			$batch_unit_id = $POST['batch_unit_id'];
			
			$company_data = getCompanyConfiguration($dbcon, $id = false);

			/*Code Added Start:: Sanat :: 18-11-21 
			  Comment ::  Delete previouse added batch data	
			*/


			$where = "status = 3 and product_id=".$_POST['product_id']." and order_no ='" . $_POST['grn_no']."' and company_id = ".$_SESSION['company_id'] ." and user_id = ".$_SESSION['user_id'];

			if($purchaseordertrn_id > 0){
				$where .= " and purchaseordertrn_id = " . $purchaseordertrn_id;
			}
			
			delete_record('tbl_batch_data',$where,$dbcon);

			$prd_qry = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id = " . $_POST['product_id'];
			$prd_row = brp_mysqli_fetch_assoc($dbcon->query($prd_qry));

			/*Code Added End:: Sanat :: 18-11-21 */
			for($i=0; $i<count($POST['batch_id_arr']);$i++)
			{

				$query="select * from tbl_batch_data where batch_id=".$batch_id_arr[$i];
				$result1=$dbcon->query($query);
				$row=brp_mysqli_fetch_assoc($result1);

				if($row['qc_status'] == '1'){
					$upd_info['accept_qty']	= $batch_qty_arr[$i];
					$upd_info['qc_qty']	= $batch_qty_arr[$i];
				}

				if($row['base_unit'] == $row['conv_unit']){
					$upd_info['base_qty'] = $batch_qty_arr[$i];
					$upd_info['conv_qty'] = $batch_qty_arr[$i];
				}else if($row['batch_unit'] == $row['conv_unit']){
					$upd_info['conv_qty'] = $batch_qty_arr[$i];
					
					$type="conv_unit";
					if(isset($diff_batch_qty_arr) && !empty($diff_batch_qty_arr[$i])){
						$upd_info['base_qty']=$diff_batch_qty_arr[$i];
					}else{
						$upd_info['base_qty']=convert_stock($dbcon,$batch_qty_arr[$i],$row['product_id'],$type);
					}

				}else{
					$upd_info['base_qty'] = $batch_qty_arr[$i];
					$type="base_unit";
					if(isset($diff_batch_qty_arr) && !empty($diff_batch_qty_arr[$i])){
						$upd_info['conv_qty']=$diff_batch_qty_arr[$i];
					}else{
						$upd_info['conv_qty']=convert_stock($dbcon,$batch_qty_arr[$i],$row['product_id'],$type);
					}
				}
				if($company_data['batchno_as_grnno']==1){
					$upd_info['batch_no']			= $_POST['grn_no'].'/'.$batch_no_arr[$i];
				}else{
					$upd_info['batch_no']			= $batch_no_arr[$i];
				}


				$mfg_date = '';
				$exp_date = '';

				if(!empty($mfg_date_arr[$i])) {

					$mfg_date = date('Y-m-d',strtotime($mfg_date_arr[$i]));
					$exp_date = date('Y-m-d',strtotime($exp_date_arr[$i]));	
					
				}else{
					$mfg_date = date("Y-m-d");
					$dt = get_exp_date_by_product($dbcon,$_POST['product_id'],date("d-m-Y"));

					$exp_date = date('Y-m-d',strtotime($dt));	
				}


				$upd_info['batch_qty']			= $batch_qty_arr[$i];
				$upd_info['supplier_tc_no']			= @$supplier_tc_no_arr[$i];
				$upd_info['mfg_date']			= $mfg_date;
				$upd_info['exp_date']			= $exp_date;
				$upd_info['order_no']			= $_POST['grn_no'];
				$upd_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));

				$updateid=update_record('tbl_batch_data', $upd_info,"batch_id=".$batch_id_arr[$i] , $dbcon);
			}

			for($i=0; $i<count($POST['batch_no_arr_temp']);$i++)
			{
				/*if($company_data['batchno_as_grnno']==1){
					$batch_no = $_POST['grn_no'].'/'.$batch_no_arr_temp[$i];
				}else{*/
					$batch_no = $batch_no_arr_temp[$i];
				//}
				if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
					$batch_no = get_batch_no($dbcon,$_POST['product_id']);
				}


			$qc_paramter_info = check_product_qc_paramter($dbcon,$_POST['product_id'],"-1");
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$info['qc_status']	= 0;
			}else{
				$info['qc_status']	= 1;
				$info['accept_qty']			= $batch_qty_arr_temp[$i];
			}

			$mfg_date = '';
			$exp_date = '';

			if(!empty($mfg_date_arr_temp[$i])) {
				$mfg_date = date('Y-m-d',strtotime($mfg_date_arr_temp[$i]));
				$exp_date = date('Y-m-d',strtotime($exp_date_arr_temp[$i]));
			}else{
				$mfg_date = date("Y-m-d");
				$dt = get_exp_date_by_product($dbcon,$_POST['product_id'],date("d-m-Y"));
				$exp_date = date('Y-m-d',strtotime($dt));	
			}
			
		
				if($prd_row['product_base_unit'] != $prd_row['product_conv_unit']){
					if($batch_unit_id == $prd_row['product_conv_unit']){
						$info['conv_qty'] = $batch_qty_arr_temp[$i];

						$base_qty =  convert_stock($dbcon,$batch_qty_arr_temp[$i],$_POST['product_id'],"base_unit");

						if(isset($diff_batch_qty_arr_temp) && !empty($diff_batch_qty_arr_temp[$i])){
							$info['base_qty'] = $diff_batch_qty_arr_temp[$i];
						}else{
							$info['base_qty'] = $base_qty;
						}

						// $info['base_qty'] = $base_qty;
					}else{
						$info['base_qty'] = $batch_qty_arr_temp[$i];
						$conv_qty =  convert_stock($dbcon,$batch_qty_arr_temp[$i],$_POST['product_id'],"conv_unit");

						if(isset($diff_batch_qty_arr_temp) && !empty($diff_batch_qty_arr_temp[$i])){
							$info['conv_qty'] = $diff_batch_qty_arr_temp[$i];
						}else{
							$info['conv_qty'] = $conv_qty;
						}
						// $info['conv_qty'] = $conv_qty;
					}
				}else{
					$info['base_qty'] = $batch_qty_arr_temp[$i];
					$info['conv_qty'] = $batch_qty_arr_temp[$i];
				}
				
				$info['grn_id']				= 0;	
				$info['batch_no']			= $batch_no;
				$info['batch_qty']			= $batch_qty_arr_temp[$i];
				$info['mfg_date']			= $mfg_date;
				$info['exp_date']			= $exp_date;
				$info['supplier_tc_no']		= @$supplier_tc_no_arr_temp[$i];
				$info['order_no']			= $_POST['grn_no'];
				$info['product_id']			= $_POST['product_id'];
				$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
				$info['batch_type']			= $company_data['batch_type'];
				$info['batch_unit']			= $batch_unit_id;
				$info['base_unit']			= $prd_row['product_base_unit'];	
				$info['conv_unit']			= $prd_row['product_conv_unit'];
				$info['production_type']	= '1';			
				$info['status']				= '3';			
				$info['purchaseordertrn_id']= $purchaseordertrn_id;			
				$info['cdate']				= date("Y-m-d H:i:s"); 
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];	
				$info['branch_id']			= $_SESSION['branch_id'];


				if($batch_qty_arr_temp[$i] !="" && $batch_qty_arr_temp[$i] != '0'){
					$grn_id=add_record('tbl_batch_data', $info, $dbcon, $branch_id);		
				
					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1') {
						update_batch_no($dbcon,$_POST['product_id']);
					}		
				}		
				
			}
			// var_dump($info);	

			$arr['msg'] = 'true';
			$arr['batch_total_qty'] = get_batch_qty_using_grn_no($dbcon,$POST['grn_no']);
		echo json_encode($arr);
	}
	else if(brp_strtolower($POST['mode'])=="load_product")
		{			
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'','and p.product_type='.$type_id.'');
		}
		else if(brp_strtolower($POST['mode'])=="load_product_caegory")
		{			
			$pr_id=$POST['pr_id'];
			echo getrequiredproductcat($dbcon,$pr_id);
		}
		else if(brp_strtolower($POST['mode']) == "load_productdata") {
		
		$pid=$POST['eid'];
		
		$sel=$dbcon->query("select m.*,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from product_mst as m 
			left join unit_mst as bunit on bunit.unitid=m.product_base_unit
			left join unit_mst as cunit on cunit.unitid=m.product_conv_unit

		left join mst_material_spec as s on m.product_specification=s.ms_id where product_id='$pid'"); // s.m_type_density,
		$row=brp_mysqli_fetch_assoc($sel);
	

		/*
			Code By Umair: 31-05-2021
			Comment : Below Code is use for product specification dynamically
			START
		*/
			$html = '';	
			if($row['product_specification']!='' && $row['product_specification']!='0'){
				$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
				$rs_parameter=$dbcon->query($param_sql);	
				while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
					$parameter_name = ucfirst(brp_strtolower($rel_param['material_parameter_name']));	
					$parameter_id = 'product_'.$rel_param['material_parameter_id'];	

					$material_parameter_id = $rel_param['material_parameter_id'];

					$param_trn_sql = "select * from mst_material_spec_trn where material_parameter_id = '".$material_parameter_id."' and ms_id='".$row['product_specification']."' ";
					$rs_exec=$dbcon->query($param_trn_sql);	
					$rel_data=brp_mysqli_fetch_assoc($rs_exec);
					if($rel_data['material_parameter_value']){
						$html .= $parameter_name. ' : <input type="text" class="form-control get_ms_kg" name="'.$parameter_id.'" id="'.$parameter_id.'" value="'.$rel_data['material_parameter_value'].'" data-parameter="'.$material_parameter_id.'" data-msid="'.$row['product_specification'].'"  onkeyup="get_ms_kg();" />';
					}
				}
				if($html!=''){
					$html .= '<input type="hidden" name="msid" id="msid" value="'.$row['product_specification'].'">';
					$html .= '<input type="text" class="form-control" name="product_kg" id="product_kg" value="" readonly /> 
					<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET'; 
				}
			}
			$row['product_specification_code']=$html;
			/* END */
			
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode']) == "get_product_specification_cal") {
				$query="select * from mst_material_spec as trn where trn.ms_id=".$POST['msid'];
				$result1=$dbcon->query($query);
				$row=brp_mysqli_fetch_assoc($result1);

				$formula = $row['formula']; 
				$parameter_value = $POST['values'];

				$material_calculation = 0;
				$material_parameter_value = 0;
				foreach ($parameter_value as $key => $val){
					$material_parameter_id = str_replace('PRODUCT_', '', $val['name']);
					$material_parameter_value = floatval($val['value']);

					$p_query="select * from tbl_material_parameter as mp where mp.material_parameter_id=".$material_parameter_id;
					$p_result1=$dbcon->query($p_query);
					$p_row=brp_mysqli_fetch_assoc($p_result1);

					$material_parameter_code = $p_row['material_parameter_code'];

					$formula = str_replace($material_parameter_code, $material_parameter_value, $formula);
				}

				echo $material_calculation = do_maths($formula);


			}

			else if(brp_strtolower($POST['mode']) == "outside_so_fieldadd") {

			$info1['product_id']		= $POST['product_id'];
			$info1['unit_id']			= $POST['unit_id']; 
			$info1['product_qty']		= $POST['base_qty'];
			$info1['product_conv_unit']	= $POST['conv_unit_id'];
			$info1['product_conv_qty']	= $POST['conv_qty'];
			$info1['grn_godown']		= $POST['godown_id'];
			$info1['rate_unit']			= $POST['rate_unit'];
			
			if($POST['grn_against'] == '5'){
				$info1['sales_order_id']	= $POST['outside_so_id'];
				$info1['customer_id']		= $POST['customer_id'];

			}
			
			$qc_paramter_info = check_product_qc_paramter($dbcon,$POST['product_id']);

			if($qc_paramter_info=='1')
			{
				$info1['product_qc']= 0;		
			}else{
				$info1['product_qc'] =  1;		
			}
			
			$ref_type = $POST['grn_against'];
			if($ref_type == '2' ){
				$ref_type = 4;
				$info1['purchaseorder_id'] = $POST['purchaseorder_id'];
			}
			if($ref_type == '6'){

				$info1['returnable_id'] = $POST['purchaseorder_id'];
			}

			$info1['ref_type']			= $ref_type;
			$info1['branch_id']			= $POST['branch_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$table='tbl_grn_trn';$tableid='grn_trn_id';
			$info1['grn_trn_status']= 3;
			$inserid=add_record($table, $info1, $dbcon);

			if($inserid){

					$info2['product_id']		= $POST['product_id'];
					$info2['grn_trn_id']		= $inserid;
					$info2['unit_id']			= $POST['unit_id']; 
					$info2['product_conv_unit']	= $POST['conv_unit_id'];

					$info2['godown_id']			= $POST['godown_id'];
					$info2['rate_unit']			= $POST['rate_unit'];
					$info2['status']			= 3;
					
					$info2['branch_id']			= $POST['branch_id'];
					$info2['user_id']			= $_SESSION['user_id'];
					$info2['company_id']		= $_SESSION['company_id'];
					$info2['cdate']				= date("Y-m-d H:i:s");	

					$product_qty = $POST['base_qty'];
					$product_conv_qty = $POST['conv_qty'];

				if($ref_type == '6'){
					
						$query = "select id,item_qty as pending_qty, (select IFNULL(sum(product_qty),0) as qty from tbl_returnable_chalan_grn_trn where returnable_channal_item_id = tbl_returnable_channal_item.id) as used_qty from tbl_returnable_channal_item where status=0 and grn_status=0 and remove_from_grn= 1 and returnable_id=".$POST['purchaseorder_id'];

						$result=$dbcon->query($query);
						while($row=brp_mysqli_fetch_assoc($result)){

							$pending_qty  =  $row['pending_qty'] - $row['used_qty'];
							$used_qty=0;
							if($product_qty>0){
								if($pending_qty > 0){
									if($product_qty>=$pending_qty){
										$used_qty=$pending_qty;
										$product_qty  = $product_qty - $pending_qty;
									}else{
										$used_qty=$product_qty;
										$product_qty = $product_qty - $used_qty;
									}

									$cov_qty =  convert_stock($dbcon,$used_qty,$POST['product_id'],"conv_unit");
									$product_conv_qty = $product_conv_qty - $cov_qty;

									$info2['product_qty']		= $used_qty;
									$info2['product_conv_qty']	= $cov_qty;
									$info2['returnable_chalan_id']	= $POST['purchaseorder_id'];
									$info2['returnable_channal_item_id']	= $row['id'];

									$inserid=add_record('tbl_returnable_chalan_grn_trn', $info2, $dbcon);

									$sub_info2['product_id']			= $POST['product_id'];;
					   				$sub_info2['grn_trn_id']			= $info2['grn_trn_id'];
					   				$sub_info2['purchaseordertrn_id']	=  $POST['purchaseorder_id'];
					   				$sub_info2['returnable_trn_id']		= $row['id'];;
					   				$sub_info2['product_base_unit']		= $POST['unit_id']; 
					   				$sub_info2['product_conv_unit']		= $POST['conv_unit_id']; 
					   				
					   				$sub_info2['cdate']					= date("Y-m-d H:i:s");
					   				$sub_info2['user_id']				= $_SESSION['user_id'];
					   				$sub_info2['company_id']			= $_SESSION['company_id'];
					   				$sub_info2['branch_id']				=  $POST['branch_id'];

					   				$sub_info2['product_qty']			= $used_qty;
		   							$sub_info2['product_conv_qty']		= $cov_qty;

		   							$tbl_grn_trn_id=add_record('tbl_grn_sub_trn', $sub_info2, $dbcon);
								}								
							}
						}


						if($product_qty > 0){
							$info2['product_qty']		= $product_qty;
							$info2['product_conv_qty']	= $product_conv_qty;
							$info2['returnable_chalan_id']	= $POST['purchaseorder_id'];
							$info2['returnable_channal_item_id']	="" ;
							$inserid=add_record('tbl_returnable_chalan_grn_trn', $info2, $dbcon);

							$sub_info2['product_id']			= $POST['product_id'];;
			   				$sub_info2['grn_trn_id']			= $info2['grn_trn_id'];
			   				$sub_info2['purchaseordertrn_id']	=  $POST['purchaseorder_id'];
			   				$sub_info2['returnable_trn_id']		= "";
			   				$sub_info2['product_base_unit']		= $POST['unit_id']; 
			   				$sub_info2['product_conv_unit']		= $POST['conv_unit_id']; 
			   				
			   				$sub_info2['cdate']					= date("Y-m-d H:i:s");
			   				$sub_info2['user_id']				= $_SESSION['user_id'];
			   				$sub_info2['company_id']			= $_SESSION['company_id'];
			   				$sub_info2['branch_id']				=  $POST['branch_id'];

			   				$sub_info2['product_qty']			= $product_qty;
   							$sub_info2['product_conv_qty']		= $product_conv_qty;

   							$tbl_grn_trn_id=add_record('tbl_grn_sub_trn', $sub_info2, $dbcon);
						}
					
				}

				if($POST['grn_against'] == '2'){

				 $qry = "select purchaseordertrn_id, product_qty as pending_qty,(select IFNULL(sum(product_qty),0) as qty from tbl_po_item_agains_grn where purchaseordertrn_id = tbl_purchaseordertrn.purchaseordertrn_id) as used_qty from tbl_purchaseordertrn where purchaseordertrn_status = 0 and shortclose_status = 0 and remove_from_grn = 1 and used_status = 0 and purchaseorder_id = " . $POST['purchaseorder_id'];

					$result=$dbcon->query($qry);
					while($row=brp_mysqli_fetch_assoc($result)){
					$pending_qty  =  $row['pending_qty'] - $row['used_qty'];
							$used_qty=0;
							if($product_qty>0){
								if($pending_qty > 0){
									if($product_qty>=$pending_qty){
										$used_qty=$pending_qty;
										$product_qty  = $product_qty - $pending_qty;
									}else{
										$used_qty=$product_qty;
										$product_qty = $product_qty - $used_qty;
									}
									
									$cov_qty =  convert_stock($dbcon,$used_qty,$POST['product_id'],"conv_unit");
									$product_conv_qty = $product_conv_qty - $cov_qty;

									$info2['product_qty']		= $used_qty;
									$info2['product_conv_qty']	= $cov_qty;
									$info2['purchaseorder_id']	= $POST['purchaseorder_id'];
									$info2['purchaseordertrn_id']	= $row['purchaseordertrn_id'];
									
									$inserid=add_record('tbl_po_item_agains_grn', $info2, $dbcon);

									// $hhhh=grn_po_sub_trn($dbcon,$inserid,$row['purchaseordertrn_id']);

									$query_po_trn="select * from tbl_purchaseordertrn as po where purchaseordertrn_status=0 and purchaseordertrn_id = ".$row['purchaseordertrn_id'];
									   $rs_product=$dbcon->query($query_po_trn);


									  $row_trn=brp_mysqli_fetch_array($rs_product);

									$sub_info2['product_id']			= $row_trn['product_id'];;
					   				$sub_info2['grn_trn_id']			= $info2['grn_trn_id'];
					   				$sub_info2['purchaseordertrn_id']	=  $row['purchaseordertrn_id'];
					   				$sub_info2['product_base_unit']		= $POST['unit_id']; 
					   				$sub_info2['product_conv_unit']		= $POST['conv_unit_id']; 
					   				
					   				$sub_info2['cdate']					= date("Y-m-d H:i:s");
					   				$sub_info2['user_id']				= $_SESSION['user_id'];
					   				$sub_info2['company_id']			= $_SESSION['company_id'];
					   				$sub_info2['branch_id']				=  $POST['branch_id'];

					   				$sub_info2['product_qty']			= $used_qty;
		   							$sub_info2['product_conv_qty']		= $cov_qty;

		   							$tbl_grn_trn_id=add_record('tbl_grn_sub_trn', $sub_info2, $dbcon);

		   							$upd_info_trn['purchaseordertrn_id'] = $row['purchaseordertrn_id'];
		   							$updateid=update_record('tbl_grn_trn', $upd_info_trn,"grn_trn_id=".$info2['grn_trn_id'], $dbcon);

								}								
							}
						}

						if($product_qty > 0){
							$info2['product_qty']		= $product_qty;
							$info2['product_conv_qty']	= $product_conv_qty;
							$info2['purchaseorder_id']	= $POST['purchaseorder_id'];
							$info2['purchaseordertrn_id']	= "";
							// var_dump($info2);
							$inserid=add_record('tbl_po_item_agains_grn', $info2, $dbcon);

							$sub_info2['product_id']			= $POST['product_id'];;
			   				$sub_info2['grn_trn_id']			= $info2['grn_trn_id'];
			   				$sub_info2['purchaseordertrn_id']	=  "";
			   				$sub_info2['returnable_trn_id']		= "";
			   				$sub_info2['product_base_unit']		= $POST['unit_id']; 
			   				$sub_info2['product_conv_unit']		= $POST['conv_unit_id']; 
			   				
			   				$sub_info2['cdate']					= date("Y-m-d H:i:s");
			   				$sub_info2['user_id']				= $_SESSION['user_id'];
			   				$sub_info2['company_id']			= $_SESSION['company_id'];
			   				$sub_info2['branch_id']				=  $POST['branch_id'];

			   				$sub_info2['product_qty']			= $product_qty;
   							$sub_info2['product_conv_qty']		= $product_conv_qty;

   							$tbl_grn_trn_id=add_record('tbl_grn_sub_trn', $sub_info2, $dbcon);
						}
					
						}

						/*$info2['purchaseorder_id']	= $POST['purchaseorder_id'];
						$inserid=add_record('tbl_po_item_agains_grn', $info2, $dbcon);	*/

				
				echo '1';
			}else{
				echo '0';
			}

		}
		else if(strtolower($POST['mode'])== "load_product_unit")
		{
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['product_id'];
			
			$rs_type1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($rs_type1);

				if($row1['product_base_unit']!=$row1['product_conv_unit']){
        			$row1['unit_status']="1";
        			$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
        			$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
        		}else{
        			$row1['unit_status']="0";
        			$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
        		}

        		$row1['unit_option']=$opt;
				//$row1['qye']=$query1;

			echo json_encode($row1);
		}
		else if(strtolower($POST['mode'])== "delete_batch_data")
		{
			$batch_id = $POST['batch_id'];
			$grn_no = $POST['grn_no'];

			// $info['status'] = '2';

			// $where = "batch_id = ". $batch_id ." and company_id = ".$_SESSION['company_id'];
			
			$update_id=$dbcon->query("update tbl_batch_data set status=2 where batch_id = ". $batch_id ." and company_id = ".$_SESSION['company_id']);
			
			$arr['msg'] = 'true';
			$arr['batch_total_qty'] = get_batch_qty_using_grn_no($dbcon,$grn_no);
			echo json_encode($arr);

		}else if(brp_strtolower($POST['mode'])== "remove_returnable_chalan_data") {
			$id = $POST['returnable_channal_trn_id'];
			$upd_info['remove_from_grn'] = 1;
			$updateid=update_record('tbl_returnable_channal_item', $upd_info,"id=".$id , $dbcon);

			echo $updateid;
		}
		else if(strtolower($POST['mode'])== "po_short_close_for_grn"){

			$purchaseorder_id =   $POST['purchaseorder_id'];
			$purchaseordertrn_id =  $POST['purchaseordertrn_id'];


			/*$query = "select trn.*,po.purchaseorder_no,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_sub_trn as chtrn where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty,(select IFNULL(sum(product_conv_qty),0) as qty from tbl_grn_sub_trn as chtrn where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_conv_qty from tbl_purchaseordertrn as trn 
			left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id

			where trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status in (1,3) and po.company_id=".$_SESSION['company_id']." and trn.purchaseorder_id=".$POST['purchaseorder_id']." and trn.purchaseordertrn_id = ".$purchaseordertrn_id;


			$que_e = $dbcon->query($query);

			$row = mysqli_fetch_array($que_e);

			$due_qty = $row['product_qty']-$row['done_qty'];
			
			$info['short_close_qty'] 	= $due_qty;
			$info['short_close_reason'] = "short close from grn";
			$info['shortclose_status']	= 1;
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];	
			$info['company_id']			= $_SESSION['company_id'];
			$updateid=update_record("tbl_purchaseordertrn", $info, "purchaseordertrn_id=".$purchaseordertrn_id, $dbcon);

			$log_entry['po_no'] 			= $row['purchaseorder_no'];
			$log_entry['po_id'] 			= $row['purchaseorder_id'];
			$log_entry['po_trn_id'] 		= $row['purchaseordertrn_id'];
			$log_entry['product_id']		= $row['product_id'];
			$log_entry['short_close_qty']	= $due_qty;
			$log_entry['unit_id']			= $row['unit_id'];
			$log_entry['short_close_reason']= "short close from grn";
			$log_entry['date'] 				= date("Y-m-d");
			$log_entry['cdate'] 			= date("Y-m-d H:i:s");
			$log_entry['user_id']			= $_SESSION['user_id'];
			$log_entry['company_id']		= $_SESSION['company_id'];
			$log_entry['branch_id'] 		= $row['branch_id'];

			$inserid=add_record("tbl_log_po_short_close", $log_entry, $dbcon);

			if($inserid){
				$arr['msg'] == '1';
			}else{
				$arr['msg'] == '0';
			}*/

			$upd_info['remove_from_grn'] = 1;
			$updateid=update_record('tbl_purchaseordertrn', $upd_info,"purchaseordertrn_id=".$purchaseordertrn_id , $dbcon);

			if($updateid){
				echo '1';
			}else{
				echo '0';
			}
			

			// echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "load_attach_document") {
		    $appData = array();
		    $i=1;
		    $where='';
		    if($POST['grn_id']){
		        $where = ' and attach.grn_id='.$POST['grn_id'];
		    }
		    // if($branch_id){
		    //     $where .= check_branch('opportun',$branch_id);
		    // }
		    $aColumns = array('attach.grn_attch_id', 'attach.grn_id','attach.grn_file');
		    $sIndexColumn = "attach.grn_attch_id";
		    $isWhere = array("attach.grn_attch_status=0 and attach.company_id in (0,$_SESSION[company_id])".$where);
		    $sTable = "tbl_grn_attch as attach";            
		    $isJOIN = array('');
		    $hOrder = "attach.grn_attch_id desc";
			$having_clause='';
		    include('../../../include/pagging.php');
		    $appData = array();
		    $id=1;
		    foreach($sqlReturn as $row) {
		        $row_data = array();
		        $row_data[] = $row['sr']; 
		        $row_data[] = '<a href="'.ROOT.RECEIPT_FILE_VWING.$row['grn_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>'; 
		    
		        $appData[] = $row_data;
		        $id++;
		    }
		    $output['aaData'] = $appData;
		    echo json_encode( $output );
		}
		
		
		
		//pathik end	

		function upd_grn_used_status($dbcon,$purchaseorder_id,$flag){
			if($flag=='1'){
		//get Same Qty Data
				$get_dt_qry="SELECT SUM(potrn.product_qty) as po_qty,(SELECT SUM(grntrn.product_qty) FROM `tbl_grn_trn` as grntrn where grntrn.grn_trn_status=0 and grntrn.purchaseorder_id=".$purchaseorder_id." and grntrn.product_id=potrn.product_id) as grn_qty FROM `tbl_purchaseordertrn` as potrn where potrn.purchaseordertrn_status=0 and potrn.purchaseorder_id=".$purchaseorder_id." group by potrn.product_id";
				$get_dt_rs=$dbcon->query($get_dt_qry);
				$same_qty=true;
				while($get_dt_rel=brp_mysqli_fetch_assoc($get_dt_rs)){
			//compare pending qty
					if($get_dt_rel['po_qty']!=$get_dt_rel['grn_qty']){
						$same_qty=false;
					}
				}
			}

	//update PO if all used in GRN
			if($same_qty){
				$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_grn_status=1 where purchaseorder_id=".$purchaseorder_id);
			}
			else{
				$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_grn_status=0 where purchaseorder_id=".$purchaseorder_id);
			}
		}

		function upload_grn_receipt($FILES,$dbcon,$grn_id){
			$cnt=count($_FILES['grn_file']['name']);
			for( $i=0 ; $i < $cnt ; $i++ ) {
				if(!empty($_FILES['grn_file']['tmp_name'][$i])) {
					$rand=rand(0,999999);
					$temp = explode(".", $_FILES["grn_file"]["name"][$i]);
					$extension = brp_strtolower(end($temp));
					$file_name = $_FILES['grn_file']['name'][$i];
					$err = $_FILES["grn_file"]["tmp_name"][$i];
					$file_name = "grn_rec_".$rand.'.'.$extension;
					move_uploaded_file($err,RECEIPT_FILE_UPING.$file_name);

					$attch['grn_id']		= $grn_id;
					$attch['grn_file']		= $file_name;
					$attch['cdate']			= date("Y-m-d H:i:s"); 
					$attch['user_id']		= $_SESSION['user_id'];
					$attch['company_id']	= $_SESSION['company_id']; 
					$inserid=add_record('tbl_grn_attch', $attch, $dbcon);
			//return 	$file_name;
				}
			}
		}

		function get_product_tax($dbcon,$product_amount,$formulaid)
		{
			$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
			$row=$dbcon->query($qry);
			$rate_total=$total=$product_amount;
			$i=1;
			while($tax=brp_mysqli_fetch_assoc($row))
			{
				$info['tax_name'.$i]=$tax['tax_name'];
				$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
				$rate_total+=$tax_amount;
				$i++;
			}
			for($j=$i;$j<=3;$j++)
			{
				$info['tax_name'.$i]='';
				$info['tax_amount'.$i]='';		
			}
			$info['total']=$rate_total;
			return $info;
		}

	function update_returnable_chalan_status($dbcon,$returnable_id,$returnable_trn_id){

		$query = "select item_qty from tbl_returnable_channal_item where id = " . $returnable_trn_id;
		$result = $dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);

		if(brp_mysqli_num_rows($result)>0){
			$total_qty = $row['item_qty'];

			$query1 = "select IFNULL(sum(po.product_qty),0) as done_qty,IFNULL(sum(po.product_conv_qty),0) as conv_done_qty from tbl_grn_sub_trn as po 
				left join tbl_grn_trn as trn on trn.grn_trn_id = po.grn_trn_id 
				left join tbl_grn as grn on grn.grn_id = trn.grn_id 
				where grn.ref_type = 6 and po.status=0 and trn.returnable_trn_id in (".$returnable_trn_id.")";	
			
			$rs_product1 = $dbcon->query($query1);	
			
			$row1=brp_mysqli_fetch_array($rs_product1);
			

			if(number_format($row['item_qty'],5) == number_format($row1['done_qty'],5)){
				$info['grn_status'] = 1;

				$updateid=update_record('tbl_returnable_channal_item', $info,"id=".$returnable_trn_id , $dbcon);
			}

		}

		
		/*$qry4="SELECT count(id) as pending_grn from tbl_returnable_channal_item where status = 0 and grn_status = 0 and returnable_id = ".$returnable_id;

			$result4=$dbcon->query($qry4);
			$rel4=brp_mysqli_fetch_assoc($result4);

			$pending_grn = $rel4['pending_grn'];

			if($pending_grn == 0){
				$info1['grn_status'] = 1;

				$updateid=update_record('tbl_returnable_channal', $info1,"id=".$returnable_id , $dbcon);
			}*/

			$qry = "SELECT id,IFNULL(sum(item_qty),0) as total_qty,(SELECT IFNULL(sum(product_qty),0) as trn_qty from tbl_returnable_chalan_grn_trn where status = 0 and grn_status = 1 and returnable_chalan_id = ".$returnable_id." and returnable_channal_item_id = tbl_returnable_channal_item.id) as scrap_qty from tbl_returnable_channal_item where status = 0 and grn_status = 0 and returnable_id = ".$returnable_id." and remove_from_grn = 1";
			$result=$dbcon->query($qry);

			while ($row=brp_mysqli_fetch_assoc($result)) {
				$total_qty_trn = floatval($row['total_qty']);
				$scrap_qty_trn = floatval($row['scrap_qty']);	

				if($scrap_qty_trn>=$total_qty_trn){
					$info_1['grn_status'] = 1;
					$updateid=update_record('tbl_returnable_channal_item', $info_1,"id=".$row['id'] , $dbcon);
				}

			}


			$qry4="SELECT (select count(id) from tbl_returnable_channal_item where status = 0 and returnable_id = ".$returnable_id.") as total_req_chalan, (select count(id) from tbl_returnable_channal_item where status = 0 and grn_status=1 and  returnable_id = ".$returnable_id.") as total_grn_done";
			$result4=$dbcon->query($qry4);
			$rel4=brp_mysqli_fetch_assoc($result4);

			if($rel4['total_req_chalan'] == $rel4['total_grn_done']){
				$info1['grn_status'] = 1;
				$updateid=update_record('tbl_returnable_channal', $info1,"id=".$returnable_id , $dbcon);
				$updateid=update_record('tbl_returnable_channal_item', $info1,"returnable_id=".$returnable_id , $dbcon);
			}

	}

	function update_po_grn_status($dbcon,$purchaseorder_id,$purchaseordertrn_id){
		
		$query = "select product_qty,product_conv_qty,used_grn_qty,used_grn_conv_qty from tbl_purchaseordertrn where purchaseordertrn_id = " . $purchaseordertrn_id;
		$result = $dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);

		if(brp_mysqli_num_rows($result)>0){
			$total_qty = $row['product_qty'];

			if(number_format($row['used_grn_qty'],5) >= number_format($row['product_qty'],5)){
				$info['used_status'] = 1;

				$updateid=update_record('tbl_purchaseordertrn', $info,"purchaseordertrn_id=".$purchaseordertrn_id , $dbcon);
			}

		}

		$qry = "SELECT purchaseordertrn_id,IFNULL(sum(product_qty),0) as total_qty,(SELECT IFNULL(sum(product_qty),0) as trn_qty from tbl_po_item_agains_grn where purchaseordertrn_status = 0 and grn_status = 1 and purchaseorder_id = ".$purchaseorder_id." and purchaseordertrn_id = tbl_purchaseordertrn.purchaseordertrn_id) as scrap_qty from tbl_purchaseordertrn where purchaseordertrn_status = 0 and used_status = 0 and purchaseorder_id = ".$purchaseorder_id." and remove_from_grn = 1 group by purchaseordertrn_id";
			$result=$dbcon->query($qry);

			while ($row=brp_mysqli_fetch_assoc($result)) {
				$total_qty_trn = floatval($row['total_qty']);
				$scrap_qty_trn = floatval($row['scrap_qty']);	

				if($scrap_qty_trn>=$total_qty_trn){
					$info_1['used_status'] = 1;
					$updateid=update_record('tbl_purchaseordertrn', $info_1,"purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);
				}

			}
			
	}

	function delete_reverse_entry_returnable_chalan_status($dbcon,$grn_trn_id){

		$query = "select returnable_id,returnable_trn_id from tbl_grn_trn where grn_trn_id = " . $grn_trn_id;
		$result = $dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);

		if(brp_mysqli_num_rows($result)>0){
				$info['grn_status'] = 0;
				$updateid=update_record('tbl_returnable_channal_item', $info,"id=".$row['returnable_trn_id'] , $dbcon);

				$info1['grn_status'] = 0;
				$updateid=update_record('tbl_returnable_channal', $info1,"id=".$row['returnable_id'] , $dbcon);

				$info2['status'] = 2;
				$info2['grn_status'] = 0;

				$updateid=update_record('tbl_returnable_chalan_grn_trn', $info2,"grn_trn_id=".$grn_trn_id, $dbcon);
		}
	}


	function delete_reverse_entry_po_chalan_status($dbcon,$grn_trn_id){

		$query = "select purchaseorder_id,purchaseordertrn_id from tbl_grn_trn where grn_trn_id = " . $grn_trn_id;
		$result = $dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);

		if(brp_mysqli_num_rows($result)>0){
				$info['used_status'] = 0;
				$updateid=update_record('tbl_purchaseordertrn', $info,"purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);

				$info1['used_status'] = 0;
				$updateid=update_record('tbl_returnable_channal', $info1,"purchaseorder_id=".$row['purchaseorder_id'] , $dbcon);

				$info2['status'] = 2;
				$info2['grn_status'] = 0;

				$updateid=update_record('tbl_po_item_agains_grn', $info2,"grn_trn_id=".$grn_trn_id, $dbcon);
		}
	}


	function update_po_delivery_date_on_delete_grn($dbcon,$purchaseordertrn_id,$qty){

		// echo "qty : " . $qty;
		// echo "</br>";
		// echo "po_id : " . $purchaseordertrn_id;
	$query = "select po_delivery_date_id,used_qty,product_qty,delivery_date from tbl_purchaseorder_delivery_date where po_delivery_date_status=0 and grn_status in (0,1) and  purchaseordertrn_id=".$purchaseordertrn_id." order by delivery_date desc";
		$base_stock = $qty;
		$result=$dbcon->query($query);
		while($row=brp_mysqli_fetch_assoc($result)){
			$used_qty=0;
			if($base_stock>0){
				if($base_stock>=$row['used_qty']){
					//used $row['pending_qty']
					$used_qty=$row['used_qty'];
					
				}else{
					//$base_stock used
					$used_qty=$base_stock;
				}
				//return $used_qty;
				$base_stock=intval($base_stock)-intval($used_qty);

				$info2['used_qty'] = intval($row['used_qty'])-intval($used_qty);
				$info2['grn_status'] = 0;
				
				update_record('tbl_purchaseorder_delivery_date', $info2,"po_delivery_date_id=".$row['po_delivery_date_id'] , $dbcon);
			}
			
		}
	}

function update_po_agains_grn_qty($dbcon,$purchaseorder_id,$grn_trn_id){
	$query = "select * from tbl_po_item_agains_grn where status = 0 and purchaseorder_id = " . $purchaseorder_id . " and grn_trn_id = " .$grn_trn_id;
		$result=$dbcon->query($query);
		while($row=brp_mysqli_fetch_assoc($result)){
			if($row['purchaseordertrn_id'] > 0){
				$product_qty = $row['product_qty'];
				$product_conv_qty = $row['product_conv_qty'];

				$qry = "select * from tbl_purchaseordertrn where purchaseordertrn_id = " . $row['purchaseordertrn_id'];
				$res=$dbcon->query($qry);
				$rel = brp_mysqli_fetch_assoc($res);


				$used_qty  = $rel['used_qty'];
				$used_grn_qty = $rel['used_grn_qty'];
				$used_grn_conv_qty = $rel['used_grn_conv_qty'];


				$info['used_qty'] = $used_qty + $product_qty;
				$info['used_grn_qty'] = $used_grn_qty + $product_qty;
				$info['used_grn_conv_qty'] = $used_grn_qty + $product_conv_qty;

			}
		}
}


function update_stock_transfer_grn_status($dbcon,$stock_transfer_id,$stock_transfer_trn_id,$base_qty,$conv_qty){
	$query = "select product_id,base_qty,conv_qty,grn_base_qty,grn_conv_qty from tbl_stock_transfer_trn where stock_transfer_trn_id = " . $stock_transfer_trn_id;
	$result = $dbcon->query($query);
	$row=brp_mysqli_fetch_array($result);


	$base_stock = 0;
	$conv_stock = 0;
	if(brp_mysqli_num_rows($result)>0){

		$st_info['grn_base_qty'] = $row['grn_base_qty'] + $base_qty;
		$st_info['grn_conv_qty'] = $row['grn_conv_qty'] + $conv_qty;
		update_record('tbl_stock_transfer_trn', $st_info,"stock_transfer_trn_id=".$stock_transfer_trn_id, $dbcon);
		$total_qty = $row['base_qty'];

		if(number_format($st_info['grn_base_qty'],5) >= number_format($row['base_qty'],5)){
			$info['grn_status'] = 1;
			$updateid=update_record('tbl_stock_transfer_trn', $info,"stock_transfer_trn_id=".$stock_transfer_trn_id , $dbcon);
		}

		$qry_r = "select res.*, (select IFNULL(sum(base_stock),0) as used_stock from tbl_reserve_stock  where stock_flage = 2 and ref_name='stock_transfer_trn' and product_id =". $row['product_id']." and ref_id=".$stock_transfer_trn_id." and stock_id=res.stock_id) as used_base_stock,(select IFNULL(sum(convert_stock),0) as used_stock from tbl_reserve_stock where stock_flage = 2 and ref_name='stock_transfer_trn' and product_id =". $row['product_id']." and ref_id=".$stock_transfer_trn_id." and stock_id=res.stock_id) as used_convert_stock from tbl_reserve_stock as res where stock_flage = 1 and ref_name='stock_transfer_trn' and product_id = ". $row['product_id']." and ref_id=".$stock_transfer_trn_id;
		$res2 = $dbcon->query($qry_r);
		while($row1 = brp_mysqli_fetch_assoc($res2)){

			$pending_stock = $row1['base_stock'] - $row1['used_base_stock'];
			$pending_convert_stock = $row1['convert_stock'] - $row1['used_convert_stock'];

			if($pending_stock > 0 && $pending_convert_stock > 0){
				if($pending_stock >= $base_qty){
					$info_rese['base_stock']		= $base_qty;
					$info_rese['convert_stock']		= $conv_qty;		
				}else{
					$info_rese['base_stock']		= $pending_stock;
					$info_rese['convert_stock']		= $pending_convert_stock;

					$base_qty = $base_qty - $pending_stock;
					$conv_qty = $conv_qty - $pending_convert_stock;
				}
			
			$info_rese['reserve_date']		= date('Y-m-d');
			$info_rese['product_id']		= $row1['product_id'];
			$info_rese['godown_id']			= $row1['godown_id'];
			$info_rese['base_unit']			= $row1['base_unit'];
			$info_rese['convert_unit']		= $row1['convert_unit'];
			
			$info_rese['stock_flage']		= "2";
			$info_rese['request_id']		= "";
			$info_rese['ref_name']			= "stock_transfer_trn";
			$info_rese['ref_id']			= $row1['ref_id'];
			$info_rese['stock_id']			= $row1['stock_id'];
			$info_rese['branch_id']			= $row1['branch_id'];
			$info_rese['cdate']				= date("Y-m-d H:i:s");
			$info_rese['user_id']			= $_SESSION['user_id'];
			$info_rese['company_id']		= $_SESSION['company_id'];		
			
			// var_dump($info_rese);					
			$reserve_id=add_record('tbl_reserve_stock',$info_rese, $dbcon);

			$qry_stk = "select * from tbl_stock_trn where stock_id =".$row1['stock_id'];
			$res3 = $dbcon->query($qry_stk);
			$r3 = brp_mysqli_fetch_assoc($res3);

			// $upd_stock['used_base_stock'] = $r3['used_base_stock'] + $row1['base_stock'];
			// $upd_stock['used_convert_stock'] = $r3['used_convert_stock'] + $row1['convert_stock'];

			// $updateid=update_record('tbl_stock_trn', $upd_stock,"stock_id=".$row1['stock_id'], $dbcon);

			add_stock($dbcon,$row1['product_id'],$row1['base_unit'],$info_rese['reserve_date'],"stock_transfer_trn",$info_rese['ref_id'],$row1['godown_id'],$info_rese['base_stock'],"2",$r3['branch_id'],"","","",$r3['batch_id'],$r3['batch_no'],$r3['base_rate'],$r3['conv_rate']);	
			}
		}
		
	}
// echo "</br></br>";
	$query1 = "select count(stock_transfer_trn_id) as pending_grn from tbl_stock_transfer_trn where status = 0 and grn_status = 0 and stock_transfer_id = " . $stock_transfer_id;
	$result1 = $dbcon->query($query1);
	$row1=brp_mysqli_fetch_array($result1);	

	if($row1['pending_grn'] == '0'){
		$upd_info['grn_status'] = 1;
		$updateid=update_record('tbl_stock_transfer', $upd_info,"stock_transfer_id=".$stock_transfer_id , $dbcon);
	}
	
}

?>