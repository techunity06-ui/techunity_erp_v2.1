<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Min Max Planning";
	$type=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $type;
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	$branch_id = $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
		<title>MIN MAX PLANNING</title>
		<?php include_once($include.'include_css_file.php');?>
</head>
<body>
  <section id="container" >
     <?php include_once($include.'include_top_menu.php');?>
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
							
							<input type="hidden" class="form-control" name="st_type" id="st_type" value="<?=$type;?>" />
							
							<h3><?=$form?> List</h3>
							
						</header>
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?> List</li>
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
					<div class="col-md-4">
						<div class="form-group">
							<?php echo getBranchBox($dbcon, $branch_id,"", false, false, 'show_data();'); ?>
						</div>
					</div>
				<div class="panel-body">
				  <div class="adv-table">
					  <table  class="display table table-bordered table-striped" id="dynamic-table">
						  <thead>
							  <tr>
								  <th>Product Name</th>
								  <th>Product Category</th>
								  <th>Min.Base Qty</th>
								  <th>Min.Conv Qty</th>
								  <th>Request Base Qty</th>
								  <th>Request Conv Qty</th>
								  <th>Current Base Qty</th>
								  <th>Current Conv Qty</th>
								  <th class="hidden-phone">Action</th>					  
							  </tr>
						  </thead>
						  <tbody id="data_table">
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
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
    <!-- js placed at the end of the document so the pages load faster -->
		<?php include_once($include.'include_js_file.php');?>    
   <script src="<?php echo ROOT.PRODUCTION_ROOT; ?>js/app/get_stock_detail.js"></script>
    <!--<script src="js/count.js"></script>-->
	
	<script>
		$(".select2").select2({
			width: '100%'
		});
				
	</script>



  </body>
</html>
