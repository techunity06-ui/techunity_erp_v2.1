<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(brp_strtolower($POST['mode']) == "add_direct_material_return") {
			
			$remark = $POST['remark'];
			$issue_no = $POST['issue_no'];
			$issue_date = date("Y-m-d H:i:s");;
			
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['branch_id']			= $POST['branch_id'];
			$info['godown_id'] = $POST['godown_id'];
			$return_user_id =  $POST['user_id'];
			
				$info['remark']	= $remark;
				$info['issue_no']	= $issue_no;
				$info['issue_date']	= $issue_date;
				$info['release_id']	= $release_id;	
				$info['return_user_id'] = $return_user_id;
				$info['product_id'] = $POST['product_id'];
				$info['return_qty'] = $POST['product_base_qty'];
				$info['return_unit'] = $POST['product_base_unit'];
				$info['return_conv_qty'] =  $POST['product_conv_qty'];
				$info['return_conv_unit'] = $POST['product_conv_unit'];
				$info['release_type'] = 1;
				
				$req_id = add_record('tbl_store_return_material',$info, $dbcon);
				// die;
				if($req_id){
			
				$stock['stock_date'] =  date("Y-m-d H:i:s");
				$stock['product_id'] = $POST['product_id'];
				$stock['base_unit'] = $POST['product_base_unit'];
				$stock['base_stock'] = $POST['product_base_qty'];
				$stock['convert_unit'] =  $POST['product_conv_unit'];
				$stock['convert_stock'] =  $POST['product_conv_qty'];
				$stock['stock_flage'] = 1;
				$stock['godown_id'] = $POST['godown_id'];
				$stock['ref_name'] = 'tbl_store_return_material';
				$stock['ref_id'] = $req_id;
				
				$stock['cdate']				= date("Y-m-d H:i:s");
				$stock['user_id']			= $_SESSION['user_id'];
				$stock['company_id']			= $_SESSION['company_id'];
				$stock['branch_id']			= $POST['branch_id'];

				$stock_id = add_record('tbl_stock_trn',$stock, $dbcon);
					
				}
			
			if($req_id > 0){
				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}

			echo json_encode($arr);
			
		}
		

?>