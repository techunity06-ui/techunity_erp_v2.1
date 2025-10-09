<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CRM_PROJECT_ASSIGN_SLUG_VIEW,
	CRM_PROJECT_ASSIGN_SLUG_CREATE,
	CRM_PROJECT_ASSIGN_SLUG_LIST,
	CRM_PROJECT_ASSIGN_SLUG_UPDATE,
	CRM_PROJECT_ASSIGN_SLUG_DELETE

]);

if(!in_array(CRM_PROJECT_ASSIGN_SLUG_VIEW,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(brp_strtolower($POST['mode']) == "fetch") {

			//$branch_id = $POST['branch_id'];	
	$where='';

	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	if($branch_id){
		$where .= check_branch('pa',$branch_id);
	}

	$appData = array();
	$i=1;
	$aColumns = array('pa.product_id','pa.product_name','pa.product_status','pa.product_icode','bm.branch_name');
	$sIndexColumn = "product_icode";
	$isWhere = array("product_status = 0 AND product_type = '-1' AND pa.company_id IN (0,$_SESSION[company_id])".$where);
	$sTable = "product_mst as pa";			
	$isJOIN = array('left join branch_mst bm on bm.branch_id=pa.branch_id');
	$hOrder = "pa.product_icode desc";
	include($incPath.'pagging.php');
	$id=1;

	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $id;
		$row_data[] = $row['product_name'];
		$row_data[] = $row['product_icode'];
		$row_data[] = $row['branch_name'];

		if(in_array(CRM_PROJECT_ASSIGN_SLUG_DELETE,$bulkAccessArray)){
			$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_project_assign('.$row['product_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		if(in_array(CRM_PROJECT_ASSIGN_SLUG_UPDATE,$bulkAccessArray)){
			$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'project_assign_edit/'.$row['product_id'].'"><i class="fa fa-pencil"></i></a>';
		}

		$view='<button class="btn btn-xs btn-primary" data-original-title="View" data-toggle="tooltip" data-placement="top" onClick="view_project_assign('.$row['product_id'].')"><i class="fa fa-eye"></i></a>';

		$row_data[] = $edit.' '.$delete.' '.$view;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo brp_json_encode( $output );
}
else if(brp_strtolower($POST['mode']) == "add") {

	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info['product_type']	= $_POST['product_type'];
	$info['product_name']	= $_POST['project_name'];
	$info['product_icode']	= $_POST['product_icode'];
	$info['product_hsn']	= $_POST['product_hsn'];
	$info['product_base_unit']	= $_POST['product_base_unit'];
	$info['product_conv_unit']	= $_POST['product_conv_unit'];
	$info['product_conv_qty']	= $_POST['product_conv_qty'];
	$info['product_base_qty']	= $_POST['product_base_qty'];
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	$inserestimateid=add_record('product_mst', $info, $dbcon, $branch_id);
			//tbl_project_assigntrn

	$info_update['project_assigntrn_status']	= 0;
	$info_update['project_assign_id']	= $inserestimateid;
	$updateid=update_record('tbl_project_assigntrn', $info_update,"project_assigntrn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);

	if($inserestimateid)
	{	
		$arr['msg']="1";
		$arr['eid']=$inserestimateid;
	}
	else
	{
		$arr['msg']="0";
	}
	echo brp_json_encode($arr);
}	

else if(brp_strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info['product_type']	= $_POST['product_type'];
	$info['product_name']	= $_POST['project_name'];
	$info['product_icode']	= $_POST['product_icode'];
	$info['product_hsn']	= $_POST['product_hsn'];
	$info['product_base_unit']	= $_POST['product_base_unit'];
	$info['product_conv_unit']	= $_POST['product_conv_unit'];
	$info['product_conv_qty']	= $_POST['product_conv_qty'];
	$info['product_base_qty']	= $_POST['product_base_qty'];
	$info['cdate']			= date("Y-m-d H:i:s");
	$info['user_id']		= $_SESSION['user_id'];
	$info['company_id']		= $_SESSION['company_id'];
	$updateid=update_record('product_mst', $info,"product_id=".$POST['eid'] , $dbcon, $branch_id);

	if($updateid)
	{	
		$arr['msg']="update";
		$arr['eid']=$POST['eid'];
	}
	else
		$arr['msg']=0;
	echo brp_json_encode($arr);	

}
else if(brp_strtolower($POST['mode']) == "delete") {

	$info['product_status']	= 2;
	$info1['project_assigntrn_status']	= 2;
	$updateestimateid=update_record('product_mst', $info,"product_id=".$POST['eid'] , $dbcon);	
	$updatetrancationid=update_record('tbl_project_assigntrn', $info1,"project_assign_id=".$POST['eid'] , $dbcon);				
	if($updateestimateid)
		echo "1";	
	else
		echo "0";			
}
else if(brp_strtolower($POST['mode']) == "fieldadd") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info1['product_id']		= $_POST['product_id'];
	$info1['description']		= stripslashes($_POST['product_disc']);
	$info1['product_disc']		= stripslashes($_POST['product_disc']);
	$info1['product_spec']		= stripslashes($_POST['product_spec']);
	$info1['product_hsn_code']	= $_POST['product_hsn_code'];
	$info1['product_qty']		= $_POST['product_qty'];
	$info1['product_rate']		= $_POST['product_rate'];
	$info1['product_amount']	= $_POST['product_qty']*$_POST['product_rate'];
	$info1['formulaid']			= $_POST['formulaid'];
	$info1['company_id']		= $_SESSION['company_id'];

	$info=get_product_common_tax($dbcon,$info1['product_amount'],$POST['formulaid']);
	$info1=array_merge($info1,$info);

	$table='tbl_project_assigntrn';$tableid='project_assigntrn_id';
	if(!empty($POST['project_assign_id']))
	{
		$info1['project_assign_id']= $POST['project_assign_id'];
		$table='tbl_project_assigntrn';
		$tableid='project_assigntrn_id';
	}
	else
	{
		$info1['user_id']	= $_SESSION['user_id'];
		$info1['project_assigntrn_status']= 3;
	}
	if(empty($POST['edit_id']))
	{
		$inserid=add_record($table, $info1, $dbcon,$branch_id);
	}
	else
	{
		$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
		$inserid=$POST['edit_id'];
	}
}

else if(brp_strtolower($POST['mode'])== "load_productdata"){
	$pro_qry="select * from product_mst where product_id=".$POST['eid'];
	$pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));

	$qry1="select c_add_state as lst,com.stateid as cst from tbl_customer as led 
	left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
	left join tbl_company as com on com.company_id=led.company_id
	where led.cust_id =".$POST['cust_id'];
	$result1=$dbcon->query($qry1);
	$row1=mysqli_fetch_assoc($result1);

	if($row1['lst']==$row1['cst']){
		$qry2="select * from formula_mst as led 
		where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$pro_rel['product_sale_gst'];
		$result2=$dbcon->query($qry2);
		$row2=mysqli_fetch_assoc($result2);
		$pro_rel['formula_id']=$row2['formulaid'];
	}else{
		$qry2="select * from formula_mst as led 
		where formula_status=0 and tax_cat='INTER' and tax_per_id=".$pro_rel['product_sale_gst'];
		$result2=$dbcon->query($qry2);
		$row2=mysqli_fetch_assoc($result2);
		$pro_rel['formula_id']=$row2['formulaid'];
	}
	echo json_encode($pro_rel);

}

else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
	if(empty($POST['so_id'])){
		$query="select project_assigntrn_id,product.product_name,mst.description,product_qty,product_rate,mst.*,hsn.hsn_code as product_hsn from tbl_project_assigntrn as mst 
		left join product_mst as product on product.product_id=mst.product_id  
		left join mst_hsn_code as hsn on hsn.hsn_id=mst.product_hsn_code  
		where project_assigntrn_status=3 and mst.user_id=".$_SESSION['user_id'];
	}else{
		$query="select project_assigntrn_id,product.product_name,mst.description,product_qty,product_rate,mst.*,hsn.hsn_code as product_hsn from tbl_project_assigntrn as mst 
		left join product_mst as product on product.product_id=mst.product_id  
		left join mst_hsn_code as hsn on hsn.hsn_id=mst.product_hsn_code 
		where project_assigntrn_status=0 and project_assign_id	=".$POST['so_id'];
	}

	$result=$dbcon->query($query);
	$str='';
	$str.='<div class="form-group">
	<div class="col-md-12 col-xs-12">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
	<tr id="field">
	<th class="text-center"width="25%">Product Name</th>
	<th class="text-center"width="8%">HSN Code</th>
	<th class="text-center"width="8%">Qty</th>
	<th class="text-center"width="10%">Rate</th>
	<th class="text-center"width="10%">Total Amount</th>
	<th class="text-center"width="10%">Action</th>
	</tr>';
	if(brp_mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			$str.='<tr id="fieldtr'.$id.'" >
			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
			</td>

			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['product_hsn'])){
				$str.=  '-';
			}else{
				$str.=  $rel['product_hsn'];
			}
			$str.='</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			'.$rel['product_qty'].'
			</td>
			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
			'.$rel['product_rate'].'
			</td>
			<td  data-label="TOTAL AMOUNT" style="vertical-align:top;" class="text-center">
			'.$rel['product_total'].'
			</td>				

			<td data-label="ACTION" style="vertical-align:top">
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['project_assigntrn_id'].');" ><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['project_assigntrn_id'].');" id="fieldremove'.$i.'">X</button>
			</td>	
			</tr>';
			$i++;
		}
	}
	else{
		$str.='<tr><td colspan="6" class="text-center">NO DATA FOUND</td></tr>';
	}
	$str.='</table></div></div>';
	echo $str;
}

