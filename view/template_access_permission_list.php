<?php 

	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Template Access Permission";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$bulkAccessArray = cancheckPermissionAccess($dbcon, [
						'template-access-permission-create'
					]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>TEMPLATE PERMISSION LIST</title>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
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
					  	<span class="tools pull-right">
					  	<?php if(in_array('template-access-permission-create',$bulkAccessArray)){ ?> 
							<a href="<?=ROOT.'template_access_permission_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
						<?php } ?> 
						</span>
					</header>	
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="template-table">
								<thead>
									<tr>
										<th>Sr</th>
										<th>Company Name</th>
										<th>Template Name</th>
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
		
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/template_access_permission.js?<?=time()?>"></script>
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
