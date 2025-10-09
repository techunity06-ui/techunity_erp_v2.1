<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PO_QUOTATION_VIEW, PO_QUOTATION_ADD, PO_QUOTATION_READ, PO_QUOTATION_UPDATE, PO_QUOTATION_DELETE, PO_QUOTATION_APPROVE, PO_QUOTATION_FINAL_APPROVE
		]);

		$companyConfiguration=getCompanyConfiguration($dbcon);
		$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		//$branch=$_SESSION['branch_id'];
		    $where=' and quotation_requirement=1';

			if($POST['po_type_status']==2){
				$where.=" and quotation_approve_status=0";
			}else if($POST['po_type_status']==3){
				$where.=" and quotation_approve_status=1";
			}
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('apo', $branch_id);
			$where.=" $where_db ";

			$where_company=check_company('apo');

			$where.=" $where_company";

			$where_user=check_user('apo');

			$where.=" $where_user";

			
			$appData = array();
			$i=1;
			$aColumns = array('apo.approve_no','apo.approve_date','apo.approve_qty','po.indent_no','delivery_date','po.indent_date','po.rp_po_qty','unit.unit_name','spro.po_req_no','pmst.product_name','pmst.product_icode', 'dr.drawing_number', 'pmst.product_alias_name','tc.cat_name','po.rp_id','apo.approve_indent_id', 'apo.quotation_approve_status', 'bms.branch_name','us.user_name');
			$sIndexColumn = "apo.approve_indent_id";
			$isWhere = array("apo.approve_indent_status=0 ".$where);
			$sTable = "approve_indent as apo";			
			$isJOIN = array('left join tbl_request_product as po on po.rp_id=apo.rp_id','left join tbl_set_main_process as spro on spro.sp_id=po.sp_id','left join branch_mst as bms on bms.branch_id=apo.branch_id','left join product_mst as pmst on pmst.product_id=po.rp_pid','left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id','left join tbl_category as tc on pmst.product_category=tc.cat_id','left join unit_mst as unit on unit.unitid=apo.approve_unit','left join users as us on us.user_id=apo.user_id');
			$hOrder = "apo.approve_indent_id desc";
			$hGroupby = array("apo.approve_indent_id");
			include($include.'pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();

				if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
		        if(in_array('alias',$pro_search)){
		            $alias = " -- (".$row['product_alias_name'].")";
		        }

				$row_data[] = $id;
				$row_data[] = $row['approve_no'].'<br>'.date('d M, Y',strtotime($row['approve_date']));
				$row_data[] = $row['indent_no'].'<br>'.date('d M, Y',strtotime($row['indent_date']));
				$row_data[] = $row['po_req_no'];
				$row_data[] = $row['product_name']." ".$drawing_number." ".$item_code." ".$alias;
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['approve_qty'];
				$row_data[] = $row['unit_name'];
				$row_data[] = date('d M, Y',strtotime($row['delivery_date']));
				
				$query="select count(po_quotation_id) as cquo from po_quotation as rpro
						where po_quotation_status in (0,1) and approve_indent_id=".$row['approve_indent_id'];
				$rel=mysqli_fetch_assoc($dbcon->query($query));
				
				$row_data[] = $rel['cquo'];
				$row_data[] = $row['user_name'];
				$add_po_btn=''; $view_quo=''; $approve_po_btn=''; 
				if(in_array(PO_QUOTATION_ADD,$bulkAccessArray)){
					if($row['quotation_approve_status']=='0'){
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="ADD Quotation" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'po_quotation_add/'.$row['approve_indent_id'].'"><i class="fa fa-plus"></i></a>';
					}
				}
				if(in_array(PO_QUOTATION_VIEW,$bulkAccessArray)){
					$view_quo = '<a class="btn btn-xs btn-warning" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'po_quotation_vendor_list/'.$row['approve_indent_id'].'"><i class="fa fa-eye"></i></a>';
				}
				if(in_array(PO_QUOTATION_APPROVE,$bulkAccessArray)){
					$approve_po_btn='<a class="btn btn-xs btn-success btn-flat" data-original-title="Approve" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'po_quotation_approve/'.$row['approve_indent_id'].'"><i class="fa fa-plus">Approve</i></a>';
				}	
				$row_data[] = $add_po_btn.' '.$view_quo.' '.$approve_po_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$info['approve_indent_id']	= $POST['approve_indent_id'];
			$info['vender_id']			= $POST['vender_id'];
			$info['quotation_no']		= $POST['quotation_no'];
			$info['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
			$info['delivery_date']		= date('Y-m-d',strtotime($POST['delivery_date']));
			$info['payment_days']		= $POST['payment_days'];
			$info['product_rate']		= $POST['product_rate'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			
			$inserpoid=add_record('po_quotation', $info, $dbcon, $branch_id);
			
			if($inserpoid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			$arr['redirect'] = 'po_quotation_list'; 
			echo json_encode($arr);	
			
		}
		else if(strtolower($POST['mode']) == "vendor_quotation_edit") {
			
			$info['quotation_no']	= $POST['quotation_no'];
			$info['quotation_date']	=date('Y-m-d',strtotime($POST['quotation_date']));
			$info['delivery_date']	= date('Y-m-d',strtotime($POST['delivery_date']));
			$info['payment_days']				= $POST['payment_days'];
			$info['product_rate']				= $POST['product_rate'];
			$info['user_id']					= $_SESSION['user_id'];
			$info['company_id']					= $_SESSION['company_id'];
			
			
			$updateid=update_record('po_quotation', $info,"po_quotation_id=".$POST['po_quotation_id'] , $dbcon);
			
			if($updateid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			$arr['redirect'] = 'po_quotation_vendor_list/'.$POST['approve_indent_id']; 
			echo json_encode($arr);	
			
		}
		else if(strtolower($POST['mode']) == "vendor_quotation_delete") {
			$info['po_quotation_status'] = 3;
			$updateid=update_record('po_quotation', $info,"po_quotation_id=".$POST['id'] , $dbcon);
			
			if($updateid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			$arr['redirect'] = 'po_quotation_vendor_list/'.$POST['aid']; 
			echo json_encode($arr);	
			
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
				if($POST['pro_type']==1){
					$ord= "order by mst.product_rate ASC";
				}else if($POST['pro_type']==2){
					$ord= "order by mst.payment_days DESC";
				}else if($POST['pro_type']==3){
					$ord= "order by mst.delivery_date ASC";
				}
				$query="select mst.*,cat.l_name,ai.rp_id from po_quotation as mst
					left join tbl_ledger as cat on cat.l_id=mst.vender_id 
					left join approve_indent as ai on mst.approve_indent_id=ai.approve_indent_id 
					where mst.po_quotation_status!=2 and mst.approve_indent_id=".$POST['eid']." ".$ord;

				/*
				Code By Umair: 08/12/2020
				Comment: Get the approve quotation count
				*/

				$approve_query="select mst.*,cat.l_name,ai.rp_id from po_quotation as mst
					left join tbl_ledger as cat on cat.l_id=mst.vender_id 
					left join approve_indent as ai on mst.approve_indent_id=ai.approve_indent_id 
					where mst.po_quotation_status=1 and mst.approve_indent_id=".$POST['eid']." ".$ord;	
				$approve_result=$dbcon->query($approve_query);
				$approve_count = mysqli_num_rows($approve_result);
				$approve_rel=mysqli_fetch_assoc($approve_result);
				
				/*
				Code By Umair: 08/12/2020
				Comment: Check quotation created or not
				*/
				
				$check_quo_sql = "select * from  tbl_purchaseordertrn where po_ref_id='".$approve_rel['rp_id']."'";
				$quo_result=$dbcon->query($check_quo_sql);
				$quo_count = mysqli_num_rows($quo_result);

			
				$result=$dbcon->query($query);
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="5%">SR. NO.</th>
							<th class="text-center" width="25%">Vendor Name</th>
							<th class="text-center"width="10%">Quotation No</th>
							<th class="text-center"width="10%">Quotation Date</th>
							<th class="text-center"width="10%">Delivery date</th>
							<th class="text-center"width="6%">Payment Days</th>
							<th class="text-center"width="8%">Rate</th>
							<th class="text-center"width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				
			 echo '<tr  >
					<td>'.$i.'</td>
					<td style="vertical-align:top;">
						<b>'.$rel['l_name'].'</b>
					</td>
					
					<td style="vertical-align:top;" class="text-center">
						'.$rel['quotation_no'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.date('d M, Y',strtotime($rel['quotation_date'])).'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['delivery_date'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['payment_days'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_rate'].'
					</td>';
					if($rel['po_quotation_status']==1){
						echo '<td>
						<button type="button" class="btn btn-xs btn-success" data-original-title="Approve Quotation Done" data-toggle="tooltip" data-placement="top" ><i class="fa fa-check"></i>Approve Done</button>';

						if($quo_count<=0){
							echo '<button type="button" class="btn btn-xs btn-info" data-original-title="Disapprove" data-toggle="tooltip" data-placement="top" onclick="disapprove_po_status('.$rel['po_quotation_id'].',2)"><i class="fa fa-check"></i>Disapprove</button>
							</td>';
						}
					}else{
						if($approve_count<=0){
							echo '<td>
							<button type="button" class="btn btn-xs btn-info" data-original-title="Approve Quotation" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status('.$rel['po_quotation_id'].',2)"><i class="fa fa-check"></i>Approve</button>
							</td>';
						}
					}
					
				echo '</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
		
			echo '</table>			 
					</div></div>';
		}
		else if(strtolower($POST['mode']) == "app_quo") {
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$com="select * from po_quotation where po_quotation_id=".$POST['eid'];
			$comty=mysqli_fetch_assoc($dbcon->query($com));
			
			$coma="select * from po_quotation where po_quotation_status=1 and po_quotation_id=".$comty['po_quotation_id'];
			$comtya=mysqli_fetch_assoc($dbcon->query($coma));
			
			if(!empty($comtya['po_quotation_id'])){
				$info21['po_quotation_status']	= 0;
				$updateid=update_record('po_quotation', $info21,"po_quotation_id=".$comtya['po_quotation_id'] , $dbcon, $branch_id);
				
				$info221['purchaseordertrn_status']	= 2;
				$updateid=update_record('tbl_purchasetrntemp', $info221,"purchaseordertrn_status=0 and po_quotation_id=".$comtya['po_quotation_id'] , $dbcon, $branch_id);
				
			}
			$info['po_quotation_status']	= 1;
			$updateid=update_record('po_quotation', $info,"po_quotation_id=".$POST['eid'] , $dbcon, $branch_id);
			
			$info1['quotation_approve_id']		= $POST['eid'];
			$info1['quotation_approve_status']	= 1;
			$updateid=update_record('approve_indent', $info1,"approve_indent_id=".$comty['approve_indent_id'] , $dbcon, $branch_id);
			
			$query_used1="select * from approve_indent as rpro
				where approve_indent_id=".$comty['approve_indent_id'];
			$rel_used1=mysqli_fetch_assoc($dbcon->query($query_used1));	
		
		
			$query_used="select * from tbl_request_product as rpro
				where rp_id=".$rel_used1['rp_id'];
			$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
		
			$total=$rel_used1['approve_qty']*$comty['product_rate'];
			
			$infpotrn['purchaseorder_id']	= '0';
			$infpotrn['product_type']		= '';
			$infpotrn['product_id']			= $rel_used['rp_pid'];
			$infpotrn['product_qty']		= $rel_used1['approve_qty'];
			$infpotrn['product_base_qty']	= $rel_used1['approve_base_qty'];
			$infpotrn['product_rate']		= $comty['product_rate'];
			$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
			$infpotrn['unit_id']			= $rel_used1['approve_unit'];
			$infpotrn['product_amount']		= $total;
			$infpotrn['total']				= $total;
			$infpotrn['parent_pro']			= 0;
			$infpotrn['main_pro_status']	= 1;//Requested products
			$infpotrn['user_id']			= $_SESSION['user_id'];
			$infpotrn['po_ref_id']			= $rel_used1['rp_id'];
			$infpotrn['po_ref_type']		= '0';
			$infpotrn['po_bom_id']			= '';
			$infpotrn['po_bom_trn_id']		= '';
			$infpotrn['mdate']				= date('Y-m-d');
			$infpotrn['po_quotation_id']	= $POST['eid'];
			$infpotrn['company_id']			= $_SESSION['company_id'];
			
			$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $rel_used['branch_id']);
			
		}

		else if(strtolower($POST['mode']) == "disapprove_quo") {
			$com="select * from po_quotation where po_quotation_id=".$POST['eid'];
			$comty=mysqli_fetch_assoc($dbcon->query($com));
			
			$coma="select * from po_quotation where po_quotation_status=1 and po_quotation_id=".$comty['po_quotation_id'];
			$comtya=mysqli_fetch_assoc($dbcon->query($coma));
			
			if(!empty($comtya['po_quotation_id'])){
				$info21['po_quotation_status']	= 0;
				$updateid=update_record('po_quotation', $info21,"po_quotation_id=".$comtya['po_quotation_id'] , $dbcon);
				
				$info221['purchaseordertrn_status']	= 2;
				$updateid=update_record('tbl_purchasetrntemp', $info221,"purchaseordertrn_status=0 and po_quotation_id=".$comtya['po_quotation_id'] , $dbcon);
				
			}
			$info['po_quotation_status']	= 0;
			$updateid=update_record('po_quotation', $info,"po_quotation_id=".$POST['eid'] , $dbcon);
			
	
			$info1['quotation_approve_id']		= 0;
			$info1['quotation_approve_status']	= 0;
			$updateid=update_record('approve_indent', $info1,"approve_indent_id=".$comty['approve_indent_id'] , $dbcon);

			$delsql = "DELETE FROM `tbl_purchasetrntemp` WHERE po_quotation_id = '".$POST['eid']."' and product_rate = '".$comty['product_rate']."' ";
			$dbcon->query($delsql);
			
		}
		
?>