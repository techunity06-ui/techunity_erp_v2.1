<?php 
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$frmdt=date('d-m-Y');
	$todt=date('d-m-Y');
	$_SESSION['company_name']="brp";
	
	$company_id=$dbcon->real_escape_string($_REQUEST['id']);
	
	$query="select * from tbl_company where company_id=".$company_id;
    $rel=mysqli_fetch_assoc($dbcon->query($query)); 
	
	$cust_code=$rel['cmp_unique_id'];
	
      
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php //include_once('../include/include_top_menu.php');?>
			<?php //include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="panel-body">
                        <div class="row">
							<form class="form-horizontal" role="form" id="licence_add" action="javascript:;" method="post" name="licence_add">
								<div class="col-md-12" style="margin-bottom: 10px;">
									<div class="col-md-6">
									   <div class="form-group">
										  <label class="col-md-4 control-label">Customer Code</label>
										  <div class="col-md-8 col-xs-11" style="font-size: 16px;color: red;font-weight: 600;">
										  <?=$cust_code?>
											<input type="hidden" name="cust_code" id="cust_code" value="<?=$cust_code?>" />
											<input type="hidden" name="company_id" id="company_id" value="<?=$company_id?>" />
											<input type="hidden" name="mode" id="mode" value="Add" />
										  </div>
									   </div>
									</div>
								</div>
								<div class="col-md-12" style="margin-bottom: 10px;">
									<div class="col-md-6">
									   <div class="form-group">
										  <label class="col-md-4 control-label">Customer Key</label>
										  <div class="col-md-8 col-xs-11">
											 <input id="cust_key" name="cust_key" type="text" class="form-control" title="Customer Key" value="<?=$customer_key?>" placeholder="Customer Key" >
										  </div>
									   </div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="col-md-6">
										<div class="form-group">
											<center>
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											</center>
										</div>
									</div>
								</div>
							</form>
                        </div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<!--<script src="<?=ROOT?>js/app/licence.js"></script>-->
		<script src="<?=ROOT?>js/app/licence.js?<?=time()?>"></script>

	
  </body>
 
</html>
