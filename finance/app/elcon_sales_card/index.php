<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		ELCON_SALES_CARD_VIEW,ELCON_SALES_CARD_UPDATE,ELCON_SALES_CARD_DELETE,ELCON_SALES_CARD_APPROVE,ELCON_SALES_CARD_ACTIVE
	]);

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	$where='';

	// $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	// $where_db = check_branch('card', $branch_id);
	// $where.=" $where_db ";

	$where_company=check_company('card');

	$where.=" card.sales_card_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND card.sales_card_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	$where.=" $where_company";

	$appData = array();
	$i=1;
	$aColumns = array('card.elcon_sales_id','card.sales_card_no','card.sales_card_date','card.is_approve','card.is_active','card.card_type');
	$sIndexColumn = "card.elcon_sales_id";
	$isWhere = array('card_status=0',$where);
	$sTable = "tbl_product_sales_elcon as card";
	$isJOIN = array('left join branch_mst as bms on bms.branch_id=card.branch_id');
	$hOrder = "card.elcon_sales_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		$row_data[] = $row['sales_card_no'];
		$row_data[] = date("d-M-Y", strtotime($row['sales_card_date']));

		$edit = '';$delete = '';$is_approove='';$is_active='';$lab='';
		if($row['is_active'] == 1){
			$lab = "<div class='external-event label label-error ui-draggable' style='cursor:auto; background-color: #d9534f !important;'>in active</div>";
		}else{
			$lab = "<div class='external-event label label-success ui-draggable' style='cursor:auto;'>active</div>";	
		}				

		$row_data[] = $lab;
		
		if($row['is_approve']==0){
			if(in_array(ELCON_SALES_CARD_UPDATE,$bulkAccessArray)){
				$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'elconsocardedit/'.$row['elcon_sales_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(ELCON_SALES_CARD_DELETE,$bulkAccessArray)){
				$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_so_card('.$row['elcon_sales_id'].');" ><i class="fa fa-trash-o"></i></button>';	
			}
		}
		if(in_array(ELCON_SALES_CARD_APPROVE,$bulkAccessArray)){
			$is_approove = '<button class="btn btn-xs btn-success" data-original-title="Approve SO Card" data-toggle="tooltip" data-placement="top" onclick="card_aprooval_status('."'$row[elcon_sales_id]'".','."'$row[card_type]'".','."'$row[sales_card_no]'".')"><i class="fa fa-check"></i></button>';
		}

		if(in_array(ELCON_SALES_CARD_ACTIVE,$bulkAccessArray)){
			$is_active = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="active_status_change('."'$row[elcon_sales_id]'".','."'$row[is_active]'".');" ><i class="fa fa-times"></i></button>';
		}

		$row_data[] = $edit." ".$delete." ".$is_approove." ".$is_active;
		$row_data[] = "";
				//print_r($row_data);
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "fetch_item") {

	$where='';
	if($POST['card_id'] != ""){
		$where.=" trncard.salescardelcontrn_status=0 and trncard.elcon_sales_id=".$POST['card_id'];
	}else{
		$where .=" trncard.salescardelcontrn_status=3";
	}

	$appData = array();
	$i=1;
	$aColumns = array('cat.cat_name','trncard.price','unit.unit_name','trncard.rate3','trncard.rate2','trncard.rate1','trncard.valid_date','trncard.effected_date','trncard.sales_type','trncard.salescardelcontrn_id','cur.currency_name');
	$sIndexColumn = "trncard.salescardelcontrn_id";
	$isWhere = array($where);
	$sTable = "tbl_salescardelcontrn as trncard";
	$isJOIN = array('left join tbl_category as cat on cat.cat_id=trncard.product_cat_id','left join currency_mst as cur on cur.currencyid=trncard.currency_id','left join unit_mst as unit on unit.unitid=trncard.unit_id');
	$hOrder = "trncard.salescardelcontrn_id desc";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['cat_name'];
		$row_data[] = $row['price'];
		$row_data[] = $row['unit_name'];
		$row_data[] = $row['rate1'];
		$row_data[] = $row['rate2'];
		$row_data[] = $row['rate3'];
		$row_data[] = date("d-M-Y",strtotime($row['valid_date']));
		$row_data[] = date("d-M-Y",strtotime($row['effected_date']));

		$edit = '';$delete = '';

		$edit = '<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$row['salescardelcontrn_id'].');" ><i class="fa fa-pencil"></i></button>';

		$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$row['salescardelcontrn_id'].');" ><i class="fa fa-times"></i></button>';

		$row_data[] = $edit." ".$delete;
		$row_data[] = "";
				//print_r($row_data);
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);

	$info['card_type']				= $POST['card_type'];
	$info['sales_card_no']			= $POST['sales_card_no'];
	$info['sales_card_date']		= date('Y-m-d',strtotime($POST['sales_card_date']));
	$info['cdate']					= date("Y-m-d H:i:s");
	$info['user_id']				= $_SESSION['user_id'];
	$info['company_id']				= $_SESSION['company_id'];

	$inserpoid=add_record('tbl_product_sales_elcon', $info, $dbcon, $branch_id);

	if($inserpoid){
		$inftrn['elcon_sales_id'] = $inserpoid;
		$inftrn['salescardelcontrn_status'] = 0;
		$updatetrnid=update_record('tbl_salescardelcontrn', $inftrn,"user_id=".$_SESSION['user_id']." and elcon_sales_id=0 and salescardelcontrn_status=3" , $dbcon);
	}
	if($inserpoid)
	{	
		$arr['msg']="1";							
	}
	else{
		$arr['msg']="0";
	}
	echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "fieldadd") {
	$info1['product_cat_id'] 		= $POST['product_cat_id'];

	// $query = "select * from tbl_salescardelcontrn where valid_date>='$valid_date' and salescardelcontrn_status=0 and product_id=".$info1['product_id']." and company_id=".$_SESSION['company_id']." and unit_id=".$POST['unit_id'];

	// $q = $dbcon -> query($query);
	// $cnt = brp_mysqli_num_rows($q);
	// if($cnt>0 && $POST['edit_id'] == ""){
	// 	$arr['msg']="-1";
	// }else{
	$info1['currency_id']	= $POST['currency_id'];
	$info1['effected_date']	= date('Y-m-d',strtotime($POST['effected_date']));
	$info1['valid_date']	= date('Y-m-d',strtotime($POST['valid_date']));
	$info1['price']			= $POST['rate'];
	$info1['rate1']			= $POST['rate1'];
	$info1['rate2']			= $POST['rate2'];
	$info1['rate3']			= $POST['rate3'];
	$info1['unit_id']		= $POST['unit_id'];
	$info1['user_id']		= $_SESSION['user_id'];
	$info1['company_id']	= $_SESSION['company_id'];
	$info1['cdate']			= date("Y-m-d H:i:s");

	$table='tbl_salescardelcontrn';$tableid='salescardelcontrn_id';
	if(!empty($POST['card_id']))
	{
		$info1['elcon_sales_id']= $POST['card_id'];
	}else{
		$info1['salescardelcontrn_status']	= 3;
	}

	if(empty($POST['edit_id']))
	{
		$inserid=add_record($table, $info1, $dbcon);
	}
	else
	{
		$inserid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
	}


	if($inserid){
		$arr['msg']="1";
	}else{
		$arr['msg']="0";
	}
	// }
	echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "preedit"){
// var_dump($POST);die;
	$q = $dbcon -> query("select * from tbl_salescardelcontrn where salescardelcontrn_id= '$POST[id]'");
	$r = $q->fetch_assoc();

	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_data")
{
	$info['salescardelcontrn_status'] = 2;
	$updateid=update_record('tbl_salescardelcontrn', $info,'salescardelcontrn_id'."=".$POST['eid'] , $dbcon);

	if($updateid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "get_series_no")
{
	$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			//var_dump($query);
	$result=$dbcon->query($query);
	$row=mysqli_fetch_assoc($result);
	echo $row['invoicetype_id'];
}

else if(strtolower($POST['mode'])== "load_invoiceno")
{
	$row=array();
	$query1="select * from  tbl_invoicetype where invoicetype_id=".$_POST['typeid'];
	$rows=mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
	if($rows['invoice_format']=='2')
	{
		$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1')
	{
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
	}
	$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "edit") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$info['card_type']				= $POST['card_type'];
	$info['sales_card_no']			= $POST['sales_card_no'];
	$info['sales_card_date']		= date('Y-m-d',strtotime($POST['sales_card_date']));
	$info['cdate']					= date("Y-m-d H:i:s");
	$info['user_id']				= $_SESSION['user_id'];
	$info['company_id']				= $_SESSION['company_id'];

	$updateid1=update_record('tbl_product_sales_elcon', $info,"elcon_sales_id=".$POST['eid'] , $dbcon,$branch_id);

	if($updateid1)
	{	
		$arr['msg']="update";
	}
	else{
		$arr['msg']=0;
	}
	echo json_encode($arr);	
}
else if(strtolower($POST['mode']) == "delete") {
	$info['card_status']		= 2;
	$info1['salescardelcontrn_status']= 2;

	$updateinvoiceid=update_record('tbl_product_sales_elcon', $info,"elcon_sales_id=".$POST['eid'] , $dbcon);	
	$updatetrancationid=update_record('tbl_salescardelcontrn', $info1,"elcon_sales_id=".$POST['eid'] , $dbcon);	

	if($updatetrancationid)
		echo "1";	
	else
		echo "0";			
}
else if(strtolower($POST['mode']) == "load_socard_vender_detail") {
	$query = "select * from tbl_product_sales_elcon as card where card.elcon_sales_id=".$POST['card_id'];

	$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($query));

		//Party PO Details Table View
	$str='';
	$str.='<div class="form-group">
	<table class="display table table-bordered table-striped">
	<tr>
	<td><strong>Sales Card No:</strong> '.$qt_rel['sales_card_no'].'</td>
	<td><strong>Sales Card Date:</strong> '.date("d-M-Y",strtotime($qt_rel["sales_card_date"])).'</td>
	</tr>
	';
	$str.='</table></div>
	<hr/>
	';

	$qt_rel['card_detail_show'] = $str;

	echo json_encode($qt_rel);		
}
else if(strtolower($POST['mode']) == "load_socard_hist") {

	$where='';
	$where.="   log.elcon_sales_id=".$POST['card_id'];

	$appData = array();
	$i=1;
	$aColumns = array('log.socard_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
	$sIndexColumn = "log.socard_aprv_id";
	$isWhere = array(" ".$where." ");
	$sTable = "tbl_elconsocard_aprv_log as log";			
	$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
	$hOrder = "log.socard_aprv_id desc";
	include($include.'/pagging.php');
			//echo $sQuery;
			//exit;
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['user_name'];

		if($row['approve_status']=='1'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
		}
		else{
			$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Disapproved</div>';
		}

		$row_data[] = nl2br($row['approve_remark']);
		$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add_socard_apprv_hist") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info1['approve_remark']	= $_POST['approve_remark'];
	$info1['approve_status']	= $POST['approve_status'];
	$info1['elcon_sales_id']	= $POST['elcon_sales_id'];
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];
	$info1['cdate']				= date('Y-m-d H:i:s');

	$inserid=add_record("tbl_elconsocard_aprv_log", $info1, $dbcon, $branch_id);

	$info['is_approve'] = $POST['approve_status'];	

	$updateid=update_record("tbl_product_sales_elcon", $info, "elcon_sales_id=".$POST['elcon_sales_id'], $dbcon);
	if($POST['approve_status']=='1'){
		$rts = $dbcon->query("SELECT * FROM tbl_product_sales_elcon WHERE elcon_sales_id = ".$POST['elcon_sales_id']);
		$getrt = brp_mysqli_fetch_assoc($rts);

		$chkcard = $dbcon->query("SELECT * FROM tbl_product_party_sales WHERE sales_card_no = ".$getrt['sales_card_no']);
		if(brp_mysqli_num_rows($chkcard) > 0){
			$getcard = brp_mysqli_fetch_assoc($chkcard);
			$insertsoid = $getcard['party_sales_id'];
			$dbcon->query("UPDATE tbl_product_party_sales SET card_status = 0, is_aproove = 1, is_active = 0 WHERE sales_card_no = ".$getrt['sales_card_no']);
			$infosop['approve_remark']	= $_POST['approve_remark'];
			$infosop['approve_status']	= $POST['approve_status'];
			$infosop['party_sales_id']	= $insertsoid;
			$infosop['user_id']			= $_SESSION['user_id'];
			$infosop['company_id']		= $_SESSION['company_id'];
			$infosop['cdate']				= date('Y-m-d H:i:s');

			$inserid=add_record("tbl_socard_aprv_log", $infosop, $dbcon, $branch_id);
		}else{
			$infoso['card_type'] = 1;
			$infoso['sales_card_no'] = $getrt['sales_card_no'];
			$infoso['sales_card_date'] = $getrt['sales_card_date'];
			$infoso['card_status'] = 0;
			$infoso['is_aproove'] = 1;
			$infoso['is_active'] = 0;
			$infoso['user_id']			= $_SESSION['user_id'];
			$infoso['company_id']		= $_SESSION['company_id'];
			$infoso['cdate']			= date('Y-m-d H:i:s');

			$insertsoid=add_record("tbl_product_party_sales", $infoso, $dbcon, $branch_id);

			$infosop['approve_remark']	= $_POST['approve_remark'];
			$infosop['approve_status']	= $POST['approve_status'];
			$infosop['party_sales_id']	= $insertsoid;
			$infosop['user_id']			= $_SESSION['user_id'];
			$infosop['company_id']		= $_SESSION['company_id'];
			$infosop['cdate']				= date('Y-m-d H:i:s');

			$inserid=add_record("tbl_socard_aprv_log", $infosop, $dbcon, $branch_id);
		}

		$qry = $dbcon->query("SELECT * FROM tbl_salescardelcontrn WHERE salescardelcontrn_status =0 AND elcon_sales_id = ".$POST['elcon_sales_id']);
		while($rel = mysqli_fetch_assoc($qry)){
			$qrys = $dbcon->query("SELECT * FROM product_mst WHERE product_category = ".$rel['product_cat_id']." AND product_status = 0 AND company_id = ".$_SESSION['company_id']." AND(product_base_unit = ".$rel['unit_id']." OR product_conv_unit = ".$rel['unit_id'].")");
			$price = $amt = 0;
			while($row = mysqli_fetch_assoc($qrys)){
				if($row['product_base_unit'] == $rel['unit_id']){
					$price = $rel['price']*$row['base_weight'];
				}else if($row['product_conv_unit'] == $rel['unit_id']){
					$price = $rel['price']*$row['conv_weight'];
				}
				$amt = $price + $rel['rate1'] + $rel['rate2'] + $rel['rate3'];

				$chksales = $dbcon->query("SELECT * FROM tbl_salescardtrn WHERE salescardtrn_status = 0");
				$getsales = brp_mysqli_fetch_assoc($chksales);
				if($getsales['product_id']!=$row['product_id']){
					$dbcon->query("INSERT INTO `tbl_salescardtrn` (`sales_type`,`product_id`,`currency_id`,`price`,`affected_date`,`unit_id`,`salescardtrn_status`,`user_id`,`company_id`,`cdate`,`salescardelcontrn_id`,`party_sales_id`,`valid_date`) VALUES ('1','".$row['product_id']."','".$rel['currency_id']."','".$amt."','".date("Y-m-d")."','".$rel['unit_id']."','0','".$_SESSION['user_id']."','".$_SESSION['company_id']."','".$rel['effected_date']."','".$rel['salescardelcontrn_id']."','".$insertsoid."','".$rel['valid_date']."')");

				}else{
					$ydate = '';
					$ydate = date('Y-m-d',strtotime("-1 days"));
					$dbcon->query("UPDATE `tbl_salescardtrn` SET valid_date = '".$ydate."' WHERE product_id = ".$row['product_id']);

					$dbcon->query("INSERT INTO `tbl_salescardtrn` (`sales_type`,`product_id`,`currency_id`,`price`,`affected_date`,`unit_id`,`salescardtrn_status`,`user_id`,`company_id`,`cdate`,`salescardelcontrn_id`,`party_sales_id`,`valid_date`) VALUES ('1','".$row['product_id']."','".$rel['currency_id']."','".$amt."','".date("Y-m-d")."','".$rel['unit_id']."','0','".$_SESSION['user_id']."','".$_SESSION['company_id']."','".$rel['effected_date']."','".$rel['salescardelcontrn_id']."','".$insertsoid."','".$rel['valid_date']."')");
				}
			}
		}
	}
}
else if(strtolower($POST['mode']) == "load_pocard_pro_detail") {
	$query = "select card.sales_card_no, card.sales_card_date, dr.drawing_number, pro.product_name,pro.product_icode, hsn.hsn_code, base.unit_name as base_unit, conv.unit_name as conv_unit, ty.product_type_name  from tbl_product_sales_elcon as card
	left join product_mst as pro on pro.product_id = card.product_id
	left join pro_ms_product_type as ty on ty.product_type_id = pro.product_type  
	left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id 
	left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
	left join unit_mst as base on base.unitid = pro.product_base_unit
	left join unit_mst as conv on conv.unitid = pro.product_conv_unit

	where card.elcon_sales_id=".$POST['card_id'];


	$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($query));

		//Party PO Details Table View
	$str='';
	$str.='<div class="form-group">
	<table class="display table table-bordered table-striped">
	<tr>
	<td colspan="2"><strong>Product Name:</strong> '.$qt_rel['product_name'].'</td>
	<td><strong>Product Code:</strong> '.$qt_rel['product_icode'].'</td>
	</tr>
	<tr>
	<td colspan="2"><strong>Product type :</strong> '.$qt_rel['product_type_name'].'</td>
	<td><strong>HSN Code:</strong> '.$qt_rel['hsn_code'].'</td>
	</tr>
	<!--<tr>
	<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
	<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
	<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
	</tr>-->
	<tr>
	<td><strong>Base Unit :</strong> '.$qt_rel['base_unit'].'</td>
	<td><strong>Conv. Unit:</strong> '.$qt_rel['conv_unit'].'</td>
	<td><strong>Drawing No:</strong> '.$qt_rel['drawing_number'].'</td>
	</tr>
	<tr>
	<td><strong>Sales Card No:</strong> '.$qt_rel['sales_card_no'].'</td>
	<td><strong>Sales Card Date:</strong> '.date("d-M-Y",strtotime($qt_rel["sales_card_date"])).'</td>
	</tr>
	';
	$str.='</table></div>
	<hr/>
	';

	$qt_rel['card_detail_show'] = $str;

	echo json_encode($qt_rel);		
}
else if(strtolower($POST['mode'])== "active_status")
{
	if($POST['active_status']==0){
		$info['is_active'] = 1;
	}else{
		$info['is_active'] = 0;
	}

	$updateid=update_record('tbl_product_sales_elcon', $info,'elcon_sales_id'."=".$POST['eid'] , $dbcon);

	if($updateid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "load_product_unit")
{
	$query = "select * from tbl_salescardelcontrn where salescardelcontrn_id=".$POST['edit_id'];
	$rs_type=$dbcon->query($query);
	$row=brp_mysqli_fetch_assoc($rs_type);
	$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
	left join unit_mst as umst on umst.unitid=promst.product_base_unit
	left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
	WHERE product_id=".$POST['product_id'];

	$rs_type1=$dbcon->query($query1);
	$row1=brp_mysqli_fetch_assoc($rs_type1);

	if($row1['product_base_unit']!=$row1['product_conv_unit']){
		$row1['unit_status']="1";
		$base='';$conv='';

		if($row1['product_base_unit']==$row['unit_id']){
			$base="selected='selected'";
		}else{
			$conv="selected='selected'";
		}
		$opt='<option '.$base.' value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
		$opt .='<option '.$conv.' value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
	}else{
		$row1['unit_status']="0";

		$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
	}

	$row1['unit_option']=$opt;
	echo json_encode($row1);
}
?>
