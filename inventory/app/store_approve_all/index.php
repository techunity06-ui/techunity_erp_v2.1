<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
//{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
//	{
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

$company_config = getCompanyConfiguration($dbcon);	

if(strtolower($POST['mode'])== "add")
{

	$arr_batch_id = $POST['batch_id'];
	$arr_batch_no = $POST['batch_no'];
	$arr_reprocess_qc = $POST['reprocess_qc'];
	$arr_remark = $POST['remark'];
	$arr_grn_trn_id = $POST['grn_trn_id'];
	$arr_godown_id = $POST['godown_id'];
	$arr_accept_qty = $POST['accept_qty'];
	$arr_product_id = $POST['product_id'];
	$arr_batch_unit = $POST['batch_unit'];

	$arr['back'] = $_POST['back'];
	for($i=0;$i<count($arr_batch_id);$i++){
		$store_no = get_store_accept_no($dbcon);
		$store_date = date("Y-m-d");

		$info = array();
		$info1 = array();
		$infog = array();
		$batch_info = array();

		$info['store_accept_no']	= $store_no;
		$info['store_accept_date']	= $store_date;
		$info['batch_id'] 			= $arr_batch_id[$i];
		$info['batch_no']			= $arr_batch_no[$i];
		$info['reprocess_qc'] 		= $arr_reprocess_qc[$i];
		$info['remark']				= empty($arr_remark[$i]) ? 'STORE APPROVE ALL' : $arr_remark[$i];
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];

		//var_dump("fdsj");
		$inserid=add_record('tbl_store_accept',$info, $dbcon);

		if($inserid){
			$arr['msg'] = '1';
			
			update_store_accept_no($dbcon);	

			$info1['grn_trn_id']				= $arr_grn_trn_id[$i];
			$info1['godown_id']					= $arr_godown_id[$i];
			$info1['batch_id']					=  $arr_batch_id[$i];

			$info1['qty']						= $arr_accept_qty[$i];
			$info1['unit_id']					= $arr_batch_unit[$i];

			$info1['product_id']				= $arr_product_id[$i];
			$info1['user_id']					= $_SESSION['user_id'];
			$info1['company_id']				= $_SESSION['company_id'];
			
			$info1['store_accept_id']	= $inserid;
			$info1['store_accept_trn_status']	= 0;
			
			$inser_trn_id=add_record('tbl_store_accept_trn',$info1, $dbcon);
			
			$abc=store_stock_add($dbcon,$inserid,$arr_reprocess_qc[$i]);
			
			$batch_info['stock_approval_status'] = 1;
			$upd_batch = update_record("tbl_batch_data",$batch_info,"batch_id=".$arr_batch_id[$i], $dbcon);

			$query="select count(batch_id) as total_accept from tbl_batch_data where grn_trn_id=".$POST['grn_trn_id']." and stock_approval_status = 0 and status = 0 and reprocess_qc_id = 0";
			$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

			if($rel['total_accept']=="0"){
				$infog['store_accept']		= 1;
				$updateid12=update_record("tbl_grn_trn",$infog,"grn_trn_id=".$arr_grn_trn_id[$i], $dbcon);
			}
		}else{
			$arr['msg'] = '0';
		}	
	}	

	echo json_encode($arr);
}

function store_stock_add($dbcon,$store_accept_id,$reprocess_qc){
	$query="select * from tbl_store_accept_trn as trn
	where trn.store_accept_trn_status=0 and store_accept_id=".$store_accept_id;
	//var_dump($query);
	$result=$dbcon->query($query);
	if(mysqli_num_rows($result)>0)
	{
		while($rel=brp_mysqli_fetch_assoc($result))
		{
			$accept_qty=$rel['qty'];
			// $accept_qty=$rel['batch_qty'];
			/*$query_grn="select trn.*,grn.grn_date,grn.ref_type from tbl_grn_trn as trn
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			where trn.grn_trn_status=0 and grn_trn_id=".$rel['grn_trn_id'];*/

		 $query_grn="select batch.*,trn.*,grn.grn_date,trn.branch_id as sel_branch from tbl_batch_data as batch
			left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			where batch.batch_id =".$rel['batch_id'];

			// $accept_qty=$rel_grn['batch_qty'];
			
			$result_grn=$dbcon->query($query_grn);
			$rel_grn=brp_mysqli_fetch_assoc($result_grn);

			// var_dump($rel_grn['ref_type']);
			if($rel_grn['reprocess_qc'] == '1' && $rel_grn['ref_type']=="2"){

			}else if($rel_grn['is_scrap'] == '1'){
				add_stock($dbcon,$rel_grn['product_scrap_id'],$rel_grn['scrap_unit'],$rel_grn['grn_date'],"scrap",$rel_grn['grn_trn_id'],$rel['godown_id'],$rel_grn['scrap_qty'],"1",$rel_grn['branch_id'],"","","",$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
			else if($rel_grn['ref_type']=="2"){
			
				purchase_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="1"){
				 jobwork_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$accept_qty,$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty'],$rel_grn['auto_store_relese']);
			}else if($rel_grn['ref_type']=="3"){
				jobwork_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$rel['qty'],$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty'],$rel_grn['auto_store_relese']);
			}else if($rel_grn['ref_type']=="4"){
				direct_grn_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
			else if($rel_grn['ref_type']=="6"){  // returnable chalan stock
				$stock_date=date("Y-m-d");

				  $query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
								where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);

				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"returnable",$rel_grn['grn_trn_id'],$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",$res1['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);

				// returnable_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$rel['qty'],$rel['unit_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="5"){ 
				$stock_date=date("Y-m-d");

				 $query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
								where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);


				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"direct_grn",$res1['grn_trn_sub_id'],$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",$rel_grn['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="7"){
			
				stock_transfer_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['stock_transfer_trn_id'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['to_godown_id']);
			}else{

				$stock_date=date("Y-m-d");

				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"reject_qc_new_product",'',$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",'',$rel_grn['batch_id'],$rel_grn['batch_no']);
			}
		}
	}
}

?>