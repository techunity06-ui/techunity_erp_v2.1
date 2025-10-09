<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Inquiry";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
if(empty($_SESSION['start'])) {
	$start=date('1-m-Y');
	$end=date("d-m-Y");
}
else {
	$start=$_SESSION['start'];
	$end=$_SESSION['end'];
}

if($_REQUEST['id']){
	$child_usr_id=$dbcon->real_escape_string($_REQUEST['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
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
								<h3> <?=$form?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.'inquiry_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>

				<div class="row">		
					<!--state overview start-->
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<div class="col-md-5">
										<div class="form-group">
											<label class="control-label col-md-4">Choose Date</label>
											<div class="col-md-7">
												<div class="input-group date form_datetime-component">
													<input type="hidden" id="from_date" value="<?=$start?>">
													<input type="hidden" id="to_date" value="<?=$end?>">
													<input type="text" id="rep_date" onChange="load_inquiry_datatable();" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
														<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>	
									<div class="col-md-5">
										<div class="form-group">
											<label class="control-label col-md-4">Choose User</label>
											<div class="col-md-7">
												<select class="select2" name="child_usr_id" id="child_usr_id" onChange="load_inquiry_datatable();">
													<?=get_child_users($dbcon,$child_usr_id);?>	
												</select>
											</div>
										</div>
									</div>

									<span class="tools pull-right">
										<a href="<?=ROOT.'inquiry_add'?>"><button class="btn btn-success btn-flat">Add <?=$form?></button></a>
									</span>
									<div class="col-md-12"	style="height:20px;" ></div>
								</header>	
								<div class="panel-body">
									<div class="adv-table dt-resp">
										<table class="display table table-bordered table-striped" id="inquiry-table">
											<thead>
												<tr>
													<!--<th>Owner</th>-->
													<th>Inquiry Date</th>
													<th>Inquiry No</th>

													<!--<th>Opportunity Name</th>-->
													<th>Company</th>
													<th>Mobile No</th>
													<!--<th>State</th>-->
													<th>City</th>
													<th>Product</th>
													<th>Stage</th>
													<th>User</th>
													<th>Assing User</th>
													<th>Action</th>	
												</tr>
											</thead>
											<tbody>
											</tbody>				 
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
					<!--state overview end-->
				</section>
			</section>
			<!--main content end-->
			<!--footer start-->
			<?php include_once('../include/preview_flp_hist.php');?>
			<?php include_once('../include/send_email.php');?>
			<?php include_once('../include/footer.php');?>
			<!--footer end-->
		</section>
		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT.CRM_ROOT?>js/app/inquiry.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			function cb(start, end) {
				$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			cb(moment().subtract(29, 'days'), moment());

			$('.datepikerdemo').daterangepicker({       
				locale: {
					format: 'DD-MM-YYYY'
				},
				"autoApply": true,	
				"startDate": $('#from_date').val(),
				"endDate": $('#to_date').val(),	
				ranges: {
					'Today': [moment(), moment()],
					'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}, cb);
			$('.date-set').click(function(){
				$('.datepikerdemo').trigger('click');
			});
			$(function(){
				setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
			});

			CKEDITOR.replace( 'email_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
		</script>
	</body>
	</html>
