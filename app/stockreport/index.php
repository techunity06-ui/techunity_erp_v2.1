<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");
include_once("../../include/common_functions/common_production_functions.php");
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
}

else if(strtolower($POST['mode']) == "abcanalysis")
{
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%" class="display">
	</table>
	<table  class="display table-bordered" width="100%" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ ABC Analysis ]</strong>
	</td>
	</tr>';
	$i=1;
	$total=0;
	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="5%" style="text-align:center;padding:10px 5px">NO.</th>
	<th width="20%" style="text-align:center">Item Details</th>
	<th width="20%" style="text-align:center">Consumption Value</th>
	<th width="20%" style="text-align:center">% in Total Consumption Value</th>
	<th width="5%" style="text-align:center">Class</th>
	</tr>';
	$j=1;
	for ($x = 1; $x <= 20; $x++)  {
		$str.='<tr style="border: 1px dashed #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">UCICS0013 <br> CASTING - Big C I <br> Drg No : PSM-196-PO-208</td>
		<td style="text-align:center">67865</td>
		<td style="text-align:center">34.68%</td>
		<td style="text-align:center">4</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px dashed #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">UM12313 <br> CASTING -  C I Housing<br> Drg No : PSM-34-12-208</td>
		<td style="text-align:center">23897</td>
		<td style="text-align:center">44.68%</td>
		<td style="text-align:center">3</td>
		</tr>';
		$j++;
	}
			// }else{
			// 	$str .='<tr>
			// 	<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			// 	</tr>';
			// }
	$str .='				 
	</table>';
	echo $str;
}
//category and group mode
else if(strtolower($POST['mode']) == "catgroupwise1")
{
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%" class="display">
	</table>
	<table  class="" width="100%" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ Stock Statement ]</strong>
	</td>
	</tr>';
	$i=1;
	$total=0;
	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="5%" style="text-align:center">Item Category</th>
	<th width="20%" style="text-align:center">Item Group</th>
	<th width="20%" style="text-align:center">Closing Stock</th>
	<th width="20%" style="text-align:center">Stock Value</th>
	</tr>';

	$j=1;
	$cat_array=['Raw Material','Sub Assembly','Edge Banner','Panel Saw','Cold Press','CNC Router'];
	for ($x = 1; $x <= 5; $x++)  {
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"><strong>'.$cat_array[$x].'</strong></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		</tr>';

		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center">AI Steel</td>
		<td style="text-align:center">67865</td>
		<td style="text-align:center">3468</td>
		</tr>';

		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center">C I CASTING</td>
		<td style="text-align:center">23897</td>
		<td style="text-align:center">4468</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center">Hardware</td>
		<td style="text-align:center">2387</td>
		<td style="text-align:center">448</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center">M S Steel</td>
		<td style="text-align:center">2390</td>
		<td style="text-align:center">48</td>
		</tr>';

		$str.='<tr style="border: 1px solid  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center">Category Total :</td>
		<td style="text-align:center">48000</td>
		</tr>';
	}
	// }else{
			// 	$str .='<tr>
			// 	<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			// 	</tr>';
			// }
	$str .='				 
	</table>';
	echo $str;
}
else if(strtolower($POST['mode']) == "stckanalysissummary")
	{
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$cust_id=$POST['cust_id'];
		$product_id=$POST['product_id'];

		$pr_row=get_product_detail($dbcon,$product_id);

		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
		$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));	

		$vendor="select * from tbl_ledger where l_id=".$POST['vendor_id'];
		$vendor_data=mysqli_fetch_assoc($dbcon->query($vendor));	


		$str .='
		<table  width="100%"   class="display">
		</table>
		<table  class="display table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>'.$set_head['company_name'].'</strong>
		</td>
		</tr>

		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7">
		<strong>[ Stock Analysis Summary ]</strong>
		</td>
		</tr>';
		
		$product_type=['FINISH PRODUCT','ASSEMBLY PRODUCT','SUB ASSEMBLY','RAW MATERIAL','FINISH PART','BOI','CAPITAL GOODS','CONSUMABLE','Service'];
		if(count($POST['pr_type'])>0){

			foreach ($POST['pr_type'] as $key => $value) { 

				$allcat=getallcategoriesdata($dbcon);
					if(count($POST['pr_cat'])>0){
						foreach ($POST['pr_cat'] as $key_cat => $value_cat) { 
							$value1=getcategoriesbyid($dbcon,$value_cat);
					$str .='<tr></tr>
					<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Category : </strong>
					</td>
					<td colspan="2" style="border-right:hidden">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="3">
					</td>
					</tr>';	 
					
					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">No</th>
					<th width="20%" style="text-align:center">Item Description</th>
					<th width="12%" style="text-align:center">Opp Bal <br> UOM</th>
					<th width="20%" style="text-align:center">Receipt Qty</th>
					<th width="20%" style="text-align:center">Issue Qty</th>
					<th width="20%" style="text-align:center">Closing Bal <br> avg rate</th>
					<th width="20%" style="text-align:center">Value of closing balance</th>
					</tr>';
					$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
						(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date<='".$frmdate."'  group by qc.product_id) as base_stock_add, 
						(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   and qc.stock_date<='".$frmdate."'  group by qc.product_id) as base_stock_minus, 
						(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='".$frmdate."'  group by qc.product_id) as con_stock_add, 
						(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='".$frmdate."'  group by qc.product_id) as con_stock_minus 
						FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					if($POST['item_id']>0){
						$query.=" and pro.product_id=".$POST['item_id'];
					}
					$query.=" ORDER BY pro.product_name ";
					$result1=$dbcon->query($query);
					$j=1;
					if(mysqli_num_rows($result1)>0)
					{
						$tot_op_stc=0;
						$tot_cl_stc=0;
						$tot_val=0;
						while($re=mysqli_fetch_assoc($result1))
						{
						 if($POST['stock_value']==2){
							$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
						}else{
							$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
						}
						 $laststock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
						 $firststock=startingstock($dbcon,$re["product_id"],$frmdate);
						 $tot_op_stc=$tot_op_stc+$firststock;
						 $tot_val=$tot_val+($laststock*$rate);
						 $tot_cl_stc=$tot_cl_stc+$laststock;						 $
							$j++;
						}
						$str.='<tr style="border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$j.'</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_op_stc.'</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">'.$tot_cl_stc.'</td>
								<td style="text-align:center">'.$tot_val.'</td>
								</tr>';
					}else{
						$tot_op_stc=0;
						$tot_cl_stc=0;
						$tot_val=0;
						$str.='<tr style="border: 1px dashed #cccccc;">
								<td style="text-align:center">-</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_op_stc.'</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">0</td>

								<td style="text-align:center">'.$tot_cl_stc.'</td>
								<td style="text-align:center">'.$tot_val.'</td>
								</tr>';
					}
				}
					}else{
						
					$str .='<tr></tr>
					<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Category : </strong>
					</td>
					<td colspan="2" style="border-right:hidden">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="3">
					</td>
					</tr>';	 
					
					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">No</th>
					<th width="20%" style="text-align:center">Item Description</th>
					<th width="12%" style="text-align:center">Opp Bal <br> UOM</th>
					<th width="20%" style="text-align:center">Receipt Qty</th>
					<th width="20%" style="text-align:center">Issue Qty</th>
					<th width="20%" style="text-align:center">Closing Bal <br> avg rate</th>
					<th width="20%" style="text-align:center">Value of closing balance</th>
					</tr>
					';
					foreach ($allcat as $key1 => $value1) {
					$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
						(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_add, 
						(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_minus, 
						(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."'    group by qc.product_id) as con_stock_add, 
						(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_minus 
						FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					if($POST['item_id']>0){
						$query.=" and pro.product_id=".$POST['item_id'];
					}
					
					 $query.=" ORDER BY pro.product_name ";
					$result1=$dbcon->query($query);
					$j=1;
					if(mysqli_num_rows($result1)>0)
					{
						$tot_op_stc=0;
						$tot_cl_stc=0;
						$tot_val=0;
						while($re=mysqli_fetch_assoc($result1))
						{
						if($POST['stock_value']==2){
							$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
						}else{
							$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
						}
						$laststock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
						 $firststock=startingstock($dbcon,$re["product_id"],$frmdate);
						 $tot_op_stc=$tot_op_stc+$firststock;
						 $tot_val=$tot_val+($laststock*$rate);
						 $tot_cl_stc=$tot_cl_stc+$laststock;			
						//$j++;
						}
						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">-</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_op_stc.'</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">'.$tot_cl_stc.'</td>
								<td style="text-align:center">'.$tot_val.'</td>
								</tr>';
					}else{
						$tot_op_stc=0;
						$tot_cl_stc=0;
						$tot_val=0;
						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">-</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_op_stc.'</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">0</td>

								<td style="text-align:center">'.$tot_cl_stc.'</td>
								<td style="text-align:center">'.$tot_val.'</td>
								</tr>';
					}
				}
					}
	


			} 

			
		}

		
		$str .='				 
		</table>';
		echo $str;
	}
else if(strtolower($POST['mode']) == "stckanalysisdetail")
	{
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$cust_id=$POST['cust_id'];
		$product_id=$POST['product_id'];

		$pr_row=get_product_detail($dbcon,$product_id);

		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
		$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));	

		$vendor="select * from tbl_ledger where l_id=".$POST['vendor_id'];
		$vendor_data=mysqli_fetch_assoc($dbcon->query($vendor));	


		$str .='
		<table  width="100%"   class="display">
		</table>
		<table  class="display table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>'.$set_head['company_name'].'</strong>
		</td>
		</tr>
		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7">
		<strong>[ Stock Analysis ]</strong>
		</td>
		</tr>';
		
		$product_type=['FINISH PRODUCT','ASSEMBLY PRODUCT','SUB ASSEMBLY','RAW MATERIAL','FINISH PART','BOI','CAPITAL GOODS','CONSUMABLE','Service'];
		if(count($POST['pr_type'])>0){

			foreach ($POST['pr_type'] as $key => $value) { 

				$allcat=getallcategoriesdata($dbcon);
					if(count($POST['pr_cat'])>0){
						foreach ($POST['pr_cat'] as $key_cat => $value_cat) { 
							$value1=getcategoriesbyid($dbcon,$value_cat);
							$str .='<tr></tr>
							<tr style="">
							<td>
							</td>
							<td colspan="">
							<strong>Category : </strong>
							</td>
							<td colspan="2" style="border-right:hidden">
							<strong>'.$product_type[$value].'</strong>
							</td>
							<td colspan="3">
							</td>
							</tr>';	 
							$str.='<tr style="">
							<td>
							</td>
							<td colspan="">
							<strong>Group : </strong>
							</td>
							<td colspan="2" style="border-right:hidden">
							<strong>'.$value1['cat_name'].'</strong>
							</td>
							<td colspan="3">
							</td>
							</tr>';

					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">No</th>
					<th width="20%" style="text-align:center">Item Description</th>
					<th width="12%" style="text-align:center">Opp Bal <br> UOM</th>
					<th width="20%" style="text-align:center">Receipt Qty</th>
					<th width="20%" style="text-align:center">Issue Qty</th>
					<th width="20%" style="text-align:center">Closing Bal <br> avg rate</th>
					<th width="20%" style="text-align:center">Value of closing balance</th>
					</tr>
					';
					$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
	(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date<='".$frmdate."'  group by qc.product_id) as base_stock_add, 
	(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   and qc.stock_date<='".$frmdate."'  group by qc.product_id) as base_stock_minus, 
	(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='".$frmdate."'  group by qc.product_id) as con_stock_add, 
	(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date<='".$frmdate."'  group by qc.product_id) as con_stock_minus 
	FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					if($POST['item_id']>0){
						$query.=" and pro.product_id=".$POST['item_id'];
					}
					$query.=" ORDER BY pro.product_name ";
					$result1=$dbcon->query($query);
					$j=1;
					if(mysqli_num_rows($result1)>0)
					{
						$tot_op_stc=0;
						$tot_cl_stc=0;
						$tot_val=0;
						while($re=mysqli_fetch_assoc($result1))
						{
						 if($POST['stock_value']==2){
							$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
						}else{
							$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
						}
						 $laststock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
						 $firststock=startingstock($dbcon,$re["product_id"],$frmdate);
						 $tot_op_stc=$tot_op_stc+$firststock;
						  $tot_val=$tot_val+($laststock*$rate);
						 $tot_cl_stc=$tot_cl_stc+$laststock;						 $str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$j.'</td>
								<td style="text-align:center">'.$re["product_name"].'</td>

								<td style="text-align:center">'.$firststock.'<br>'.$re["unit_name"].'</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">'.$rate.'</td>
								<td style="text-align:center">'.$laststock.'<br>'.$rate.'</td>
								<td style="text-align:center">'.$laststock*$rate.'</td>
								</tr>';
							$j++;
						}
						$str.='<tr style="border: 1px solid  #cccccc;">
		<td style="text-align:center"></td>
		
		<td style="text-align:center">Group Total :</td>
		<td style="text-align:center">'.$tot_op_stc.'</td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center">'.$tot_cl_stc.'</td>
		<td style="text-align:center">'.$tot_val.'</td>
		</tr>';
					}else{
						$str .='<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
					}
				}
					}else{
						foreach ($allcat as $key1 => $value1) {
					$str .='<tr></tr>
					<tr style="border-top: solid white 30px;">
					<td>
					</td>
					<td colspan="">
					<strong>Category : </strong>
					</td>
					<td colspan="2" style="border-right:hidden">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="3">
					</td>
					</tr>';	 
					$str.='<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Group : </strong>
					</td>
					<td colspan="2" style="border-right:hidden">
					<strong>'.$value1['cat_name'].'</strong>
					</td>
					<td colspan="3">
					</td>
					</tr>';

					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">No</th>
					<th width="20%" style="text-align:center">Item Description</th>
					<th width="12%" style="text-align:center">Opp Bal <br> UOM</th>
					<th width="20%" style="text-align:center">Receipt Qty</th>
					<th width="20%" style="text-align:center">Issue Qty</th>
					<th width="20%" style="text-align:center">Closing Bal <br> avg rate</th>
					<th width="20%" style="text-align:center">Value of closing balance</th>
					</tr>
					';
					$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_add, 
(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_minus, 
(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."'    group by qc.product_id) as con_stock_add, 
(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_minus 
FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					if($POST['item_id']>0){
						$query.=" and pro.product_id=".$POST['item_id'];
					}
					if($POST['po_date_type']){
						if($POST['po_date_type']=='po' || $POST['po_date_type']=='PO'){

							if($POST['fromdate']!=''){
								$query.=" and tp.purchaseorder_date>='".date('Y-m-d',strtotime($POST['fromdate']))."'";
							}

							if($POST['todate']!=''){
								$query.=" and tp.purchaseorder_date<='".date('Y-m-d',strtotime($POST['todate']))."'";
							}

						}
					}
					 $query.=" ORDER BY pro.product_name ";
					$result1=$dbcon->query($query);
					$j=1;
					if(mysqli_num_rows($result1)>0)
					{
						$tot_op_stc=0;
						$tot_cl_stc=0;
						$tot_val=0;
						while($re=mysqli_fetch_assoc($result1))
						{
						 if($POST['stock_value']==2){
							$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
						}else{
							$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
						}
						$laststock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
						 $firststock=startingstock($dbcon,$re["product_id"],$frmdate);
						  $tot_op_stc=$tot_op_stc+$firststock;
						  $tot_val=$tot_val+($laststock*$rate);
						 $tot_cl_stc=$tot_cl_stc+$laststock;			
						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$j.'</td>
								<td style="text-align:center">'.$re["product_name"].'</td>

								<td style="text-align:center">'.$firststock.'<br>'.$re["unit_name"].'</td>
								<td style="text-align:center">0</td>
								<td style="text-align:center">'.$rate.'</td>
								<td style="text-align:center">'.$laststock.'<br>'.$rate.'</td>
								<td style="text-align:center">'.$laststock*$rate.'</td>
								</tr>';
							$j++;
						}
						$str.='<tr style="border: 1px solid  #cccccc;">
		<td style="text-align:center"></td>
		
		<td style="text-align:center">Group Total :</td>
		<td style="text-align:center">'.$tot_op_stc.'</td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>

		<td style="text-align:center">'.$tot_cl_stc.'</td>
		<td style="text-align:center">'.$tot_val.'</td>
		</tr>';
					}else{
						$str .='<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
					}
				}
					}
	


			} 

			
		}

		
		$str .='				 
		</table>';
		echo $str;
	}

else if(strtolower($POST['mode']) == "itemwisesummary")
	{

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$cust_id=$POST['cust_id'];
		$product_id=$POST['product_id'];

		$pr_row=get_product_detail($dbcon,$product_id);

		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
		$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));	

		$vendor="select * from tbl_ledger where l_id=".$POST['vendor_id'];
		$vendor_data=mysqli_fetch_assoc($dbcon->query($vendor));	
		
		$str ='
		<table  width="100%"  class="display">
		</table>
		<table  class="display table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>'.$set_head['company_name'].'</strong>
		</td>
		</tr>

		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7">
		<strong>[ Item Wise Summary Report ]</strong>
		</td>
		</tr>';
		
		$product_type=['FINISH PRODUCT','ASSEMBLY PRODUCT','SUB ASSEMBLY','RAW MATERIAL','FINISH PART','BOI','CAPITAL GOODS','CONSUMABLE','Service'];
		$allcat=getallcategoriesdata($dbcon);
		if(count($POST['pr_type'])>0){

			foreach ($POST['pr_type'] as $key => $value) { 
					if(count($POST['pr_cat'])>0){
						foreach ($POST['pr_cat'] as $key_cat => $value_cat) { 
							$value1=getcategoriesbyid($dbcon,$value_cat);
					$str .='<tr></tr>
					<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Category : </strong>
					</td>
					<td colspan="2" style="border-right:hidden">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="3">
					</td>
					</tr>';	 
					$str.='<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Group : </strong>
					</td>
					<td colspan="2" style="border-right:hidden">
					<strong>'.$value1['cat_name'].'</strong>
					</td>
					<td colspan="3">
					</td>
					</tr>';

					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center;padding:10px 5px">Item Code</th>
					<th width="20%" style="text-align:center">Item Description</th>
					<th width="12%" style="text-align:center">UOM</th>
					<th width="20%" style="text-align:center">Current Stock</th>
					<th width="20%" style="text-align:center">Rate</th>
					<th width="20%" style="text-align:center">Stock Value</th></tr>
					';
					$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
					(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_add, 
					(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_minus, 
					(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_add, 
					(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_minus 
					FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					if($POST['item_id']>0){
						$query.=" and pro.product_id=".$POST['item_id'];
					}
					
				 	 $query.=" ORDER BY pro.product_name ";
					$result1=$dbcon->query($query);
					$j=1;
					if(mysqli_num_rows($result1)>0)
					{
						$tot_stock=0;
						$tot_rate=0;
						$tot_amt=0;
						while($re=mysqli_fetch_assoc($result1))
						{
							if($POST['stock_value']==2){
								 $rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
							}else{

								 $rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
							}
						   
							$stock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
							$tot_stock=$tot_stock+$stock;
							$tot_rate=$tot_rate+$rate;
							$tot_amt=$tot_amt+($rate*$stock);
						    $str.='<tr style="border:1px dashed #cccccc;">
								<td style="text-align:center">'.$re["product_icode"].'</td>
								<td style="text-align:center">'.$re["product_name"].'</td>
								<td style="text-align:center">'.$re["unit_name"].'</td>
								<td style="text-align:center">'.$stock.'</td>
								<td style="text-align:center">'.$rate.'</td>
								<td style="text-align:center">'.$rate*$stock.'</td>
								</tr>';
							$j++;
						}
						$str.='<tr style="border: 1px solid  #cccccc;">
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center">Group Total :</td>
								<td style="text-align:center">'.$tot_stock.'</td>
								<td style="text-align:center"></td>
								<td style="text-align:center">'.$tot_amt.'</td>
								</tr>';
					}else{
						$str .='<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
					}
				}
					}else{
						foreach ($POST['pr_type'] as $key => $value) { 
						foreach ($allcat as $key1 => $value1) {

					$str .='<tr></tr>
					<tr style="border-top: solid white 30px;">
					<td>
					</td>
					<td colspan="">
					<strong>Category : </strong>
					</td>
					<td colspan="">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="4">
					</td>
					</tr>';	 
					$str.='<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Group : </strong>
					</td>
					<td colspan="">
					<strong>'.$value1['cat_name'].'</strong>
					</td>
					<td colspan="4">
					</td>
					</tr>';

					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">Item Code</th>
					<th width="20%" style="text-align:center">Item Description</th>
					<th width="12%" style="text-align:center">UOM</th>
					<th width="20%" style="text-align:center">Current Stock</th>
					<th width="20%" style="text-align:center">Rate</th>
					<th width="20%" style="text-align:center">Stock Value</th></tr>
					';
					$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
					(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_add, 
					(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_minus, 
					(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."'    group by qc.product_id) as con_stock_add, 
					(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_minus 
					FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					//$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					if($POST['item_id']>0){
						$query.=" and pro.product_id=".$POST['item_id'];
					}
					$query.=" ORDER BY pro.product_name ";
					
					//echo $query;
					$result1=$dbcon->query($query);
					$j=1;
					if(mysqli_num_rows($result1)>0)
					{	
						$tot_stock=0;
						$tot_rate=0;
						$tot_amt=0;
						while($re=mysqli_fetch_assoc($result1))
						{
						if($POST['stock_value']==2){
							$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
						}else{
							$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
						}
						$stock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
							$tot_stock=$tot_stock+$stock;
							$tot_rate=$tot_rate+$rate;
							$tot_amt=$tot_amt+($rate*$stock);
							
								$str.='<tr style="border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$re["product_icode"].'</td>
								<td style="text-align:center">'.$re["product_name"].'</td>
								<td style="text-align:center">'.$re["unit_name"].'</td>
								<td style="text-align:center">'.$stock.'</td>
								<td style="text-align:center">'.$rate.'</td>
								<td style="text-align:center">'.$rate*$stock.'</td>
								</tr>';
							$j++;
						}
						$str.='<tr style="border: 1px solid  #cccccc;">
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center">Group Total :</td>
								<td style="text-align:center">'.$tot_stock.'</td>
								<td style="text-align:center"></td>
								<td style="text-align:center">'.$tot_amt.'</td>
								</tr>';		
					}else{

						$str.='<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
					}
				}
			}
					}
	


			} 

			
		}
		

		
		$str .='				 
		</table>';
		echo $str;
	}

	else if(strtolower($POST['mode']) == "catgroupwise")
	{
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$cust_id=$POST['cust_id'];
		$product_id=$POST['item_id'];
		
		$pr_row=get_product_detail($dbcon,$product_id);
		
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
		$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));	

		$vendor="select * from tbl_ledger where l_id=".$POST['vendor_id'];
		$vendor_data=mysqli_fetch_assoc($dbcon->query($vendor));	

		$where = '';
		if(!empty(array_filter($POST['pr_cat'],'strlen'))){
			$pro_cat = implode(",",array_filter($POST['pr_cat'],'strlen'));	
			$where = " and prd.product_category in (".$pro_cat.")";
		}

		$str .='
		<table  width="100%"  class="display">
		</table>
		<table   width="100%"  class="table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>'.$set_head['company_name'].'</strong>
		</td>
		</tr>

		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7">
		<strong>[ Category Group Wise Report ]</strong>
		</td>
		</tr>';

		$frmdate=date('Y-m-d',strtotime($_SESSION['start']));
		$todate=date('Y-m-d',strtotime($_SESSION['end']));

		if($POST['stock_value'] == 0){
			$brate = "left join (select min(base_rate) as brate,pr.product_category as pcat from tbl_stock_trn as mr  
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1  and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' group by pr.product_category) as pr11 on pr11.pcat = prd.product_category";

			$crate = "left join (select min(conv_rate) as crate,pr.product_category as pcat from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' group by pr.product_category) as pr12 on pr12.pcat = prd.product_category";
		}else if($POST['stock_value'] == 1){
			$brate = "left join (select max(base_rate) as brate, pr.product_category as pcat from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1  and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' group by pr.product_category) as pr11 on pr11.pcat=prd.product_category";
			
			$crate = "left join (select max(conv_rate) as crate, pr.product_category as pcat from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' group by pr.product_category) as pr12 on pr12.pcat = prd.product_category";
		}else if($POST['stock_value'] == 2){
			$brate = "left join (select avg(base_rate) as brate, pr.product_category as pcat from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1  and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' group by pr.product_category ) as pr11 on pr11.pcat = prd.product_category";
			$crate = "left join (select avg(conv_rate) as crate,pr.product_category as pcat from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' group by pr.product_category) as pr12 on pr12.pcat = prd.product_category";
		}else if($POST['stock_value'] == 3){
			$brate = "left join (select base_rate as brate,pr.product_category as pcat  from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id 
			where mr.stock_status!=2  and mr.stock_flage=1  and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' order by mr.stock_id desc limit 1) as pr11 on pr11.pcat=prd.product_category ";
			$crate = "left join (select conv_rate as crate, pr.product_category as pcat from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' order by mcr.stock_id desc limit 1 ) as pr12 on pr12.pcat=prd.product_category";
		}else{
			$brate = "left join (select sum(mr.base_rate*mr.base_stock) as brate,pr.product_type as pcat from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status=0  and mr.stock_flage=1 group by pr.product_category) as pr11 on pr11.pcat=prd.product_category";
			$crate = "left join (select sum(mcr.base_rate*mcr.base_stock) as crate, pr.product_category as pcat from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2 and mcr.stock_flage=2 group by pr.product_category) as pr12 on pr12.pcat=prd.product_category"; 
		}

		$query = "select brate, crate, plus_opening_stock, plus_conv_opening_stock, minus_opening_stock, minus_conv_opening_stock, closing_stock_plus, conv_closing_stock_plus, closing_stock_minus, conv_closing_stock_minus, prd.product_category, cat.cat_name

	 from tbl_stock_trn as stock 
		left join product_mst as prd on prd.product_id = stock.product_id
		left join tbl_category as cat on cat.cat_id = prd.product_category

		".$brate." ".$crate."
		left join (select sum(base_stock) as plus_opening_stock, pr.product_category as pcat from tbl_stock_trn as st
	left join product_mst as pr on pr.product_id=st.product_id 
	where st.stock_status!=2 and st.stock_flage=1 and st.stock_date<'".$frmdate."'  group by pr.product_category) as pr on pr.pcat=prd.product_category

		left join (select sum(stc.convert_stock) as plus_conv_opening_stock, pr.product_category as pcat from tbl_stock_trn as stc 
	left join product_mst as pr on pr.product_id=stc.product_id 
	where stc.stock_status!=2 and stc.stock_flage=1 and stc.stock_date<'".$frmdate."'  group by pr.product_category ) as pr1 on pr1.pcat=prd.product_category
		
		left join (select sum(base_stock) as minus_opening_stock, pr.product_category as pcat from tbl_stock_trn as st1 
	left join product_mst as pr on pr.product_id=st1.product_id
	where st1.stock_status!=2 and st1.stock_flage=2 and st1.stock_date<'".$frmdate."'  group by pr.product_category ) as pr2 on pr2.pcat=prd.product_category

		left join (select sum(stc1.convert_stock) as minus_conv_opening_stock, pr.product_category as pcat from tbl_stock_trn as stc1 
	left join product_mst as pr on pr.product_id=stc1.product_id
	where stc1.stock_status!=2 and stc1.stock_flage=2 and stc1.stock_date<'".$frmdate."'  group by pr.product_category ) as pr3 on pr3.pcat=prd.product_category

		
		left join (select sum(base_stock) as closing_stock_plus, pr.product_category as pcat from tbl_stock_trn as st3 
	left join product_mst as pr on pr.product_id=st3.product_id
	where st3.stock_status!=2  and st3.stock_date<='".$todate."' and st3.stock_flage=1 group by pr.product_category) as pr6 on pr6.pcat=prd.product_category

		left join (select sum(stc3.convert_stock) as conv_closing_stock_plus, pr.product_category as pcat from tbl_stock_trn as stc3 
	left join product_mst as pr on pr.product_id=stc3.product_id
	where stc3.stock_status!=2 and stc3.stock_date<='".$todate."' and stc3.stock_flage=1 group by pr.product_category) as pr7 on pr7.pcat=prd.product_category

	left join (select sum(base_stock) as closing_stock_minus, pr.product_category as pcat from tbl_stock_trn as st4 
	left join product_mst as pr on pr.product_id=st4.product_id
	where st4.stock_status!=2  and st4.stock_date<='".$todate."' and st4.stock_flage=2 group by pr.product_category) as pr8 on pr8.pcat=prd.product_category

	left join (select sum(stc4.convert_stock) as conv_closing_stock_minus, pr.product_category as pcat from tbl_stock_trn as stc4 
	left join product_mst as pr on pr.product_id=stc4.product_id
	where stc4.stock_status!=2 and stc4.stock_date<='".$todate."' and stc4.stock_flage=2 group by pr.product_category) as pr9 on pr9.pcat = prd.product_category


		where stock.stock_status!=2 ".$where." and stock.company_id=".$_SESSION['company_id']."  Group by cat.cat_id";
		//echo $query;
		$result = $dbcon->query($query);

		$str .= '<tr>
			<th style="padding:10px 5px;text-align:center">Sr No.</th>
			<th style="text-align:center">Category Name</th>
			<th style="text-align:center">Opening Stock</th>
			<th style="text-align:center">Closing Stock</th>
			<th style="text-align:center">Stock Value</th>
		</tr>';
		if(brp_mysqli_num_rows($result)>0){
			$i = 1;
			while($row = brp_mysqli_fetch_array($result)){

				$base_opening_stock = $row['plus_opening_stock'] - $row['minus_opening_stock'];
				$conv_opening_stock = $row['plus_conv_opening_stock'] - $row['minus_conv_opening_stock']; 
				$base_closing_stock =  $row['closing_stock_plus'] - $row['closing_stock_minus'];
				$conv_closing_stock =  $row['conv_closing_stock_plus'] - $row['conv_closing_stock_minus'];

				if($POST['stock_value']!=4){
					$base_stock_value   = ($row['brate']*$base_closing_stock);
				}else{
					$base_stock_value	= $row['brate']-$row['crate']; 
				}
			 
			
				$b_opening = '<strong style="color:green">'.$base_opening_stock.'</strong><br><strong style="color:orange">'.$conv_opening_stock.'</strong>';
				$b_clstock = '<strong style="color:green">'.$base_closing_stock.'</strong><br><strong style="color:orange">'.$conv_closing_stock.'</strong>';
				
				if($row['product_category']==0){
					$row['cat_name'] = 'PRIMARY';
				}

				$str .= '<tr>
					<td style="text-align:center">'.$i.'</td>
					<td style="text-align:center">'.$row['cat_name'].'</td>
					<td style="text-align:center">'.$b_opening.'</td>
					<td style="text-align:center">'.$b_clstock.'</td>
					<td style="text-align:center"><strong>'.number_format($base_stock_value,2).'</strong></td>
				</tr>';

				$total_value = $total_value + $base_stock_value;
				$i++;
			}
			$str .= "<tr>
				<td colspan='4' style='text-align:right'><strong>Total</strong></td>
				<td style='text-align:center'><strong>".number_format($total_value,2)."</strong></td>
			</tr>";	
		}else{
			$str .= '<tr>
				<td colspan="5" style="text-align:center">No Data Yet..!!</td>
			</tr>';
		}
		

		/*$product_type=['FINISH PRODUCT','ASSEMBLY PRODUCT','SUB ASSEMBLY','RAW MATERIAL','FINISH PART','BOI','CAPITAL GOODS','CONSUMABLE','Service'];*/

		/*if(count($POST['pr_type'])>0){

			foreach ($POST['pr_type'] as $key => $value) { 

				$allcat=getallcategoriesdata($dbcon);

					if(count($POST['pr_cat'])>0){
						foreach ($POST['pr_cat'] as $key_cat => $value_cat) { 
							$value1=getcategoriesbyid($dbcon,$value_cat);
							if(empty($value1['cat_id'])){
								$value1['cat_id'] = 0;
								$value1['cat_name'] = 'PRIMARY';
							}
					$str .='<tr></tr>
					<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Item Type : </strong>
					</td>
					<td colspan="">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="4">
					</td>
					</tr>';	 
					

					$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center;padding:10px 5px">Item Type</th>
					<th width="20%" style="text-align:center">Item Category</th>
					<th width="20%" style="text-align:center">Closing Stock</th>
					<th width="20%" style="text-align:center">Stock Value</th></tr>
					';
					$frmdate=date('Y-m-d',strtotime($_SESSION['start']));
					$todate=date('Y-m-d',strtotime($_SESSION['end']));
					
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
					(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_add, 
					(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1   and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_minus, 
					(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_add, 
					(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_minus 
					FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					
					$query.=" ORDER BY pro.product_name ";
					$result1=$dbcon->query($query);
					$j=1;
					if(brp_mysqli_num_rows($result1)>0)
					{
						$tot_stock=0;
						$tot_rate=0;
						$tot_amt=0;
						while($re=brp_mysqli_fetch_assoc($result1))
						{
						   	if($POST['stock_value']==2){
								$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
							}else{
								$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
							}

							/*var_dump($rate);*/

							/*$stock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
							$tot_stock=$tot_stock+$stock;
							$tot_rate=$tot_rate+$rate;
							$tot_amt=$tot_amt+($rate*$stock);
						    
							$j++;
						}

						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$product_type[$value].'</td>
								<td style="text-align:center">'.$value1['cat_name'].'';

						$str.='<td style="text-align:center">'.$tot_stock.'</td>
								<td style="text-align:center">'.$tot_amt.'</td>
								</tr>';
								
					}else{
						$tot_stock=0;
						$tot_rate=0;
						$tot_amt=0;
						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$product_type[$value].'</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_stock.'</td>
								<td style="text-align:center">'.$tot_rate.'</td>
								</tr>';
					}
				}
					}else{

						foreach ($POST['pr_type'] as $key => $value) { 
							$str .='<tr></tr>
					<tr style="">
					<td>
					</td>
					<td colspan="">
					<strong>Category : </strong>
					</td>
					<td colspan="">
					<strong>'.$product_type[$value].'</strong>
					</td>
					<td colspan="4">
					</td>
					</tr>';	 
							$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center;padding:10px 5px">Item Category</th>
					<th width="20%" style="text-align:center">Item Group</th>
					<th width="20%" style="text-align:center">Closing Stock</th>
					<th width="20%" style="text-align:center">Stock Value</th></tr>
					';
						foreach ($allcat as $key1 => $value1) {
					*/
					// $str.='<tr style="">
					// <td>
					// </td>
					// <td colspan="">
					// <strong>Group : </strong>
					// </td>
					// <td colspan="">
					// <strong>'.$value1['cat_name'].'</strong>
					// </td>
					// <td colspan="4">
					// </td>
					// </tr>';

					
					/*$frmdate=date('Y-m-d',strtotime($POST['from_date']));
					$todate=date('Y-m-d',strtotime($POST['to_date']));
					$query="SELECT  pro.product_id, pro.product_base_unit, un.unit_name, pro.product_name, pro.product_status,pro.product_icode, 
(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_add, 
(select sum(qc.base_stock) as base_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=2 and qc.base_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as base_stock_minus, 
(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1  and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."'    group by qc.product_id) as con_stock_add, 
(select sum(qc.convert_stock) as con_stock_minus from tbl_stock_trn as qc where qc.stock_status!=2 and stock_flage=1 and qc.base_unit!=qc.convert_unit and qc.convert_unit=pro.product_base_unit and qc.product_id=pro.product_id and qc.company_id=1 and qc.stock_date>='".$frmdate."' and qc.stock_date<='".$todate."' group by qc.product_id) as con_stock_minus 
FROM product_mst as pro left join unit_mst as un on un.unitid=pro.product_base_unit where ( 1 AND pro.product_status !=2 )";
					$query.=" and pro.product_type=".$value;
					$query.=" and pro.product_category=".$value1['cat_id'];
					
					$query.=" ORDER BY pro.product_name ";

					$result1=$dbcon->query($query);
					$j=1;
					if(brp_mysqli_num_rows($result1)>0)
					{
						$tot_stock=0;
						$tot_rate=0;
						$tot_amt=0;
						while($re=brp_mysqli_fetch_assoc($result1))
						{
							if($POST['stock_value']==2){
								$rate=(getavgprorate($dbcon,$re["product_id"],$frmdate,$todate) > 0 ) ? getavgprorate($dbcon,$re["product_id"],$frmdate,$todate):0;
							}else{
								$rate=(getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate) > 0 ) ? getprorate($dbcon,$re["product_id"],$POST['stock_value'],$frmdate,$todate):0;
							}
						
							$stock=getstockusingprid($dbcon,$re["product_id"],$frmdate,$todate);
							$tot_stock=$tot_stock+$stock;
							$tot_rate=$tot_rate+$rate;
							$tot_amt=$tot_amt+($rate*$stock);
							
								
								
							$j++;
						}
						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$product_type[$value].'</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_stock.'</td>
								<td style="text-align:center">'.$tot_rate.'</td>
								</tr>';
					}else{
						$tot_stock=0;
						$tot_rate=0;
						$tot_amt=0;
						$str.='<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">'.$product_type[$value].'</td>
								<td style="text-align:center">'.$value1['cat_name'].'';
						$str.='<td style="text-align:center">'.$tot_stock.'</td>
								<td style="text-align:center">'.$tot_rate.'</td>
								</tr>';
					}
				}
			}
					}
	


			} 

			
		}
*/
		
		$str .='				 
		</table>';
		echo $str;
	}
else if(strtolower($POST['mode']) == "itemwisesummary1")
{
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table width="100%" class="display">
	</table>
	<table  class="" width="100%" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ Stock Statement ]</strong>
	</td>
	</tr>';
	$product_type=['FINISH PRODUCT','ASSEMBLY PRODUCT','SUB ASSEMBLY','RAW MATERIAL','FINISH PART','BOI','CAPITAL GOODS','CONSUMABLE','Service'];
	$i=1;
	$total=0;
	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="20%" style="text-align:center">Item Code</th>
	<th width="20%" style="text-align:center">Item Details</th>
	<th width="20%" style="text-align:center">UOM</th>
	<th width="20%" style="text-align:center">Current Stock</th>
	<th width="10%" style="text-align:center">Rate</th>
	<th width="10%" style="text-align:center">Stock Value</th>
	</tr>';

	$j=1;
	$cat_array=['Raw Material','Sub Assembly','Edge Banner','Panel Saw','Cold Press','CNC Router'];
	$subcat_array=['AI Steel','C I CASTING','Hardware','M S Steel'];
	for ($x = 1; $x <= 5; $x++)  {
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"><strong>Item Category :'.$cat_array[$x].'</strong> <br> <strong>Item Group :'.$subcat_array[$x].'</strong></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		</tr>';

		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">-UALMP001</td>
		<td style="text-align:center">Alluminium Casting</td>
		<td style="text-align:center">NOS</td>
		<td style="text-align:center">35</td>
		<td style="text-align:center">20</td>
		<td style="text-align:center">700</td>
		</tr>';

		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">-UALMP002</td>
		<td style="text-align:center">Alluminium Flat Bar</td>
		<td style="text-align:center">NOS</td>
		<td style="text-align:center">25</td>
		<td style="text-align:center">20</td>
		<td style="text-align:center">500</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">-UALMP003</td>
		<td style="text-align:center">Allu Plate </td>
		<td style="text-align:center">MM</td>
		<td style="text-align:center">60</td>
		<td style="text-align:center">20</td>
		<td style="text-align:center">1200</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">-UALMP004</td>
		<td style="text-align:center">Allu Round Plate</td>
		<td style="text-align:center">NOS</td>
		<td style="text-align:center">100</td>
		<td style="text-align:center">20</td>
		<td style="text-align:center">2000</td>
		</tr>';

		$str.='<tr style="border: 1px   #cccccc;">
		<td style="text-align:center">-UALMP005</td>
		<td style="text-align:center">Allu Round Bar</td>
		<td style="text-align:center">MM</td>
		<td style="text-align:center">25</td>
		<td style="text-align:center">20</td>
		<td style="text-align:center">500</td>
		</tr>';
		$str.='<tr style="border: 1px solid  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center">Group Total :</td>
		<td style="text-align:center">245</td>
		<td style="text-align:center"></td>
		<td style="text-align:center">4900</td>
		</tr>';
	}


			// }else{
			// 	$str .='<tr>
			// 	<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			// 	</tr>';
			// }
	$str .='				 
	</table>';
	echo $str;
}

else if(strtolower($POST['mode']) == "stckanalysisdetail")
{
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%" class="display">
	</table>
	<table  class="display table-bordered" width="100%" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ Stock Analysis ]</strong>
	</td>
	</tr>';
	$i=1;
	$total=0;
	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="20%" style="text-align:center">No</th>
	<th width="20%" style="text-align:center">Item Details</th>
	<th width="20%" style="text-align:center">Op.Bal <br> UOM</th>
	<th width="20%" style="text-align:center">Receipt <br> Quantity</th>
	<th width="10%" style="text-align:center">Issue <br> Quantity</th>
	<th width="20%" style="text-align:center">Closing Bal <br> Avg Rate</th>
	<th width="10%" style="text-align:center">Value Of <br> Closing Balance</th>
	</tr>';
	
	$cat_array=['Raw Material','Sub Assembly','Edge Banner','Panel Saw','Cold Press','CNC Router','Raw Material'];
	$subcat_array=['AI Steel','C I CASTING','Hardware','M S Steel','AI Steel','Other'];
	for ($x = 1; $x <= 5; $x++)  {
		$j=1;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"><strong>Item Category :'.$cat_array[$x].'</strong> <br> <strong>Item Group :'.$subcat_array[$x].'</strong></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		</tr>';

		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">-UALMP001 <br> Alluminium Casting <br> Drg No : PSM-OO3-PU</td>
		<td style="text-align:center">42 <br> NOS</td>
		<td style="text-align:center">0.00</td>
		<td style="text-align:center">10.00</td>
		<td style="text-align:center">15 <br> 8.89</td>
		<td style="text-align:center">5900</td>
		</tr>';

		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">-UALMP002 <br> Alluminium Flat Bar <br> Drg No : PSM-OO4-PU</td>
		<td style="text-align:center">412 <br> NOS</td>
		<td style="text-align:center">10.00</td>
		<td style="text-align:center">2.00</td>
		<td style="text-align:center">159 <br> 18.89</td>
		<td style="text-align:center">4100</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">-UALMP003 <br> Allu Plate <br> Drg No : PSM-OO5-PU </td>
		<td style="text-align:center">92 <br> MM</td>
		<td style="text-align:center">0.00</td>
		<td style="text-align:center">2.00</td>
		<td style="text-align:center">115<br> 48.89</td>
		<td style="text-align:center">10000</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">-UALMP004 <br> Allu Round Plate <br> Drg No : PSM-OO6-PU </td>
		<td style="text-align:center">672 <br> NOS</td>
		<td style="text-align:center">100.00</td>
		<td style="text-align:center">20.00</td>
		<td style="text-align:center">1599 <br> 68.89</td>
		<td style="text-align:center">5000</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px   #cccccc;">
		<td style="text-align:center">'.$j.'</td>
		<td style="text-align:center">-UALMP005 <br> Allu Round Bar <br> Drg No : PSM-OO7-PU</td>
		<td style="text-align:center">562 <br> MM</td>
		<td style="text-align:center">25.00</td>
		<td style="text-align:center">30.00</td>
		<td style="text-align:center">1895 <br> 88.89</td>
		<td style="text-align:center">5900</td>
		</tr>';
		
		$str.='<tr style="border: 1px solid  #cccccc;">
		<td style="text-align:center"></td>
		<td style="text-align:center">Group Total :</td>
		<td style="text-align:center">5678</td>
		<td style="text-align:center">135</td>
		<td style="text-align:center">64</td>
		<td style="text-align:center">6754</td>
		<td style="text-align:center">30900</td>
		</tr>';
	}


			// }else{
			// 	$str .='<tr>
			// 	<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			// 	</tr>';
			// }
	$str .='				 
	</table>';
	echo $str;
}

else if(strtolower($POST['mode']) == "stckanalysissummary1")
{
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%" class="display">
	</table>
	<table  class="table-bordered" width="100%" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ Stock Analysis ]</strong>
	</td>
	</tr>';
	$i=1;
	$total=0;
	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="20%" style="text-align:center">Item Group</th>
	<th width="20%" style="text-align:center">Op.Bal <br> UOM</th>
	<th width="20%" style="text-align:center">Receipt <br> Quantity</th>
	<th width="10%" style="text-align:center">Issue <br> Quantity</th>
	<th width="20%" style="text-align:center">Closing Bal <br> Avg Rate</th>
	<th width="10%" style="text-align:center">Value Of <br> Closing Balance</th>
	</tr>';

	$j=1;
	$cat_array=['Raw Material','Sub Assembly','Edge Banner','Panel Saw','Cold Press','CNC Router'];
	$subcat_array=['AI Steel','C I CASTING','Hardware','M S Steel'];
	for ($x = 1; $x <= 5; $x++)  {
		$str.='<tr style="border: 1px  #cccccc;">
		<td style="text-align:center"><strong>'.$cat_array[$x].'</strong> </td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		<td style="text-align:center"></td>
		</tr>';

		$str.='<tr style="border: 1px  #cccccc;">

		<td style="text-align:center">-AL Steel</td>
		<td style="text-align:center">42 <br> NOS</td>
		<td style="text-align:center">0.00</td>
		<td style="text-align:center">10.00</td>
		<td style="text-align:center">15 <br> 8.89</td>
		<td style="text-align:center">5900</td>
		</tr>';

		$j++;
		$str.='<tr style="border: 1px  #cccccc;">

		<td style="text-align:center">-CI Casting</td>
		<td style="text-align:center">412 <br> NOS</td>
		<td style="text-align:center">10.00</td>
		<td style="text-align:center">2.00</td>
		<td style="text-align:center">159 <br> 18.89</td>
		<td style="text-align:center">4100</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">

		<td style="text-align:center">-Hardware </td>
		<td style="text-align:center">92 <br> MM</td>
		<td style="text-align:center">0.00</td>
		<td style="text-align:center">2.00</td>
		<td style="text-align:center">115<br> 48.89</td>
		<td style="text-align:center">10000</td>
		</tr>';
		$j++;
		$str.='<tr style="border: 1px  #cccccc;">

		<td style="text-align:center">-MS Steel</td>
		<td style="text-align:center">672 <br> NOS</td>
		<td style="text-align:center">100.00</td>
		<td style="text-align:center">20.00</td>
		<td style="text-align:center">1599 <br> 68.89</td>
		<td style="text-align:center">5000</td>
		</tr>';

		$str.='<tr style="border: 1px #cccccc;">

		<td style="text-align:center">-Other</td>
		<td style="text-align:center">562 <br> MM</td>
		<td style="text-align:center">25.00</td>
		<td style="text-align:center">30.00</td>
		<td style="text-align:center">1895 <br> 88.89</td>
		<td style="text-align:center">5900</td>
		</tr>';
		$str.='<tr style="border: 1px solid  #cccccc;">

		<td style="text-align:right; "><strong>Category Total : </strong></td>
		<td style="text-align:center">5678</td>
		<td style="text-align:center">135</td>
		<td style="text-align:center">64</td>
		<td style="text-align:center">6754</td>

		<td style="text-align:center">30900</td>
		</tr>';
	}


			// }else{
			// 	$str .='<tr>
			// 	<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			// 	</tr>';
			// }
	$str .='				 
	</table>';
	echo $str;
}
else if(strtolower($POST['mode']) == "itemwisedetail")
{
	$cust_id=$POST['cust_id'];

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	
	$product_id=$POST['item_id'];
	$where='';
	if($product_id != ''){
		$where .= ' and strn.product_id='.$product_id;
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=brp_mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table width="100%" class="display">
	</table>
	<table class="display table-bordered" width="100%" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>	
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ Detail Item Ledger ]</strong>
	</td>
	</tr>';
	$i=1;
	$total=0;
	
	$frmdate=date('Y-m-d',strtotime($_SESSION['start']));
	$todate=date('Y-m-d',strtotime($_SESSION['end']));

	if($POST['stock_value'] == 0){
		$brate = "(select min(mr.base_rate) from tbl_stock_trn as mr where  mr.stock_status!=2  and mr.stock_flage=1 and mr.product_id = strn.product_id and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' group by mr.product_id) brate";
		$crate = "(select min(mcr.conv_rate)  from tbl_stock_trn as mcr where mcr.stock_status!=2 and mcr.product_id = strn.product_id and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."'  and mcr.stock_date<='".$todate."' group by mcr.product_id) as crate";
	}else if($POST['stock_value'] == 1){
		$brate = "(select max(mr.base_rate) from tbl_stock_trn as mr where mr.stock_status!=2 and mr.product_id = strn.product_id and mr.stock_flage=1  and mr.stock_date>='".$frmdate."'  and mr.stock_date<='".$todate."' group by mr.product_id) as brate";
		$crate = "(select max(mcr.conv_rate) from tbl_stock_trn as mcr where mcr.stock_status!=2 and mcr.product_id = strn.product_id and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."'  and mcr.stock_date<='".$todate."' group by mcr.product_id) as crate";
	}else if($POST['stock_value'] == 2){
		$brate = "(select avg(mr.base_rate) from tbl_stock_trn as mr where  mr.stock_status!=2 and mr.product_id = strn.product_id and mr.stock_flage=1  and mr.stock_date>='".$frmdate."'  and mr.stock_date<='".$todate."' group by mr.product_id) as brate";
		$crate = "(select avg(mcr.conv_rate)  from tbl_stock_trn as mcr where  mcr.stock_status!=2 and mcr.product_id = strn.product_id and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."'  and mcr.stock_date<='".$todate."' group by mcr.product_id) as crate";
	}else if($POST['stock_value'] == 3){
		$brate = "(select mr.base_rate  from tbl_stock_trn as mr where  mr.stock_status!=2  and mr.stock_flage=1 and mr.product_id = strn.product_id  and mr.stock_date>='".$frmdate."'  and mr.stock_date<='".$todate."' order by mr.stock_id desc limit 1) as brate";
		$crate = "(select mcr.conv_rate from tbl_stock_trn as mcr where  mcr.stock_status!=2 and mcr.product_id = strn.product_id and mcr.stock_flage=1  and mcr.stock_date>='".$frmdate."'  and mcr.stock_date<='".$todate."' order by mcr.stock_id desc limit 1 ) as crate";
	}else{
		$brate = "(select sum(mr.base_rate*mr.base_stock) from tbl_stock_trn as mr where mr.stock_status=0 and mr.product_id = strn.product_id   and mr.stock_flage=1 group by mr.product_id) as brate";
		$crate = "(select sum(mcr.base_rate*mcr.base_stock) from tbl_stock_trn as mcr where mcr.stock_status=0 and mcr.product_id = strn.product_id  and mcr.stock_flage=2 group by mcr.product_id) as crate"; 
	}

	
	 $query = "select ".$brate." ,".$crate.", plus_opening_stock, plus_conv_opening_stock, minus_opening_stock, minus_conv_opening_stock, closing_stock_plus, conv_closing_stock_plus, closing_stock_minus, pro.product_name, pro.product_icode, bunit.unit_name as baseunit, cunit.unit_name as conv_unit, strn.base_unit, strn.convert_unit,strn.product_id from tbl_stock_trn as strn

	

	left join (select sum(st.base_stock) as plus_opening_stock,product_id from tbl_stock_trn as st where st.stock_status!=2  and st.stock_flage=1 and st.stock_date<'".$frmdate."' group by st.product_id) as st on st.product_id=strn.product_id

	left join (select sum(stc.convert_stock) as plus_conv_opening_stock,stc.product_id from tbl_stock_trn as stc where stc.stock_status!=2 and stc.stock_flage=1 and stc.stock_date<'".$frmdate."'  group by stc.product_id ) as stc on stc.product_id=strn.product_id

	left join (select sum(st1.base_stock) as minus_opening_stock,st1.product_id from tbl_stock_trn as st1 where st1.stock_status!=2  and st1.stock_flage=2 and st1.stock_date<'".$frmdate."'  group by st1.product_id ) as st1 on st1.product_id=strn.product_id

	left join (select sum(stc1.convert_stock) as minus_conv_opening_stock,product_id from tbl_stock_trn as stc1 where stc1.stock_status!=2 and stc1.stock_flage=2 and stc1.stock_date<'".$frmdate."' and stc1.ref_name!='opening_stock' group by stc1.product_id) as stc1 on stc1.product_id=strn.product_id



	left join (select sum(base_stock) as closing_stock_plus,st3.product_id from tbl_stock_trn as st3 where st3.stock_status!=2  and st3.stock_date<='".$todate."' and st3.stock_flage=1 group by st3.product_id) as st3 on st3.product_id=strn.product_id

	left join (select sum(stc3.convert_stock) as conv_closing_stock_plus,stc3.product_id from tbl_stock_trn as stc3 where stc3.stock_status!=2  and stc3.stock_date<='".$todate."' and stc3.stock_flage=1 group by stc3.product_id) as stc3 on stc3.product_id=strn.product_id

	left join (select sum(base_stock) as closing_stock_minus,st4.product_id from tbl_stock_trn as st4 where st4.stock_status!=2  and st4.stock_date<='".$todate."' and st4.stock_flage=2 group by st4.product_id) as st4 on st4.product_id=strn.product_id

	left join (select sum(stc4.convert_stock) as conv_closing_stock_minus,stc4.product_id from tbl_stock_trn as stc4 where stc4.stock_status!=2 and stc4.stock_date<='".$todate."' and stc4.stock_flage=2 group by stc4.product_id) as stc4 on  stc4.product_id=strn.product_id

	left join product_mst as pro on pro.product_id = strn.product_id
	left join unit_mst as bunit on bunit.unitid = strn.base_unit
	left join unit_mst as cunit on cunit.unitid = strn.convert_unit
	where strn.stock_status!=2 and strn.company_id=".$_SESSION['company_id']." ".$where." group by strn.product_id";
	
	$result = $dbcon->query($query);

	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
		<th width="5%" style="text-align:center;padding:10px 5px;">No</th>
		<th width="10%" style="text-align:center">Product Name</th>
		<th width="10%" style="text-align:center">Opening Stock</th>
		<th width="15%" style="text-align:center">Closing Stock</th>
		<th width="15%" style="text-align:center">Stock Value</th>
	</tr>';

	$i=1;
	$cnt = brp_mysqli_num_rows($result);
	if($cnt>0){
		while($row=brp_mysqli_fetch_array($result)){
			$base_opening_stock = $row['plus_opening_stock'] - $row['minus_opening_stock'];
			$conv_opening_stock = $row['plus_conv_opening_stock'] - $row['minus_conv_opening_stock']; 
			$base_closing_stock = $row['closing_stock_plus'] - $row['closing_stock_minus'];
			$conv_closing_stock = $row['conv_closing_stock_plus'] - $row['conv_closing_stock_minus'];

			if($POST['stock_value']!=4){		
				$base_stock_value   = $row['brate']*$base_closing_stock; 
			}else{
				$base_stock_value   = $row['brate']-$row['crate'];
			}
			if($row['base_unit'] != $row['convert_unit']){
				$b_opening = '<strong style="color:green">'.$base_opening_stock.' '.$row['baseunit'].'</strong><br><strong style="color:orange">'.$conv_opening_stock.' '.$row['conv_unit'].'</strong>';
				$b_clstock = '<strong style="color:green">'.$base_closing_stock.' '.$row['baseunit'].'</strong><br><strong style="color:orange">'.$conv_closing_stock.' '.$row['conv_unit'].'</strong>';
			}else{
				$b_opening = '<strong style="color:green">'.$base_opening_stock.' '.$row['baseunit'].'</strong>';
				$b_clstock = '<strong style="color:green">'.$base_closing_stock.' '.$row['baseunit'].'</strong>';
			}
			$str.='<tr>
				<td style="text-align:center">'.$i.'</td>
				<td style="text-align:center">'.$row['product_name'].' -- '.$row['product_icode'].'</td>
				<td style="text-align:center">'.$b_opening.'</td>
				<td style="text-align:center">'.$b_clstock.'</td>
				<td style="text-align:center"><strong>'.number_format($base_stock_value,2).'</strong></td>
			</tr>';
			$total_valuation = $total_valuation + $base_stock_value;
			$i++;
		}
		$str .= '<tr>
			<td colspan="4" style="text-align:right"><strong>Total</strong></td>
			<td style="text-align:center"><strong>'.number_format($total_valuation,2).'</strong></td>
		</tr>';
	}else{
		$str .='<tr>
			<td colspan="5" style="text-align:center">No Data Yet...!!</td>
		</tr>';
	}
		
	$str .='				 
	</table>';
	echo $str;
}

else if(strtolower($POST['mode']) == "typewise")
{
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$cust_id=$POST['cust_id'];
		$product_id=$POST['item_id'];
		
		$pr_row=get_product_detail($dbcon,$product_id);
		
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
		$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));	

		$vendor="select * from tbl_ledger where l_id=".$POST['vendor_id'];
		$vendor_data=mysqli_fetch_assoc($dbcon->query($vendor));	

		$where = '';
		if(!empty(array_filter($POST['pr_type'],'strlen'))){
			$pr_type = implode(",",array_filter($POST['pr_type'],'strlen'));	
			$where = " and prd.product_type in (".$pr_type.")";
		}

		$str .='
		<table  width="100%"  class="display">
		</table>
		<table   width="100%"  class="table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>'.$set_head['company_name'].'</strong>
		</td>
		</tr>

		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7">
		<strong>[ Item Type Wise Report ]</strong>
		</td>
		</tr>';

		$frmdate=date('Y-m-d',strtotime($_SESSION['start']));
		$todate=date('Y-m-d',strtotime($_SESSION['end']));

		if($POST['stock_value'] == 0){
			$brate = "left join (select min(base_rate) as brate,pr.product_type from tbl_stock_trn as mr  
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1 and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' ) as pr12 on pr12.product_type=prd.product_type";
			$crate = "left join (select min(conv_rate) as crate, pr.product_type from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1 and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' ) as pr13 on pr13.product_type=prd.product_type";
		}else if($POST['stock_value'] == 1){
			$brate = "left join (select max(base_rate) as brate, pr.product_type from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1 and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' ) as pr12 on pr12.product_type=prd.product_type";
			$crate = "left join (select max(conv_rate) as crate, pr.product_type from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1 and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' ) as pr13 on pr13.product_type=prd.product_type";
		}else if($POST['stock_value'] == 2){
			$brate = "left join (select avg(base_rate) as brate, pr.product_type from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1 and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' ) as pr12 on pr12.product_type=prd.product_type";
			$crate = "left join (select avg(conv_rate) as crate, pr.product_type from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1 and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' ) as pr13 on pr13.product_type=prd.product_type";
		}else if($POST['stock_value'] == 3){
			$brate = "left join (select base_rate as brate,pr.product_type from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id 
			where mr.stock_status!=2  and mr.stock_flage=1 and mr.stock_date>='".$frmdate."' and mr.stock_date<='".$todate."' order by mr.stock_id desc limit 1) as pr12 on  pr12.product_type=prd.product_type";
			
			$crate = "left join (select conv_rate as crate, pr.product_type  from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=1 and mcr.stock_date>='".$frmdate."' and mcr.stock_date<='".$todate."' order by mcr.stock_id desc limit 1 ) as pr13 on pr13.product_type=prd.product_type";
		}else{
			$brate = "left join (select sum(base_rate*base_stock) as brate, pr.product_type from tbl_stock_trn as mr 
			left join product_mst as pr on pr.product_id = mr.product_id
			where mr.stock_status!=2  and mr.stock_flage=1 ) as pr12 on pr12.product_type=prd.product_type";

			$crate = "left join (select sum(base_rate*base_stock) as crate,pr.product_type from tbl_stock_trn as mcr 
			left join product_mst as pr on pr.product_id = mcr.product_id
			where mcr.stock_status!=2  and mcr.stock_flage=2 ) as pr13 on pr13.product_type=prd.product_type"; 
		}

		$query = "select brate, crate,plus_opening_stock, plus_conv_opening_stock, minus_opening_stock, minus_conv_opening_stock, closing_stock_plus, conv_closing_stock_plus, closing_stock_minus, conv_closing_stock_minus, prd.product_type, cat.product_type_name

	 from tbl_stock_trn as stock 
		left join product_mst as prd on prd.product_id = stock.product_id
		left join pro_ms_product_type as cat on cat.product_type_id = prd.product_type

		".$brate." ".$crate."

		left join (select sum(base_stock) as plus_opening_stock,pr.product_type from tbl_stock_trn as st
	left join product_mst as pr on pr.product_id=st.product_id 
	where st.stock_status!=2 and st.stock_flage=1 and st.stock_date<='".$frmdate."') as pr on pr.product_type=prd.product_type

		left join (select sum(stc.convert_stock) as plus_conv_opening_stock,pr.product_type from tbl_stock_trn as stc 
	left join product_mst as pr on pr.product_id=stc.product_id 
	where stc.stock_status!=2 and stc.stock_flage=1 and stc.stock_date<='".$frmdate."') as pr1 on pr1.product_type=prd.product_type

		left join (select sum(base_stock) as minus_opening_stock, pr.product_type from tbl_stock_trn as st1 
	left join product_mst as pr on pr.product_id=st1.product_id
	where st1.stock_status!=2 and st1.stock_flage=2 and st1.stock_date<='".$frmdate."') as pr2 on pr2.product_type=prd.product_type

		left join (select sum(stc1.convert_stock) as minus_conv_opening_stock, pr.product_type from tbl_stock_trn as stc1 
	left join product_mst as pr on pr.product_id=stc1.product_id
	where stc1.stock_status!=2 and stc1.stock_flage=2 and stc1.stock_date<='".$frmdate."') as pr3 on pr3.product_type=prd.product_type

		left join (select sum(base_stock) as closing_stock_plus, pr.product_type from tbl_stock_trn as st3 
	left join product_mst as pr on pr.product_id=st3.product_id
	where st3.stock_status!=2  and st3.stock_date<='".$todate."' and st3.stock_flage=1) as pr6 on pr6.product_type=prd.product_type

		left join (select sum(stc3.convert_stock) as conv_closing_stock_plus, pr.product_type from tbl_stock_trn as stc3 
	left join product_mst as pr on pr.product_id=stc3.product_id
	where stc3.stock_status!=2  and stc3.stock_date<='".$todate."'  and stc3.stock_flage=1) as pr7 on pr7.product_type=prd.product_type

		left join (select sum(base_stock) as closing_stock_minus, pr.product_type from tbl_stock_trn as st4 
	left join product_mst as pr on pr.product_id=st4.product_id
	where st4.stock_status!=2  and st4.stock_date<='".$todate."' and st4.stock_flage=2) as pr8 on pr8.product_type=prd.product_type

		left join (select sum(stc3.convert_stock) as conv_closing_stock_minus, pr.product_type from tbl_stock_trn as stc3 
	left join product_mst as pr on pr.product_id=stc3.product_id
	where stc3.stock_status!=2  and stc3.stock_date<='".$todate."' and stc3.stock_flage=2) as pr9 on pr9.product_type=prd.product_type		

		where stock.stock_status!=2 ".$where." and stock.company_id=".$_SESSION['company_id']."  Group by prd.product_type";
		//echo $query;
		$result = $dbcon->query($query);

		$str .= '<tr>
			<th style="padding:10px 5px;text-align:center">Sr No.</th>
			<th style="text-align:center">Type Name</th>
			<th style="text-align:center">Opening Stock</th>
			<th style="text-align:center">Closing Stock</th>
			<th style="text-align:center">Stock Value</th>
		</tr>';
		if(brp_mysqli_num_rows($result)>0){
			$i = 1;
			while($row = brp_mysqli_fetch_array($result)){

				$base_opening_stock = $row['plus_opening_stock'] - $row['minus_opening_stock'];
				$conv_opening_stock = $row['plus_conv_opening_stock'] - $row['minus_conv_opening_stock']; 
				$base_closing_stock = $row['closing_stock_plus'] - $row['closing_stock_minus'];
				$conv_closing_stock = $row['conv_closing_stock_plus'] - $row['conv_closing_stock_minus'];
				
				if($POST['stock_value'] != 4){
					$base_stock_value   = $row['brate']*$base_closing_stock;
				}else{
					$base_stock_value   = $row['brate']-$row['crate'];
				}
			 
			
				$b_opening = '<strong style="color:green">'.$base_opening_stock.'</strong><br><strong style="color:orange">'.$conv_opening_stock.'</strong>';
				$b_clstock = '<strong style="color:green">'.$base_closing_stock.'</strong><br><strong style="color:orange">'.$conv_closing_stock.'</strong>';
				
				/*if($row['product_category']==0){
					$row['cat_name'] = 'PRIMARY';
				}*/

				$str .= '<tr>
					<td style="text-align:center">'.$i.'</td>
					<td style="text-align:center">'.$row['product_type_name'].'</td>
					<td style="text-align:center">'.$b_opening.'</td>
					<td style="text-align:center">'.$b_clstock.'</td>
					<td style="text-align:center"><strong>'.number_format($base_stock_value,2).'</strong></td>
				</tr>';

				$total_value = $total_value + $base_stock_value;
				$i++;
			}
			$str .= "<tr>
				<td colspan='4' style='text-align:right'><strong>Total</strong></td>
				<td style='text-align:center'><strong>".number_format($total_value,2)."</strong></td>
			</tr>";	
		}else{
			$str .= '<tr>
				<td colspan="5" style="text-align:center">No Data Yet..!!</td>
			</tr>';
		}
		
		$str .='				 
		</table>';
		echo $str;
	}
?>