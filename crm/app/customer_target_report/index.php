<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}

		if(strtolower($POST['mode']) == "generate_report") {
			$date = getFinacialyear_data($dbcon);
			extract($date);
			$companyConfiguration=getCompanyConfiguration($dbcon);
			$outstanding = $companyConfiguration['enable_count_outstanding_target'];
			$str='';
			$set="SELECT * FROM `tbl_company` where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));
			$str .='<div id="payment_detail">
			<table  class="display table table-bordered table-striped" id="data_list">
			<thead> 
			<tr><td class="noborder" colspan="20" style="border:none;text-align: center;">
			<span id="head_logo"><strong style="">'.$set_head['company_name'].'</strong></span></td>
			<td colspan="22" style="text-align: center;">
			<strong style="">'.$set_head['company_name'].'</strong></span>
			</td>
			</td>
			</tr>
			<tr>
			<td class="" colspan="5" style="border-left:none;border-right:none"><strong>Target Report</strong></td>
			<td class="" colspan="10" style="border-left:none;border-right:none"></td>
			<td class="" colspan="6" style="text-align:right;border-left:none;border-right:none">Date<label>  : <strong>'.date('d/m/Y',strtotime($date['financial_start_date'])).'</strong> To <strong>'.date('d/m/Y',strtotime($date['financial_end_date'])).'</strong></label>
			</td>
			<td class="" colspan="4" style="border-right:none"><strong>Target Report</strong></td>
			<td class="" colspan="10" style="border-left:none;border-right:none"></td>
			<td class="" colspan="7" style="border-left:none;border-right:none"> 
			Date
			<label>  : <strong>'.date('d/m/Y',strtotime($date['financial_start_date'])).'</strong> To <strong>'.date('d/m/Y',strtotime($date['financial_end_date'])).'</strong></label>
			</td>
			</tr>
			<tr>
			<th  style="text-align:left">No</th>
			<th  style="text-align:left">Name</th>
			<th  style="text-align:left">Owner user</th>
			<th  style="text-align:left">APR TARGET</th>
			<th  style="text-align:left">APR</th>
			<th  style="text-align:left">APR OUT </th>
			<th  style="text-align:left;">MAY TARGET </th>
			<th  style="text-align:left">MAY</th>
			<th  style="text-align:left">MAY OUT</th>
			<th  style="text-align:left">JUN TARGET</th>
			<th  style="text-align:left">JUN</th>
			<th  style="text-align:left">JUN OUT</th>
			<th  style="text-align:left">JUL TARGET</th>
			<th  style="text-align:left">JUL</th>
			<th  style="text-align:left">JUL OUT</th>
			<th  style="text-align:left">AUG TARGET</th>
			<th  style="text-align:left">AUG</th>
			<th  style="text-align:left">AUG OUT</th>
			<th  style="text-align:left">SEP TARGET</th>
			<th  style="text-align:left">SEP</th>
			<th  style="text-align:left">SEP OUT</th>
			<th  style="text-align:left">OCT TARGET</th>
			<th  style="text-align:left">OCT</th>
			<th  style="text-align:left">OCT OUT</th>
			<th  style="text-align:left">NOV TARGET</th>
			<th  style="text-align:left">NOV</th>
			<th  style="text-align:left">NOV OUT</th>
			<th  style="text-align:left">DECC TARGET</th>
			<th  style="text-align:left">DECC</th>
			<th  style="text-align:left">DECC OUT</th>
			<th  style="text-align:left">JAN TARGET</th>
			<th  style="text-align:left">JAN</th>
			<th  style="text-align:left">JAN OUT</th>
			<th  style="text-align:left">FEB TARGET</th>
			<th  style="text-align:left">FEB</th>
			<th  style="text-align:left">FEB OUT</th>
			<th  style="text-align:left">MAR TARGET</th>
			<th  style="text-align:left">MAR</th>
			<th  style="text-align:left">MAR OUT</th>
			<th  style="text-align:left">Target</th>
			<th  style="text-align:left">Achived Target</th>
			<th  style="text-align:left">Pening Traget</th>
			</tr>
			</thead>
			<tbody>';
			$start_year = date('Y',strtotime($date['financial_start_date']));
			$end_year = date('Y',strtotime($date['financial_end_date']));
			$start_date = date('Y-m-d',strtotime($date['financial_start_date']));
			$end_date = date('Y-m-d',strtotime($date['financial_end_date']));
			if($_SESSION['user_type']==2)
			{
				if(!empty($_POST['user_id'])){
					$where.=" and FIND_IN_SET(".$_POST['user_id'].",cst.cust_assign_user)";
				}else{
					$where="";
				}
				// $where.=" and FIND_IN_SET(".$_POST['user_id'].",cst.cust_assign_user)";
			}
			else{
				$where.=" and FIND_IN_SET(".$_POST['user_id'].",cst.cust_assign_user)";
			}
			if(!empty($_POST['state_id'])){
				$where.=" and cst.state_id = ".$_POST['state_id'];
			}
			$sql = "SELECT name, user_name, 
			APR_TARGET,APR,APR_OUT,
			MAY_TARGET,MAY,MAY_OUT,
			JUN_TARGET,JUN,JUN_OUT,
			JUL_TARGET,JUL,JUL_OUT,
			AUG_TARGET,AUG,AUG_OUT,
			SEP_TARGET,SEP,SEP_OUT,
			OCT_TARGET,OCT,OCT_OUT,
			NOV_TARGET,NOV,NOV_OUT,
			DECC_TARGET,DECC,DECC_OUT,
			JAN_TARGET,JAN,JAN_OUT,
			FEB_TARGET,FEB,FEB_OUT,
			MAR_TARGET,MAR,MAR_OUT, 
			(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET) as Target ,
			(APR+MAY+JUN+JUL+AUG+SEP+OCT+NOV+DECC+JAN+FEB+MAR) as Achived_Target,
			(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET)-(APR+MAY+JUN+JUL+AUG+SEP+OCT+NOV+DECC+JAN+FEB+MAR) as Pening_Traget from( 
				select cst.cust_name as name,users.user_name as user_name,
				f.forecast_amount_pr as APR_TARGET, sum(case when MONTH(invs.invoice_date)= 4 then total else 0 end) 'APR', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 4 then total else 0 end) 'APR_OUT', 
				f.forecast_amount_pr as MAY_TARGET, sum(case when MONTH(invs.invoice_date)= 5 then total else 0 end) 'MAY', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 5 then total else 0 end) 'MAY_OUT', 
				f.forecast_amount_pr as JUN_TARGET, sum(case when MONTH(invs.invoice_date)= 6 then total else 0 end) 'JUN', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 6 then total else 0 end) 'JUN_OUT', 
				f.forecast_amount_pr as JUL_TARGET, sum(case when MONTH(invs.invoice_date)= 7 then total else 0 end) 'JUL', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 7 then total else 0 end) 'JUL_OUT', 
				f.forecast_amount_pr as AUG_TARGET, sum(case when MONTH(invs.invoice_date)= 8 then total else 0 end) 'AUG', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 8 then total else 0 end) 'AUG_OUT', 
				f.forecast_amount_pr as SEP_TARGET, sum(case when MONTH(invs.invoice_date)= 9 then total else 0 end) 'SEP', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 9 then total else 0 end) 'SEP_OUT', 
				f.forecast_amount_pr as OCT_TARGET, sum(case when MONTH(invs.invoice_date)= 10 then total else 0 end) 'OCT', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 10 then total else 0 end) 'OCT_OUT', 
				f.forecast_amount_pr as NOV_TARGET, sum(case when MONTH(invs.invoice_date)= 11 then total else 0 end) 'NOV', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 11 then total else 0 end) 'NOV_OUT', 
				f.forecast_amount_pr as DECC_TARGET, sum(case when MONTH(invs.invoice_date)= 12 then total else 0 end) 'DECC', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 12 then total else 0 end) 'DECC_OUT', 
				f.forecast_amount_pr as JAN_TARGET, sum(case when MONTH(invs.invoice_date)= 1 then total else 0 end) 'JAN', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 1 then total else 0 end) 'JAN_OUT', 
				f.forecast_amount_pr as FEB_TARGET, sum(case when MONTH(invs.invoice_date)= 2 then total else 0 end) 'FEB', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 2 then total else 0 end) 'FEB_OUT', 
				f.forecast_amount_pr as MAR_TARGET, sum(case when MONTH(invs.invoice_date)= 3 then total else 0 end) 'MAR', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 3 then total else 0 end) 'MAR_OUT', 
				total 'Achived_Target' from tbl_customer cst 
				LEFT JOIN tbl_cust_forecast_pr as f ON cst.cust_id = f.forecast_cust_id 
				LEFT JOIN users as users ON users.user_id =cst.cust_owner 
				LEFT join ( select invs.cust_id,invs.invoice_date,SUM(invtrn.total) as total from tbl_invoice as invs left join tbl_invoicetrn as invtrn on invtrn.invoice_id=invs.invoice_id where invs.invoice_status=0 and invtrn.trancation_status=0 and invs.invoice_date>='$start_date' and invs.invoice_date <='$end_date' AND MONTH(invs.invoice_date)=4) as invs on invs.cust_id = cst.ledger_id 
				where f.forecast_type='1' and f.isdelete='0' AND f.forecast_year between $start_year AND $end_year AND cst.ledger_id != 0 and cst.cust_status=0 $where GROUP BY cst.cust_id) as a
			Union ALL 
				select 'Total','-', sum(APR_TARGET),sum(APR),sum(APR_OUT),sum(MAY_TARGET),sum(MAY),sum(MAY_OUT),sum(JUN_TARGET),sum(JUN),sum(JUN_OUT),sum(JUL_TARGET),sum(JUL),sum(JUL_OUT),sum(AUG_TARGET),sum(AUG),sum(AUG_OUT),sum(SEP_TARGET), sum(SEP),sum(SEP_OUT),sum(OCT_TARGET),sum(OCT),sum(OCT_OUT),sum(NOV_TARGET),sum(NOV),sum(NOV_OUT),sum(DECC_TARGET),sum(DECC),sum(DECC_OUT),sum(JAN_TARGET),sum(JAN),sum(JAN_OUT),sum(FEB_TARGET),sum(FEB),sum(FEB_OUT),sum(MAR_TARGET),sum(MAR),sum(MAR_OUT), sum(Target) ,sum(Achived_Target),sum(Pening_Traget) from ( 
					SELECT name,
					APR_TARGET,APR,APR_OUT,
					MAY_TARGET,MAY,MAY_OUT,
					JUN_TARGET,JUN,JUN_OUT,
					JUL_TARGET,JUL,JUL_OUT,
					AUG_TARGET,AUG,AUG_OUT,
					SEP_TARGET,SEP,SEP_OUT,
					OCT_TARGET,OCT,OCT_OUT,
					NOV_TARGET,NOV,NOV_OUT,
					DECC_TARGET,DECC,DECC_OUT,
					JAN_TARGET,JAN,JAN_OUT,
					FEB_TARGET,FEB,FEB_OUT,
					MAR_TARGET,MAR,MAR_OUT, 
					(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET) as Target ,
					(APR+MAY+JUN+JUL+AUG+SEP+OCT+NOV+DECC+JAN+FEB+MAR) as Achived_Target,
					(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET)-(APR+MAY+JUN+JUL+AUG+SEP+OCT+NOV+DECC+JAN+FEB+MAR) as Pening_Traget from( 
						select cst.cust_name as name,users.user_name as user_name ,
						f.forecast_amount_pr as APR_TARGET, sum(case when MONTH(invs.invoice_date)= 4 then total else 0 end) 'APR', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 4 then total else 0 end) 'APR_OUT', 
						f.forecast_amount_pr as MAY_TARGET, sum(case when MONTH(invs.invoice_date)= 5 then total else 0 end) 'MAY', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 5 then total else 0 end) 'MAY_OUT', 
						f.forecast_amount_pr as JUN_TARGET, sum(case when MONTH(invs.invoice_date)= 6 then total else 0 end) 'JUN', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 6 then total else 0 end) 'JUN_OUT', 
						f.forecast_amount_pr as JUL_TARGET, sum(case when MONTH(invs.invoice_date)= 7 then total else 0 end) 'JUL', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 7 then total else 0 end) 'JUL_OUT', 
						f.forecast_amount_pr as AUG_TARGET, sum(case when MONTH(invs.invoice_date)= 8 then total else 0 end) 'AUG', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 8 then total else 0 end) 'AUG_OUT', 
						f.forecast_amount_pr as SEP_TARGET, sum(case when MONTH(invs.invoice_date)= 9 then total else 0 end) 'SEP', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 9 then total else 0 end) 'SEP_OUT', 
						f.forecast_amount_pr as OCT_TARGET, sum(case when MONTH(invs.invoice_date)= 10 then total else 0 end) 'OCT', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 10 then total else 0 end) 'OCT_OUT', 
						f.forecast_amount_pr as NOV_TARGET, sum(case when MONTH(invs.invoice_date)= 11 then total else 0 end) 'NOV', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 11 then total else 0 end) 'NOV_OUT', 
						f.forecast_amount_pr as DECC_TARGET, sum(case when MONTH(invs.invoice_date)= 12 then total else 0 end) 'DECC', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 12 then total else 0 end) 'DECC_OUT', 
						f.forecast_amount_pr as JAN_TARGET, sum(case when MONTH(invs.invoice_date)= 1 then total else 0 end) 'JAN', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 1 then total else 0 end) 'JAN_OUT',
						f.forecast_amount_pr as FEB_TARGET, sum(case when MONTH(invs.invoice_date)= 2 then total else 0 end) 'FEB', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 2 then total else 0 end) 'FEB_OUT', 
						f.forecast_amount_pr as MAR_TARGET, sum(case when MONTH(invs.invoice_date)= 3 then total else 0 end) 'MAR', f.forecast_amount_pr-sum(case when MONTH(invs.invoice_date)= 3 then total else 0 end) 'MAR_OUT', 
						total 'Achived_Target' from tbl_customer cst 
						LEFT JOIN tbl_cust_forecast_pr as f ON cst.cust_id = f.forecast_cust_id 
						LEFT JOIN users as users ON users.user_id =cst.cust_owner 
						LEFT join ( select invs.cust_id,invs.invoice_date,SUM(invtrn.total) as total from tbl_invoice as invs left join tbl_invoicetrn as invtrn on invtrn.invoice_id=invs.invoice_id where invs.invoice_status=0 and invtrn.trancation_status=0 and invs.invoice_date>='$start_date' and invs.invoice_date <='$end_date'  AND MONTH(invs.invoice_date)=4) as invs on invs.cust_id = cst.ledger_id 
						where f.forecast_type='1' and f.isdelete='0' AND f.forecast_year between $start_year AND $end_year AND cst.ledger_id != 0 and cst.cust_status=0 $where GROUP BY cst.cust_id) as a ) as b 
					group by 1";
			// echo $sql;
			$query = $dbcon->query($sql);
			$i = 1;
			while($re=brp_mysqli_fetch_assoc($query))
			{
				$str.='<tr>
				<td style="text-align:center" class="">'.$i.'</td>
				<td style="text-align:center" class="">'.$re["name"].'</td>
				<td style="text-align:center" class="">'.$re["user_name"].'</td>
				<td style="text-align:center" class="">'.$re["APR_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["APR"].'</td>
				<td style="text-align:center" class="">'.$re["APR_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["MAY_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["MAY"].'</td>
				<td style="text-align:center" class="">'.$re["MAY_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["JUN_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["JUN"].'</td>
				<td style="text-align:center" class="">'.$re["JUN_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["JUL_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["JUL"].'</td>
				<td style="text-align:center" class="">'.$re["JUL_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["AUG_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["AUG"].'</td>
				<td style="text-align:center" class="">'.$re["AUG_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["SEP_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["SEP"].'</td>
				<td style="text-align:center" class="">'.$re["SEP_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["OCT_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["OCT"].'</td>
				<td style="text-align:center" class="">'.$re["OCT_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["NOV_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["NOV"].'</td>
				<td style="text-align:center" class="">'.$re["NOV_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["DECC_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["DECC"].'</td>
				<td style="text-align:center" class="">'.$re["DECC_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["JAN_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["JAN"].'</td>
				<td style="text-align:center" class="">'.$re["JAN_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["FEB_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["FEB"].'</td>
				<td style="text-align:center" class="">'.$re["FEB_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["MAR_TARGET"].'</td>
				<td style="text-align:center" class="">'.$re["MAR"].'</td>
				<td style="text-align:center" class="">'.$re["MAR_OUT"].'</td>
				<td style="text-align:center" class="">'.$re["Target"].'</td>
				<td style="text-align:center" class="">'.$re["Achived_Target"].'</td>
				<td style="text-align:center" class="">'.$re["Pening_Traget"].'</td>

				</tr>';


				$i++;
			}

			echo $str;

		}

	}

}
?>