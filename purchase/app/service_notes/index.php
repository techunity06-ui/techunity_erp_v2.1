<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_GRN_LIST_SLUG_VIEW,PRODUCTION_GRN_LIST_SLUG_CREATE,PRODUCTION_GRN_LIST_SLUG_UPDATE,PRODUCTION_GRN_LIST_SLUG_DELETE
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and grn.grn_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND grn.grn_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		$where.=" and grn.ref_type=".$POST['grn_against'];
		
		if($POST['grn_against']=="1"){
			$isJOIN_new = array();
			$aColumns_new=array('"" as pono');
		}else{
			$isJOIN_new = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=gtrn.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id');
			$aColumns_new=array('group_concat(DISTINCT po.purchaseorder_no) as pono');
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('grn.grn_id','grn.grn_no', 'grn.grn_date','grn.ref_type', 'cust.l_name', 'grn.grn_status','grn.cdate','grn.user_id','grn.purchaseorder_id');
		$aColumns=array_merge($aColumns,$aColumns_new);
		$sIndexColumn = "grn.grn_id";
		$isWhere = array("grn.grn_status = 0".$where);
		$sTable = "tbl_grn as grn";
		//$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vender_id','left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=gtrn.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id');
		$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vender_id');
		$isJOIN=array_merge($isJOIN,$isJOIN_new);
		$hOrder = "grn.grn_id desc";
		$hGroupby = array("grn.grn_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			if($row['ref_type']=='1'){ $ref_type="JOBWORK"; } else {  $ref_type="PO"; } 
			
			$row_data = array();
			$que_po12="select grn_id from tbl_grn_trn where product_qc=0 and grn_id=".$row['grn_id'];
			$resi_grn12=$dbcon->query($que_po12);
			$re12=mysqli_fetch_assoc($resi_grn12);
			
			if(in_array(PRODUCTION_GRN_LIST_SLUG_UPDATE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$row["grn_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$row["pono"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$ref_type.'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$row["l_name"].'</a>';
				}else{
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["grn_no"].'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["pono"].'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$ref_type.'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["l_name"].'</a> ';
				}
			}else{
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["grn_no"].'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["pono"].'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$ref_type.'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["l_name"].'</a> ';
			}
			

			$edit_btn=''; $delete_btn=''; $view='';

			if(in_array(PRODUCTION_GRN_LIST_SLUG_UPDATE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
			}
			if(in_array(PRODUCTION_GRN_LIST_SLUG_DELETE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn('.$row['grn_id'].','.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
			}
			if(in_array(PRODUCTION_GRN_LIST_SLUG_VIEW,$bulkAccessArray)){
				$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'"><i class="fa fa-eye"></i></a> ';
			}  
			
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") 
	{
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='33' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			$info['service_no']				= $POST['service_no'];
			$info['service_date']			= date('Y-m-d',strtotime($POST['service_date']));
			$info['vender_id']			= $POST['vender_id'];
			
			$info['invoice_no']			= $POST['invoice_no'];
			
			$info['purchaseorder_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$service_id=add_record('tbl_service_notes', $info, $dbcon, $branch_id);
			
			if($service_id){
				$grn_qty=$POST['grn_qty'];
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){

								$info2s['purchaseordertrn_id']	=$POST['purchaseordertrn_id'][$k];
								$info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['service_id']				=$service_id;
								$info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								$info2s['unit_id']				=$POST['unit_id'][$k];
								$info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								$info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								
					
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
								
						//var_dump($info2);
								$tbl_grn_trn_id=add_record('tbl_service_notes_trn', $info2s, $dbcon, $branch_id);

								$ptrn=$info2s['purchaseordertrn_id'];
								// $hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);
						
								}
							
						}
					}
				}		
			//$updatetrnid=update_record('tbl_grn_trn', $infotrn,"grn_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			
			/*Update Data in Trn Table End*/
			$UPD_PO=upd_service_used_status($dbcon, $POST['purchaseorder_id'], 1);
		
		if(!empty($_FILES['grn_file']['tmp_name'][0])) {
			$imgresp = upload_grn_receipt($_FILES,$dbcon,$grn_id);
		}
		if($service_id){	
			$arr['msg']="1";	
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"service_add",1,"tbl_service_notes",$inserpoid);						
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		
		echo json_encode($arr);	
	}
	
		else if(strtolower($POST['mode']) == "edit") {

			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['gir_no']				= $POST['gir_no'];
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['remark']				= $_POST['remark']; 

			$info['cdate']			= date("Y-m-d H:i:s"); 
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id']; 
			$updateid=update_record('tbl_grn', $info,"grn_id=".$POST['eid'] , $dbcon);

			$grn_qty=$POST['grn_qty'];
			
			for($k=0;$k<count($grn_qty);$k++)
			{
				$loop_id=$grn_qty[$k];
				$qc_st=$POST['qc_status'][$k];
				
				if(strtolower($POST['qc_type'][$k])=="no"){
					$godown_id=$POST['grn_godown'][$k];
					$product_qc=1;
				}else{
					$godown_id="";
					$product_qc=0;
				}
				
				$info2['product_qty']		=$POST['grn_qty'][$k];
				$info2['unit_id']			=$POST['unit_id'][$k];
				$info2['grn_godown']		=$godown_id;
				//$info2['product_qc']		=$product_qc;
				
				$info2['cdate']				= date("Y-m-d H:i:s");
				$info2['user_id']			= $_SESSION['user_id'];
				$info2['company_id']		= $_SESSION['company_id'];
				//var_dump($info2);
				//$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2, $dbcon);
				if($qc_st!=1){
					$updateid1=update_record('tbl_grn_trn', $info2,"grn_trn_id=".$POST['grn_trn_id'][$k] , $dbcon);
				}
				if($POST['grn_against']==1){
					close_grn_to_process($dbcon,$POST['eid'],$POST['purchaseorder_id'],$info2['product_qty']);
				}

			}

			$UPD_PO=upd_service_used_status($dbcon, $POST['purchaseorder_id'], 1);

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
		else if(strtolower($POST['mode']) == "fieldadd") {

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
		else if(strtolower($POST['mode'])== "load_grn_trn_data") {

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
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
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
		else if(strtolower($POST['mode'])== "preedit") {
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
		else if(strtolower($POST['mode'])== "delete_data") {
			$row=array();
			$info['grn_trn_status']=2;	
			$updateid=update_record('tbl_grn_trn', $info, "grn_trn_id=".$POST['grn_trn_id'] , $dbcon);

		//$UPD_PO=upd_service_used_status($dbcon, $POST['purchaseorder_id'], 2);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_grn") {
			$row=array();
			$info['grn_status']=2;
			$updateid=update_record('tbl_grn', $info, "grn_id=".$POST['grn_id'] , $dbcon);

			$upd_po_sts=upd_service_used_status($dbcon, $POST['purchaseorder_id'], 2);

		//Insert LOG
			$log_entry=common_log_entry($dbcon,"grn_add",3,"tbl_grn",$POST['grn_id']);	

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		} 
		else if(strtolower($POST['mode'])== "load_purhcase_order_data") {

			$id=$POST['order_id'];
			
				$resp['pro_html'] = purchase_order_product_for_pending_service($dbcon,$id,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
				$resp['request_id'] ='';
			
			$vendor_id=get_vender_id($dbcon,$id,$grn_type);
			$resp['vendor_id'] = $vendor_id;
			$resp['vendor_name'] = get_vender_name($dbcon,$vendor_id,$grn_type);

			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_po_ven_wise") {
			$resp['pro_html'] = get_po_for_grn($dbcon,'',$POST['vender_id'],'Add');
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_productdetail") {
			$purchaseorder_id=$POST['purchaseorder_id'];
			$product_id=$POST['product_id'];
			$query="select trn.*,main_grn_qty from tbl_purchaseordertrn as trn
			left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status!=2 and chtrn.purchaseorder_id=".$purchaseorder_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
			where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$purchaseorder_id." and trn.product_id=".$product_id;
			$rel=mysqli_fetch_assoc($dbcon->query($query));
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
		else if(strtolower($POST['mode'])== "load_service_no") {
			$row=array();
			$query1="select * from tbl_invoicetype where type_id='33' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
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
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_attch") {
			$row=array();
			$info['grn_attch_status']=2;	
			$updateid=update_record('tbl_grn_attch', $info, "grn_attch_id=".$POST['grn_attch_id'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_order_no") {

			$grn_type=$POST['grn_type'];
			$vender_id=$POST['vender_id'];

			if($grn_type==2)
			{
				$row=get_all_po_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id);
			}
			else
			{
				$row=get_all_jobwork_for_grn($dbcon,$rel['purchaseorder_id']);
			}
			echo $row;
		}
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			$row=array();
			if($POST["type"]=="1"){

				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="conv_unit";
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
		
		
			//pathik end	

		function upd_service_used_status($dbcon,$purchaseorder_id,$flag){
			if($flag=='1'){
		//get Same Qty Data
				$get_dt_qry="SELECT SUM(potrn.product_qty) as po_qty,(SELECT SUM(grntrn.product_qty) FROM `tbl_grn_trn` as grntrn where grntrn.grn_trn_status=0 and grntrn.purchaseorder_id=".$purchaseorder_id." and grntrn.product_id=potrn.product_id) as grn_qty FROM `tbl_purchaseordertrn` as potrn where potrn.purchaseordertrn_status=0 and potrn.purchaseorder_id=".$purchaseorder_id." group by potrn.product_id";
				$get_dt_rs=$dbcon->query($get_dt_qry);
				$same_qty=true;
				while($get_dt_rel=mysqli_fetch_assoc($get_dt_rs)){
			//compare pending qty
					if($get_dt_rel['po_qty']!=$get_dt_rel['grn_qty']){
						$same_qty=false;
					}
				}
			}

	//update PO if all used in GRN
			if($same_qty){
				$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_status=1 where purchaseorder_id=".$purchaseorder_id);
				$upd_po_qry=$dbcon->query("update tbl_purchaseordertrn set used_status=1 where purchaseorder_id=".$purchaseorder_id);
			}
			else{
				$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_status=0 where purchaseorder_id=".$purchaseorder_id);
				$upd_po_qry=$dbcon->query("update tbl_purchaseordertrn set used_status=1 where purchaseorder_id=".$purchaseorder_id);
			}
		}

		function upload_grn_receipt($FILES,$dbcon,$grn_id){
			$cnt=count($_FILES['grn_file']['name']);
			for( $i=0 ; $i < $cnt ; $i++ ) {
				if(!empty($_FILES['grn_file']['tmp_name'][$i])) {
					$rand=rand(0,999999);
					$temp = explode(".", $_FILES["grn_file"]["name"][$i]);
					$extension = strtolower(end($temp));
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
			while($tax=mysqli_fetch_assoc($row))
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

	function purchase_order_product_for_pending_service($dbcon,$id,$mode,$eid,$vender_id,$branch_id)
	{
		$str='';
		if(!empty($eid)){
			$grn_ids=" and grn_id!=".$eid;
		}
		if(!empty($vender_id)){
			$ven=" and op.vender_id=".$vender_id;
		}
		if(!empty($id)){
			$po=" and po.purchaseorder_id=".$id;
		}
		$branch_where=" and po.branch_id=".$branch_id;
		//$branch_where=" and branch_id=".$branch_id;
		$query="select po.*,sum(po.product_qty)as produ_qty,sum(po.product_conv_qty)as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as trn_id,group_concat(po.po_ref_id ORDER BY po.po_ref_id DESC) as ref_id,con_unit.unit_name as conv_unit_name,op.po_type from tbl_purchaseordertrn as po 
		left join product_mst as p on p.product_id=po.product_id
		left join tbl_category as tc on p.product_category=tc.cat_id 
		left join unit_mst as unit on unit.unitid=po.unit_id
		left join unit_mst as con_unit on con_unit.unitid=po.conv_unit_id
		left join tbl_purchaseorder as op on op.purchaseorder_id=po.purchaseorder_id
		where op.po_approval_status=1 and po.used_status=0 and purchaseordertrn_status=0 ".$branch_where." ".$ven." ".$po." group by po.product_id,po.unit_id,po.conv_unit_id";
		$rs_product=$dbcon->query($query);
		
		
		$cnt=1;
		while($row=brp_mysqli_fetch_array($rs_product))
		{
			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$query1="select sum(product_qty) as done_qty,sum(product_conv_qty) as conv_done_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id in (".$row['trn_id'].")";
			$rs_product1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_array($rs_product1);
			
			$pending_qty=$row['produ_qty']-$row1['done_qty'];
			$pending_conv_qty=$row['produ_con_qty']-$row1['conv_done_qty'];

			
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],"-1");
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}

			if(!empty($eid)){
				$query11="select * from tbl_grn_trn as mst
				where mst.grn_id=".$eid." and product_id=".$row['product_id']." and purchaseorder_id=".$row['purchaseorder_id'];
				$rol=mysqli_fetch_assoc($dbcon->query($query11));
				
				if($rol['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}
			}
			$tolerance=get_pro_field($dbcon,$row['product_id'],'tolerance');
			$maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
			$minimum_tolerance=get_pro_field($dbcon,$row['product_id'],'minimum_tolerance');
			if($tolerance=="1"){
				// $maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
				$pending_qty1=$pending_qty;
			}else{
				$pending_qty1=$pending_qty;
			}
			/* Code By Umair: 29/10/2020 
			   Comment: I have removed the max value from the input tag for tolerance functionality for grn module.	
			   ".$pending_qty1."
			*/
			$display_none = '';
			if($row['po_type']=='1'){ 
				$display_none .= "display:none"; 
			}
			else{ 
				$display_none.= "display:block";
			}
			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
						<td>".$row['product_name']."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
						<td>".$cat_name."</td>
						<td>
							<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".number_format($row['produ_con_qty'],4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";
							 if($row["unit_id"]!=$row["conv_unit_id"]){ 
							
								$str.="</br>
									<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
								".number_format($row['produ_qty'],4,".","")." </br> <span>".$row['unit_name']."</span> 
								</div>";
							 } 
						$str.="</td>
						<td>
							
							<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".number_format($pending_conv_qty,4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";
							
							if($row["unit_id"]!=$row["conv_unit_id"]){
							
								$str.="</br>
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
									".number_format($pending_qty,4,".","")." </br> ".$row['unit_name']." 
								</div>";
							}
						$str.="<td>							
							<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".$pending_qty1."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['conv_unit_name']."						
							
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
						
						if($row["unit_id"]!=$row["conv_unit_id"]){
							$str.="<br/>
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input type='number' class='form-control'  name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['unit_name']."
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						}else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";*/
							
							$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
						}
							
						$str.="</td>
						<!--<td>
							<input type='number' min='0' max='' data-pendingqty='".$pending_qty1."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
							<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
							$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
							$str.="</select>
							<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
							<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
							<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
							<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
							
						</td>
						<td>
							<button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button>
						</td>
					</tr>";
			
			$cnt++;	
		}
		
		return $str;
	}
	?>