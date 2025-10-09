<?php 
	session_start();
	include('../include/urlfile.php');
	$form=" Requested Returnable Receipt";
	$returnable_receipt_date = date('d-m-Y');
	$branch_id = $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>RETURNABLE REQUEST</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
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
										<li><a href="<?=ROOT.INVENTORY_ROOT.'returnable_receipt_request_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="returnable_receipt_request_add" action="javascript:;" method="post" name="returnable_receipt_request_add">
										<div class="row">
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-3">
													<div class="form-group">
													<label class="col-md-4 control-label"> Select Vendor * </label>
													<div class="col-md-6 col-xs-11">
														<select class="select2" name="vender_id" id="vender_id" onChange="get_product(this.value)" required title="Select Vender">
															<?=getcust($dbcon,$vender_id);?>	
														</select>
													</div>
													</div>	
												</div>
												<div class="col-md-3">
													<div class="form-group">
													<label class="col-md-4 control-label"> Select Channal * </label>
													<div class="col-md-6 col-xs-11">
														<select class="select2" name="channal_id" id="channal_id" title="Select Channal" onChange="get_channal(this.value)">
														</select>
													</div>
													</div>	
												</div>
												<div class="col-md-3">
			                                          <?php echo getBranchBox($dbcon, $branch_id, $branchId, false, true); ?>
			                                    </div>
												
											</div>	 
											<div class="col-md-12" style="margin-top:10px;">
											</div>			
											<div class="col-md-12" style="margin-top:10px;">
												<div id="returnable_receipt_data"></div>	
											</div>
											<div class="clearfix"></div>
											<button type="submit" class="btn btn-success" id="save" name="save">Create Receipt</button>
											<a href="<?=ROOT.INVENTORY_ROOT.'returnable_receipt_request_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-5"></div>					
										</div>
										<input type='hidden' name='mode' id='mode' value='req_returnable_to_main_grn' />
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/returnable_receipt_request.js?<?=time()?>"></script>
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
	</body>
</html>
