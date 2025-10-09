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
				 $where.=" and stock.stock_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND stock.stock_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
	 	$qry = "SELECT stock.stock_id,gd.gd_name, pro.product_icode, dr.drawing_number, pro.product_id, pro.product_base_unit, un.unit_name, c_un.unit_name as conv_unit_name, pro.product_name, pro.product_status, stock.stock_date, stock.batch_no, IFNULL(stock.base_stock,0) as total_batch_stock,IFNULL(stock.convert_stock,0) as total_batch_conv_stock,stock.stock_flage,

			(select sum(qc.base_stock) as base_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and qc.customer_id = 0 and qc.customer_id = '' and stock_flage=2 and qc.product_id=pro.product_id and qc.batch_no = stock.batch_no and qc.company_id=1 and qc.perent_id = stock.stock_id) as used_base_stock, 

			(select sum(qc.convert_stock) as con_stock_add from tbl_stock_trn as qc where qc.stock_status != 2 and stock_flage=2 and qc.customer_id = 0 and qc.customer_id = '' and qc.batch_no = stock.batch_no and qc.product_id=pro.product_id and qc.company_id=1 and qc.perent_id = stock.stock_id) as used_convert_stock,
			(select sum(qc.base_stock) as base_stock_plus from tbl_reserve_stock as qc where qc.stock_status != 2 and qc.customer_id = 0 and qc.customer_id = '' and stock_flage=1 and qc.product_id=pro.product_id and qc.stock_id = stock.stock_id and qc.company_id=1 group by qc.stock_id) as reserve_batch_stock_plus,

			(select sum(qc.convert_stock) as base_stock_plus from tbl_reserve_stock as qc where qc.stock_status != 2 and qc.customer_id = 0 and qc.customer_id = '' and stock_flage=1 and qc.product_id=pro.product_id and qc.stock_id = stock.stock_id and qc.company_id=1 group by qc.stock_id) as reserve_batch_conv_stock_plus,

			(select sum(qc.base_stock) as base_stock_minus from tbl_reserve_stock as qc where qc.stock_status != 2 and qc.customer_id = 0 and qc.customer_id = '' and stock_flage=2 and qc.product_id=pro.product_id and qc.stock_id = stock.stock_id and qc.company_id=1 group by qc.stock_id) as reserve_batch_stock_minus,

			(select sum(qc.convert_stock) as base_stock_add from tbl_reserve_stock as qc where qc.stock_status != 2 and qc.customer_id = 0 and qc.customer_id = '' and stock_flage=2 and qc.product_id=pro.product_id and qc.stock_id = stock.stock_id and qc.company_id=1 group by qc.stock_id) as reserve_batch_conv_stock_minus 

			FROM tbl_stock_trn as stock 

			left join product_mst as pro on pro.product_id = stock.product_id 
			left join unit_mst as un on un.unitid=pro.product_base_unit 
			left join unit_mst as c_un on c_un.unitid=pro.product_conv_unit 
			left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id 
			left join mst_godown as gd on gd.gd_id = stock.godown_id 

			where ( 1 AND stock.batch_no != '' and stock.stock_flage=1 and stock.stock_status != 2 and pro.product_status !=2 ". $where.")  ORDER BY stock.batch_no asc";
				// echo $qry;
	 		$res = $dbcon->query($qry);

	 		$str ='
				<table  width="100%"   class="display table  table-striped">
				</table>
				<table  class="display table table-bordered table-striped" id="data_list">
					<th>Sr. NO.</th>
					<th>Product</th>
					<th>Batch No</th>
					<th>Godown</th>	  
					<th>Total Batch Stock</th>	  
					<th>Total Used  Stock</th>	  
					<th>Total Reserve Stock</th>	  
					<th>Available Stock</th>	  
					
				 <tbody>';

	 		if(brp_mysqli_num_rows($res)>0){
	 			$i = 1;
	 			$batch_no = "";
	 			$bal_base_stock = 0;
	 			$bal_conv_stock = 0;
	 			while($row = brp_mysqli_fetch_assoc($res)) {
					$total_batch_stock = 0;
					$total_batch_conv_stock = 0;
					 $used_base_stock = 0;
					 $used_convert_stock = 0;

					 $reserve_stock = 0;
					 $reserve_conv_stock = 0;

					 $res_stock_plus = 0;
					 $res_stock_conv_plus =  0;

					 $res_stock_minus =  0;
					 $res_stock_conv_minus =  0;

					 if(!empty($row['used_base_stock'])) {
					 	$used_base_stock = $row['used_base_stock'];
					 }

					 if(!empty($row['used_convert_stock'])) {
					 	$used_convert_stock = $row['used_convert_stock'];
					 }


					 if(!empty($row['reserve_batch_stock_plus'])) {
					 	$res_stock_plus = $row['reserve_batch_stock_plus'];
					 }

					 if(!empty($row['reserve_batch_conv_stock_plus'])) {
					 	$res_stock_conv_plus = $row['reserve_batch_conv_stock_plus'];
					 }

					  if(!empty($row['reserve_batch_stock_minus'])) {
					 	$res_stock_minus = $row['reserve_batch_stock_minus'];
					 }

					 if(!empty($row['reserve_batch_conv_stock_minus'])) {
					 	$res_stock_conv_minus = $row['reserve_batch_conv_stock_minus'];
					 }


					 $reserve_stock = $res_stock_plus - $res_stock_minus;
					 $reserve_conv_stock = $res_stock_conv_plus - $res_stock_conv_minus;


					 $total_batch_stock = $row['total_batch_stock'];
					 $total_batch_conv_stock = $row['total_batch_conv_stock'];

					 $bal_base_stock = $total_batch_stock -  $used_base_stock;
					 $bal_conv_stock = $total_batch_conv_stock - $used_convert_stock;

					$batch_no = $row['batch_no'];
					/*if($batch_no == "" || $batch_no != $row['batch_no']){
						$total_batch_stock = $row['total_batch_stock'];
						$total_batch_conv_stock = $row['total_batch_conv_stock'];

						$bal_base_stock = $total_batch_stock -  $used_base_stock;
						$bal_conv_stock = $total_batch_conv_stock - $used_convert_stock;

					} else if($batch_no == $row['batch_no']){
						$bal_base_stock = $bal_base_stock - $used_base_stock;
						$bal_conv_stock = $bal_conv_stock - $used_convert_stock;
					}else{
						$batch_no = "";
					}*/

					$bal_base_stock = $bal_base_stock - $reserve_stock;
					$bal_conv_stock = $bal_conv_stock - $reserve_conv_stock;

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
								<td style="text-align:center">'.$row['product_name'].'</td>
								<td style="text-align:center">'.$row['batch_no'].'</td>
								<td style="text-align:center">'.$row['gd_name'].'</td>
								<td style="text-align:center">'.$total_batch_stock .' '. $row['unit_name'].'</br>
								'.$total_batch_conv_stock.' '. $row['conv_unit_name'].'</td>
								<td style="text-align:center"><a class="ttip" data-original-title="Used Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'batch_stock_report/'.$row['product_id'].'">'. $used_base_stock.' '. $row['unit_name'].'</br>
								'.$used_convert_stock.' '. $row['conv_unit_name'].'</a></td>
								<td style="text-align:center"><a  class="ttip" data-original-title="Reserve Stock Details" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.REPORT_ROOT.'reserve_stock_report/'.$row['product_id'].'">'. $reserve_stock.' '. $row['unit_name'].'</br>
								'.$reserve_conv_stock.' '. $row['conv_unit_name'].'</a></td>
								<td style="text-align:center">'.$bal_base_stock .' '. $row['unit_name'].'</br>
								'.$bal_conv_stock .' '. $row['conv_unit_name'].'</td>
							</tr>';
					
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
	else if(strtolower($POST['mode']) == "product_load") {
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

    $query="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from tbl_stock_trn as stock left join product_mst as pro on pro.product_id = stock.product_id left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where stock.batch_no != '' and pro.product_status=0 AND pro.company_id IN (0,".$_SESSION['company_id'].")".$whr." group by stock.product_id order by pro.product_name";
	
	
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
		where trn.stock_status != 2 and stock_flage=1 and batch_no !='' and product_id=".$product_id."  group by batch_no";


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