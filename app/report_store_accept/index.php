<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    ADMINISTRATOR_LEDGER_DELETE,
    ADMINISTRATOR_LEDGER_EDIT
]);

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

		if(strtolower($POST['mode']) == "generate_report_production") 
		{
			$s_date=$POST['report_date'];
			$str="";
			
			$query="select pro.product_name,pro.product_category,sum(gtrn.product_qty) as total_qty,msgo.gd_name as grn_godwon,qcgo.gd_name as qc_godown,GROUP_CONCAT(gtrn.grn_trn_id) as trn_id from tbl_grn_trn as gtrn 
					left join tbl_grn as grn on grn.grn_id=gtrn.grn_id
					left join product_mst as pro on pro.product_id=gtrn.product_id
					left join mst_godown as msgo on msgo.gd_id=gtrn.grn_godown
					left join tbl_qc as qc on qc.grn_trn_id=gtrn.grn_trn_id
					left join mst_godown as qcgo on qcgo.gd_id=qc.qc_godown
					where gtrn.grn_trn_status=0 and pro.product_type=0 and grn.ref_type=1 and grn.grn_date='".date('Y-m-d',strtotime($s_date))."' group by gtrn.product_id";
			$qry=$dbcon->query($query);
			
			$cnt=1;
			
				$str.='<table class="table table-bordered table-stripped">
					<thead>
						<tr>
							<th colspan="3" >Store Accept Report</th>
							<th colspan="2" >Date : '.date('d-m-Y',strtotime($s_date)).'</th>
						</tr>
						<tr>
							<th>Sr. No.</th>
							<th>Model</th>
							<th>Qty</th>
							<th>Finish</th>
							<th>Rack</th>
						</tr>
					</thead>
					<tbody>';
			
			while($row=brp_mysqli_fetch_assoc($qry))
			{
				/* $query="select * from tbl_grn_trn as gtrn 
					where gtrn.grn_trn_status=0 and grn.ref_type=1 and grn.grn_date='".date('Y-m-d',strtotime($s_date))."' group by gtrn.product_id";
					$qry=$dbcon->query($query);
					$row=brp_mysqli_fetch_assoc($qry); */
				if(!empty($row['grn_godwon'])){
					$gstatus=$row['grn_godwon'];
				}else if(!empty($row['qc_godown'])){
					$gstatus=$row['qc_godown'];
				}else{
					$gstatus="Qc Pending";
				}	
				
				$str.='<tr>
					<td>'.$cnt.'</td>
					<td>'.$row['product_name'].'</td>
					<td>'.$row['total_qty'].'</td>
					<td>'.$row['product_category'].'</td>
					<td>'.$gstatus.'</td>
				</tr>';
				
				$cnt++;
				$total_qty=$total_qty+$row['total_qty'];
			}
			$str.='<tr>
					<td colspan="2" style="text-align:right;"><strong>Total</strong></td>
					<td>'.$total_qty.'</td>
					<td></td>
					<td></td>
				</tr>
				</tbody>
			</table>
			<div class="col-md-12">
				<div class="col-md-3"> <strong>Name : </strong><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> </div>
				<div class="col-md-3"> <strong> Date : </strong><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></div>
				<div class="col-md-3"><strong> Time : </strong><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></div>
				<div class="col-md-3"><strong>Signature : </strong><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></div>
			</div>
			';
				
			
			
			echo $str;
		}
		
?>