<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
		$qry3="SELECT * FROM `tbl_stock_trn` as potrn WHERE stock_status=0 and ref_name!='opening_stock'";
		$result3=$dbcon->query($qry3);
		while($rel3=brp_mysqli_fetch_assoc($result3))
		{
			$qry4="SELECT stock_id,ref_id FROM `tbl_stock_trn` as g_trn
					WHERE ref_name='opening_stock' and stock_flage=1 and stock_status=0 and godown_id=".$rel3['godown_id']." and product_id=".$rel3['product_id'];
				$result4=$dbcon->query($qry4);
				$rel4=brp_mysqli_fetch_assoc($result4);
				
			if($rel3['stock_flage']=="2"){
				if(!empty($rel4['stock_id'])){
					$qry_up11="UPDATE `tbl_stock_trn` SET `base_stock` = base_stock+".$rel3['base_stock'].",`convert_stock` = convert_stock+".$rel3['convert_stock']." WHERE stock_id=".$rel4['stock_id'];
						$result_up11=$dbcon->query($qry_up11);
					
					$qry_up112="UPDATE `tbl_branch_product_stock` SET `product_stock` = product_stock+".$rel3['base_stock']." WHERE branch_product_stock_id=".$rel4['ref_id'];
						$result_up131=$dbcon->query($qry_up112);
				}else{
					//start
						
							$info['product_stock']	=$rel3['base_stock'];
							$info['branch_id']		=$rel3['godown_id'];
							$info['priority']		="0";
							$info['user_id']		=$_SESSION['user_id'];
							$info['cdate']			=date("Y-m-d h:i:s");
							$info['company_id']		=$_SESSION['company_id'];
							
							$info['product_id']=$rel3['product_id'];
							//var_dump($info);
							$table='tbl_branch_product_stock';$tableid='branch_product_stock_id';
							
							if(!empty($info['product_stock'])){
								if($info['product_stock']!="0.00"){
									$inserid=add_record($table, $info, $dbcon);
								}
							}
								$ref_id=$inserid;
							
							$date1=date("Y-m-d");
							$ref_name="opening_stock";
							
							if(!empty($info['product_stock'])){
								if($info['product_stock']!="0.00"){
									add_stock($dbcon,$rel3['product_id'],$rel3['base_unit'],$date1,$ref_name,$ref_id,$info['branch_id'],$info['product_stock'],1,$rel3['branch_id']);
								}
							}
					
					//end
				}
			}else{
				$qry_up11="UPDATE `tbl_stock_trn` SET `base_stock` = base_stock-".$rel3['base_stock'].",`convert_stock` = convert_stock-".$rel3['convert_stock']." WHERE stock_id=".$rel4['stock_id'];
					$result_up11=$dbcon->query($qry_up11);
				
				$qry_up112="UPDATE `tbl_branch_product_stock` SET `product_stock` = product_stock-".$rel3['base_stock']." WHERE branch_product_stock_id=".$rel4['ref_id'];
					$result_up131=$dbcon->query($qry_up112);
			}
			
			
		}
	
	
	$qry2="SELECT product_id FROM `product_mst` as potrn WHERE 1";
		$result2=$dbcon->query($qry2);
		while($rel1=brp_mysqli_fetch_assoc($result2))
		{
			
			$qry4="SELECT sum(base_stock) as cstock FROM `tbl_stock_trn` as g_trn
					WHERE ref_name='opening_stock' and stock_flage=1 and stock_status=0 and product_id=".$rel1['product_id'];
			$result4=$dbcon->query($qry4);
			$rel4=brp_mysqli_fetch_assoc($result4);
			
			$qry_up11="UPDATE `product_mst` SET `product_opening` = ".$rel4['cstock']." WHERE product_id=".$rel1['product_id'];
				$result_up11=$dbcon->query($qry_up11);
				
				
		}

?>
