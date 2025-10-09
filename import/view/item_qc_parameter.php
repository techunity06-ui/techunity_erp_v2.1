<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Import Product Qc Parameter";
	$mode = "Add";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>IMPORT PRODUCT QC PARAMETER</title>
<?php include_once($include.'include_css_file.php');?>
</head>
<body>
  <section id="container" >
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
						  <h3><?=$form?></h3>
						</header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.IMPORT_ROOT.'item_qc_parameter'?>">Product QC Parameter</a></li>
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
					<div class="panel-body ">
					<form class="form-horizontal" role="form" id="product_qc_importfile" action="javascript:;" method="post" name="product_qc_importfile">
							<div class="row">
							<div class="col-md-10">
							<div class="form-group">
							  <label class="col-md-3 control-label">Import product .csv File</label>
									<div class="col-md-4 col-xs-11">
									<input type="file" id="excel_file" name="excel_file" class="form-control"  accept=".csv" required title="Select File"/>
									 <div id="msg"></div>
								</div>
								
							 </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">File Formate</label>
									<div class="col-md-6 col-xs-11">
					<a href="<?=ROOT.PRODUCT_DEMO.'product_qc_parameter_demo.csv'?>" target="_blank" class="btn btn-info">Click to View Csv File Formate </a>
								</div>
								
							 </div>
							
							<button type="submit" class="btn btn-success">Submit</button> &nbsp;
							<a href="<?=ROOT.IMPORT_ROOT.'item_qc_parameter'?>" type="button" class="btn btn-danger">Cancel</a>
							<div class="col-md-3"></div>	
							</div>
						</div><!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='check_data' />
						  </form>
</div>	
					</section>
					<section class="panel" id="sampledata_show" style="display:none">
						<header class="panel-heading">
							 Error In Import Data Record
							</header>
							<div class="panel-body">
								 
								<div id="temp_productdata">
								
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
   <script src="<?=ROOT.IMPORT_ROOT?>js/app/item_qc_parameter.js?<?=time()?>"></script>
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
