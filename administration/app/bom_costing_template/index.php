<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	UPDATE_TDS_TAX_CATEGORY_MASTER,
	        DELETE_TDS_TAX_CATEGORY_MASTER
	    ]);
	 //    $branch_id = $POST['branch_id'];
		 $where='';
	 //    if($branch_id){
	 //        $where .= check_branch('fmst',$branch_id);
	 //    }
			
		$appData = array();
		$aColumns = array('template_name','cdate','bom_costing_template_id');
		$sIndexColumn = "bom_costing_template_id";
		$isWhere = array("status=0  and company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_bom_costing_template as fmst";			
		$isJOIN = array();
		$hOrder = "bom_costing_template_id desc";
		include($include.'pagging.php');
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['template_name'];
			$row_data[] = $row['cdate'];
			
			$edit_btn='';$delete_btn='';
				if(in_array(UPDATE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){
					
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'bom_costing_template_edit/'.$row['bom_costing_template_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				if(in_array(DELETE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){
					$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_tax_data('.$row['bom_costing_template_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
			
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add_cost_center_group") {
		
		$tr = $dbcon -> query("SELECT `cost_group_id`,`cost_group_name`,`company_id` FROM `tbl_cost_center_group` WHERE `isdelete`=0 and `cost_group_name` ='".$POST['cost_group_name']."' and `company_id`='".$_SESSION['company_id']."'");
		if($tr->num_rows > 0) {
			$r = brp_mysqli_fetch_assoc($tr);
			if($r['isdelete'] != 0) {
				$info['isdelete']=0;
				$updateid=update_record('tbl_cost_center_group', $info,"cost_group_id=".$r['cost_group_id'] , $dbcon);						
				if($updateid)
				echo "1";
				else
				echo "0";
			}
			else {
				echo '-1';
			}
		}
		else {
			$info['cost_group_name']	= $POST['cost_group_name'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			//print_r($info);exit();
			$inserid=add_record('tbl_cost_center_group', $info, $dbcon);
			if($inserid)
			echo "1".'-'.$inserid.'-'.$POST['cost_group_name'];
			else
			echo "0";
		}

	}else if(strtolower($POST['mode']) == "add") {

		$tr = $dbcon -> query("SELECT `template_name` FROM `tbl_bom_costing_template` WHERE `status`=0 and `template_name` ='".$POST['template_name']."' and `company_id`='".$_SESSION['company_id']."' ");
		if($tr->num_rows > 0) {
			echo '-1';
		}else{
	
			$info['template_name']	= $POST['template_name'];
			
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			
			$inserid=add_record('tbl_bom_costing_template', $info, $dbcon);
		
		
		if($inserid){
			
			$info1['bom_costing_template_id'] = $inserid ; 
			$info1['status'] = 0 ; 
			
			$updateid=update_record('tbl_bom_costing_template_trn', $info1,'status=3', $dbcon);						
		}
		
		if($inserid){
			echo "1";
		}else{
			echo "0";
		}

		}
		
		
	}
	else if(strtolower($POST['mode'])== "preedit")
	{
		$q = $dbcon -> query("SELECT ttc.tds_cat_detail_id, cm.common_mst_name,cm.common_mst_id, ttc.`tds_thresold_limit`,ttc.`tds_with_pan`,ttc.`tds_without_pan`,ttc.`tds_surcharge` FROM `tbl_tds_tax_category_detail` ttc	join tbl_common_mst as cm on cm.common_mst_id=ttc.tds_payee 
					where ttc.isdelete=0 and ttc.tds_cat_detail_id = '$POST[id]'");
		$r = brp_mysqli_fetch_assoc($q);
		
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		//echo '<pre>';print_r($POST);exit;

		$info['template_name']	= $POST['template_name'];
			
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		
		$updateid=update_record('tbl_bom_costing_template', $info,"bom_costing_template_id=".$POST['eid'] , $dbcon);
			
		
		
		if($updateid)
			echo "3";
		else
			echo "0".$dbcon->error;
		
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['isdelete']='1';
		$updateid=update_record('tbl_cost_center', $info,"tds_cat_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	else if(strtolower($POST['mode']) == "load_tempoutward") {

		//echo '<pre>';print_r($POST);exit;
			if($POST['eid']){
				$query="SELECT ttc.tds_cat_detail_id, cm.common_mst_name, ttc.tds_thresold_limit,ttc.tds_with_pan,ttc.tds_without_pan,ttc.tds_surcharge FROM tbl_tds_tax_category_detail ttc join tbl_common_mst as cm on cm.common_mst_id=ttc.tds_payee
					where ttc.isdelete=0 and ttc.tds_cat_id=".$POST['eid'];
			}
			else{
			$query="SELECT ttc.tds_cat_detail_id, cm.common_mst_name, ttc.`tds_thresold_limit`,ttc.`tds_with_pan`,ttc.`tds_without_pan`,ttc.`tds_surcharge` FROM `tbl_tds_tax_category_detail` ttc	join tbl_common_mst as cm on cm.common_mst_id=ttc.tds_payee and ttc.tds_cat_id = 0
					where ttc.isdelete=0 ";
			}
			
			$result=$dbcon->query($query);
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="15%">Payee Category</th>
							<th class="text-center" width="10%">Threshold Limit</th>
							<th class="text-center" width="10%">TDS(With PAN)%</th>
							<th class="text-center" width="10%">TDS(Without PAN)%</th>
							<th class="text-center" width="10%">Surcharge %</th>
							<th class="text-center" width="6%">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel= brp_mysqli_fetch_assoc($result))
				{
	
				 	echo '<tr id="fieldtr'.$id.'" >
						
						<td style="vertical-align:top;">
							<b>'.$rel['common_mst_name'].'</b>
						</td>
						
						<td style="vertical-align:top;" class="text-center">
							'.$rel['tds_thresold_limit'].'
						</td>
						<td style="vertical-align:top;">
							<b>'.$rel['tds_with_pan'].'</b>
						</td>
						
						<td style="vertical-align:top;" class="text-center">
							'.$rel['tds_without_pan'].'
						</td>
						<td style="vertical-align:top;">
							<b>'.$rel['tds_surcharge'].'</b>
						</td>						
						
						<td style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['tds_cat_detail_id'].',\' tbl_tds_tax_category_detail\',\'tds_cat_detail_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['tds_cat_detail_id'].',\' tbl_tds_tax_category_detail\',\'tds_cat_detail_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					</tr>';
					$i++;
					if($rel['product_type']!="8"){
						$sales_account_amount=$sales_account_amount+$rel["taxable_value"];
					}
				}
			}
			else{
				echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
			}
				
		}
		else if(strtolower($POST['mode']) == "delete_data") {
			$row=array();
			$info['isdelete']=1;	
			
			$updateid=update_record('tbl_tds_tax_category_detail', $info, "tds_cat_detail_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="delete_tax_data")
		{
			$row=array();
			$info['isdelete']=1;	
			
			$updateid=update_record('tbl_tax_category_details', $info, "tax_cat=".$POST['eid'] , $dbcon);
			$updateid=update_record('tbl_tax_category', $info, "tax_cat_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="delete_tax_details_data")
		{
			$row=array();
			$info['status']=2;	
			
			$updateid=update_record('tbl_bom_costing_template_trn', $info, "bom_costing_template_trn_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		else if(strtolower($POST['mode'])=="add_tax_percentage")
		{
				
				$info['type_name']=$POST['type_name'];
				$info['type']=$POST['type'];
				$info['per']=$POST['per'];
				$info['amount']=$POST['amount'];

				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['status']=3;
				if(!empty($POST['eid'])){
					$info['status']=0;
					$info['bom_costing_template_id'] =$POST['eid'];
				}
				//var_dump($info);
				if(!empty($POST['edit_id'])){
					
					$updateid=update_record('tbl_bom_costing_template_trn', $info, "bom_costing_template_trn_id=".$POST['edit_id'] , $dbcon);

				}else{
					$inserid=add_record('tbl_bom_costing_template_trn', $info, $dbcon);	
				}
				
				if($inserid){
					echo "1";
				}else if($updateid){
					echo "2";
				}
				else{
					echo "0";	
				}
				
			
			
		}
		else if(strtolower($POST['mode'])=="load_tax_category_data")
		{
			
			$eid = $POST['eid'];
			if(!empty($eid)){
				$edid_val=" and bom_costing_template_id=".$POST['eid'];
				$result = $dbcon->query("select * from tbl_bom_costing_template_trn as t  where t.status='0' ".$edid_val);
			}else{
				$edid_val=" and user_id=".$_SESSION['user_id'];
				$result = $dbcon->query("select * from tbl_bom_costing_template_trn as t  where t.status='3' ".$edid_val);
			}
			
			
			
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="15%">Type Name</th>
							<th class="text-center" width="10%">Type</th>
							<th class="text-center" width="10%">Per(%)</th>
							<th class="text-center" width="10%">Amount</th>
							<th class="text-center" width="6%">Action</th>
						</tr>';
			
				while($rel= brp_mysqli_fetch_assoc($result))
				{
					if($rel['type']==1){
						$type1="Subtractive";
					}else{
						$type1="Additive";
					}
					echo '<tr>
					
						<th class="text-center">'.$rel['type_name'].'</th>
						<th class="text-center">'.$type1.'</th>
						<th class="text-center">'.$rel['per'].'</th>
						<th class="text-center">'.$rel['amount'].'</th>
						<th class="text-center">
							<a class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_tax_details_data('.$rel['bom_costing_template_trn_id'].')"><i class="fa fa-trash-o"></i></a>
						</th>
					</tr>';
				}
				
			
		}
	
?>