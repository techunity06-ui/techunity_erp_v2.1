<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//check permission for get sales order details

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	MRP_GET_SALES_ORDER_SLUG_VIEW,MRP_GET_SALES_ORDER_SLUG_CREATE
]);
$companyConfiguration=getCompanyConfiguration($dbcon);

$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report_min_new") {
			
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		//$where_db = check_branch('so_trn', $branch_id);
		
		if(!empty($branch_id)){
			//$pro_branch=" and so_trn.production_branch_id=".$branch_id;
		}
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)

		$aColumns = array('pro.product_name','bsm.balty_name','so_trn.printing_balty','so_trn.product_id','IFNULL(sum(planning_qty-printing_allocate_qty),0) as pending_qty','IFNULL(sum(printing_allocate_qty-printing_allocate_approve_qty),0) as pending_allo_qty','IFNULL(sum(printing_allocate_approve_qty-printing_complate_qty),0) as pending_end_qty');


		$sIndexColumn = "so_trn.solid_production_planning_id";
		$isWhere = array("so_trn.status=0");

		$sTable = "solid_production_planning as so_trn";

		$isJOIN = array("left join product_mst as pro on pro.product_id=so_trn.product_id","left join solid_balty_mst as bsm on bsm.balty_id=so_trn.printing_balty");
		
		$hOrder = "pro.product_name";
		//$hGroupby = "so_trn.product_id,so_trn.printing_balty";
		$hGroupby =array("so_trn.product_id","so_trn.printing_balty");
		
		if($POST['stage1']==0){
			$having=" pending_qty > 0";
		}else if($POST['stage1']==1){
			$having=" pending_allo_qty > 0";
		}else{
			$having=" pending_end_qty > 0";
		}
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		
		//print_r($sqlReturn);
		foreach($sqlReturn as $row) {

			$row_data = array();
			
			$row_data[] = $row['product_name'];
			$row_data[] = $row['balty_name'];
			
			if($POST['stage1']==0){
				$row_data[] = $row['pending_qty'];
			}else if($POST['stage1']==1){
				$row_data[] = $row['pending_allo_qty'];
			}else{
				$row_data[] = $row['pending_end_qty'];
			}

			if($POST['stage1']==0){
				$view_desc='';
			}else if($POST['stage1']==1){
				$view_desc='<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_allo_modal('.$row['product_id'].','.$row['printing_balty'].')"><i class="fa fa-eye"></i></button>';
			}else{
				$view_desc='<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_exe_end_modal('.$row['product_id'].','.$row['printing_balty'].')"><i class="fa fa-eye"></i></button>';
			}
			//$view_desc='<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_so_trn_modal('.$row['printing_material'].','.$row['extrusion_size'].')"><i class="fa fa-eye"></i></button>';
			
			$row_data[] = $view_desc;

			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );

	}else if(strtolower($POST['mode']) == "preview_solid_allocate") {
		$html='<table cellpadding="5" cellspacing="5" border="1" style="font-size: 16px; font-family: Proxia Nova; border: 1;">';
		$html.='<tr>
							<td width="20%">Batch No</td>
							<td width="20%">Stock</td>
							<td width="20%">Action</td>
						</tr>';
			$q="select * from solid_production_planning as gd 
		where gd.status=0 and gd.product_id=".$POST['product_id']." and gd.printing_balty=".$POST['balty']." and printing_allocate_qty>printing_allocate_approve_qty";
		$rel=$dbcon->query($q);
		while($row=mysqli_fetch_array($rel)){
			 $q1="select gd.*,sto.batch_no from tbl_reserve_stock as gd 
				left join tbl_stock_trn as sto on sto.stock_id=gd.stock_id
				where gd.stock_status=0 and gd.ref_name='exe' and gd.stock_flage=1 and gd.p_id=".$row['solid_production_planning_id']." and gd.base_stock>gd.approve_base_stock";
			$rel1=$dbcon->query($q1);
			while($row1=mysqli_fetch_array($rel1)){
				$html.='<tr>
							<td>'.$row1['batch_no'].'</td>
							<td>'.$row1['base_stock'].'</td>
							<td id="res'.$row1['reserve_id'].'">
								<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="reserve_exe('.$row1['reserve_id'].')">Allocate</button>
							</td>
						</tr>';
			}
		}
		$html.='</table>';
		$rw['html']=$html;
		echo json_encode($rw);
	}else if(strtolower($POST['mode']) == "reserve_exe") {
		$q1="select gd.reserve_id,gd.base_stock,gd.approve_base_stock,gd.p_id,spa.printing_allocate_approve_qty,spa.solid_production_planning_id from tbl_reserve_stock as gd 
				left join solid_production_planning as spa on spa.solid_production_planning_id=gd.p_id
				where gd.reserve_id=".$POST['reserve_id'];
			$rel1=$dbcon->query($q1);
			$row1=mysqli_fetch_array($rel1);

			$info['printing_allocate_approve_qty']= $row1['printing_allocate_approve_qty']+$row1['base_stock'];
				$updateid_k=update_record("solid_production_planning",$info,"solid_production_planning_id=".$row1['solid_production_planning_id'],$dbcon);
				
				$info1['approve_base_stock']= $row1['approve_base_stock']+$row1['base_stock'];
				$info1['approve_convert_stock']= $info1['approve_base_stock'];
				$updateid_k1=update_record("tbl_reserve_stock",$info1,"reserve_id=".$row1['reserve_id'],$dbcon);
			if($updateid_k){
				$arr['msg']=1;
			}else{
				$arr['msg']=0;
			}
			echo json_encode($arr);
			
	}else if(strtolower($POST['mode']) == "open_exe_end_modal") {
		$q="select pro.product_name,bsm.balty_name,IFNULL(sum(printing_allocate_approve_qty-printing_complate_qty),0) as pending_qty from solid_production_planning as gd 
		left join product_mst as pro on pro.product_id=gd.printing_material
		left join solid_balty_mst as bsm on bsm.balty_id=gd.printing_balty
		where gd.status=0 and gd.product_id=".$POST['product_id']." and gd.printing_balty=".$POST['balty']." group by gd.product_id,gd.printing_balty";
		$rel=$dbcon->query($q);
		$row=mysqli_fetch_array($rel);
		echo json_encode($row);
		
	}else if(strtolower($POST['mode']) == "save_solid_exe_planning") {
		$end_qty=$_POST['finish_qty'];

		$info_dil['product_id']			= $_POST['product_id'];
		$info_dil['balty']				= $_POST['balty'];
		$info_dil['end_qty']			= $end_qty;

		$info_dil['user_id']				= $_SESSION['user_id'];
		$info_dil['cdate']					= date("Y-m-d h:i:s");
		$info_dil['company_id']				= $_SESSION['company_id'];
		
		$inserid_k=add_record("tbl_printing_end",$info_dil,$dbcon);

		//stock entry
		$rolsize=$_POST['rolsize'];
		$rolqty=$_POST['rolqty'];
		for($i=0;$i<count($rolsize);$i++)
		{
			$rsize=$rolsize[$i];
			$rqty=$rolqty[$i];
			for($p=0;$p<$rqty;$p++)
			{
				$q1="select product_base_unit,product_conv_unit from product_mst as gd 
				where gd.product_id=".$_POST['product_id'];
				$rel1=$dbcon->query($q1);
				$row1=mysqli_fetch_array($rel1);

				$batch_no1 = get_batch_no($dbcon,$_POST['product_id']);
				update_batch_no($dbcon,$_POST['product_id']);

				$info_dil12['stock_date']		= date("Y-m-d");
				$info_dil12['product_id']		= $_POST['product_id'];
				$info_dil12['base_unit']		= $row1['product_base_unit'];
				$info_dil12['base_stock']		= $rsize;
				$info_dil12['convert_unit']		= $row1['product_conv_unit'];
				$info_dil12['convert_stock']	= $rsize;
				$info_dil12['stock_flage']		= 1;
				$info_dil12['godown_id']		= 1;
				$info_dil12['ref_name']			= "printing";
				$info_dil12['ref_id']			= $inserid_k;
				$info_dil12['cdate']			= date("Y-m-d h:i:s");
				$info_dil12['user_id']			= $_SESSION['user_id'];
				$info_dil12['company_id']		= $_SESSION['company_id'];
				$info_dil12['batch_no']			= $batch_no1;
				$inserid_ks2=add_record("tbl_stock_trn",$info_dil12,$dbcon);
			}
		}


		$q="select IFNULL(printing_allocate_approve_qty-printing_complate_qty,0) as pending_qty,solid_production_planning_id,printing_complate_qty from solid_production_planning as gd 
		where gd.status=0 and gd.product_id=".$_POST['product_id']." and gd.printing_balty=".$_POST['balty'];
		$rel=$dbcon->query($q);
		while($row=mysqli_fetch_array($rel))
		{
			if($row['pending_qty']>0)
			{
				if($end_qty>0)
				{
					if($row['pending_qty']>=$end_qty)
					{
						$useqty=$end_qty;
					}else
					{
						$useqty=$row['pending_qty'];
					}
					$end_qty=$end_qty-$useqty;
					$info['printing_complate_qty']= $row['printing_complate_qty']+$useqty;
					$updateid_k=update_record("solid_production_planning",$info,"solid_production_planning_id=".$row['solid_production_planning_id'],$dbcon);	

					$info_dil1['printing_end_id']						= $inserid_k;
					$info_dil1['solid_production_planning_id']			= $row['solid_production_planning_id'];
					$info_dil1['end_qty']								= $useqty;

					$info_dil1['user_id']				= $_SESSION['user_id'];
					$info_dil1['cdate']					= date("Y-m-d h:i:s");
					$info_dil1['company_id']			= $_SESSION['company_id'];
					
					$inserid_ks=add_record("tbl_printing_end_trn",$info_dil1,$dbcon);
					//deduction start
					$ddqty=$info_dil1['end_qty'];
					$q2="select * from tbl_reserve_stock as gd 
					where gd.stock_status=0 and stock_flage=1 and cast(approve_base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and gd.ref_name='exe' and gd.p_id=".$info_dil1['solid_production_planning_id'];
					$rel2=$dbcon->query($q2);
					while($row2=mysqli_fetch_array($rel2))
					{
						$ptqty=$row2['approve_base_stock']-$row2['used_base_stock'];
						if($ddqty>0 && $ptqty>0)
						{
							if($ptqty>=$ddqty){
								$rusqty=$ddqty;
							}else{
								$rusqty=$ptqty;
							}
							$ddqty=$ddqty-$rusqty;
							$info_dil13['reserve_date']		= date("Y-m-d");
							$info_dil13['product_id']		= $row2['product_id'];
							$info_dil13['godown_id']		= 1;
							$info_dil13['base_unit']		= $row2['base_unit'];
							$info_dil13['base_stock']		= $rusqty;
							$info_dil13['convert_unit']		= $row2['convert_unit'];
							$info_dil13['convert_stock']	= $rusqty;
							$info_dil13['stock_flage']		= 2;
							$info_dil13['ref_name']			= "printing";
							$info_dil13['ref_id']			= $inserid_k;
							$info_dil13['cdate']			= date("Y-m-d h:i:s");
							$info_dil13['user_id']			= $_SESSION['user_id'];
							$info_dil13['company_id']		= $_SESSION['company_id'];
							$info_dil13['p_id']				= $row2['p_id'];
							$info_dil13['stock_id']			= $row2['stock_id'];
							$info_dil13['perent_id']		= $row2['reserve_id'];
							$inser=add_record("tbl_reserve_stock",$info_dil13,$dbcon);
							
							$infor1['used_base_stock']= $row2['used_base_stock']+$info_dil13['base_stock'];
							$infor1['used_convert_stock']= $row2['used_convert_stock']+$info_dil13['base_stock'];
							$updateid_k=update_record("tbl_reserve_stock",$infor1,"reserve_id=".$row2['reserve_id'],$dbcon);


							$info_dil14['stock_date']		= date("Y-m-d");
							$info_dil14['product_id']		= $info_dil13['product_id'];
							$info_dil14['base_unit']		= $info_dil13['base_unit'];
							$info_dil14['base_stock']		= $info_dil13['base_stock'];
							$info_dil14['convert_unit']		= $info_dil13['convert_unit'];
							$info_dil14['convert_stock']	= $info_dil13['convert_stock'];
							$info_dil14['stock_flage']		= 2;
							$info_dil14['godown_id']		= 1;
							$info_dil14['ref_name']			= "printing";
							$info_dil14['ref_id']			= $inserid_k;
							$info_dil14['cdate']			= date("Y-m-d h:i:s");
							$info_dil14['user_id']			= $_SESSION['user_id'];
							$info_dil14['company_id']		= $_SESSION['company_id'];
							//$info_dil14['batch_no']			= $batch_no1;
							$info_dil14['perent_id']		= $info_dil13['stock_id'];
							$info_dil14['reserve_id']		= $inser;
							
							$inserid_ks2d=add_record("tbl_stock_trn",$info_dil14,$dbcon);

						}
						
					}
					//deduction end

					//reserve start
						$rrqty=$info_dil1['end_qty'];
						$q4="select gd.*,spa.product_id,pro.product_base_unit,pro.product_conv_unit from solid_production_planning as gd 
						left join product_mst as pro on pro.product_id=gd.product_id
						where gd.solid_production_planning_id=".$info_dil1['solid_production_planning_id'];
						$rel4=$dbcon->query($q4);
						$row4=mysqli_fetch_array($rel4);
						
							$q6="select * from tbl_sales_order_production_trn as gd
								where sales_order_production_status=0 and cast(product_qty AS DECIMAL(50,5))>cast(allocate_qty AS DECIMAL(50,5)) and gd.request_id=".$info_dil1['solid_production_planning_id'];
								$rel6=$dbcon->query($q6);
								while($row6=mysqli_fetch_array($rel6))
								{
									
									$ppshqty=$row6['product_qty']-$row6['allocate_qty'];
									if($ppshqty>0 && $rrqty>0)
									{
										if($ppshqty>=$rrqty){
											$sssqty=$rrqty;
										}else{
											$sssqty=$ppshqty;
										}
										$rrqty=$rrqty-$sssqty;
										$q7="select * from tbl_stock_trn as gd 
										where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and ref_name='printing' and gd.ref_id=".$inserid_k;
										$rel7=$dbcon->query($q7);
										while($row7=mysqli_fetch_array($rel7))
										{
											if($sssqty>0)
											{
												$abcqty=$row7['base_stock']-$row7['used_base_stock'];
												if($sssqty>=$abcqty){
													$stuqty=$abcqty;
												}else{
													$stuqty=$sssqty;
												}
												$sssqty=$sssqty-$stuqty;
												$info_dil15['reserve_date']				= date("Y-m-d");
												$info_dil15['product_id']				= $row7['product_id'];
												$info_dil15['godown_id']				= 1;
												$info_dil15['base_unit']				= $row7['base_unit'];
												$info_dil15['base_stock']				= $stuqty;
												$info_dil15['convert_unit']				= $row7['convert_unit'];
												$info_dil15['convert_stock']			= $stuqty;
												$info_dil15['stock_flage']				= 1;
												$info_dil15['ref_name']					= "printing";
												$info_dil15['ref_id']					= $inserid_k;
												$info_dil15['cdate']					= date("Y-m-d h:i:s");
												$info_dil15['user_id']					= $_SESSION['user_id'];
												$info_dil15['company_id']				= $_SESSION['company_id'];
												$info_dil15['p_id']						= $info_dil1['solid_production_planning_id'];
												$info_dil15['stock_id']					= $row7['stock_id'];
												$info_dil15['sales_order_trn_id']		= $row6['sales_ordertrn_id'];
												$info_dil15['temp_stock_allocate']		= 1;
												
												
												$inserd=add_record("tbl_reserve_stock",$info_dil15,$dbcon);
												$infor1wsc['used_base_stock']= $row7['used_base_stock']+$stuqty;
												$infor1wsc['used_convert_stock']= $row7['used_convert_stock']+$stuqty;
												$updateid_k=update_record("tbl_stock_trn",$infor1wsc,"stock_id=".$row7['stock_id'],$dbcon);

												

													$infor1wp['allocate_qty']= $row6['allocate_qty']+$stuqty;
													$updateid_kx=update_record("tbl_sales_order_production_trn",$infor1wp,"sales_order_production_trn_id=".$row6['sales_order_production_trn_id'],$dbcon);
											}
										}
									}
								}
							//reserve end
					}
				}
			}
		if($inserid_k){
			$arr['msg']=1;
			$arr['id']=$inserid_k;
		}else{
			$arr['msg']=0;
		}	
		echo json_encode($arr);
	}else if(strtolower($POST['mode']) == "save_mixing") {
		$end_qty=$_POST['mixing_finish_qty'];

		$info_dil['extrusion_material']			= $_POST['product_id'];
		$info_dil['mixing_batch_size']			= $_POST['batch_size_id'];
		$info_dil['end_qty']					= $end_qty;

		$info_dil['user_id']				= $_SESSION['user_id'];
		$info_dil['cdate']					= date("Y-m-d h:i:s");
		$info_dil['company_id']				= $_SESSION['company_id'];
		
		$inserid_k=add_record("tbl_mixing_end",$info_dil,$dbcon);

		$q="select IFNULL(req_batch-complate_batch,0) as pending_qty,solid_production_planning_id,complate_batch,calculation_batch_qty from solid_production_planning as gd 
		where gd.status=0 and gd.extrusion_material=".$_POST['product_id']." and gd.mixing_batch_size=".$_POST['batch_size_id'];
		$rel=$dbcon->query($q);
		while($row=mysqli_fetch_array($rel)){
			if($end_qty>0){
				if($end_qty>=$row['pending_qty']){
					$entry_qty=$row['pending_qty'];
				}else{
					$entry_qty=$end_qty;
				}
				$end_qty=$end_qty-$entry_qty;
	
				$info['complate_batch']= $row['complate_batch']+$entry_qty;
				$updateid_k=update_record("solid_production_planning",$info,"solid_production_planning_id=".$row['solid_production_planning_id'],$dbcon);	

				$info_dil1['mixing_end_id']							= $inserid_k;
				$info_dil1['solid_production_planning_id']			= $row['solid_production_planning_id'];
				$info_dil1['end_qty']								= $entry_qty;

				$info_dil1['user_id']				= $_SESSION['user_id'];
				$info_dil1['cdate']					= date("Y-m-d h:i:s");
				$info_dil1['company_id']			= $_SESSION['company_id'];
				
				$inserid_ks=add_record("tbl_mixing_end_trn",$info_dil1,$dbcon);
				$cal_batch=$row['calculation_batch_qty'];
			}
		}
		mixing_stock_effects($dbcon,$inserid_k,$info_dil['end_qty'],$cal_batch);
	}

	function mixing_stock_effects($dbcon,$mixing_id,$mixing_qty,$cal_batch){
		$q="select gd.extrusion_material,product_base_unit,product_conv_unit from tbl_mixing_end as gd 
			left join product_mst as pro on pro.product_id=gd.extrusion_material
		where gd.status=0 and gd.mixing_end_id=".$mixing_id;
		$rel=$dbcon->query($q);
		$row=mysqli_fetch_array($rel);
		
		for($i=0;$i<$mixing_qty;$i++)
		{
			$batch_no1 = get_batch_no($dbcon,$row['extrusion_material']);
			update_batch_no($dbcon,$row['extrusion_material']);

			$info_dil1['stock_date']	= date("Y-m-d");
			$info_dil1['product_id']	= $row['extrusion_material'];
			$info_dil1['base_unit']		= $row['product_base_unit'];
			$info_dil1['base_stock']	= $cal_batch;
			$info_dil1['convert_unit']	= $row['product_conv_unit'];
			$info_dil1['convert_stock']	= $cal_batch;
			$info_dil1['stock_flage']	= 1;
			$info_dil1['godown_id']		= 1;
			$info_dil1['ref_name']		= "mixing";
			$info_dil1['ref_id']		= $mixing_id;
			$info_dil1['cdate']			= date("Y-m-d h:i:s");
			$info_dil1['user_id']		= $_SESSION['user_id'];
			$info_dil1['company_id']	= $_SESSION['company_id'];
			$info_dil1['batch_no']		= $batch_no1;
			$inserid_ks=add_record("tbl_stock_trn",$info_dil1,$dbcon);
			reserve_mixing($dbcon,$mixing_id,$inserid_ks,$info_dil1['base_stock']);
		}
		
	}
	function reserve_mixing($dbcon,$mixing_id,$stock_id,$stock_qty){
		$q="select IFNULL(end_qty-reserve_qty,0) as pending_qty,miz.extrusion_material,product_base_unit,product_conv_unit,mixing_end_trn_id from tbl_mixing_end_trn as gd 
			left join tbl_mixing_end as miz on miz.mixing_end_id=gd.mixing_end_id
			left join product_mst as pro on pro.product_id=miz.extrusion_material
			where gd.status=0 and gd.mixing_end_id=".$mixing_id;
		$rel=$dbcon->query($q);
		while($row=mysqli_fetch_array($rel)){
			if($row['pending_qty']>0){
				$info_dil1['reserve_date']		= date("Y-m-d");
				$info_dil1['product_id']		= $row['extrusion_material'];
				$info_dil1['godown_id']			= 1;
				$info_dil1['base_unit']			= $row['product_base_unit'];
				$info_dil1['base_stock']		= $stock_qty;
				$info_dil1['convert_unit']		= $row['product_conv_unit'];
				$info_dil1['convert_stock']		= $stock_qty;
				$info_dil1['stock_flage']		= 1;
				$info_dil1['ref_name']			= "mixing";
				$info_dil1['ref_id']			= $mixing_id;
				$info_dil1['cdate']				= date("Y-m-d h:i:s");
				$info_dil1['user_id']			= $_SESSION['user_id'];
				$info_dil1['company_id']		= $_SESSION['company_id'];
				$info_dil1['p_id']				= $row['solid_production_planning_id'];
				$info_dil1['stock_id']			= $stock_id;
				$inserid_ks=add_record("tbl_reserve_stock",$info_dil1,$dbcon);

				$info['reserve_qty']= $row['reserve_qty']+1;
				$updateid_k=update_record("tbl_mixing_end_trn",$info,"mixing_end_trn_id=".$row['mixing_end_trn_id'],$dbcon);	
			}
		}
	}
	
?>


