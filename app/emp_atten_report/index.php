<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "generate_report_emp_ledger")
	{
		$s_date=explode(' - ',$POST['date']);
		$start_date=date("Y-m-d",strtotime($s_date[0]));
		$end_date=date("Y-m-d",strtotime($s_date[1]));
		
		$user_id=$POST['user_id'];
		
		$whr='';
		$whr.=" and DATE_FORMAT(log.in_time,'%Y-%m-%d')>='".$start_date."' and DATE_FORMAT(log.in_time,'%Y-%m-%d')<='".$end_date."'";
		
		
		$str.='<table class="display table table-bordered" id="data_list">
		  <thead>
			  <tr>
				  <th width="5%" style="text-align:center">Sr. NO.</th>
				  <th width="10%" style="text-align:center">Date</th>
				  <th width="10%" style="text-align:center">Attendance</th>
				  <th width="75%" style="text-align:center">Remark</th>
			  </tr>
		  </thead>
		  <tbody>';
				  
		$query="SELECT log.*,usr.user_name, DATE(log.in_time) DateOnly,(SELECT count(log_id) from login_history WHERE uid=log.uid and in_time=log.in_time and attendance='yes') as attn_cnt FROM login_history as log 
INNER join users as usr on usr.user_id=log.uid
WHERE log.uid=$user_id ".$whr."
GROUP BY DateOnly";
		$query_rs=($dbcon->query($query));
		if(mysqli_num_rows($query_rs)>0){
			$i=1;
			while($rel=mysqli_fetch_assoc($query_rs))
			{
				$attn_cnt="No";
				if(intval($rel['attn_cnt'])>0){
					$attn_cnt="Yes";
				}
				
				
				$str .='
					<tr>
						<td>'.$i.'</td>
						<td>'.date("d-M-Y",strtotime($rel['DateOnly'])).'</td>
						<td>'.$attn_cnt.'</td>
						<td>';
				//Get Trn Remark Data
				$trn_qry="SELECT trn.*,comp.complaint_no FROM `tbl_comp_flp_rmrk_trn` as trn
					left join tbl_complaint as comp on comp.complaint_id=trn.complaint_id
					WHERE trn.rmrk_trn_status=0 and trn.user_id=".$user_id." and DATE_FORMAT(trn.rmrk_trn_date,'%Y-%m-%d')='".date("Y-m-d",strtotime($rel['DateOnly']))."'";
				$trn_qry_rs=$dbcon->query($trn_qry);
				if(mysqli_num_rows($trn_qry_rs)){
					$str .='<table class="display table table-bordered">
							<tr>
								<th>Sr.</th>
								<th>Complaint No.</th>
								<th>Remark</th>
							</tr>
						';
					$k=1;
					while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
						$str .='<tr>
							<td>'.$k.'</td>
							<td>'.$trn_rel['complaint_no'].'</td>
							<td>'.nl2br($trn_rel['rmrk_trn_remark']).'</td>
						</tr>';
						$k++;
					}
					$str .='</table>';
				}
					
				$str .='</td></tr>';
				$i++;
			}
		}
		else {
			$str .='<tr>
				<td colspan="11" style="text-align:center">NO DATA FOUND</td>
			</tr>';
		}
		
		$str .='</tbody>				 
				  </table>';
				  
		echo $str;	
	}
?>