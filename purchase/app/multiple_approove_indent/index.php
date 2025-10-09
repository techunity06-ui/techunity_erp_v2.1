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
				INDENT_APPROVE
		]);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		//$branch=$_SESSION['branch_id'];
		$where='';

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('po', $branch_id);
		$where.=" $where_db ";

		$where_company=check_company('po');

		$where.=" $where_company";

		$where_user=check_user('po');

		$where.=" $where_user";

		$appData = array();
		$i=1;
		$aColumns = array('po.indent_no','po.indent_date','po.rp_po_qty','unit.unit_name','spro.po_req_no','used_qty','pmst.product_name','tc.cat_name','po.rp_id','bms.branch_name','po.indent_status');
		$sIndexColumn = "po.rp_id";
		$isWhere = array("po.indent_status in (".$POST['po_type_status'].")".$where);
		$sTable = "tbl_request_product as po";			
		$isJOIN = array('left join tbl_set_main_process as spro on spro.sp_id=po.sp_id','left join product_mst as pmst on pmst.product_id=po.rp_pid','left join tbl_category as tc on pmst.product_category=tc.cat_id','left join branch_mst as bms on bms.branch_id=po.branch_id','left join unit_mst as unit on unit.unitid=po.purchase_unit','left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0  group by req.rp_id) as rereq on rereq.rp_id=po.rp_id');
		$hOrder = "po.rp_id desc";
		$hGroupby = array("po.rp_id");
		include($include.'pagging.php');
		//echo $squery;
		$appData = array();
		$id=1;
		//print_r($sqlReturn);
		foreach($sqlReturn as $row) {
			$row_data = array();
			$max_approve_qty=$row['rp_po_qty']-$row['used_qty'];
			$row_data[] = $id;
			$row_data[] = $row['indent_no'];
			$row_data[] = date('d M, Y',strtotime($row['indent_date']));
			$row_data[] = $row['po_req_no'];
			$row_data[] = $row['product_name'];
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = $row['branch_name'];
			$row_data[] = $row['rp_po_qty'];
			$row_data[] = $max_approve_qty;
			$row_data[] = $row['unit_name'];
			
			$add_po_btn = '';
			if(in_array(INDENT_APPROVE,$bulkAccessArray)){
				if($row['indent_status'] == '1'){
					$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Approve Indent" data-toggle="tooltip" data-placement="top" href="'.ROOT.'indent_approve/'.$row['rp_id'].'"><i class="fa fa-plus"></i></a>';
				}
			}
				
			$row_data[] = $add_po_btn;
		 
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
			$infpotrn['mdate']			= date('Y-m-d');
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
		
	}else if(strtolower($POST['mode']) == "load_pending_indent"){
		$html = '';
		$where = '';$where1 = ''; $where2 = '';
		if($POST['work_ono'] != ''){
			$where .= " and spro.po_req_no='$POST[work_ono]'";
		}

		if($POST['sp_id'] != ''){
			$where .= " and po.sp_id in (".$POST['sp_id'].")";
		}

		if($POST['indent_no'] != ''){
			$where1 = " and po.indent_no='$POST[indent_no]'";
		}
		
		if($POST['product_id'] != ''){
			$where2 .= " and pmst.product_name='$POST[product_id]'";
		}

		if($POST['branch_id'] != ''){
			$where2 .= " and po.branch_id='$POST[branch_id]'";
		}
		
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);

		$query="SELECT SQL_CALC_FOUND_ROWS po.indent_no, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, used_qty, pmst.product_name, pmst.product_desc,tc.cat_name, po.rp_id, po.rp_pid, bms.branch_name, po.indent_status,po.sp_id, po.branch_id,pmst.product_icode, dr.drawing_number, pmst.product_alias_name, po.product_remark, pmst.product_base_unit, pmst.product_conv_unit, po.purchase_unit

		FROM tbl_request_product as po 

		left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
		left join product_mst as pmst on pmst.product_id=po.rp_pid 
		left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id
		left join tbl_category as tc on pmst.product_category=tc.cat_id 
		left join branch_mst as bms on bms.branch_id=po.branch_id 
		left join unit_mst as unit on unit.unitid=po.purchase_unit 
		left join (select IFNULL(sum(req.approve_qty),0) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 

		where ( 1 AND po.indent_status in (1) and po.status !=2  and po.company_id=".$_SESSION['company_id']." ".$where." ".$where1." ".$where2.") Group by po.rp_id ORDER BY po.rp_id desc";
		$result=$dbcon->query($query);
		$cnt = brp_mysqli_num_rows($result);
		$html .= '<div class="form-group">
			<div class="col-md-12" style="margin-top:10px;text-align:left">
				<span><strong>Approove Data Select In '.$cnt.' Out Of <span id="chk_sel_count"></span> </strong> </span>
			</div>
			<div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
					<tr id="field">
						<th width="2%" class="text-center">
							<input type="checkbox" id="all_chk_box" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_all();updateCounter();">
						</th>
						<th width="10%" class="text-center">Branch</th>
						<th width="25%" class="text-center">Description</th>
						<th width="8%" class="text-center">Approve Qty</th>
						<th width="8%" class="text-center">Quotation Requirement</th>
						<th width="40%" class="text-center">Product Description</th>
					</tr>';
				$i=1;
				if($cnt>0){
					while($rel_trn=brp_mysqli_fetch_assoc($result)){
						$cat_name = ($rel_trn['cat_name']!=null) ? $rel_trn['cat_name'] : 'PRIMARY';

						if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel_trn['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel_trn['product_icode'].")";
				        }
				        if(in_array('alias',$pro_search)){
				            $alias = " -- (".$rel_trn['product_alias_name'].")";
				        }

				        if($rel_trn['product_remark']){
				        	$rel_trn['product_desc'] = $rel_trn['product_remark'];
				        }else{
				        	$rel_trn['product_desc'] = $rel_trn['product_desc'];
				        }
						$query_used="select round(IFNULL(sum(approve_qty),0),4) as used_qty from approve_indent as rpro
						where approve_indent_status=0 and rp_id=".$rel_trn['rp_id'];
						$rel_used=brp_mysqli_fetch_assoc($dbcon->query($query_used));
						$max_approve_qty=round($rel_trn['rp_po_qty'],5)-$rel_used['used_qty'];

						$remark = '';

						$que = "select req.pre_trn_id,req.rp_req_type,pre.remark from tbl_request_product as req 
									left join tbl_pre_trn as ptr on ptr.pre_trn_id=req.pre_trn_id
									left join tbl_pre as pre on pre.pre_id = ptr.pre_id
									where req.rp_req_type='direct' and req.rp_id=".$rel_trn['rp_id'];

						$result_1 = $dbcon->query($que);
						$res = brp_mysqli_fetch_array($result_1);

						if($res['rp_req_type']=='direct' && $res['remark']!=''){
							$remark = $res['remark'];
						}

						$ret_req_conv = '';
						if($rel_trn['product_base_unit'] != $rel_trn['product_conv_unit']){
							if($rel_trn['purchase_unit'] == $rel_trn['product_base_unit']){
								$type="conv_unit";
								$unit_name  = getunitname($dbcon,$rel_trn['product_conv_unit']);
								$ret_qty=convert_stock($dbcon,$rel_trn['rp_po_qty'],$rel_trn['rp_pid'],$type);
								
								$ret_req_conv='<tr>
									<td><strong>Conv Unit</strong></td>
									<td>: '.round_up($ret_qty,5).' '.$unit_name.'</td>
								</tr>';
							}else{
								$type="base_unit";
								$unit_name  = getunitname($dbcon,$rel_trn['product_base_unit']);
								$ret_qty=convert_stock($dbcon,$rel_trn['rp_po_qty'],$rel_trn['rp_pid'],$type);
								$ret_req_conv='<tr>
									<td><strong>Base Unit</strong></td>
									<td> : '.round_up($ret_qty,5).' '.$unit_name.'</td>
								</tr>';
							}	
						}
						//var_dump($query_used);
						$getspecialConfiguration=getspecialConfiguration($dbcon);
						$html .='<tr>
									<td style="vertical-align:top;text-align:center;">
										<input type="checkbox" name="che_box[]" class="chk_box" id="che_box'.$i.'" value="'.$rel_trn['rp_id'].'" style="width: 23px;height: 23px;margin-top: 0px;" onchange="check_box_limit(this.id);updateCounter();">
									</td>
									<td style="vertical-align:top;">';
									if($_SESSION['user_type'] == '2'){
										if($rel_trn['branch_id'] == '10000'){
											$html .= 'All Branch';
										}else{
											$html .= $rel_trn['branch_name'];
										}
										$html .= '<input type="hidden" name="branch_id_1[]" id="branch_id_1'.$i.'" value="'.$rel_trn['branch_id'].'">';
									}else{
										$html .= '<input type="hidden" name="branch_id_1[]" value="'.$rel_trn['branch_id'].'">';
									}
									$html .='</td>
									<td style="vertical-align:top;">
										<table style="width:100%">
											<tr>
												<td style="width:50%"><strong>Category Name </strong></td>
												<td style="width:50%">: '.$cat_name.'</td>
											</tr>
											<tr>
												<td><strong>Indent No  </strong></td>
												<td>: '.$rel_trn['indent_no'].'</td>
											</tr>
											
											<tr>
												<td><strong>Indent Date </strong></td>
												<td>: '.date('d-m-Y',strtotime($rel_trn['indent_date'])).'</td>
											</tr>
											
											<tr>
												<td><strong>Work Order No </strong></td>
												<td>: '.$rel_trn['po_req_no'].'</td>
											</tr>';
											if($getspecialConfiguration['hermattic_permission']==1){
												
												$so_no = "select sales_order_trn_id from tbl_request_product where sp_id =".$rel_trn['sp_id']." and main_request=1";
												$q = $dbcon->query($so_no);
												$r = brp_mysqli_fetch_array($q);	

												$get_so = "select so.sales_order_no from tbl_sales_ordertrn as trn
												left join tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
												where trn.sales_ordertrn_id=".$r['sales_order_trn_id']; 
												$exe = $dbcon->query($get_so);
												$rel = brp_mysqli_fetch_array($exe);									
												$html .='<tr>
													<td><strong>Sales Order No</strong></td>
													<td>: '.$rel['sales_order_no'].'</td>
												</tr>';
											}
											$html .='<tr>
												<td><strong>Product Name </strong></td>
												<td>: '.$rel_trn['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias.'</td>
											</tr>
											
											<tr>
												<td><strong>Product Qty </strong></td>
												<td>: '.number_format($rel_trn['rp_po_qty'],4,'.','').' '.$rel_trn['unit_name'].' <input type="hidden" name="rp_po_qty[]" value="'.round_up($rel_trn['rp_po_qty'],5).'"></td>
											</tr>';

											$html .=$ret_req_conv;

											if($remark != ''){
												$html .= '<tr>
													<td><strong>Indent Remark </strong></td>
													<td> : '.$remark.' </td>
												</tr>';
											}
										$html .='</table>
									</td>
									<td style="vertical-align:top;" class="text-center">
										<input name="apr_qty[]" id="apr_qty'.$i.'" type="text" class="form-control approve_qty" title="Approve qty" value="'.round_up($max_approve_qty,5).'" placeholder="Approve qty" max="'.$max_approve_qty.'" onkeyup="copy_qty(this.value,'.$i.')">
										<input name="approve_qty[]" id="approve_qty'.$i.'" type="hidden" class="form-control approve_qty" title="Approve qty" value="'.round_up($max_approve_qty,5).'" placeholder="Approve qty" max="'.round_up($max_approve_qty,5).'">

										<input type="hidden" name="max_approve_qty[]" id="max_approve_qty'.$i.'" value="'.round_up($max_approve_qty,5).'" />
									</td>
									
									<td>
										<select class="form-control quotation_requirement" name="quotation_requirement[]" id="quotation_requirement'.$i.'">
											<option value="0">No</option>
											<option value="1">Yes</option>
										</select>
									</td>
									<td>
									 <textarea class="form-control product_desc" id="product_desc'.$i.'" name="product_desc[]" placeholder="Enter Product Description">'.$rel_trn['product_desc'].'</textarea>
									 </td>
								</tr>';
							$i++;
					}
				}else{
					$html .= '<tr>
						<td style="text-align:center" colspan="5">No Data Yet...!!</td>
					</tr>';
				}
		$html .='</table>
			</div>
		</div>';
		$resp['html_resp']=$html;
		$resp['row']=$i;
		echo json_encode($resp);
		
	}
	else if(strtolower($POST['mode']) == "work_order_no") {
		$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];				
		echo get_pending_work_order($dbcon, $branch_id);
	}
	else if(strtolower($POST['mode']) == "sales_order_no"){
		$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];				
		echo get_pending_sales_order($dbcon, $branch_id);
	}
	else if(strtolower($POST['mode']) == "get_indent_no") {
		$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];				
		echo get_pending_indent_no($dbcon, $branch_id);
	}else if(strtolower($POST['mode']) == "get_pro") {
		$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];				
		echo get_indent_pending_product($dbcon, $branch_id);
	}else if(strtolower($POST['mode']) == "get_work_o_no") {
		$sp_id=$POST['id'];			

		echo get_salesorderwise_workorder_no($dbcon,$sp_id);
	}else if(strtolower($POST['mode']) == "work_or_in") {
		$work_order_id=$POST['id'];				
		echo get_workorderwise_indent_no($dbcon,$work_order_id);
	}else if(strtolower($POST['mode']) == "get_indentnowise_pro") {
		$indent_no_id=$POST['id'];				
		echo get_indentnowise_pro($dbcon,$indent_no_id);
	}else if(strtolower($POST['mode']) == "multiple_indent_approove"){
		
		foreach ($POST['approve_qty'] as $key => $name) {
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id'][$key]) && $POST['branch_id'][$key]) ? $POST['branch_id'][$key] : $_SESSION['branch_id'];
				$ind_q = "select rpro.*,pro.product_name,unit.unit_name,tc.cat_name,spro.po_req_no from tbl_request_product as rpro
				left join product_mst as pro on pro.product_id=rpro.rp_pid
				left join tbl_category as tc on pro.product_category=tc.cat_id
				left join unit_mst as unit on unit.unitid=rpro.purchase_unit
				left join tbl_set_main_process as spro on spro.sp_id=rpro.sp_id
				where rp_id=".$POST['indent_id'][$key];
				
				$rs_type=$dbcon->query($ind_q);
				
				$row=mysqli_fetch_assoc($rs_type);
				
				$product_detail = get_product_detail($dbcon,$row['rp_pid']);

				/*var_dump($key);
				var_dump($product_detail['product_name'].' -- ('.$product_detail['product_icode'].')');
				var_dump($POST['indent_id'][$key]);
				var_dump($POST['approve_qty'][$key]);
				var_dump($_POST['product_desc'][$key]);*/
				if($product_detail['product_base_unit'] != $product_detail['product_conv_unit']){
					if($row['purchase_unit'] == $product_detail['product_base_unit']){
						$type="conv_unit";
						$unit_name  = getunitname($dbcon,$product_detail['product_conv_unit']);
						$ret_qty=convert_stock($dbcon,$POST['approve_qty'][$key],$row['rp_pid'],$type);
					}else{
						$type="base_unit";
						$unit_name  = getunitname($dbcon,$product_detail['product_base_unit']);
						$ret_qty=convert_stock($dbcon,$POST['approve_qty'][$key],$row['rp_pid'],$type);
					}	
				}else{
					$ret_qty=$POST['approve_qty'][$key];
				}
				$product_desc ='';
				
				if(!empty($_POST['product_desc'][$key])){
					$product_desc = $_POST['product_desc'][$key];	
				}

				$approve_no=load_common_no($dbcon,18);
				$info['approve_no']					= $approve_no;
				$info['approve_date']				= date("Y-m-d");
				$info['rp_id']						= $POST['indent_id'][$key];
				$info['approve_qty']				= $POST['approve_qty'][$key];
				$info['approve_base_qty']			= $ret_qty;
				$info['product_desc']				= $product_desc;
				$info['approve_unit']				= $row['purchase_unit'];
				$info['approve_base_unit']			= $row['process_unit'];
				$info['delivery_date']				= date("Y-m-d H:i:s");
				$info['quotation_requirement']		= $POST['quotation_requirement'][$key];	
				$info['cdate']						= date("Y-m-d H:i:s");
				$info['user_id']					= $_SESSION['user_id'];
				$info['company_id']					= $_SESSION['company_id'];
				
				//var_dump($info);

				$inserpoid=add_record('approve_indent', $info, $dbcon, $branch_id);
				
				/////////////////////////Harshil - 29-9-2022///////////////////////////////////////


				////////check maulik---open code/////////////////////
					$getspecialConfiguration=getspecialConfiguration($dbcon);
					if($getspecialConfiguration['filter_concept_permission']==1)
					{
						$inftrn['indent_status'] = 3;
						//var_dump($inftrn);
						//exit();
						$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$POST['indent_id'][$key] , $dbcon, $branch_id);
						
					}
					else
					{
						if($POST['max_approve_qty'][$key]==$POST['approve_qty'][$key])
						{
								$inftrn['indent_status'] = 3;
								$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$POST['indent_id'][$key] , $dbcon, $branch_id);
						}
					}
					
					
					
					
				///////////////////////////Harshil  - 29-9-2022 END///////////////////////////////////
				
				
				
				

				$query_used="select * from tbl_request_product as rpro
				where rp_id=".$POST['indent_id'][$key]." and company_id = '".$_SESSION['company_id']."' ";
				
				
				$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));

				$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
				
				$infpotrn['approve_indent_id']  = $inserpoid;
				$total=$POST['approve_qty'][$key]*$rate;
				$infpotrn['purchaseorder_id']	= '0';
				$infpotrn['product_type']		= '';
				$infpotrn['product_id']			= $rel_used['rp_pid'];
				$infpotrn['product_qty']		= $POST['approve_qty'][$key];
				$infpotrn['product_base_qty']	= $ret_qty;
				$infpotrn['product_desc']		= $product_desc;
				$infpotrn['product_rate']		= $rate;
				$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
				$infpotrn['unit_id']			= $row['purchase_unit'];
				$infpotrn['base_unit_id']		= $row['process_unit'];
				$infpotrn['product_amount']		= $total;
				$infpotrn['total']				= $total;
				$infpotrn['parent_pro']			= 0;
				$infpotrn['main_pro_status']	= 1;//Requested products
				$infpotrn['user_id']			= $_SESSION['user_id'];
				$infpotrn['po_ref_id']			= $POST['indent_id'][$key];
				$infpotrn['po_ref_type']		= '0';
				$infpotrn['po_bom_id']			= '';
				$infpotrn['po_bom_trn_id']		= '';
				$infpotrn['mdate']				= date('Y-m-d');
				$infpotrn['company_id']			= $_SESSION['company_id'];
				
				//var_dump($infpotrn);
				
				if($info['quotation_requirement']==0){
					$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $branch_id);
				}
			}
			
				
			if($inserpoid){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
	}
?>