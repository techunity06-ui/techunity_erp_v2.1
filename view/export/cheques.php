<?php
    session_start();
    include('../../config/config.php');
    include('../../config/session.php');
 
    $rows = array();
    
    $sWhere = " WHERE ( `cheque_iscancel` = 0 AND cheque_of = '$_SESSION[user_id]' ";
    
    if(isset($_SESSION['ch_payee']) && $_SESSION['ch_payee'] != -9 && $_SESSION['ch_payee'] != "") {
	$sWhere .= " AND `cheque_payee` = '$_SESSION[ch_payee]'";
    }
    
    if(isset($_SESSION['ch_acc']) && $_SESSION['ch_acc'] != -9 && $_SESSION['ch_acc'] != "") {
	$sWhere .= " AND `cheque_acc` = '$_SESSION[ch_acc]'";
    }
    
    if(isset($_SESSION['ch_amnt']) && $_SESSION['ch_amnt'] != -9 && $_SESSION['ch_amnt'] != "") {
	$sWhere .= " AND `cheque_amount` = '$_SESSION[ch_amnt]'";
    }
    
    if(isset($_SESSION['ch_sdate']) && isset($_SESSION['ch_edate']) && $_SESSION['ch_sdate'] != "" && $_SESSION['ch_edate'] != "") {
	$sWhere .= " AND (`cheque_date` >= '$_SESSION[ch_sdate]' AND `cheque_date` <= '$_SESSION[ch_edate]')";
    }
    
    $sWhere .= ")";
    
    
    $str = "SELECT cheque_id, cust_name, acc_name, bank_name, cheque_number, cheque_acc, acc_number, cheque_date, cheque_payee, cheque_amount, cheque_note,cheque_mode, cheque_morethen, cheque_iscancel, cheque_tmst, cheque_of FROM coro_cheques INNER JOIN `coro_accounts` ON `acc_id` = `cheque_acc` INNER JOIN `coro_banks` ON `bank_id` = `acc_bank` INNER JOIN `coro_customers` ON `cust_id` = `cheque_payee` $sWhere ORDER BY `cheque_date`,`acc_name`,`bank_name`,`cust_name`";
    
    $query = $dbcon -> query($str);
    
    $data = array();
    
    $TOTAL = 0;
    
    $One = array();
    
    while($r = $query->fetch_assoc()) {
	$data[] = $r;
	$TOTAL += $r['cheque_amount'];
	$One = $r;
    }
    
    
    $statement = "";
    
    if(isset($_SESSION['ch_payee']) && $_SESSION['ch_payee'] != -9 && $_SESSION['ch_payee'] != "") {
	$statement .= "PAYEE : ".$One['cust_name'];
    }
    
    if(isset($_SESSION['ch_acc']) && $_SESSION['ch_acc'] != -9 && $_SESSION['ch_acc'] != "") {
	$statement .= " ACCOUNT : ".$One['acc_name'].'( '.$One['bank_name'].' )';
    }
    
    if(isset($_SESSION['ch_amnt']) && $_SESSION['ch_amnt'] != -9 && $_SESSION['ch_amnt'] != "") {
	$statement .= " AMOUNT : ".indian_number($_SESSION['ch_amnt'],2);
    }
    
    if(isset($_SESSION['ch_sdate']) && isset($_SESSION['ch_edate']) && $_SESSION['ch_sdate'] != "" && $_SESSION['ch_edate'] != "") {
	$statement .= " FROM : ".date("d-m-Y",strtotime($_SESSION['ch_sdate'])).' To '.date("d-m-Y",strtotime($_SESSION['ch_edate']));
    }
    
?>
<html>
    <head>
	<title>Cheque Statement : <?php echo $statement; ?></title>
	<style>
	    body {
		width: 90%;
		height: 90%;
		margin: 20px auto;
		font-size: 11px;
	    }
	    #header {
		width: 100%;
	    }
	    
	    table.record
	    {
		font-size: 11px;
		border-color: #600;
		border-width: 0 0 1px 1px;
		border-style: dotted;
	    }
	    
	    .record td,.record th
	    {
		font-size: 11px;
		border-color: #600;
		border-width: 1px 1px 0 0;
		border-style: dotted;
		margin: 0;
		padding: 4px;;
	    }
	    
	    .note {
		font-size: 10px;
	    }
	    
	    
	    
		@media print
		{
			body {
				width: 90%;
				height: 90%;
				margin: 20px auto;
				font-size: 11px;
			}
			table { page-break-inside:auto }
	    		tr    { page-break-inside:avoid; page-break-after:auto }
		}
	</style>
    </head>
    <body onLoad="window.print()">
	<br>
	<table width="100%" cellspacing="20">
	    <tr>
		<td><img src="<?php echo DOMAIN; ?>images/mail/logo.png" style="display: inline-block;" height="70px" /></td>
		<td style="text-align: right;">
		    <h4 style="display: inline-block; float: right">Date : <?php echo date("d-m-Y"); ?></h4>
		</td>
	    </tr>
	    <tr>
		<td colspan="2" style="text-align: center;">
		    <h5>STATEMENT REPORT - <?php echo $statement; ?></h5>
		</td>
	    </tr>
	</table>
	<br />
	<table width="100%" class="record">
	    <tr>
		<td width="5%">Sr.</td>
		<td width="5%">Date</td>
		<td width="10%">Cheque</td>
		<td width="25%">Account</td>
		<td width="20%">Bank</td>
		<td width="25%">Payee</td>
		<td style="width: 10%;text-align: right;">Amount</td>
	    </tr>
	    <?php
	    $i=1;
	    foreach($data as $r) {
	        
		echo '
		    <tr>
			<td>'.$i.'</td>
			<td>'.date("d/m/Y",strtotime($r['cheque_date'])).'</td>
			<td>'.str_pad($r['cheque_number'], 6, '0', STR_PAD_LEFT).'</td>
			<td>'.$r['acc_name'].'</td>
			<td>'.$r['bank_name'].'</td>
			<td>'.$r['cust_name'].'</td>
			<td style="text-align: right;">'.indian_number($r['cheque_amount'],2).'</td>
		    </tr>
		';
		if(trim($r['cheque_note']) != "") {
		    echo '
			<tr>
			    <td colspan="2"></td>
			    <td class="note">NOTE:</td>
			    <td colspan="4" class="note">'.$r['cheque_note'].'</td>
			</tr>
		    ';
		}
		$i++;
	    }
	    ?>
	    <tr>
		<td colspan="6" style="text-align: right;">TOTAL </td>
		<td style="text-align: right;"><?php echo indian_number($TOTAL,2); ?></td>
	    </tr>
	</table>
    </body>
</html>