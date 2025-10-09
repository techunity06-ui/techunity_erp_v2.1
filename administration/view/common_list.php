<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Common Master";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$end = date("d-m-Y");
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	READ_COMMON_MASTER,
        CREATE_COMMON_MASTER
    ]);

    if(!in_array(READ_COMMON_MASTER,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>COMMON MASTER</title>
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
						<h3>New <?=$form?></h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							<li class="active"><?=$form?> List</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!--Common overview start-->
		<div class="row">
			<?php if(in_array(CREATE_COMMON_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-4">
					<section class="panel">
						<header class="panel-heading">
							New Common Master
						</header>	
						<div class="panel-body">
							<form role="form" id="Common_add" action="javascript:;" method="post" name="Common_add">
								<div class="row">
								<div class="col-md-9">
									<div class="form-group">
										<label>Select Category *</label>
										<?php
											$str='';
											$query="SELECT common_category_id, common_category_name FROM tbl_common_category_mst WHERE isactive=1 AND isdelete=0";
											$rs_dispatch=$dbcon->query($query);	
										?>
										<select class="common_category" name="common_category_id" id="common_category_id" required >
	                    					<option value="">Select Category</option>
	                    					<option value="10000">All Category</option>
	                    					<?php 
	                    						while($rel= brp_mysqli_fetch_assoc($rs_dispatch))
												{	
													$sel=''; 
													if($rel['common_category_id']==$branchid){
														$sel ="selected='selected'";
													}
													$str .= '<option '.$sel.' value="'.$rel['common_category_id'].'">'.$rel['common_category_name'].'</option>';
												}
												echo $str;
	                    					?>
	                					</select>
						            </div>
								</div>

								<div class="form-group col-md-3">
									<input type="button" style="margin-top: 23px;" name="addCommonCategory" id="addCommonCategory" data-toggle="modal" data-target="" onclick="add_common_category();" class="btn btn-primary" value="+ Add"/>
								</div>
								</div>
								<div class="form-group">
									<label>Master Name *</label>
									<input class="form-control" type='text' required="" minlength="2" name='Common_name' id='Common_name' placeholder="Master Name" value='' />
								</div>
								<div class="form-group">
									<label>Description </label>
									<textarea class="form-control" rows="4" name="common_mst_desc" id="common_mst_desc" placeholder="Description" ></textarea>		
								</div>			  
								<button type="submit" class="btn btn-info">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(CREATE_COMMON_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-8">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
						Common Master List
						<!--<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>-->
							<?if($_SESSION['user_type'] == 2){?>					  
					<span class="tools pull-right">		
						<a href="javascript:;" onClick="tableToExcel('common-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	
					</span>
					<?}?>
						<!--</span>-->
					</header>
					<div class="col-md-5">
									<div class="form-group">
										<label>Select Category *</label>
										<?php
											$str='';
											$query="SELECT common_category_id, common_category_name FROM tbl_common_category_mst WHERE isactive=1 AND isdelete=0 order by common_category_id desc";
											$rs_dispatch=$dbcon->query($query);	
										?>
										<select class="select2" name="common_category_id" id="common_category_ids" required  onchange="load_Common_datatable();">
	                    					<option value="">Select Category</option>
	                    					
	                    					<?php 
	                    						while($rel= brp_mysqli_fetch_assoc($rs_dispatch))
												{	
													$sel=''; 
													if($rel['common_category_id']==$branchid){
														$sel ="selected='selected'";
													}
													$str .= '<option '.$sel.' value="'.$rel['common_category_id'].'">'.$rel['common_category_name'].'</option>';
												}
												echo $str;
	                    					?>
	                					</select>
						            </div>
								</div>
					<div class="panel-body">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="common-table">
								
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Master Name</th>
										<th>Category</th>
										<th>Date</th>
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
		<input type="hidden" name="coid" id="coid" value="<?=$end?>">
		<!--Common overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Edit Common </h3>
			
		</div>
		<div class="modal-body form">
		<form id="FormEditCommon" role="form" method="post" novalidate>
			<div class="form-group">
				<label>Common *</label>
				<?php
					$str='';
					$query="SELECT common_category_id, common_category_name FROM tbl_common_category_mst WHERE isactive=1 AND isdelete=0 order by common_category_id desc";
					$rs_dispatch=$dbcon->query($query);	
				?>
				<select class="common_category" name="common_category_id" id="e_common_category_id" required>
					<option value="">Select Common Category</option>
					<?php 
						while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
						{	
							$str .= '<option '.$sel.' value="'.$rel['common_category_id'].'">'.$rel['common_category_name'].'</option>';
						}
						echo $str;
					?>
				</select>
            </div>	        
			<div class="form-group">
				<label for="Commonid">Common Name</label>
				<input class="form-control" required="" minlength="2" type='text' name='edit_Common_name' id='edit_Common_name' value='' />
			</div>
			<div class="form-group">
				<label>Description </label>
				<textarea class="form-control" rows="4" name="edit_common_mst_desc" id="edit_common_mst_desc" placeholder="Description" ></textarea>		
			</div>		
			
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Common </button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<?php include_once($include1.'add_common_category.php');?>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/common_mst.js?<?=time()?>"></script>
<script>
var tableToExcel = (function() {
	var uri = 'data:application/vnd.ms-excel;base64,'
	, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
	, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
	, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
	return function(table, name) {
		if (!table.nodeType) table = document.getElementById(table)
		var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
		var coid= $('#coid').val();
	var link = document.createElement("a");
    link.download = "common-list-# "+coid + ".xls";
    link.href = uri + base64(format(template, ctx));
    link.click();
	}
})()
$(".select2").select2({
	width: '100%'
});

$(".common_category").select2({
width: '100%'
}).on('change', function() {
$(this).valid();
});
</script>
</body>
</html>
