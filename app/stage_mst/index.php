<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

	$whr='';
	$appData = array();
	$i=1;
	$aColumns = array('stage_id', 'stage_name', 'cdate', 'stage_status', 'user_id');
	$sIndexColumn = "stage_id";
	$isWhere = array("stage_status !=2".$whr);
	$sTable = "stage_mst as zmst";			
	$isJOIN = array();
	$hOrder = "stage_status desc ,zmst.stage_name";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
			//print_r($row);

			// if($row['stage_status']==0)
			// {  
			// 	$status="<strong style='color:green'>Approved</strong>";
			// 	$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['stage_id']."\",\"".$row['stage_status']."\")'><i class='fa fa-check-square-o'></i></a>";
			// }
			// else
			// {
			// 	$status="<strong style='color:red' >Pending</strong>"; 
			// 	$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['stage_id']."\",\"".$row['stage_status']."\")'><i class='fa fa-window-close'></i></a>";
			// }

		$row_data[]=$id;
		$row_data[]=$row['stage_name'];

		$edit_btn='';$delete_btn='';
		$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'stage_edit/'.$row['stage_id'].'"><i class="fa fa-pencil"></i></a>';
		$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_stage('.$row['stage_id'].')"><i class="fa fa-trash-o"></i></button>';
			// if($edit_btn_per){
			// 	$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_edit/'.$row['stage_id'].'"><i class="fa fa-pencil"></i></a>';
			// }
			// if($delete_btn_per){
			// 	$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product('.$row['stage_id'].')"><i class="fa fa-trash-o"></i></button>';
			// }



		//	$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status; 
		$row_data[] = $edit_btn.' '.$delete_btn; 
		$appData[] = $row_data;
		$id++;
	}
	//	print_r($appData);
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	$tr = $dbcon -> query("SELECT `product_id`,`stage_name`,`stage_status` FROM `stage_mst` WHERE stage_status=0 and `stage_name` ='".$POST['stage_name']."' ");
	if($tr->num_rows > 0) {
		$resp['msg'] = '-1';
	}
	else {
		$info['stage_name']	= $POST['stage_name'];							
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$inserid=add_record('stage_mst', $info, $dbcon);

		if($inserid){
			$log_entry=common_log_entry($dbcon,"stage_add",1,"stage_mst",$inserid);
			
			$resp['msg'] = "1";
		}
		else{
			$resp['msg'] = "0";
		}
	}
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `product_mst` WHERE `product_id` = '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	$info['stage_name']	= $POST['stage_name'];
	$info['cdate']		= date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];
	$updateid=update_record('stage_mst', $info,"stage_id=".$POST['eid_main'] , $dbcon);
	$log_entry=common_log_entry($dbcon,"stage_edit",2,"stage_mst",$POST['eid_main']);
	$resp['msg'] = "1";
	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "delete") {
	$info['stage_status']='2';
	$updateid=update_record('stage_mst', $info,"stage_id=".$POST['eid'] , $dbcon);
	if($updateid)
		echo "1";
	else
		echo "0"; 
}

