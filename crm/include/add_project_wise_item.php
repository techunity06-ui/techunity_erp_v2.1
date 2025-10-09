<?php
$companySettings = getCompanySettings($dbcon);
$project_wise_item_rate = '';
if($companySettings) {
   $project_wise_item_rate = $companySettings['project_wise_item_rate'];
}
?>
<div class="modal colored-header info " id="add_project_wise_item_modal" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg" style="width: 1100px;height: 500px">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Project Wise Item</h3>
	</div>
	<div class="modal-body form">
		<div class="row">
			<div id="sale_productdata"></div>
		</form>
	</div>
</div>	
</div>
</div><!-- /.modal-content -->
<?php include_once('../../include/include_js_file.php');?>