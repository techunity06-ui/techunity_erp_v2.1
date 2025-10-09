<?php
session_start();
$AJAX = true;
include("../../config/config.php");
error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
	if(strtolower($POST['mode']) == "fetch") {
			$bulkAccessArray = canCheckPermissionAccess($dbcon, [
					INDENT_APPROVE
			]);

			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			//$branch=$_SESSION['branch_id'];
			$where='';

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);
			$where.=" $where_db and po.company_id=".$_SESSION['company_id'];

			$appData = array();
			$i=1;
			$aColumns = array('po.indent_no','po.indent_date','po.rp_po_qty','unit.unit_name','spro.po_req_no','used_qty','pmst.product_name','tc.cat_name','po.rp_id','bms.branch_name','po.indent_status','po.branch_id','po.shortclose_qty');
			$sIndexColumn = "po.rp_id";
			$isWhere = array("po.indent_status in (".$POST['po_type_status'].")".$where);
			$sTable = "tbl_request_product as po";			
			$isJOIN = array('left join tbl_set_main_process as spro on spro.sp_id=po.sp_id','left join product_mst as pmst on pmst.product_id=po.rp_pid','left join tbl_category as tc on pmst.product_category=tc.cat_id','left join branch_mst as bms on bms.branch_id=po.branch_id','left join unit_mst as unit on unit.unitid=po.purchase_unit','left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0  group by req.rp_id) as rereq on rereq.rp_id=po.rp_id');
			$hOrder = "po.rp_id desc";
			$hGroupby = array("po.rp_id");
			$having_clause='';
			include('../../include/pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				$max_approve_qty=round($row['rp_po_qty'],4)-$row['used_qty']-$row['shortclose_qty'];
				$row_data[] = $id;
				$row_data[] = $row['indent_no'];
				$row_data[] = date('d M, Y',strtotime($row['indent_date']));
				$row_data[] = $row['po_req_no'];
				$row_data[] = $row['product_name'];
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				if($row['branch_id'] == '10000'){
					$row_data[] = 'All Branch';
				}else{
					$row_data[] = $row['branch_name'];
				}
				$row_data[] = $row['rp_po_qty'];
				$row_data[] = $max_approve_qty;
				$row_data[] = $row['shortclose_qty'];
				$row_data[] = $row['unit_name'];
				
				$add_po_btn = '';$done = '';
				if(in_array(INDENT_APPROVE,$bulkAccessArray)){
					if($row['indent_status'] == '1'){
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Approve Indent" data-toggle="tooltip" data-placement="top" href="'.ROOT.'indent_approve/'.$row['rp_id'].'"><i class="fa fa-plus"></i></a>';
						
						$shortclose='<a onclick="shortcloseindent('.$row['rp_id'].','.$max_approve_qty.')" class="btn btn-xs btn-danger" data-original-title="Sort Close Indent" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i></a>';
					}
					else{$shortclose='';}
					
					if($row['indent_status'] == '3'){
						$done='<button type="button" class="btn btn-sm btn-success" data-original-title="Indent Done" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i> Done</button>';
						if($row['shortclose_qty'] != '0'){
							$reason ='<a onclick="shortclosereason('.$row['rp_id'].')" class="btn btn-xs btn-info" data-original-title="Sort Close Reason" data-toggle="tooltip" data-placement="top"><i class="fa fa-info"></i></a>';
						}
						else{
							$reason='';
						}
					}
					else{
						$reason='';
					}
				}
					
				$row_data[] = $add_po_btn." ".$done." ".$shortclose." ".$reason;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$approve_no=load_common_no($dbcon,18);
			update_common_no($dbcon,18);
			$info['approve_no']					= $approve_no;
			$info['approve_date']				= date("Y-m-d");
			$info['rp_id']						= $POST['work_order_id'];
			$info['approve_qty']				= $POST['approve_qty'];
			$info['approve_unit']				= $POST['unit_id'];
			$info['delivery_date']				= date("Y-m-d H:i:s");
			$info['quotation_requirement']		= $POST['quotation_requirement'];
			$info['cdate']						= date("Y-m-d H:i:s");
			$info['user_id']					= $_SESSION['user_id'];
			$info['company_id']					= $_SESSION['company_id'];
			
			
			$inserpoid=add_record('approve_indent', $info, $dbcon, $branch_id);
			
			if($POST['max_approve_qty']==$POST['approve_qty']){
				$inftrn['indent_status'] = 3;
				$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$POST['work_order_id'] , $dbcon, $branch_id);
			}
			
			$query_used="select * from tbl_request_product as rpro
				where rp_id=".$POST['work_order_id']." and company_id = '".$_SESSION['company_id']."' ";
			$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
		
			
			$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
			
				$total=$POST['approve_qty']*$rate;
				
				$infpotrn['purchaseorder_id']	= '0';
				$infpotrn['product_type']		= '';
				$infpotrn['product_id']			= $rel_used['rp_pid'];
				$infpotrn['product_qty']		= $POST['approve_qty'];
				$infpotrn['product_rate']		= $rate;
				$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
				//$infpotrn['unit_id']			= get_pro_field($dbcon,$pr_id,'product_base_unit');
				$infpotrn['unit_id']			= $POST['unit_id'];
				$infpotrn['product_amount']		= $total;
				$infpotrn['total']				= $total;
				$infpotrn['parent_pro']			= 0;
				$infpotrn['main_pro_status']	= 1;//Requested products
				$infpotrn['user_id']			= $_SESSION['user_id'];
				$infpotrn['po_ref_id']			= $POST['work_order_id'];
				$infpotrn['po_ref_type']		= '0';
				$infpotrn['po_bom_id']			= '';
				$infpotrn['po_bom_trn_id']		= '';
				$infpotrn['mdate']				= date('Y-m-d');
				$infpotrn['company_id']			= $_SESSION['company_id'];
				
			
			if($info['quotation_requirement']==0){
				$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $branch_id);
			}
				
			if($inserpoid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	
			
		}
		else if(strtolower($POST['mode']) == "sortclose_indent") {
			$info['shortclose_qty'] 	= $POST['pending_qty'];
			$info['shortclose_remark'] 	= $_POST['remark'];
			$info['indent_status'] 		= 3;
			
			$updateid=update_record('tbl_request_product', $info,"rp_id=".$POST['rp_id'] , $dbcon, $branch_id);
			
			if($updateid){
				$row['msg']	= 1;
			}else{
				$row['msg']	= 0;
			}
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_reason_short_close") {
			$query = "select shortclose_remark from tbl_request_product where rp_id=".$POST['id'];
			$q = $dbcon->query($query);
			$r = mysqli_fetch_array($q);
			echo json_encode($r);
		}
?>