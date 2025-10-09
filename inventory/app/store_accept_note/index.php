<?php

session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    PRODUCTION_GIR_LIST_SLUG_CREATE,PRODUCTION_GIR_LIST_SLUG_UPDATE,PRODUCTION_GIR_LIST_SLUG_DELETE
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and gir.cdate>='".date('Y-m-d',strtotime($s_date[0]))."' AND gir.cdate<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		if(!empty($POST['gir_type']) && !empty($POST['gir_bill_type']) )
		{
			$where.=" and gir.gir_type='".$POST['gir_type']."' AND gir.gir_bill_type='".$POST['gir_bill_type']."'";
		}
			
		$appData = array();
		$i=1;
		$aColumns = array('gir.pro_gir_id', 'gir.gir_type','gir.gir_bill_type','gir.gir_no','gir.vender_id','gir.gir_chalan_no','cust.l_name','gir.gir_status','gir.cdate','gir.user_id','gir.company_id');
		//$aColumns=array_merge($aColumns,$aColumns_new);
		$sIndexColumn = "gir.pro_gir_id";
		$isWhere = array("gir.gir_status = 0".$where);
		$sTable = "pro_gir as gir";
		
		$isJOIN = array('left join tbl_ledger as cust on cust.l_id=gir.vender_id');
		$hOrder = "gir.pro_gir_id desc";
		$hGroupby = array("gir.pro_gir_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			if( $row['gir_type'] == "I"){ $gir_type = "Inward";} else { $gir_type = "Outward";}
			$gir_bill_type = $row['gir_bill_type'];
			$gir_bill_type_vaue = '';
			switch ($gir_bill_type) {
			case "PO":
			$gir_bill_type = "Purchase Order";
			break;
			case "JW":
			$gir_bill_type = "Jobwork";
			break;
			case "SA":
			$gir_bill_type = "Sales";
			break;
			case "SR":
			$gir_bill_type = "Service";
			break;
			case "RC":
			$gir_bill_type = "Return Chalan";
			break;			
			}
			$row_data[] = $gir_type;
			$row_data[] = $gir_bill_type; 
			$row_data[] = $row['gir_no']; 
			$row_data[] = $row['l_name']; 
			$row_data[] = $row['gir_chalan_no']; 
			$row_data[] = $row['gir_status'];			
			
			$edit_btn=''; $delete_btn=''; $view='';
				
			if(in_array(PRODUCTION_GIR_LIST_SLUG_UPDATE,$bulkAccessArray)){
				if(!empty($row['pro_gir_id'])){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'gir_edit/'.$row['pro_gir_id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
			}
			if(in_array(PRODUCTION_GIR_LIST_SLUG_DELETE,$bulkAccessArray)){
				if(!empty($row['pro_gir_id'])){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_gir('.$row['pro_gir_id'].','.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
			}
			/*if(in_array(PRODUCTION_GIR_LIST_SLUG_VIEW,$bulkAccessArray)){
				   
				$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'gir_view/'.$row['pro_gir_id'].'"><i class="fa fa-eye"></i></a>';
			}  */
						
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
			$info['gir_type']			= $POST['gir_type'];
			$info['gir_bill_type']		= $POST['gir_bill_type'];
			$info['gir_no']				= $POST['gir_no'];
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_chalan_no']		= $POST['gir_chalan_no'];			
			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['branch_id']			= $branch_id;		
			//echo "<pre>"; print_r($info);die;		

			$gir_id=add_record('pro_gir', $info, $dbcon, $branch_id);			
			 
		if($gir_id){
			
		if(!empty($_FILES['gir_file']['tmp_name'][0])) {
			$imgresp = upload_gir_receipt($_FILES,$dbcon,$gir_id);
		}
		if($gir_id){	
			$arr['msg']="1";	
			//Insert LOG
			//$log_entry=common_log_entry($dbcon,"gir_add",1,"pro_gir",$gir_id);						
		}
		else{
			$arr['msg']="0";
		}
			$arr['msg']= "1";
		}
		else
		{
			$arr['msg']="1";
		}
		
		echo json_encode($arr);	
	}
	
	else if(strtolower($POST['mode']) == "edit") {		
		
		$info['gir_type']			= $POST['gir_type'];
		$info['gir_bill_type']		= $POST['gir_bill_type'];
		$info['gir_no']				= $POST['gir_no'];
		$info['vender_id']			= $POST['vender_id'];
		$info['gir_chalan_no']		= $POST['gir_chalan_no'];			
		$info['cdate']				= date("Y-m-d H:i:s"); 
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$info['branch_id']			= $branch_id;	

		$updateid=update_record('pro_gir', $info,"pro_gir_id=".$POST['eid'] , $dbcon);
		 	
		if($updateid){	
			$arr['msg']="update";		
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		echo json_encode($arr);	
	}
	
	else if(strtolower($POST['mode'])== "load_gir_trn_data") {
		
		if($POST['gir_id']){
			 $query="select mst.*,pro.product_name,cat.unit_name from pro_gir_trn as mst
			left join product_mst as pro on pro.product_id=mst.product_id
			left join unit_mst as cat on cat.unitid=mst.unit_id  
			where gir_trn_status=0 and mst.gir_id=".$POST['gir_id'];
		}
		else{
			 $query="select mst.*,pro.product_name,cat.unit_name from pro_gir_trn as mst
			left join product_mst as pro on pro.product_id=mst.product_id
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			where gir_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
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
						<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_gir_data('.$rel['gir_trn_id'].')"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_gir_data('.$rel['gir_trn_id'].','.$rel['purchaseorder_id'].')">X</button>
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
		$q = $dbcon -> query("SELECT mst.* FROM pro_gir_trn as mst WHERE gir_trn_id = '$POST[gir_trn_id]'");
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
		
		//$r['po_html_resp']=get_po_for_gir($dbcon,$r['purchaseorder_id'],'Edit');
		$r['pro_html_resp']=get_po_for_gir_trn($dbcon,$r['purchaseorder_id'],$r['product_id'],'Edit');

		echo json_encode($r);
	}
	else if(strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$info['gir_trn_status']=2;	
		$updateid=update_record('pro_gir_trn', $info, "gir_trn_id=".$POST['gir_trn_id'] , $dbcon);
		
		//$UPD_PO=upd_gir_used_status($dbcon, $POST['purchaseorder_id'], 2);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "delete_gir") {
		$row=array();
		$info['gir_status']=2;
		$updateid=update_record('pro_gir', $info, "gir_id=".$POST['gir_id'] , $dbcon);
		
		$upd_po_sts=upd_gir_used_status($dbcon, $POST['purchaseorder_id'], 2);
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"gir_add",3,"pro_gir",$POST['gir_id']);	
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	} 
	else if(strtolower($POST['mode'])== "load_purhcase_order_data") {
		
		$id=$POST['order_id'];
		$gir_type=$POST['gir_type'];
		
		if($gir_type==2)
		{
			$resp['pro_html'] = get_po_details_for_gir_trn($dbcon,$id,$gir_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
			$resp['request_id'] ='';
		}
		else
		{
			$resp['pro_html'] = job_work_product_for_pending_gir($dbcon,$POST['vender_id'],$POST['order_id']);
			
			//$resp['pro_html'] = get_jobwork_details_for_gir_trn($dbcon,$id,'',$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['order_id']);
			//$resp['request_id'] = get_request_id_jobwork($dbcon,$id);
			$resp['request_id'] = "";
		}
		
		$vendor_id=get_vender_id($dbcon,$id,$gir_type);
		$resp['vendor_id'] = $vendor_id;
		$resp['vendor_name'] = get_vender_name($dbcon,$vendor_id,$gir_type);
		
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_po_ven_wise") {
		$resp['pro_html'] = get_po_for_gir($dbcon,'',$POST['vender_id'],'Add');
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "load_productdetail") {
		$purchaseorder_id=$POST['purchaseorder_id'];
		$product_id=$POST['product_id'];
		$query="select trn.*,main_gir_qty from tbl_purchaseordertrn as trn
		left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_gir_qty FROM pro_gir_trn as chtrn where chtrn.gir_trn_status!=2 and chtrn.purchaseorder_id=".$purchaseorder_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
		where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$purchaseorder_id." and trn.product_id=".$product_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$rel['pending_qty']=floatval($rel['product_qty'])-floatval($rel['main_gir_qty']);
		
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
	else if(strtolower($POST['mode'])== "load_gir_no") {
		$row=array();
		$query1="select * from tbl_invoicetype where type_id='8'";
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
		$info['gir_attch_status']=2;	
		$updateid=update_record('pro_gir_attch', $info, "gir_attch_id=".$POST['gir_attch_id'] , $dbcon);
		 
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "get_gir_bill_type_company"){
		$bill_type_id=$_POST['bill_type_id'];
		$type_id=$_POST['type_id'];
		$str='';
		$i = true;
		if($type_id == "I")
		{
			$bill_type_array = array('PO'=>'Purchase Order','RC'=>'Return Chalan');
		}
		else
		{
			$bill_type_array = array('JW'=>'Jobwork','SA'=>'Sales','SR'=>'Service','RC'=>'Return Chalan');
		}

		$str .= '<option value="">--Select GIR Bill Type--</option>';
		foreach($bill_type_array as $key=>$value)
		{
		$sel=''; 
		if($key==$bill_type_id)
		{$sel ="selected='selected'";}

		$str .= '<option '.$sel.' value="'.$key.'">'.$value.'</option>';
		}
		echo  $str;
	}				
	else if(strtolower($POST['mode'])== "get_vender_by_bill_type")
	{
		$bill_type_id=$_POST['bill_type_id'];
		$type_id=$_POST['type_id'];
		$vender_id=$_POST['vender_id'];
		$str='';
		$i = true;
		if($type_id == "I")
		{
			$bill_type_array = array('PO'=>'Purchase Order','RC'=>'Return Chalan');
		}
		else
		{
			$bill_type_array = array('JW'=>'Jobwork','SA'=>'Sales','SR'=>'Service','RC'=>'Return Chalan');
		}
		
		if($type_id == "I" && $bill_type_id == "PO" )
		{
			$query="select * from tbl_purchaseorder as p inner join tbl_ledger as l on l.l_id=p.vender_id where p.po_approval_status=0";	
			$result=$dbcon->query($query);
				
		}
		else if($type_id == "I" && $bill_type_id == "RC" )
		{
			$query="select *,rc.cust_id as vender_id from tbl_returnable_channal as rc inner join tbl_ledger as l on l.l_id=rc.cust_id where rc.returnable_type='returnable' AND rc.status=0 ";	
			$result=$dbcon->query($query);
		}
		else if($type_id == "O" && $bill_type_id == "JW" )
		{
			$query="select *,j.j_vendor as vender_id from  tbl_jobwork as j inner join tbl_ledger as l on l.l_id=j.j_vendor where j.job_close_status=0";	
			$result=$dbcon->query($query);
		}
		else if($type_id == "O" && $bill_type_id == "SA" )
		{
			$query="select *,i.cust_id as vender_id  from tbl_invoice as i inner join tbl_ledger as l on l.l_id=i.cust_id";	
			$result=$dbcon->query($query);
		}
		else if($type_id == "O" && $bill_type_id == "SR" )
		{
			$query="select * from tbl_purchaseorder as p inner join tbl_ledger as l on l.l_id=p.vender_id where p.po_approval_status=0";	
			$result=$dbcon->query($query);
		}
		else if($type_id == "O" && $bill_type_id == "RC" )
		{
			$query="select *,rc.cust_id as vender_id from tbl_returnable_channal as rc inner join tbl_ledger as l on l.l_id=rc.cust_id where rc.returnable_type='non-returnable' AND rc.status=0 ";	
			$result=$dbcon->query($query);
		}
		

		$str .= '<option value="">--Select Vender --</option>';
		while($rel=mysqli_fetch_assoc($result))
		{
		$sel=''; 
		if($rel['vender_id']==$vender_id)
		{$sel ="selected='selected'";}

		$str .= '<option '.$sel.'  value="'.$rel['vender_id'].'">'.$rel['l_name'].'</option>';
		}
		echo  $str;
	}	
			
			
			
	
function upd_gir_used_status($dbcon,$purchaseorder_id,$flag){
	if($flag=='1'){
		//get Same Qty Data
		$get_dt_qry="SELECT SUM(potrn.product_qty) as po_qty,(SELECT SUM(girtrn.product_qty) FROM `pro_gir_trn` as girtrn where girtrn.gir_trn_status=0 and girtrn.purchaseorder_id=".$purchaseorder_id." and girtrn.product_id=potrn.product_id) as gir_qty FROM `tbl_purchaseordertrn` as potrn where potrn.purchaseordertrn_status=0 and potrn.purchaseorder_id=".$purchaseorder_id." group by potrn.product_id";
		$get_dt_rs=$dbcon->query($get_dt_qry);
		$same_qty=true;
		while($get_dt_rel=mysqli_fetch_assoc($get_dt_rs)){
			//compare pending qty
			if($get_dt_rel['po_qty']!=$get_dt_rel['gir_qty']){
				$same_qty=false;
			}
		}
	}
	
	//update PO if all used in gir
	if($same_qty){
		$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_gir_status=1 where purchaseorder_id=".$purchaseorder_id);
	}
	else{
		$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_gir_status=0 where purchaseorder_id=".$purchaseorder_id);
	}
}

function upload_gir_receipt($FILES,$dbcon,$gir_id){
	$cnt=count($_FILES['gir_file']['name']);
	for( $i=0 ; $i < $cnt ; $i++ ) {
		if(!empty($_FILES['gir_file']['tmp_name'][$i])) {
			$rand=rand(0,999999);
			$temp = explode(".", $_FILES["gir_file"]["name"][$i]);
			$extension = strtolower(end($temp));
			$file_name = $_FILES['gir_file']['name'][$i];
			$err = $_FILES["gir_file"]["tmp_name"][$i];
			$file_name = "gir_rec_".$rand.'.'.$extension;
			move_uploaded_file($err,RECEIPT_FILE_UPING.$file_name);
			
			$attch['gir_id']		= $gir_id;
			$attch['gir_file']		= $file_name;
			$attch['cdate']			= date("Y-m-d H:i:s"); 
			$attch['user_id']		= $_SESSION['user_id'];
			$attch['company_id']	= $_SESSION['company_id']; 
			$inserid=add_record('pro_gir_attch', $attch, $dbcon);
			//return 	$file_name;
		}
	}
}



?>