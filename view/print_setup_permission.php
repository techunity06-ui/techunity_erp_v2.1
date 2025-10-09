<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Print Setup Permission";
$companyID = $_SESSION['company_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRINT SETUP PERMISSION</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<style type="text/css">
	h2 {
		width: 100%; 
		text-align: center; 
		border-bottom: 1px solid #a7a2a2; 
		line-height: 0.1em;
		margin: 100px 0 20px; 
	} 

	h2 span { 
		background:#fff; 
		padding:0 10px;
		color: #e05a5a; 
	}

	table {
		text-align: left;
		position: relative;
		border-collapse: collapse; 
	}

	th, td {
		padding: 0.25rem;
	}

	th {
		background: #ece5e5;
		position: sticky;
		top: 68px; /* Don't forget this, required for the stickiness */
		box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
	}	
</style>	
<section id="container" >
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
							<h3>New <?=$form?></h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
								<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
								<li class="active"><?=$form?></li>
							</ul>
						</div>
					</section>
					<!--breadcrumbs end -->
				</div>	
			</div>
			<!--state overview start-->
			<div class="row">
				<div class="col-sm-12">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="printsetup_permission_add" action="javascript:;" method="post" name="printsetup_permission_add">
								<div id="show_user_menu"></div>
								<div class="form-group col-md-12 save-permission-btn" style="display: none;">	
									<div class="col-md-5"></div>				  
									<input type='hidden' name='mode' id='mode' value='printsetup_permission_add' />				  
									<button type="submit" class="btn btn-info" style="margin-left: 50px;">Save Permission</button>
								</div>
							</div>
						</form>

					</div>
				</section>
			</div>

		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once('../include/footer.php');?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/print_setup_list.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		$(document).on('click', '.allMenuShow', function() {
			var dataId = $(this).attr('data-id');
			var dataCls = $(this).attr('data-cls');
			var isChecked = $(this).find('.mainChk').prop('checked');
			$('.sub_'+dataId+' .'+dataCls).prop('checked', isChecked);
		});

	</script>
</body>
</html>
