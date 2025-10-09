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
		if($POST['fil_product_type']!=''){
			$whr.=' and product_type='.$POST['fil_product_type'];
		}
			
		$appData = array();
		$i=1;
		$aColumns = array('product_id', 'product_type', 'product_name', 'product_desc','zmst.cdate', 'product_status', 'zmst.user_id');
		$sIndexColumn = "product_id";
		$isWhere = array("product_status !=2".$whr);
		$sTable = "product_mst as zmst";			
		$isJOIN = array();
		$hOrder = "product_status desc ,zmst.product_name";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			if($row['product_status']==0)
			{  
				$status="<strong style='color:green'>Approved</strong>";
				$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-check-square-o'></i></a>";
			}
			else
			{
				$status="<strong style='color:red' >Pending</strong>"; 
				$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-window-close'></i></a>";
			}
			
			$row_data[] = $row['sr'];
			$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
			$row_data[] = stripcslashes($row['product_name']); 
			$row_data[] = nl2br($row['product_desc']); 
			$row_data[] = $status; 
			
			$edit_btn='';$delete_btn='';
			if($edit_btn_per){
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_edit/'.$row['product_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product('.$row['product_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			if($row['product_id']=='2862'){//Fixed Product Type Service ID
				$delete_btn='';$change_status='';
			}
				
			$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$tr = $dbcon -> query("SELECT `product_id`,`product_name`,`product_status` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$POST['product_name']."' ");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['product_type']	= $POST['product_type'];							
			$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$POST['product_name']));							
			$info['product_desc']	= $_POST['product_desc'];							
			$info['product_icode']	= $_POST['product_icode'];							
			$info['product_hsn']= $POST['product_hsn'];							
			$info['product_purchase_rate']= $POST['product_purchase_rate'];							
			$info['product_sale_rate']= $POST['product_sale_rate'];							
			$info['product_base_unit']= $POST['product_base_unit'];
			
			$info['product_base_qty']= $POST['product_base_qty'];
			$info['product_conv_unit']= $POST['product_conv_unit'];
			$info['product_conv_qty']= $POST['product_conv_qty'];
			
			$info['product_gst']= $POST['product_gst'];							
			$info['product_sale_gst']= $POST['product_sale_gst'];							
			$info['product_purchase_gst']= $POST['product_purchase_gst'];							
			$info['product_opening']= $POST['product_opening'];							
			$info['product_opening_valuation']= $POST['product_opening_valuation'];							
			$info['product_min_stock']= $POST['product_min_stock'];							
			$info['product_max_stock']= $POST['product_max_stock'];							
			$info['product_category']= $POST['product_category'];							
			$info['product_barcode']= $POST['product_barcode'];							
			$info['multi_branch']= $POST['multi_branch'];							
			$info['product_status']= '1';							
			$info['count_stock']= $POST['count_stock'];							
			$info['product_making_time']= $POST['product_making_time'];							
			$info['product_check']= implode(",",$POST['product_check']);							
			$info['product_setting_check']= implode(",",$POST['product_setting_check']);							
			//$info['product_icode_code']= $POST['product_icode_code'];
			$info['product_width']= $POST['product_width'];				
			$info['product_height']= $POST['product_height'];				
			$info['product_thickness']= $POST['product_thickness'];				
			$info['product_density']= $POST['product_density'];				
			$info['product_kg']= $POST['product_kg'];				
			$info['product_specification']= $POST['product_specification'];
			
							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $POST['branchid'];
			
			$inserid=add_record('product_mst', $info, $dbcon);
			
			if($inserid){
				
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"product_add",1,"product_mst",$inserid);
			
				$dbcon->query("update tbl_product_unit set unit_product='$inserid' WHERE unit_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_branch_product_stock set product_id='$inserid' WHERE product_id='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_images set im_product='$inserid' WHERE im_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_party_purchase set party_product='$inserid' WHERE party_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_job_party_purchase set job_party_product='$inserid' WHERE job_party_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
				
				if(strtolower($POST['product_model']) == "product_model"){
					$zone_qry="select * from product_mst where product_id=".$inserid; 
					$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
					$resp=$zone_rel;
					$resp['msg'] = "3";
				}
				else
				{
					$resp['msg'] = "1";
				}
				
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
		
			$info['product_type']	= $POST['product_type'];							
			$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$POST['product_name']));	
			$info['product_desc']	= $_POST['product_desc'];							
			$info['product_icode']	= $_POST['product_icode'];							
			$info['product_hsn']= $POST['product_hsn'];							
			$info['product_purchase_rate']= $POST['product_purchase_rate'];							
			$info['product_sale_rate']= $POST['product_sale_rate'];							
			$info['product_base_unit']= $POST['product_base_unit'];	

			$info['product_base_qty']= $POST['product_base_qty'];
			$info['product_conv_unit']= $POST['product_conv_unit'];
			$info['product_conv_qty']= $POST['product_conv_qty'];
			
			$info['product_gst']= $POST['product_gst'];							
			$info['product_sale_gst']= $POST['product_sale_gst'];							
			$info['product_purchase_gst']= $POST['product_purchase_gst'];							
			$info['product_opening']= $POST['product_opening'];							
			$info['product_opening_valuation']= $POST['product_opening_valuation'];							
			$info['product_min_stock']= $POST['product_min_stock'];
			$info['product_max_stock']= $POST['product_max_stock'];				
			$info['product_category']= $POST['product_category'];							
			$info['product_barcode']= $POST['product_barcode'];							
			$info['multi_branch']= $POST['multi_branch'];							
			$info['count_stock']= $POST['count_stock'];							
			$info['product_making_time']= $POST['product_making_time'];							
			$info['product_check']= implode(",",$POST['product_check']);
			$info['product_setting_check']= implode(",",$POST['product_setting_check']);		

			$info['product_width']= $POST['product_width'];				
			$info['product_height']= $POST['product_height'];				
			$info['product_thickness']= $POST['product_thickness'];				
			$info['product_density']= $POST['product_density'];				
			$info['product_kg']= $POST['product_kg'];
			$info['product_specification']= $POST['product_specification'];					

							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $POST['branchid'];
			
			$updateid=update_record('product_mst', $info,"product_id=".$POST['eid_main'] , $dbcon);
			
			//$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"product_add",2,"product_mst",$POST['eid_main']);
				
			$resp['msg'] = "2";
			
			echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$chk_arr[]=array("complaint_trn_id","tbl_complaint_trn","complaint_trn_status=0 and product_id=".$POST['eid']); 
		$chk_arr[]=array("bom_trn_id","tbl_bomtrn","bom_trn_status=0 and product_id=".$POST['eid']); 
		
		$chk_resp=check_delete_trn($dbcon,$chk_arr);
		if($chk_resp)
		{
			echo "-1";
		}
		else
		{
			$info['product_status']='2';
			$updateid=update_record('product_mst', $info,"product_id=".$POST['eid'] , $dbcon);

			//Insert LOG
			$log_entry=common_log_entry($dbcon,"product_add",3,"product_mst",$POST['eid']);
			
			if($updateid)
				echo "1";
			else
				echo "0"; 
		}
	}
	else if(strtolower($POST['mode']) == "add_unit_converter") {
			
			
			$info1['unit_alt_qty']= $POST['utab_alt_qty'];
			$info1['unit_alt_unit']= $POST['utab_alt_unit'];
			$info1['unit_basic_qty']= $POST['utab_basic_qty'];
			$info1['unit_basic_unit']= $POST['utab_basic_unit'];
			$info1['unit_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_unit';$tableid='unit_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_unit_converter") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,unit.unit_name as uname,unit1.unit_name as uname1 from tbl_product_unit as mst 
				left join unit_mst as unit on unit.unitid=mst.unit_alt_unit  left join unit_mst as unit1 on unit1.unitid=mst.unit_basic_unit
				where mst.unit_product='$POST[product_id]' order by unit_id Desc";
			}
			else{
				$query="select mst.*,unit.unit_name as uname,unit1.unit_name as uname1 from tbl_product_unit as mst 
				left join unit_mst as unit on unit.unitid=mst.unit_alt_unit  left join unit_mst as unit1 on unit1.unitid=mst.unit_basic_unit
				where mst.user_id=".$_SESSION['user_id']." and mst.unit_product='0' order by unit_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Alt Qty.</th>
							<th width="10%" class="text-center">Alt Unit</th>
							<th width="15%" class="text-center">Base Qty.</th>
							<th width="15%" class="text-center">Base Unit</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['unit_alt_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['uname'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['unit_basic_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['uname1'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_unit('.$rel['unit_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_unit('.$rel['unit_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
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
	
		else if(strtolower($POST['mode']) == "add_branch_stock") {
			
			$bstock=$POST['bstock'];
			$bid=$POST['bid'];
			$form_mode=$POST['form_mode'];
			$pid=$POST['pid'];
			$bpriority=$POST['bpriority'];
			
			for($i=0;$i<count($bstock);$i++)
			{
				$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='$bid[$i]' and product_id='$pid'");
				$count=mysqli_num_rows($q);
				
				$info['product_stock']=$bstock[$i];
				$info['branch_id']=$bid[$i];
				$info['priority']=$bpriority[$i];
				$info['user_id']=$_SESSION['user_id'];
				$info['cdate']=date("Y-m-d h:i:s");
				$info['company_id']=$_SESSION['company_id'];
				
				$table='tbl_branch_product_stock';$tableid='branch_product_stock_id';
				
				if($count>0)
				{
					$updateid=update_record($table, $info,"branch_id='$bid[$i]' and product_id='$pid'", $dbcon);	
				}else{
					
					if($pid>0)
					{
						$info['product_id']=$pid;
					}
					
					$inserid=add_record($table, $info, $dbcon);
				}
			}
			print_r($bid);
			
		}
		else if(strtolower($POST['mode']) == "add_product_image_temp") {
			
			 $test = explode('.', $_FILES["file"]["name"]);
			 $ext = end($test);
			 $name = rand(100, 999) . '.' . $ext;
			 $path='../../view/upload/product_images/';
			 $location = $path . $name;  
			 move_uploaded_file($_FILES["file"]["tmp_name"], $location);
			 
			 $info1['im_name']=$name;
			 $info1['cdate']=date("Y-m-d");
			 $info1['user_id']			= $_SESSION['user_id'];
			 $info1['branch_id']			= $POST['branchid'];
			 $info1['im_product']			= $POST['pid'];
			
			 $table='tbl_product_images';$tableid='img_id';
			
			 $inserid=add_record($table, $info1, $dbcon);
			
			 echo get_images_product($dbcon,'0');
			 
			
		}
		
		else if(strtolower($POST['mode']) == "load_product_images") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='$POST[product_id]' order by img_id Desc";
			}
			else{
				
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='0' order by img_id Desc";
			}	
				$rel=$dbcon->query($q);
				$path='view/upload/product_images/';
				$str="";
				$str.="<table></tr>";
				while($row  = mysqli_fetch_assoc($rel))
				{
					$str.='<td>
						<a onclick="delete_data_image('.$row['img_id'].');" href="#">
							<div class="img-wrap">
								<span class="close">&times;</span>
								<img src="'.ROOT.'view/img/close_img.jpg" width="30" height="30" class="img-thumbnail">
							</div>
							<img src="'.ROOT.$path.$row['im_name'].'" height="150" width="225" class="img-thumbnail" />
						</a>
					</td>';
				}
				$str.="</tr></table>";
				echo $str;
			
		    
		}
		else if(strtolower($POST['mode'])== "delete_data_image")
		{
			
			$deleteid=delete_record('tbl_product_images', "img_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Party Purchase
		
		else if(strtolower($POST['mode']) == "add_party_purchase") {
			
			
			$info1['party_id']= $POST['party_id'];
			$info1['party_rate']= $POST['party_rate'];
			$info1['party_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_party_purchase';$tableid='party_purchase_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_party_purchase") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.l_name from tbl_product_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.party_id where mst.party_product='$POST[product_id]' order by mst.party_purchase_id Desc";
			}
			else{
				$query="select mst.*,p.l_name from tbl_product_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.party_id where mst.user_id=".$_SESSION['user_id']." and mst.party_product='0' order by mst.party_purchase_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Party</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['l_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['party_rate'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_party_purchase('.$rel['party_purchase_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_party_purchase('.$rel['party_purchase_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
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
		
		else if(strtolower($POST['mode'])== "preedit_party")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_party_purchase WHERE party_purchase_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_party")
		{
			
			$deleteid=delete_record('tbl_product_party_purchase', "party_purchase_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// JOB Party Purchase
		
		else if(strtolower($POST['mode']) == "add_job_party_purchase") {
			
			
			$info1['job_party_id']= $POST['party_id'];
			$info1['job_party_process_id']= $POST['job_party_process_id'];
			$info1['job_party_rate']= $POST['party_rate'];
			$info1['job_party_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_job_party_purchase';$tableid='job_party_purchase_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_job_party_purchase") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.l_name,proc.process_name from tbl_product_job_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.job_party_id 
				left join process_mst as proc on proc.process_id=mst.job_party_process_id
				where mst.job_party_product='$POST[product_id]' order by mst.job_party_purchase_id Desc";
			}
			else{
				$query="select mst.*,p.l_name,proc.process_name from tbl_product_job_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.job_party_id
				left join process_mst as proc on proc.process_id=mst.job_party_process_id
				where mst.user_id=".$_SESSION['user_id']." and mst.job_party_product='0' order by mst.job_party_purchase_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Process</th>
							<th width="20%" class="text-center">Party</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['process_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['l_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['job_party_rate'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_job_party_purchase('.$rel['job_party_purchase_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_job_party_purchase('.$rel['job_party_purchase_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
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
		
		else if(strtolower($POST['mode'])== "preedit_job_party")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_job_party_purchase WHERE job_party_purchase_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_job_data_party")
		{
			
			$deleteid=delete_record('tbl_product_job_party_purchase', "job_party_purchase_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Product Parameter
		
		else if(strtolower($POST['mode']) == "add_param_value") {
			
			
			$info1['param_id']= $POST['param_id'];
			$info1['param_value']= $POST['param_value'];
			$info1['product_id']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			
			$table='tbl_product_parameter';$tableid='pr_param_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_product_param") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.p_name from tbl_product_parameter as mst 
				left join tbl_qc_param as p on p.p_id=mst.param_id where mst.product_id='$POST[product_id]'";
			}
			else{
				$query="select mst.*,p.p_name from tbl_product_parameter as mst 
				left join tbl_qc_param as p on p.p_id=mst.param_id where mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' ";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Parameter</th>
							<th width="10%" class="text-center">Value</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['p_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['param_value'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_product_param('.$rel['pr_param_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_param('.$rel['pr_param_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
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
		
		else if(strtolower($POST['mode'])== "preedit_param")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_parameter WHERE pr_param_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_param")
		{
			
			$deleteid=delete_record('tbl_product_parameter', "pr_param_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
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
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				
				//Product Opening to process Allocation set 
				
				/*$q1=$dbcon->query("select p_id from tbl_allocate_process where p_ref_id='$POST[edit_id]'");
				$count1=mysqli_num_rows($q1);
				
				$process=get_product_process($dbcon,$POST['pid']);
						
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
					update_record('tbl_allocate_process',$info6,"p_ref_id=".$POST['edit_id'] , $dbcon);	
				}
				else
				{
					$inserid_alloc=add_record('tbl_allocate_process', $info6, $dbcon);
				}
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_product_process") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.product_id='$POST[product_id]'";
			}
			else{
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' ";
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
			
			$deleteid=delete_record('tbl_product_process', "pr_process_id=$POST[eid]", $dbcon);

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