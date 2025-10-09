<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Menu Master Access";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		$parentid = $dbcon->real_escape_string($_REQUEST['id']);
	}else{
		$parentid = 0;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>MENU LIST</title>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container">
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
						<li><a href="<?=ROOT.'menu_master_access_list'?>"><?=$form?> List</a></li>
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
					    <label id="pname"></label> Menu List	
						<span class="tools pull-right">
							<a href="<?= ROOT . 'menu_master_access_add/'.$parentid ?>" class="update_link"><button class="btn btn-success btn-flat" >Add Module</button></a>
						</span>
						<span class="tools pull-right" >
							<button type="submit" class="btn btn-primary" onClick="pid_home(0);">Home</button>
							<!--<button type="submit" id="return" name="return" class="btn btn-success" value="" onClick="pid_return(this.value);"> Return</button>-->
						 </span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="dynamic-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Menu Name</th>
										<th>Menu Description</th>
										<th>Menu Path</th>
										<th>Sort Order</th>
										<th>Status</th>
										<th class="hidden-phone">Action</th>	
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
<input type='hidden' name='parent_id' id='parent_id' value='<?=$parentid?>' />
<input type='hidden' name='ppname' id='ppname' value='' />
<!--main content end-->
<!--footer start-->
<?php include_once('../include/add_flp_hist.php');?>
<?php include_once('../include/open_clone_modal.php');?>
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/menu_master_access.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
