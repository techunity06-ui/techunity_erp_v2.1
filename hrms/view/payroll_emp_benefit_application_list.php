<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/payroll_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Payroll Employee Benefit Application";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	$infopage = pathinfo(__FILE__);
	$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?> List</h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT . HRMS_ROOT . 'payroll_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li class="active"><?=$form?> List</li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading respadlr0">	
								<span class="tools pull-right respadr_15">
									<a href="<?=ROOT . HRMS_ROOT . 'payroll_emp_benefit_application_add'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
								</span>
								<div class="col-md-12"	style="height:10px;" ></div>
							</header>	
							<div class="panel-body">
								<div class="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
											  <th>Series No</th>
											  <th>Company Name</th>
											  <th>Employee Name</th>
											  <th>Payroll Period Name</th>
											  <th>Benefit Date</th>
											  <th>Status</th>
											  <th class="hidden-phone">Action</th>					  
											</tr>
										</thead>
										<tbody></tbody>				 
									</table>
								</div>
							</div>
						</section>
					</div>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
		<script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_emp_benefit_application.js?<?=time()?>"></script>
	</body>
</html>
