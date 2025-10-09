<?php 
	session_start();
	include('../include/urlfile.php');
	
	$form="Series Type";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_SERIES_TYPE_LIST,
        ADMINISTRATOR_SERIES_TYPE_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_SERIES_TYPE_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>SERIES TYPE</title>
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
		<!--Customer overview start-->
		<div class="row">
			<?php if(in_array(ADMINISTRATOR_SERIES_TYPE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="invoicetype_add" action="javascript:;" method="post" name="invoicetype_add">
								<?php //if($branch_id=='0'){ ?>
									<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										<select class="branch_validate" name="branch_id" id="abranch_id" required >
	                    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
											<?=getBranchBox_new($dbcon, $branch,'all');?>
	                					</select>
						            </div>
						        <?php } ?>
								<div class="form-group">
									<label for="invoice_type">Series Type*</label>
									<input type="text" class="form-control" id="invoice_type" name="invoice_type" placeholder="Series Type" />
								</div>
								<div class="form-group">
									<label for="taxinvoice_start">Start Series</label>
									<input type="number" min="0" class="form-control" id="taxinvoice_start" name="taxinvoice_start" placeholder="Start Series" />
								</div>
								
								<div class="form-group">
									<label for="series_type">Series Type</label>
									<select class="form-control" id="type_id" name="type_id" title="Series Type">
										<?=get_series_type($dbcon,"")?>
									</select>
								</div>

								<div class="form-group">
									<label for="invoice_format">Series Format</label>
									<select class="form-control" id="invoice_format" name="invoice_format"  onchange="format_valuechange(this.value);">
										<option value="0">None</option>
										<option value="1">Prefix</option>
										<option value="2">Suffix</option>
										<option value="3">Both</option>
									</select>								  
								</div>
								
								<div class="hidden form-group" id="format_value_div">
									<label for="invoice Type">Format Value</label>
									<input type="text" class="form-control" id="format_value" name="format_value" placeholder="eg.EXP, RS" onKeyUp="view_format(this.value)"/>
								</div>
								
								<div class="hidden form-group" id="end_format_value_div">
									<label for="invoice Type">End Format Value</label>
									<input type="text" class="form-control" id="end_format_value" name="end_format_value" placeholder="eg.EXP, RS" onKeyUp="view_format(this.value)"/>
								</div>
								<div class="hidden form-group" id="ex_format_div">
									<label for="invoice Type">Example Format : </label>
									<span id="ex_format" style="font-size:17px;"></span>							  
								</div> 
								<div class="form-group">
									<label for="gst_code">GST Code</label>
									<input type="text" class="form-control" id="gst_code" name="gst_code" placeholder="GST Code" />
								</div>
								<input type='hidden' name='mode' id='mode' value='add' />				  
								<button type="submit" class="btn btn-info">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_SERIES_TYPE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
						<?=$form?> List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
						<span class="tools pull-right">
							<!--<button class="btn btn-primary" data-original-title="Invoice Series Same" data-toggle="tooltip" data-placement="top" onClick="invoice_series_same()">Series Same</button>	-->	
						</span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="dynamic-table">
								<div class="col-md-12">
			                        <div class="col-md-6">
			                        <select class="select2" name="branch_id" id="branch_id" onchange="load_series_type_datatable()" required <?php if($companyConfiguration['branch_wise_manage']=='0'){ ?>disabled<?php } ?>>
	                    								<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                							</select>
			                          
			                        </div>
			                    </div>
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Series Type</th>
										<th>Starting Series</th>
										<th>Format</th>
										<th>GST Code</th>
										<th>Financial Year</th>
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
		
		<!--Customer overview end-->
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
			<h3>Edit Series Type</h3>				
		</div>
		<div class="modal-body form">
			<form id="FormEditinvoicetype" role="form" method="post" novalidate>
				<?php //if($branch_id=='0'){ ?>
					<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
					<div class="form-group">
						<label>Branch *</label>
						<?php
							$str='';
							$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
							$rs_dispatch=$dbcon->query($query);	
						?>
						<select class="branch_validate" name="branch_id" id="e_branch_id" required>
	    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
						<?=getBranchBox_new($dbcon, $branch,'all');?>
						</select>
		            </div>
		        <?php } ?>				
				<div class="form-group">
					<label for="invoice Type">Series Type*</label>
					<input type="text" class="form-control" id="edit_invoice_type" name="edit_invoice_type" placeholder="Invoice Type" />
				</div>
				<div class="form-group">
					<label for="invoice Type">Start Series</label>
					<input type="number" min="0" class="form-control" id="edit_taxinvoice_start" name="edit_taxinvoice_start" placeholder="Invoice Type" />
				</div>
				<div class="form-group">
					<label for="Series Type">Series Type</label>
					<select class="form-control" id="edit_type_id" name="edit_type_id" title="Series Type">
						<?=get_series_type($dbcon,"")?>
					</select>
				</div>
				<!--  <div class="form-group">
					<label for="invoice Type">Excise Invoice Start Series</label>
					<input type="number" min="0" class="form-control" id="edit_exciseinvoice_start" name="edit_exciseinvoice_start" placeholder="Invoice Start Series" />
				</div>	-->						  
				<div class="form-group">
					<label for="invoice Type">Series Format</label>
					<select class="form-control" id="edit_invoice_format" name="invoice_format"  onchange="edit_format_valuechange(this.value);">
						<option value="0">None</option>
						<option value="1">Prefix</option>
						<option value="2">Suffix</option>
						<option value="3">Both</option>
					</select>								  
				</div>
				
				<div class="hidden form-group" id="edit_format_value_div">
					<label for="invoice Type">Format Value</label>
					<input type="text" class="form-control" id="edit_format_value" name="format_value" placeholder="eg.EXP, RS"/>
				</div>
				<div class="hidden form-group" id="edit_end_format_value_div">
					<label for="invoice Type">Format Value</label>
					<input type="text" class="form-control" id="edit_end_format_value" name="edit_end_format_value" placeholder="eg.EXP, RS"/>
				</div>		
				<div class="form-group">
					<label for="invoice Type">GST Code</label>
					<input type="text" class="form-control" id="edit_gst_code" name="edit_gst_code" placeholder="GST Code" />
				</div>					  										  
				
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update</button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/series_type_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
	$(".branch_validate").select2({
width: '100%'
}).on('change', function() {
$(this).valid();
});
</script>
</body>
</html>