<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../include/function_database_query.php");
   $form="Product Wise Process Opening Stock";
    $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   $countryid='101';
   $stateid='1';
   $cityid='1';
   $currency_id=$_SESSION['currency_id'];
   $conversion_rate = $_SESSION['currency_rate'];
   $vendor_reference='';$quotation_no='';$quotation_date='d-m-Y';

   //check permission for process type add
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        ADMINISTRATOR_DRAWING_CREATE,
        ADMINISTRATOR_DRAWING_UPDATE
   ]);
   $branch_id = $_SESSION['branch_id'];

   if(strpos($_SERVER['REQUEST_URI'], "drawingedit")==true){
      
      $back="po_list";
      $mode="Edit";$direct_add='0';$request=0;
      if(!in_array(ADMINISTRATOR_DRAWING_UPDATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
      }
      $drawing_id=$dbcon->real_escape_string($_REQUEST['id']);
      //$query="select dr.*,im.file_name,im.drawing_image_id  from tbl_drawing as dr left join tbl_drawing_image as im on dr.drawing_id=im.drawing_id where dr.drawing_id=$drawing_id";

      $query="select dr.* from tbl_drawing as dr where dr.drawing_id=$drawing_id";
      $rel=mysqli_fetch_assoc($dbcon->query($query)); 

      $revsql = "SELECT * FROM `tbl_revision` where drawing_id='".$drawing_id."' ORDER BY `tbl_revision`.`revision_id` DESC LIMIT 1";
      $rev_rel=mysqli_fetch_assoc($dbcon->query($revsql)); 
      $revison_number_val = $rev_rel['revision_number'];
      
      $vender_id=$rel['vender_id'];
      $revision_id = $rel['revision_id'];
   }
   else{
      $back="drawing_list";
      $mode="Add";
      $direct_add='0';$request=0;
      if(!in_array(ADMINISTRATOR_DRAWING_CREATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
      }
      
   }
	//echo getproduct_process_stock($dbcon,"");
   //echo $purchaseorder_id;
   ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
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
										<li><a href="<?=ROOT.'drawing_list'?>"><?=$form?> List</a></li>
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
											<div class="col-md-12">
												<div class="col-md-2"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Select Product*</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2 selproduct" title="Select product" name="product_id" id="product_id" onchange="load_product_process(this.value);" >
																<option value="">Choose Product</option>
																<?=getproduct_process_stock($dbcon,'');?>
															</select>
														</div>
													</div>
												</div>
												<!--<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Product Qty *</label>
														<div class="col-md-8 col-xs-11">
															<input id="product_qty" name="product_qty" type="number" class="form-control" title="Total Opening Stock Qty" value="" placeholder="Total Opening Stock" >
														</div>
													</div>
												</div>-->
											</div>
											<div class="col-md-12" id="sale_productdata">
											
											</div>
											<div class="col-md-12" style="margin-top: 20px;" >
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
													<a href="<?=ROOT.'drawing_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$drawing_id;?>' />   
											<input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
											<input type='hidden' name='revision_id' id='revision_id' value='<?=$rev_rel['revision_id']?>' />
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php //include_once('../include/add_vender.php');?>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/process_opening_stock.js?<?=time()?>"></script>
		<script>
			 $(".selproduct").select2({
				width: '100%',
				minimumInputLength: 3,
			});	
			
			$(".select2").select2({
				width: '100%'
			 });
			 
			 /*$("#product_id").select2({
				width: '86%'
			 });*/
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
	</body>
</html>
