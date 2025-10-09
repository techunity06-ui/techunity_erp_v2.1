<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Item";
	$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$comty=mysqli_fetch_assoc($dbcon->query($com));	
	//echo $_SESSION['branch_id'];
	//echo $_SERVER[REQUEST_URI];
	if(strpos($_SERVER[REQUEST_URI], "product_edit")==false) {
		$mode="Add";
	
	}
	else {
		$mode="Edit";
		$pro_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from product_mst where product_id=$pro_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$check_array=explode(",",$rel['product_check']);
		$check_array_setting=explode(",",$rel['product_setting_check']);
		//print_r($check_array_setting);
	}
	

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
	
	<style>
		
		.margin_row{
			
			margin-top:10px !important;
		}
		
		.margin_span{
			margin-left:10px !important;
			font-size:16px;
			vertical-align:middle;
		}
		
		.container input {
		  position: absolute;
		  opacity: 0;
		  cursor: pointer;
		  height: 0;
		  width: 0;
		}
				
				.checkmark {
		  position: absolute;
		  top: 0;
		  left: 0;
		  height: 25px;
		  width: 25px;
		  background-color: #eee;
		}

		/* On mouse-over, add a grey background color */
		.container:hover input ~ .checkmark {
		  background-color: #ccc;
		}

		/* When the checkbox is checked, add a blue background */
		.container input:checked ~ .checkmark {
		  background-color: #2196F3;
		}

		/* Create the checkmark/indicator (hidden when not checked) */
		.checkmark:after {
		  content: "";
		  position: absolute;
		  display: none;
		}

		/* Show the checkmark when checked */
		.container input:checked ~ .checkmark:after {
		  display: block;
		}

		/* Style the checkmark/indicator */
		.container .checkmark:after {
		  left: 9px;
		  top: 5px;
		  width: 5px;
		  height: 10px;
		  border: solid white;
		  border-width: 0 3px 3px 0;
		  -webkit-transform: rotate(45deg);
		  -ms-transform: rotate(45deg);
		  transform: rotate(45deg);
		}
		
		.img-wrap {
			position: relative;
		}
		.img-wrap .close {
			position: absolute;
			top: 2px;
			right: 2px;
			z-index: 100;
		}
		.head_margin
		{
			margin-bottom:10px;
		}
	</style>
	<script type="text/javascript" src="js/jquery.form.min.js"></script>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once('../include/include_top_menu.php');?>
     <!--sidebar start-->
      <?php include_once('../include/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper">
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
						  <h3>New <?=$form?>
						  <!--<a href="<?=ROOT.'import_product'?>" >
						  <button class="btn btn-primary btn-flat pull-right">Import <?=$form?></button></a>-->
						  </h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><a href="<?=ROOT.'product_list'?>"><?=$form?> List </a></li>
						  </ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--Customer overview start-->
		
		  <div class="row">
			<div class="col-sm-12">
				<section class="panel">
					<header class="panel-heading">
					  New <?=$form?> 
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>	
					<div class="panel-body">
					
					<form role="form" id="product_add" action="javascript:;" method="post" name="product_add">
						
						<div class="col-md-12" style="padding-top: 25px;">
							
							 <div class="col-md-12 margin_row">
								
								<div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Type" class="col-md-4 control-label">Product Type*</label>
										  <div class="col-md-8 col-xs-11">
												 
												<select class="select2" id="product_type" name="product_type" onchange="pro_status(this.value);get_product_code(this.value);">
													<option value="">--Select Product Type--</option>
													<option value="0" <?php if($mode=='Edit'){ if($rel['product_type']=='0'){ echo "selected"; } } ?>>FINISH PRODUCT</option>
													
													<option value="1" <?php if($mode=='Edit'){ if($rel['product_type']=='1'){ echo "selected"; } } ?>>ASSEMBLY PRODUCT</option>
													
													<option value="2" <?php if($mode=='Edit'){ if($rel['product_type']=='2'){ echo "selected"; } } ?>>SUB ASSEMBLY</option>
													
													<option value="3" <?php if($mode=='Edit'){ if($rel['product_type']=='3'){ echo "selected"; } } ?>>RAW MATERIAL</option>
													
													<option value="4" <?php if($mode=='Edit'){ if($rel['product_type']=='4'){ echo "selected"; } } ?>>FINISH PART</option>
													
													<option value="5" <?php if($mode=='Edit'){ if($rel['product_type']=='5'){ echo "selected"; } } ?>>BOI</option>
													
													<option value="6" <?php if($mode=='Edit'){ if($rel['product_type']=='6'){ echo "selected"; } } ?>>CAPITAL GOODS</option>
													
													<option value="7" <?php if($mode=='Edit'){ if($rel['product_type']=='7'){ echo "selected"; } } ?>>CONSUMABLE</option>
													<option value="8" <?php if($mode=='Edit'){ if($rel['product_type']=='8'){ echo "selected"; } } ?>>Service</option>
												</select>
												
												
										  </div>
									  </div>							 
								  </div>
								  <div class="col-md-4 typeled"><!-- add pathik -->
										<div class="form-group">
											<!--<div class="col-md-4" style="white-space:nowrap;"><strong>Select Ledger*</strong></div>-->
											<label for="Product Type" class="col-md-4 control-label">Select Ledger*</label>
											<div class="col-md-8">
												<select class="select2" name="ledger_id" id="ledger_id"  title="Select Ledger">
													<?=get_ledger($dbcon,$rel['ledger_id']," and l_group in (16,17)");?>
												</select>
											</div>
										</div>
								  </div>
								
							 </div>
							 
							 <div class="col-md-12 margin_row">
								  <div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Type" class="col-md-4 control-label">Product Name*</label>
										  <div class="col-md-8 col-xs-11">
										  <input type="text"  class="form-control" id="product_name" name="product_name" placeholder="Product Name"  value="<?=htmlspecialchars(stripcslashes($rel['product_name']))?>" />
										  </div>
									  </div>							 
								  </div>
							
								  <div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Description" class="col-md-4 control-label">Description</label>
										  <div class="col-md-8 col-xs-11">
											<textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description"><?=$rel['product_desc']?></textarea>
										  </div>
									  </div>
								  </div>
									
								  <div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Type" class="col-md-4 control-label">Item Code</label>
										  <div class="col-md-8 col-xs-11">
											<input type="text" class="form-control" id="product_icode" name="product_icode" placeholder="Item Code" value="<?=$rel['product_icode'];?>" readonly />
											
											<input type="hidden" class="form-control" id="product_icode_code" name="product_icode_code"  value="" readonly />
										  </div>
									  </div>
								</div>
							</div> 
							
							<div class="col-md-12 margin_row">
							
								 <div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Type" class="col-md-4 control-label">HSN Code</label>
										  <div class="col-md-8 col-xs-11">
											<input type="text" class="form-control" id="product_hsn" name="product_hsn" placeholder="HSN Code" value="<?=$rel['product_hsn']?>" required />
										  </div>
									  </div>
								  </div>
								  <div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Type" class="col-md-4 control-label">Sale Rate</label>
										  <div class="col-md-8 col-xs-11">
										  <input type="number" min="0" class="form-control" id="product_sale_rate" name="product_sale_rate" placeholder="Product Sale Rate" value="<?=$rel['product_sale_rate']?>" onkeypress="return isNumberKey(event)"  />
										  </div>
									  </div>
								  </div>
							  
								  <div class="col-md-4">
									  <div class="form-group">
										  <label for="Product Type" class="col-md-4 control-label">Purchase Rate</label>
										  <div class="col-md-8 col-xs-11">
										  <input type="number" min="0" class="form-control" id="product_purchase_rate" name="product_purchase_rate" placeholder="Product Purchase Rate" value="<?=$rel['product_purchase_rate']?>" onkeypress="return isNumberKey(event)"  />
										  </div>
									  </div>
								  </div>
							   </div>
							   
							   
							   
							
								<div class="col-md-12 margin_row">
								
									  <div class="col-md-4">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">GST Type</label>
												<div class="col-md-8 col-xs-11">
												<select class="select2" name="product_gst" id="product_gst"  title="Select Unit" required>
													<option value="">--Select GST Type--</option>
													<option value="including" <?php if($rel['product_gst']=='including'){ echo "selected"; }?>>Including</option>
													<option value="excluding" <?php if($mode=='Edit'){ if($rel['product_gst']=='excluding'){ echo "selected"; } } else { echo "selected"; } ?>>Excluding</option>
												</select>
												</div>
										  </div>
									  </div>
									  
									  <div class="col-md-4">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Sale GST</label>
												<div class="col-md-8 col-xs-11">
												<select class="select2" name="product_sale_gst" id="product_sale_gst"  title="Select Unit" required>
													
													<?=get_tax_percentage($dbcon,$rel['product_sale_gst']);?>
												</select>
												</div>
										  </div>
									  </div>
									  
									   <div class="col-md-4">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Purchase GST</label>
												<div class="col-md-8 col-xs-11">
												<select class="select2" name="product_purchase_gst" id="product_purchase_gst"  title="Select Unit" required>
													<?=get_tax_percentage($dbcon,$rel['product_purchase_gst']);?>
												</select>
												</div>
										  </div>
									  </div>									
								</div>								
							
								<div class="col-md-12 margin_row">
									
									
									
									<div class="col-md-4">  
										<div class="form-group">
												<label for="opening stock" class="col-md-4 control-label">Opening Stock</label>
												<div class="col-md-8 col-xs-11">
													<input type="number" name="product_opening" min="0" id="product_opening" class="form-control" placeholder="Opening Stock" value="<?=$mode=='Edit'?$rel['product_opening']:0?>" required />
												</div>
										</div>
									</div>
									
									<div class="col-md-4">  
										<div class="form-group">
											<label for="opening stock" class="col-md-4 control-label">Minimum Stock</label>
											<div class="col-md-8 col-xs-11">
												<input type="number" name="product_min_stock" min="0" id="product_min_stock" class="form-control" placeholder="Minimum Stock" value="<?=$mode=='Edit'?$rel['product_min_stock']:''?>" required />
											</div>
										</div>
									</div>
									
									<div class="col-md-4">  
										<div class="form-group">
											<label for="opening stock" class="col-md-4 control-label">Maximum Stock</label>
											<div class="col-md-8 col-xs-11">
												<input type="number" name="product_max_stock" min="0" id="product_max_stock" class="form-control" placeholder="Minimum Stock" value="<?=$mode=='Edit'?$rel['product_max_stock']:''?>" required />
											</div>
										</div>
									</div>
									
																	
								</div>
						
								<div class="col-md-12 margin_row">
								
									<div class="col-md-4">  
										<div class="form-group">
											<label for="opening stock" class="col-md-4 control-label">Select Category</label>
											<div class="col-md-8 col-xs-11">
												 <select class="select2" name="product_category" id="product_category">
													<?=get_all_category($dbcon,$rel['product_category']);?>
												 </select>
											</div>
										</div>
									</div>
									
									<div class="col-md-4">  
										<div class="form-group">
												<label for="opening stock" class="col-md-4 control-label">Product Barcode</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" name="product_barcode" id="product_barcode" value="<?=$rel['product_barcode']?>" />
												</div>
										</div>
									</div>	
									
									<div class="col-md-4"> 
										<?php if($_SESSION['user_type']=='1' || $_SESSION['user_type']=='2') { ?>
										<div class="form-group">
											<label class="col-md-4 control-label" style="">Select Branch </label>
											<div class="col-md-6 col-xs-11">
											
												<select class="select2" name="branchid" id="branchid">
												
													<?=get_branch($dbcon,$rel['branch_id'])?>	
													<option value="0" selected>Admin</option>							
												</select>
											</div>
											<?php } else {  ?>
												<input type="hidden" name="branchid" id="branchid" value="<?=$_SESSION['branch_id']; ?>" />	
											<?php } ?>
											
										</div>
										
									</div>
									
								</div>
								
								<div class="col-md-12 margin_row">
									
									<div class="col-md-4">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Making Time</label>
												<div class="col-md-8 col-xs-11">
												<input type="text" class="form-control" name="product_making_time" id="product_making_time" value="<?=$mode=='Edit'?$rel['product_making_time']:0?>" /> ( In Minute..)
												</div>
										  </div>
									</div>
									 
									<div class="col-md-4">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Product Specifaication</label>
												<div class="col-md-8 col-xs-11">
													<select class="form-control" name="product_specification" id="product_specification">
														<?= get_product_specification($dbcon,$rel['product_specification']); ?>
													</select>
												</div>
										  </div>
									</div>
									
									<div class="col-md-4">  
										<div class="form-group">
												<label for="opening stock" class="col-md-4 control-label">Product Valuation</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" name="product_opening_valuation" id="product_opening_valuation" value="<?=$rel['product_opening_valuation']?>" onkeypress="return isNumberKey(event)"  />
												</div>
										</div>
									</div>	
								
								</div>
								
									
								<div class="col-md-12 margin_row" style="margin-top:25px !important;">
									
									<div class="col-md-3">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Base Unit</label>
												<div class="col-md-8 col-xs-11">
												<select class="select2" name="product_base_unit" id="product_base_unit"  title="Select Unit" onchange="get_product_unit(this.value)" required>
													<?php if($mode=='Edit') { echo getunit($dbcon,$rel['product_base_unit']); } else { echo getunit($dbcon,3); } ?>
												</select>
												
												</div>
										  </div>
									  </div>
									  
									  <div class="col-md-3">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Qty</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" name="product_base_qty" id="product_base_qty" value="<?php if($mode=='Edit'){ echo $rel['product_base_qty'];  } else { ?> 1 <?php } ?>" required  />
												</div>
										  </div>
									  </div>
									  
									  <div class="col-md-3">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Conv. Unit</label>
												<div class="col-md-8 col-xs-11">
												<select class="select2" name="product_conv_unit" id="product_conv_unit"  title="Select Unit"  required>
													<?php if($mode=='Edit') { echo getunit($dbcon,$rel['product_conv_unit']); } else { echo getunit($dbcon,3); } ?>
												</select>
												
												</div>
										  </div>
									  </div>
									  
									  <div class="col-md-3">
										  <div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Qty</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" name="product_conv_qty" id="product_conv_qty" value="<?php if($mode=='Edit'){ echo $rel['product_conv_qty'];  } else { ?> 1 <?php } ?>" required  />
												</div>
										  </div>
									  </div>
									  
									
									
									
									<input type="hidden" name="mode" id="mode" value="<?php if($mode=='Add'){ echo "add"; } else { echo "edit"; } ?>" />
									
									<input type="hidden" name="eid_main" id="eid_main" value="<?php if($mode=='Edit'){ echo $rel['product_id']; } ?>" />
									
									
									
								</div>
							
								
							<div class="clearfix" style="margin-bottom:10px;">		
							</div>	
							
							<div class="col-md-5"></div>
							
							</div>
						

					</div>
				</section>
			</div>
		  </div>
		  
		  
		  <!--- Tab View -->
			
			
			<div class="row " style="background-color:white !important;padding:10px;">
			
				<div class="col-xs-2"> <!-- required for floating -->
				  <!-- Nav tabs -->
				  <ul class="nav nav-tabs tabs-left">
					
					<!--<li class="active"><a href="#tunit" data-toggle="tab" id="ltunit" >Unit Converter</a></li>-->
					
					<li class="active"><a href="#tbopen" data-toggle="tab" id="ltbopen" >Godown Opening</a></li>
					
					<li><a href="#timages" data-toggle="tab" id="ltimages">Images</a></li>
					
					<li><a href="#tpurchase" data-toggle="tab" id="ltpurchase">Purchase Party</a></li>
					
					<li><a href="#tjobpurchase" data-toggle="tab" id="ltjobwork"> Jobwork Party</a></li>
				
					<li><a href="#tparam" data-toggle="tab"  id="ltparam">QC Parameter</a></li>
					
					<li><a href="#tsetting" data-toggle="tab" id="ltreq">Product Setting</a></li>
					
					<li><a href="#tprocess" data-toggle="tab" id="ltprocess">Process List</a></li>
				  </ul>
				</div>

				<div class="col-xs-9">
				  <!-- Tab panes -->
				  <div class="tab-content">
					<!--
					<div class="tab-pane active" id="tunit" >
						
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;"  class="head_margin"><a style="border-bottom:dotted blue thin">Unit Converter</a></h3>
							</div>
						</div>
						
						<div class="row">
						
						
						
						<div class="col-md-12 margin_row">
									
								<table class="table table-bordered">
									
									<tr>
										
										<th>Alt.Qty</th>
										<th>Alt.Unit</th>
										<th>Base Qty</th>
										<th>Base Unit</th>
										<td></td>
									</tr>
									
									<tr>
										
										<td>
											<input type="text" class="form-control" name="utab_alt_qty" id="utab_alt_qty" onkeypress="return isNumberKey(event)"  />
										</td>
										<td>
											<select class="form-control" name="utab_alt_unit" id="utab_alt_unit">
												<option value="">--Select Unit--</option>
												<?=getunit($dbcon,0);?>
											</select>
										</td>
										<td><input type="text" class="form-control" name="utab_basic_qty" value="1" id="utab_basic_qty" onkeypress="return isNumberKey(event)"  /></td>
										<td>
											<select class="form-control" name="utab_basic_unit" id="utab_basic_unit">
												<option value="">--Select Unit--</option>
												<?=getunit($dbcon,0);?>
											</select>
										</td>
										<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_unit_converter()" id="add_unit" /></td>
										
										<input type="hidden" id="edit_id" value=""  />
										<input type="hidden" id="eid" value=""  />
									</tr>
									
								</table>
								
								<div class="table table-bordered" id="table_unit_converter"></div>
								
							</div>
							
						</div>
						
					</div>
					-->
					<div class="tab-pane active" id="tbopen"  >
						
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;"  class="head_margin"><a style="border-bottom:dotted blue thin">Godown Opening Stock</a></h3>
							</div>
							
							<div class="col-md-12">
								
									<table class="table table-bordered">
										<tr>
											<th>#</th>
											<th>Godown</th>
											<th>Stock</th>
											<th>Priority</th>
										</tr>
										<?php 
											$cnt=1;
											$selb=$dbcon->query("select * from mst_godown where g_status=0");
											while($rb=mysqli_fetch_array($selb))
											{
										?>
											<tr>
												<td><?php echo $cnt; ?></td>
												<td><?php echo $rb['gd_name']; ?></td>
												<td>
													<input type="text" class="form-control bstock" name="bstock[]" value="<?php echo get_stock_by_branch($dbcon,$rb['gd_id'],$rel['product_id']); ?>" onkeypress="return isNumberKey(event)"  />
													
													<input type="hidden" class="form-control bid" name="bid[]" value="<?php echo $rb['gd_id']; ?>" />
													
												</td>
												<td>
													<input type="text" class="form-control bpriority" name="bpriority[]" onkeypress="return isNumberKey(event)" />
												</td>
											
											</tr>
										<?php $cnt++; } ?>
										<input type="hidden" name="branch_mode" id="branch_mode" value="add_branch_stock"  />
										<tr>
											<td colspan="4" align="center">
												<input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_branch_stock()" id="add_bstock" />
											</td>
										</tr>
									</table>
								
							</div>
						</div>
					
					
					</div>
					
					<div class="tab-pane" id="timages" >
							
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Upload Product Images</a></h3>
							</div>
						</div>
					
					
							<table class="table table-bordered">
								<tr>
									<th>
										<input type="file" name="file" id="file" />
									<th>
									<th>
										<input type="button" name="" value="Upload" class="btn btn-info" onclick="add_product_image()" />
										
										<input type="hidden" name="img_mode" id="img_mode" value="add_product_image_temp" />
									</th>
								</tr>
							</table>
						
						 <span id="uploaded_image"></span>
					</div>
					
					<div class="tab-pane" id="tpurchase" >
					
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Purchase Party</a></h3>
							</div>
						</div>
						
						<div class="row">
						
							<div class="col-md-12 margin_row">
									
								<table class="table table-bordered">
									
									<tr>
										<th>Party Name</th>
										<th>Rate</th>
										<td></td>
									</tr>
									
									<tr>
										
										<td>
											
											<select class="select2" name="party_id" id="party_id">
												<?php  echo getcust($dbcon,$id) ?>
											</select>
										</td>
										<td>
											<input type="text" class="form-control" name="party_rate" id="party_rate" onkeypress="return isNumberKey(event)"  />
										</td>
										
										<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_party_purchase()" id="add_party_btn" /></td>
										
										<input type="hidden" id="edit_id_party" value=""  />
										<input type="hidden" id="eid_party" value=""  />
									</tr>
									
								</table>
								
								<div id="table_party_purchase"></div>
								
							</div>
							
						</div>
						
						
					</div>
					
					<div class="tab-pane" id="tjobpurchase" >
					
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Jobwork Party</a></h3>
							</div>
						</div>
						
						<div class="row">
						
							<div class="col-md-12 margin_row">
									
								<table class="table table-bordered">
									
									<tr>
										<th>Process Name</th>
										<th>Party Name</th>
										<th>Rate</th>
										<td></td>
									</tr>
									
									<tr>
										
										<td>
											<select class="select2" name="job_party_process_id" id="job_party_process_id">
												<?php echo get_all_process($dbcon,$id) ?>
											</select>
										</td>
										<td>
											
											<select class="select2" name="job_party_id" id="job_party_id">
												<?php  echo getcust($dbcon,$id) ?>
											</select>
										</td>
										<td>
											<input type="text" class="form-control" name="job_party_rate" id="job_party_rate" onkeypress="return isNumberKey(event)"  />
										</td>
										
										<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_job_party_purchase()" id="add_job_party_btn" /></td>
										
										<input type="hidden" id="edit_id_job_party" value=""  />
										<input type="hidden" id="eid_job_party" value=""  />
									</tr>
									
								</table>
								
								<div id="table_job_party_purchase"></div>
								
							</div>
							
						</div>
						
						
					</div>
					
					<div class="tab-pane" id="tparam">
						
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Parameter</a></h3>
							</div>
						</div>
						
						<div class="row">
						
							<div class="col-md-12 margin_row">
									
								<table class="table table-bordered">
									
									<tr>
										<th>Parameter Name</th>
										<th>Value</th>
										<td></td>
									</tr>
									
									<tr>
										
										<td>
											
											<select class="select2" name="param_id" id="param_id">
												<?php  echo get_all_parameter($dbcon,$id) ?>
											</select>
										</td>
										<td>
											<input type="text" class="form-control" name="param_value" id="param_value" />
										</td>
										
										<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_param_value()" id="add_param" /></td>
										
										<input type="hidden" id="edit_id_param" value=""  />
									</tr>
									
								</table>
								
								<div id="table_product_parameter"></div>
								
							</div>
							
						</div>
					
					
					
					</div>
					
					<div class="tab-pane" id="tsetting" >
						
						
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Setting</a></h3>
							</div>
						</div>
						
						<div class="row" style="margin-left:20px;">
						
							<div class="col-md-6 margin_row">
								
									<label class="container"><span class="margin_span">Process on Product</span>
									  <input type="checkbox"  name="product_setting_check[]" id="product_setting_check"  value="process_product" class="product_process" <?php if($mode=='Edit'){ if(in_array("process_product",$check_array_setting)) { echo "checked"; } } ?>>
									  <span class="checkmark"></span>
									</label>
									
							</div>
							
							<div class="col-md-6 margin_row">
							
									<label class="container"><span class="margin_span">QC For Product</span>
									  <input type="checkbox"  name="product_setting_check[]" id="product_setting_check"  value="product_qc" class="product_process" <?php if($mode=='Edit'){ if(in_array("product_qc",$check_array_setting)) { echo "checked"; } } ?>>
									  <span class="checkmark"></span>
									</label>
							</div>	
							
						</div>
					
					
					</div>
					
					<div class="tab-pane" id="tprocess">
						
						<div class="row">
							<div class="col-md-12">
								<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Product Process</a></h3>
							</div>
						</div>
						
						<div class="row">
						
							<div class="col-md-12 margin_row">
									
								<table class="table table-bordered">
									
									<tr>
										<th>Process Name</th>
										<th>Priority</th>
										<th>Type</th>
										<th>Time  (In Min.)</th>
										<th>Opening Stock</th>
										<td></td>
									</tr>
									
									<tr>
										
										<td>
											
											<select class="select2" name="process_id" id="process_id">
												<?php  echo get_all_process($dbcon,$id) ?>
											</select>
										</td>
										<td>
											<input type="text" class="form-control" name="process_priority" id="process_priority" />
										</td>
										<td>
											<select class="form-control" name="process_type" id="process_type">
												<option value="">--Select Process Type--</option>
												<option value="1">Inhouse</option>
												<option value="2">Outside</option>
											</select>
										</td>
										<td>
											<input type="text" class="form-control" name="process_time" id="process_time" />
										</td>
										<td>
											<input type="text" class="form-control" name="process_opening" id="process_opening" />
										</td>
										
										<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_process_value()" id="add_process" /></td>
										
										<input type="hidden" id="edit_id_process" value=""  />
									</tr>
									
								</table>
								
								<div id="table_product_process"></div>
								
							</div>
							
						</div>
					
					
					
					</div>
				  
				  </div>
				</div>

				<div class="clearfix"></div>

			  </div>

			 </div>

  
		  <!-- End Tab View -->
		  
		  <!--Customer overview end-->
          </section>
		  
		  <section>
			
				<div class="row" style="background-color:white !important;padding:10px;">
					<div class="col-md-4 col-md-offset-5">	
						<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
						<input type='hidden' name='form_mode' id='form_mode' value='<?php echo $mode; ?>' />				  
						<input type='hidden' name='pid' id='pid' value='<?php if($mode=='Edit'){ echo $rel['product_id']; } else { echo "0"; } ?>' />				  
						<input type='hidden' name='product_model' id='product_model' value='' />				  
						<button type="submit" class="btn btn-shadow btn-success" style="box-shadow: 3px 3px #61a642;">Submit</button>
					</div>
				</div>
			  
			  </form>
		  </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->

	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php include_once('../include/add_productinpro.php');?>  
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/product_mst.js?<?php echo time(); ?>"></script>
	
	
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
 var tableToExcel = (function() {
 var uri = 'data:application/vnd.ms-excel;base64,'
   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
   , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
   , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
 return function(table, name) {
   if (!table.nodeType) table = document.getElementById(table)
   var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
   window.location.href = uri + base64(format(template, ctx))
 }
})()
</script>
<?php
	echo "<script>pro_status(".$rel['product_type'].");</script>";
?>
</body>
</html>