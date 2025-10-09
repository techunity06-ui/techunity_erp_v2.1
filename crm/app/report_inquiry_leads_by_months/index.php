<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "generate_report_product_service1")
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
							
						</tr>
						<tbody>';
						
							/* echo $query="select e.cdate,cust.cust_name,us.user_name as lead_owner,op.opp_stage as sales_stage,et.stage_prob as probablity,e.closing_date from tbl_inquiry as e 
									left join tbl_task  as et on et.inquiry_id=e.inquiry_id
									left join tbl_customer as cust on cust.cust_id=e.cust_id
									left join users as us on us.user_id=e.user_id
									left join tbl_opportunity_mst as op on op.opp_id=et.opp_id
									where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.rb_id in (".$sour.") and e.won_user_id=0"; */
							 $query="select e.cdate,e.inquiry_name as oppurtunity_name,cust.cust_name,us.user_name as lead_owner,op.opp_stage as stage,e.stage_prob as probablity,e.closing_date,mc.mcd_name as sales_stage from tbl_inquiry as e 
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
	}
    
    else if(strtolower($POST['mode']) == "generate_report_product_service"){
		
		$s_date=explode(' - ',$POST['date']);
			
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$soure=explode(",",$POST['source_id']);
		//$sour=implode(",",$POST['source_id']);
		$sour=implode(",",$soure);
		//print_r($POST['source_id']);
		$closing_date=$POST['leaddate'];
			//var_dump($sour);
			$start_date=$POST['source_id'];
			$end_date=date("Y-m-t", strtotime($start_date));

		if(!empty($closing_date)){
			$va='';
			$va=" and e.closing_date='".$closing_date."'";
		}else{
			$va=" and e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.inquiry_date >= '".date('Y-m-d',strtotime($start_date))."' AND e.inquiry_date <= '".date('Y-m-d',strtotime($end_date))."'";
		}
		$appData = array();
		//'mc.mcd_name as sales_stage'
			$i=1;
			$aColumns = array('DISTINCT e.cdate','e.inquiry_date','e.inquiry_id','e.inquiry_name as oppurtunity_name','mc.mcd_name as sales_stage','mc1.mcd_name as inquiry_type','cust.cust_name','country.country_name','state.state_name','city.city_name','cadd.c_add_address','us.user_name as lead_owner','rf.rb_name','op.opp_stage as stage','e.stage_prob as probablity','e.closing_date','e.inquiry_id');
			$sIndexColumn = "e.inquiry_id";
			$isWhere = array("e.company_id IN (0,$_SESSION[company_id])".$va);
			$sTable = " tbl_inquiry as e";			
			$isJOIN = array("left join tbl_task  as et on et.inquiry_id=e.inquiry_id",
							"left join tbl_customer as cust on cust.cust_id=e.cust_id",
							"left join tbl_cust_address as cadd on cadd.cust_id=e.cust_id and cadd.c_add_status=0 and cadd.c_addr_defult=1",
						    "left join city_mst as city on city.cityid=cadd.c_add_city",
						    "left join state_mst as state on state.stateid=cadd.c_add_state",
   							"left join country_mst as country on country.countryid=cadd.c_add_country",
							"left join users as us on us.user_id=e.user_id",
							"left join tbl_opportunity_mst as op on op.opp_id=e.opp_id",
							"left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id","left join tbl_refer_by as rf on rf.rb_id=e.rb_id",
							"left join tbl_master_category_detail as mc1 on mc1.mcd_id=e.inquiry_type_id");
			$hOrder = "e.inquiry_id";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = date('d-m-Y',strtotime($row['inquiry_date']));
				/*$row_data[] = $row['inquiry_type'];*/
				$row_data[] = $row['cust_name'];
				/*$row_data[] = $row['oppurtunity_name'];*/
				$row_data[] = $row['c_add_address']." ,".$row['city_name']." ,".$row['state_name'];
				$row_data[] = $row['lead_owner'];
				$row_data[] = $row['stage'];
				$row_data[] = $row['sales_stage'];
				/*$row_data[] = $row['probablity'];*/
				$row_data[] = $row['rb_name'];
				$row_data[] = date('d-m-Y',strtotime($row['closing_date']));
				$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_view/'.$row['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
				$row_data[] = $view_hist_btn;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "generate_report_leads_months") {
			$start_date=$POST['start_date'];
			$end_date=date("Y-m-t", strtotime($start_date));
			$group.=" e.inquiry_date between '".date("Y-m-d",strtotime($start_date))."' and '".date("Y-m-d",strtotime($end_date))."'";
			$whr.=" and e.inquiry_date between '".date("Y-m-d",strtotime($start_date))."' and '".date("Y-m-d",strtotime($end_date))."'";
			$query="select COUNT(*)as led,e.closing_date,e.inquiry_name as oppurtunity_name from tbl_inquiry as e left join tbl_task  as et on et.inquiry_id=e.inquiry_id where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 ".$whr." AND e.company_id IN (0,".$_SESSION['company_id'].") group by ".$group."";
			//var_dump($query);
			$result=$dbcon->query($query);
			$i=0;
			$date=date_create($end_date);
			$start_datename= date_format($date,"Y-F");
			while($row=mysqli_fetch_assoc($result))
			{	
				$row1[$i]['y']= (int)$row['led'];
				$row1[$i]['label']=$start_datename;
				//$row1[$i]['label1']=$end_date;
				$row1[$i]['month']=$start_date;	
				$i++;
			}	
			echo json_encode($row1);
		}
	
?>