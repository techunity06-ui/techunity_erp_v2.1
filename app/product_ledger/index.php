<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include("../../config/image.php");
$image = new SimpleImage();
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
		if(brp_strtolower($POST['mode']) == "generate_report") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$start_date = $s_date[0];
		$end_date=$s_date[1];
		$qry_pro="select * from product_mst where product_id=".$POST['prod_id'];
		$pro_rel=brp_mysqli_fetch_assoc($dbcon->query($qry_pro));		
			$str .='
				<table  width="100%"   class="display table  table-striped">
				</table>
				<table  class="display table table-bordered table-striped" id="data_list">
					<tr>
						<td colspan="2"><strong>Product Ledger </strong></td>
						<td colspan="2" style="text-align:center"><strong> Product Name : '.$pro_rel['product_name'].' -- '.$pro_rel['product_icode'].'
						</strong></td>
						<td colspan="2" style="text-align:right">Date
						<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
					</tr>
					
					<tr>
						<th width="5%" style="text-align:center">Sr. NO.</th>
						<th width="12%" style="text-align:center">Date</th>
						<th width="47%" style="text-align:center">Description</th>
						<th width="12%" style="text-align:center">Add Stock</th>
						<th width="12%" style="text-align:center">Deduct Stock</th>
						<th width="12%" style="text-align:center">Total</th>
					</tr>
				 <tbody>';
		$query="select * from tbl_stock_trn where stock_status!=2";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
		
		$opening_stock = get_current_opening_stock_below_start_date($dbcon,$POST['prod_id'],$pro_rel['product_base_unit'],$start_date,$end_date);
		$total = '';
		$total = $opening_stock;
// var_dump($opening_stock);
		$str .='<tr>
					<td data-label="" style="text-align:center"></td>
					<td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
					<td data-label="DESCRIPTION" style="text-align:center">Opening Stock</td>
					<td data-label="DEBIT AMOUNT" style="text-align:center">- </td>
					<td data-label="CREDIT AMOUNT" style="text-align:center"> -</td>';
					if($opening_stock<0){
						$str .='<td data-label="BALANCE" style="text-align:center;color:red;">'.$opening_stock.'</td>';
					}else if($opening_stock>0){
						$str .='<td data-label="BALANCE" style="text-align:center;color:green;">'.$opening_stock.'</td>';
					}else{
						$str .='<td data-label="BALANCE" style="text-align:center;color:green;">-</td>';
					}
			$qry='select mst.*,IFNULL(SUM(mst.base_stock),0) as base_stock,IFNULL(SUM(mst.convert_stock),0) as convert_stock,pro.product_base_unit,unit.unit_name from tbl_stock_trn as mst
			left join product_mst as pro on pro.product_id = mst.product_id
			left join unit_mst as unit on unit.unitid = pro.product_base_unit
			 where mst.stock_status=0 and mst.stock_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and mst.stock_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and mst.product_id='.$POST['prod_id'] . ' GROUP BY ref_id,ref_name ORDER BY mst.stock_id';
			// echo "</br></br>";
			//var_dump($qry);
			$result1=$dbcon->query($qry);
			$i=1;
				
			if(brp_mysqli_num_rows($result1)>0)
			{
					$i=1;
					$add_stock="";
					$deduct_stock="";
					while($re=brp_mysqli_fetch_assoc($result1))
					{
						$description  = get_stock_ledger($dbcon,$re['ref_name'],$re['ref_id']);
						$str .='<tr>
							<td style="text-align:center">'.$i.'</td>
							<td style="text-align:center">'.date('d/m/Y',strtotime($re['stock_date'])).'</td>
							<td style="text-align:center">'.$description.'</td>';
							if($re['stock_flage'] ==1){
								if($re['product_base_unit'] == $re['base_unit']){
									$str .='<td style="text-align:center;color:green;">'.$re['base_stock'].' '.$re['unit_name'].'</td>';
									$add_stock = $re['base_stock'];
								}else{
									$str .='<td style="text-align:center;color:green;">'.$re['convert_stock'].' '.$re['unit_name'].'</td>';
									$add_stock = $re['convert_stock'];
								}
								$total +=$add_stock; 
							}else{
								$str .='<td style="text-align:center"> - </td>';
							}
							if($re['stock_flage'] ==2){
								if($re['product_base_unit'] == $re['base_unit']){
									$str .='<td style="text-align:center;color:red;">'.$re['base_stock'].' '.$re['unit_name'].'</td>';
									$deduct_stock =  $re['base_stock'];
								}else{
									$str .='<td style="text-align:center;color:red;">'.$re['convert_stock'].' '.$re['unit_name'].'</td>';
									$deduct_stock = $re['convert_stock'];
								}
								$total -=$deduct_stock;
							}else{
								$str .='<td style="text-align:center"> - </td>';
							}
							
							if($total<0){
								$str .='<td style="text-align:center;color:red;">'.$total.' '.$re['unit_name'].'</td>';
							}else if($total>0){
								$str .='<td style="text-align:center;color:green;">'.$total.' '.$re['unit_name'].'</td>';
							}else{
								$str .='<td style="text-align:center"></td>';
							}
						$str .='</tr>';
					}
					$i++;
			}
			else
			{
				$str .='<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
			}
			$str .='</tbody>				 
				  </table>';
				  
			echo $str;
		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
?>