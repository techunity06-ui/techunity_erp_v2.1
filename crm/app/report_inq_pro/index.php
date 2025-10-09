<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "generate_report") {
		
		$s_date=explode(' - ',$POST['date']);
		$str='';$whr='';
		$str.='<table class="display table table-bordered table-striped">
				<thead>
					<tr>
						<th>Sr. No.</th>
						<th>Product Name</th>				  
						<th>Quantity</th>				  
						<!--<th>Amount</th>-->				  
						<th>Customer Name</th>
						<th>Contact Name</th>				  
						<th>Address</th>				  
						<th>City Name</th>				  
						<th>State Name</th>				  
						<th>Mobile</th>				  
						<th>Email</th>				  
						<th>Inquiry No</th>				  
						<!--<th>Opportunity Name</th>-->			  
						<th>Owner</th>				  
						<!--<th>Territory</th>-->				  
						<th>Created Date</th>				  
						<th>Modified Date</th>				  
						<th>Type</th>				  
						<th>Stage</th>				  
						<th>Close Date</th>				  
						<!--<th>Category</th>-->				  
						<th>Sales Stage</th>				  
						<th>Desc.</th>				  
						<th>Competition Status</th>	
						<th>Actions</th>			  
					</tr>
				</thead>
				<tbody>';
	
	$whr.=" and inq.inquiry_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	if(!empty($POST['product_name'])){
		$whr='';
		$whr.=" and pro.product_name='".$POST["product_name"]."'";
		$whr.=" and pro.product_id='".$POST["dproduct_id"]."'";
	}
	if($POST['user_id']){
		$whr.=' and inq.user_id='.$POST['user_id'];
	}
	if($POST['t_id']){
		$whr.=' and inq.t_id='.$POST['t_id'];
	}
	if($POST['country_id']){
		$whr.=' and addr.c_add_country='.$POST['country_id'];
	}
	if($POST['state_id']){
		$whr.=' and addr.c_add_state='.$POST['state_id'];
	}
	if($POST['city_id']){
		$whr.=' and addr.c_add_city='.$POST['city_id'];
	}
	
	//var_dump($POST['product_id']);
	if($POST['product_id']){
		$whr.=' and trn.product_id='.$POST['product_id'];
	}
	
