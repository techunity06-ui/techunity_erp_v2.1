<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
		
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
			$where.="  and pl_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND pl_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			$appData = array();
			$i=1;
			$aColumns = array('pl_order_id','pl_order_no','pl_order_date','pl_order_status','estimate.cdate','estimate.user_id', 'invoice_status','bom_use_status');
			$sIndexColumn = "pl_order_id";
			$isWhere = array("pl_order_status = 0".$where.check_user('estimate'));
			$sTable = "tbl_planning as estimate";			
			$isJOIN = array();
			$hOrder = "estimate.pl_order_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				//$row_data[] = $id;
				$row_data[] = $row['pl_order_no'];
				$row_data[] = date('d M, Y',strtotime($row['pl_order_date']));
				
					$invoicestatus='';$delete='';$edit='';$add_bom_btn='';$req_po_btn="";
					//$sales_order_print='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'planning_print/'.$row['pl_order_id'].'"><i class="fa fa-print"></i></a>';
					if($row["invoice_status"]==1)
					{
						$invoicestatus='<a class="btn btn-xs btn-primary" data-original-title="Estimate" data-toggle="tooltip" data-placement="top" href="Javascript:;">
						<i class="fa fa-thumb-up">Invoice Done</i>
						</a>';
					}
					else
					{
						$invoicestatus='<a class="btn btn-xs btn-primary" data-original-title="Click To Make Invoice" data-toggle="tooltip" data-placement="top" href="invoice/'.$row['pl_order_id'].'"><i class="fa fa-arrow-right "></i></a>';
					
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_planning('.$row['pl_order_id'].')"><i class="fa fa-trash-o"></i></button>';
						
						$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'planning_edit/'.$row['pl_order_id'].'"><i class="fa fa-pencil"></i></a>';
					
						
						$req_po_btn='<a class="btn btn-xs btn-success" data-original-title="Request PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'bom_po_request/'.$row['pl_order_id'].'"><i class="fa fa-plus"></i></a>';
					}
					
					
					$row_data[] = $sales_order_print.' '.$edit.' '.$delete.' '.$add_bom_btn.' '.$req_po_btn;
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") 
		{
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
			
				$info['pl_order_no']	= $POST['pl_order_no'];
				$info['ref_no']			= $POST['ref_no'];
				$info['pl_order_date']	= date('Y-m-d',strtotime($POST['pl_order_date']));
				$info['remark']			= $POST['remark'];
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				$inserestimateid=add_record('tbl_planning', $info, $dbcon);
				
				$infosp['pl_ordertrn_status']		= 0;
				$infosp['pl_order_id']		= $inserestimateid;
			$updateid=update_record('tbl_planning_ordertrn', $infosp,"pl_ordertrn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			
			
				$dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='6'");
			
			if($inserestimateid){	
				$arr['msg']="1";
				$arr['eid']=$inserestimateid;
			}
			else{
				$arr['msg']="0";
			}
					
			echo json_encode($arr);
			
		}		
		else if(strtolower($POST['mode']) == "edit") 
		{
			$info['pl_order_no']	= $POST['pl_order_no'];
			$info['ref_no']			= $POST['ref_no'];
			$info['pl_order_date']	= date('Y-m-d',strtotime($POST['pl_order_date']));
			$info['cust_id']		= $POST['cust_id'];
			$info['packing']		= $POST['packing'];
			$info['freight']		= $POST['freight'];
			$info['g_total']		= $POST['g_total'];
			$info['remark']			= text_rnremove($POST['remark']);
			$info['mdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$updateid=update_record('tbl_planning', $info,"pl_order_id=".$POST['eid'] , $dbcon);
				if($updateid)
				{	
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}
				else{
					$arr['msg']=0;
				}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
					
			$info['pl_order_status']	= 2;
			$info1['pl_ordertrn_status']	= 2;
			$updateestimateid=update_record('tbl_planning', $info,"pl_order_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_planning_ordertrn', $info1,"pl_order_id=".$POST['eid'] , $dbcon);				
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") 
		{
			$info1['product_type']		= $POST['product_type'];
			$info1['product_id']		= $POST['product_id'];
			$info1['description']		= stripslashes($POST['product_des']);
			$info1['product_hsn_code']	= $POST['product_hsn_code'];
			$info1['product_qty']		= $POST['product_qty'];
			$info1['unit_id']			= $POST['unit_id'];
			
			$table='tbl_planning_ordertrn';$tableid='pl_ordertrn_id';
			if(!empty($POST['planning_id']))
			{
					$info1['pl_order_id']= $POST['planning_id'];
					$table='tbl_planning_ordertrn';
					$tableid='pl_ordertrn_id';
			}
			else
			{
				$info1['user_id']	= $_SESSION['user_id'];
				$info1['pl_ordertrn_status']	= 3;
				
			}
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
		}
		else if(strtolower($POST['mode']) == "formulavalue") {
				$rate_total=0;$c_total=$POST['c_total'];
		 $qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$POST['eid']." order by tax_value desc";
			$row=$dbcon->query($qry);
			$j=0;
				//$dis=$POST['total']*$POST['t_dis']/100;
				$rate_total=$total=$POST['total'];
			while($tax=mysqli_fetch_assoc($row))
			{	
				if(strpos(strtolower(" ".$tax['tax_name']), "excise")==true)
				{
					$rate=$total*$tax['tax_value']/100;
					$total+=$rate;
				}
				else	
				{
					 $rate=($total)*$tax['tax_value']/100;
				}
				echo '<div class="form-group">
								<label class="col-md-3 control-label">'.$tax['tax_name'].'</label>
								<div class="col-md-6 col-xs-11">
								<input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
					$rate_total=$rate_total+$rate;
					$j++;
			}
			$g_total=$rate_total+$c_total;
			echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			//$qry="select * from product_mst where product_id=".$POST['eid'];
			$qry="select * from product_mst where product_id=".$POST['eid'];
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);
					
			echo json_encode( $row );
		
		}	
		else if(strtolower($POST['mode'])== "load_podata")
		{
				getpono($dbcon,$POST['cust_id']);
		}
			
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['planning_id']==""){
				 $query="select mst.*,product.product_name,cat.unit_name,product.product_name from tbl_planning_ordertrn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id  where pl_ordertrn_status=3 and mst.user_id=".$_SESSION['user_id'];
			}else{
				$query="select mst.*,product.product_name,cat.unit_name,product.product_name from  tbl_planning_ordertrn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id  where pl_ordertrn_status=0 and pl_order_id=".$POST['planning_id'];
			}
		    
			$result=$dbcon->query($query);
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-11">
							<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
								<tr id="field">
									<th class="text-center" width="10%">Product Type</th>
									<th class="text-center" width="20%">Product Name</th>
									<th class="text-center"width="8%">HSN Code</th>
									<th class="text-center"width="8%">Qty</th>
									<th class="text-center"width="6%">Per</th>
									<th class="text-center"width="10%">Action</th>
								</tr>';
								if(mysqli_num_rows($result)>0)
								{
									$i=1;
									while($rel=mysqli_fetch_assoc($result))
									{
				
									 echo '<tr id="fieldtr'.$id.'" >
											<td style="vertical-align:top;">
												'.get_pro_type_name($rel['product_type']).'
											</td>
											<td style="vertical-align:top;">
												'.$rel['product_name'].'
												'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
											</td>
											<td style="vertical-align:top;" class="text-center">
												'.$rel['product_hsn_code'].'
											</td>
											<td style="vertical-align:top;" class="text-center">
												'.$rel['product_qty'].'
											</td>				
														
											<td style="vertical-align:top" class="text-center">
												'.$rel['unit_name'].'
											</td>
											
											<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['total'].'"/>
											<td style="vertical-align:top">
												<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['pl_ordertrn_id'].',\' tbl_planning_ordertrn\',\'pl_ordertrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
												<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['pl_ordertrn_id'].',\' tbl_planning_ordertrn\',\'pl_ordertrn_id\');" id="fieldremove'.$i.'">X</button>
											</td>	
										</tr>';
										$i++;
									}
								}
							else{
								echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
								}
					echo '</table>			 
						</div>
                    </div>	';
		}
		else if(strtolower($POST['mode']) == "show_bom_product_data") {
			
				$query="select mst.*,product.product_name,cat.unit_name,product.product_name from  tbl_planning_ordertrn as mst left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id  where pl_ordertrn_status=0 and pl_order_id=".$POST['pl_order_id'];
			$result=$dbcon->query($query);
			echo ' <div class="form-group">
						<div class="col-md-3"></div>
						<div class="col-md-6 col-xs-11">
							<table cellspacing="10" style="border-spacing:10px;font-size:16px;" class="display table table-bordered table-striped">
								<tr id="field">
									<th class="text-center" style="font-size: 19px;color: #0a0a0a;" width="20%">Product Type</th>
									<th class="text-center" width="40%" style="font-size: 19px;color: #0a0a0a;">Product Name</th>
									<th class="text-center"width="13%" style="font-size: 19px;color: #0a0a0a;">Qty</th>
									<th class="text-center"width="13%" style="font-size: 19px;color: #0a0a0a;">Per</th>
									<th class="text-center"width="13%" style="font-size: 19px;color: #0a0a0a;">Action</th>
								</tr>';
								if(mysqli_num_rows($result)>0)
								{
									$i=1;
									while($rel=mysqli_fetch_assoc($result))
									{
				
									 echo '<tr id="fieldtr'.$id.'" style="color: #1a0865;font-weight: 600;" >
											<td style="vertical-align:top;">
												'.get_pro_type_name($rel['product_type']).'
											</td>
											<td style="vertical-align:top;" class="text-center">
												'.$rel['product_name'].'
												'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
											</td>
											<td style="vertical-align:top;" class="text-center">
												'.$rel['product_qty'].'
											</td>				
											<td style="vertical-align:top" class="text-center">
												'.$rel['unit_name'].'
											</td>
											<td style="vertical-align:top">';
												if($rel['bom_use_trn_status']==1){
													echo'<button type="button" title="Edit Bom" class="btn btn-round btn-warning btn-xs" onclick="show_bom_product_trn_data('.$rel['pl_ordertrn_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>';
												}else{
													echo '<button type="button" title="Add Bom" class="btn btn-round btn-danger btn-xs" onclick="load_bom_data('.$rel['pl_ordertrn_id'].');" id="fieldremove'.$i.'"> <strong>Add</strong> </button>';
												}
											echo '</td>	
											
											
										</tr>';
										$i++;
									}
								}
							else{
								echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
								}
					echo '</table>			 
						</div>
						<div class="col-md-3"></div>
						<div class="col-md-12" style="margin-top:10px;">
							<center>
								<a href="'.ROOT.'sales_order_list'.'" type="button" class="btn btn-danger">Back</a>
							</center>
						</div>
                    </div>	';
		}
		else if(strtolower($POST['mode']) == "show_bom_product_trn_data") {
			echo ' <div class="form-group">
						<div class="col-md-12">
							<input type="hidden" name="bom_sotrn_id" id="bom_sotrn_id" value="'.$POST['sales_order_trn_id'].'" />
							<div class="col-md-3">
								<div class="form-group">
									<label class="col-md-4 control-label" style="white-space:nowrap;">Main Product</label>
									<div class="col-md-8 col-xs-11">
										<select class="select2" name="up_main_pro" id="up_main_pro">
											<option  value="">Main Product</option>
										';
											getmainpro($dbcon,$POST['sales_order_trn_id'],0);
										echo '</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label class="col-md-4 control-label">Product Type</label>
									<div class="col-md-8 col-xs-11">
										<select class="select2" name="up_pro_type" id="up_pro_type" onChange="load_product_bom(this.value);">';
											echo getproducttype($dbcon,'');
										echo '</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label class="col-md-4 control-label">Product</label>
									<div class="col-md-8 col-xs-11">
										<select class="select2" name="up_pro" id="up_pro">
											<option value="">Choose Product</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label class="col-md-4 control-label">Qty</label>
									<div class="col-md-8 col-xs-11">
										<input type="number"  title="Enter Qty" min="0" id="up_add_qty" name="up_add_qty"  class="form-control" />
									</div>
								</div>
							</div>
							<div class="col-md-1">
								<input type="button"  name="ad" id="ad" onClick="return add_bom_field();"  class="btn btn-primary" value="Add"/>
							</div>
						</div>
						<div class="col-md-3"></div>
						<div class="col-md-6 col-xs-11">
							<table width="100%" class="maintable"  id="invoice_type" >
								<tr height="30px" style="font-size: 19px;color: #0c0c0c;">					
									<th  width="10%" style="text-align:center;border:1px solid;">
										<strong>SR. NO.</strong>
									</th>
									<th width="55%"  style="text-align:center;border:1px solid;" ><strong>Item Description</strong></th>
									<th width="17%" style="text-align:center;border:1px solid;"><strong>Quantity</strong></th>
									<th width="10%" style="text-align:center;border:1px solid;"><strong>Action</strong></th>
								</tr>';
								$qry="select * FROM `tbl_so_bomtrn` as trn 
								left join product_mst as product on product.product_id=trn.product_id 
								left join unit_mst as per on per.unitid=product.product_mst_unitid
								where so_bom_trn_status!=2 and sales_order_trn_id='$POST[sales_order_trn_id]' and parent_id='0'";
								$result=$dbcon->query($qry);		
								$i=1;$total=0;$discount=0;
								$cnt1=mysqli_num_rows($result);
								$cnt=1;
								while($row=mysqli_fetch_assoc($result))
								{
									$number="1.".$cnt;
									echo '
									<tr>';
										//get_so_tree_bom($dbcon,$row['product_id'],$row['parent_id'],0,$cnt,$sales_order_trn_id,$number,$row['product_qty'],$row['bom_trn_id']);
										
										get_so_tree_bom($dbcon,$row['product_id'],$row['parent_id'],0,$cnt,$POST['sales_order_trn_id'],$number,$row['product_qty'],$row['so_bom_trn_id']);
					
										echo '</tr>';
									$cnt++;$i++; 
									$total=$total+=$row['product_amount'];
									$totalsqr=$totalsqr+$row['sqr_ft'];
								}
							echo'</table>	 
						</div>
						<div class="col-md-3"></div>
						<div class="col-md-12" style="margin-top:10px;" >
							<center>
								<button type="button" class="btn btn-danger" id="save" name="save" onclick="show_bom_product_data();">Back</button>
							</center>
						</div>
                    </div>	';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.*,pro.product_name FROM ".$_POST['table']." as mst left join product_mst as pro on mst.product_id=pro.product_id WHERE ".$_POST['whereid']." = '$POST[id]'");
			$r = $q->fetch_assoc();
			$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['pl_ordertrn_status']=2;	
			$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_bom_data")
		{
			$row=array();
			$info['so_bom_trn_status']=2;	
			$updateid=update_record("tbl_so_bomtrn", $info,"so_bom_trn_id=".$POST['bomtrnid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=7";
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
		else if(strtolower($POST['mode'])== "load_bom_data")
		{
			$sales_order_trn_id=$POST['sales_order_trn_id'];
			
			$infobo['bom_use_trn_status']=1;	
			$updateid=update_record("tbl_planning_ordertrn",$infobo,"pl_ordertrn_id=".$sales_order_trn_id, $dbcon);

			//so data query
			$query12="select * from tbl_planning_ordertrn as mst 
				where pl_ordertrn_status!=2 and mst.pl_ordertrn_id=".$sales_order_trn_id."";
			$result12=$dbcon->query($query12);
			$rel12=mysqli_fetch_assoc($result12);
			
			//bom id data 
			$query1="select MAX(bom_id) as bid from tbl_bom as mst 
				where bom_status!=1 and mst.bom_product=".$rel12['product_id']."";
			$result1=$dbcon->query($query1);
			$rel1=mysqli_fetch_assoc($result1);
			
			//copy data
			 $query="select mst.*,product.product_name,product.product_id,product.product_mst_unitid	,u.unit_name from tbl_bomtrn as mst 
				left join product_mst as product on product.product_id=mst.product_id 
				left join unit_mst as u on u.unitid=product_mst_unitid	
				where bom_trn_status!=1 and mst.parent_id=0 and bom_id=".$rel1['bid']." order by bom_trn_id Desc";
				$old_qty='';
				$result=$dbcon->query($query);
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					load_bom_to_sobom($dbcon,$rel['bom_trn_id'],0,$sales_order_trn_id);
				}
			}
			echo 1;
		}
		else if(strtolower($POST['mode'])== "update_trn"){
			
			$info['product_qty']=$POST['qty'];	
			$updateid=update_record("tbl_so_bomtrn",$info,"so_bom_trn_id=".$POST['bom_trn_id'],$dbcon);
		}
		else if(strtolower($POST['mode'])== "fieldbomadd"){
			if($POST['up_main_pro']!=""){
					$query12="select * from tbl_so_bomtrn as mst 
					where so_bom_trn_status!=2 and mst.so_bom_trn_id=".$POST['up_main_pro']."";
					$result12=$dbcon->query($query12);
					$rel12=mysqli_fetch_assoc($result12);
				
					$level=$rel12['bom_level']+1;
				}else{
					$level=0;
				}
				
				$info['product_type']		= $POST['up_pro_type'];
				$info['sales_order_trn_id']	= $POST['bom_sotrn_id'];
				$info['product_id']			= $POST['up_pro'];
				$info['parent_id']			= $POST['up_main_pro'];
				$info['product_qty']		= $POST['up_add_qty'];
				$info['bom_level']			= $level;
				
				
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				
				$inserestimateid=add_record('tbl_so_bomtrn', $info, $dbcon);
			
		}
		
		
	
function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$i]='';
		$info['tax_amount'.$i]='';		
	}
	$info['total']=$rate_total;
	return $info;
}
?>