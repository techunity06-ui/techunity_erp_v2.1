<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
include($path."config/image.php");

$getspecialConfiguration=getspecialConfiguration($dbcon);


$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
$image = new SimpleImage();
if(strtolower($POST['mode']) == "generate_report") {
	$s_date=explode(' - ',$POST['date']);
	$vender_id = $POST['ledger_id'];
	$led_id = implode(",",$POST['ledger_id']);
	$where ="";
	if($led_id != ""){
		$where .= " and job.vender_id in (".$led_id.")";
	}
	
	$product_id = $POST['product_id'];
	$pro_id = implode(",",$POST['product_id']);
	if($pro_id != ""){
		$where .= " and jstrn.product_id in (".$pro_id.")";
	}
	
	$str .='<table  class="display table table-bordered table-striped" id="" style="overflow: auto;">
	<thead class="resdisplay">
	<tr id="logo">
	<td class="noborder" colspan="'.$colspan.'" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong></td>
	</tr>
	<tr>
	<td colspan="'.$colspan.'" class="noborder"><strong>GRN Register</strong></td>
	<td class="noborder"><strong>Date :</strong>'.date('d/m/Y').'</td>
	</tr>
	<tr>

	<th style="text-align:center;">Sr. NO.</th>';
	$str .='<th style="text-align:center;white-space:nowrap;">WORK ORDER NO</th>';
	$str .='<th style="text-align:center;white-space:nowrap;">JOB WORK DATE</th>
	<th style="text-align:center;white-space:nowrap;">JOB WORK ORDER NO</th>
	<th style="text-align:center;white-space:nowrap;"> CHALAN NO</th>
	<th style="text-align:center;white-space:nowrap;">GRN NO </th>
	<th style="text-align:center;white-space:nowrap;">GRN DATE</th>
	<th style="text-align:center;white-space:nowrap;">Party Name</th>
	<th style="text-align:center;white-space:nowrap;">Product Name</th>
	<th style="text-align:center;white-space:nowrap;">JOB WORK QTY</th>
	<th style="text-align:center;white-space:nowrap;">GRN QTY</th>
	<th style="text-align:center;white-space:nowrap;">Process Name</th>
	<th style="text-align:center;white-space:nowrap;">Location</th>
	<th style="text-align:center;white-space:nowrap;">IQC Date</th>
	<th style="text-align:center;white-space:nowrap;">Insp. User</th>
	</tr>
	</thead>
	<tbody>';
	
	
	$grn_register = 'select job.*,sp.po_req_no as workorder_no,grn.grn_no,grn.challan_no,grn.grn_date,led.l_name as party_name,pro.product_name,jstrn.product_base_qty as jobwork_qty,gstrn.product_qty as grn_qty, pr.process_name, gd.gd_name,user.user_name from tbl_job_work_sub_trn as jstrn 
	left join tbl_job_work_trn as jtrn on jtrn.job_work_trn_id = jstrn.job_work_trn_id
	left join tbl_job_work as job on job.job_work_id = jtrn.job_work_id
	left join tbl_grn_sub_trn as gstrn on gstrn.job_work_sub_trn_id = jstrn.job_work_sub_trn_id
	left join tbl_grn_trn as gtrn on gtrn.grn_trn_id = gstrn.grn_trn_id
	left join tbl_grn as grn on grn.grn_id = gtrn.grn_id
	left join tbl_ledger as led on led.l_id = job.vender_id
	left join tbl_request_product as rp on rp.rp_id = jstrn.rp_id
	left join tbl_set_main_process as sp on sp.sp_id = rp.sp_id
	left join product_mst as pro on pro.product_id = jstrn.product_id
	left join process_mst as pr on pr.process_id = jtrn.process_id
	left join mst_hsn_code as mhsn on mhsn.hsn_id = pro.product_hsn
	left join unit_mst as unit on unit.unitid = jstrn.product_base_unit
	left join unit_mst as cunit on cunit.unitid = jstrn.product_con_unit
	left join users as user on user.user_id = jstrn.user_id
	left join mst_godown as gd on gd.gd_id = gtrn.grn_godown
	where gtrn.grn_trn_status=0 and gstrn.status = 0 and grn.grn_status=0 and jstrn.job_work_sub_trn_status = 0 and job.job_work_status = 0 and jtrn.job_work_trn_status in(0,1) and grn.ref_type in (1) and job.job_work_type = 2  '.$where.' and grn.grn_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and grn.grn_date<="'.date('Y-m-d',strtotime($s_date[1])).'"';
		//$str .= $grn_register;
	$grn_ex = $dbcon->query($grn_register);
	$i = 1;
	
		//$str .= $grn_register;
	if(brp_mysqli_num_rows($grn_ex)>0){
	
		while($grn_row = brp_mysqli_fetch_assoc($grn_ex)){
	
				$str .='<tr>
				<td style="text-align:center;white-space:nowrap">'.$i.'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['workorder_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['job_work_date'])).'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['job_work_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['challan_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['grn_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['grn_date'])).'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['party_name'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['product_name'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['jobwork_qty'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['grn_qty'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['process_name'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['gd_name'].'</td>
				<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['job_work_date'])).'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['user_name'].'</td>			
			</tr>';

			$i++; 
			}
		}else{
			$str .='<tr>
			<td colspan="14" style="text-align:center">No Data Yet..!!!</td>
			</tr>';
		}
		$str .='</tbody>				 
		</table>';
		
		echo $str;
	}
	?>
