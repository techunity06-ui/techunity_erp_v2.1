<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//echo '<pre>'; print_r($_POST);exit;
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
// error_reporting(E_ALL);
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
		$i=1;
		$aColumns = array('fmst.tds_cat_id','fmst.tds_cat_name', 'fmst.tds_section','fmst.tds_date','fmst.isdelete','fmst.is_deletable');
		$sIndexColumn = "fmst.tds_cat_id";
		$isWhere = array("fmst.isdelete=0 and fmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_tds_tax_category as fmst";			
		//$isJOIN = array("left join tbl_cost_center_group as cg on fmst.cost_group_id=cg.cost_group_id");
		$isJOIN = array('');
		$hOrder = "fmst.tds_cat_id desc";
		$having_clause = '';
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['tds_cat_name'];
			$row_data[] = $row['tds_section'];
			$row_data[] = date('d, M y',strtotime($row['tds_date']));
			
			$edit_btn='';$delete_btn='';
			if(in_array(UPDATE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){
				
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'tds_tax_category_edit/'.$row['tds_cat_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(DELETE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray) && $row['is_deletable']=='0'){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_tds_data('.$row['tds_cat_id'].')"><i class="fa fa-trash-o"></i></button>';
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
		//echo '<pre>';print_r($POST);exit;
		$tr = $dbcon -> query("SELECT * FROM `tbl_tds_tax_category` WHERE `tds_cat_name` ='".$POST['tds_cat_name']."' and `tds_section` = '".$POST['tds_section']."' and `effected_ledger_id` ='".$POST['effective_ledger_id']."' and `isdelete`=0");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {

			$info['tds_cat_name']		= $POST['tds_cat_name'];
			$info['effected_ledger_id']	= $POST['effective_ledger_id'];
			$info['tds_section']		= $POST['tds_section'];
			$info['tds_date']			= date("Y-m-d",strtotime($POST['tds_date']));

			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$inserid=add_record('tbl_tds_tax_category', $info, $dbcon);
			if($inserid){
				$tds_tax_cat = $dbcon -> query("SELECT * FROM `tbl_tds_tax_category_detail`where isdelete=0 and tds_cat_id = 0");
				$tds_tax_cat_r = brp_mysqli_fetch_assoc($tds_tax_cat);
				if(!empty($tds_tax_cat_r)){
					$info1['tds_cat_id']	= $inserid;
					$updateid=update_record('tbl_tds_tax_category_detail', $info1,"tds_cat_id=0 and isdelete=0" , $dbcon);
					if($updateid){
						echo "1";
					}else{
						echo "0";
					}
				}else{
					echo "1";
				}
				
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

		$info['tds_cat_name']		= $POST['tds_cat_name'];
		$info['effected_ledger_id']	= $POST['effective_ledger_id'];
		$info['tds_section']		= $POST['tds_section'];							
		$info['tds_date']			= date("Y-m-d",strtotime($POST['tds_date']));

		$info['cdate']				= date("Y-m-d H:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		
		$updateid=update_record('tbl_tds_tax_category', $info,"tds_cat_id=".$POST['eid'] , $dbcon);
		
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
	else if(strtolower($POST['mode']) == 'fieldadd'){
		if(!empty($POST['tds_cat_id'])){
			$where = ' and tds_cat_id='.$POST['tds_cat_id'].'';
		}else{
			$where = ' and tds_cat_id=0';
		}

		if(!empty($POST['edit_id'])){
			$where1= ' and tds_cat_detail_id != '.$POST['edit_id'].' ';
		}else{
			$where1= ' ';
		}

		$tr = $dbcon -> query("SELECT tds_cat_detail_id FROM `tbl_tds_tax_category_detail` WHERE `tds_payee` ='".$POST['common_mst_id']."' and `isdelete`=0".$where.$where1);
		
		if($tr->num_rows > 0) {
			echo "-1";
		}else{
			$info['tds_payee']			= $POST['common_mst_id'];
			$info['tds_thresold_limit']	= $POST['tds_thresold_limit'];
			$info['tds_with_pan']		= $POST['tds_with_pan'];
			$info['tds_without_pan']	= $POST['tds_without_pan'];	
			$info['tds_surcharge']		= $POST['tds_surcharge'];						
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['usertype_id']		= $_SESSION['user_type'];

			if(!empty($POST['tds_cat_id'])){
				$info['tds_cat_id']= $POST['tds_cat_id'];
			}
			else{
				$info['tds_cat_id']	= 0;
			}
			if(empty($POST['edit_id'])){
				$inserid=add_record('tbl_tds_tax_category_detail', $info, $dbcon);
			}
			else{
				$updateid=update_record('tbl_tds_tax_category_detail', $info,'tds_cat_detail_id='.$POST['edit_id'] , $dbcon);	
				$updateid=$POST['edit_id'];
			}
			if($inserid){
				echo "1";
			}else if($updateid){
				echo "2";	
			}else{
				echo "0";
			}	
		}
		
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
	
				 	echo '<input type="hidden" value="'.$i.'" name="field_cnt" id="field_cnt"/>
				 	<tr id="fieldtr'.$id.'" >
						
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
				echo '<input type="hidden" value="0" name="field_cnt" id="field_cnt"/><tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
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
		else if(strtolower($POST['mode'])=="delete_tds_data")
		{
			$row=array();
			$info['isdelete']=1;	
			
			$updateid=update_record('tbl_tds_tax_category', $info, "tds_cat_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
	
?>