else if(strtolower($POST['mode'])== "preedit_unit")
{
	$q = $dbcon -> query("SELECT * FROM tbl_product_unit WHERE unit_id	= '$POST[id]'");
	$r = $q->fetch_assoc();

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_data_unit")
{

	$deleteid=delete_record('tbl_product_unit', "unit_id=$POST[eid]", $dbcon);

	if($deleteid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}




		// Process Parameter

else if(strtolower($POST['mode']) == "add_process_value") {


	$info1['process_id']= $POST['process_id'];
	$info1['process_priority']= $POST['process_priority'];
	$info1['process_type']= $POST['process_type'];
	$info1['product_id']= $POST['pid'];
	$info1['process_time']= $POST['process_time'];
	$info1['process_opening']= $POST['process_opening'];

	$info1['cdate'] = date("Y-m-d");
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']			= $_SESSION['company_id'];

	$table='tbl_product_process';$tableid='pr_process_id';


	if(empty($POST['edit_id']))
	{
		$inserid=add_record($table, $info1, $dbcon);

				//Product Opening to process Allocation set 

				/*$process=get_product_process($dbcon,$POST['pid']);
						
				$process_pr=json_decode($process);
			
				$process_id=$process_pr->process_id;
				$process_type=$process_pr->process_type; */
				if($POST['process_opening']!="0"){
					if($POST['process_opening']!=""){
						$info5['process_id']	= $POST['process_id'];			
						$info5['p_qty']	= $POST['process_opening'];		
						$info5['pen_qty']	= $POST['process_opening'];		
						$info5['p_ref_id']	=$inserid;		
						$info5['p_ref_type']	='process_opening';		
						$info5['p_product_id']	= $POST['pid'];	
						$info5['pr_process_type']	= $POST['process_type'];						
						$info5['process_type_data']	= '1';						
						
						$info5['cdate']		= date("Y-m-d H:i:s");
						$info5['user_id']	= $_SESSION['user_id'];
						$info5['company_id']	= $_SESSION['company_id'];	
						
						$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon);
					}
				}
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				
				//Product Opening to process Allocation set 
				
				$q1=$dbcon->query("select p_id from tbl_allocate_process where p_ref_type='process_opening' and p_ref_id='$POST[edit_id]'");
				$count1=mysqli_num_rows($q1);
				
				/*$process=get_product_process($dbcon,$POST['pid']);
						
				$process_pr=json_decode($process);
			
				$process_id=$process_pr->process_id;
				$process_type=$process_pr->process_type; */
				
				$info6['process_id']	= $POST['process_id'];			
				$info6['p_qty']	= $POST['process_opening'];		
				$info6['pen_qty']	= $POST['process_opening'];		
				$info6['p_ref_id']	=$POST['edit_id'];		
				$info6['p_ref_type']	='process_opening';		
				$info6['p_product_id']	= $POST['pid'];	
				$info6['pr_process_type']	= $POST['process_type'];						
				$info6['process_type_data']	= '1';						
				
				$info6['cdate']		= date("Y-m-d H:i:s");
				$info6['user_id']	= $_SESSION['user_id'];
				$info6['company_id']	= $_SESSION['company_id'];
				
				if($count1>0)
				{
					update_record('tbl_allocate_process',$info6,"p_ref_type='process_opening' and p_ref_id=".$POST['edit_id'] , $dbcon);	
				}
				else
				{
					if($POST['process_opening']!="0"){
						if($POST['process_opening']!=""){
							$inserid_alloc=add_record('tbl_allocate_process', $info6, $dbcon);
						}
					}
				}
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_product_process") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status=0 and  mst.product_id='$POST[product_id]'";
			}
			else{
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status=0 and  mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' ";
			}

			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>

			<div class="col-md-12 col-xs-11 margin_row">
			<div class="form-group">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th width="20%" class="text-center">Process</th>
			<th width="10%" class="text-center">Priority</th>
			<th width="10%" class="text-center">Type</th>
			<th width="10%" class="text-center">Time (In Min.)</th>
			<th width="10%" class="text-center">Opening Stock</th>
			<th width="10%" class="text-center">Action</th>
			</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					if($rel['process_type']=='1'){ $ptype="Inhouse"; } else { $ptype="Outside"; }
					echo '<tr id="fieldtr'.$id.'" >
					<td style="vertical-align:top;">
					'.$rel['process_name'].'
					</td>
					<td style="vertical-align:top;" class="text-center hide_act_add">
					'.$rel['process_priority'].'
					</td>
					<td style="vertical-align:top;" class="text-center hide_act_add">
					'.$ptype.'
					</td>
					<td style="vertical-align:top;" class="text-center hide_act_add">
					'.$rel['process_time'].'
					</td>
					<td style="vertical-align:top;" class="text-center hide_act_add">
					'.$rel['process_opening'].'
					</td>
					<td style="vertical-align:top" class="text-center">
					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_product_process('.$rel['pr_process_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_process('.$rel['pr_process_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '
			</table>			 
			</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_process")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_process WHERE pr_process_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_process")
		{
			$del_info['status'] = 2;
			// $deleteid=delete_record('tbl_product_process', "pr_process_id=$POST[eid]", $dbcon);
			$deleteid = update_record('tbl_product_process',$del_info,"pr_process_id=".$POST['eid'], $dbcon);
			
			
			
			$q = $dbcon -> query("SELECT * FROM tbl_allocate_process WHERE p_ref_type='process_opening' and p_ref_id=".$POST['eid']."");
			$r = $q->fetch_assoc();
			
			$info6['p_status']=2;
			update_record('tbl_allocate_process',$info6,"p_id=".$r['p_id'] , $dbcon);
			update_record('tbl_allocate_process_trn',$info6,"pt_alloc_id=".$r['p_id'] , $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") 
		{
			$p_status=$POST['p_status'];
			$pid=$POST['pid'];
			
			if($p_status==0)
			{
				$info['product_status'] = 1;
			}
			else
			{
				$info['product_status'] = 0;
			}
			
			$updateid=update_record('product_mst', $info,"product_id=".$POST['pid'] , $dbcon);		
			
			if($updateid)
				echo "1";	
			else
				echo "0";	
		}
		?>