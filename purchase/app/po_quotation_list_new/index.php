<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "fetch"){
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where ='';
		//$where="  and pqr.ref_quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND pqr.ref_quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

		$appData = array();
		$i=1;
		$aColumns = array('pqr.quotation_ref_id','pqr.ref_quotation_no','pqr.ref_quotation_date','us.user_name','pqr.approve_status');
		$sIndexColumn = "pqr.quotation_ref_id";
		$isWhere = array("pqr.ref_quotation_status=0 ".$where);
		$sTable = "po_quotation_ref as pqr";			
		$isJOIN = array('left join users as us on us.user_id=pqr.user_id');
		$hOrder = "pqr.quotation_ref_id desc";
		$hGroupby = array("pqr.quotation_ref_id");
		include($include.'pagging.php');
		//echo $sQuery;
		$appData = array();
		$id=1;
		
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$row_data[] = $id;
			$row_data[] = $row['ref_quotation_no'];
			$row_data[] = date('d M, Y',strtotime($row['ref_quotation_date']));
			$row_data[] = $row['user_name'];

			//$edit = '<a href="'.ROOT.'"></a>';

			if($row['approve_status']==1){
				$aprv_btn = '<button type="button" class="btn btn-xs btn-success" data-original-title="Approve Quotation Done" data-toggle="tooltip" data-placement="top" ><i class="fa fa-check"></i>Approve Done</button>';

				
				$disaprv_btn = '<button type="button" class="btn btn-xs btn-info" data-original-title="Disapprove" data-toggle="tooltip" data-placement="top" onclick="disapprove_quotation('.$row['quotation_ref_id'].',2)"><i class="fa fa-check"></i>Disapprove</button>';
			}else{
				
				$aprv_btn = '<button type="button" class="btn btn-xs btn-info" data-original-title="Approve Quotation" data-toggle="tooltip" data-placement="top" onclick="approve_quotation('.$row['quotation_ref_id'].',2)"><i class="fa fa-check"></i>Approve</button>';
				
				$edit = '<a class="btn btn-xs btn-warning" href="'.ROOT.PURCHASE_ROOT.'purchase_quotation/'.$row['quotation_ref_id'].'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
				
				$delete = '<button class="btn btn-xs btn-danger" onclick="delete_quotation()"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
			}

			$row_data[] = $edit.' '.$delete.' '.$aprv_btn.' '.$disaprv_btn;
			
		$appData[] = $row_data;
		$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}else if(strtolower($POST['mode']) == "load_pending_quotation") {
		/*$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PO_QUOTATION_VIEW, PO_QUOTATION_ADD, PO_QUOTATION_READ, PO_QUOTATION_UPDATE, PO_QUOTATION_DELETE, PO_QUOTATION_APPROVE, PO_QUOTATION_FINAL_APPROVE
		]);*/

		$companyConfiguration=getCompanyConfiguration($dbcon);
		$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);

		

		    $where=' and quotation_requirement=1 and used_document=0';

			
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('apo', $branch_id);
			$where.=" $where_db ";

			$where_company=check_company('apo');

			$where.=" $where_company";

			
			$query = "select apo.approve_no, apo.approve_date, apo.approve_qty, po.indent_no, delivery_date, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, pmst.product_name, pmst.product_icode, dr.drawing_number, pmst.product_alias_name, tc.cat_name, po.rp_id, apo.approve_indent_id, apo.quotation_approve_status, bms.branch_name, us.user_name, apo.approve_indent_id
				from approve_indent as apo
				left join tbl_request_product as po on po.rp_id=apo.rp_id
				left join tbl_set_main_process as spro on spro.sp_id=po.sp_id
				left join branch_mst as bms on bms.branch_id=apo.branch_id
				left join product_mst as pmst on pmst.product_id=po.rp_pid
				left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id
				left join tbl_category as tc on pmst.product_category=tc.cat_id
				left join unit_mst as unit on unit.unitid=apo.approve_unit
				left join users as us on us.user_id=apo.user_id
				where apo.approve_indent_status=0 and quotation_approve_status=0 ".$where;
			$result = $dbcon->query($query);
			$cnt = brp_mysqli_num_rows($result);
			$str = '';
			$str.='<div class="col-md-12" style="margin-top:15px"></div>
			<div class="col-md-6"><span><strong>Approove Data Select In '.$cnt.' Out Of <span id="chk_sel_count"></span> </strong> </span></div>
			<div class="col-md-6 text-right">
				<button type="button" class="btn btn-success" onClick="po_quotation_create()"><i class="fa fa-plus" aria-hidden="true"></i> Add
				</button>
			</div>
			<div class="col-md-12" style="margin-top:15px"></div>
			<table class="display table table-bordered table-striped" >
				<thead>
					<tr>
						<th class="text-center"><input type="checkbox" id="all_chk_box" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_all(); updateCounter();"></th>
						<th class="text-center">Approve No</th>
						<th class="text-center">Indent No</th>
						<th class="text-center">WorkOrder No</th>
						<th class="text-center">Product Name</th>
						<th class="text-center">Product Category</th>
						<th class="text-center">Branch Name</th>
						<th class="text-center">Total Qty</th>
						<th class="text-center">Unit</th>
						<th class="text-center">Delivery Date</th>
						<th class="text-center">User Name</th>
					</tr>
				</thead>
				<tbody>';
			if($cnt>0){
				$i=1;
				while($row = brp_mysqli_fetch_array($result)){
					if(in_array('drawing',$pro_search)){
			            $drawing_number = " -- (".$row['drawing_number'].")";
			        }
			        if(in_array('item',$pro_search)){
			            $item_code = " -- (".$row['product_icode'].")";
			        }
			        if(in_array('alias',$pro_search)){
			            $alias = " -- (".$row['product_alias_name'].")";
			        }

			        $cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
					$str .='<tr>
						<td class="text-center"><input type="checkbox" name="che_box[]" class="chk_box" id="che_box'.$i.'" value="'.$row['approve_indent_id'].'" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_box_limit(this.id);updateCounter();"></td>
						<td class="text-center">'.$row['approve_no'].'<br>'.date('d M, Y',strtotime($row['approve_date'])).'</td>
						<td class="text-center">'.$row['indent_no'].'<br>'.date('d M, Y',strtotime($row['indent_date'])).'</td>
						<td class="text-center">'.$row['po_req_no'].'</td>
						<td class="text-center">'.$row['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias.'</td>
						<td class="text-center">'.$cat_name.'</td>
						<td class="text-center">'.$row['branch_name'].'</td>
						<td class="text-center">'.$row['approve_qty'].'</td>
						<td class="text-center">'.$row['unit_name'].'</td>
						<td class="text-center">'.date('d M, Y',strtotime($row['delivery_date'])).'</td>
						<td class="text-center">'.$row['user_name'].'</td>
					</tr>';
					$i++;
				}
			}else{
				$str .='<tr>
					<td colspan="11" class="text-center">No Data Found...!!!</td>
				</tr>';
			}
			
			$str .='</tbody>
			</table>';
			
			echo $str;
		}
		else if(strtolower($POST['mode']) == "add_new_quotation_ref") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$ref_quotation_no=load_common_no($dbcon,REF_QUOTATION_NO);
			// update_common_no($dbcon,REF_QUOTATION_NO);
			//var_dump(REF_QUOTATION_NO); exit;
			$info['ref_quotation_no']	= $ref_quotation_no;
			$info['ref_quotation_date']	= date('Y-m-d');
			//$info['vender_id']			= $POST['vender_id'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$inserpoid=add_record('po_quotation_ref', $info, $dbcon, $branch_id);

			

			$approve_id = implode(",",$POST['approove_id']);
			$query = "select pmst.product_id, apo.approve_qty, apo.approve_unit, apo.approve_base_qty, apo.approve_base_unit,apo.approve_indent_id, apo.product_desc
				from approve_indent as apo
				left join tbl_request_product as po on po.rp_id=apo.rp_id
				left join branch_mst as bms on bms.branch_id=apo.branch_id
				left join product_mst as pmst on pmst.product_id=po.rp_pid
				left join users as us on us.user_id=apo.user_id
				where apo.approve_indent_status=0 and apo.quotation_approve_status=0 and apo.quotation_requirement=1 and apo.approve_indent_id in (".$approve_id.")";
			$result = $dbcon->query($query);
			$cnt = brp_mysqli_num_rows($result);
			while($row = brp_mysqli_fetch_array($result)){
				$info1['approve_indent_id']	= $row['approve_indent_id'];
				$info1['product_id']		= $row['product_id'];
				$info1['product_qty']		= $row['approve_base_qty'];
				$info1['product_conv_qty']	= $row['approve_qty'];
				$info1['unit_id']			= $row['approve_base_unit'];
				$info1['conv_unit_id']		= $row['approve_unit'];
				$info1['remark']			= $row['product_desc'];
				$info1['ref_name']			= 'request_for_quotation';
				$info1['ref_id']			= $inserpoid;
				$info1['cdate']				= date("Y-m-d H:i:s");
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$inserpoid1=add_record('po_quotationtrn_ref', $info1, $dbcon);

				$info_used['used_document']	= 1;
				$info_used['quotation_ref_id']	= $inserpoid1;

				$updateid = update_record('approve_indent', $info_used,"approve_indent_id=".$row['approve_indent_id'], $dbcon);
			}

			if($inserpoid){
				$arr['msg']="1";
				$arr['insert_id'] = $inserpoid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);			
		}

		else if(strtolower($POST['mode']) == "load_req_quotation") {
			$query = "select rtrn.po_quotationtrn_id,pro.product_name,rtrn.product_qty,rtrn.product_conv_qty,unit.unit_name as base_unit,cunit.unit_name as conv_unit,rtrn.remark, ain.approve_no,ain.approve_indent_id, ain.approve_date, req.indent_no,req.indent_date,rtrn.unit_id, rtrn.conv_unit_id from po_quotationtrn_ref as rtrn 
			left join product_mst as pro on pro.product_id = rtrn.product_id
			left join unit_mst as unit on unit.unitid = rtrn.unit_id
			left join unit_mst as cunit on cunit.unitid = rtrn.conv_unit_id
			left join approve_indent as ain on ain.approve_indent_id = rtrn.approve_indent_id
			left join tbl_request_product as req on req.rp_id = ain.rp_id
			where rtrn.ref_name='request_for_quotation' and rtrn.ref_id=".$POST['quotation_ref_id']." and rtrn.po_quotationtrn_status=0";
			$str= '<table class="display table table-bordered table-striped" >
			<thead>
				<tr>
					<th class="text-center editmode1"><input type="checkbox" id="all_chk_box1" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_all_item_req();"></th>
					<th class="text-center">Approve No</th>
					<th class="text-center">Approve Date</th>
					<th class="text-center">Indent No</th>
					<th class="text-center">Indent Date</th>
					<th class="text-center">Product Name</th>
					<th class="text-center">Product Qty</th>
					<th class="text-center">Remark</th>
				</tr>
			</thead>
			<tbody>';
			$result = $dbcon->query($query);
			$i=1;
			while($row = brp_mysqli_fetch_array($result)){
				if($row['unit_id']!=$row['conv_unit_id']){
					$product_qty = '<span style="color:green">'.$row['product_qty'].' '.$row['base_unit'].'</span><br><span style="color:orange">'.$row['product_conv_qty'].' '.$row['conv_unit'];
				}else{
					$product_qty = '<span style="color:green">'.$row['product_qty'].' '.$row['base_unit'].'</span>';
				}
				$str .='<tr>
					<td class="editmode1"><input type="checkbox" name="chk_box_item_req[]" class="chk_box_item_req" id="chk_box_item_req'.$i.'" value="'.$row['po_quotationtrn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_box_limit_item_req(this.id);"></td>
					<td>'.$row['approve_no'].'</td>
					<td>'.date('d-m-Y',strtotime($row['approve_date'])).'</td>
					<td>'.$row['indent_no'].'</td>
					<td>'.date('d-m-Y',strtotime($row['indent_date'])).'</td>
					<td>'.$row['product_name'].'</td>
					<td>'.$product_qty.'</td>
					<td>'.$row['remark'].'</td>
				</tr>';
				$i++;
			}
			$str.='</tbody>
			</table>';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "mode_change_req_quot") {
			$query = "select * from po_quotation_ref where quotation_ref_id=".$POST['quotation_ref_id'];
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result);
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_supplier_quotation"){
    // Validate and sanitize input parameters
    $quotation_ref_id = isset($POST['quotation_ref_id']) && !empty($POST['quotation_ref_id']) ? $dbcon->real_escape_string($POST['quotation_ref_id']) : 0;
    $vender_id = isset($POST['vender_id']) && !empty($POST['vender_id']) ? $dbcon->real_escape_string($POST['vender_id']) : 0;
    
    // Build query with proper validation
    $query = "SELECT rtrn.po_quotationtrn_id, pro.product_name, rtrn.product_qty, rtrn.product_conv_qty, 
                     unit.unit_name as base_unit, cunit.unit_name as conv_unit, ain.approve_no, 
                     rtrn.remark, rtrn.product_rate, ain.approve_date, req.indent_no, req.indent_date,
                     rtrn.unit_id, rtrn.delivery_date, rtrn.payment_days, rtrn.conv_unit_id 
              FROM po_quotationtrn_ref as rtrn 
              LEFT JOIN product_mst as pro ON pro.product_id = rtrn.product_id
              LEFT JOIN unit_mst as unit ON unit.unitid = rtrn.unit_id
              LEFT JOIN unit_mst as cunit ON cunit.unitid = rtrn.conv_unit_id
              LEFT JOIN approve_indent as ain ON ain.approve_indent_id = rtrn.approve_indent_id
              LEFT JOIN tbl_request_product as req ON req.rp_id = ain.rp_id
              WHERE rtrn.ref_name = 'supplier_quotation' 
                AND rtrn.ref_id = " . $quotation_ref_id . " 
                AND rtrn.vender_id = " . $vender_id . " 
                AND rtrn.po_quotationtrn_status = 0";
    
    $str = '';
    $str .= '<table class="display table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center editmode1"><input type="checkbox" id="all_chk_box12" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_all_item_req12();"></th>
            <th class="text-center">Approve No</th>
            <th class="text-center">Approve Date</th>
            <th class="text-center">Indent No</th>
            <th class="text-center">Indent Date</th>
            <th class="text-center">Product Name</th>
            <th class="text-center">Product Qty</th>
            <th class="text-center">Product Rate</th>
            <th class="text-center">Delivery Date</th>
            <th class="text-center">Payment Days</th>
            <th class="text-center">Product Remark</th>
        </tr>
    </thead>
    <tbody>';
    
    $result = $dbcon->query($query);
    $i = 1;
    
    if($result && brp_mysqli_num_rows($result) > 0){
        while($row = brp_mysqli_fetch_array($result)){
            if($row['unit_id'] != $row['conv_unit_id']){
                $product_qty = '<span style="color:green">'.$row['product_qty'].' '.$row['base_unit'].'</span><br><span style="color:orange">'.$row['product_conv_qty'].' '.$row['conv_unit'].'</span>';
            } else {
                $product_qty = '<span style="color:green">'.$row['product_qty'].' '.$row['base_unit'].'</span>';
            }
            
            $delivery_date = ''; 
            if($row['delivery_date'] != '0000-00-00' && $row['delivery_date'] != '' && $row['delivery_date'] != '1970-01-01'){
                $delivery_date = date('d-m-Y', strtotime($row['delivery_date']));
            }

            $str .= '<tr>
                <td class="editmode1"><input type="checkbox" name="chk_box_item_req12[]" class="chk_box_item_req12" id="chk_box_item_req12'.$i.'" value="'.$row['po_quotationtrn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_box_limit_item_req12(this.id);"></td>
                <td>'.$row['approve_no'].'</td>
                <td>'.date('d-m-Y', strtotime($row['approve_date'])).'</td>
                <td>'.$row['indent_no'].'</td>
                <td>'.date('d-m-Y', strtotime($row['indent_date'])).'</td>
                <td>'.$row['product_name'].'</td>
                <td>'.$product_qty.'</td>
                <td>'.$row['product_rate'].'</td>
                <td>'.$delivery_date.'</td>
                <td>'.$row['payment_days'].'</td>
                <td>'.$row['remark'].'</td>
            </tr>';
            $i++;
        }
    } else {
        $str .= '<tr>
            <td colspan="11" class="text-center">No Data Yet...!!</td>
        </tr>';
    }
    
    $str .= '</tbody>
    </table>';
    echo $str;
}
		else if(strtolower($POST['mode']) == "request_quotation_data"){
			$supplier_id = array_filter($_POST['supplier_id']);
			$vender_id = trim(implode(",", @$POST['supplier_id']),","); 
			$req_quot_id = trim(implode(",", @$POST['req_quot_id']),",");
			$info['vender_id']	= $vender_id;
			$updateid=update_record('po_quotation_ref', $info,"quotation_ref_id=".$POST['quotation_ref_id'], $dbcon);
			$info_sup['supplier_status']=2;
			$updatesup=update_record('tbl_supplier_quotation_detail', $info_sup,'quotation_ref_id='.$POST['quotation_ref_id'].' and vender_id not in ('.$vender_id.')', $dbcon);
			foreach($supplier_id as $value){
				$info2['po_quotationtrn_status']=2;
				$updateid1=update_record('po_quotationtrn_ref', $info2,"parent_req_id not in(".$req_quot_id.") and ref_name='supplier_quotation' and vender_id=".$value, $dbcon);

				$ven_query = "select * from tbl_supplier_quotation_detail where quotation_ref_id=".$POST['quotation_ref_id']." and vender_id=".$value;
				$ven_resul = $dbcon->query($ven_query); 
				if(brp_mysqli_num_rows($ven_resul)==0){
					$info3['quotation_ref_id']	 = $POST['quotation_ref_id'];
					$info3['vender_id']			 = $value;
					$info3['cdate']				 = date("Y-m-d H:i:s");
					$info3['user_id']			 = $_SESSION['user_id'];
					$info3['company_id']		 = $_SESSION['company_id'];
					$insertid = add_record('tbl_supplier_quotation_detail', $info3, $dbcon);
				}
				
				
				$query = 'select * from po_quotationtrn_ref where po_quotationtrn_id in ('.$req_quot_id.')';
				$result = $dbcon->query($query);
				while($row = brp_mysqli_fetch_array($result)){
					$info_quot_trn['approve_indent_id']		= $row['approve_indent_id'];
					$info_quot_trn['product_id']			= $row['product_id'];
					$info_quot_trn['product_qty']			= $row['product_qty'];
					$info_quot_trn['product_conv_qty']		= $row['product_conv_qty'];
					$info_quot_trn['unit_id']				= $row['unit_id'];
					$info_quot_trn['conv_unit_id']			= $row['conv_unit_id'];
					$info_quot_trn['remark']				= $row['remark'];
					$info_quot_trn['ref_name']				= 'supplier_quotation';
					$info_quot_trn['ref_id']				= $POST['quotation_ref_id'];
					$info_quot_trn['vender_id']				= $value;
					$info_quot_trn['parent_req_id']			= $row['po_quotationtrn_id'];
					$info_quot_trn['cdate']					= date("Y-m-d H:i:s");
					$info_quot_trn['user_id']				= $_SESSION['user_id'];
					$info_quot_trn['company_id']			= $_SESSION['company_id'];
					
					$query_supplier = 'select * from po_quotationtrn_ref where ref_name="supplier_quotation" and vender_id = '.$value.' and parent_req_id='.$row['po_quotationtrn_id'];
					$result_supplier = $dbcon->query($query_supplier);

					if(brp_mysqli_num_rows($result_supplier)==0){
						$insertid1 = add_record('po_quotationtrn_ref', $info_quot_trn, $dbcon);
					}
				}
			}
			$ledger  = "select GROUP_CONCAT(l_name) as ledger_name from tbl_ledger where l_id in (".$vender_id.")";
			$led_result = $dbcon->query($ledger);
			$led_row = brp_mysqli_fetch_array($led_result);
			$arr['ledger'] = $led_row['ledger_name'];

			if($updateid){
				$arr['msg'] =1;
			}else{
				$arr['msg'] =0;
			}
			
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "load_supplier_quotation_vender"){
			$query = "select sdet.*,led.l_name from tbl_supplier_quotation_detail as sdet
			left join tbl_ledger as led on led.l_id = sdet.vender_id
			where supplier_status=0 and quotation_ref_id=".$POST['quotation_ref_id'];
			$result = $dbcon->query($query);
			$str = '';
			$str.='<option value="">Choose Vendor</option>';
			while($row = brp_mysqli_fetch_array($result)){
				$str .='<option value="'.$row['vender_id'].'">'.$row['l_name'].'</option>';
			}
			echo $str;
		}
		else if(strtolower($POST['mode']) == "load_supplier_detail"){
			$query = 'select * from tbl_supplier_quotation_detail where quotation_ref_id ='.$POST['quotation_ref_id'].' and vender_id='.$POST['vender_id'];
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result);
			$quotation_date='';$delivery_date='';
			if($row['quotation_date']!="1970-01-01" && $row['quotation_date']!="0000-00-00"){
				$quotation_date = date('d-m-Y',strtotime($row['quotation_date']));
			}
			if($row['delivery_date']!="1970-01-01" && $row['delivery_date']!="0000-00-00"){
				$delivery_date = date('d-m-Y',strtotime($row['delivery_date']));
			}
			$row['quotation_date'] = $quotation_date;
			$row['delivery_date']  = $delivery_date;
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "supplier_quotation_data"){
			$info['quotation_ref_id']		= $POST['quotation_ref_id'];
			$info['vender_id']				= $POST['vender_id'];
			$info['quotation_no']			= $POST['quotation_no'];
			$info['quotation_date']			= date('Y-m-d',strtotime($POST['quotation_date']));
			//$info['delivery_date']		= date('Y-m-d',strtotime($POST['delivery_date']));
			//$info['payment_days']			= $POST['payment_days'];
			$info['cdate']					= date("Y-m-d H:i:s");
			$info['user_id']				= $_SESSION['user_id'];
			$info['company_id']				= $_SESSION['company_id'];
			//var_dump($info);
			$updateid=update_record('tbl_supplier_quotation_detail', $info,"supplier_detail_id=".$POST['supplier_detail_id'], $dbcon);

			$po_quotationtrn_id = implode(",",$POST['req_quot_id']);
			//var_dump($po_quotationtrn_id);
			$info1['supplier_detail_id']=$POST['supplier_detail_id'];
			$info1['quotation_copm']	= 1;
			$updateid1=update_record('po_quotationtrn_ref', $info1,"po_quotationtrn_id in(".$po_quotationtrn_id.") and vender_id=".$POST['vender_id']." and ref_name='supplier_quotation' and ref_id=".$POST['quotation_ref_id'], $dbcon);

			$info2['quotation_copm']	= 0;
			$info2['supplier_detail_id']= 0;
			$updateid2=update_record('po_quotationtrn_ref', $info2,"po_quotationtrn_id not in(".$po_quotationtrn_id.") and vender_id=".$POST['vender_id']." and ref_name='supplier_quotation' and ref_id=".$POST['quotation_ref_id'], $dbcon);

			if($updateid){
				$arr['msg'] =1;
			}else{
				$arr['msg'] =0;
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "load_quotation_comparision"){
			$str ='';
			$query = "select led.l_name, sd.quotation_no, sd.quotation_date, tref.vender_id, tref.supplier_detail_id, sd.quotation_copm_aprv as sup_aprv 
			from po_quotationtrn_ref as tref
			left join tbl_supplier_quotation_detail as sd on sd.supplier_detail_id=tref.supplier_detail_id
			left join tbl_ledger as led on led.l_id = tref.vender_id
			where tref.quotation_copm=1 and po_quotationtrn_status=0 and ref_name='supplier_quotation' and ref_id='".$POST['quotation_ref_id']."' group by tref.vender_id";
// 			$query = "SELECT led.l_name,MAX(sd.quotation_no) AS quotation_no, MAX(sd.quotation_date) AS quotation_date, tref.vender_id,MAX(tref.supplier_detail_id) AS supplier_detail_id,MAX(sd.quotation_copm_aprv) AS sup_aprv
// 			FROM po_quotationtrn_ref AS tref
// 			LEFT JOIN tbl_supplier_quotation_detail AS sd ON sd.supplier_detail_id = tref.supplier_detail_id
// 			LEFT JOIN tbl_ledger AS led ON led.l_id = tref.vender_id
// 			WHERE tref.quotation_copm = 1 AND po_quotationtrn_status = 0 AND ref_name = 'supplier_quotation' AND ref_id = {$POST['quotation_ref_id']}
// 			GROUP BY tref.vender_id
// ";
			$result = $dbcon->query($query);
			$rows = brp_mysqli_fetch_all($result);

			$query_pro = "select pro.product_name,tref.product_id from po_quotationtrn_ref as tref
			left join product_mst as pro on pro.product_id = tref.product_id
			where tref.quotation_copm=1 and tref.po_quotationtrn_status=0 and tref.ref_name='supplier_quotation' and tref.ref_id='".$POST['quotation_ref_id']."' group by tref.product_id";

			$result_pro = $dbcon->query($query_pro);
			$rows_pro = brp_mysqli_fetch_all($result_pro); 
			$str .= '<table class="table table-striped">
			<tr>
				<th class="text-center" style="background-color:#2727b7;color:white;max-width:25%">Supplier Name</th>';
				foreach($rows as $row){
					$str.='<th class="text-center">'.$row['l_name'].'</th>';
				}
			$str.='</tr>
			<tr>
				<td class="text-center" style="background-color:#2727b7;color:white">Quot Num</td>';
				foreach($rows as $row){
					$str.='<td class="text-center">'.$row['quotation_no'].'</td>';
				}
			$str.='</tr>';
			$j=1;$k=1;
			foreach($rows_pro as $row_pro){
				$str.='<tr>
					<td class="text-center" style="background-color:#2727b7;color:white">'.$row_pro['product_name'].'</td>';
				
				foreach($rows as $row){
					$query_rate = "select product_rate,po_quotationtrn_id,quotation_copm_aprv,quotation_copm from po_quotationtrn_ref where product_id=".$row_pro['product_id']." and vender_id=".$row['vender_id']." and ref_name='supplier_quotation' and ref_id=".$POST['quotation_ref_id'];
					$result_rate = $dbcon->query($query_rate);
					$row_rate = brp_mysqli_fetch_array($result_rate);

					$checked='';
					if($row_rate['quotation_copm_aprv']==1){
						$checked = "checked";
					}

					$str.='<td class="text-center">'.$row_rate['product_rate'].'<br>';
					if($row_rate['quotation_copm']==1){
						$str.='<input type="radio" name="abc'.$j.'" class="comapre_prod_wise" id="abc'.$k.'" value="'.$row_rate['po_quotationtrn_id'].'" '.$checked.' style="width: 23px;height: 23px;margin-top: 0px;">';
					}
					
					$str.'</td>';
					$k++;
				}
				$str.='</tr>';
				$j++;
			}
			$str .='<input type="hidden" name="cnt" id="cnt" value="'.($j-1).'">
				<tr class="comapre_quot_wise">
				<td class="text-center" style="background-color:#2727b7;color:white"><strong></strong>Select Quotation</strong></td>';
				$i=1;
				foreach($rows as $row){
					/* $query_sup  = "select * from tbl_supplier_quotation_detail where supplier_detail_id =".$POST['supplier_detail_id'];
					$result_sup = $dbcon->query($query_sup);
					$row_sup = brp_mysqli_fetch_array($result_sup); */
					$checked1='';
					if($row['sup_aprv']==1){
						$checked1 = "checked";
					}
					$str .='<td class="text-center"><input type="radio" name="abc" class="" id="" value="'.$row['supplier_detail_id'].'" '.$checked1.' style="width: 23px;height: 23px;margin-top: 0px;"></td>';
					$i++;
				}
			$str .='</tr>';
			$str.'</table>';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "add_temp_edit_product"){
			$delte_temp_rec = delete_record('tbl_multiple_product_edit_flag_temp','quotation_ref_id='.$POST['quotation_ref_id'].' and ref_name="'.$POST['ref_name'].'" and user_id='.$_SESSION['user_id'],$dbcon);
			foreach($POST['checked_id'] as $value){
				$info['quotation_ref_id']		= $POST['quotation_ref_id'];
				$info['po_quotationtrn_id']		= $value;
				$info['ref_name']				= $POST['ref_name'];
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];
				
				$insertid = add_record('tbl_multiple_product_edit_flag_temp', $info, $dbcon);
			}

			$query = "select * from tbl_multiple_product_edit_flag_temp where ref_name='".$POST['ref_name']."' and quotation_ref_id=".$POST['quotation_ref_id']." LIMIT 1";
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result);

			$arr['po_quotationtrn_id']	= $row['po_quotationtrn_id'];
			$arr['ref_name']			= $POST['ref_name'];
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "edit_modal_data_preview"){
			$query = "select trn.*,pro.product_name,unit.unit_name,apr.approve_no,apr.approve_date,req.indent_no,req.indent_date from po_quotationtrn_ref as trn
			left join product_mst as pro on pro.product_id = trn.product_id
			left join unit_mst as unit on unit.unitid = trn.unit_id
			left join approve_indent as apr on apr.approve_indent_id = trn.approve_indent_id
			left join tbl_request_product as req on req.rp_id  = apr.rp_id
			where trn.po_quotationtrn_id=".$POST['id'];

			
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result); 
			
			$delivery_date = ''; 
			if($row['delivery_date']!='0000-00-00' && $row['delivery_date'] !='' && $row['delivery_date']!='1970-01-01'){
				$delivery_date = date('d-m-Y',strtotime($row['delivery_date']));
			}
			$query_save="select * from tbl_multiple_product_edit_flag_temp where quotation_ref_id = ".$row['ref_id']." and ref_name='".$row['ref_name']."'";
			$result_save = $dbcon->query($query_save);
			$cnt_save = brp_mysqli_num_rows($result_save);
			
			$class ='';
			if($cnt_save==1){
				$class='hide';
			}

			$str ='';

			$str.='<div class="col-md-12" style="margin-top:20px">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Approve No</label>
						<div class="col-md-8 col-xs-11">
							<strong style="color:green">'.$row['approve_no'].'</strong>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Approve Date</label>
						<div class="col-md-8 col-xs-11">
							<strong style="color:green">'.date('d-m-Y',strtotime($row['approve_date'])).'</strong>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-12" style="margin-top:20px">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Indent No</label>
						<div class="col-md-8 col-xs-11">
							<strong style="color:green">'.$row['indent_no'].'</strong>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Indent Date</label>
						<div class="col-md-8 col-xs-11">
							<strong style="color:green">'.$row['indent_date'].'</strong>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-12" style="margin-top:20px">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Product Name</label>
						<div class="col-md-8 col-xs-11">
							<strong style="color:green">'.$row['product_name'].'</strong>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Qty</label>
						<div class="col-md-8 col-xs-11">
							<strong style="color:green">'.$row['product_qty'].' '.$row['unit_name'].'</strong>
						</div>
					</div>
				</div>
			</div>';

			if($row['ref_name']=='supplier_quotation'){
				$str .= '<div class="col-md-12" style="margin-top:20px">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label">Delivery Date</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" name="delivery_date" id="delivery_date" class="form-control default-date-picker" placeholder="Delivery Date" title="Delivery Date" value="'.$delivery_date.'">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label">Payment Days</label>
							<div class="col-md-8 col-xs-11">
							<input type="number" class="form-control" name="payment_days" id="payment_days" placeholder="Payment Days" title="Payment Days" value="'.$row['payment_days'].'">
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-top:20px">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label">Product Rate</label>
							<div class="col-md-8 col-xs-11">
							<input type="number" class="form-control" name="product_rate" id="product_rate" placeholder="Product Rate" title="Product Rate" value="'.$row['product_rate'].'">
							</div>
						</div>
					</div>
				</div>';
			}
			$str .='<div class="col-md-12" style="margin-top:20px">
				<div class="form-group">
					<label class="col-md-2 control-label">Remark</label>
					<div class="col-md-8 col-xs-11">
						<textarea class="form-control" id="product_desc_sup" name="product_desc_sup">'.$row['remark'].'</textarea>
					</div>
				</div>
			</div>';

			$str.='<div class="col-md-12 text-center" style="margin-top:20px">
				<input type="hidden" name="ref_name" id="ref_name" value="'.$row['ref_name'].'">
				<input type="hidden" name="ref_trn" id="ref_trn" value="'.$POST['id'].'">
				<button class="btn btn-success" value="1"  onclick="save_trn_data(this.value)" >Save</button> &nbsp;
				<button class="btn btn-success '.$class.'" value="2" onclick="save_trn_data(this.value)">Save & Next</button> &nbsp;
				<button class="btn btn-danger" >Cancel</button>
			</div>';
			$str.='';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "save_trn_data"){
			/* var_dump($POST); */
			if($POST['btnval'] == 2){
				$delete = delete_record('tbl_multiple_product_edit_flag_temp','po_quotationtrn_id='.$POST['po_quotationtrn_id'].' and ref_name="'.$POST['ref_name'].'" and user_id='.$_SESSION['user_id'],$dbcon);
			}else{
				$delete = delete_record('tbl_multiple_product_edit_flag_temp','quotation_ref_id='.$POST['quotation_ref_id'].' and ref_name="'.$POST['ref_name'].'" and user_id='.$_SESSION['user_id'],$dbcon);
			}
					
			$info['delivery_date']		= date('Y-m-d',strtotime($POST['delivery_date']));
			$info['payment_days']		= $POST['payment_days'];
			$info['product_rate']		= $POST['product_rate'];
			$info['remark']				= $_POST['product_desc'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			//var_dump($info);
			$updateid=update_record('po_quotationtrn_ref', $info,"po_quotationtrn_id=".$POST['po_quotationtrn_id'], $dbcon);

			if($POST['btnval'] == 2){
				$query = "select * from tbl_multiple_product_edit_flag_temp where ref_name='".$POST['ref_name']."' and quotation_ref_id=".$POST['quotation_ref_id']." LIMIT 1";
				$result = $dbcon->query($query);
				$row = brp_mysqli_fetch_array($result); 
				$arr['po_quotationtrn_id']	= $row['po_quotationtrn_id'];
				$arr['ref_name']			= $row['ref_name'];
			}

			if($updateid){
				$arr['msg']	= 1;
			}else{
				$arr['msg']	= 0;
			}

			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "quotation_comparision_add"){
			$info['comparision']	= $POST['comparision'];
			$updateid=update_record('po_quotation_ref', $info,"quotation_ref_id=".$POST['quotation_ref_id'], $dbcon);

			$info1['quotation_copm_aprv'] = 0;
			$info1['cdate']	= date("Y-m-d H:i:s");
			$updateid1=update_record('po_quotationtrn_ref', $info1,"ref_id=".$POST['quotation_ref_id']." and ref_name='supplier_quotation'", $dbcon);
			
			$updateid4=update_record('tbl_supplier_quotation_detail', $info1,"supplier_detail_id=".$POST['supplier_detail_id']." and quotation_ref_id=".$POST['quotation_ref_id'], $dbcon);

			if($POST['comparision']==2){
				foreach($POST['po_quotationtrn_id'] as $value){
					$info2['quotation_copm_aprv'] = 1;
					$updateid2=update_record('po_quotationtrn_ref', $info2,"po_quotationtrn_id=".$value, $dbcon);
				}
			}else{
				$info2['quotation_copm_aprv'] =1;
				$updateid2=update_record('po_quotationtrn_ref', $info2,"supplier_detail_id=".$POST['supplier_detail_id'], $dbcon);
				
				$updateid3=update_record('tbl_supplier_quotation_detail', $info2,"supplier_detail_id=".$POST['supplier_detail_id'], $dbcon);
			}
			
			if($updateid1){
				$arr['msg']	=1;
			}else{
				$arr['msg']	=0;
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "delete_data"){
			foreach($POST['checked_id'] as $value){
				if($POST['ref_name']=='supplier_quotation'){
					$info['quotation_copm']	=0;
					$info['quotation_copm_aprv'] = 0;
					$updateid = update_record('po_quotationtrn_ref', $info,"po_quotationtrn_id=".$value, $dbcon);
				}else{
					$info['quotation_copm']	=0;
					$info['quotation_copm_aprv'] = 0;
					$info['po_quotationtrn_status'] =2;
					$updateid = update_record('po_quotationtrn_ref', $info,"po_quotationtrn_id=".$value." or parent_req_id=".$value, $dbcon);

					$query = "select * from po_quotationtrn_ref where po_quotationtrn_id=".$value;
					$result = $dbcon->query($query);
					$row = brp_mysqli_fetch_array($result);
					$info_used['used_document']	= 0;
					$info_used['quotation_ref_id']	= 0;

					$updateid = update_record('approve_indent', $info_used,"approve_indent_id=".$row['approve_indent_id'], $dbcon);
				}
			}
			if($updateid){
				$arr['msg']	= 1;
			}else{
				$arr['msg']	= 0;
			}
			echo json_encode($arr);
		}else if(strtolower($POST['mode']) == "delete_quotation"){
			$info['ref_quotation_status']	= 2;
			$updateid = update_record('po_quotation_ref', $info,"quotation_ref_id=".$POST['eid'], $dbcon);

			$info_trn['quotation_copm']	=0;
			$info_trn['quotation_copm_aprv'] = 0;
			$info_trn['po_quotationtrn_status'] =2;
			$updateid = update_record('po_quotation_ref', $info,"quotation_ref_id=".$POST['eid'], $dbcon);

			$query = "select * from po_quotation_ref where ref_name='request_for_quotation' and ref_id=".$POST['eid']." and po_quotationtrn_status=0";
			$result = $dbcon->query($query);
			while($row = brp_mysqli_fetch_array($result))
			{
				$info_used['used_document']	= 0;
				$info_used['quotation_ref_id']	= 0;

				$updateid = update_record('approve_indent', $info_used,"approve_indent_id=".$row['approve_indent_id'], $dbcon);
			}

		}else if(strtolower($POST['mode']) == "approve_data"){
			//var_dump($POST);exit;
			$purchase_quotation = get_generate_purchase_quotation($dbcon,$POST['id']);

			$branch_id = $_SESSION['branch_id'];
			$query_aprv = "select pquot.* from po_quotationtrn_ref as qtrn 
			left join po_quotation as pquot on pquot.po_quotationtrn_id = qtrn.po_quotationtrn_id
			where po_quotationtrn_status=0 and quotation_copm_aprv=1 and ref_name='supplier_quotation' and ref_id=".$POST['id'];

			$result_aprv = $dbcon->query($query_aprv);
			while($row = brp_mysqli_fetch_array($result_aprv)){
				$com="select * from po_quotation where po_quotation_id=".$row['po_quotation_id'];
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
				$updateid=update_record('po_quotation', $info,"po_quotation_id=".$row['po_quotation_id'] , $dbcon, $branch_id);
				
				$info1['quotation_approve_id']		= $row['po_quotation_id'];
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
				$infpotrn['product_desc']		= $row['remark'];
				$infpotrn['user_id']			= $_SESSION['user_id'];
				$infpotrn['po_ref_id']			= $rel_used1['rp_id'];
				$infpotrn['po_ref_type']		= '0';
				$infpotrn['po_bom_id']			= '';
				$infpotrn['po_bom_trn_id']		= '';
				$infpotrn['mdate']				= date('Y-m-d');
				$infpotrn['po_quotation_id']	= $row['po_quotation_id'];
				$infpotrn['company_id']			= $_SESSION['company_id'];
				
				$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $rel_used['branch_id']);
			}
			$info_ref['approve_status']=1;
			$updateid=update_record('po_quotation_ref', $info_ref,"quotation_ref_id=".$POST['id'] , $dbcon);
			
			if($inserpotrnid){
				$arr['msg']	= 1;
			}else{
				$arr['msg']	= 0;
			}
			
			echo json_encode($arr);
		}else if(strtolower($POST['mode']) == "disapprove_data"){
			$query_aprv = "select pquot.* from po_quotationtrn_ref as qtrn 
			left join po_quotation as pquot on pquot.po_quotationtrn_id = qtrn.po_quotationtrn_id
			where po_quotationtrn_status=0 and quotation_copm_aprv=1 and ref_name='supplier_quotation' and ref_id=".$POST['id'];

			$result_aprv = $dbcon->query($query_aprv);
			while($row = brp_mysqli_fetch_array($result_aprv)){
				$com="select * from po_quotation where po_quotation_id=".$row['po_quotation_id'];
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
				$updateid=update_record('po_quotation', $info,"po_quotation_id=".$row['po_quotation_id'] , $dbcon);
				
		
				$info1['quotation_approve_id']		= 0;
				$info1['quotation_approve_status']	= 0;
				$updateid=update_record('approve_indent', $info1,"approve_indent_id=".$comty['approve_indent_id'] , $dbcon);

				$delsql = "DELETE FROM `tbl_purchasetrntemp` WHERE po_quotation_id = '".$row['po_quotation_id']."' and product_rate = '".$comty['product_rate']."' ";
				$dbcon->query($delsql);
			}
			$info_ref['approve_status']=2;
			$updateid=update_record('po_quotation_ref', $info_ref,"quotation_ref_id=".$POST['id'] , $dbcon);
			
			if($updateid){
				$arr['msg']	=1;
			}else{
				$arr['msg']	=0;
			}

			echo json_encode($arr);
		}
function get_generate_purchase_quotation($dbcon, $id){

	$query = "select qtrn.*, sd.quotation_no, sd.quotation_date from po_quotationtrn_ref as qtrn
	left join tbl_supplier_quotation_detail as sd on sd.supplier_detail_id = qtrn.supplier_detail_id	
	where qtrn.quotation_copm_aprv=1 and qtrn.po_quotationtrn_status=0 and qtrn.ref_name='supplier_quotation' and qtrn.ref_id=".$id;

	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_array($result)){
		$query_quot = "select * from po_quotation where po_quotation_status !=2 and po_quotationtrn_id=".$row['po_quotationtrn_id']; 
		$result_quot  = $dbcon->query($query_quot);
		$cnt  = brp_mysqli_num_rows($result_quot);

		if($cnt==0){
			$info['approve_indent_id']	= $row['approve_indent_id'];
			$info['vender_id']			= $row['vender_id'];
			$info['quotation_no']		= $row['quotation_no'];
			$info['quotation_date']		= date('Y-m-d',strtotime($row['quotation_date']));
			$info['delivery_date']		= date('Y-m-d',strtotime($row['delivery_date']));
			$info['payment_days']		= $row['payment_days'];
			$info['product_rate']		= $row['product_rate'];
			$info['po_quotationtrn_id']	= $row['po_quotationtrn_id'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['branch_id']			= $_SESSION['branch_id'];
			add_record('po_quotation', $info, $dbcon);
		}
	}
}
?>