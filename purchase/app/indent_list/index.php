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

			$date = " and po.rp_req_date between '" . date('Y-m-d', strtotime($s_date[0])) . "' AND '" . date('Y-m-d', strtotime($s_date[1])) . "'";
			$where.=" $where_db ".$date;

			$where_company=check_company('po');

			$where.=" $where_company";

			//$where_user=check_user('po');

			//$where.=" $where_user";

			$companyConfiguration=getCompanyConfiguration($dbcon);
			$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
			$pro_search=explode(",", $purchase_pro_search);
			$appData = array();
			$i=1;
			$aColumns = array('po.indent_no','po.indent_date','po.rp_po_qty','unit.unit_name','spro.po_req_no','used_qty','pmst.product_name','pmst.product_icode', 'dr.drawing_number', 'pmst.product_alias_name','tc.cat_name','po.rp_id','bms.branch_name','po.indent_status','po.branch_id','po.shortclose_qty','po.sp_id','us.user_name','pmst.product_base_unit','pmst.product_conv_unit','po.rp_pid','po.product_remark');
			$sIndexColumn = "po.rp_id";
			$isWhere = array("po.jobwork_type = 0 AND po.status !=2 AND po.indent_status in (".$POST['po_type_status'].")".$where);
			$sTable = "tbl_request_product as po";			
			$isJOIN = array('left join tbl_set_main_process as spro on spro.sp_id=po.sp_id','left join product_mst as pmst on pmst.product_id=po.rp_pid','left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id','left join tbl_category as tc on pmst.product_category=tc.cat_id','left join branch_mst as bms on bms.branch_id=po.branch_id','left join unit_mst as unit on unit.unitid=po.purchase_unit','left join unit_mst as cunit on cunit.unitid=pmst.product_conv_unit','left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0  group by req.rp_id) as rereq on rereq.rp_id=po.rp_id','left join users as us on us.user_id=po.user_id');
			$hOrder = "po.rp_id desc";
			$hGroupby = array("po.rp_id");
			include($path.'include/pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			$getspecialConfiguration=getspecialConfiguration($dbcon);
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

		        if($row['purchase_unit']==$row['product_base_unit']){
		        	$type="conv_unit"; 
		        	$unit_name  = getunitname($dbcon,$row['product_conv_unit']);
		        }else{
		        	$type="base_unit";
		        	$unit_name  = getunitname($dbcon,$row['product_base_unit']); 
		        }

		        $ret_req_conv='';$max_approve_qty_conv='';$shortclose_qty_conv='';

				$max_approve_qty=round($row['rp_po_qty'],4)-$row['used_qty']-$row['shortclose_qty'];

		        if($row['product_base_unit']!=$row['product_conv_unit']){
		        	$ret_qty=convert_stock($dbcon,$row['rp_po_qty'],$row['rp_pid'],$type);
		        	$ret_req_conv=round_up($ret_qty,5).' '.$unit_name;

		        	$max_approve_qty_c = convert_stock($dbcon,$max_approve_qty,$row['rp_pid'],$type);
		        	$max_approve_qty_conv=round_up($max_approve_qty_c, 5).' '.$unit_name;

		        	$shortclose_qty_c = convert_stock($dbcon,$row['shortclose_qty'],$row['rp_pid'],$type);

		        	$shortclose_qty_conv=round_up($shortclose_qty_c, 5).' '.$unit_name;
		        }

		        $product_remark = "";

		        if(!empty($row['product_remark'])){
		        	$product_remark = '</br>'. $row['product_remark'];
		        }

				$row_data[] = $id;
				$row_data[] = $row['indent_no'];
				$row_data[] = date('d M, Y',strtotime($row['indent_date']));
				
				if($companyConfiguration['po_work_order_wise']==1){
					$so_no = "select sales_order_trn_id from tbl_request_product where sp_id =".$row['sp_id']." and main_request=1";
					$q = $dbcon->query($so_no);
					$r = brp_mysqli_fetch_array($q);

					$get_so = "select so.sales_order_no from tbl_sales_ordertrn as trn
					left join tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
					where trn.sales_ordertrn_id=".$r['sales_order_trn_id']; 
					$exe = $dbcon->query($get_so);
					$rel = brp_mysqli_fetch_array($exe);
					//var_dump($get_so);
					$row_data[] = $rel['sales_order_no'];
				}

				$row_data[] = $row['po_req_no'];
				$row_data[] = $row['product_name']." ".$drawing_number." ".$item_code." ".$alias. $product_remark;
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				if($row['branch_id'] == '10000'){
					$row_data[] = 'All Branch';
				}else{
					$row_data[] = $row['branch_name'];
				}
				$row_data[] = round_up($row['rp_po_qty'], 5).' '.$row['unit_name'].' '.$ret_req_conv;
				$row_data[] = round_up($max_approve_qty,5).' '.$row['unit_name'].' '.$max_approve_qty_conv;
				$row_data[] = round_up($row['shortclose_qty'],5).' '.$row['unit_name'].' '.$shortclose_qty_conv;
				/*$row_data[] = $row['unit_name'];*/
				$row_data[] = $row['user_name'];
				
				$add_po_btn = '';$done = '';$indent_view='';
				if($POST['po_type_status']==3){
					$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
					$rels=mysqli_fetch_assoc($menusql);
					$menu_show_permissions = explode(",",$rels['print_permission']);
					$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 18 AND approve_status = 1 AND status = 0 ORDER BY priority");
					while($res = mysqli_fetch_assoc($sql)){
						if(in_array($res['id'],$menu_show_permissions)) {
							$indent_view.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['rp_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
						}
					}
				}else{
					$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
					$rels=mysqli_fetch_assoc($menusql);
					$menu_show_permissions = explode(",",$rels['print_permission']);
					$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 17 AND approve_status = 1 AND status = 0 ORDER BY priority");
					while($res = mysqli_fetch_assoc($sql)){
						if(in_array($res['id'],$menu_show_permissions)) {
							$indent_view.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['rp_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
						}
					}
				}
				if(in_array(INDENT_APPROVE,$bulkAccessArray)){
					if($row['indent_status'] == '1'){
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Approve Indent" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'indent_approve/'.$row['rp_id'].'"><i class="fa fa-plus"></i></a>';
						
						$shortclose='<a onclick="shortcloseindent('.$row['rp_id'].','.$max_approve_qty.')" class="btn btn-xs btn-danger" data-original-title="Sort Close Indent" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i></a>';

						$delete = '<button type="button" class="btn btn-xs btn-danger" data-original-title="Indent Delete" data-toggle="tooltip" data-placement="top" onClick="delete_indent('.$row['rp_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
					
					if($row['indent_status'] == '3'){
						$done='<button type="button" class="btn btn-sm btn-success" data-original-title="Indent Done" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i> Done</button>';
						if($row['shortclose_qty'] != '0'){
							$reason ='<a onclick="shortclosereason('.$row['rp_id'].')" class="btn btn-xs btn-info" data-original-title="Sort Close Reason" data-toggle="tooltip" data-placement="top"><i class="fa fa-info"></i></a>';
						}

						$view = '<button type="button" class="btn btn-sm btn-primary" data-original-title="Indent Done" data-toggle="tooltip" data-placement="top" onClick="open_approve_history('.$row['rp_id'].',\''.$row['indent_no'].'\')"><i class="fa fa-eye"></i> View</button>';
					}
				}

				$remark = '';
				$que = "select req.pre_trn_id,req.rp_req_type,pre.remark from tbl_request_product as req 
				left join tbl_pre_trn as ptr on ptr.pre_trn_id=req.pre_trn_id
				left join tbl_pre as pre on pre.pre_id = ptr.pre_id
				where req.rp_req_type='direct' and req.rp_id=".$row['rp_id'];

				$result = $dbcon->query($que);
				$res = brp_mysqli_fetch_array($result);
				if($res['rp_req_type']=='direct' && $res['remark']!=''){
					$remark = '<a onclick="show_remark('.$res['pre_trn_id'].',\''.$row['indent_no'].'\',\''.$res['remark'].'\')" class="btn btn-xs btn-primary" data-original-title="Indent Remark" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
				}

				$row_data[] = $add_po_btn." ".$done." ".$shortclose." ".$reason." ".$indent_view." ".$view." ".$delete." ".$remark;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$product_detail = get_product_detail($dbcon,$POST['product_id']);

			if($product_detail['product_base_unit'] != $product_detail['product_conv_unit']){
				if($POST['unit_id'] == $product_detail['product_base_unit']){
					$type="conv_unit";
					$unit_name  = getunitname($dbcon,$product_detail['product_conv_unit']);
					$ret_qty=convert_stock($dbcon,$POST['apr_qty'],$POST['product_id'],$type);
				}else{
					$type="base_unit";
					$unit_name  = getunitname($dbcon,$product_detail['product_base_unit']);
					$ret_qty=convert_stock($dbcon,$POST['apr_qty'],$POST['product_id'],$type);
				}	
			}else{
				$ret_qty=$POST['apr_qty'];
			}

			$approve_no=load_common_no($dbcon,JOURNAL_SERIES);
			update_common_no($dbcon,JOURNAL_SERIES);
			$info['approve_no']					= $approve_no;
			$info['approve_date']				= date("Y-m-d");
			$info['rp_id']						= $POST['work_order_id'];
			$info['approve_qty']				= $POST['apr_qty'];
			$info['approve_base_qty']			= $ret_qty;
			$info['product_desc']				= $_POST['product_desc'];
			$info['approve_unit']				= $POST['unit_id'];
			$info['approve_base_unit']			= $product_detail['product_base_unit'];
			$info['delivery_date']				= date("Y-m-d H:i:s");
			$info['quotation_requirement']		= $POST['quotation_requirement'];
			$info['cdate']						= date("Y-m-d H:i:s");
			$info['user_id']					= $_SESSION['user_id'];
			$info['company_id']					= $_SESSION['company_id'];
			
			
			$inserpoid=add_record('approve_indent', $info, $dbcon, $branch_id);
			
			/*var_dump(round_up($POST['max_approve_qty'],5));
			var_dump(round_up($POST['approve_qty'],5));*/
			/////////////////////////Harshil - 27-9-2022///////////////////////////////////////
			$getspecialConfiguration=getspecialConfiguration($dbcon);
			if($getspecialConfiguration['filter_concept_permission']==1)
			{
				$inftrn['indent_status'] = 3;
				$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$POST['work_order_id'] , $dbcon, $branch_id);
			}
			else
			{
				if((round_up($POST['max_approve_qty'],5)>=round_up($POST['approve_qty'],5)) || (round_up($POST['max_approve_conv_qty'],5)>=round_up($ret_qty,5))){
					$inftrn['indent_status'] = 3;
					$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$POST['work_order_id'] , $dbcon, $branch_id);
				}
			}
			///////////////////////////Harshil  - 27-9-2022 END///////////////////////////////////
			$query_used="select * from tbl_request_product as rpro
				where rp_id=".$POST['work_order_id']." and company_id = '".$_SESSION['company_id']."' ";
			$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
		
			
			$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
			
				$total=$POST['apr_qty']*$rate;
				$infpotrn['approve_indent_id']  = $inserpoid;
				$infpotrn['purchaseorder_id']	= '0';
				$infpotrn['product_type']		= '';
				$infpotrn['product_id']			= $rel_used['rp_pid'];
				$infpotrn['product_qty']		= $POST['apr_qty'];
				$infpotrn['product_base_qty']	= $ret_qty;
				$infpotrn['product_desc']		= $_POST['product_desc'];
				$infpotrn['product_rate']		= $rate;
				$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
				//$infpotrn['unit_id']			= get_pro_field($dbcon,$pr_id,'product_base_unit');
				$infpotrn['unit_id']			= $POST['unit_id'];
				$infpotrn['base_unit_id']		= $product_detail['product_base_unit'];
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
		else if(strtolower($POST['mode']) == "indent_delete") {
			
			$info['indent_status'] 		= 2;
			$query = "select sp_id,main_request,sales_order_trn_id,rp_req_type from tbl_request_product where rp_id=".$POST['eid'];
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_array($result);

			if($row['main_request']=='1' && $row['rp_req_type'] != 'direct'){
				$info['status'] 	= 2;
				$info1['sp_status'] = 2;
				$info2['sales_order_production_status'] =2;

				$updateid1=update_record('tbl_set_main_process', $info1,"sp_id=".$row['sp_id'] , $dbcon);
				$updateid2=update_record('tbl_sales_order_production_trn', $info2,"sales_ordertrn_id=".$row['sales_order_trn_id'] , $dbcon);
			}else{
				$info['status'] = 3;
				$info['indent_no'] = "";
				$info['indent_date'] = "";
				$info['indent_status'] = 0;
			}

			
			
			$updateid=update_record('tbl_request_product', $info,"rp_id=".$POST['eid'] , $dbcon, $branch_id);
			
			if($updateid){
				echo "1";
			}else{
				echo "0";
			}
		}

		else if(strtolower($POST['mode']) == "sortclose_indent") {
			$info['shortclose_qty'] 	= $POST['pending_qty'];
			$info['shortclose_remark'] 	= $_POST['remark'];
			$info['indent_status'] 		= 3;
			
			//short close time reindent generate in short close pending 
			//$new_indent_generate = new_indent_generate($dbcon,$POST['rp_id'],$POST['pending_qty']);
			//short close time reindent generate in short close End
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

		else if(strtolower($POST['mode']) == "indent_hist_datatable") {

			$where='';
			$where.=" log.approve_indent_status=0 and log.rp_id=".$POST['rp_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.approve_indent_id', 'usr.user_name', 'log.approve_no', 'log.approve_date', 'log.approve_qty','unit.unit_name', 'log.user_id','ptr.purchaseordertrn_id','log.rp_id');
			$sIndexColumn = "log.approve_indent_id";
			$isWhere = array(" ".$where." ");
			$sTable = "approve_indent as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id','left join unit_mst as unit on unit.unitid = log.approve_unit', 'left join tbl_purchasetrntemp as ptr on ptr.approve_indent_id = log.approve_indent_id');
			$hOrder = "log.approve_indent_id desc";
			include($include.'/pagging.php');
			//echo $sQuery;
			//exit;
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['approve_no'];
				$row_data[] = date("d-M-Y",strtotime($row['approve_date']));
				$row_data[] = $row['approve_qty']." ".$row['unit_name'];
				$row_data[] = $row['user_name'];

				$query = "SELECT IFNULL(SUM(used_qty), 0) AS used_qty FROM tbl_purchaseorder_req_trn WHERE purchaseordertrn_req_status=0";

					if (!empty($row['purchaseordertrn_id'])) {
						$query .= " AND req_id IN (" . $row['purchaseordertrn_id'] . ")";
					}

					$q = $dbcon->query($query);
					$r = mysqli_fetch_array($q);

				$unapproved='';
				if($r['used_qty']==0){
					$unapproved = '<button type="button" class="btn btn-sm btn-primary" data-original-title="Unapproved" data-toggle="tooltip" data-placement="top" onClick="un_approve_indent('.$row['rp_id'].','.$row['purchaseordertrn_id'].')"><i class="fa fa-reply"></i> Unapproved</button>';
				}
				
				$row_data[] = $unapproved;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "un_approve_indent") {
			$query = "select approve_indent_id from tbl_purchasetrntemp where purchaseordertrn_id=".$POST['purchaseordertrn_id'];
			$q = $dbcon->query($query);
			$r = mysqli_fetch_array($q);

			$info['indent_status'] 		= 1;
			
			$updateid=update_record('tbl_request_product', $info,"rp_id=".$POST['rp_id'] , $dbcon);

			$info1['purchaseordertrn_status']	= 2;

			$updateid=update_record('tbl_purchasetrntemp', $info1,"purchaseordertrn_id=".$POST['purchaseordertrn_id'] , $dbcon);

			$info2['approve_indent_status'] = 2;

			$updateid=update_record('approve_indent', $info2,"approve_indent_id=".$r['approve_indent_id'] , $dbcon);

			if($updateid){
				$row['msg']	= 1;
			}else{
				$row['msg']	= 0;
			}
			echo json_encode($row);
		}

function new_indent_generate($dbcon,$rp_id,$pendingqty){
	$query = "select * from tbl_request_product where rp_req_type='min_max' and rp_id=".$rp_id;
	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	$row = brp_mysqli_fetch_array($result);
	if($cnt>0){
		$info['sp_id']					= $row['sp_id'];				 
		$info['sr_no']					= $row['sr_no'];
		$info['rp_req_no']				= $row['rp_req_no'];
		$info['rp_req_date']			= date('Y-m-d');
		$info['rp_pid']					= $row['rp_pid'];
		$info['rp_req_qty']				= $pendingqty;
		$info['req_qty_one']			= $row['req_qty_one'];
		$info['rp_po_base_qty']			= '';
		$info['in_process_qty']			= '';
		$info['in_process_conv_qty']	= '';		
		$info['out_process_qty']		= '';
		$info['rp_req_type']			= 'indent_short_close';
		$info['rp_po_req_no']			= '';
		$info['rp_process_req_no']		= '';
		$info['cdate']					= date("Y-m-d H:i:s");
		$info['user_id']				= $_SESSION['user_id'];
		$info['company_id']				= $_SESSION['company_id'];
		$info['status']					= 3;
		$info['row_cnt']				= '';
		$info['process_unit']			= $row['process_unit'];
		$info['purchase_unit']			= $row['purchase_unit'];
		$info['reserve_status']			= '';
		$info['used_rp_req_qty']		= '';
		$info['used_status']			= '';
		$info['perent_id']				= $row['perent_id'];
		$info['reserve_stock']			= '';
		$info['main_request']			= $row['main_request'];
		$info['indent_no']				= '';
		$info['indent_date']			= '';
		$info['indent_status']			= 0;
		$info['job_card_no']			= '';
		$info['job_card_date']			= '';
		$info['job_card_status']		= '';
		$info['reject_status']			= '';
		$info['sales_order_trn_id']		= $row['sales_order_trn_id'];
		$info['branch_id']				= $row['branch_id'];
		$info['finish_used_qty']		= '';
		$info['finish_status']			= '';
		$info['product_version']		= $row['product_version'];
		$info['pre_trn_id']				= '';
		$info['shortclose_qty']			= 0;
		$info['shortclose_remark']		= '';
		$info['work_order_no']			= $row['work_order_no'];
		$info['work_order_date']		= $row['work_order_date'];
		$info['work_order_status']		= $row['work_order_status'];
		$info['bom_id']					= $row['bom_id'];
		$info['approval_status']		= '';
		$info['jobwork_type']			= '';
		$info['customer_id']			= $row['customer_id'];
		$info['purchaseordertrn_id']	= '';
 		$info['customer_req_material']	= $row['customer_req_material'];
		$info['customer_req_grade']		= $row['customer_req_grade'];
		$info['customer_req_size']		= $row['customer_req_size'];
		$info['customer_req_id']		= $row['customer_req_id'];
		$info['customer_req_length']	= $row['customer_req_length'];
		$info['customer_req_heat']		= $row['customer_req_heat'];
		$info['customer_req_coc']		= $row['customer_req_coc'];
		$info['customer_ref_no']		= $row['customer_ref_no'];
		$info['customer_asset_serial']	= $row['customer_asset_serial'];
		$info['customer_bevel_spec']	= $row['customer_bevel_spec'];
		$info['store_order_id']			= $row['store_order_id'];

		$inserpotrnid=add_record('tbl_request_product', $info, $dbcon);

	}
}
?>