<?php 

session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Work Order";
$countryid='101';
$stateid='1';
$cityid='1';
	
	if(strpos($_SERVER['REQUEST_URI'], "planning_edit")==true){
		$mode="Edit";
		$sales_order_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_planning where pl_order_id=$sales_order_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$pl_order_no=$rel['pl_order_no'];
		$date=date('d-m-Y',strtotime($rel['pl_order_date']));
	}
	else if(strpos($_SERVER['REQUEST_URI'], "salesorderbom")==true){
		$mode="Bom";
		$sales_order_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_sales_order where sales_order_id=$sales_order_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$sales_order_no=$rel['sales_order_no'];
		$date=date('d-m-Y',strtotime($rel['pl_order_date']));
		$readonly="yes";
		$display="display:none";
	}
	else{
		$mode="Add";
		$date=date('d-m-Y');
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
		<style>
			.td1
				{
					text-align:left;
					vertical-align:center;
					border-right:1px solid;
					border-bottom:1px solid;
					border-left:1px solid;
					color: #1a0865;
					font-weight: 600;
					height: 45px;
				}
				.td2
				{
					padding-left:5px;
					border-right:1px solid;
					border-bottom:1px solid;
					vertical-align:center;
					color: #1a0865;
					font-weight: 600;
					height: 45px;
				}
				.td3
				{
					text-align:center;
					padding-right:10px;
					vertical-align:center;
					border-right:1px solid;
					border-bottom:1px solid;
					color: #1a0865;
					font-weight: 600;
					height: 45px;
				}
		</style>
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
									<h3> <?=$form .' '.$mode?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li ><a href="<?=ROOT.'planning_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								<?php if($mode=="Bom"){ echo "Sales Order Bom";}else{?>New <?=$form?><?php } ?>
									
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="planning_add" action="javascript:;" method="post" name="planning_add">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Work Order No *</label>
														<div class="col-md-6 col-xs-11">
															<input id="pl_order_no" name="pl_order_no" type="text" class="form-control" title="Enter Planning No" value="<?=$pl_order_no?>" placeholder="Planning Order No" required <?=($readonly=='yes') ? readonly:'';?> >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label">Work Order Date*</label>
														<div class="col-md-6 col-xs-11">
															<input id="pl_order_date" name="pl_order_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$date?>" placeholder="Sales Order Date" <?=($readonly=='yes') ? readonly:'';?>>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Ref No.</label>
														<div class="col-md-6 col-xs-11">
															<input id="ref_no" name="ref_no" type="text" class="form-control" title="Ref No." value="<?=$rel['ref_no']?>" placeholder="Ref No." <?=($readonly=='yes') ? readonly:'';?>>		
														</div>
													</div>
												</div>
											</div>
											
											<?php 
											if($mode=="Add" || $mode=="Edit")
											{ ?>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="product_list" class="display table table-bordered table-striped">
														<tr id="field">
															<th width="4%" class="text-center">Type</th>
															<th width="20%" class="text-center">Product Detail</th>
															<th width="8%" class="text-center">HSN Code</th>
															<th width="6%" class="text-center">Quantity</th>
															<th width="7%" class="text-center">Unit</th>
															<th width="5%" class="text-center"></th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td  style="vertical-align:top;">
																<select class="select2" name="product_type" id="product_type" onChange="load_product(this.value);" title="Select Product Type">
																	<?=getproducttype($dbcon,'');?>
																</select>
															</td>
															<td style="vertical-align:top;">
																<select class="select2" title="Select product" name="product_id" id="product_id"  onChange="load_productdetail(this.value)">
																	<option value="">Choose Product</option>
																	<?php //=getproduct($dbcon,0,'0,1,2,4')?>
																</select>
																<input type="button"  name="addproduct" id="addproduct" data-toggle="modal" data-target="#bs-example-modal-addproduct"  class="btn btn-primary" value="+"/>
																<br/><br/>
																<textarea id="product_des" name="product_des" class="form-control" ></textarea>
															</td>	
															<td style="vertical-align:top;">
																<input type="text"  title="Enter HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
															</td>
															<td style="vertical-align:top;">
																<input type="number"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control"/>
															</td>
															
															<td style="vertical-align:top;">
																<select class="select2"  title="Select Unit" name="unit_id" id="unit_id">
																	<?=getunit($dbcon,0);?>
																</select>
															</td>
															
															<td style="vertical-align:top;">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<?php } ?>
											<div id="sale_productdata"></div>
											<?php if($mode=="Add" || $mode=="Edit")
											{ ?>
											<div class="col-md-12">
												<div class="col-md-7">
													<div class="form-group">
													  <label class="col-md-2 control-label">Remarks </label>
															<div class="col-md-6 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div>
												</div>
												
											</div>
											
											<div class="col-md-12">
												<center>
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												<!--<button type="submit" class="btn btn-success" id="saveprint" name="saveprint" onClick="submit_estimate()">Save and Print</button> &nbsp;-->
												<a href="<?=ROOT.'sales_order_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>	
										<?php } ?>
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$rel['pl_order_id']?>' />
										<input type='hidden' name='save_print' id='save_print' value='' />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
									</form>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/add_cust.php');?>
			<?php include_once('../include/add_product.php');?>
			<?php include_once('../include/add_city.php');?>
			<?php include_once('../include/add_state.php');?>
			<?php include_once('../include/footer.php');?>
		</section>
			<?php include_once('../include/include_js_file.php');?>   
			<script src="<?=ROOT?>js/app/planning.js"></script>
			<script src="<?=ROOT?>js/app/customer.js"></script>
			<script src="<?=ROOT?>js/app/product_mst.js"></script>
			<script src="<?=ROOT?>js/app/city_mst.js"></script>
			<script src="<?=ROOT?>js/app/state_mst.js"></script>
			<script>
				//CKEDITOR.replace('quotation_condition');
				$(".select2").select2({
					width: '100%'
				});
					
				$("#product_id").select2({
					width: '83%'
				});
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
			</script>
			<?php 
			echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
			if($mode=="Add")
			{
				echo "<script>get_series_no() </script>";
			}
			if($mode=="Add" || $mode=="Edit"){
				echo "<script>show_data() </script>";
			}else if($mode=="Bom"){ ?>
				<script>
					$('#cust_id').select2('readonly',true);
				</script>
			<?php 	echo "<script>show_bom_product_data() </script>";
			}
			

			?>
	</body>
</html>
