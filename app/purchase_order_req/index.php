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

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);
			$where.=" $where_db and po.company_id=".$_SESSION['company_id'];

			$appData = array();
			$i=1;
			$aColumns = array('po.purchaseordertrn_id','po.mdate','tc.cat_name','pr.product_name','bms.branch_name','po.total','po.purchaseordertrn_status','led.l_name','po.cdate','po.user_id','po.po_ref_type','sum(po.product_qty) as pqty','po.po_ref_id','po.product_id','po.po_trn_req_status','GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id', 'po.branch_id');
			$sIndexColumn = "po.purchaseordertrn_id";
			$isWhere = array("po.purchaseordertrn_status = 0 and po_trn_req_status=".$POST['po_type_status'].$where);
			$sTable = "tbl_purchasetrntemp as po";			
			$isJOIN = array('left join product_mst as pr on pr.product_id=po.product_id','left join tbl_category as tc on pr.product_category=tc.cat_id','left join po_quotation as poq on poq.po_quotation_id=po.po_quotation_id','left join branch_mst as bms on bms.branch_id=po.branch_id','left join tbl_product_party_purchase as ppp on pr.product_id=ppp.party_product','left join tbl_ledger as led on led.l_id=ppp.party_id');
			$hOrder = "po.purchaseordertrn_id desc";
			$hGroupby = array("po.po_quotation_id,po.product_id,po.po_trn_req_status");
			include('../../include/pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				
				$row_data[] = $id;
				$row_data[] = date('d M, Y',strtotime($row['mdate']));
				$row_data[] = $row['product_name'];
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $row['l_name'];
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['pqty'];
				//$row_data[] = $row['pen_qty'];
				//$row_data[] = $row['reqested_qty'];
			
				//$query="select sum(product_qty) as used_qty from tbl_purchaseordertrn where purchaseordertrn_status=0 and temptrn_ref_id=".$row['purchaseordertrn_id'];
			if($POST['po_type_status']==0){	
				$query="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id in (".$row['purchastrn_id'].")";
				
				$rel=mysqli_fetch_assoc($dbcon->query($query));	
				$pending_qty=$row['pqty']-$rel['used_qty'];
				
				$row_data[] = $pending_qty;
				$row_data[] = $rel['used_qty'];
				if($pending_qty>0){
					if(in_array(PO_REQ_ADD,$bulkAccessArray)){
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'po_req_add/'.$row['product_id'].'/'.$row['po_ref_type'].'/'.$row['branch_id'].'"><i class="fa fa-plus"></i></a>';
					}
				}else{
					$add_po_btn='';
				}
			}else{
				$row_data[] = "";
				$row_data[] = $row['pqty'];
				$add_po_btn='';
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
					
				$row_data[] = $add_po_btn.' '.$poprint;
			 
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
					$pr_rate = get_product_rate_at_purchase_time($dbcon, $POST['vender_id'], $eid); // $eid is product_id
					
					$que="select * from product_mst as ta where product_id=".$eid;
					$rs_di=$dbcon->query($que);
					$re=brp_mysqli_fetch_assoc($rs_di);
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
					
					$info1['temptrn_ref_id']	= $potemp_id;
					$info1['product_id']		= $eid;
					$info1['product_qty']		= $base_stock;
					$info1['product_conv_qty']	= $con_stock;
					$info1['unit_id']			= $re['product_base_unit'];
					$info1['conv_unit_id']		= $re['product_conv_unit'];
					$info1['product_rate']		= $pr_rate;
					$info1['product_amount']	= $pr_rate*$POST['product_alloc_qty'][$loop_id];
					
					$query="select product_purchase_gst from product_mst as trn
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
					$tamou=($info1['product_amount']+$taxamo);
					
					$info1['formulaid']			= $rel3['formulaid'];
					$info1['sel_tax']			= $rel3['formula_name'];
					$info1['formula_tax_id']	= $rel3['tax_id'];
					$info1['total']				= $tamou;
					$info1['product_amount_tax']= $taxamo;
					$info1['po_ref_id']			= $po_ref_id;
					
					$info1['user_id']			= $_SESSION['user_id'];
					$info1['purchaseordertrn_status']			= 3;
					$info1['company_id']        = $_SESSION['company_id'];
					$info1['branch_id']         = $POST['branch_id'];
					
					$ins_id=add_record('tbl_purchaseordertrn', $info1, $dbcon);
					
					$formula_tax_id=explode(",",$rel3['tax_id']);
					
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
					}
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
		else if(strtolower($POST['mode'])== "get_product")
		{
			$query="select sum(po.product_qty) as pqty,tc.cat_name,po.unit_id,product.product_name,product.product_type,product.product_base_unit,product.product_conv_unit,purchaseordertrn_id,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as req_id,group_concat(po.po_ref_id order by po.po_ref_id) po_ref_id,po.product_id 
			from tbl_purchasetrntemp  as po 
			left join product_mst as product on product.product_id=po.product_id 
			left join tbl_category as tc on product.product_category=tc.cat_id
			inner join tbl_product_party_purchase as pvr on pvr.party_product=po.product_id
			where purchaseordertrn_status=0 and pvr.party_id=".$POST['vender_id']." and po.po_trn_req_status=0 group by po.product_id";
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
								$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
								$query_q="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn  as po 
								where purchaseordertrn_req_status=0 and po.req_id in (".$rel_trn['req_id'].")";

								$result1=$dbcon->query($query_q);
								$rel_u=mysqli_fetch_assoc($result1);
								$pending_qty=$rel_trn['pqty']-$rel_u['used_qty'];
								//$ch="che_box".$i;
								echo '<tr>
									<td style="vertical-align:top;text-align:center;">
										<input type="checkbox" name="che_box[]" class="chk_box" id="che_box'.$i.'" value="'.$rel_trn['purchaseordertrn_id'].'" onclick="check_box('.$i.');" style="width: 23px;height: 23px;margin-top: 0px;">
										
										<input type="hidden" name="purchaseordertrn_id[]" id="purchaseordertrn_id'.$i.'" value="'.$rel_trn['purchaseordertrn_id'].'" />
										
										<input type="hidden" class="chk_box_st" name="check_status[]" id="check_status'.$i.'" value="1" />
										
										<input type="hidden" name="potemp_id[]" id="potemp_id'.$i.'" value="'.$rel_trn['req_id'].'" />
										
										<input type="hidden" name="po_ref_id[]" id="po_ref_id'.$i.'" value="'.$rel_trn['po_ref_id'].'" />
									</td>
									<td style="vertical-align:top;">
										'.get_pro_type_name($rel_trn['product_type']).'
									</td>
									<td style="vertical-align:top;">
										<b>'.$rel_trn['product_name'].'</b>
										
										<input type="hidden" name="product_id[]" id="product_id'.$i.'" value="'.$rel_trn['product_id'].'" />
									</td>
									<td style="vertical-align:top;">
										'.$cat_name.'
									</td>
									<td style="vertical-align:top;" class="text-center">
										<input type="text" class="form-control" name="product_qty[]" id="product_qty'.$i.'" value="'.$rel_trn['pqty'].'"  readonly />
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
									
										<input type="text" class="form-control" name="product_alloc_qty[]" id="product_alloc_qty'.$i.'" value="'.$pending_qty.'"  />
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

?>