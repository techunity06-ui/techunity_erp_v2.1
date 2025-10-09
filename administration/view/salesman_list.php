<?php 
	session_start();
	include('../include/urlfile.php');

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        READ_SALESMAN_MASTER,
        CREATE_SALESMAN_MASTER
    ]);

    if(!in_array(READ_SALESMAN_MASTER,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }

	$form = "Salesman";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = $infopage['filename'];
    $branch_id = $_SESSION['branch_id'];
	$countryid = '101';
	$stateid = '1';
	$cityid = '1';
	$end = date("d-m-Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($include.'include_css_file.php');?>
<style>
tbody>tr>:nth-child(2){ 
	 text-align: center;
}
@media (min-width: 1200px){
#custom_sold_modal {
    width: 1150px;
}
}
</style>
</head>
<body>
<section id="container" class="sidebar-closed">
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'left_menu.php');?>
<!--sidebar end-->
<!--main content start-->

<section id="main-content">
	
	<section class="wrapper">
		
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3><?=$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                                        <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							<li><a href="<?=ROOT.ADMINISTRATION_ROOT.'salesman_list'?>"><?=$form?> list</a></li>
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
                        <?php if(in_array(CREATE_SALESMAN_MASTER,$bulkAccessArray)){ ?>
						<span class="tools pull-right"> 
							<a href="<?=ROOT.ADMINISTRATION_ROOT.'salesman_create'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
						</span>
                        <?php } ?>
					</header>
                                        <div class="col-md-5">
                                            <?php //echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_ledger_datatable()'); 
                                            ?>
					</div>
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table  class="display table table-bordered table-striped" id="salesman-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Name</th>
										<th>Allias</th>
										<th>Print Name</th>
										<th>Address</th>
										<th>Mobile</th>
										<th>Whatsup</th>
										<th>Email</th>					  
										<th>Commision type</th>
										<th>Commision per</th>	
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
		<input type="hidden" name="custno" id="custno" value="<?=$end?>">	
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php 
	
	include_once($include.'allocate_sold_product.php');
	include_once($include.'footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/salesman.js?<?=time()?>"></script>

<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});

 
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>
</body>
</html>
