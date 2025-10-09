<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	$_id = $dbcon -> real_escape_string($_GET['id']);
	$q = $dbcon -> query("SELECT `v_id`,`v_cheque`,`v_tds`,DATE(v_tmst) as `date`,`v_rec_name`,`v_rec_mobno` FROM `coro_vouchers` WHERE `v_id` = '$_id' AND `v_of` = '$_SESSION[user_id]'");
	$row = $q->fetch_assoc();
	
	$query = $dbcon -> query("SELECT cheque_id, vender_name, acc_name, bank_name, cheque_number, cheque_acc, acc_number, cheque_date, cheque_payee, ROUND(cheque_amount) as cheque_amount, cheque_mode, cheque_morethen, cheque_iscancel, cheque_tmst, cheque_of FROM coro_cheques INNER JOIN `account_mst` as acc ON `acc_id` = `cheque_acc` INNER JOIN `bank_mst` as bank  ON bank.`bankid` = acc.`bankid` INNER JOIN `tbl_vender` ON `vender_id` = `cheque_payee` WHERE `cheque_id` = '$row[v_cheque]' AND `cheque_of` = '$_SESSION[user_id]'");
	$r = $query->fetch_assoc();
	//var_dump($r);
	$r['cheque_date'] = date("d-m-Y",strtotime($r['cheque_date']));
	$r['cheque_number'] = str_pad($r['cheque_number'], 6, '0', STR_PAD_LEFT);
	$row['v_id'] = str_pad($row['v_id'], 6, '0', STR_PAD_LEFT);
	$r['mode'] = "1";
	if($row['v_tds'] == 0) {
		$tds = "N/A";
		$tds_amnt = 0;
		$o_amnt = $r['cheque_amount'];
	}
	else {
		$tds = $row['v_tds'];
		$r_tds = 100 - $row['v_tds'];
		$o_amnt = (100 * $r['cheque_amount']) / $r_tds;
		$tds_amnt = ($o_amnt * ($row['v_tds'] / 100));
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo "Voucher - ".$row['v_id'];?></title>
	<style type="text/css">
		body {
			width: 95%;
			height: 421 px !important;
			overflow: hidden;
			margin: 0 auto;
			padding: 10px;
		}
		
		header {
			width: 100%;
			height: auto;
		}
		
		table.dotted
		{
		    border-color: #600;
		    border-width: 0 0 1px 1px;
		    border-style: dotted;
		}
		
		.dotted td,.dotted th
		{
		    border-color: #600;
		    border-width: 1px 1px 0 0;
		    border-style: dotted;
		    margin: 0;
		    padding: 4px;;
		}
		
		/*table.dotted , .dotted td, .dotted th {
			border: 1px dotted;
		}*/
	</style>
<script src='<?=ROOT_CHEQUE?>js/jquery.js'></script>
<script type="text/javascript">
function printbill()
{
	var originalContents = document.body.innerHTML;
	//var duplicate = $("#voucherprint").clone().appendTo("#voucherprint");
	var duplicate = $("#voucherprint").clone().prepend("<hr style='border-color:#000; border-style:dashed; margin:10px 0' />").appendTo("#voucherprint");
	 var printContents = document.getElementById('voucherprint').innerHTML;     
     document.body.innerHTML = printContents;
	 console.log(printContents);
     window.print();

     //document.body.innerHTML = originalContents;
}
</script>
</head>
<body onload="printbill();" >
	<div id="voucherprint">
	<table width="100%" >
		<tr>
			<td width="70%"><?=COMPANY?></td>
			<td><h4 style="float: right;">DATE : <?php echo date("d-m-Y",strtotime($row['date'])); ?></h4></td>
		</tr>
	</table>
	<center><h4>PAYMENT VOUCHER</h4></center>
	<table width="100%" class="dotted">
		<thead>
			<tr>
				<th width="70%">NAME / COMPANY</th>
				<th width="30%">AMOUNT</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $r['vender_name'].' - '.$r['bank_name'].'('.$r['acc_number'].'- #'.$r['cheque_number'].')'; ?></td>
				<td style="text-align: right;"><?php echo indian_number($o_amnt,2); ?></td>
			</tr>
			<tr>
				<td style="text-align: right;"><strong>TDS (@ <?php echo $tds; ?> %) :</strong></td>
				<td style="text-align: right;"><?php echo indian_number($tds_amnt,2); ?></td>
			</tr>
			<tr>
				<td style="text-align: right;"><strong>AMOUNT :</strong></td>
				<td style="text-align: right;"><?php echo indian_number($r['cheque_amount'],2); ?></td>
			</tr>
			<tr>
				<td colspan="2"><strong>NOTE :</strong><?php echo $r['cheque_note']; ?><br/> <i>If there is no objection against the payment made within 7 days of issue of cheque, we will take it granted that the payment made by us is acceptable to you</i></td>				
			</tr>
		</tbody>
	</table>
	<table width="100%">
		<tr>
			<td width="50%" style="text-align: left;"><br><br><br><hr align="left" width="40%"><strong>Received By</strong>: <?=$row['v_rec_name']?></td>
			<td width="50%" style="text-align: right;"><br><br><br><hr align="right" width="40%"><strong>Authorized Sign.</strong></td>
		</tr>
		<tr>
			<td width="50%" style="text-align: left;"><strong>Contact NO.</strong>: <?=$row['v_rec_mobno']?></td>
			<td width="50%" style="text-align: right;"></td>
		</tr>
	</table>
	</div>
	
</body>
</html>
