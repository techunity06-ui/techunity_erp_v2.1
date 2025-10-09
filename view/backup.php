<?php

    include("../config/config.php");		
	include("../include/function_database_query.php");
	$flag=$_REQUEST['flag'];
//SERVER,DB_USER,DB_PASS,DB
function EXPORT_TABLES($host,$user,$pass,$name,$flag,$dbcon,  $tables=false, $backup_name=false ){
	$mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
	$queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); }
	$content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--Database: `".$name."`\r\n\r\n\r\n";
	foreach($target_tables as $table){
		$result	= $mysqli->query('SELECT * FROM '.$table); 	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	$TableMLine=$res->fetch_row();
		$content .= "\n\n".$TableMLine[1].";\n\n";
		for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
			while($row = $result->fetch_row())	{ //when started (and every after 100 command cycle):
				if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
					$content .= "\n(";
					for($j=0; $j<$fields_amount; $j++)  { $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ; }else {$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}		}
					$content .=")";
				//every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
				if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
			}
		} $content .="\n\n\n";
	}
	$content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
	/**Code for backup record add*/
	$backup_name = $backup_name ? $backup_name : $name."___(".date('d-m-Y H').")__rand".".sql";
	$info['type']=$flag;
	$info['filename']=$backup_name;
	add_record('tbl_db_backup',$info,$dbcon);
	/**Code for backup record add*/
	if($flag==1)//from login js call
	{
		$handle = fopen(BACKUP.$backup_name,'w+');
		fwrite($handle,$content);
		fclose($handle);
		echo '<META http-equiv="refresh" content="0;URL='.ROOT_F.'">';//autobkp and then redirect to dashboard
	}
	else if($flag==2)//direct url call from menu or button of backup
	{
		header('Content-Type: application/octet-stream');	
		header('Content-Description: File Transfer');
 
		header("Content-Disposition: attachment; filename=\"".$backup_name."\"");
		//readfile($backup_name);
		echo $content;
		exit;
		
		/*header( "Content-Disposition: attachment; filename=$FileName" );
		$Path = "FielPath";
		$Filename_Path = $Path.$FileName;
		$FielContents = file_get_contents($Filename_Path);
		print "$FielContents";*/
	}
	if($flag==3)//from setting call Remove all data
	{
		$handle = fopen(BACKUP.$backup_name,'w+');
		fwrite($handle,$content);
		fclose($handle);
		$alltable=array("coro_chequebook","coro_cheques","coro_vouchers","cust_tempdata","login_history","product_tempdata","tbl_banktransaction","tbl_customer","tbl_db_backup","tbl_direct_entry","tbl_estimate","tbl_estimatetrn","tbl_estimatetrntemp","tbl_invoice","tbl_invoicetrn","tbl_invoicetrntemp","tbl_passbookentry","tbl_payment_cheque_generate","tbl_pono","tbl_potrancation","tbl_purchaseorder","tbl_purchaseordertrn","tbl_purchaseproduct","tbl_purchasereceipt","tbl_purchasetrntemp","tbl_receipt","tbl_vender","todo_mst","tbl_inward","tbl_inwardtrn","tbl_inwardtrntemp","tbl_per_invoice","tbl_per_invoicetrn","tbl_per_invoicetrntemp","tbl_potrntemp","tbl_custmer_consignee","tbl_sales_order","tbl_sales_ordertrn","tbl_sales_ordertrntemp","tbl_serialtrn","tbl_product","pay_terms","supply_place","mode_of_dispatch","tbl_credit_note","tbl_credit_notetrn","tbl_credit_notetrntemp","tbl_debitnote","tbl_debitnote_trn","tbl_debitnote_trntemp","tbl_used_credit","tbl_used_debit","terms_mst","terms_condition");
		for($i=0;$i<sizeof($alltable);$i++)
		{
			$tan_query="TRUNCATE TABLE ".$alltable[$i];
			$rel_tan=$dbcon->query($tan_query);
		}
		
		echo '<META http-equiv="refresh" content="0;URL='.ROOT_F.'company-setting">';//autobkp and then TRUNCATE  All Table
	}
	if($flag==4)//from setting call Remove transaction tables
	{
		$handle = fopen(BACKUP.$backup_name,'w+');
		fwrite($handle,$content);
		fclose($handle);
		$handle = fopen(BACKUP.$backup_name,'w+');
		fwrite($handle,$content);
		fclose($handle);
		$alltable=array("coro_chequebook","coro_cheques","coro_vouchers","cust_tempdata","login_history","product_tempdata","tbl_banktransaction","tbl_db_backup","tbl_direct_entry","tbl_estimate","tbl_estimatetrn","tbl_exporttemp","tbl_invoice","tbl_invoicetrn","tbl_invoicetrntemp","tbl_passbookentry","tbl_payment_cheque_generate","tbl_pono","tbl_potrancation","tbl_purchaseorder","tbl_purchaseordertrn","tbl_purchaseproduct","tbl_purchasereceipt","tbl_purchasetrntemp","tbl_receipt","todo_mst","tbl_inward","tbl_inwardtrn","tbl_inwardtrntemp","tbl_per_invoice","tbl_per_invoicetrn","tbl_per_invoicetrntemp","tbl_potrntemp","tbl_serialtrn","tbl_used_debit","terms_mst","terms_condition");
		for($i=0;$i<sizeof($alltable);$i++)
		{
			$tan_query="TRUNCATE TABLE ".$alltable[$i];
			$rel_tan=$dbcon->query($tan_query);
		}
		echo '<META http-equiv="refresh" content="0;URL='.ROOT_F.'company-setting">';//autobkp and then TRUNCATE  tranaction Table
	}
	else if($flag==5)//direct url call from Logout 
	{
		$backup_name1 = $backup_name1 ? $backup_name1 : $name.".sql";
		$handle = fopen(BACKUP.$backup_name1,'w+');
		fwrite($handle,$content);
		fclose($handle);
		echo '<META http-equiv="refresh" content="0;URL='.ROOT.'logout">';
	}
	
}
EXPORT_TABLES(SERVER,DB_USER,DB_PASS,DB,$flag,$dbcon);
?>
