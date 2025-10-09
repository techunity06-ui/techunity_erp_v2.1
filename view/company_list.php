<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Company";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>COMPANY LIST</title>
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
			<?php 
//				include_once('../include/quick_link.php');
				?>
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
							  <li ><a href="<?=ROOT.'company_list'?>"><?=$form?> list</a></li>
							 
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
					<a href="<?=ROOT.'create_company'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>	
											
				 </span>
				 
					</header>	
					 <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Company Name</th>
					  <th>Address</th>
					  <th>Bank Name</th>
					  <th>Pan No</th>
					  
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
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="js/app/company.js?<?=time()?>"></script>
    <!--<script src="js/count.js"></script>-->
	

  </body>
</html>
