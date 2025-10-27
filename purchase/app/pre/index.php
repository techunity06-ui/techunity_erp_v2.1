<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
include_once("../../include/common_send_email.php");

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
			
			$where_company=check_company('pre');

			$where.=" $where_company";

			//$where_user=check_user('pre');

			//$where.=" $where_user";

			$date = " and pr.pre_date between '" . date('Y-m-d', strtotime($s_date[0])) . "' AND '" . date('Y-m-d', strtotime($s_date[1])) . "'";
			$where.=" $where_db ".$date;

			$appData = array();
			$i=1;
			$aColumns = array('pre.rp_id','pr.pre_no','pre.indent_no','pr.pre_date','pre.pre_trn_id','bms.branch_name','pre.branch_id','us.user_name','pre.rp_req_type','pre_trn.pre_id','tl.l_name','tl.cust_mobile','tl.cust_email','pr.pre_id');
			$sIndexColumn = "pre.rp_id";
			$isWhere = array("pre.indent_status in (1,3) and status=0".$where);
			$sTable = "tbl_request_product as pre";			
			$isJOIN = array('left join tbl_pre_trn as pre_trn on pre_trn.pre_trn_id=pre.pre_trn_id','left join tbl_pre as pr on pr.pre_id=pre_trn.pre_id','left join branch_mst as bms on bms.branch_id=pr.branch_id','left join users as us on us.user_id=pre.user_id','left join tbl_ledger as tl on tl.l_id=pre_trn.vender_id');
			$hOrder = "pr.pre_date desc";
			$hGroupby = array("pr.pre_id");
			include($include.'pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;
				$row_data[] = $row['pre_no'];
				// $row_data[] = $row['indent_no'];
				$row_data[] = date('d M, Y',strtotime($row['pre_date']));
				
				if($row['branch_id'] == '10000'){
					$row_data[] = 'All Branch';
				}else{
					$row_data[] = $row['branch_name'];
				}

				$row_data[] = $row['user_name'];
				
				$edit = ''; $delete = '';$indent_view='';
				if(in_array(PRE_VIEW,$bulkAccessArray)){
					if($row['rp_req_type'] == 'direct'){
						
						$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
						$rels=mysqli_fetch_assoc($menusql);
						$menu_show_permissions = explode(",",$rels['print_permission']);
						$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 17 AND approve_status = 1 AND status = 0 ORDER BY priority");
						while($res = mysqli_fetch_assoc($sql)){
							$page_path = $res['page_path'];
							if(in_array($res['id'],$menu_show_permissions)) {
								$indent_view.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$page_path.'/'.$row['pre_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
							}
						}
					}

					$inde_c = "select pre.pre_id,trn.pre_trn_id,req.rp_id,used_qty,req.indent_status,round(IFNULL(sum(used_qty),0),4) as preqty from tbl_pre as pre
					left join tbl_pre_trn as trn on trn.pre_id = pre.pre_id
					left join tbl_request_product as req on req.pre_trn_id = trn.pre_trn_id
					left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=req.rp_id 

					where pre.pre_status=0 and pre.user_id=".$_SESSION['user_id']." and pre.pre_id=".$row['pre_id']." Group by pre.pre_id";
					
					
					$inde_q = $dbcon->query($inde_c);
					
					$inde_r = mysqli_fetch_array($inde_q);
					
					//var_dump($inde_c);
					//$a = $inde_c;
					$indent_tracking='<a class="btn btn-xs btn-primary" data-original-title="Pre Tracking" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'indent_tracking/'.$row['pre_id'].'"><i class="fa fa-history"></i> Indent Tracking Report</a>';

					$customer_mobile = '+91' . $row['cust_mobile']; 
					$indent_link = DOMAIN . PRINT_ROOT . $page_path . '/' . $row['pre_id'] . '?' . time();
					$text = "Thank you.\nI am From Aero Engineers.\n\n" . $indent_link;
					$encoded_text = urlencode($text);
					$send_whatsapp_new = '<a data-link="'.$indent_link.'" title="Send to Whatsapp" type="button" class="btn btn-xs btn-success" href="https://web.whatsapp.com/send?phone=' . $customer_mobile . '&text=' . $encoded_text . '" target="_blank"> <i class="fa fa-whatsapp"></i></a> ';
					
					$send_email = '<button class="btn btn-xs btn-primary" data-original-title="Send Email" data-toggle="tooltip" data-placement="top" onClick="open_inq_email(' . $row['pre_id'] . ',\'' . $row['pre_no'] . '\',\'' . $row['cust_email'] . '\')"><i class="fa fa-envelope"></i></button>';

					if($row['rp_req_type'] == 'direct'){
						if($inde_r['preqty'] == '0.0000' && $inde_r['indent_status'] !=3){
						    $edit='<a class="btn btn-xs btn-warning" data-original-title="Edit Pre" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'pre_edit/'.$row['pre_id'].'"><i class="fa fa-pencil"></i></a>';
							$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_row('.$row['pre_id'].');" ><i class="fa fa-times"></i></button>';
						}
					}
				}
					
				$row_data[] = $edit." ".$delete." ".$indent_view." ".$indent_tracking." ".$send_whatsapp_new." ".$send_email;
				
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
		else if(strtolower($POST['mode'])== "load_product_unit")
		{
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['product_id'];
			$rs_type1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($rs_type1);
			//var_dump($row1);
			if($row1['product_base_unit']!=$row1['product_conv_unit']){
				$row1['unit_status']="1";
			}else{
				$row1['unit_status']="0";
			}
				//$row1['qye']=$query1;

			echo json_encode($row1);
		}
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			//var_dump($POST);
			$row=array();
			if($POST["type"]=="1"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
				//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 4, ".", "");
					//$ret_qty=$ret_qty;
				//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "add_field") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$companyConfiguration=getCompanyConfiguration($dbcon);

			$info1['product_id']		 = $POST['product_id'];
			$info1['product_desc']		 = $_POST['product_desc'];
			$info1['so_id']			 	 = $POST['sales_order_id'];
			$info1['sp_id']			 	 = $POST['work_order_id'];
			$info1['product_qty']		 = $POST['product_qty'];
			$info1['product_conv_qty']	 = $POST['product_conv_qty'];
			$info1['unitid']			 = $POST['unitid'];
			$info1['conv_unitid']		 = $POST['conv_unitid'];
			$info1['rate']				 = $POST['rate'];
			$info1['purchasecardtrn_id'] = $POST['purchasecardtrn_id'];
			$info1['vender_id']		= $POST['vender_id'];
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
					$prod_p = "select product_conv_unit,product_base_unit from product_mst where product_id=".$POST['product_id'];
					$prod_e = $dbcon->query($prod_p);
					$prod_r = mysqli_fetch_array($prod_e);
					
					$indenttrn['indent_no']			= load_common_no($dbcon,INDENT_SERIES);
					
					update_common_no($dbcon,INDENT_SERIES);
					
					$indenttrn['indent_date']		= date('Y-m-d');
					$indenttrn['rp_req_date']		= date('Y-m-d');
					$indenttrn['rp_req_qty']		= $POST['product_qty'];
					$indenttrn['purchase_unit']		= $prod_r['product_conv_unit'];
					$indenttrn['process_unit']		= $prod_r['product_base_unit'];
					$indenttrn['rp_pid']			= $POST['product_id'];
					//$indenttrn['branch_id']			= $branch_id;
					$indenttrn['indent_status']		= 1;
					$indenttrn['rp_req_type']		= "direct";
					
					$indenttrn['sr_no']				= 0;
					$indenttrn['sales_order_id']	= $POST['sales_order_id'];
					$indenttrn['sp_id']				= $POST['work_order_id'];	
					$indenttrn['req_qty_one']		= 0;
					$indenttrn['rp_po_qty']			= $POST['product_conv_qty'];	
					$indenttrn['rp_po_base_qty']	= $POST['product_qty'];	
					$indenttrn['in_process_qty']	= 0;				
					$indenttrn['out_process_qty']	= 0;
					$indenttrn['perent_id']			= 0;
					$indenttrn['reserve_stock']		= 0;
					$indenttrn['main_request']		= 1;
					$indenttrn['pre_trn_id']		= $inserid;
					$indenttrn['product_remark']	= $_POST['product_desc'];
					
					$indenttrn['cdate']				= date("Y-m-d H:i:s");;
					$indenttrn['user_id']			= $_SESSION['user_id'];
					$indenttrn['company_id']		= $_SESSION['company_id'];
					$indenttid = add_record('tbl_request_product', $indenttrn, $dbcon, $branch_id);
					
					//This Code Use For Auto Indent Approve time --Kapatel Maulik
					if($companyConfiguration['automatic_approval_indent']==1){
						$approve_no=load_common_no($dbcon,JOURNAL_SERIES);
						update_common_no($dbcon,JOURNAL_SERIES);
						
						$info['approve_no']					= $approve_no;
						$info['approve_date']				= date("Y-m-d");
						$info['rp_id']						= $indenttid;
						$info['approve_base_unit']			= $prod_r['product_base_unit'];
						$info['approve_base_qty']			= $POST['product_qty'];
						$info['approve_qty']				= $POST['product_conv_qty'];
						$info['approve_unit']				= $prod_r['product_conv_unit'];
						$info['delivery_date']				= date("Y-m-d H:i:s");
						$info['quotation_requirement']		= 0;
						$info['cdate']						= date("Y-m-d H:i:s");
						$info['user_id']					= $_SESSION['user_id'];
						$info['company_id']					= $_SESSION['company_id'];
						
						
						$inserpoid=add_record('approve_indent', $info, $dbcon, $branch_id);
						
						if($POST['product_conv_qty']==$POST['product_conv_qty']){

							$inftrn['indent_status'] = 3;
							$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$indenttid , $dbcon, $branch_id);
						}
						
						$query_used="select * from tbl_request_product as rpro
							where rp_id=".$indenttid." and company_id = '".$_SESSION['company_id']."' ";
						$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
					
						
						$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
						
							$total=$POST['product_conv_qty']*$rate;
							
							$infpotrn['purchaseorder_id']	= '0';
							$infpotrn['product_type']		= '';
							$infpotrn['product_id']			= $rel_used['rp_pid'];
							$infpotrn['product_desc']		= $rel_used['product_remark'];
							$infpotrn['product_base_qty']	= $POST['product_qty'];
							$infpotrn['product_qty']		= $POST['product_conv_qty'];
							$infpotrn['product_rate']		= $rate;
							$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
							//$infpotrn['unit_id']			= get_pro_field($dbcon,$pr_id,'product_base_unit');
							$infpotrn['base_unit_id']		= $prod_r['product_base_unit'];
							$infpotrn['unit_id']			= $prod_r['product_conv_unit'];
							$infpotrn['product_amount']		= $total;
							$infpotrn['total']				= $total;
							$infpotrn['parent_pro']			= 0;
							$infpotrn['main_pro_status']	= 1;//Requested products
							$infpotrn['user_id']			= $_SESSION['user_id'];
							$infpotrn['po_ref_id']			= $indenttid;
							$infpotrn['po_ref_type']		= '0';
							$infpotrn['po_bom_id']			= '';
							$infpotrn['po_bom_trn_id']		= '';
							$infpotrn['mdate']				= date('Y-m-d');
							$infpotrn['company_id']			= $_SESSION['company_id'];
							
						
						if($info['quotation_requirement']==0){
							$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $branch_id);
						}
					}
				}
			}
			else {
				$indeedi['rp_req_qty']		= $POST['product_qty'];
				$indeedi['rp_po_qty']		= $POST['product_conv_qty'];
				$indeedi['rp_po_base_qty']	= $POST['product_qty'];
				$indeedi['product_remark']	= $_POST['product_desc'];
				
				$updateinde = update_record('tbl_request_product', $indeedi,"pre_trn_id=".$POST['edit_id'] , $dbcon, $branch_id);
				$updateid = update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
				
				//This Used For Auto Indent Approve time --Kapatel Maulik
				if($companyConfiguration['automatic_approval_indent']==1){
					$approve_indent  = "select * from tbl_request_product where pre_trn_id=".$POST['edit_id'];
					$rel_used=brp_mysqli_fetch_array($dbcon->query($approve_indent));
					$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
					$total = $POST['product_conv_qty'] * $rate;
					
					$update_approve['approve_base_qty']	= $POST['product_qty'];
					$update_approve['approve_qty']		= $POST['product_conv_qty'];

					$update_purchasetrn['product_base_qty']	= $POST['product_qty'];
					$update_purchasetrn['product_qty']	= $POST['product_conv_qty'];
					$update_purchasetrn['product_desc']	= $_POST['product_desc'];
					$update_purchasetrn['total']		= $total;

					$update_approve_in = update_record('approve_indent', $update_approve,"rp_id=".$rel_used['rp_id'] , $dbcon, $branch_id);

					$update_purchase_trn = update_record('tbl_purchasetrntemp', $update_purchasetrn,"po_ref_id=".$rel_used['rp_id'] , $dbcon, $branch_id);
				}
			}
			if(strtolower($POST['vender_id']) == 'new'){
				echo json_encode($row);
			}else{
				echo "0";
			}
			
			/* END */   
		}
		else if(strtolower($POST['mode']) == "show_data") {
			$companyConfiguration=getCompanyConfiguration($dbcon);
			$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
			$pro_search=explode(",", $purchase_pro_search);
			if($POST['pre_id']){
				$query = "select pro.product_name,pro.product_icode, dr.drawing_number, pro.product_alias_name, ven.l_name,mst.*, cat.unit_name, cat_con.unit_name as conv_unit_name, crtrn.price, crtrn.rate_tolerance, sm.po_req_no,tso.sales_order_no, pro.product_alias_name from tbl_pre_trn as mst 
				left join product_mst as pro on pro.product_id=mst.product_id
				left join unit_mst as cat on cat.unitid=mst.unitid
				left join unit_mst as cat_con on cat_con.unitid=mst.conv_unitid
				left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
				left join tbl_ledger as ven on ven.l_id=mst.vender_id
				left join tbl_set_main_process as sm on sm.sp_id = mst.sp_id
				left join tbl_sales_order as tso on tso.sales_order_id = mst.so_id
				left join tbl_purchasecardtrn as crtrn on crtrn.purchasecardtrn_id=mst.purchasecardtrn_id
				where mst.pre_trn_status=0 and pre_id=".$POST['pre_id']." Order By pre_id Desc";
			}else{
				$query = "select pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name,ven.l_name,mst.*, cat.unit_name, cat_con.unit_name as conv_unit_name, crtrn.price, crtrn.rate_tolerance, sm.po_req_no,tso.sales_order_no, pro.product_alias_name from tbl_pre_trn as mst 
				left join product_mst as pro on pro.product_id=mst.product_id
				left join unit_mst as cat on cat.unitid=mst.unitid
				left join unit_mst as cat_con on cat_con.unitid=mst.conv_unitid
				left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
				left join tbl_ledger as ven on ven.l_id=mst.vender_id
				left join tbl_set_main_process as sm on sm.sp_id = mst.sp_id
				left join tbl_sales_order as tso on tso.sales_order_id = mst.so_id
				left join tbl_purchasecardtrn as crtrn on crtrn.purchasecardtrn_id=mst.purchasecardtrn_id
				where mst.pre_trn_status=3 and mst.user_id=".$_SESSION['user_id']." Order By pre_id Desc";
			}

			$data_e = $dbcon->query($query);
			//$str.=$query;
			$str.='<table class="display table table-bordered table-striped" style="width:100%;">
					<tr>';
						$str .='<th width="10%" class="text-center">Sr. No</th>';
						if($companyConfiguration['po_work_order_wise']==1){
							$str .='<th width="20%" class="text-center">Sales Order No</th>';
							$str .='<th width="20%" class="text-center">Work Order No</th>';
						}
						$str .='<th width="20%" class="text-center">Product Name</th>
						<th width="10%" class="text-center">Product Qty</th>
						<th width="10%" class="text-center">Product Rate</th>
						<th width="2%" class="text-center">Vender</th>
						<th width="5%" class="text-center">Document</th>
						<th width="3%" class="text-center">Action</th>
					</tr>
					<tbody>';
			$i=1;
			if(mysqli_num_rows($data_e) > 0 ){
				while($row = brp_mysqli_fetch_array($data_e)){
					if(in_array('drawing',$pro_search)){
			            $drawing_number = " -- (".$row['drawing_number'].")";
			        }
			        if(in_array('item',$pro_search)){
			            $item_code = " -- (".$row['product_icode'].")";
			        }
			        if(in_array('alias',$pro_search)){
			            $alias = " -- (".$row['product_alias_name'].")";
			        }
					if($row['unitid']!=$row['conv_unitid']){
						$show_qty="<strong style='color:green'>".number_format($row['product_qty'], 4, '.', '')." ".$row['unit_name']."</strong> </br> <strong style='color:orange'>".number_format($row['product_conv_qty'], 4, '.', '')." ".$row['conv_unit_name']."</strong>";
					}else{
$qty = is_numeric($row['product_qty']) ? (float)$row['product_qty'] : 0;
$show_qty = "<strong style='color:green'>" . number_format($qty, 4, '.', '') . " " . $row['unit_name'] . "</strong>";
					}
					
					$over_tol = '';
					if($row['price'] != ''){
						if($row['rate']>$row['price']){
							$tole_rate = ($row['price']*$row['rate_tolerance'])/100;
							$tol_rate  = $row['price']+$tole_rate;
							if($row['rate']>$tol_rate){
								$over_tol .= "<strong><span style='color:red'>Over Tolerance Rate</span></strong>";
							} 
						}	
					}
					
					$str .='<tr>';
						$str .='<td class="text-center">'.$i.'</td>';
						if($companyConfiguration['po_work_order_wise']==1){
							$str .='<td>'.$row['sales_order_no'].'</td>';
							$str .='<td>'.$row['po_req_no'].'</td>';
						}
						$str .='<td>'.$row['product_name'].' '.$item_code.' '.$drawing_number.' '.$alias.'<br>Desc : '.$row['product_desc'].'</td>
						<td>'.$show_qty.'</td>
						<td>'.$row['rate'].'<br>'.$over_tol.'</td>
						<td>'.$row['l_name'].'</td>
						<td>';
						if($row['att_doc'] != ''){	
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
					
					$i++;
				}
			}else{
				$str.= '<tr><td colspan="6" class="text-center">NO DATA FOUND</td></tr>';
			}
				$str.= '</tbody>
				</table>';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "edit_data") {
			$q = $dbcon -> query("SELECT trn.*,pro.product_name,pro.product_type FROM tbl_pre_trn as trn
				left join product_mst as pro on pro.product_id=trn.product_id
			 WHERE trn.pre_trn_id = '$POST[id]'");
			
			$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "load_rate") {
			$rate = get_po_card_rate($dbcon,$_POST['product_id'],$_POST['vender_id'],$_POST['unit_id']);
			$row['rate'] = $rate['price'];
			$row['purchasecardtrn_id']   = $rate['purchasecardtrn_id'];
			//var_dump($row);
			echo json_encode($row);
		}
		else if (strtolower($POST['mode']) == "delete_data") {
			$row = array();
			$info['pre_trn_status'] = 2; 
			$info3['status'] = 2;

			// Update main table first
			$updateid = update_record('tbl_pre_trn', $info, "pre_trn_id=" . $POST['id'], $dbcon);

			// Check if related record exists in tbl_request_product
			$chk_sql = "SELECT pre_trn_id FROM tbl_request_product WHERE pre_trn_id = " . intval($POST['id']);
			$chk_qry = $dbcon->query($chk_sql);

			if ($chk_qry && $chk_qry->num_rows > 0) {
				// Only update if record exists
				$updateid2 = update_record('tbl_request_product', $info3, "pre_trn_id=" . $POST['id'], $dbcon);
			} else {
				$updateid2 = true; // Consider true since there's nothing to update
			}

			// Return result
			if ($updateid && $updateid2)
				$row['res'] = "1";
			else
				$row['res'] = "0";

			echo json_encode($row);
		}


		else if(strtolower($POST['mode'])== "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$products = get_pre_product($dbcon,'');
			$companyConfiguration=getCompanyConfiguration($dbcon);
			if(empty($products)){
				$arr['msg'] = "2";
			} else {
			    $pre_no	= load_common_no($dbcon, MANUAL_INDENT_SERIES, $POST['invoicetype_id']);
				update_common_no($dbcon, MANUAL_INDENT_SERIES, $POST['invoicetype_id']);
				$info['pre_no']		= $pre_no;
				$info['pre_date']	= date('Y-m-d',strtotime($POST['pre_date']));
				$info['remark']		= $_POST['remark'];

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
				$indent_p = "select * from tbl_pre_trn where pre_trn_status=0 and pre_id=".$inderid;
				$indent_e = $dbcon->query($indent_p);
				while($row = mysqli_fetch_array($indent_e)){
					
					$prod_p = "select * from product_mst where product_id=".$row['product_id'];
					$prod_e = $dbcon->query($prod_p);
					$prod_r = mysqli_fetch_array($prod_e);
					
					
					$indenttrn['indent_no']			= load_common_no($dbcon,INDENT_SERIES);
					
					$query_invoicetype 	= $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=17 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
					
					$indenttrn['indent_date']		= date('Y-m-d',strtotime($POST['pre_date']));
					$indenttrn['rp_req_date']		= date('Y-m-d',strtotime($POST['pre_date']));
					$indenttrn['rp_req_qty']		= $row['product_qty'];
					$indenttrn['purchase_unit']		= $row['conv_unitid'];
					$indenttrn['process_unit']		= $row['unitid'];
					$indenttrn['rp_pid']			= $row['product_id'];
					$indenttrn['branch_id']			= $branch_id;
					$indenttrn['indent_status']		= 1;
					$indenttrn['rp_req_type']		= "direct";
					
					$indenttrn['sr_no']				= 0;
					$indenttrn['sales_order_id']	= $row['so_id'];	
					$indenttrn['sp_id']				= $row['sp_id'];
					$indenttrn['req_qty_one']		= 0;
					$indenttrn['rp_po_base_qty']	= $row['product_qty'];
					$indenttrn['rp_po_qty']			= $row['product_conv_qty'];	
					$indenttrn['in_process_qty']	= 0;				
					$indenttrn['out_process_qty']	= 0;
					$indenttrn['perent_id']			= 0;
					$indenttrn['reserve_stock']		= 0;
					$indenttrn['product_remark']	= $row['product_desc'];
					
					if(!empty($indenttrn['sp_id'])){
						$indenttrn['main_request']		= 0;
					}else{
						$indenttrn['main_request']		= 1;
					}
					
					$indenttrn['pre_trn_id']		= $row['pre_trn_id'];
					
					$indenttrn['cdate']				= date("Y-m-d H:i:s");;
					$indenttrn['user_id']			= $_SESSION['user_id'];
					$indenttrn['company_id']		= $_SESSION['company_id'];
					
					$indenttid = add_record('tbl_request_product', $indenttrn, $dbcon, $branch_id);

					if($companyConfiguration['automatic_approval_indent']==1){
						$approve_no=load_common_no($dbcon,JOURNAL_SERIES);
						update_common_no($dbcon,JOURNAL_SERIES);
						
						$info['approve_no']					= $approve_no;
						$info['approve_date']				= date("Y-m-d");
						$info['rp_id']						= $indenttid;
						$info['approve_base_qty']			= $row['product_qty'];
						$info['approve_qty']				= $row['product_conv_qty'];
						$info['approve_base_unit']			= $row['unitid'];
						$info['approve_unit']				= $row['conv_unitid'];
						$info['delivery_date']				= date("Y-m-d H:i:s");
						$info['quotation_requirement']		= 0;
						$info['cdate']						= date("Y-m-d H:i:s");
						$info['user_id']					= $_SESSION['user_id'];
						$info['company_id']					= $_SESSION['company_id'];
						
						
						$inserpoid=add_record('approve_indent', $info, $dbcon, $branch_id);
						
						if($row['product_conv_qty']==$row['product_conv_qty']){

							$inftrn['indent_status'] = 3;
							$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$indenttid , $dbcon, $branch_id);
						}
						
						$query_used="select * from tbl_request_product as rpro
							where rp_id=".$indenttid." and company_id = '".$_SESSION['company_id']."' ";
						$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
					
						
						$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
						
							$total=$row['product_conv_qty']*$rate;
							$infpotrn['approve_indent_id']	= $inserpoid;
							$infpotrn['purchaseorder_id']	= '0';
							$infpotrn['product_desc']		= $rel_used['product_remark'];
							$infpotrn['product_type']		= '';
							$infpotrn['product_id']			= $rel_used['rp_pid'];
							$infpotrn['product_base_qty']	= $row['product_qty'];
							$infpotrn['product_qty']		= $row['product_conv_qty'];
							$infpotrn['product_rate']		= $rate;
							$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
							//$infpotrn['unit_id']			= get_pro_field($dbcon,$pr_id,'product_base_unit');
							$infpotrn['unit_id']			= $row['conv_unitid'];
							$infpotrn['base_unit_id']		= $row['unitid'];
							$infpotrn['product_amount']		= $total;
							$infpotrn['total']				= $total;
							$infpotrn['parent_pro']			= 0;
							$infpotrn['main_pro_status']	= 1;//Requested products
							$infpotrn['user_id']			= $_SESSION['user_id'];
							$infpotrn['po_ref_id']			= $indenttid;
							$infpotrn['po_ref_type']		= '0';
							$infpotrn['po_bom_id']			= '';
							$infpotrn['po_bom_trn_id']		= '';
							$infpotrn['mdate']				= date('Y-m-d');
							$infpotrn['company_id']			= $_SESSION['company_id'];
							
						
						if($info['quotation_requirement']==0){
							$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $branch_id);
						}
					}
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
			$info['remark']		= $_POST['remark'];
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
		else if(strtolower($POST['mode']) == "so_to_workorder_load")
		{
			$soid=$POST['soid'];
			$edit_id = $POST['edit_id'];
			//echo $soid;
			if($soid!='' && $soid!=0 )
			{
				$str = "";
				 $q = "SELECT tsmp.sp_id,tsmp.po_req_no FROM `tbl_set_main_process` as tsmp left join tbl_sales_ordertrn as tso on tso.sales_ordertrn_id = tsmp.sales_order_trn_id
				where tso.sales_order_id =".$soid." and tsmp.company_id=" . $_SESSION['company_id'];
				$sel = $dbcon->query($q);
				$str .= "<option value=''>--Select Work Order No--</option>";
				while ($row = brp_mysqli_fetch_assoc($sel))
				{
					 if ($edit_id == $row['sp_id'])
					{
						$select = 'selected';
					}
					else
					{
						$select = '';
					} 

					$str .= "<option value='" . $row['sp_id'] . "' " . $select . ">" . $row['po_req_no'] . "</option>";
				}
				echo $str;
			}
			else
			{
				$str = "";
				echo  $q = "select * from tbl_set_main_process where company_id=" . $_SESSION['company_id'];
				$sel = $dbcon->query($q);
				$str .= "<option value=''>--Select Work Order No--</option>";
				while ($row = brp_mysqli_fetch_assoc($sel))
				{
					if ($edit_id == $row['sp_id'])
					{
						$select = 'selected';
					}
					else
					{
						$select = '';
					}

						$str .= "<option value='" . $row['sp_id'] . "' " . $select . ">" . $row['po_req_no'] . "</option>";
				}
				echo $str;
			}
		} else if (strtolower($POST['mode']) == "open_inq_email") {
			// $set = "select inq_email_content from tbl_company where company_id=" . $_SESSION['company_id'];
			// $set_head = mysqli_fetch_assoc($dbcon->query($set));
			$email_content = 'Hello Sir, <br> Your Indent No. : '.$POST['indent_no'];
			$resp['email_content']    = $email_content;
			$resp['to_email_id']    = strtolower($POST['cust_email']);
		
			echo json_encode($resp);

		} 
		
		else if (strtolower($POST['mode']) == "send_mail") {
// 			echo '<pre>';
// 			print_r($_SESSION);
// 			echo '</pre>';exit;
            $cur_user_id = $_SESSION['user_id'];
			$cur_user = getUserDetailById($dbcon, $cur_user_id);
			$user['user_name'] = $cur_user['user_name'];
			$user['user_email'] = $cur_user['user_mail'] ? $cur_user['user_mail'] : $cur_user['common_email_id'];
			$email_subject = $_POST['email_subject'];
			$email_content = $_POST['email_content'];
			$to = $_POST['to_email_id'];

			// CC 
			$query = "select website as email,company_name from tbl_company where company_id=" . $_SESSION['company_id'];
			$company = mysqli_fetch_assoc($dbcon->query($query));
			$ccemails = [];
			$ccemails[] = array('email' => $company['email'], 'name' => $company['company_name']);
			if ($_POST['ccemail_id']) {
				$ccemail_id_d = explode(';', $_POST['ccemail_id']);
				foreach ($ccemail_id_d as $val) {
					$ccemails[]['email'] = $val;
				}
			}

			// BCC
			$bccemail[] = array('email' => $user['user_email'], 'name' => $user['user_name']);
			if ($_POST['bccemail_id']) {
				$bccemail_id_d = explode(';', $_POST['bccemail_id']);
				foreach ($bccemail_id_d as $val) {
					$ccemails[]['email'] = $val;
				}
			}
            $files = array();
			array_push($files, $file);
			$res = common_print_send_email($to, $user, $email_subject, $email_content, $attachment_path, $files, $ccemails, $bccemail);
			unlink(MAIL_ATTACH_UPING . $file);

			echo $res;
			
// 			$inquiry_id = strtolower($POST['email_ref_id']);
// 			$from_email = strtolower($POST['from_email']);
// 			$to_email_id = strtolower($POST['to_email_id']);
// 			$ccemail_id = strtolower($POST['ccemail_id']);
// 			$bccemail_id = strtolower($POST['bccemail_id']);
// 			$email_subject = $_POST['email_subject'];
// 			$email_content = $_POST['email_content'];
// 			if (!empty($_FILES['email_attach']['tmp_name'])) {
// 				$file = upload_mail_attch_file($_FILES, $dbcon);
// 			}

			
// 			$set = "SELECT user_email FROM `users` where user_id=" . $_SESSION['user_id'];
// 			$set_head = mysqli_fetch_assoc($dbcon->query($set));

// 			$files = array();
// 			array_push($files, $file);
// 			$resp = final_send_email($set_head['user_email'],$to_email_id, $ccemail_id, $bccemail_id, $email_subject, $email_content, $files);
// 			unlink(MAIL_ATTACH_UPING . $file);
		
// 			$arr['msg'] = array();
// 			if ($resp['code'] == 'success') {
// 				$arr['msg'] = '1';
// 			} else {
// 				$arr['msg'] = '0';
// 			}
// 			echo json_encode($arr);
		} else if (brp_strtolower($POST['mode'] == 'send_quotation_whatsapp')) {
			$email_page_path = $_POST['email_page_path'];
		
			include('../../../print/view/quotation_print_whatsapp.php');
			// include('../../../print/view/'.$email_page_path.'.php');
		
			$pre_id = $_POST['pre_id'];
			$query  = "select quot_subject,quot_header, quotation_no from tbl_quotation where quotation_id=" . $quotation_id;
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result);
		
			$quot_header_msg = $row['quot_header'];
			$mobile_no = str_replace('-', '', $_POST['cust_mobile']);
		
			$save_file = 'Yes';
			$file_name = whatsapp_quotation_print($dbcon, $quotation_id, $save_file);
			$attachment_path  = DOMAIN . 'upload/mail_attach/' . $file_name;
			$template_name = "quotation_sharing";
			$res = send_whatsapp_message($dbcon, $mobile_no, $attachment_path, $template_name);
			unlink($attachment_path);
		
			$arr = array();
			$arr['msg'] = $res;
			echo json_encode($arr);
		} else if (strtolower($POST['mode']) == "get_series_no") {
			$query = "select * from tbl_invoicetype where status=0 and type_id=" . trim($POST['type_id']) . " and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		} else if (strtolower($POST['mode']) == "load_invoiceno") {
		    $invoicetype_id = $POST['typeid'];
			$row = array();
			$row['invoiceno'] = load_common_no_for_manual_indent($dbcon, MANUAL_INDENT_SERIES,$invoicetype_id);
			echo json_encode($row);
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