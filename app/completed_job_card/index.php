<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
	/*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report") {
			$s_date=date('Y-m-d',strtotime($POST['date']));
			$branch=$POST['branch_id'];
			$st_type=$POST['st_type'];

			$where='';

				//$where.=" and pro.branch_id='$branch'";

		/*HAVING j_qty>tqty
		,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
		where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty*/

		if(!empty($POST['product_id'])){
			$ser_pro=" and jo.j_product_id=".$POST['product_id'];
		}
		if(!empty($POST['vender_id'])){
			$ser_ven=" and jo.j_vendor=".$POST['vender_id'];
		}
		$query='select jo.*,pr.product_name,led.l_name,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
		where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
		left join product_mst as pr on pr.product_id=jo.j_product_id
		left join tbl_ledger as led on led.l_id=jo.j_vendor
		where jo.job_close_status="0" '.$ser_pro.' '.$ser_ven.' and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'].' HAVING j_qty<=tqty';
		$rs=$dbcon->query($query);
		$str='';$i=1;
		$rel_num_rows=mysqli_num_rows($rs);
		if($rel_num_rows>0){
			while($rel=mysqli_fetch_assoc($rs))
			{
				$pending_qty=$rel['j_qty']-$rel['tqty'];
				$user_n=find_user_name($dbcon,$rel['userid']);
				$str.='<tr>
				<td style="text-align:center;">'.$i.'</td>

				<td><!--<a href="'.ROOT.'jobcard_detail/'.$rel['jobwork_id'].'">'.$rel['product_name'].'</a>-->'.$rel['product_name'].'</td>
				<td >'.$rel['l_name'].'</td>
				<td style="text-align:right;">'.$rel['j_qty'].'</td>
				<td style="text-align:right;">'.$pending_qty.'</td>
				<td >'.$user_n.'</td>
				<td style="text-align:center;">

				<a class="btn btn-xs btn-success" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.'job_card_print/'.$rel['jobwork_id'].'" ><i class="fa fa-print"></i></a>
				</td>
				</tr>';
				$i++;					  
			}
		}
		else{
			$str.= '<tr><td colspan="7" style="text-align:center;">No Data Found !!!</td></tr>';
		}


			//echo $query;
		echo $str;

	}


	


	
	
}

}

?>