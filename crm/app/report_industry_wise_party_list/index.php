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
//print_r($_POST);	
if(strtolower($POST['mode']) == "generate_industry_wise_party_list1")
{
	$s_date=explode(' - ',$POST['date']);

	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];

			//$source=explode(',',$POST['source_id']);
			//echo $POST['source_id'];
	$sour=implode(",",$POST['source_id']);

			//$pr_row=get_product_detail($dbcon,$product_id);

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%"   class="display table table-bordered table-striped">
	</table>
	<table  class="display table table-bordered table-striped" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr>
	<td colspan="3"><strong>Leads by Source Reports</strong></td>
	<td colspan="3" style="text-align:center">
	<!--<strong>	Name:'.$cust_rel['company_name'].'</strong><br>
	<strong>Product Name :'.$pr_row['product_name'].'</strong>-->
	</td>
	<td colspan="4" style="text-align:right">
	Date <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
	</td>
	</tr>
	<tr>
	<th width="5%" style="text-align:center">Sr. NO.</th>
	<th width="12%" style="text-align:center;white-space:nowrap;"><span>Lead generation </span><br/> Date Time</th>
	<th width="20%" style="text-align:center">Compamy Name</th>
	<th width="12%" style="text-align:center">Oppurtunity Name</th>
	<th width="12%" style="text-align:center">Lead Owner</th>
	<th width="12%" style="text-align:center">Stage</th>
	<th width="12%" style="text-align:center">Sales Stage</th>
	<th width="12%" style="text-align:center">Probablity</th>
	<th width="12%" style="text-align:center">Closing Date</th>
	<th width="12%" style="text-align:center">Actions</th>

	</tr>
	<tbody>';

	$query="select e.cdate,e.inquiry_name as oppurtunity_name,e.inquiry_id,cust.cust_name,us.user_name as lead_owner,op.opp_stage as stage,e.stage_prob as probablity,e.closing_date,mc.mcd_name as sales_stage from tbl_inquiry as e 
	left join tbl_task  as et on et.inquiry_id=e.inquiry_id
	left join tbl_customer as cust on cust.cust_id=e.cust_id
	left join users as us on us.user_id=e.user_id
	left join tbl_opportunity_mst as op on op.opp_id=e.opp_id
	left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id
	where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.rb_id in (".$sour.") AND e.company_id IN (0,".$_SESSION['company_id'].")";

	$result1=$dbcon->query($query);
	$i=1;
	$cnt=mysqli_num_rows($result1);
	if($cnt>0)
	{
		$total=0;
		while($re=mysqli_fetch_assoc($result1))
		{
			$balancetype='';
			$str.='<tr>
			<td style="text-align:center">'.$i.'</td>
			<td style="text-align:center">'.date('d-m-Y H:i:s',strtotime($re['cdate'])).'</td>

			<td style="text-align:center">'.$re["cust_name"].'</td>
			<td style="text-align:center">'.$re["oppurtunity_name"].'</td>
			<td style="text-align:center">'.$re["lead_owner"].'</td>
			<td style="text-align:center">'.$re["stage"].'</td>
			<td style="text-align:center">'.$re["sales_stage"].'</td>
			<td style="text-align:center">'.$re["probablity"].'</td>
			<td style="text-align:center">'.date('d-m-Y',strtotime($re['closing_date'])).'</td>
			<td style="text-align:center">'.date('d-m-Y',strtotime($re['closing_date'])).'</td>

			';
			$i++;
		}

	}
	else
	{
		$str .='<tr>
		<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
		</tr>';

	}
	$str .='</tbody>				 
	</table>';
	echo $str;
}else if(strtolower($POST['mode']) == "generate_industry_wise_party_list"){
	$cust_ind_id=$POST['cust_ind'];
	$t_id=$POST['t_id'];
	$countryid = $POST['country'];
	$stateid = $POST['state'];
	$cityid = $POST['city'];

	$where='';
	if($cust_ind_id){
		$where.=' and cindu.ci_id='.$POST['cust_ind'];
	}
	if($t_id){
		$where.=' and cust.t_id='.$POST['t_id'];
	}
	
	if($countryid !=''){
		$where.=' and custadd.c_add_country='.$countryid;
	}

	if($stateid !=''){
		$where.=' and custadd.c_add_state='.$stateid;
	}

	if($cityid !=''){
		$where.=' and custadd.c_add_city='.$cityid;
	}

	$appData = array();
	$i=1;
	$aColumns = array('cust.cust_id', 'cindu.ci_name', 'tere.t_name','customcon.c_con_fname', 'customcon.c_con_lname', 'comp.company_name', 'custadd.c_add_address', 'country.country_name', 'state.state_name', 'city.city_name', 'cc.cc_name', 'cust.party_type', 'cust.cust_name', 'cust.cust_email', 'cust.cust_mobile', 'cust.cust_gst', 'cust.cust_status','cust.cdate','cust.user_id');
	$sIndexColumn = "cust.cust_id";
	$isWhere = array("cust.cust_status = 0 AND cust.company_id IN (0,$_SESSION[company_id])".$where);
	$sTable = " tbl_customer as cust";			
	$isJOIN = array("left join tbl_customer_industry as cindu on cindu.ci_id=cust.cust_ind",
		"left join tbl_customer_category as cc on cc.cc_id=cust.cust_cat",
		"left join tbl_company as comp on comp.company_id = cust.company_id",
		"left join tbl_cust_address as custadd on custadd.cust_id=cust.cust_id and custadd.c_addr_defult=1",
		"left join country_mst as country on country.countryid=custadd.c_add_country",
		"left join state_mst as state on state.stateid=custadd.c_add_state",
		"left join city_mst as city on city.cityid=custadd.c_add_city",
		"left join tbl_cust_contact as customcon on customcon.cust_id=cust.cust_id",
		"left join territory_mst as tere on tere.t_id=cust.t_id");
	$hOrder = "cust.cust_id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;

	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['cust_name'];
		$row_data[] = $row['ci_name'];
			$row_data[] = $row['t_name'];
		$row_data[] = $row['c_add_address'];
		$row_data[] = $row['country_name'];
		$row_data[] = $row['state_name'];
		$row_data[] = $row['city_name'];
		$row_data[] = $row['c_con_fname'].' '.$row['c_con_lname'];
		$row_data[] = $row['cust_mobile'];
		$row_data[] = $row['cust_email'];
		$row_data[] = 0;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}



?>