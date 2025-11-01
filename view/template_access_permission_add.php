<?php 
	session_start();
	include_once("../config/config.php");
	//error_reporting(E_ALL);
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$form="Template Access Permission";
	if(strpos($_SERVER['REQUEST_URI'], "template_access_permission_edit")==false) {
		$mode="Add";
	}else{
		$mode="Edit";
		$template_id = $dbcon->real_escape_string($_REQUEST['id']);
		$query="select tempaccess.* from template_access_permission as tempaccess
		left join tbl_company as com on com.company_id=tempaccess.company_id
		where tempaccess.id=$template_id";	
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
	$bulkAccessArray = cancheckPermissionAccess($dbcon, [
		'template-access-permission-create'
	]);
	if(!in_array('template-access-permission-create',$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>TEMPLATE PERMISSION</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<style type="text/css">
h2 {
   width: 100%; 
   text-align: center; 
   border-bottom: 1px solid #a7a2a2; 
   line-height: 0.1em;
   margin: 100px 0 20px; 
} 

h2 span { 
    background:#fff; 
    padding:0 10px;
    color: #e05a5a; 
}

table {
  text-align: left;
  position: relative;
  border-collapse: collapse; 
}

th, td {
  padding: 0.25rem;
}

th {
  background: #ece5e5;
  position: sticky;
  top: 68px; /* Don't forget this, required for the stickiness */
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
}	
</style>
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
			<h3><?=$mode .' '.$form?></h3>
		</header>	
		<div class="">
			<ul class="breadcrumb">
				<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
				<li><a href="<?=ROOT.'template_access_permission_list'?>"><?=$form?> List</a></li>
			</ul>
		</div>
	</section>
	<!--breadcrumbs end -->
</div>	
</div>
<!--state overview start-->
<div class="row">			
<div class="col-md-12">
<section class="panel">
<header class="panel-heading">
    <?=$mode.' '.$form?>
</header>	
<div class="panel-body">
	<form role="form" id="template_access_permission_add" action="javascript:;" method="post" name="template_access_permission_add">
		<div class="row">
			<div class="col-md-12 row_margin" style="margin-top:5px">
				<div class="col-md-2"></div>
				<div class="col-md-8">
					<div class="form-group">
						<label class="col-md-3 control-label">Template Name</label>
						<div class="col-md-6"> 
							<input type="text"  name="template_name" title="Enter Template Name" placeholder="Template Name" id="template_name" class="form-control" value="<?php if($mode=='Edit'){ echo $rel['template_name'];} ?>" />
						</div>
					</div>
				</div>
				<div class="col-md-2"></div>	
			</div>
			<div class="col-md-12 row_margin" style="margin-top:15px">
				<div class="form-group">
					<div id="show_user_menu"></div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="col-md-12 text-center">
			<button type="submit" class="btn btn-success" id="save" name="save">Save Template</button>
			<a href="<?=ROOT.'template_access_permission_list'?>" type="button" class="btn btn-danger">Cancel</a>	
		</div>	
	</div>
</div><!--Vendor row end-->	
<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
<input type='hidden' name='eid' id='eid' value='<?=$template_id?>' />
</form>
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
<?php 
	if($mode == 'Add'){
		echo "<script>load_user_menu() </script>";
	}else{
		echo "<script>load_user_menu(".$rel['id'].") </script>";
	}
?>
</body>
</html>