$c=1;
$qry="SELECT DISTINCT trn.*,pro.product_name,cust.cust_name,inq.inquiry_no,inq.inquiry_name,usr.user_name,ter.t_name,inq_type.mcd_name as inq_type_name,stage.opp_stage,pro.product_icode,inq.closing_date,inq.create_date,inq.cdate,inq_cat.mcd_name as inq_cat_name,inq_sale_stage.mcd_name as inq_sale_stage_name,inq.inq_desc,inq.inq_comp_desc , con.c_con_fname, con.c_con_lname, con.c_con_email, con.c_con_mobile, addr.c_add_address, city.city_name, state.state_name
from tbl_inquiry_trn as trn 
left join tbl_inquiry as inq on inq.inquiry_id=trn.inquiry_id
left join tbl_customer as cust on cust.cust_id=inq.cust_id
left join tbl_cust_contact as con on con.c_con_id = inq.c_con_id
left join tbl_cust_address as addr on addr.cust_id = cust.cust_id and addr.c_addr_defult=1
left join users as usr on usr.user_id=inq.user_id
left join territory_mst as ter on ter.t_id=inq.t_id
left join tbl_master_category_detail as inq_type on inq_type.mcd_id=inq.inquiry_type_id
left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id
left join tbl_master_category_detail as inq_cat on inq_cat.mcd_id=inq.inquiry_cat_id
left join tbl_master_category_detail as inq_sale_stage on inq_sale_stage.mcd_id=inq.sales_stage_id
left join city_mst as city on city.cityid = addr.c_add_city
left join state_mst as state on state.stateid = addr.c_add_state
left join product_mst as pro on pro.product_id=trn.product_id
WHERE trn.inquiry_trn_status=0 and inq.inquiry_status=0 and stage.opp_stage!='LOST' AND trn.company_id IN (0,".$_SESSION['company_id'].")".$whr." order by inquiry_date";
	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$closing_date='';
			if($rel['closing_date']!='1970-01-01' && $rel['closing_date']!='0000-00-00'){
				$closing_date=date("d-m-Y",strtotime($rel['closing_date']));
			}
			$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_view/'.$rel['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
			$str.='<tr>
				<td class="text-left">'.$c.'</td>
				<td class="text-left">'.$rel['product_name'].'--'.$rel['product_icode'].'</td>
				<td class="text-left">'.$rel['product_qty'].'</td>
				<!--<td class="text-left">'.$rel['product_amount'].'</td>-->
				<td class="text-left">'.$rel['cust_name'].'</td>
				<td class="text-left">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</td>
				<td class="text-left">'.$rel['c_add_address'].'</td>
				<td class="text-left">'.$rel['city_name'].'</td>
				<td class="text-left">'.$rel['state_name'].'</td>
				<td class="text-left">'.$rel['c_con_mobile'].'</td>
				<td class="text-left">'.brp_ucwords(brp_strtolower($rel['c_con_email'])).'</td>
				<td class="text-left">'.$rel['inquiry_no'].'</td>
				<!--<td class="text-left">'.$rel['inquiry_name'].'</td>-->
				<td class="text-left">'.$rel['user_name'].'</td>
				<!--<td class="text-left">'.$rel['t_name'].'</td>-->
				<td class="text-left" style="white-space:nowrap;">'.date("d-m-Y h:i A",strtotime($rel['create_date'])).'</td>
				<td class="text-left" style="white-space:nowrap;">'.date("d-m-Y h:i A",strtotime($rel['cdate'])).'</td>
				<td class="text-left">'.$rel['inq_type_name'].'</td>
				<td class="text-left">'.$rel['opp_stage'].'</td>
				<td class="text-left" style="white-space:nowrap;">'.$closing_date.'</td>
				<!--<td class="text-left">'.$rel['inq_cat_name'].'</td>-->
				<td class="text-left">'.$rel['inq_sale_stage_name'].'</td>
				<td class="text-left">'.nl2br($rel['inq_desc']).'</td>
				<td class="text-left">'.nl2br($rel['inq_comp_desc']).'</td>
				<td class="text-left">'.$view_hist_btn.'</td>
			</tr>';
			$c++;
		}
	}
	else{
		$str.='<tr><td colspan="20" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
		
		$str.='</tbody>				 
			</table>';
		
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "generate_report_inq_pro") {
		$s_date=explode(' - ',$POST['rep_date']);
		$whr.=" and inq.inquiry_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
		if($POST['user_id']){
			$whr.=' and inq.user_id='.$POST['user_id'];
		}
		if($POST['t_id']){
			$whr.=' and inq.t_id='.$POST['t_id'];
			$stat=1;
		}
		
		if($POST['country_id']){
			$whr.=' and addr.c_add_country='.$POST['country_id'];
		}
		
		if($POST['state_id']){
			$whr.=' and addr.c_add_state='.$POST['state_id'];
		}
		
		if($POST['city_id']){
			$whr.=' and addr.c_add_city='.$POST['city_id'];
		}

		if($stat==1){
			$pro_id=$POST['product_id'];
			if($pro_id != ''){
				//$pro_id=implode(",",$pro_id1);
				$whr.=' and trn.product_id in('.$pro_id.')';
			}
		}else{
			$pro_id=$POST['product_id'];
			if($pro_id != ''){
				//$pro_id=implode(",",$pro_id1);
				$whr.=' and trn.product_id in('.$pro_id.')';
			}
		}
		
		$query="SELECT COUNT(*)as tot_rec,pro.product_name,inq.create_date,inq.inquiry_no,pro.product_id from tbl_inquiry_trn as trn 
		left join tbl_inquiry as inq on inq.inquiry_id=trn.inquiry_id 
		left join product_mst as pro on pro.product_id=trn.product_id 
		left join tbl_cust_address as addr on addr.cust_id = inq.cust_id and addr.c_addr_defult=1
		WHERE trn.inquiry_trn_status=0 and inq.inquiry_status=0 AND trn.company_id IN (0,".$_SESSION['company_id'].") ".$whr." group by pro.product_name";
		//var_dump($query);
		$result=$dbcon->query($query);
		$i=0;
		while($row=mysqli_fetch_assoc($result))
		{	
			$row1[$i]['y']= (int)$row['tot_rec'];
			$row1[$i]['label']=$row['product_name'];
			//$row1[$i]['label']=$POST['rep_date'];
			$row1[$i]['product_name']=$row['product_name'];	
			$row1[$i]['product_id']=$row['product_id'];	
			$i++;
		}	
		echo json_encode($row1);

	}
?>