<?php 
	session_start();
	include('../include/urlfile.php');
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        ADMINISTRATOR_GROUP_READ,
        ADMINISTRATOR_GROUP_ADD
    ]);

    if(!in_array(ADMINISTRATOR_GROUP_READ,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$form="Group List";
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$branch_id = $_SESSION['branch_id'];
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
    $class = "col-sm-12";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>GROUP LIST</title>
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
							  <li class="active"><?=$form?></li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--unit overview start-->
			<?php include_once($include.'country_unit_city.php');?>

		        <div class="row">
            <?php if(in_array(ADMINISTRATOR_GROUP_ADD,$bulkAccessArray)){
                $class = "col-sm-9";
            ?>
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New Group List
					</header>	
					<div class="panel-body">
						<form role="form" id="group_add" action="javascript:;" method="post" name="group_add">
                                                            
                                                            <div class="form-group">
								  <label>Sub Group Name *</label>
								 <input class="form-control" type='text' name='g_name' id='g_name' value='' />
							    </div>
								
								<div class="form-group">
								  <label>Select Group *</label>
								  <select class="select2" name="g_parent" id="g_parent" onchange="get_form_type(this.value,'g_form')">
									<?=get_all_group($dbcon,$id);?>
								  </select>
							    </div>
								
								<div class="form-group">
								  <label>Opening  Balance</label>
								 <input class="form-control numbersOnly copyPastNotAllowed" type='text' name='g_opening' id='g_opening' value='' onkeypress="return validateFloatKeyPress(this,event)" />
							    </div>
								
								<div class="form-group">
									<label for="Group Type">Start Series</label>
									<input type="number" min="0" class="form-control" id="group_series_start" name="group_series_start" placeholder="Start Series" />
								</div>
								<div class="form-group">
									<label for="series_format">Series Format</label>
									<select class="form-control" id="series_format" name="series_format"  onchange="format_valuechange(this.value);">
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

								<div class="form-group" id="">
									<label for="invoice Type">Priority : </label>
									<span id="ex_priority" style="font-size:17px;"></span>
									<input type="text" class="form-control numbersOnly" id="group_priority" name="group_priority" placeholder="priority" />
								</div>

								
								<div class="form-group">
									<input class="form-control" type='hidden' name='g_form' id='g_form' value='' />
							    </div>
								
							  	<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
								<button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
            <?php } ?>

			<div class="<?= $class ?>">
			<section class="panel">
				  <header class="panel-heading">
					  Group List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					
				 </span>
				  </header>
                                    
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>group Name</th>
						<th>Parent group</th>
						<th>Starting Series</th>
						<th>Format</th>
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
		  
		  <!--unit overview end-->
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
				<h3>Edit group</h3>
				
			</div>
                    <div class="modal-body form">
			<form id="FormEditunit" role="form" method="post" novalidate>                            
				<div class="form-group">
				  <label for="unitid">group Name *</label>
				   <input class="form-control" type='text' name='e_g_name' id='e_g_name' value='' />
				</div>	

				<div class="form-group">
				   <label for="unitid">Parent group *</label>
				   <select class="form-control" name="e_g_parent" id="e_g_parent" onchange="get_form_type(this.value,'e_g_form')">
					 
				   </select>
				</div>

				<div class="form-group">
				   <label for="unitid">Opening Balance</label>
				   <input type="text" class="form-control  numbersOnly copyPastNotAllowed" name="e_g_opening" id="e_g_opening" onkeypress="return validateFloatKeyPress(this,event)" />
				</div>	
				
				<div class="form-group">
					<label for="invoice Type">Start Series</label>
					<input type="number" min="0" class="form-control" id="edit_taxinvoice_start" name="edit_taxinvoice_start" placeholder="Invoice Type" />
				</div>
									  
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

				<div class="form-group" id="">
					<label for="invoice Type">Priority : </label>
					<span id="ex_priority" style="font-size:17px;"></span>
					<input type="text" class="form-control numbersOnly" id="edit_group_priority" name="edit_group_priority" placeholder="priority" />
				</div>
				
				<div class="form-group">
				   <input type="hidden" class="form-control" name="e_g_form" id="e_g_form" />
				</div>					
				
			</div>
                            <div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<input type="hidden" name="edit_pid" id="edit_pid" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update group</button>
                            </div>
                        </form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/group_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
$(".branch_validate").select2({
   width: '100%'
}).on('change', function() {
    $(this).valid();
});

function validateFloatKeyPress(el, evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    var number = el.value.split('.');
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    //just one dot
    if(number.length>1 && charCode == 46){
         return false;
    }
    //get the carat position
    var caratPos = getSelectionStart(el);
    var dotPos = el.value.indexOf(".");
    if( caratPos > dotPos && dotPos>-1 && (number[1].length > 1)){
        return false;
    }
    return true;
}

function getSelectionStart(o) {
	if (o.createTextRange) {
		var r = document.selection.createRange().duplicate()
		r.moveEnd('character', o.value.length)
		if (r.text == '') return o.value.length
		return o.value.lastIndexOf(r.text)
	} else return o.selectionStart
}
</script>
  </body>
</html>
