<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	 if(strtolower($POST['mode']) == "generate_report_stock"){

	 	$s_date=explode(' - ',$POST['date']);
				$_SESSION['start']=$s_date[0];
				$_SESSION['end']=$s_date[1];
			
				$product_id=$POST['product_id'];
				$batch_no=$POST['batch_no'];
				
				if($product_id!='')
				{
					$where.=" and stock.product_id='$product_id'";
				}
				if($batch_no!='')
				{
					$where.=" and stock.batch_no='$batch_no'";
				}
				if(!empty($POST['branch_id'])){
					$where.= " and stock.branch_id=".$POST['branch_id'];
				}
				 $where.=" and stock.stock_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND stock.stock_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	 	$qry = "SELECT SQL_CALC_FOUND_ROWS stock.stock_id, pro.product_icode, dr.drawing_number, pro.product_id, pro.product_base_unit, un.unit_name,bms.branch_name, c_un.unit_name as conv_unit_name, pro.product_name, pro.product_status, stock.stock_date, stock.batch_no, IFNULL(stock.base_stock,0) as base_stock, IFNULL(stock.convert_stock,0) as convert_stock, IFNULL(stock.used_base_stock,0) as used_base_stock, IFNULL(stock.used_convert_stock,0) as used_convert_stock, stock.stock_flage, (select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status=0 and qc.customer_id = 0 and qc.customer_id = '' and stock_flage=1 and qc.product_id=pro.product_id and qc.batch_no = stock.batch_no and qc.company_id=1 group by qc.batch_no) as opening_base_stock, (select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status=0 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = '' and qc.batch_no = stock.batch_no and qc.product_id=pro.product_id and qc.company_id=1 group by qc.batch_no) as opening_conv_stock FROM tbl_stock_trn as stock left join product_mst as pro on pro.product_id = stock.product_id left join unit_mst as un on un.unitid=pro.product_base_unit left join unit_mst as c_un on c_un.unitid=pro.product_conv_unit left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id left join branch_mst as bms on bms.branch_id=stock.branch_id where ( 1 AND stock.batch_no != '' and stock.stock_flage=2 and pro.product_status !=2 ". $where.") ORDER BY stock.batch_no asc";
	 		$res = $dbcon->query($qry);

	 		$str ='
				<table  width="100%"   class="display table  table-striped">
				</table>
				<table  class="display table table-bordered table-striped" id="data_list">
					<th>Sr. NO.</th>
					<th>Product Name</th>
					<th>Batch No</th>
					<th>Total Base Stock</th>
					<th>Total Conv Stock</th>	  
					<th>Used Base Stock</th>	  
					<th>Used Conv Stock</th>	  
					<th>Workorder</th>	  
					<th>Balance Base Stock</th>	  
					<th>Balance Conv Stock</th>	  
					<th>Branch Name</th>	  
				 <tbody>';

	 		if(brp_mysqli_num_rows($res)>0){
	 			$i = 1;
	 			$batch_no = "";
				$opening_base_stock = 0;
				$opening_conv_stock = 0;
	 			while($row = brp_mysqli_fetch_assoc($res)) {
					// $bal_base_stock = 0;
					// $bal_conv_stock = 0;
					if($batch_no == "" || $batch_no != $row['batch_no']){
						$batch_no = $row['batch_no'];
						$opening_base_stock = $row['opening_base_stock'];
						$opening_conv_stock = $row['opening_conv_stock'];

						$bal_base_stock = $opening_base_stock - $row['base_stock'];
						$bal_conv_stock = $opening_conv_stock - $row['convert_stock'];

					} else if($batch_no == $row['batch_no']){
						$bal_base_stock = $bal_base_stock - $row['base_stock'];
						$bal_conv_stock = $bal_conv_stock - $row['convert_stock'];
					}else{
						$batch_no = "";
					}

					$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }	
					
					$str .='<tr>
								<td style="text-align:center">'.$i.'</td>
								<td style="text-align:center">'.$row['product_name'].' '.$item_code.' '.$drawing_number.'</td>
								<td style="text-align:center">'.$row['batch_no'].'</td>
								<td style="text-align:center">'.$opening_base_stock .' '. $row['unit_name'].'</td>
								<td style="text-align:center">'.$opening_conv_stock.' '. $row['conv_unit_name'].'</td>
								<td style="text-align:center">'.$row['base_stock'].' '. $row['unit_name'].'</td>
								<td style="text-align:center">'.$row['convert_stock'].' '. $row['conv_unit_name'].'</td>
								<td style="text-align:center">-</td>
								<td style="text-align:center">'.$bal_base_stock .' '. $row['unit_name'].'</td>
								<td style="text-align:center">'.$bal_conv_stock .' '. $row['conv_unit_name'].'</td>
								<td style="text-align:center">'. $row['branch_name'].'</td>
							</tr>';
					
					if($batch_no == "" || $batch_no != $row['batch_no']){
						$opening_base_stock = $bal_base_stock;						
						$opening_conv_stock = $bal_conv_stock;						
					} else if($batch_no == $row['batch_no']){
						$opening_base_stock = $bal_base_stock;						
						$opening_conv_stock = $bal_conv_stock;						
					}else{
						$batch_no = "";
					}
					$i++;
				}
	 		}else
			{
				$str .='<tr>
							<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
			}
			$str .='</tbody>				 
				  </table>';
				  
			echo $str;


	 		$rel=mysqli_fetch_assoc($res);		
	 }
	 if(strtolower($POST['mode']) == "generate_report_stock_old")
		{
				$s_date=explode(' - ',$POST['date']);
				$_SESSION['start']=$s_date[0];
				$_SESSION['end']=$s_date[1];
			
				$product_id=mysqli_real_escape_string($dbcon,$POST['product_id']);
				$batch_no=$POST['batch_no'];
				
				if($product_id!='')
				{
					$where="and stock.product_id='$product_id'";
				}
				if($batch_no!='')
				{
					$where="and stock.batch_no='$batch_no'";
				}
				 $where.=" and stock.stock_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND stock.stock_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
				
				$appData = array();
				$i=1;
				$aColumns = array('stock.stock_id','pro.product_icode', 'dr.drawing_number','pro.product_id','pro.product_base_unit','un.unit_name','c_un.unit_name as conv_unit_name','pro.product_name','pro.product_status','stock.stock_date','stock.batch_no','IFNULL(stock.base_stock,0) as base_stock','IFNULL(stock.convert_stock,0) as convert_stock','IFNULL(stock.used_base_stock,0) as used_base_stock','IFNULL(stock.used_convert_stock,0) as used_convert_stock','stock.stock_flage','(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc 
					where qc.stock_status=0 and qc.customer_id = 0 and qc.customer_id = "" and stock_flage=1 and qc.product_id=pro.product_id and qc.batch_no = stock.batch_no and qc.company_id='.$_SESSION['company_id'].' 
					group by qc.batch_no) as opening_base_stock','(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc 
					where qc.stock_status=0 and stock_flage=1 and qc.customer_id = 0 and qc.customer_id = "" and qc.batch_no = stock.batch_no  and qc.product_id=pro.product_id and qc.company_id='.$_SESSION['company_id'].' 
					group by qc.batch_no) as opening_conv_stock');
				$sIndexColumn = "pro.product_id";
				$isWhere = array("stock.batch_no != '' and stock.stock_flage=2 and pro.product_status !=2 ".$where);
				$sTable = "tbl_stock_trn as stock";			
				$isJOIN = array("left join product_mst as pro on pro.product_id = stock.product_id","left join unit_mst as un on un.unitid=pro.product_base_unit","left join unit_mst as c_un on c_un.unitid=pro.product_conv_unit","left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id");
				$hOrder = "stock.batch_no asc";
				include($include.'pagging.php');
				$appData = array();
				$id=1;

				$batch_no = "";
				$opening_base_stock = 0;
				$opening_conv_stock = 0;
				foreach($sqlReturn as $row) {
					// $bal_base_stock = 0;
					// $bal_conv_stock = 0;
					if($batch_no == "" || $batch_no != $row['batch_no']){
						$batch_no = $row['batch_no'];
						$opening_base_stock = $row['opening_base_stock'];
						$opening_conv_stock = $row['opening_conv_stock'];

						$bal_base_stock = $opening_base_stock - $row['base_stock'];
						$bal_conv_stock = $opening_conv_stock - $row['convert_stock'];

					} else if($batch_no == $row['batch_no']){
						$bal_base_stock = $bal_base_stock - $row['base_stock'];
						$bal_conv_stock = $bal_conv_stock - $row['convert_stock'];
					}else{
						$batch_no = "";
					}

					$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }	
					
					$row_data = array();
					$row_data[] = $row['sr'];
					$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
					$row_data[] = $row['batch_no'];
					$row_data[] = $opening_base_stock .' '. $row['unit_name'];
					$row_data[] = $opening_conv_stock.' '. $row['conv_unit_name']; 
					$row_data[] = $row['base_stock'].' '. $row['unit_name']; 
					$row_data[] = $row['convert_stock'].' '. $row['conv_unit_name']; 
					$row_data[] = '0459999'; 
					$row_data[] = $bal_base_stock .' '. $row['unit_name'];
					$row_data[] = $bal_conv_stock.' '. $row['conv_unit_name']; 
					$appData[] = $row_data;
					$id++;

					if($batch_no == "" || $batch_no != $row['batch_no']){
						$opening_base_stock = $bal_base_stock;						
						$opening_conv_stock = $bal_conv_stock;						
					} else if($batch_no == $row['batch_no']){
						$opening_base_stock = $bal_base_stock;						
						$opening_conv_stock = $bal_conv_stock;						
					}else{
						$batch_no = "";
					}
				}
				$output['aaData'] = $appData;
				echo json_encode( $output );
			
		}else if(strtolower($POST['mode']) == "product_load") {
    $drawing_number = '';
    $item_code = '';
    $alias = '';
    $whr = '';


		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
	

	 $whr.=" and stock.stock_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND stock.stock_date<='".date('Y-m-d',strtotime($s_date[1]))."'";

    $companyConfiguration=getCompanyConfiguration($dbcon);
    $crm_pro_type=$companyConfiguration['crm_pro_type'];
    $so_pro_type=$companyConfiguration['so_pro_type'];
    $indent_po_pro_type=$companyConfiguration['indent_po_pro_type'];
    $production_pro_type=$companyConfiguration['production_pro_type'];
    $crm_pro_search=$companyConfiguration['crm_pro_search'];
    $purchase_pro_search=$companyConfiguration['purchase_pro_search'];
    $sales_pro_search=$companyConfiguration['sales_pro_search'];
    $bom_pro_search=$companyConfiguration['bom_pro_search'];
    $production_pro_search = $companyConfiguration['production_pro_search'];

    $inquiry_type = $POST['inquiry_type'];
    $type = strtolower($POST['type']);
    $search = strtolower($POST['search']);

    if($type=='crm_pro_type'){
        $whr=' and pro.product_type in('.$crm_pro_type.')';
    } else if($type=='so_pro_type'){
        $whr=' and pro.product_type in('.$so_pro_type.')';
    } else if($type=='production_pro_type'){
        $whr=' and pro.product_type in('.$production_pro_type.')';
    } else if($type=='indent_po_pro_type'){
        $whr=' and pro.product_type in('.$indent_po_pro_type.')';
    }
    if($search=='crm_pro_search'){
        $pro_search=explode(",", $crm_pro_search);
    } else if($search=='purchase_pro_search'){
        $pro_search=explode(",", $purchase_pro_search);
    } else if($search=='sales_pro_search'){
        $pro_search=explode(",", $sales_pro_search);
    } else if($search=='bom_pro_search'){
        $pro_search=explode(",", $bom_pro_search);
    } else if($search == 'production_pro_search'){
        $pro_search=explode(",", $production_pro_search);
    }

    $query="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from tbl_stock_trn as stock left join product_mst as pro on pro.product_id = stock.product_id left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where stock.stock_flage=2 and stock.batch_no != '' and pro.product_status=0 AND pro.company_id IN (0,".$_SESSION['company_id'].")".$whr." group by stock.product_id order by pro.product_name";
	
	
	// /echo $query;
	
    $result=$dbcon->query($query);
	// echo "<pre>"; var_dump($crm_pro_type);var_dump($query); die();
    $i=0;
    while($row=mysqli_fetch_array($result)){
		
        if(in_array('drawing',$pro_search)){
            $drawing_number = " -- (".$row['drawing_number'].")";
        }
        if(in_array('item',$pro_search)){
            $item_code = " -- (".$row['product_icode'].")";
        }
        if(in_array('alias',$pro_search)){
            $alias = " -- (".$row['product_alias_name'].")";
        }
        $row1[0][]=$row['product_id'];
        $row1[1][]=$row['product_name'].' '.$item_code.' '.$drawing_number.' '.$alias;
    }
	//$row=mysqli_fetch_array($result);		
    // print_r($POST);
	//echo "<pre>"; print_r($row1); die();
    echo json_encode($row1); //die();
}else if(strtolower($POST['mode'])== "load_batch_no")
	{
		$product_id=$POST['product_id'];
		
		$query="select batch_no,stock_id from tbl_stock_trn as trn
		where trn.stock_status=0 and stock_flage=2 and product_id=".$product_id."  group by batch_no";


			//echo $query;
		$str="";
		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{	
			$str .= '<option value="">Select Batch Data</option>';
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$str .= '<option value="'.$rel['batch_no'].'">'.$rel['batch_no'].'</option>';
			}
		}else{
			$str .= '<option value="">No Batch Data !!</option>';
		}

		echo $str;
	}
		
		
	
?>