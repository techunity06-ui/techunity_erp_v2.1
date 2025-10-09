<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$companyConfiguration=getCompanyConfiguration($dbcon);
//print_r($companyConfiguration);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PO_REQ_ADD
		]);
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		//$branch=$_SESSION['branch_id'];
		$where='';
		//	$where.="  and purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			/*if($POST['po_type_status']!=''){
				$where.=" and po.po_trn_req_status=".$POST['po_type_status'];
				$_SESSION['po_type_status_filter']=$POST['po_type_status'];
			}*/
		//	$where.=" and po.branch_id=$branch";
			$today_date = date('Y-m-d');
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);

			$date = " and DATE(po.mdate) between '" . date('Y-m-d', strtotime($s_date[0])) . "' AND '" . date('Y-m-d', strtotime($s_date[1])) . "'";
			$where.=" $where_db ".$date;

			$where_company=check_company('po');

			$where.=" $where_company";

			//$where_user=check_user('po');

			$where.=" $where_user";

			$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
			$pro_search=explode(",", $purchase_pro_search);
			if($companyConfiguration['po_work_order_wise'] == 1){
				$group=",po_ref_id,po_ref_type";
				$left=" left join tbl_request_product as res on res.rp_id=po.po_ref_id  left join tbl_set_main_process as setm on setm.sp_id=res.sp_id";
				$pera=",res.indent_no,res.sp_id,res.indent_date,setm.po_req_no,setm.po_req_date";
			}

			if($POST['po_type_status'] == 1){
				$left_join = " left join(select group_concat(led.l_name) as vendor_name,preq.rp_id,preq.req_id from tbl_purchaseorder_req_trn as preq
				left join tbl_ledger as led on led.l_id = preq.vender_id
				where preq.purchaseordertrn_req_status=0) as po_ven on po_ven.req_id = po.purchaseordertrn_id";
				$parameter = ",vendor_name";
			}else{
				$left_join = " left join tbl_ledger as qled on qled.l_id = poq.vender_id left join tbl_ledger as inled on inled.l_id=prtr.vender_id left join tbl_ledger as led on led.l_id=ppp.vendor_id";
				$parameter = ",led.l_name,qled.l_name as vendor_name,inled.l_name as vendor";
			}

			$appData = array();
			$i=1;
			$aColumns = array('po.purchaseordertrn_id','po.mdate','tc.cat_name','pr.product_name','pr.product_icode', 'dr.drawing_number', 'pr.product_alias_name','bms.branch_name','po.total','po.purchaseordertrn_status','po.cdate','po.user_id','po.po_ref_type','sum(po.product_qty) as pqty','po.po_ref_id','po.product_id','po.po_trn_req_status','GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id','pr.product_base_unit','pr.product_conv_unit','po.unit_id','unit.unit_name', 'po.branch_id','req.product_remark'.$pera.$parameter);
			$sIndexColumn = "po.purchaseordertrn_id";
			$isWhere = array("po.purchaseordertrn_status = 0 and po_trn_req_status=".$POST['po_type_status'].$where);
			$sTable = "tbl_purchasetrntemp as po";			
			$isJOIN = array('left join product_mst as pr on pr.product_id=po.product_id','left join unit_mst as unit on unit.unitid=po.unit_id','left join tbl_drawing as dr on dr.drawing_id = pr.drawing_id','left join tbl_category as tc on pr.product_category=tc.cat_id','left join po_quotation as poq on poq.po_quotation_id=po.po_quotation_id','left join branch_mst as bms on bms.branch_id=po.branch_id','left join (
				SELECT cardtrn.product_id, cardtrn.vendor_id FROM tbl_purchasecardtrn as cardtrn
				left join tbl_product_party_purchase as pcard on pcard.party_purchase_id = cardtrn.party_purchase_id
			 	where pcard.card_status=0 and cardtrn.purchasecardtrn_status=0 and cardtrn.valid_date>="'.$today_date.'" and cardtrn.affected_date<="'.$today_date.'" and pcard.is_aproove=1 and pcard.is_active=0 group by cardtrn.product_id ) ppp on pr.product_id=ppp.product_id','left join tbl_request_product as req on req.rp_id=po.po_ref_id','left join tbl_pre_trn as prtr on prtr.pre_trn_id=req.pre_trn_id'.$left_join.$left);
			$hOrder = "po.purchaseordertrn_id desc";
			$hGroupby = array("po.po_quotation_id,po.product_id,po.po_trn_req_status".$group);
			include($include.'pagging.php');
			//echo $sQuery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			
			foreach($sqlReturn as $row) {
				$row_data = array();
				$sono='';

				if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
		        if(in_array('alias',$pro_search)){
		            $alias = " -- (".$row['product_alias_name'].")";
		        }

		         $product_remark = "";

		        if(!empty($row['product_remark'])){
		        	$product_remark = '</br>'. $row['product_remark'];
		        }


		        if($row['unit_id']==$row['product_base_unit']){
		        	$type="conv_unit"; 
		        	$unit_name  = getunitname($dbcon,$row['product_conv_unit']);
		        }else{
		        	$type="base_unit";
		        	$unit_name  = getunitname($dbcon,$row['product_base_unit']); 
		        }
		        $ret_req_conv='';
		        if($row['product_base_unit']!=$row['product_conv_unit']){
		        	$ret_qty=convert_stock($dbcon,$row['pqty'],$row['product_id'],$type);
		        	$ret_req_conv=number_format($ret_qty, 4, '.', '').' '.$unit_name;
		        }

				if(!empty($row['po_req_no'])){
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
						$sono .= "<span style='white-space:nowrap;'><strong>Sales Order No : </strong>".$rel['sales_order_no']." </span></br>";
					}
					$wodata="<span style='white-space:nowrap;'><strong>Work Order No : </strong>".$row['po_req_no']." </span></br> <span style='white-space:nowrap;'><strong>Work Order Date : </strong>".date('d M, Y',strtotime($row['po_req_date']))." </span></br>".$sono;
				}else{
					$wodata="";
				}
				$indata="<span style='white-space:nowrap;'><strong>Indent No : </strong>".$row['indent_no']." </span></br> <span style='white-space:nowrap;'><strong>Indent Date : </strong>".date('d M, Y',strtotime($row['indent_date']))." </span></br>";
				$row_data[] = $id;
				if($companyConfiguration['po_work_order_wise'] == 1){
					$row_data[] = $wodata." ".$indata;
				}
				$row_data[] = date('d M, Y',strtotime($row['mdate']));
				$row_data[] = $row['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias. $product_remark;
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				if($POST['po_type_status'] == 1){
					$row_data[] = $row['vendor_name'];
				}else{
					if($row['vendor_name'] != ""){
						$row_data[] = $row['vendor_name'];
					}else if($row['vendor'] != ""){
						$row_data[] = $row['vendor'];
					}else{
						$row_data[] = $row['l_name'];
					}
				}
				$row_data[] = $row['branch_name'];
				$row_data[] = number_format($row['pqty'],4,'.','').' '.$row['unit_name'].' '.$ret_req_conv;
				//$row_data[] = $row['pen_qty'];
				//$row_data[] = $row['reqested_qty'];
			
				//$query="select sum(product_qty) as used_qty from tbl_purchaseordertrn where purchaseordertrn_status=0 and temptrn_ref_id=".$row['purchaseordertrn_id'];
			if($POST['po_type_status']==0){	
				$query="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id in (".$row['purchastrn_id'].")";
				
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
				$pending_qty=$row['pqty']-$rel['used_qty'];
				$pending_qty_conv='';$used_qty_conv='';
				if($row['product_base_unit']!=$row['product_conv_unit']){
					$pending_qty_c=convert_stock($dbcon,$pending_qty,$row['product_id'],$type);
		        	$pending_qty_conv=number_format($pending_qty_c, 4, '.', '').' '.$unit_name;

		        	$used_qty_c = convert_stock($dbcon,$rel['used_qty'],$row['product_id'],$type);

		        	$used_qty_conv = number_format($used_qty_c, 4, '.', '').' '.$unit_name;
				}
				
				$row_data[] = number_format($pending_qty,4,'.','').' '.$row['unit_name'].' '.$pending_qty_conv;
				$row_data[] = number_format($rel['used_qty'],4,'.','').' '.$row['unit_name'].' '.$used_qty_conv;
				if($pending_qty>0){
					if(in_array(PO_REQ_ADD,$bulkAccessArray)){
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'po_req_add/'.$row['product_id'].'/'.$row['po_ref_type'].'/'.$row['branch_id'].'/'.$row['po_ref_id'].'"><i class="fa fa-plus"></i></a>';
					}
				}else{
					$add_po_btn='';
				}
			}else{
				$row_data[] = "";
				$row_data[] = number_format($row['pqty'],4,'.','').' '.$row['unit_name'].' '.$ret_req_conv;
				$add_po_btn='';
			}

			$remark = '';
			$que = "select COUNT(temp.purchaseordertrn_id) as cnt  from tbl_purchasetrntemp as temp 
				left join tbl_request_product as req on req.rp_id = temp.po_ref_id
				left join tbl_pre_trn as ptr on ptr.pre_trn_id=req.pre_trn_id
				left join tbl_pre as pre on pre.pre_id = ptr.pre_id
				where req.rp_req_type='direct' and pre.remark !='' and temp.purchaseordertrn_id in (".$row['purchastrn_id'].")";

			$result = $dbcon->query($que);
			$res = brp_mysqli_fetch_array($result);

			if($res['cnt']>0){
				$remark = '<a onclick="show_remark(\''.$row['purchastrn_id'].'\')" class="btn btn-xs btn-info" data-original-title="Indent Remark" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
			}
				//$row_data[] = $row['po_ref_type'];
			    //if($row['po_trn_req_status']=='1'){
				// if(!$row['pen_qty']){    
					/*$row_data[] ='<label class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Requested</label>';*/
				//	$add_po_btn='';
				//}
				//else{
					/*$row_data[] ='<label class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Pending</label>';*/
					
				//	$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'po_req_add/'.$row['product_id'].'/'.$row['po_ref_type'].'"><i class="fa fa-plus"></i></a>';
				//} 
				
				// $poprint='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poprint/'.$row['purchaseordertrn_id'].'"><i class="fa fa-print"></i></a>';
				
				$row_data[] = find_user_name($dbcon,$row['user_id']);
					
				$row_data[] = $add_po_btn.' '.$poprint.' '.$remark;
			 
			$appData[] = $row_data;
			$id++;
			}

			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "fetch_done"){
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$where='';
		
			$today_date = date('Y-m-d');
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);

			$date = " and po.mdate between '" . date('Y-m-d', strtotime($s_date[0])) . "' AND '" . date('Y-m-d', strtotime($s_date[1])) . "'";
			$where.=" $where_db ".$date;

			$where_company=check_company('po');

			$where.=" $where_company";

			$where.=" $where_user";

			$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
			$pro_search=explode(",", $purchase_pro_search);
			
			$appData = array();
			$i=1;
			$aColumns = array('po.purchaseordertrn_id','po.product_qty','unit.unit_name','req.indent_no','req.indent_date','pr.product_name','pr.product_icode','pr.product_alias_name','dr.drawing_number','led.l_name','pod.purchaseorder_no','pod.purchaseorder_date','preq.used_qty','ptrn.product_qty as po_qty','bms.branch_name','us.user_name','ptrn.unit_id','ptrn.conv_unit_id','ptrn.product_conv_qty','ptrn.product_id');
			$sIndexColumn = "po.purchaseordertrn_id";
			$isWhere = array("po.po_trn_req_status=1 and po.purchaseordertrn_status=0".$where);
			$sTable = "tbl_purchasetrntemp as po";			
			$isJOIN = array('left join tbl_request_product as req on req.rp_id = po.po_ref_id','left join tbl_purchaseorder_req_trn as preq on preq.rp_id=po.po_ref_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=preq.purchaseordertrn_id','left join tbl_purchaseorder as pod on pod.purchaseorder_id = ptrn.purchaseorder_id','left join tbl_ledger as led on led.l_id=pod.vender_id','left join product_mst as pr on pr.product_id=po.product_id','left join unit_mst as unit on unit.unitid=po.unit_id','left join tbl_drawing as dr on dr.drawing_id = pr.drawing_id','left join tbl_category as tc on pr.product_category = tc.cat_id','left join branch_mst as bms on bms.branch_id=pod.branch_id','left join users as us on us.user_id = pod.userid');
			$hOrder = "po.purchaseordertrn_id desc";
			//$hGroupby = array("");
			include($include.'pagging.php');
			//echo $sQuery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			
			foreach($sqlReturn as $row) {
				$row_data = array();
				$sono='';

				if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
		        if(in_array('alias',$pro_search)){
		            $alias = " -- (".$row['product_alias_name'].")";
		        }

		        if($row['unit_id']==$row['conv_unit_id']){
		        	$unit_name  = getunitname($dbcon,$row['conv_unit_id']);
		        	$indent_qty = $row['used_qty'].' '.$unit_name;
		        	$po_qty     = $row['po_qty'].' '.$unit_name;
				}else{
		        	$unit_name  = getunitname($dbcon,$row['unit_id']); 
		        	$conv_unit_name  = getunitname($dbcon,$row['conv_unit_id']); 
		        	$type="base_unit";
					$ret_qty=convert_stock($dbcon,$row['used_qty'],$row['product_id'],$type);
					$indent_qty = $ret_qty.' '.$unit_name.'<br>'.$row['used_qty'].' '.$conv_unit_name;

					$po_qty     = $row['po_qty'].' '.$unit_name.'<br>'.$row['product_conv_qty'].' '.$conv_unit_name;
		        }

				
				$row_data[] = $id;
				
				$row_data[] = $row['indent_no'];
				$row_data[] = date('d-m-Y',strtotime($row['indent_date']));
				$row_data[] = $row['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias;
				$row_data[] = $row['l_name'];
				$row_data[] = $row['purchaseorder_no'];
				$row_data[] = date('d-m-Y',strtotime($row['purchaseorder_date']));
				
				$row_data[] = $indent_qty;
				$row_data[] = $po_qty;
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['user_name'];
					
				$row_data[] = '';
				 
				$appData[] = $row_data;
				$id++;
			}

			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {

				$info['po_req_mode']		= 1;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				//$updateid=update_record('tbl_purchaseorder', $info, "purchaseorder_id=".$POST['eid'] , $dbcon);
				/* Entry In trn Table Start */
					//$deleteid=delete_record('tbl_purchaseordertrn', "purchaseorder_id=".$POST['eid'], $dbcon);	
					foreach ($POST['product_id'] as $key => $name) {
						//var_dump($name);
						$info1['po_trn_req_status']	= $POST['po_trn_req_status'][$key];
						$info1['product_type']		= $POST['product_type'][$key];
						$info1['product_id']		= $POST['product_id'][$key];
						$info1['main_pro_status']	= $POST['main_pro_status'][$key];
						$info1['product_qty']		= $POST['product_qty'][$key];
						$info1['purchaseorder_id']	= $POST['eid'];
						$info1['user_id']			= $_SESSION['user_id'];
						//var_dump($info1);
						//$inserid=add_record('tbl_purchaseordertrn', $info1, $dbcon);
					}
				/* Entry In trn Table End */
		
				if($inserestimateid){	
					$arr['msg']="1";
				}
				else{
					$arr['msg']="0";
				}
					
			echo json_encode($arr);
			
		}
		else if(strtolower($POST['mode']) == "req_po_to_main_po") {
			
			$sp_array=$POST['check_status'];
			$deleteid=delete_record('tbl_purchaseordertrn',"user_id=".$_SESSION['user_id']." and purchaseordertrn_status=3 and purchaseorder_id=0", $dbcon);
			for($k=0;$k<count($sp_array);$k++)
			{
				if($POST['check_status'][$k]=="2")
				{
					$loop_id=$k;
					$eid=$POST['product_id'][$loop_id];
					$purchaseordertrn_id=$POST['purchaseordertrn_id'][$loop_id];
					$pr_rate=get_pro_field($dbcon,$eid,'product_purchase_rate');
					$potemp_id=$POST['potemp_id'][$loop_id];
					$po_ref_id=$POST['po_ref_id'][$loop_id];
					
					/*$que_po="select min(party_rate) as mrate from tbl_product_party_purchase where party_product=".$eid;
					$resi=$dbcon->query($que_po);
					$re_po=mysqli_fetch_assoc($resi);
					
					$que_po1="select party_rate from tbl_product_party_purchase where party_id=".$POST['vender_id']." and party_product=".$eid."  order by party_purchase_id desc limit 1 " ;
					$resi1=$dbcon->query($que_po1);
					$re_po1=mysqli_fetch_assoc($resi1);
					
					
					$query_used="select quo.product_rate from tbl_purchasetrntemp as rpro 
							left join po_quotation as quo on quo.po_quotation_id=rpro.po_quotation_id
							where purchaseordertrn_status=0 and po_trn_req_status=0 and rpro.po_quotation_id!=0 and rpro.product_id=".$eid;
						$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));
					if(!empty($rel_used['product_rate'])){
						$pr_rate=$rel_used['product_rate'];
					}else{
						if(!empty($re_po1['party_rate'])){
							$pr_rate=$re_po1['party_rate'];
						}else{
							$pr_rate=$re_po['mrate'];
						}
					}*/
					
					
					$que_product_desc="select * from tbl_purchasetrntemp where purchaseordertrn_id=".$purchaseordertrn_id."<br>";
					
					$rs_di_product_desc=$dbcon->query($que_product_desc);
					$re_product_desc=brp_mysqli_fetch_assoc($rs_di_product_desc);
					
					
					
					$que="select ta.*,hsn.hsn_code as hcode,ta.product_desc,ta.product_spec from product_mst as ta 
					left join mst_hsn_code as hsn on hsn.hsn_id = ta.product_hsn
					where product_id=".$eid;
					
					$rs_di=$dbcon->query($que);
					$re=brp_mysqli_fetch_assoc($rs_di);
					
					
					$discount_percentage=0;
					if($POST['man_rate'][$loop_id] != '' && $POST['man_rate'][$loop_id]!='0.00'){
						$pr_rate = $POST['man_rate'][$loop_id];
						$purchasecardtrn_id = $POST['purchasecardtrn_id'][$loop_id];
					}else{
						$rate = get_po_card_rate($dbcon,$eid,$POST['vender_id'],$re['product_conv_unit']);
						$pr_rate = $rate['price']; 
						$purchasecardtrn_id = $rate['purchasecardtrn_id'];
						$discount_percentage = $rate['discount_percentage'];
						// $eid is product_id
					}
					
					$unit_id=$POST['product_uom'][$loop_id];
					$po_qty=$POST['product_alloc_qty'][$loop_id];
					if($re['product_conv_unit']==$unit_id){
						$type="base_unit";
						$con_stock=$po_qty;
						$base_stock=convert_stock_new($dbcon,$po_qty,$eid,$type);
					}else{
						$type="conv_unit";
						$base_stock=$po_qty;
						$con_stock=convert_stock_new($dbcon,$po_qty,$eid,$type);
					}
					
					$company_state = get_company_data($dbcon,$_SESSION['company_id']);
					//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
					$sale_gst = get_tax_cat_by_hsn($dbcon,$re['hcode']);
					
					$custLedgerDetails = get_cust_data_arr($dbcon,$POST['vender_id']);
					$ven_s = "select stateid from tbl_ledger where l_id=".$POST['vender_id'];
					$ves=$dbcon->query($ven_s);
					$vers = mysqli_fetch_array($ves); 
					

					$pro_amt = $pr_rate*$con_stock;
					$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
					$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
					$igst_tax_rate=0;$igst_tax_rate_conv=0;
					if(($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
						$gst = $sale_gst['tax_gst']/2;
						$cgst_tax_per = $gst;
						$cgst_tax_rate = ($gst*$pro_amt)/100;
						$cgst_tax_rate_conv = ($gst*$pro_amt)/100;
						$sgst_tax_per = $gst;
						$sgst_tax_rate = ($gst*$pro_amt)/100;
						$sgst_tax_rate_conv = ($gst*$pro_amt)/100;
					}else{
						$igst_tax_per = $sale_gst['tax_gst'];
						$igst_tax_rate = ($sale_gst['tax_gst']*$pro_amt)/100;
						$igst_tax_rate_conv = ($sale_gst['tax_gst']*$pro_amt)/100;
					}
					
					$info1['temptrn_ref_id']	= $potemp_id;
					$info1['product_id']		= $eid;
					$info1['product_des']		= $re_product_desc['product_desc'];
					$info1['pro_spe']			= $re['product_spec'];
					$info1['product_qty']		= $base_stock;
					$info1['product_conv_qty']	= $con_stock;
					$info1['product_hsn_code']	= $re['hcode'];
					$info1['unit_id']			= $re['product_base_unit'];
					$info1['conv_unit_id']		= $re['product_conv_unit'];
					$info1['rate_unit']			= $re['product_conv_unit'];
					
					$info1['currency_id'] 		= $_SESSION['currency_id'];
					$info1['currency_rate'] 	= 1;

					$disc=0;
					if($discount_percentage!=""){
						$pm =$pr_rate*$con_stock;
						$disc = $pm*$discount_percentage/100;
						$info1['product_discount']		= $disc;
						$info1['product_discount_conv']	= $disc;
						$info1['discount_per'] 			= $discount_percentage; 
					}


					
					$info1['cgst_tax_per'] 		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
					$info1['sgst_tax_per'] 		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
					$info1['igst_tax_per'] 		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
					
					$info1['cgst_tax_rate'] 	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
					$info1['sgst_tax_rate'] 	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
					$info1['igst_tax_rate'] 	= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;

					$info1['product_rate']		= $pr_rate;
					$info1['product_amount']	= $pr_rate*$con_stock-$disc;
					$info1['product_amount_tax']= $cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
					$info1['total']				= $info1['product_amount'];


					$info1['sgst_tax_rate_conv']	= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
					$info1['cgst_tax_rate_conv']	= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
					$info1['igst_tax_rate_conv']	= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;

					$info1['product_currency_rate']	= $pr_rate;
					$info1['product_currency_amount'] = $pr_rate*$con_stock-$disc;
					$info1['product_currency_amount_tax']	= $cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;
					
					
					$info1['currency_total']		= $info1['product_currency_amount'];
					
					$info1['product_tax_cat'] = $sale_gst['tax_cat_id'];
					
					/* $query="select product_purchase_gst from product_mst as trn
							where trn.product_id=".$eid;
					$result=$dbcon->query($query);
					$rel=mysqli_fetch_assoc($result);
						
					$query1="select stateid from tbl_ledger as trn
							where trn.l_id=".$POST['vender_id'];
					$result1=$dbcon->query($query1);
						$rel1=mysqli_fetch_assoc($result1);
						
						$query2="select stateid from tbl_company as trn
								where trn.company_id=".$_SESSION['company_id'];
						$result2=$dbcon->query($query2);
						$rel2=mysqli_fetch_assoc($result2);
					
					if($rel2['stateid']==$rel1['stateid']){
						$query3="select trn.*,tmst.tp_per from formula_mst as trn
								left join tbl_tax_per_master as tmst on tmst.tp_id=trn.tax_per_id
							where trn.tax_per_id=".$rel['product_purchase_gst']." and tax_cat='INTRA'";
						$result3=$dbcon->query($query3);
						$rel3=mysqli_fetch_assoc($result3);
					
					}else{
						$query3="select trn.*,tmst.tp_per from formula_mst as trn
								left join tbl_tax_per_master as tmst on tmst.tp_id=trn.tax_per_id
							where trn.tax_per_id=".$rel['product_purchase_gst']." and tax_cat='INTER'";
						$result3=$dbcon->query($query3);
						$rel3=mysqli_fetch_assoc($result3);
					}
					
					$taxamo=(($info1['product_amount']*$rel3['tp_per'])/100);
					$tamou=($info1['product_amount']+$taxamo); */
					
					//$info1['formulaid']			= $rel3['formulaid'];
					//$info1['sel_tax']			= $rel3['formula_name'];
					//$info1['formula_tax_id']	= $rel3['tax_id'];
					//$info1['total']				= $tamou;
					//$info1['product_amount_tax']= $taxamo;
					
					$info1['po_ref_id']			= $po_ref_id;
					$info1['purchasecardtrn_id']= $purchasecardtrn_id;
					
					$info1['user_id']			= $_SESSION['user_id'];
					$info1['purchaseordertrn_status']			= 3;
					$info1['company_id']        = $_SESSION['company_id'];
					$info1['branch_id']         = $POST['branch_id'];
					
					/* echo "<pre>";print_r($info1); echo "</pre>";exit; */
					$ins_id=add_record('tbl_purchaseordertrn', $info1, $dbcon);
					
					if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'CGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$ins_id,"tbl_purchaseordertrn",$eid,3,0,$POST['branch_id'],$info1['currency_id'],$info1['currency_rate'],$cgst_tax_rate_conv);
					}
					if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'SGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$ins_id,"tbl_purchaseordertrn",$eid,3,0,$POST['branch_id'],$info1['currency_id'],$info1['currency_rate'],$sgst_tax_rate_conv);
					}
					if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'IGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$ins_id,"tbl_purchaseordertrn",$eid,3,0,$POST['branch_id'],$info1['currency_id'],$info1['currency_rate'],$igst_tax_rate_conv);
					}
				
				// check for the addiotional tax on product Start -- Maulik
				
					$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$info1['product_amount'],$ins_id,$eid,0,$POST['branch_id'],'tbl_purchaseordertrn',$info1['currency_id'],$info1['currency_rate'],$info1['product_amount']);
					
					/* $formula_tax_id=explode(",",$rel3['tax_id']);
					
					foreach($formula_tax_id as $f)
					{
						$tax_value=get_tax_field_tax_id($dbcon,$f,'tax_value');
						$taxable_value=($tax_value*$info1['product_amount'])/100;
						
						$infot['tx_tax_id']=$f;
						$infot['tx_tax_value']=$tax_value;
						$infot['tx_taxable_value']=$taxable_value;
						$infot['tx_transaction_id']=$ins_id;
						$infot['tx_transaction_type']='purchase_order';
						$infot['tx_product_id']=$eid;
						$infot['tx_tran_type_id']=$tx_tran_type_id;
						$infot['user_id']	= $_SESSION['user_id'];
						$infot['cdate']= date("Y-m-d H:i:s");
						$infot['company_id']=$_SESSION['company_id'];
						$infot['branch_id']=$POST['branch_id'];
						
						$table1='tbl_tax_trn';$tableid1='tx_id';
						$inserid1=add_record($table1, $infot, $dbcon);
						
						//echo $taxable_value."<br>";
					} */
				}
			}
			
			$row['msg']="1";
				
			echo json_encode($row);
			
		}	
		else if(strtolower($POST['mode'])== "cancel_po_status")
		{
			$row=array();
			$info['po_type_status'] = $POST['po_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info,"purchaseorder_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "close_po_status")
		{
			$row=array();
			$info['po_req_status'] = $POST['po_req_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info,"purchaseorder_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_alt_qty")
		{
			
			$unit = $POST['unit'];	
			$product = $POST['product'];	
			
			$sel=$dbcon->query("select * from tbl_product_unit where unit_alt_unit='$unit' and unit_product='$product'");
			$count=mysqli_num_rows($sel);
			$row=mysqli_fetch_assoc($sel);
			
			$data['alt_qty']=$row['unit_alt_qty'];
			$data['base_qty']=$row['unit_basic_qty'];
			$data['count']=$count;
			
			echo json_encode($data);
		}
		else if(strtolower($POST['mode'])== "get_work_o_no"){
			$sp_id=$POST['sp_id'];			

			echo getworkorderpo($dbcon,$sp_id);
		}

		else if(strtolower($POST['mode'])== "show_remark"){
			$str='';
			$query = "select req.pre_trn_id,req.rp_req_type,req.indent_no,pre.remark from tbl_purchasetrntemp as temp
				left join tbl_request_product as req on req.rp_id=temp.po_ref_id 
				left join tbl_pre_trn as ptr on ptr.pre_trn_id=req.pre_trn_id
				left join tbl_pre as pre on pre.pre_id = ptr.pre_id
				where req.rp_req_type='direct' and temp.purchaseordertrn_id in (".$POST['purchaseordertrn_id'].")";

			$result = $dbcon->query($query);
			while($row = brp_mysqli_fetch_array($result)){
				$str .= '<strong>Indent No</strong> : '.$row['indent_no'].'<br> <strong> Indent Remark : </strong>'.$row['remark'].'<br>---------------------------------------------------------------<br>';
			}
			echo $str;
		}

		else if(strtolower($POST['mode'])== "get_product")
		{
			$vendor="";$workorder="";$sales_order_id="";
			if(!empty($POST['workorder_id'])){
				$query_w="select sp_id from tbl_request_product  as po 
						where rp_id=".$POST['workorder_id'];
					$result_w=$dbcon->query($query_w);
				$rel_trn_w=mysqli_fetch_assoc($result_w);
				if($rel_trn_w['sp_id']=="0"){
					$workorder=" and req.rp_id=".$POST['workorder_id'];
				}else{
					$workorder=" and sps.sp_id=".$rel_trn_w['sp_id'];
				}
				
				if(!empty($POST['vender_id'])){
					$vendor=" and inled.l_id=".$POST['vender_id'];
				}
				$yt="left";
			}
			if(!empty($POST['sp_id'])){
				if(!empty($POST['vender_id'])){
					$vendor=" and inled.l_id=".$POST['vender_id'];
				}
				$sales_order_id .=" and req.sp_id in (".$POST['sp_id'].")";
				$yt="left";
			}
			if($POST['product_cat']!=''){

				if(!empty($POST['vender_id'])){
					$vendor=" and inled.l_id=".$POST['vender_id'];
				}
				$sales_order_id .= " and product.product_category=".$POST['product_cat'];
				$yt="left";
			}else{
				if(!empty($POST['vender_id'])){
					$vendor="and inled.l_id=".$POST['vender_id'];
				}
				$yt="left";
			}
			$grou='';
			$today_date = date('Y-m-d');
			$companyConfiguration=getCompanyConfiguration($dbcon);
			$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
			$pro_search=explode(",", $purchase_pro_search);
			if($companyConfiguration['po_work_order_wise']==1){
				$grou=",req.sp_id,po.po_ref_id";
			}
			
			$query="select sum(po.product_qty) as pqty,po.product_rate,	prtr.rate, tc.cat_name, po.unit_id, product.product_name, product.product_icode, dr.drawing_number, product.product_alias_name,product.product_type,product.product_base_unit,product.product_conv_unit,po.purchaseordertrn_id,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as req_id,group_concat(po.po_ref_id order by po.po_ref_id) po_ref_id,po.product_id,sps.po_req_no,req.indent_no, req.product_remark 
			from tbl_purchasetrntemp  as po 
			left join product_mst as product on product.product_id=po.product_id 
			left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
			left join tbl_category as tc on product.product_category=tc.cat_id
			
			LEFT JOIN
            (
            SELECT cardtrn.product_id, cardtrn.vendor_id FROM tbl_purchasecardtrn as cardtrn
				left join tbl_product_party_purchase as pcard on pcard.party_purchase_id = cardtrn.party_purchase_id
				 where pcard.card_status=0 and cardtrn.purchasecardtrn_status=0 and cardtrn.valid_date>='".$today_date."' and cardtrn.affected_date<='".$today_date."' and pcard.is_aproove=1 and pcard.is_active=0 group by cardtrn.product_id,cardtrn.vendor_id
            ) B
            ON po.product_id = B.product_id
           	
           	left join tbl_request_product as req on req.rp_id=po.po_ref_id
			left join tbl_set_main_process as sps on sps.sp_id=req.sp_id
			left join tbl_pre_trn as prtr on prtr.pre_trn_id=req.pre_trn_id
			left join po_quotation as quo on quo.po_quotation_id=po.po_quotation_id
			left join tbl_ledger as inled on inled.l_id=prtr.vender_id or inled.l_id=B.vendor_id or inled.l_id = quo.vender_id

			where purchaseordertrn_status=0 ".$vendor." ".$workorder." and po.po_trn_req_status=0 ".$sales_order_id." group by po.product_id".$grou . " order by  prtr.pre_trn_id";

			$result=$dbcon->query($query);
			$count=mysqli_num_rows($result);
			if($count){
			echo '<div class="form-group">
					<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="10%" class="text-center">
									<input type="checkbox" id="all_chk_box" style="width: 23px;height: 23px;margin-top: 0px;" onclick="check_all();">
								</th>
								<th width="15%" class="text-center">Type</th>
								<th width="15%" class="text-center">Product Name</th>
								<th width="10%" class="text-center">Product Category</th>
								<th width="8%" class="text-center">Qty</th>
								<th width="8%" class="text-center">UOM</th>
								<th width="8%" class="text-center">Unit Of PO </th>
								<th width="8%" class="text-center">PO qty</th>
							</tr>';
							$i=1;
							while($rel_trn=mysqli_fetch_assoc($result))
							{
								if($companyConfiguration['po_work_order_wise']==1){
									$wno = "<strong style='color:green'><br>Work Order No : ".$rel_trn['po_req_no']."</strong>";
								}
								if(in_array('drawing',$pro_search)){
						            $drawing_number = " -- (".$rel_trn['drawing_number'].")";
						        }
						        if(in_array('item',$pro_search)){
						            $item_code = " -- (".$rel_trn['product_icode'].")";
						        }
						        if(in_array('alias',$pro_search)){
						            $alias = " -- (".$rel_trn['product_alias_name'].")";
						        }

						        $product_remark = "";

						        if(!empty($rel_trn['product_remark'])){
						        	$product_remark = '</br><span style="color:green"> Remark : </span>'. $rel_trn['product_remark'];
						        }

								$cat_name = ($rel_trn['cat_name']!=null) ? $rel_trn['cat_name'] : 'PRIMARY';
								$query_q="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn  as po 
								where purchaseordertrn_req_status=0 and po.req_id in (".$rel_trn['req_id'].")";

								$result1=$dbcon->query($query_q);
								$rel_u=mysqli_fetch_assoc($result1);
								$pending_qty=$rel_trn['pqty']-$rel_u['used_qty'];

								$pro_qty_val = '';
								if($companyConfiguration['direct_po_create']==0){
									$pro_qty_val = "max='".$pending_qty."'";
								}

								$ret_req_conv = '';
								if($rel_trn['product_base_unit'] != $rel_trn['product_conv_unit']){
									if($rel_trn['unit_id'] == $rel_trn['product_base_unit']){
										$type="conv_unit";
										$unit_name  = getunitname($dbcon,$rel_trn['product_conv_unit']);
										$ret_qty=convert_stock($dbcon,$rel_trn['pqty'],$rel_trn['product_id'],$type);
										$ret_req_conv='<span style="color:orange"> Conv Unit : '.number_format($ret_qty,4,'.','').' '.$unit_name.'</span>';
									}else{
										$type="base_unit";
										$unit_name  = getunitname($dbcon,$rel_trn['product_base_unit']);
										$ret_qty=convert_stock($dbcon,$rel_trn['pqty'],$rel_trn['product_id'],$type);
										$ret_req_conv='<span style="color:green"> Base Unit : '.number_format($ret_qty,4,'.','').' '.$unit_name.'</span>';
									}
								}

								//$ch="che_box".$i;
								echo '<tr>
									<td style="vertical-align:top;text-align:center;">
										<input type="checkbox" name="che_box[]" class="chk_box" id="che_box'.$i.'" value="'.$rel_trn['purchaseordertrn_id'].'" onclick="check_box('.$i.');" style="width: 23px;height: 23px;margin-top: 0px;">
										
										<input type="hidden" name="purchaseordertrn_id[]" id="purchaseordertrn_id'.$i.'" value="'.$rel_trn['purchaseordertrn_id'].'" />
										
										<input type="hidden" class="chk_box_st" name="check_status[]" id="check_status'.$i.'" value="1" />
										
										<input type="hidden" name="potemp_id[]" id="potemp_id'.$i.'" value="'.$rel_trn['req_id'].'" />

										<input type="hidden" name="man_rate[]" id="man_rate'.$i.'" value="'.$rel_trn['rate'].'" />
										
										<input type="hidden" name="po_ref_id[]" id="po_ref_id'.$i.'" value="'.$rel_trn['po_ref_id'].'" />
									</td>
									<td style="vertical-align:top;">
										'.get_pro_type_name($rel_trn['product_type']).'
									</td>
									<td style="vertical-align:top;">
										<b>'.$rel_trn['product_name'].' '.$drawing_number.' '.$item_code.' '.$alias.' '.$wno.$product_remark.'</b>
										
										<input type="hidden" name="product_id[]" id="product_id'.$i.'" value="'.$rel_trn['product_id'].'" />
									</td>
									<td style="vertical-align:top;">
										'.$cat_name.'
									</td>
									<td style="vertical-align:top;white-space:nowrap" class="text-center">
										<input type="text" class="form-control" name="product_qty[]" id="product_qty'.$i.'" value="'.number_format($rel_trn['pqty'],4,'.','').'"  readonly />
										'.$ret_req_conv.'
									</td>	
									<td style="vertical-align:top;" class="text-center">
										<select class="form-control" id="product_base_unit'.$i.'" name="product_base_unit[]" >
											'.getunit($dbcon,$rel_trn['unit_id']).'
										</select>
									</td>
									<td style="vertical-align:top;" class="text-center">
										<select class="form-control" id="product_uom'.$i.'" name="product_uom[]" onchange="get_alt_qty(this.value,'.$rel_trn['product_id'].','.$i.')" >
											'.getunit($dbcon,$rel_trn['unit_id']).'
										</select>
									</td>
									<td style="vertical-align:top;" class="text-center">
										<input type="hidden" class="form-control" name="unit_alt_qty[]" id="unit_alt_qty'.$i.'" value="" />
										
										<input type="hidden" class="form-control" name="unit_base_qty[]" id="unit_base_qty'.$i.'" value="" />
										
										<input type="text" class="form-control numbersOnly" name="pro_q[]" id="pro_q'.$i.'" value="'.number_format($pending_qty,4,'.','').'" '.$pro_qty_val.'onkeyup="copy_qty1(this.value,'.$i.')" />
										<input type="hidden" class="form-control " name="product_alloc_qty[]" id="product_alloc_qty'.$i.'"  value="'.$pending_qty.'"  />
									</td>
								</tr>';
								$i++;
							}
						echo '</table>
						</div>
					</div>';
			}else{
				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
							<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
								<tr id="field">
									<th class="text-center" style="font-size: 20px;background-color: #9a9a9a;color: #040404;">
										<strong>No Product Found....</strong>
									</th>
								</tr>
							</table>
						</div>
					</div>';
			}
			
		}
 

function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';		
	}
	$info['total']=$rate_total;
	return $info;
}
function change_po_trn_status($dbcon, $purchaseordertrn_id, $purchaseorder_id){
	$upd_po_trn['po_trn_req_status'] = 1;
	//$upd_po_trn['cdate'] = date("Y-m-d H:i:s");
	$updatepotrnid=update_record('tbl_purchaseordertrn', $upd_po_trn, "purchaseordertrn_id in(".$purchaseordertrn_id.") and purchaseorder_id=".$purchaseorder_id, $dbcon);
	
	//Update Main status if all used
	$sel_po_ord_qry="select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and po_trn_req_status=0 and purchaseorder_id=".$purchaseorder_id;
	$po_num_row=mysqli_num_rows($dbcon->query($sel_po_ord_qry));
	if(!$po_num_row){
		$upd_po['po_req_status']	= 1;
		$upd_po['cdate']			= date("Y-m-d H:i:s");
		$updateslsid = update_record('tbl_purchaseorder', $upd_po, "purchaseorder_id=".$purchaseorder_id, $dbcon);
	}
	else{
		$upd_po['po_req_status'] 	= 0;
		$upd_po['cdate'] 			= date("Y-m-d H:i:s");
		$updateslsid = update_record('tbl_purchaseorder', $upd_po, "purchaseorder_id=".$purchaseorder_id, $dbcon);
	}
}
function getworkorderpo($dbcon,$sp_id){
	$sel = '';
	$str = '';
	if(!empty($sp_id)){
		$query = "select req.rp_id,req.indent_no,po.po_req_no,so.sales_order_no from tbl_purchasetrntemp as gt
		left join tbl_request_product as req on req.rp_id = gt.po_ref_id
		left join tbl_set_main_process as po on po.sp_id = req.sp_id
		left join tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=req.sales_order_trn_id
		left join tbl_sales_order as so on so.sales_order_id=sotrn.sales_order_id
		where gt.purchaseordertrn_status=0 and gt.po_trn_req_status=0 and req.sp_id in(".$sp_id.") group by req.sp_id";
		$rs_type=$dbcon->query($query);
		$str .='<option value="" >--Choose Work Order / Indent--</option>';
		while($row=mysqli_fetch_assoc($rs_type)){
			if(!empty($row['sales_order_no'])){
				$so=" - ".$row['sales_order_no'];
			}
			$str .= '<option '.$sel.' value="'.$row['rp_id'].'">'.$row['po_req_no'].' '.$so.'</option>';
			
		}
	}else{
		$query = "select req.rp_id,req.indent_no,po.po_req_no,so.sales_order_no from tbl_purchasetrntemp as gt
		left join tbl_request_product as req on req.rp_id = gt.po_ref_id
		left join tbl_set_main_process as po on po.sp_id = req.sp_id
		left join tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=req.sales_order_trn_id
		left join tbl_sales_order as so on so.sales_order_id=sotrn.sales_order_id
		where gt.purchaseordertrn_status=0 and gt.po_trn_req_status=0 and req.sp_id!=0 group by req.sp_id";
		$rs_type=$dbcon->query($query);
		$str .='<option value="" >--Choose Work Order / Indent--</option>';
		while($row=mysqli_fetch_assoc($rs_type)){
			if(!empty($row['sales_order_no'])){
				$so=" - ".$row['sales_order_no'];
			}
			$str .= '<option '.$sel.' value="'.$row['rp_id'].'">'.$row['po_req_no'].' '.$so.'</option>';
			
		}
	}
	return $str;
}

?>