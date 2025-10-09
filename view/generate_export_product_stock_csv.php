<?php
/// stock report convert in excel

	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
		
		$delimiter = ",";
		$filename = "products_".date('d-M-Y H:i:s O').".csv";
		
		//create a file pointer
		$f = fopen('php://memory', 'w');
		
		//set column headers
		$fields = array('Sr No','Product Code','Product Name','Product Alias Name','Godown Name','Godown Stock','Reserve Stock','Stock','Unit');
		fputcsv($f, $fields, $delimiter);
		$p=1;
					
		 
		 $get_pro_qry="SELECT SQL_CALC_FOUND_ROWS pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name,pro.product_icode,pro.product_alias_name, pro.product_status, 
					(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." group by qc.product_id) as base_stock_add, 
					(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." group by qc.product_id) as base_stock_minus, 
					
					(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." group by qc.product_id) as con_stock_add, 
					
					(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=".$_SESSION['company_id']." group by qc.product_id) as con_stock_minus 
					
					FROM tbl_stock_trn as sto 
					left join product_mst as pro on pro.product_id=sto.product_id
					left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 ) group by sto.product_id ORDER BY pro.product_name" ;
					
		
				$get_pro_qry_rs=($dbcon->query($get_pro_qry));
				
				while($pro_rel=mysqli_fetch_assoc($get_pro_qry_rs)){

					//$product_name = $pro_rel['product_name'].'-'.$pro_rel['product_icode'].'-'.$pro_rel['product_alias_name'];
					//$pro_type_name=get_product_type_name($dbcon,$pro_rel['product_type']);
					$stock=($pro_rel['base_stock_add']+$pro_rel['con_stock_add'])-($pro_rel['base_stock_minus']+$pro_rel['con_stock_minus']);
					//$processstock=get_process_stock_detail($dbcon,$pro_rel['product_id']);
					$godownstock=get_godown_stock($dbcon,$pro_rel['product_id'],$pro_rel['product_base_unit']);
					$reserstock=reserve_stock($dbcon,$pro_rel['product_id'],$pro_rel['product_base_unit']);
					
					//$processstock=str_replace('<table class="table ">'," ",$processstock);
					//$processstock=str_replace('<tr>',"</br>",$processstock);
					$unitname=$pro_rel['unit_name'];
					
                    if($stock!=0){
					$q="select gd_name,gd_id from mst_godown as gd 
					where g_status=0 order by gd_id";
			
					$rel=$dbcon->query($q);
					$s=0;
					while($row=mysqli_fetch_array($rel))
					{
						$stock_g=get_current_godown_stock_new($dbcon,$pro_rel['product_id'],$pro_rel['product_base_unit'],$row['gd_id']);
						if($stock_g!=0){
							if($s=="0"){
								$lineData = array($p,$pro_rel['product_icode'],$pro_rel['product_name'],$pro_rel['product_alias_name'],$row['gd_name'],$stock_g,$reserstock,$stock,$unitname);
							}else{
								$lineData = array("","","","",$row['gd_name'],$stock_g,"","","");
							}
							fputcsv($f, $lineData, $delimiter);
							$s=1;
						}
					}
					
					$p++;
                    }
				}
		
		//move back to beginning of file
		fseek($f, 0);
		
		//set headers to download file rather than displayed
		/*header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '";');*/
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: ".date('D M d Y H:i:s O'));
		//header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: ".$now." GMT");
		
		// force download  
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		
		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=".$filename."");
		header("Content-Transfer-Encoding: binary");
		
		//output all remaining data on a file pointer
		fpassthru($f);
		exit;

?>
