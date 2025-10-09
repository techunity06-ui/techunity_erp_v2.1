<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "generate_report_product_service"){
	$where = "";
	$s_date=explode(' - ',$POST['date']);
	if(strtolower($POST['extra']) == "datatable_filter" && $POST['source_id'] != ""){
		$sour=implode(",",$POST['source_id']);
		$where .= " and e.rb_id in (".$sour.")";
	}else if($POST['source_id'] != ""){
		$sour=$POST['source_id'];
		$where .= " and e.rb_id in (".$sour.")";
	}
	$where .= " and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	$appData = array();
	$i=1;
	$aColumns = array('e.cdate','e.inquiry_name as opportunity_name','mc.mcd_name as sales_stage','cust.cust_name','us.user_name as lead_owner','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id','e.opp_id','comp.company_name','rf.rb_name');
	$sIndexColumn = "e.inquiry_id";
	$isWhere = array("e.inquiry_status=0 AND e.company_id IN (0,$_SESSION[company_id])".$where);
	$sTable = " tbl_inquiry as e";			
	$isJOIN = array("left join tbl_customer as cust on cust.cust_id=e.cust_id",
		"left join users as us on us.user_id=e.user_id",
		"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
		"left join tbl_company as comp on comp.company_id=e.company_id",
		"left join tbl_refer_by as rf on rf.rb_id=e.rb_id",
		"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id");
	$hOrder = "e.inquiry_id";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	$view_hist_btn = "";
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['rb_name'];
		$row_data[] = date('d-m-Y H:i:s',strtotime($row['cdate']));
		$row_data[] = $row['company_name'];
		$row_data[] = $row['cust_name'].$row['opportunity_name'];
		$row_data[] = $row['lead_owner'];
		$row_data[] = $row['stage'];
		$row_data[] = $row['sales_stage'];
		$row_data[] = $row['probablity'];
		$row_data[] = date('d-m-Y',strtotime($row['closing_date']));

		$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_view/'.$row['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
		$row_data[] = $view_hist_btn;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_source_code") {
	$s_date=explode(' - ',$POST['date']);
	$where .= " and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";

	$query="select rf.rb_id, rf.rb_name, (select count(inquiry_id) as led from tbl_inquiry as e where e.inquiry_status=0 ".$where." and e.rb_id = rf.rb_id) as led from tbl_refer_by as rf WHERE rf.company_id IN (0,".$_SESSION['company_id'].")  group by rf.rb_id";

	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['rb_name'];
		$row1[$i]['id']=$row['rb_id'];	
		$i++;
	}	
	echo json_encode($row1);
}

?>