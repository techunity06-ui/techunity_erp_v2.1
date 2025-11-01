<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$form="Menu Master Access";
	$response = $getData = [];
	/*START JAYESH ADD CLONE PARAMETER*/
    $opertion_name = array('V'=>'View','C'=>'Create','R'=>'Read','U'=>'Update','D'=>'Delete','A'=>'Approve','FA'=>'Final Approve','O'=>'Others','CL'=>'Clone');
    /*END JAYESH ADD CLONE PARAMETER*/
	$userID =  $_SESSION['user_id'];
	if(strpos($_SERVER['REQUEST_URI'], "menu_master_access_edit")==true) {
		$mode="Edit";
		$menuMasterId = $dbcon->real_escape_string($_REQUEST['id']);
		$query="select mainaccess.*,menumaster.id as routes_id,menumaster.access_type,menumaster.slug_name,menumaster.route_path_name from menu_master_access as mainaccess left join menu_master_access_routes as menumaster ON menumaster.access_id = mainaccess.id where mainaccess.id = $menuMasterId";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		if(isset($rel['parent_id']) && !empty($rel['parent_id'])){
	    	$parentID = $rel['parent_id'];
	    }else{
	    	$parentID = 0;	
	    }

		$result=$dbcon->query($query);
		if (mysqli_num_rows($result)>0) {
		  // output data of each row
		  while($row=mysqli_fetch_array($result)) {
		  	$response['routes_id'] = $row['routes_id'];
		  	$response['access_type'] = $row['access_type'];
		  	$response['slug_name'] = $row['slug_name'];
		  	$response['route_path_name'] = $row['route_path_name'];
		  	$getData[$row['access_type']] = $response;
		  }
		}
		if($rel){
		}else{
			header("Location: " . DOMAIN . "menu_master_access_list");
		}
	}else{
		$mode="Add";
		if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
			$parentID = $dbcon->real_escape_string($_REQUEST['id']);
		}else{
			$parentID = 0;
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>MENU</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
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
				<li><a href="<?=ROOT.'menu_master_access_list'?>"><?=$form?> List</a></li>
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
	<form class="form-horizontal" role="form" id="menu_master_access_add" action="javascript:;" method="post" name="menu_master_access_add">
		<div class="row">
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Menu Name</label>
					<div class="col-md-6"> 
						<input type="text" class="form-control" id="menu_name" name="menu_name" placeholder="Menu Name" value="<?php if($mode=='Edit') { echo $rel['menu_name']; } ?>">
					</div>
				 </div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Menu Path</label>
					<div class="col-md-6"> 
						<input type="text" class="form-control" id="menu_path" name="menu_path" placeholder="Menu Page Path" value="<?php if($mode=='Edit') { echo $rel['menu_path']; } ?>">
					</div>
				 </div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Menu Description</label>
					<div class="col-md-6">
						<textarea style="border: 1px solid #ccc;" id="menu_description" name="menu_description" placeholder="Menu Description" rows="5" cols="103"><?php if($mode=='Edit') { echo $rel['menu_description']; } ?></textarea>
					</div>
				</div>	
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Fa Icon</label>
					<div class="col-md-6"> 
						<input type="text" class="form-control" id="menu_fa_icon" name="menu_fa_icon" placeholder="Fa Icon" value="<?php if($mode=='Edit') { echo $rel['menu_fa_icon']; } ?>">
					</div>
				 </div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Menu Image URL</label>
					<div class="col-md-6"> 
						<input type="text" class="form-control" id="menu_image_url" name="menu_image_url" placeholder="Menu Image URL" value="<?php if($mode=='Edit') { echo $rel['menu_image_url']; } ?>">
					</div>
				 </div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Menu Order</label>
					<div class="col-md-6"> 
						<input type="text" class="form-control" id="menu_order" name="menu_order" placeholder="Menu Order" value="<?php if($mode=='Edit') { echo $rel['menu_order']; } ?>">
					</div>
				 </div>
			</div>
			<div class="clearfix"></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label"></label>
					<div class="col-md-6">
						<input type="checkbox" name="report_status_flag" id="report_status_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['report_status_flag'] : 'No' ?>" <?php if($rel['report_status_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Report Status</span>
					</div>
				</div>
			</div>
			<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
			<div class="col-md-12">
				<h4>PERMISSION OPERATIONS</h4>
				<table class="table table-bordered table-striped table-hover">
					<thead>
						<tr>
							<th>Operation Name</th>
							<th>Operation Unique Name (EX. inquiry-list)</th>
							<th>Route (EX. crm/inquiry-list)</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($opertion_name as $opkey => $opval){
								if($getData[$opkey]['access_type'] == $opkey){ ?>
							<tr>
								<td><?=$opval?></td>
								<td>
									<input type="text" name="slug_name[<?=$opkey?>]" data-access_id="<?=$opkey?>" id="<?=$opkey?>" data-table_id="<?=$getData[$opkey]['routes_id']?>" class="form-control validateName" value="<?php if($mode=='Edit' && $getData[$opkey]['access_type'] == $opkey) { echo $getData[$opkey]['slug_name']; } ?>"></td>
								<td>
									<input type="text" name="route_name[<?=$opkey?>]" id="v_route" class="form-control" value="<?php if($mode=='Edit' && $getData[$opkey]['access_type'] == $opkey) { echo $getData[$opkey]['route_path_name']; } ?>">
								</td>
							</tr>
						<?php }else{ ?>
							<tr>
								<td><?=$opval?></td>
								<td>
									<input type="text" name="slug_name[<?=$opkey?>]" data-access_id="<?=$opkey?>" id="<?=$opkey?>" data-table_id="<?=$getData[$opkey]['routes_id']?>" class="form-control validateName" value="<?php if($mode=='Edit' && $rel['access_type'] == $opkey) { echo $rel['slug_name']; } ?>"></td>
								<td>
									<input type="text" name="route_name[<?=$opkey?>]" id="v_route" class="form-control" value="<?php if($mode=='Edit' && $rel['access_type'] == $opkey) { echo $rel['route_path_name']; } ?>">
								</td>
							</tr>
						<?php }}  ?>		
					</tbody>
				</table>
				
			</div>
			<div class="col-md-12"><br></div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-md-3 control-label">Status*</label>
				  	<div class="col-md-6">
						<select class="select2" id="status" name="status">
							<?php echo getStatusOptions($rel['status']); ?>
						</select>	
				  	</div>  	
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
		<input type='hidden' name='eid' id='eid' value='<?=$menuMasterId?>' />
		<input type='hidden' name='parent_id' id='parent_id' value='<?=$parentID?>' />
		<div class="col-md-12 text-center">
			<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
			<a href="<?=ROOT.'menu_master_access_list'?>" type="button" class="btn btn-danger">Cancel</a>	
		</div>	
	</div>
</div>

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
	
	<?php include_once('../include/preview_rel_details.php');?>
	<?php include_once('../include/footer.php');?>
	<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/menu_master_access.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$(document).ready(function(){
	$(document).on("blur",".validateName", function(){
		var currentVal = $(this).val();
		var currentTableVal = $(this).data('table_id');
		var currentID = $(this).attr('id');
		$.ajax({
			type: "POST",
			url: root_domain+'app/menu_master_access/',
			data: { mode : "check_validate", currentVal : currentVal,currentTableVal : currentTableVal },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.warning("CURRENT SLUG NAME ALREADY EXISTS", "ERROR");
					$("#"+currentID).val('');
				}						
			}
		});	
	});
	$(document).on("click","#report_status_flag", function(){
		if($(this).is(":checked")){
			$("#report_status_flag").val('Yes');
		}else{
			$("#report_status_flag").val('No');
		}
	});
});
</script>
</body>
</html>