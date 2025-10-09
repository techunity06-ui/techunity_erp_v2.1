<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
$total=0;
if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
	if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['type']) == "search") {
			
			$html = '
			<div class="table-responsive">
				<table class="no-border">
					<thead class="no-border">
						<tr>
							<th style="color:#7761a7">NUMBER</th>
							<th style="color:#7761a7">DATE</th>
							<th style="color:#7761a7">PAYEE</th>
							<th style="color:#7761a7">ACCOUNT</th>
							<th class="text-right;" style="color:#7761a7;">AMOUNT</th>
						</tr>
					</thead>
					<tbody class="no-border-y">
			';
			
			$query = $dbcon->query("SELECT `cheque_number`,`cheque_date`,`vender_name`,`acc_name`,`bank_name`,`cheque_amount` FROM `coro_cheques` INNER JOIN `account_mst` as acc ON acc.acc_id = `cheque_acc` INNER JOIN `bank_mst` as bank ON bank.bankid = acc.bankid INNER JOIN `tbl_vender` as ven ON ven.vender_id = `cheque_payee` WHERE (`cheque_number` = '$POST[q]' OR `vender_name` LIKE '%$POST[q]%' OR `bank_name` = '$POST[q]' OR `cheque_amount` = '$POST[q]') AND `cheque_of` = '$_SESSION[user_id]' ORDER BY `cheque_date` DESC");
			if($query->num_rows == 0) {
				$html .= '
					<tr>
						<td colspan="5" class="color:red;"><center><b>NO RECORDS FOUND !</b></center></td>
					</tr>
				';
			}
			else {
				while($r = $query->fetch_assoc()) {
					$html .= '
						<tr>
							<td>'.str_pad($r['cheque_number'], 6, '0', STR_PAD_LEFT).'</td>
							<td>'.date("d/m/Y",strtotime($r['cheque_date'])).'</td>
							<td>'.$r['vender_name'].'</td>
							<td>'.$r['acc_name'].'<br /> ('.$r['bank_name'].')</td>
							<td align="right">'.indian_number($r['cheque_amount'],2).'</td>
						</tr>
					';
					$total=$r['cheque_amount']+$total;
				}
				$html .='<tr><td colspan="4" align="right"> <b>Total</b> :</td><td align="right">'.indian_number($total,2).' </td></tr>';
			}
			
			$html .= '</tbody>
			</table>
			</div>';
			
			echo $html;
		}
	}
    else {
        die("Error - 2");
    }
}
else {
    die("Error - 1");
}
?>