else if(brp_strtolower($POST['mode'])== "preedit"){
	$q = $dbcon -> query("select mst.*,pro.product_name from tbl_project_assigntrn as mst left join product_mst as pro on mst.product_id=pro.product_id where project_assigntrn_id = '$POST[id]'");
	$r = $q->fetch_assoc();

	echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_data"){
	$row=array();
	$info['project_assigntrn_status']=2;	
	$updateid=update_record("tbl_project_assigntrn", $info,"project_assigntrn_id=".$POST['eid'] , $dbcon);
	if($updateid)
		$row['res']="1";
	else
		$row['res']="0";
	echo brp_json_encode($row);
}
else if(strtolower($POST['mode'])== "getproduct_amount")
{
	$arr=get_product_common_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
	echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "get_product_code")
{
	$q = $dbcon -> query("SELECT * FROM tbl_product_code_series WHERE pr_type = '$POST[pcode]'");
	$r = $q->fetch_assoc();

	$pr_series=$r['pr_code_series']+1;
	$short_code=$r['pr_code_short'];

	$res['series']=$short_code."".sprintf('%05d',$pr_series);
	$res['code']=$pr_series;

	echo json_encode($res);
}
else if(strtolower($POST['mode'])== "check_pro_unit"){
	$row=array();
	$tbl=array();

	$sql1=$dbcon -> query("SELECT * FROM tbl_bom WHERE bom_product = '".$_POST['product_id']."' AND bom_status = 0");
	$sql2=$dbcon -> query("SELECT * FROM tbl_purchaseordertrn WHERE product_id = '".$_POST['product_id']."' AND purchaseordertrn_status = 0");
	$sql3=$dbcon -> query("SELECT * FROM tbl_potrancation WHERE product_id = '".$_POST['product_id']."' AND potrancation_status = 0");
	$sql4=$dbcon -> query("SELECT rp.*, ai.* FROM tbl_request_product AS rp LEFT JOIN approve_indent AS ai ON ai.rp_id = rp.rp_id WHERE rp.rp_pid = '".$_POST['product_id']."' AND rp.status = 0 AND ai.approve_indent_status = 0");

	$q = brp_mysqli_fetch_assoc($sql1);
	$qr = brp_mysqli_fetch_assoc($sql2);
	$qry = brp_mysqli_fetch_assoc($sql3);
	$querys = brp_mysqli_fetch_assoc($sql4);

	if(mysqli_num_rows($sql1) > 0){
		if($q['product_base_unit']==$_POST['unit_id']){
			$row['bom_status'] = "1";
		} else{
				// $row['tbl'] = "BOM";
			array_push($tbl, "BOM");
			$row['bom_status'] = "0";
		}
	} else{
		$row['bom_status'] = "1";
	}
	if(mysqli_num_rows($sql2) > 0){
		if($qr['unit_id']==$_POST['unit_id']){
			$row['purchase_status'] = "1";
		} else{
			array_push($tbl, "Purchase Order");
				// $row['tbl'] = "Purchase Order";
			$row['purchase_status'] = "0";
		}
	} else{
		$row['purchase_status'] = "1";
	}
	if(mysqli_num_rows($sql3) > 0){
		if($qry['unit_id']==$_POST['unit_id']){
			$row['purchasebill_status'] = "1";
		} else{
			array_push($tbl, "Purchase Bill");
				// $row['tbl'] = "Purchase Bill";
			$row['purchasebill_status'] = "0";
		}
	} else{
		$row['purchasebill_status'] = "1";
	}
	if(mysqli_num_rows($sql4) > 0){
		if($querys['approve_unit']==$_POST['unit_id']){
			$row['indent_status'] = "1";
		} else{
			array_push($tbl, "Indent");
				// $row['tbl'] = "Indent";
			$row['indent_status'] = "0";
		}
	} else{
		$row['indent_status'] = "1";
	}

	if($row['bom_status']==1 && $row['purchase_status']==1 && $row['purchasebill_status']==1 && $row['indent_status']==1){
		$row['status'] = 1;
	} else {
		$row['status'] = 0;
		$row['table'] = implode(",", $tbl);
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "view_project_assign"){
	$row=array();
	$sql = $dbcon->query("SELECT pro.*, unit.unit_name FROM product_mst AS pro LEFT JOIN unit_mst AS unit ON unit.unitid = pro.product_base_unit WHERE product_id='".$_POST['id']."'");
	$res=brp_mysqli_fetch_assoc($sql);

	$row['project_name']=$res['product_name'];
	$row['project_code']=$res['product_icode'];
	$row['project_unit']=$res['unit_name'];

	$query="SELECT mst.*, product.product_name FROM tbl_project_assigntrn AS mst 
	LEFT JOIN product_mst as product on product.product_id=mst.product_id  
	WHERE project_assigntrn_status=0 AND project_assign_id	=".$_POST['id'];

	$result=$dbcon->query($query);
	$str='';
	$str.='<div class="form-group">
	<div class="col-md-12 col-xs-12">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
	<tr id="field">
	<th class="text-center"width="25%">Product Name</th>
	<th class="text-center"width="8%">HSN Code</th>
	<th class="text-center"width="8%">Qty</th>
	<th class="text-center"width="10%">Rate</th>
	<th class="text-center"width="10%">Taxable Value</th>
	<th class="text-center"width="10%">Tax</th>
	<th class="text-center"width="10%">Total Amount</th>
	</tr>';
	if(brp_mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			$str.='<tr id="fieldtr'.$id.'" >
			<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
			'.$rel['product_name'].'
			'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
			</td>

			<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
			if(empty($rel['product_hsn_code'])){
				$str.=  '-';
			}else{
				$str.=  $rel['product_hsn_code'];
			}
			$str.='</td>
			<td data-label="QTY" style="vertical-align:top;" class="text-center">
			'.$rel['product_qty'].'
			</td>
			<td  data-label="RATE" style="vertical-align:top;" class="text-center">
			'.$rel['product_rate'].'
			</td>
			<td  data-label="TAXABLE AMOUNT" style="vertical-align:top;" class="text-center">
			'.$rel['product_amount'].'
			</td>
			<td  data-label="TAX" style="vertical-align:top;" class="text-center">';
			if(empty($rel['formulaid'])){
				$str.= '-';
			}else{
				$str.= (empty($rel['tax_name1']) ? " " : $rel['tax_name1'] .' : '. $rel['tax_amount1']).'<br/>';
				$str.= (empty($rel['tax_name2']) ? " " : $rel['tax_name2'] .' : '. $rel['tax_amount2']).'<br/>';
				$str.= (empty($rel['tax_name3']) ? " " : $rel['tax_name3'] .' : '. $rel['tax_amount3']).'<br/>';
			}
			$str.='</td>
			<td  data-label="TOTAL AMOUNT" style="vertical-align:top;" class="text-center">
			'.$rel['product_total'].'
			</td>	
			</tr>';
			$i++;
		}
	}
	else{
		$str.='<tr><td colspan="7" class="text-center">NO DATA FOUND</td></tr>';
	}
	$str.='</table></div></div>';

	$row['show_product'] = $str;

	echo json_encode($row);
}
?>