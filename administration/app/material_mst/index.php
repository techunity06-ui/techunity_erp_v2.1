<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			//check permission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_MSPEC_UPDATE,
		        ADMINISTRATOR_MSPEC_DELETE
		    ]);
		    $branch_id = $POST['branch_id'];
			$where='';
		    
		    if($branch_id != '1000'){
	        $where .= check_branch('mstmat',$branch_id);
	    }
	    if($branch_id == ""){
	    	 $output = array(
		        "sEcho" => 1,
		        "iTotalRecords" => 0,
		        "iTotalDisplayRecords" => 0,
		        "aaData" => array()
		    );
	     	
	     	echo json_encode( $output );
	     }else{
		 
			$appData = array();
			$i=1;
			$aColumns = array('mstmat.ms_name','mstmat.ms_id','mstmat.formula');
			$sIndexColumn = "mstmat.ms_id";
			$isWhere = array("mstmat.ms_status = 0 and mstmat.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "mst_material_spec as mstmat";			
			$isJOIN = array();
			$hOrder = "mstmat.ms_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['ms_name'];

				/*$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
				$rs_parameter=$dbcon->query($param_sql);	
				while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
					$material_parameter_id = $rel_param['material_parameter_id'];
					$param_trn_sql = "select * from mst_material_spec_trn where material_parameter_id = '".$material_parameter_id."' and ms_id='".$row['ms_id']."' ";
					$rs_exec=$dbcon->query($param_trn_sql);	
					$rel_data=brp_mysqli_fetch_assoc($rs_exec);
					if($rel_data['material_parameter_value']){
						$row_data[] = $rel_data['material_parameter_value'];
					}else{
						$row_data[] = '';
					}
					
				}	*/
				$row_data[] = $row['formula'];
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(ADMINISTRATOR_MSPEC_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('.$row['ms_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_MSPEC_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$row['ms_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "add_model") {
			$mode = $POST['mode'];
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT * FROM `mst_material_spec` WHERE `ms_name` ='".$POST['ms_name']."' and `ms_status`='0' and `company_id`='".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				$resp['msg'] = "-1";
			} else {
				$info['ms_name']	= $POST['m_type_name'];							
				/*$info['m_type_width']	= $POST['m_type_width'];							
				$info['m_type_height']	= $POST['m_type_height'];							
				$info['m_type_thick']	= $POST['m_type_thick'];							
				$info['m_type_density']	= $POST['m_type_density'];*/						
				$info['formula']		= $POST['formula'];				
				$info['cdate']		= date("Y-m-d H:i:s");
				
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$inserid=add_record('mst_material_spec', $info, $dbcon, $branch_id);
				if($inserid)
				{	
					unset($POST['mode']);
					unset($POST['m_type_name']);
					unset($POST['branch_id']);
					unset($POST['formula']);
					foreach ($POST as $key => $val){

						$info1['ms_id'] = $inserid;
						$info1['material_parameter_id'] = str_replace('param_', '', $key);
						$info1['material_parameter_value'] = $val;
						$info1['cdate']		= date("Y-m-d H:i:s");
						$info1['user_id']	= $_SESSION['user_id'];
						$info1['company_id']	= $_SESSION['company_id'];
						add_record('mst_material_spec_trn', $info1, $dbcon, $branch_id);
					}
					
					if(strtolower($mode) == "add")
					{
						$resp['msg'] = "1";
					}
					else
					{
						$zone_qry="select * from mst_material_spec where ms_id=".$inserid; 
						$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
						$resp=$zone_rel;
						$resp['msg'] = "2";
					}
				}
				else
				{
					$resp['msg'] = "0";
				}
			}
			
			echo json_encode($resp);
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `mst_material_spec` WHERE `ms_id` = '$POST[id]'");
			$r = $q->fetch_assoc();

			$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
			$rs_parameter=$dbcon->query($param_sql);	
			while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
				$material_parameter_id = $rel_param['material_parameter_id'];
				$param_trn_sql = "select * from mst_material_spec_trn where material_parameter_id = '".$material_parameter_id."' and ms_id='".$r['ms_id']."' ";
				$rs_exec=$dbcon->query($param_trn_sql);	
				$rel_data=brp_mysqli_fetch_assoc($rs_exec);
				if($rel_data['material_parameter_value']){
					$r['param']['edit_param_'.$material_parameter_id] = $rel_data['material_parameter_value'];
				}else{
					$r['param']['edit_param_'.$material_parameter_id] = '';
				}
				
			}
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			
			$eid = $POST['eid'];
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `ms_id`,`ms_name`,`ms_status`,`company_id` FROM `mst_material_spec` WHERE `ms_name` ='".$POST['e_m_type_name']."' and `ms_status`='0' and `ms_id` != '".$POST['eid']."' and `company_id`='".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				echo "-1";
			} else {
				$info['ms_name']	= $POST['e_m_type_name'];
				/*$info['m_type_width']	= $POST['e_m_width'];							
				$info['m_type_height']	= $POST['e_m_height'];							
				$info['m_type_thick']	= $POST['e_m_thick'];							
				$info['m_type_density']	= $POST['e_m_density'];			*/	
				$info['formula']		= $POST['edit_formula'];
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('mst_material_spec', $info,"ms_id=".$POST['eid'] , $dbcon, $branch_id);

				if($updateid){

					unset($POST['mode']);
					unset($POST['edit_formula']);
					unset($POST['e_m_type_name']);
					unset($POST['branch_id']);
					unset($POST['edit_id']);
					unset($POST['eid']);
					unset($POST['token']);
					$dbQuery = "delete from mst_material_spec_trn Where ms_id='".$eid."'";	
					$dbcon->query($dbQuery);
					
					foreach ($POST as $key => $val){
						$info1['ms_id'] = $eid;
						$info1['material_parameter_id'] = str_replace('edit_param_', '', $key);
						$info1['material_parameter_value'] = $val;
						$info1['cdate']		= date("Y-m-d H:i:s");
						$info1['user_id']	= $_SESSION['user_id'];
						$info1['company_id']	= $_SESSION['company_id'];
						
						add_record('mst_material_spec_trn', $info1, $dbcon, $branch_id);
					}
					
					echo "1";
				}
				else{
					echo "0".$dbcon->error;
				}
			}
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				$info['ms_status']='2';
				$updateid=update_record('mst_material_spec', $info,"ms_id=".$POST['eid'] , $dbcon);
				$dbQuery = "delete from mst_material_spec_trn Where ms_id='".$POST['eid']."'";	
				$dbcon->query($dbQuery);
				if($updateid)
					echo "1";
				else
					echo "0";
			
		}
		else if(strtolower($POST['mode']) == "get_all_product") {
			echo getproduct($dbcon,$POST['id']);
		}
		else if(strtolower($POST['mode']) == "get_formula") {
			$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
			$rs_parameter=$dbcon->query($param_sql);
			$html = '';	
			$i=1;
			while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
				$onlick = 'copy_fun("'.$rel_param['material_parameter_code'].'")';
				$html .= "<tr>
							<td>".$i."</td>
							<td>".$rel_param['material_parameter_name']."</td>
							<td><a href='javascript:void(0)' onclick='".$onlick."'>".$rel_param['material_parameter_code']."</td>
							</tr>";
				$i++;				
			}

			echo $html;
		}
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>