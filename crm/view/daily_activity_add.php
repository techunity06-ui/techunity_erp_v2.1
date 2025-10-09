<?php 
	session_start();
	include('../include/urlfile.php');
	$path = '../../';
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Daily Activity Log";
	$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$comty=mysqli_fetch_assoc($dbcon->query($com));	
	// check permission for terms and condition
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        CUSTOMER_DAILY_UPDATE_SLUG_CREATE,
        CUSTOMER_DAILY_UPDATE_SLUG_UPDATE
    ]);

    $branch_id = $_SESSION['branch_id'];
	if(strpos($_SERVER[REQUEST_URI], "crm/daily_activity_edit")==false) {
		$mode="Add";
		if(!in_array(CUSTOMER_DAILY_UPDATE_SLUG_CREATE,$bulkAccessArray)){
        	header("Location: ".DOMAIN."permission_access");
    	}
	}
	else {
		$mode="Edit";
		if(!in_array(CUSTOMER_DAILY_UPDATE_SLUG_UPDATE,$bulkAccessArray)){
        	header("Location: ".DOMAIN."permission_access");
    	}
		$daily_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_daily_activity_log where id=$daily_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($include.'include_css_file.php');?>
	
	<style>
		
		.row_margin{
			
			margin-top:15px !important;
		}
		.check_span
		{
			margin:10px;
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
						  <h3>New <?=$form?>
						  <!--<a href="<?=ROOT.'import_product'?>" >
						  <button class="btn btn-primary btn-flat pull-right">Import <?=$form?></button></a>-->
						  </h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><a href="<?=ROOT.CRM_ROOT.'daily_activity_list'?>"><?=$form?> List </a></li>
						  </ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--Customer overview start-->
		
		  <div class="row">
			<div class="col-sm-12">
				<section class="panel">
					<header class="panel-heading">
					  New <?=$form?> 
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>	
					<div class="panel-body">
						<form role="form" id="daily_activity_add" action="javascript:;" method="post" name="daily_activity_add">
					<div class="col-md-12 row_margin" style="padding-top: 25px;">
						<div class="col-md-12">
							<div class="col-md-6">
				                <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8','',''); ?>
				            </div>
				        </div>
					</div>	
					<div class="col-md-12 row_margin">
						
						<div class="col-md-12">
						
							<div class="col-md-6">
							  <div class="form-group">
								  <label for="Daily Update Date" class="col-md-4 control-label">Date*</label>
								  <div class="col-md-8 col-xs-11">
								  <input type="text"  class="form-control default-date-picker" id="daily_activity_date" name="daily_activity_date" placeholder="Date" value="<?=($rel['daily_activity_date'])?date('d-m-Y', strtotime($rel['daily_activity_date'])):date('d-m-Y');?>" disabled/>
								  </div>
							  </div>							 
							</div>
						</div> 
						
						<div class="col-md-12  row_margin" style="margin-top:5px">
							 <div class="col-md-12">
							  <div class="form-group">
								  <label for="Description" class="col-md-2 control-label">Description *</label>
								  <div class="col-md-10 col-xs-11">
									<textarea class="form-control" name="description" id="description" rows="5" maxlength="1200"><?=$rel['description'];?></textarea>
									<span id="rchars">1200</span> Character(s) Remaining
								  </div>
							  </div>							 
							</div>
						</div>
						
						<div class="clearfix" style="margin-bottom:10px;">		
						</div>	
						
						<div class="col-md-5"></div>
						<div class="col-md-3">	
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />	  
							<input type='hidden' name='eid' id='eid' value='<?php if($mode=='Edit'){ echo $daily_id; } else { echo "0"; } ?>' />				  
							<input type='hidden' name='mode' id='mode' value='<?php echo $mode; ?>' />			
							<button type="submit" class="btn btn-success">Submit</button>
							&nbsp;<a href="<?=ROOT.CRM_ROOT.'daily_activity_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>
						</div>
						</div>
					</form>

					</div>
				</section>
			</div>
			  </div>
		  
		  <!--Customer overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/daily_activity.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
var maxLength = 1200;
<?php if($mode=="Edit"){	?>
	var str_len = "<?= strlen($rel['description']); ?>";
	var textlen = maxLength - str_len;
	$('#rchars').text(textlen);
<?php } ?>
$('#description').keyup(function() {
  var textlen = maxLength - $(this).val().length;
  $('#rchars').text(textlen);
});
 var tableToExcel = (function() {
 var uri = 'data:application/vnd.ms-excel;base64,'
   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>'
   , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
   , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
 return function(table, name) {
   if (!table.nodeType) table = document.getElementById(table)
   var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
   window.location.href = uri + base64(format(template, ctx))
 }
})()
</script>
</body>
</html>
