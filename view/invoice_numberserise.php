<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	if(strpos($_SERVER[REQUEST_URI], "invoice_numberserise")==false)
	{
		$mode="Add";
		
	}
	else
	{
		$mode="Edit";
		$eid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_invoiceserise where invoiceserice_id=$eid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));		
	}
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
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode?> Invoice Series</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							
							
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
					  Edit Invoice Series
					</header>	
					<div class="panel-body ">
						<form class="form-horizontal" role="form" id="a_add" action="javascript:;" method="post" name="a_add">
							<div class="row">
							<div class="col-md-10">
							<div class="form-group">
							  <label class="col-md-3 control-label">Start Number *</label>
							  <div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Start Number" name="start_no" id="start_no"  value="<?=$rel['start_no']?>" /> 
								</div>
                             </div>						 
							<div class="form-group">
							<?php$sel='';$sel1='';$sel2='';
							if($rel['invoice_format']=="0")
							{
								$sel='selected="selected"';
							}
							else if($rel['invoice_format']=="1")
							{
								$sel1='selected="selected"';
							}
							else if($rel['invoice_format']=="2")
							{
								$sel2='selected="selected"';
							}
							?>
								  <label class="col-md-3 control-label">Invoice Format</label>
								  <div class="col-md-6 col-xs-11">
								  <select class="form-control" id="invoice_format" name="invoice_format"  onchange="format_valuechange(this.value);">
									<option <?=$sel?> value="0">None</option>
									<option  <?=$sel1?> value="1">Prefix</option>
									<option  <?=$sel2?> value="2">Suffix</option>
								  </select>
								</div>
							  </div> 	
							<div class="form-group">
							  <label class="col-md-3 control-label">Formate *</label>
									<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Formate" name="formate" id="formate"  value="<?=$rel['formate']?>" onKeyUp="view_format(this.value)" /> 
								</div>
                             </div>	
							 <div class="hidden form-group" id="ex_format_div">
								  <label for="invoice Type">Example  Format</label>
								  <span id="ex_format" ></span>							  
							  </div>
						
							 <div class="col-md-3"></div>
							 <button type="submit" class="btn btn-success">Submit</button> &nbsp;
							<div class="col-md-3"></div>					 						 	
							</div>
						</div><!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='edit' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['invoiceserice_id']?>' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							
						  </form>
					</div>
				</section>
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
	<script src="<?=ROOT?>js/app/number_serise.js"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });

</script>
<?
echo "<script>format_valuechange(".$rel['invoice_format'].")</script>";


?>
  </body>
</html>