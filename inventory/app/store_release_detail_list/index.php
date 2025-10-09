<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');


		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(brp_strtolower($POST['mode']) == "fetch_working") {
			
			
			$str='';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Godown Name</th>
				<th>Qty</th>
				<th>Returnable</th>';

				if($_SESSION['branch_id']==0){
					$str.='<th>Branch Name</th>';
				}
				$str.='<th>Action</th>
				
			</tr>';
			
			 $s_ql = "select release_trn_id,release_qty,gd_name,branch.branch_name,p.product_name,p.product_icode,returnable from tbl_store_release_trn as tsr
			left join product_mst as p on p.product_id=tsr.product_id 
			left join branch_mst as branch on branch.branch_id=tsr.branch_id
			left join mst_godown as gd on gd.gd_id=tsr.godown_id
			where tsr.release_status = 3 and tsr.company_id=".$_SESSION['company_id'];

			$q=$dbcon->query($s_ql);
			$count=brp_mysqli_num_rows($q);
			// echo $s_ql;
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				// if(in_array(PRODUCTION_BOM_LIST_SLUG_DELETE,$bulkAccessArray)){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_material_stock('.$rel['release_trn_id'].')"><i class="fa fa-trash-o"></i></button>';
				// }
				
				// if(in_array(PRODUCTION_BOM_LIST_SLUG_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_data('.$rel['release_trn_id'].')"><i class="fa fa-pencil"></i></a>';
				// }
					
					
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';
					$returnable = 'No';	
					if($rel["returnable"]=='1'){
						$returnable = 'Yes'; 
					}
					
					$str.='<tr>
							<th>'.$cnt.'</th>
							<th>'.$rel['product_name'].' -- ('.$rel['product_icode'].')'.'</th>
							<th>'.$rel['gd_name'].'</th>
							<th>'.$rel['release_qty'].'</th>
							<th>'.$returnable.'</th>';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							$str.='<th>'.$edit .' '.$delete .'</th>
						</tr>';
						$cnt++;
						$datacheck=1;
				}
			
			if($datacheck!=1){
				$str.= '<tr><td colspan="9"> <center>No Process Found!!!!!</center></td></tr>';
			}
			
			$arr['html'] = $str;
			$arr['count'] = $count;
			echo json_encode($arr);
		}
		else if(brp_strtolower($POST['mode']) == "load_productdata") {
		
		$pid=$POST['product_id'];
		
		$sel=$dbcon->query("select m.*,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from product_mst as m 
			left join unit_mst as bunit on bunit.unitid=m.product_base_unit
			left join unit_mst as cunit on cunit.unitid=m.product_conv_unit

		left join mst_material_spec as s on m.product_specification=s.ms_id where product_id='$pid'"); // s.m_type_density,
		$row=brp_mysqli_fetch_assoc($sel);

		
		echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "convert_qty")
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
			
			$ret_qty_new=number_format($ret_qty, 3, ".", "");
			
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode']) == "get_stock") {

			$cstock=get_current_stock_new_data($dbcon,$POST["product_id"],$POST["unit_id"],$POST["godown_id"]);
			$rstock=reserve_stock_data($dbcon,$POST["product_id"],$POST["unit_id"],"","","","","",0,$POST["godown_id"]);
			
			
			$actualstock=$cstock-$rstock;

			echo $actualstock;

		}else if(brp_strtolower($POST['mode']) == "add_field") {

			$edit_id = $_POST['edit_id'];
			$where = "";
			if($edit_id !=""){
				$where = " and release_trn_id != " .$edit_id;
			}
		$tr = $dbcon -> query("SELECT `release_trn_id` FROM `tbl_store_release_trn` WHERE  `product_id` ='".$POST['product_id']."' and release_status= 3 and company_id='".$_SESSION['company_id']."'" . $where);
		if($tr->num_rows > 0) {
			$arr['msg'] = "-1";
		}else{

				$info['product_id'] = $POST['product_id'];
				$info['release_qty'] = $POST['base_qty'];
				$info['release_unit'] = $POST['unit_id'];
				$info['release_conv_qty'] = $POST['conv_qty'];
				$info['release_conv_unit'] = $POST['conv_unit'];
				$info['returnable'] = $POST['returnable'];
				$info['release_status'] =3;
				$info['godown_id'] = $POST['godown_id'];
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['branch_id']		= $_SESSION['branch_id'];
				$info['company_id']			= $_SESSION['company_id'];

				$msg = "";
				if(!empty($edit_id)){
					$msg ="update";
					$inserestimateid=update_record('tbl_store_release_trn', $info, "release_trn_id=".$edit_id , $dbcon);	

				}else{		

					$inserestimateid=add_record('tbl_store_release_trn', $info, $dbcon);
					$msg = "1";
				}
								
			if($inserestimateid){	
				
				$arr['msg']= $msg;
			}
			else{
				$arr['msg']="0";
			}
		}
			echo json_encode($arr);

		}
		else if(brp_strtolower($POST['mode']) == "delete") {
	
				$info['release_status']	= 2;
				$updateestimateid=update_record('tbl_store_release_trn', $info, "release_trn_id=".$POST['eid'], $dbcon);	
				
				if($updateestimateid){
					echo "1";	
				}else{
					echo "0";
				}
			
		}
		else if(brp_strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_store_release_trn where release_trn_id = '$POST[id]'");

			$r = $q->fetch_assoc();
		
			echo json_encode($r);
		}
		
		else if(brp_strtolower($POST['mode'])== "release_stock_material")
		{
				
				$info['to_user_id'] = $POST['user_id'];
				$info['to_branch_id'] = $POST['branch_id'];
				$info['issue_no'] = $POST['issue_no'];
				$info['issue_date']	= date('Y-m-d',strtotime($POST['issue_date']));
				$info['release_type'] = 1;
				$info['release_status'] = 0;
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['branch_id'] = $_SESSION['branch_id'];
				$inserestimateid=add_record('tbl_store_release', $info, $dbcon);
				$msg = "1";
		
								
			if($inserestimateid){	
				$update_info['release_id'] = $inserestimateid;
				$update_info['release_status'] = 0;
				$uid = update_record('tbl_store_release_trn', $update_info, "release_status=3", $dbcon);	
				update_issue_no($dbcon);
				$arr['msg']= $msg;
			}
			else{
				$arr['msg']="0";
			}
		
			echo json_encode($arr);
		}


?>