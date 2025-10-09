<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
	if(strtolower($POST['mode']) == "fetch") {
			$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PRE_VIEW
			]);

			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			//$branch=$_SESSION['branch_id'];
			$where='';

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('pre', $branch_id);
			$date = " and pre.pre_date between '" . date('Y-m-d', strtotime($s_date[0])) . "' AND '" . date('Y-m-d', strtotime($s_date[1])) . "'";
			$where.=" $where_db and pre.company_id=".$_SESSION['company_id'].$date;

			$appData = array();
			$i=1;
			$aColumns = array('pre.pre_no','pre.pre_date','pre.pre_id','bms.branch_name','pre.branch_id');
			$sIndexColumn = "pre.pre_id";
			$isWhere = array("pre.pre_status=0".$where);
			$sTable = "tbl_pre as pre";			
			$isJOIN = array('left join branch_mst as bms on bms.branch_id=pre.branch_id');
			$hOrder = "pre.pre_id desc";
			$hGroupby = array("pre.pre_id");
			include('../../include/pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;
				$row_data[] = $row['pre_no'];
				$row_data[] = date('d M, Y',strtotime($row['pre_date']));
				
				if($row['branch_id'] == '10000'){
					$row_data[] = 'All Branch';
				}else{
					$row_data[] = $row['branch_name'];
				}
				
				$edit = ''; $delete = '';
				if(in_array(PRE_VIEW,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit Pre" data-toggle="tooltip" data-placement="top" href="'.ROOT.'pre_edit/'.$row['pre_id'].'"><i class="fa fa-pencil"></i></a>';
					
					$inde_c = "select pre.pre_id,trn.pre_trn_id,req.rp_id,used_qty,req.indent_status,round(IFNULL(sum(used_qty),0),4) as preqty from tbl_pre as pre
					left join tbl_pre_trn as trn on trn.pre_id = pre.pre_id
					left join tbl_request_product as req on req.pre_trn_id = trn.pre_trn_id
					left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=req.rp_id 

					where pre.pre_status=0 and pre.user_id=".$_SESSION['user_id']." and pre.pre_id=".$row['pre_id']." Group by pre.pre_id";
					
					
					$inde_q = $dbcon->query($inde_c);
					
					$inde_r = mysqli_fetch_array($inde_q);
					
					//var_dump($inde_c);
					//$a = $inde_c;
					if($inde_r['preqty'] == '0.0000' && $inde_r['indent_status'] !=3){
						$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_row('.$row['pre_id'].');" ><i class="fa fa-times"></i></button>';
					}
				}
					
				$row_data[] = $edit." ".$delete;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_product_dtls"){
			$pro_qry="select pro.product_purchase_rate,unit.unit_name from product_mst as pro 
			left join unit_mst as unit on unit.unitid = pro.product_base_unit
			where pro.product_id=".$POST['product_id'];
			$pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
			echo json_encode($pro_rel);
		}
		else if(strtolower($POST['mode']) == "add_field") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$info1['product_id']		= $POST['product_id'];
			$info1['product_qty']		= $POST['product_qty'];
			$info1['rate']				= $POST['rate'];
			
			
			if(strtolower($POST['vender_id']) == 'new'){
				$info2['l_name']		= $POST['vendor_name'];
				$info2['l_group']		= 37;
				$info2['cdate']			= date("Y-m-d H:i:s");
				$info2['user_id']		= $_SESSION['user_id'];
				$info2['company_id']	= $_SESSION['company_id'];
				
				$indeser_ledger = add_record('tbl_ledger' , $info2, $dbcon, $branch_id);
				
				$qv = "select l_id,l_name from tbl_ledger where l_id=".$indeser_ledger;
				$ve = $dbcon->query($qv);
				$res = mysqli_fetch_array($ve);
				$row = $res;
				$info1['vender_id']		= $indeser_ledger;
				echo json_encode($row);
			}else{
				$info1['vender_id']		= $POST['vender_id'];
			}
			if($_FILES["att_doc"]["name"] !=""){
				$name 					= upload_attch_file($_FILES);
			}else{
				$name 					= $_POST['img_name']; 
			}
			$info1['att_doc']			= $name;
			
			$info['cdate']				= date("Y-m-d H:i:s");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
		   
			$table='tbl_pre_trn';$tableid='pre_trn_id';
			if(!empty($POST['pre_id'])) {
				$info1['pre_id']= $POST['pre_id'];
			}
			else{
				$info1['pre_trn_status']= 3;
			}
			
			if(empty($POST['edit_id'])) {
				$inserid = add_record($table, $info1, $dbcon, $branch_id);
				if(!empty($POST['pre_id'])) {
					$prod_p = "select product_base_unit from product_mst where product_id=".$POST['product_id'];
					$prod_e = $dbcon->query($prod_p);
					$prod_r = mysqli_fetch_array($prod_e);
					
					$indenttrn['indent_no']			= load_common_no($dbcon,17);
					
					$query_invoicetype 	= $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=17 and company_id=".$_SESSION['company_id']);
					
					$indenttrn['indent_date']		= date('Y-m-d');
					$indenttrn['rp_req_date']		= date('Y-m-d');
					$indenttrn['rp_req_qty']		= $POST['product_qty'];
					$indenttrn['purchase_unit']		= $prod_r['product_base_unit'];
					$indenttrn['rp_pid']			= $POST['product_id'];
					$indenttrn['branch_id']			= $branch_id;
					$indenttrn['indent_status']		= 1;
					$indenttrn['rp_req_type']		= "direct";
					
					$indenttrn['sr_no']				= 0;
					$indenttrn['sp_id']				= 0;	
					$indenttrn['req_qty_one']		= 0;
					$indenttrn['rp_po_qty']			= $POST['product_qty'];	
					$indenttrn['in_process_qty']	= 0;				
					$indenttrn['out_process_qty']	= 0;
					$indenttrn['perent_id']			= 0;
					$indenttrn['reserve_stock']		= 0;
					$indenttrn['main_request']		= 1;
					$indenttrn['pre_trn_id']		= $inserid;
					
					$indenttrn['cdate']				= date("Y-m-d H:i:s");;
					$indenttrn['user_id']			= $_SESSION['user_id'];
					$indenttrn['company_id']		= $_SESSION['company_id'];
					
					$indenttid = add_record('tbl_request_product', $indenttrn, $dbcon, $branch_id);
				}
			}
			else {
				$indeedi['rp_req_qty']	= $POST['product_qty'];
				$indeedi['rp_po_qty']	= $POST['product_qty'];
				
				$updateinde = update_record('tbl_request_product', $indeedi,"pre_trn_id=".$POST['edit_id'] , $dbcon, $branch_id);
				$updateid = update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
			}
			/* END */   
		}
		else if(strtolower($POST['mode']) == "show_data") {
			if($POST['pre_id']){
				$query = "select pro.product_name,ven.l_name,mst.* from tbl_pre_trn as mst 
				left join product_mst as pro on pro.product_id=mst.product_id
				left join tbl_ledger as ven on ven.l_id=mst.vender_id
				where mst.pre_trn_status=0 and pre_id=".$POST['pre_id'];
			}else{
				$query = "select pro.product_name,ven.l_name,mst.* from tbl_pre_trn as mst 
				left join product_mst as pro on pro.product_id=mst.product_id
				left join tbl_ledger as ven on ven.l_id=mst.vender_id
				where mst.pre_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
			}
			$data_e = $dbcon->query($query);
			
			$str.='<table class="display table table-bordered table-striped" style="width:100%;">
					<tr>
						<th width="20%" class="text-center">Product Name</th>
						<th width="10%" class="text-center">Product Qty</th>
						<th width="10%" class="text-center">Product Rate</th>
						<th width="2%" class="text-center">Vender</th>
						<th width="5%" class="text-center">Document</th>
						<th width="3%" class="text-center">Action</th>
					</tr>
					<tbody>';
			if(mysqli_num_rows($data_e) > 0 ){
				while($row = mysqli_fetch_array($data_e)){
					$str .='<tr>
						<td>'.$row['product_name'].'</td>
						<td>'.$row['product_qty'].'</td>
						<td>'.$row['rate'].'</td>
						<td>'.$row['l_name'].'</td>
						<td>';
						if($row['att_doc'] != '0'){	
							$str .='<a href="'.ROOT.'/view/upload/pre_prod_doc/'.$row['att_doc'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a></td>';
						}else{
							$str .= '<lable class="btn btn-warning">Not Attached</lable>';
						}
					$str .='<td>';
						
						$inde_c = "select pre.pre_id,trn.pre_trn_id,req.rp_id,used_qty,req.indent_status from tbl_pre as pre
						left join tbl_pre_trn as trn on trn.pre_id = pre.pre_id
						left join tbl_request_product as req on req.pre_trn_id = trn.pre_trn_id
						left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=req.rp_id 

						where pre.pre_status=0 and pre.user_id=".$_SESSION['user_id']." and trn.pre_trn_id=".$row['pre_trn_id'];
						
						$inde_q = $dbcon->query($inde_c);
					
						$inde_r = mysqli_fetch_array($inde_q);
						if(empty($inde_r['used_qty']) &&  $inde_r['indent_status'] !=3){
							$str .='<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$row['pre_trn_id'].');"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$row['pre_trn_id'].');" ><i class="fa fa-times"></i></button>';
						}
						
					$str .='</td>
					</tr>';
				}
			}else{
				$str.= '<tr><td colspan="6" class="text-center">NO DATA FOUND</td></tr>';
			}
				$str.= '</tbody>
				</table>';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "edit_data") {
			$q = $dbcon -> query("SELECT * FROM tbl_pre_trn as trn WHERE pre_trn_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data") {
			$row=array();
			$info['pre_trn_status']=2; 
			$info3['status']=2;
			
			$updateid=update_record('tbl_pre_trn', $info, "pre_trn_id=".$POST['id'] , $dbcon);
			$updateid2=update_record('tbl_request_product', $info3, "pre_trn_id=".$row['pre_trn_id'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$products = get_pre_product($dbcon,'');
			if(empty($products)){
				$arr['msg'] = "2";
			} else {
				$pre_no  		   	= load_common_no($dbcon,21);
				$query_invoicetype 	= $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=21 and company_id=".$_SESSION['company_id']);
				
				$info['pre_no']		= $pre_no;
				$info['pre_date']	= date('Y-m-d',strtotime($POST['pre_date']));	
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];	
				$info['company_id'] = $_SESSION['company_id'];
				$info['branch_id']  = $branch_id;
				
				$inderid = add_record('tbl_pre', $info, $dbcon, $branch_id);
				
				if($inderid){
					$infotrn['pre_id'] = $inderid;
					$infotrn['pre_trn_status'] = 0;
					$updatetrnid=update_record('tbl_pre_trn', $infotrn,"pre_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
				} 
				
				//Indent Manually Add
				$indent_p = "select * from tbl_pre_trn where pre_id=".$inderid;
				$indent_e = $dbcon->query($indent_p);
				while($row = mysqli_fetch_array($indent_e)){
					
					$prod_p = "select * from product_mst where product_id=".$row['product_id'];
					$prod_e = $dbcon->query($prod_p);
					$prod_r = mysqli_fetch_array($prod_e);
					
					
					$indenttrn['indent_no']			= load_common_no($dbcon,17);
					
					$query_invoicetype 	= $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=17 and company_id=".$_SESSION['company_id']);
					
					$indenttrn['indent_date']		= date('Y-m-d',strtotime($POST['pre_date']));
					$indenttrn['rp_req_date']		= date('Y-m-d',strtotime($POST['pre_date']));
					$indenttrn['rp_req_qty']		= $row['product_qty'];
					$indenttrn['purchase_unit']		= $prod_r['product_base_unit'];
					$indenttrn['rp_pid']			= $row['product_id'];
					$indenttrn['branch_id']			= $branch_id;
					$indenttrn['indent_status']		= 1;
					$indenttrn['rp_req_type']		= "direct";
					
					$indenttrn['sr_no']				= 0;
					$indenttrn['sp_id']				= 0;	
					$indenttrn['req_qty_one']		= 0;
					$indenttrn['rp_po_qty']			= $row['product_qty'];	
					$indenttrn['in_process_qty']	= 0;				
					$indenttrn['out_process_qty']	= 0;
					$indenttrn['perent_id']			= 0;
					$indenttrn['reserve_stock']		= 0;
					$indenttrn['main_request']		= 1;
					$indenttrn['pre_trn_id']		= $row['pre_trn_id'];
					
					$indenttrn['cdate']				= date("Y-m-d H:i:s");;
					$indenttrn['user_id']			= $_SESSION['user_id'];
					$indenttrn['company_id']		= $_SESSION['company_id'];
					
					$indenttid = add_record('tbl_request_product', $indenttrn, $dbcon, $branch_id);
				}
				
				if($inderid){
					$arr['msg'] = "1";
				}else{
					$arr['msg'] = "0";
				}
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$info['pre_no']		= $POST['pre_no'];
			$info['pre_date']	= date('Y-m-d',strtotime($POST['pre_date']));	
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];	
			$info['company_id'] = $_SESSION['company_id'];
			$info['branch_id']  = $branch_id;
			
			$updateid = update_record('tbl_pre', $info, "pre_id=".$POST['eid'], $dbcon, $branch_id);
			
			if($updateid){
				$arr['msg'] = "update";
			}else{
				$arr['msg'] = "0";
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['pre_trn_status']=2;  
			$info1['pre_status']=2;
			$info3['status']=2;
			$updateid=update_record('tbl_pre', $info1, "pre_id=".$POST['id'] , $dbcon);
			$updateid1=update_record('tbl_pre_trn', $info, "pre_id=".$POST['id'] , $dbcon);
			
			$pre_trm_p = "select pre_trn_id from tbl_pre_trn where pre_id=".$POST['id'];
			$pre_trm_e = $dbcon->query($pre_trm_p);
			while($row = mysqli_fetch_array($pre_trm_e)){
				$updateid2=update_record('tbl_request_product', $info3, "pre_trn_id=".$row['pre_trn_id'] , $dbcon);
			}
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "has_product") {
			$products = get_pre_product($dbcon,$POST['pre_id']);
			echo ($products) ? json_encode($products) : 0;
		}
	
function upload_attch_file($FILES){
	$rand=rand(0,99999999);
	if(!empty($FILES['att_doc']['tmp_name'])) {
		$temp = explode(".", $FILES["att_doc"]["name"]);
		$extension = strtolower(end($temp));
		$File = "pre_attach".$rand.".".$extension;
		$tmp_name = $FILES["att_doc"]["tmp_name"];
		move_uploaded_file($tmp_name,PRE_UPING.$File);
		return  $File;				
	}
}
?>