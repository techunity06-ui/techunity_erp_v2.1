<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Purchase Bill";
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
			
			<?php //include_once('../include/equick_link.php');?>
     		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode.' '.$form?> List</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li ><a href="<?=ROOT.'purchasebill_list'?>"><?=$form?> list</a></li>
							 
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
					<a href="<?=ROOT.'purchasebilladd'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
				 </span>
					</header>	
						<div class="form-group">
                                  <label class="control-label col-md-2">Choose Date</label>
                                  <div class="col-md-3">
                                      <div class="input-group input-large" data-date="13/07/2013" data-date-format="mm/dd/yyyy">						<?php 
									  $start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									
									  ?>
                                          <input type="text" value="<?=$start?>" class="form-control default-date-picker" name="from_date" id="from_date" onChange="reload_data();">
                                          <span class="input-group-addon">To</span>
                                          <input type="text" class="form-control default-date-picker" value="<?=date('d-m-Y')?>" name="to_date" id="to_date" onChange="reload_data();">
                                      </div>
                                  </div>
                             
							  </div>	
					 <div class="panel-body">
					 
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr No</th>
					  <th>Vender Name</th>
					  <th>Bill Date</th>
					  <th>Amount</th>
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
   <script src="js/app/purchase.js"></script>
    <!--<script src="js/count.js"></script>-->
	<script>
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
</script>

  </body>
</html>
