<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Job Card";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	//$ver = (float)phpversion();
	//echo $ver; 
	
	if(strpos($_SERVER['REQUEST_URI'], "jobcardedit")==true)
	{
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_request_product where rp_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		
		$first_page_style="display:none";
		$second_page_style="";
		
	}else
	{
		$mode="Add";
		$purchaseorder_date=date('d-m-Y');
		$po_type_status='';
		$second_page_style="display:none";
		$first_page_style="";
	}
	$branchid=$_SESSION['branch_id'];
	$branch_style="";
	if($branchid!=0){
		$branch_style="Display:none;";
	}
	//echo $mode;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
		<style >
			.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
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
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'job_card_list'?>"><?=$form?> List</a></li>
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
									<div class="first_page" style="<?=$first_page_style?>" >
										<div class="col-md-12" style="margin-top: 10px;<?=$branch_style?>">
											<div class="col-md-4"></div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label"><strong>Branch </strong></label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="branch_id" id="branch_id" onChange="check_already_job_card();" title="Select Branch Name">
															<?=get_branch_name_company($dbcon,$branchid)?>
														</select>
													</div>
												</div>	
											</div>
										</div>
										<div class="col-md-12" style="    margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label"><strong>Product </strong></label>
												<div class="col-md-8 col-xs-11">
													<select class="select2 selproduct1" title="Select product" name="product_id" id="product_id" onchange="check_bom_version();" >
														<option value="">Choose Product</option>
														<?=getproduct($dbcon,'');?>
													</select>
												</div>
											</div>	
											</div>
										</div>
										<div class="col-md-12" style="    margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label"><strong>Bom Version </strong></label>
												<div class="col-md-8 col-xs-11">
													<select class="select2 selbom" title="Select Bom Version" name="bom_version_id" id="bom_version_id">							<option selected="selected" value="10000">R&D</option>
														
													</select>
												</div>
											</div>	
											</div>
										</div>
										<div class="col-md-12" style="    margin-top: 10px;">
											<div class="col-md-4"></div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label" ><strong>Quantity </strong></label>
													<div class="col-md-8 col-xs-11">
														<input id="qty" name="qty" type="text" class="form-control" title="" value="" placeholder="Qty" onkeypress="return isNumberKey(event)" >
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12" style="    margin-top: 10px;">
											<div class="col-md-5"></div>
											<div class="col-md-4">
												<button class="btn btn-danger" data-original-title="Next" data-toggle="tooltip" data-placement="Next" onClick="set_data()"><i class="fa fa-arrow-right"></i>Next</button>
											</div>
										</div>
									</div>
									<div class="second_page" style="<?=$second_page_style?>">
										
									</div>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once('../include/update_product_process.php');?>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/job_card_add.js?<?=time()?>"></script>
		<script>
			$(".selproduct1").select2({
				width: '100%',
				minimumInputLength: 2,

			});	
			
			$(".selbom").select2({
				width: '100%'	

			});	
			
			
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
		<?php if($mode=="Edit"){ 
				echo "<script>next_page(".$rel['rp_pid'].",'',".$rel['rp_id'].");</script>";
			} ?>
	</body>
</html>
