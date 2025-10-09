<?php 

	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once($include."function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");


 $qry122="select * from tbl_journal as cert where journal_status=2";
	$ro12=$dbcon->query($qry122);
	while($rea=mysqli_fetch_assoc($ro12)){
		
		 $query="select * from  tbl_journal_trn as mst 
			where mst.journal_id=".$rea['journal_id'];
		    $result=$dbcon->query($query);
			$table_name="tbl_journal_trn";
			while($rel=mysqli_fetch_assoc($result))
			{
				echo $rel['journal_trn_id'];
				echo "</br>";
				$info1['genral_book_status']		= 2;
				$updateid=update_record('tbl_general_book', $info1,"table_name='".$table_name."' and table_id=".$rel['journal_trn_id'] , $dbcon);
			}
}
	
	

?>