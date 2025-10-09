<?php 
	session_start();
	include('../include/urlfile.php');
	$form = 'Paking';

	$branch_id = $_SESSION['branch_id'];
	$companyID = $_SESSION['company_id'];
	if(strpos($_SERVER[REQUEST_URI], "paking_edit")==true){
		$mode="Edit";
		$general_stock_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$query="select * from so_paking where so_paking_id =$general_stock_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$general_stock_no = $rel['so_paking_no'];
		$general_stock_date ="";
		if($rel['so_paking_date']!="1970-01-01" && $rel['so_paking_date']!="0000-00-00")
		{
			$general_stock_date=date('d-m-Y',strtotime($rel['so_paking_date']));
		}
		$cust_id=$rel['so_paking_cust_id'];
		$back="paking_list";
	}else if(strpos($_SERVER[REQUEST_URI], "paking_add_sin")==true){
		$mode="Add";
		$cust_id=$dbcon->real_escape_string($_REQUEST['cust_id']);
		$so_id=$dbcon->real_escape_string($_REQUEST['sotrn_id']);
		//$general_stock_no = load_common_no($dbcon,50);
		$general_stock_no = date('hisdmY');
		$general_stock_date = date('d-m-Y');
		$back="paking_pending_list";
		$viewmode="addsin";
	}else {
		$mode="Add";
		//$general_stock_no = load_common_no($dbcon,50);
		$general_stock_no = date('hisdmY');
		$general_stock_date = date('d-m-Y');
		$back="stock_general_list";
	}
	//$max_followup_date = MAX_FOLLOWUP_DATE;
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?=$form?></title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.deduct_stock_background{
				background-color : #ff9494;
				color: black;
			}
			.additive_stock_background{
				background-color: #3bc73bab;
				color: black;
			}
			th{
				text-align: center;
			}
		</style>
		<script>
    // Function to prevent form submission on Enter key press
    function preventFormSubmit(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        check_qty();
      }
    }
  </script>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php //include_once('../include/equick_link.php');?>
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										
										<li><a href="<?=ROOT.INVENTORY_ROOT.'paking_list'?>"><?=$form?> List</a></li>
										
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="stock_general_add" action="javascript:;" method="post" name="stock_general_add" enctype="multipart/form-data">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Paking No </label>
														<div class="col-md-6 col-xs-11">
															<input type="text" name="paking_no" id="paking_no" class="form-control" title="Packing No" value="<?=$general_stock_no?>" placeholder="Packing No">
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Paking Date </label>
														<div class="col-md-6 col-xs-11">
															<input type="text" name="paking_date" id="paking_date" class="form-control default-date-picker" title="Packing Date" value="<?=$general_stock_date?>" placeholder="Packing Date"> 
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Company * </label>
														<div class="col-md-6 col-xs-11">
															<select class="select2" name="cust_id" id="cust_id" onChange="get_sales_order(this.value);load_pro_table();">
																<?=get_party_for_paking($dbcon,$cust_id);?>	
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;"></div>	

											<div class="col-md-12">
												
													<table class="table table-bordered ">
														<thead>
															<tr>
																<th colspan="5" style="text-align: center;" class="additive_stock_background"> So Wise Paking</th>
															</tr>
															
															<tr>
																<th style="width:30%;">Sales Order No</th>
																<th style="width:30%;">Product Name</th>
																<th style="width:20%;">Batch No</th>
																<th style="width:10%;">Qty</th>
																<th style="width:10%;">Action</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td style="max-width:150px">
																	<select class="select2" name="salesorderid" id="salesorderid" onChange="get_sales_product(this.value);">
																		<?php //=getcust($dbcon,$cust_id,$sales_party_show,0);?>	
																	</select>
																</td>
																<td style="max-width:150px">
																	<select class="select2" name="sales_order_trn_id" id="sales_order_trn_id" onChange="get_product_pen_qty(this.value);">
																		<?php //=getcust($dbcon,$cust_id,$sales_party_show,0);?>	
																	</select>
																	<span id="so_pro_pending_qty" style="font-weight: 600;color: red;"></span> <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="unit_show">  </span>
																</td>
																<td>
																	<div>
			                                                            <input type="text" title="Enter Batch No" id="batch_no" name="batch_no" class="form-control" onblur="check_qty();" >
			                                                        </div>
																</td>
																<td>
																	<div>
			                                                            <input type="text" title="Enter Qty" min="0" id="qty" name="qty" class="form-control numbersOnly" >
			                                                        </div>
																</td>
																<td>
																	<input type="hidden" name="edit_in_id" id="edit_in_id">
																	
																	<!-- <input type="button" id="batch_wise_in_st" value="Add" style="display: block;" class="btn btn-primary" onclick="batch_wise_in_stock_open()"> -->
																	<input type="button" id="batch_wise_in_st" value="Add" style="display: block;" class="btn btn-primary" onclick="add_entry()">
																</td>
															</tr>

														</tbody>		
													</table>
	</form>
													<div class="col-lg-12" style="margin-top:10px" id="stock_in_detail"></div>
												
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['so_paking_remark']?></textarea> 
														</div>
													</div> 
												</div>
											</div>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$general_stock_id?>' />
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											
											<div class="clearfix"></div>	
											<div class="col-md-12" style="margin-top:10px;text-align: center;">
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
												
												<a href="<?=ROOT.INVENTORY_ROOT.'paking_list'?>" type="button" class="btn btn-danger">Cancel</a>
											</div>
										</div>
								
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		 
		<?php //include_once($include1.'add_batch_data.php');?>   
		<?php include_once($include1.'add_paking_batch.php');?>   
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/paking.js?<?=time()?>"></script>
		<!-- <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script> -->

		<script>
			$(".select2").select2({
				width: '100%'
			});

			
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
			
         
		</script> 
		<?php 
			if($viewmode=="addsin"){
				echo"<script>get_sales_order(".$cust_id.",".$so_id.");</script>";
				echo"<script>load_pro_table();</script>";
				echo"<script>get_sales_product(".$so_id.");</script>";
				
			}
			
		?>
		<script>
    document.querySelector('form').addEventListener('keydown', preventFormSubmit);
  </script>
	</body>
</html>