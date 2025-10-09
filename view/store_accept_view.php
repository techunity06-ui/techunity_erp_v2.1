<?php 
	session_start();
	$path = '../';
	$incPath = $path.'include/';

	include_once($path.'config/config.php');
	include_once($path.'config/session.php');
	include_once($incPath.'common_functions.php');

	$form="Store Accept";

	$machine_confi_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_store_accept_configuration as machineconf
	where machineconf.id=$machine_confi_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$queryImage="select machineimage.* from tbl_store_accept_image_upload as machineimage where machineimage.store_accept_id=$machine_confi_id";
	$relImageData = $dbcon->query($queryImage);	
	
	$back_link = $_SERVER['HTTP_REFERER'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($incPath.'include_css_file.php');?>
	<style>
		.mg10{
			margin-left:5px;
		}
		#radioBtn .notActive{
			color: #3276b1;
			background-color: #fff;
		}
		.redc
		{
			color:#EB6A5D !important;
			text-align:center !important;
		}
		.machine-class{
			width: 300px;
		}

	</style>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
	
	<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>

</head>
<body>
<section id="container" >
<?php include_once($incPath.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($incPath.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">			
<div class="row">
	<div class="col-lg-12">
		<!--breadcrumbs start -->
		<section class="panel">
			<header class="panel-heading">
				<a href="<?=$back_link?>" type="button" class="btn btn-info" style="float:right;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back</a>
				<h3><?='View '.' '.$form?></h3>
			</header>	
			<div class="">
				<ul class="breadcrumb">
					<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
					<li><a href="<?=ROOT.'store_accept_list'?>">Store Accept List</a></li>
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
			<div class="panel-body ">
				<table class="table table-bordered table-hover table-striped ">
					
					<tr>
						<th colspan="2" class="redc">Store Accept Detail</th>
					</tr>
					<tr>
						<th class="machine-class">Accept Date</th>
						<td><?=$rel['approve_date']?></td>
					</tr>
					<tr>
						<th class="machine-class">Approve Status</th>
						<td><?= ($rel['approve_status'] == '0') ? 'Approve' : 'Pending';?></td>
					</tr>
					<tr>
						<th class="machine-class">Machine Configure Upload Images</th>
						<td>
							<?php while($relImg=mysqli_fetch_assoc($relImageData)){	?>
								<a class="fancybox" href="<?php if(isset($relImg['upload_machine_file']) && !empty($relImg['upload_machine_file'])){ echo ROOT .'upload/store_accept/'.$relImg['upload_machine_file']; } else { echo ROOT .'upload/store_accept/no_profile.png'; } ?>"><img src="<?php if(isset($relImg['upload_machine_file']) && !empty($relImg['upload_machine_file'])){ echo ROOT .'upload/store_accept/'.$relImg['upload_machine_file']; } else { echo ROOT .'upload/store_accept/no_profile.png'; } ?>" width="50" height="50" /></a>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th class="machine-class">Remark</th>
						<td><?=$rel['remark'];?></td>
					</tr>
					
				</table>
				
			</div>
		</section>
	</div>
</div>
<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->

<?php include_once($incPath.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script type="text/javascript">
	$(".fancybox").fancybox();
</script>
</body>
</html>
