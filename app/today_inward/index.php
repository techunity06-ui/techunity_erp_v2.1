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
		//$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				//PO_QUOTATION_VIEW, PO_QUOTATION_ADD, PO_QUOTATION_READ, PO_QUOTATION_UPDATE, PO_QUOTATION_DELETE, PO_QUOTATION_APPROVE, PO_QUOTATION_FINAL_APPROVE
		//]);

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		//$branch=$_SESSION['branch_id'];
		    
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('pod', $branch_id);
			$where.=" $where_db and pod.company_id=".$_SESSION['company_id'];
			$today_date = date('Y-m-d');
			
			$appData = array();
			$i=1;
			$aColumns = array('pod.po_delivery_date_id','pod.delivery_date','pod.product_qty','po.purchaseorder_no','po.purchaseorder_date','bms.branch_name','pmst.product_name', 'tc.cat_name', 'unit.unit_name','po.purchaseorder_id', 'trn.purchaseordertrn_id','led.l_name','led.cust_mobile','(pod.product_qty-pod.used_qty) as pending_qty');
			$sIndexColumn = "pod.po_delivery_date_id";
			$isWhere = array("pod.po_delivery_date_status=0 and po.po_approval_status = 1 and delivery_date='$today_date' and pod.grn_status!=1".$where);
			$sTable = "tbl_purchaseorder_delivery_date as pod";			
			$isJOIN = array('left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id','left join tbl_ledger as led on led.l_id=po.vender_id','left join branch_mst as bms on bms.branch_id=pod.branch_id','left join product_mst as pmst on pmst.product_id=trn.product_id','left join tbl_category as tc on pmst.product_category=tc.cat_id','left join unit_mst as unit on unit.unitid=trn.unit_id');
			$hOrder = "pod.delivery_date desc";
			$hGroupby = array("pod.po_delivery_date_id");
			include('../../include/pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;
				$row_data[] = $row['purchaseorder_no'];
				$row_data[] = date('d M, Y',strtotime($row['purchaseorder_date']));
				$row_data[] = $row['l_name']."<br><strong>(".$row['cust_mobile'].")</strong>";
				$row_data[] = $row['product_name'];
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['product_qty'];
				$row_data[] = $row['pending_qty'];
				$row_data[] = $row['unit_name'];
				$row_data[] = date('d M, Y',strtotime($row['delivery_date']));
				
				$folloup='<button class="btn btn-xs btn-warning" data-original-title="Folloup" data-toggle="tooltip" data-placement="top" onClick="open_followup('.$row['purchaseorder_id'].' , '."'$row[purchaseorder_no]'".', '.$row['po_delivery_date_id'].')"><i class="fa fa-list-alt"></i></button>';
				
				$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.'grn_add_po/'.$row['purchaseorder_id'].'"><i class="fa fa-plus"></i></a>';
				
				$row_data[] = $folloup." ".$add_po_btn;
				
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "followup_add") {
			$info1['remark']			= $_POST['followup_remark'];
			$info1['purchaseorder_id']	= $POST['purchase_order_id'];
			$info1['po_delivery_date_id'] = $POST['delever_id'];	
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['cdate']				= date('Y-m-d H:i:s');

			$inserid=add_record("tbl_purchaseorder_followup", $info1, $dbcon);
		}
		else if(strtolower($POST['mode']) == "po_folloup_fetch") {
			$where='';
			$where.="   foll.po_delivery_date_id=".$POST['delever_id'];
			
			$appData = array();
			$i=1;
			$aColumns = array('foll.po_folloup_id', 'foll.remark', 'foll.cdate');
			$sIndexColumn = "foll.po_folloup_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_purchaseorder_followup as foll";			
			$isJOIN = array('');
			$hOrder = "foll.po_folloup_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['remark'];
				$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_party_purchase_dtl") {
			$qt_qry="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no,led.cust_email from tbl_purchaseorder as qt
			left join tbl_ledger as led on led.l_id=qt.vender_id
			left join country_mst as country on country.countryid=led.countryid
			left join state_mst as state on state.stateid=led.stateid
			left join city_mst as city on city.cityid=led.cityid
			where qt.purchaseorder_id=".$POST['purchase_order_id'];
			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));
			
			$pro_dt = "select del.product_qty,del.delivery_date,pro.product_name,trn.currency_total,trn.product_qty as trnqty, trn.product_rate from tbl_purchaseorder_delivery_date as del
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=del.purchaseordertrn_id
			left join product_mst as pro on pro.product_id = trn.product_id
			where po_delivery_date_id =".$POST['delever_id'];
		
			$pro_dtl=brp_mysqli_fetch_assoc($dbcon->query($pro_dt));
		
			$pro_amt = $pro_dtl['product_rate'] * $pro_dtl['product_qty'];
			
			//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
					<table class="display table table-bordered table-striped">
						<tr>
							<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['company_name'].'</td>
							<td><strong>Contact No.:</strong> '.$qt_rel['cust_mobile'].'</td>
						</tr>
						<tr>
							<td colspan="2"><strong>Address:</strong> '.$qt_rel['m_address'].'</td>
							<td><strong>Mail Id.:</strong> '.$qt_rel['cust_email'].'</td>
						</tr>
						<!--<tr>
							<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
							<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
							<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
						</tr>-->
						<tr>
							<td style="width:50%"><strong>City:</strong> '.$qt_rel['city_name'].'</td>
							<td style="width:25%"><strong>State:</strong> '.$qt_rel['state_name'].'</td>
							<td style="width:25%"><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
						</tr>
						<tr>
							<td><strong>Product Name :</strong> '.$pro_dtl['product_name'].'</td>
							<td colspan="2"><strong>Delivery Date :</strong> '.date("d-M-Y",strtotime($pro_dtl["delivery_date"])).'</td>
						</tr>
					
						<tr>
							<td><strong>Product Qty:</strong> '.$pro_dtl['product_qty'].'</td>
							<td><strong>Product Rate:</strong> '.$pro_dtl['product_rate'].'</td>
							<td><strong>Product Amount :</strong> <strong>'.$pro_amt.'</strong></td>
						</tr>
					';
			$str.='</table></div>
			<hr/>
			';
			
			$qt_rel['mod_po_comp_div_sec'] = $str;
			
			echo json_encode($qt_rel);
		}
?>