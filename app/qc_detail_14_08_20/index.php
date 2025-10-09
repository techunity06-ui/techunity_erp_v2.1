<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
			//$where=" and g.qc_status=0 and FIND_IN_SET('product_qc',p.product_setting_check)";
			//grn.grn_status=0 and trn.grn_trn_status=0 and grn.qc_status=0 and trn.product_qc=0 and grn.ref_type='1'
			$where=" and g.qc_status=0 ";
			//$where=" ";
			$appData = array();
			$i=1;
			$aColumns = array('gt.product_id', 'p.product_type','p.product_name','g.ref_type','g.grn_no','gt.product_qty','g.grn_date','g.qc_status','g.grn_status','g.ref_type','g.product_qc','gt.grn_trn_id','p.product_setting_check');
			$sIndexColumn = "gt.grn_trn_id";
			$isWhere = array("gt.grn_trn_status = 0 and g.qc_status=0 and gt.product_qc=0".$where);
			$sTable = "tbl_grn_trn as gt";			
			$isJOIN = array('left join tbl_grn as g on g.grn_id=gt.grn_id','left join product_mst as p on p.product_id=gt.product_id');
			$hOrder = "grn_trn_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				
				if($row['ref_type']==1)
				{
					$ref_type='jobcard';
				}
				else
				{
					$ref_type='PO';
				}
				
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['product_name'];
				$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
				$row_data[] = $ref_type;
				$row_data[] = $row['grn_no'];
				$row_data[] = $row['product_qty'];
				$row_data[] = date("d-M-Y",strtotime($row['grn_date']));
				
				$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.'qc_add/'.$row['grn_trn_id'].'" ><i class="fa fa-plus"></i></a>';
				//$row_data[] = $edit_btn.' '.$delete_btn.' '.$mrn_btn.' '.$allocate_btn; 
				$row_data[] = ''; 
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode($output);
		}
		else if(strtolower($POST['mode']) == "add") {
			
			$info['qc_no']			= $POST['qc_no'];			
			$info['qc_date']		= date("Y-m-d",strtotime($POST['qc_date']));
			$info['qc_remark']		= $POST['qc_remark'];			
			$info['grn_id']			= $POST['grn_no'];			
			$info['po_ref_id']		= $POST['po_ref_id'];			
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['purchase_id']	= $POST['po_id'];	
			$info['qc_godown']		= $POST['qc_godown'];	
			//$info['grn_type']		= $POST['grn_type'];

			$inserid=add_record('tbl_qc', $info, $dbcon);
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=14 and company_id=".$_SESSION['company_id']);
			
			if($POST['grn_type']=="1"){
				
				//eid
				$querypro="select * from tbl_allocate_process as p where 1";
				$query=$dbcon->query($query1);
				while($rel=mysqli_fetch_array($query))
				{
					$process_id=get_current_process($dbcon,$POST['po_id'],$POST['grn_product']);
					$process_type_current=get_current_process_type($dbcon,$POST['po_id'],$POST['grn_product']);
					$pre_process_id=get_current_process_allocate($dbcon,$POST['po_id'],$POST['grn_product']);
			
					$set11="select * from tbl_allocate_process where p_id=".$pre_process_id;
					$set_row=mysqli_fetch_assoc($dbcon->query($set11));
					$p_ref_id1=$set_row['p_ref_id'];
					$pt_alloca_id=get_alloc_id($dbcon,$POST['grn_ref'],$process_id);
			
					$process=get_next_process($dbcon,$process_id,$POST['grn_product']);
					$process_pr=json_decode($process);
					
					$process_id_new=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

				}
			}else{
				
			}
			
			
			
			
			
			
						
			
			
			
				if($POST['qty_reject']>0)
				{
					$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$POST[grn_no]'");
					$c_mrn=mysqli_num_rows($sel_m);
					
					if($c_mrn==0)
					{
						$info2['mrn_no']			= "1";			
						$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
						$info2['grn_no']			= $POST['grn_no'];			
						$info2['qc_no']				= $inserid;	
						$info2['purchaseorder_id']	= $POST['po_id'];	
						
						$info2['cdate']				= date("Y-m-d H:i:s");
						$info2['user_id']			= $_SESSION['user_id'];
						$info2['company_id']		= $_SESSION['company_id'];
						
						$inserid_mrn=add_record('tbl_mrn', $info2, $dbcon);
					}
					else
					{
						$r_m=mysqli_fetch_assoc($sel_m);
						$inserid_mrn=$r_m['mrn_id'];
					}
					
					$info3['mrn_no']		= $inserid_mrn;			
					$info3['product_id']	= $POST['grn_product'];			
					$info3['rejected_qty']	= $POST['qty_reject'];		
					
					$info3['cdate']			= date("Y-m-d H:i:s");
					$info3['user_id']		= $_SESSION['user_id'];
					$info3['company_id']	= $_SESSION['company_id'];	
					
					$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon);
					
					$stock_status='1';	
					
					$infox2['pt_alloc_id']		= $pt_alloca_id;			
					$infox2['pt_ref_id']		= $POST['grn_ref'];			
					$infox2['pt_product_id']	= $POST['grn_product'];			
					$infox2['pt_process_id']	= $process_id;			
					$infox2['pt_qty']			= $POST['qty_reject'];			
					$infox2['cdate']			= date("Y-m-d H:i:s");
					$infox2['user_id']			= $_SESSION['user_id'];
					$infox2['company_id']		= $_SESSION['company_id'];	
					
					//add_record('tbl_allocate_process_trn', $infox2, $dbcon);
					
					
					$grn_ref=$POST['grn_ref'];
					
					$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
					
				//	echo $POST['grn_ref'];
				}
				
				if($POST['qty_accept']>0)
				{
					if($process_id_new!=0 && $POST['grn_type']==1)
					{
						if(!empty($process_id_new)){
							process_allocate($dbcon,$pre_process_id,$process_id_new,$POST['qty_accept'],$p_ref_id1,"tbl_qc",$POST['grn_product'],$process_type,$POST['qc_unit_id'],$process_priority);
						}
					}
					else
					{
						$stock_status='1';
					}
					if($POST['j_reprocess']==1)
					{
						$info6['pt_alloc_id']		= $pt_alloca_id;			
						$info6['pt_ref_id']			= $p_ref_id1;			
						$info6['pt_product_id']		= $POST['grn_product'];			
						$info6['pt_process_id']		= $process_id;			
						$info6['pt_qty']			= $POST['qty_accept'];			
						$info6['cdate']				= date("Y-m-d H:i:s");
						$info6['user_id']			= $_SESSION['user_id'];
						$info6['company_id']		= $_SESSION['company_id'];	
						
						add_record('tbl_allocate_re_process_trn', $info6, $dbcon);
					}
					else
					{
						$info6['pt_alloc_id']	= $pt_alloca_id;			
						$info6['pt_ref_id']	= $p_ref_id1;			
						$info6['pt_product_id']	= $POST['grn_product'];			
						$info6['pt_process_id']	= $process_id;			
						$info6['pt_qty']	= $POST['qty_accept'];			
							
					
						$info6['cdate']		= date("Y-m-d H:i:s");
						$info6['user_id']	= $_SESSION['user_id'];
						$info6['company_id']	= $_SESSION['company_id'];	
						
						add_record('tbl_allocate_process_trn', $info6, $dbcon);
						
					}
				}
				
				if($POST['qty_reprocess']>0)
				{
					
					
						$info7['process_id']		= $process_id;
						$info7['pt_alloc_id']		= $pt_alloca_id;							
						$info7['p_start_time']		= '';		
						$info7['p_end_time']		= '';		
						$info7['p_qty']				= $POST['qty_reprocess'];		
						$info7['pen_qty']			= $POST['qty_reprocess'];		
						$info7['p_ref_id']			= $p_ref_id1;		
						$info7['p_ref_type']		= 'process_request';		
						$info7['p_product_id']		= $POST['grn_product'];		
						$info7['pr_process_type']	= $process_type_current;		
						$info7['pr_process_id']		= $pre_process_id;		
						
						$info7['cdate']				= date("Y-m-d H:i:s");
						$info7['user_id']			= $_SESSION['user_id'];
						$info7['company_id']		= $_SESSION['company_id'];	
						
						$inserid_alloc=add_record('tbl_allocate_re_process', $info7, $dbcon);
						
						$info8['pt_alloc_id']	= $pt_alloca_id;			
						$info8['pt_ref_id']		= $p_ref_id1;			
						$info8['pt_product_id']	= $POST['grn_product'];			
						$info8['pt_process_id']	= $process_id;			
						$info8['pt_qty']	= $POST['qty_accept'];			
							
					
						$info8['cdate']		= date("Y-m-d H:i:s");
						$info8['user_id']	= $_SESSION['user_id'];
						$info8['company_id']	= $_SESSION['company_id'];	
						
						add_record('tbl_allocate_re_process_trn', $info8, $dbcon);
						
					
					
					$infox1['pt_alloc_id']	= $pt_alloca_id;			
					$infox1['pt_ref_id']	= $POST['grn_ref'];			
					$infox1['pt_product_id']	= $POST['grn_product'];			
					$infox1['pt_process_id']	= $process_id;			
					$infox1['pt_qty']	= $POST['qty_reprocess'];			
						
				
					$infox1['cdate']		= date("Y-m-d H:i:s");
					$infox1['user_id']	= $_SESSION['user_id'];
					$infox1['company_id']	= $_SESSION['company_id'];	
					
					//add_record('tbl_allocate_process_trn', $infox1, $dbcon);
				}
				
				$info1['qc_id']				= $inserid;			
				$info1['qc_product']		= $POST['grn_product'];			
				$info1['qc_product_qty']	= $POST['grn_pqty'];		
				$info1['qc_accepted'] 		= $POST['qty_accept'];	
				$info1['qc_rejected']		= $POST['qty_reject'];	
				$info1['qty_reprocess']		= $POST['qty_reprocess'];	
				$info1['stock_status']		= $stock_status;	
				$info1['po_id']				= $POST['po_id'];		
				$info1['qc_unit_id']		= $POST['qc_unit_id'];		
				
				
				$info1['cdate']				= date("Y-m-d H:i:s");
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];	

				$insertid_qc=add_record('tbl_qc_trn', $info1, $dbcon);
				
				
				
				if($POST['grn_type']==2)
					{
						if(!empty($POST['qty_accept'])){
							add_stock($dbcon,$POST['grn_product'],$POST['qc_unit_id'],$POST['qc_date'],"tbl_qc_trn",$insertid_qc,$POST['qc_godown'],$POST['qty_accept'],"1");
							
							add_request_reserve_stock($dbcon,$POST['po_ref_id'],$POST['qty_accept'],$POST['qc_unit_id']);
						}
					}else{
						if($process_id_new==0)
						{
							if(!empty($POST['qty_accept'])){
								add_stock($dbcon,$POST['grn_product'],$POST['qc_unit_id'],$POST['qc_date'],"tbl_qc_trn",$insertid_qc,$POST['qc_godown'],$POST['qty_accept'],"1");
								
								add_request_reserve_stock($dbcon,$POST['po_ref_id'],$POST['qty_accept'],$POST['qc_unit_id']);
							}
						}
					}
				
				if($POST['grn_type']==1){	
					add_process_stock($dbcon,$pre_process_id,$info1['qc_accepted'],$info1['qc_rejected'],$process_id_new);
				}
				
				$job_qty=get_jobwork_qc_qty($dbcon,$POST['po_id']);
				
				//echo $job_qty;
				if($job_qty==0)
				{
					$dbcon->query("update tbl_jobwork set job_close_status='1' where jobwork_id='$POST[po_id]'");
				}
				
			// Add Qc Parameter Values
			
			$qc_pname=$POST['qc_pname'];
			$qc_param_value=$POST['qc_param_value'];
			$tested_value=$POST['tested_value'];
			$pid=$POST['grn_product'];
			$eid=$POST['eid'];
			$grn_no=$POST['grn_no'];
			
			for($i=0;$i<count($qc_pname);$i++)
			{
				$q=$dbcon->query("select qc_pr_tested from tbl_qc_param_trn where qcpt_grn_id='$grn_no' and qc_param='$qc_pname[$i]' and qc_product='$pid' and qcpt_qc_id='$eid'");
				$count=mysqli_num_rows($q);
				
				$info_qc['qc_param']=$qc_pname[$i];
				$info_qc['qc_pr_actual']=$qc_param_value[$i];
				$info_qc['qc_pr_tested']=$tested_value[$i];
				$info_qc['qc_product']=$pid;
				$info_qc['qcpt_qc_id']=$inserid;
				$info_qc['qcpt_grn_id']=$grn_no;
				
				$info_qc['user_id']=$_SESSION['user_id'];
				$info_qc['cdate']=date("Y-m-d h:i:s");
				$info_qc['company_id']=$_SESSION['company_id'];
				
				$table_qc='tbl_qc_param_trn';$tableid_qc='qcpt_id';
				
				if($count>0)
				{
					$updateid=update_record($table_qc, $info,"qcpt_grn_id='$grn_no' and qc_param='$qc_pname[$i]' and qc_product='$pid' and qcpt_qc_id='$eid'", $dbcon);	
				}else{
					
					add_record($table_qc, $info_qc, $dbcon);
				}
			}
			
			
		//	$dbcon->query("update tbl_qc_param_trn set qcpt_qc_id='$inserid',qcpt_grn_id='$POST[grn_no]' where qcpt_qc_id='0' and user_id='$_SESSION[user_id]' and qcpt_grn_id='$POST[grn_no]'");
			
			$dbcon->query("update tbl_grn set qc_status='1' where grn_id='$POST[grn_no]'");
				
			
			if($inserid)
			{
				$resp['msg'] = "1";
			}
			else
			{
				$resp['msg'] = "0";
			}
			$resp['back'] = $POST['back'];
			echo json_encode($resp); 
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_qc_param` WHERE `p_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			
				$info['qc_no']	= $POST['qc_no'];			
				$info['qc_date']	= date("Y-m-d",strtotime($POST['qc_date']));			
				$info['qc_grn']	= $POST['grn_no'];			
				$info['qc_remark']	= $POST['qc_remark'];			
				
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('tbl_qc', $info,"qc_id=".$POST['eid'] , $dbcon);
				
				$dbcon->query("delete from tbl_qc_trn where qc_id='$POST[eid]'");
				
				foreach($POST['grn_product'] as $row=>$name)
				{
					$info1['qc_id']	= $POST['eid'];			
					$info1['qc_product']	= $POST['grn_product'];			
					$info1['qc_product_qty']	= $POST['grn_pqty'];		
					$info1['qc_accepted'] = $POST['qty_accept'];	
					$info1['qc_rejected']	=$POST['qty_reject'];	
					$info1['qty_reprocess']	=$POST['qty_reprocess'];	

					$info1['cdate']		= date("Y-m-d H:i:s");
					$info1['user_id']	= $_SESSION['user_id'];
					$info1['company_id']	= $_SESSION['company_id'];	

					add_record('tbl_qc_trn', $info1, $dbcon);					
				}
				
				if($updateid)
					$resp['msg'] = "2";
				else
					$resp['msg'] = "0";
				
				echo json_encode($resp);
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				
				$info['qc_status']  = 2;
				$update=update_record('tbl_qc', $info,"qc_id=".$POST['eid'], $dbcon);	
							
				if($update)
					echo "1";	
				else
					echo "0";		
			
		}
		else if(strtolower($POST['mode']) == "get_grn_product") {
			
			//$grn_id=$POST['grn_id'];
			$eid=$POST['eid'];
			
			$str="";
			
			$str="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th style='width:5%;' >#</th>
				<th style='width:27%;'>Product Name</th>
				<th style='width:8%;'>Unit</th>
				<th style='width:15%;'>Total Qty</th>
				<th style='width:15%;'>Accepted Qty</th>
				<th style='width:15%;'>Rejected Qty</th>
				<th style='width:15%;'>Reprocess Qty</th>
				
			</tr>";
			
			$cnt=1;
			$sel=$dbcon->query("select gt.product_id, gt.purchaseorder_id,p.product_type,p.product_name,g.ref_type,g.ref_no,g.grn_no,gt.product_qty,g.grn_date,g.qc_status,g.grn_status,g.ref_type,g.product_qc,gt.grn_trn_id,gt.grn_id,gt.unit_id,umst.unit_name,gt.po_ref_id from tbl_grn_trn as gt 
			left join unit_mst as umst on umst.unitid=gt.unit_id
			left join tbl_grn as g on g.grn_id=gt.grn_id 
			left join product_mst as p on p.product_id=gt.product_id 
			where gt.grn_trn_id='$eid'");
			$row=mysqli_fetch_assoc($sel);
			
			
				$str.="<tr>
					
					<th>".$cnt."</th>
					<th>".$row['product_name']."
						<input type='hidden' class='form-control' name='grn_product' id='grn_product' value='".$row['product_id']."' />
						<input type='hidden' class='form-control' name='grn_type' id='grn_type' value='".$row['ref_type']."' />
						<input type='hidden' class='form-control' name='grn_ref' id='grn_ref' value='".$row['ref_no']."' />
						<input type='hidden' class='form-control' name='j_reprocess' id='j_reprocess' value='".$row['j_reprocess']."' />
						
					</th>
					<th>".$row['unit_name']."
						<input type='hidden' class='form-control' name='qc_unit_id' id='qc_unit_id' value='".$row['unit_id']."' />
					</th>
					<th>".$row['product_qty']."
						<input type='hidden' class='form-control' name='grn_pqty' id='grn_pqty' value='".$row['product_qty']."' />
					</th>
					<th>
						<input type='text' class='form-control' name='qty_accept' id='qty_accept' value='".$accept."' onkeyup='sub_accept_value()' />
						<input type='hidden' class='form-control' name='' id='qty_accept_hid' value='".$accept."' />
						<strong id='qty_error' style='color:red'></strong>
					</th>
					<th>
						<input type='text' class='form-control' name='qty_reject' id='qty_reject' value='".$reject."' onkeyup='sub_accept_value()'  />
						<input type='hidden' class='form-control' name='' id='qty_reject_hid' value='' />
						<strong id='qty_error_reject' style='color:red'></strong>
					</th>
					<th>
						<input type='text' class='form-control' name='qty_reprocess' id='qty_reprocess' value='".$reprocess."' onkeyup='sub_accept_value()'  />
						<input type='hidden' class='form-control' name='' id='qty_reprocess_hid' value='' />
						<strong id='qty_error_reprocess' style='color:red'></strong>
						
						<input type='text' name='po_id' id='po_id' value='".$row['purchaseorder_id']."' />
						<input type='hidden'  name='grn_no' id='grn_no' value='".$row['grn_id']."' />
						<input type='hidden'  name='po_ref_id' id='po_ref_id' value='".$row['po_ref_id']."' />
					</th>
					
					
						
				</tr>";
				
				
			echo $str;
		}
		
		else if(strtolower($POST['mode']) == "get_po_no") {
			
			$grn_id=$POST['grn_id'];
			$sel=$dbcon->query("select purchaseorder_id from tbl_grn where grn_id='$grn_id'");
			$row=mysqli_fetch_assoc($sel);
			
			echo $row['purchaseorder_id'];
		}
		else if(strtolower($POST['mode']) == "show_qc_param_details") {
			
			
			$eid=$POST['eid'];
			
			$s1=$dbcon->query("select product_id from tbl_grn_trn where grn_trn_id='$eid'");
			$r1=mysqli_fetch_array($s1);
			
			$pid=$r1['product_id'];
			
			$str="";
			
			$str.="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th>#</th>
				<th>Parameter Name</th>
				<th>Actual Value</th>
				<th>Testing Value</th>
			
			</tr>";
			
			$cnt=1;
			$sel=$dbcon->query("select mst.*,p.p_name from tbl_product_parameter as mst left join tbl_qc_param as p on p.p_id=mst.param_id where mst.product_id='$pid'");
			while($row=mysqli_fetch_assoc($sel))
			{
				
				$str.="<tr>
					
					<th>".$cnt."</th>
					
					<th>".$row['p_name']."
						<input type='hidden' class='form-control qc_pname' name='qc_pname[]' id='tested_value".$cnt."' value='".$row['param_id']."' />
					</th>
					
					<th>".$row['param_value']."
						<input type='hidden' class='form-control qc_param_value' name='qc_param_value[]' id='tested_value".$cnt."' value='".$row['param_value']."' />
					</th>
					
					<th><input type='text' class='form-control tested_value' name='tested_value[]' id='tested_value".$cnt."'  /></th>
				
				</tr>";
				
				$cnt++;
			}
			
			$total_param=$cnt-1;
			$str.="<tr>
				
				<td colspan='4' align='center'>
				
				<input type='hidden' name='total_param' value='".$total_param."' />
				
				</td>
			
			</tr>";
			
			
			echo $str;
			
		}
		else if(strtolower($POST['mode']) == "add_param") {
			
			$resp['msg'] = "1";
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "add_qc_param_data") {
			
			$qc_pname=$POST['qc_pname'];
			$qc_param_value=$POST['qc_param_value'];
			$tested_value=$POST['tested_value'];
			$form_mode=$POST['form_mode'];
			$pid=$POST['pid'];
			$eid=$POST['eid'];
			$grn_no=$POST['grn_no'];
			
			for($i=0;$i<count($qc_pname);$i++)
			{
				$q=$dbcon->query("select qc_pr_tested from tbl_qc_param_trn where qcpt_grn_id='$grn_no' and qc_param='$qc_pname[$i]' and qc_product='$pid' and qcpt_qc_id='$eid'");
				$count=mysqli_num_rows($q);
				
				$info['qc_param']=$qc_pname[$i];
				$info['qc_pr_actual']=$qc_param_value[$i];
				$info['qc_pr_tested']=$tested_value[$i];
				$info['qc_product']=$pid;
				$info['qcpt_qc_id']=$eid;
				$info['qcpt_grn_id']=$grn_no;
				
				$info['user_id']=$_SESSION['user_id'];
				$info['cdate']=date("Y-m-d h:i:s");
				$info['company_id']=$_SESSION['company_id'];
				
				$table='tbl_qc_param_trn';$tableid='qcpt_id';
				
				if($count>0)
				{
					$updateid=update_record($table, $info,"qcpt_grn_id='$grn_no' and qc_param='$qc_pname[$i]' and qc_product='$pid' and qcpt_qc_id='$eid'", $dbcon);	
				}else{
					
					$inserid=add_record($table, $info, $dbcon);
				}
			}
			//print_r($bid);
			
		}
		else if(strtolower($POST['mode']) == "show_mrn_details") {
			
			$qid=$POST['qid'];
			
			$str="";
			
			$sel_m=$dbcon->query("select * from tbl_mrn where qc_no='$qid'");
			$r_m=mysqli_fetch_assoc($sel_m);
			
			
			$str.="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th>MRN No</th>
				<td>".$r_m['mrn_no']."</td>
				<th>Date</th>
				<td>".date("d/m/Y",strtotime($r_m['mrn_date']))."</td>
			</tr>";
			
			$str.="</table>";
			
			$str.="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th>#</th>
				<th>Product </th>
				<td>Qty</td>
	
			</tr>";
			
			$cnt=1;
			$sel=$dbcon->query("select mst.*,p.product_name from tbl_mrn_trn as mst inner join product_mst as p on p.product_id=mst.product_id where mst.mrn_no='$r_m[mrn_id]'");
			while($row=mysqli_fetch_assoc($sel))
			{
				
				$str.="<tr>
					
					<th>".$cnt."</th>
					<th>".$row['product_name']."</th>
					<th>".$row['rejected_qty']."</th>
					
				</tr>";
				
				$cnt++;
			}
			
			echo $str;
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=14 and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>