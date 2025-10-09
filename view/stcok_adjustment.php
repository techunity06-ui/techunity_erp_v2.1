<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/function_database_query.php");
	$form="Stcok Adjustment";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER['REQUEST_URI'], "stcok_adjustment_edit")==true)
	{
		$mode="Edit";
		$stcok_adjustment_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_stcok_adjustment where stcok_adjustment_id=$stcok_adjustment_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$stcok_adjustment_date = date('d-m-Y',strtotime($rel['stcok_adjustment_date']));
		
	}
	else
	{
		$mode="Add";
		$stcok_adjustment_date=date('d-m-Y');
	}
	
	//echo $purchaseorder_id;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'stcok_adjustment_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="purchaseorder_add" action="javascript:;" method="post" name="purchaseorder_add">
										<div class="row">
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Stcok Adjustment No </label>
														<div class="col-md-6 col-xs-11">
															<input id="stcok_adjustment_no" name="stcok_adjustment_no" type="text" class="form-control" title="Date" value="<?=$rel['stcok_adjustment_no']?>" placeholder="Stcok Adjustment No" >
														</div>
													</div>	
												</div>	
												<div class="col-md-6">
													<div class="form-group">  	
														<label class="col-md-3 control-label" >Purchase Order date </label>
														<div class="col-md-5 col-xs-11">
															<input id="stcok_adjustment_date" name="stcok_adjustment_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$stcok_adjustment_date?>" placeholder="Stcok Adjustment Date">
														</div>
													</div>	
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;">
												<div class="form-group">
													<div class="col-md-2 col-xs-11"></div>
													<div class="col-md-8">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
															<tr id="field" >
																<th width="4%" class="text-center">Type</th>
																<th width="25%" class="text-center">Product</th>
																<th width="7%" class="text-center">Current Stock</th>
																<th width="7%" class="text-center">Adjustment Stock</th>
																<th width="5%" class="text-center"></th>
															</tr>
															<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
															<tr id="field1">
																<td style="vertical-align:top;">
																	<select class="select2" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type">
																		<?=getproducttype($dbcon,'');?>
																	</select>
																</td>
																<td style="vertical-align:top;">
																	<select class="select2" title="Select product" name="product_id" id="product_id" onchange="current_stock1(this.value);">
																		<option value="">Choose Product</option>
																		<?//=getproduct($dbcon,0,'0,1,2,4')?>
																	</select>
																	<br/><br/>
																	<textarea id="product_des" name="product_des" class="form-control" ></textarea>
																</td>	
																<td style="vertical-align:top;">
																	<input type="text" title="Current Stock" id="current_stock" name="current_stock" class="form-control" readonly />
																</td>
																<td style="vertical-align:top;">
																	<input type="number"  title="Enter Stock Qty" min="0" id="stock_qty" name="stock_qty"  class="form-control" onkeyup="find_stock_aju(this.value);" />
																	
																	<input type="hidden"  title="Enter Adjustment Qty" min="0" id="add_adjustment_qty" name="add_adjustment_qty" />
																	
																	<input type="hidden"  title="Enter Adjustment Qty" min="0" id="remove_adjustment_qty" name="remove_adjustment_qty" />
																</td>
																<td width="5%">
																	<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
																</td>
																	<input type='hidden' name='edit_id' id='edit_id' value='' />
															</tr>
														</table>
													</div>
												</div>
												<div class="col-md-12">
													<div class="col-md-2"></div>
													<div class="col-md-8">
														<div id="sale_productdata"></div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
													  <label class="col-md-3 control-label">Remarks </label>
															<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
												<div class="col-md-6"></div>	
											</div>
											<div class="col-md-12">
												<center>
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
							
												<a href="<?=ROOT.'stcok_adjustment_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
										</div>
							<!--Vendor row end-->	
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
										<input type='hidden' name='eid' id='eid' value='<?=$stcok_adjustment_id;?>' />	
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
			<script src="<?=ROOT?>js/app/stcok_adjustment.js?<?=time()?>"></script>
			<script>
				$(".select2").select2({
					width: '100%'
				});
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
				$(".form_datetime").datetimepicker({
					format: 'dd-mm-yyyy hh:ii',
					autoclose: true,
					todayBtn: true,
					pickerPosition: "bottom-left"

				});
			</script>
			<?
				if($mode=="Add"){
					echo "<script>show_data();</script>";
					echo "<script>get_series_no(15);</script>";
				}
			?>
	</body>
</html>
