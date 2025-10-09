	<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	error_reporting(E_ALL);
	$form="Work Order";
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_WORK_ORDER_SLUG_VIEW,PRODUCTION_WORK_ORDER_SLUG_CREATE
	]);

	if(!in_array(PRODUCTION_WORK_ORDER_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }

    $company_config = getCompanyConfiguration($dbcon);
    $branch_id = $_SESSION['branch_id'];

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>WORK ORDER LIST</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?> List</h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li class="active"><?=$form?> List</li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading respadlr0">
								<div class='col-lg-4 col-md-6 col-xs-12'>
									<div class="form-group">
										<label class="control-label col-lg-4 col-md-4 col-xs-4 respad-l0">Choose Date</label>
										<div class=" col-lg-8 col-md-8 col-xs-8 respad-r0">
											<div class="input-group date form_datetime-component">
												<input type="hidden" id="from_date"  value="<?=$start?>">
												<input type="hidden" id="to_date"  value="<?=$end?>">
												<input type="text" id="rep_date"  onChange="reload_data();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>	
								<div class='col-lg-4 col-md-6 col-xs-12'>
									<?php if($branch_id=='0'){ ?>
												<div class="col-md-12">
													
													<?php echo getBranchBox($dbcon, $branch_id,$select_branchId, $branch_read, false, 'reload_data();'); ?>	
												</div>
											
											<?php }else{ ?>
												<input type="hidden" name="branch_id" id="branch_id" value="<?=$branch_id?>">
											<?php } ?>
								</div>
								<div class="col-lg-8 col-md-6 col-xs-12" style="margin: 12px">
									<div class="form-group">
										
										<div class="col-md-3">
											<label>
											<input id="status_pend" name="workorder_status" checked="checked" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_po_datatable();" class="" title="Pending" value="0">
											<div class='external-event label label-success ui-draggable' style='position: relative;width:70px;'>Pending</div>              
											
											</label>
										</div>
										<div class="col-md-3">
											<label>
											<input id="status_all" name="workorder_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_po_datatable();" class="" title="All" value="1">
											<div class='external-event label label-primary ui-draggable' style='position: relative;width:70px;'>Done</div>              
											
											</label>
										</div>
									</div>
								</div>
                        	
								<span class="tools pull-right respadr_15">
									<a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	

									<?php if(in_array(PRODUCTION_WORK_ORDER_SLUG_CREATE,$bulkAccessArray)){	?>
    										<a href="<?=ROOT.PRODUCTION_ROOT.'work_order_add'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
    									<?php } ?>	
								</span>
								<div class="col-md-12"	style="height:10px;" ></div>
							</header>	
							<div class="panel-body">
								<div class="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
										<tr>
											
											<th>WO No.</th>
											<th>Date</th>
											<th>Document No.</th>
											<th>SO No.</th>
											<th>SO Date</th>
											<!-- <th>Product Code</th> -->
											<th>Product Name</th>
											<th>Type</th>											
											<th>WO Qty</th>
											<th>Comp Qty</th>
											<th>Pend Qty</th>
											<th>Rej Qty</th>
											<th>Sc Qty</th>
											<th>Status</th>
											<th>Priority</th>
											<?php if($company_config['customer_show_in_production']){?>
											<th>Party Name</th>
										<?php } ?>
											<th>Branch</th>
											<th class="hidden-phone">Action</th>
										</tr>
										</thead>
										<tbody></tbody>				 
									</table>
								</div>
							</div>
						</section>
					</div>
				</section>
			</section>

			
		<?php include_once($include.'footer.php');?>
		</section>

		<div class="modal colored-header info" id="Modal_preiview_wo_image" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
               <div class="modal-content">
                  <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                     <h3 style="margin-top:-6px;" important!>Workorder Attachments</h3>
                  </div>
                  <div class="modal-body form">
                     <div class="form-group">
                       <!-- <div id="drawing_image_list"></div> -->
                        <div id="wo_preview_image_list"></div>
                     </div>   
                  </div>
                  <div class="modal-footer">
                     <input type="hidden" name="edit_id" id="edit_id" value="" />
                     <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                     
                  </div>
               </div>
            </div>
         </div>
		<?php include_once($include1.'work_order_details.php');?> 
		<?php include_once($include1.'work_order_reports.php');?> 
		<?php include_once($include1.'work_order_notes.php');?> 
		<?php include_once($include1.'work_order_notes.php');?> 
		<?php include_once($include1.'bom_costing_model.php');?> 
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/work_order.js"></script>
		<script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
				function cb(start, end) {
				$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			cb(moment().subtract(29, 'days'), moment());
			
		  
			$('.datepikerdemo').daterangepicker({       
					locale: {
						format: 'DD-MM-YYYY'
					},
				 "autoApply": true,	
				"startDate": $('#from_date').val(),
				"endDate": $('#to_date').val(),	
				ranges: {
				   'Today': [moment(), moment()],
				  // 'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				   //'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				   //'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				   'Last 3 Month': [moment().subtract(3, 'months'), moment().endOf('month')],
				   'Last 6 Month': [moment().subtract(6, 'months'), moment().endOf('month')],
				   'Last 1 Year': [moment().subtract(12, 'months'), moment().endOf('month')]
				}
			}, cb);
		$('.date-set').click(function(){
			   $('.datepikerdemo').trigger('click')
		});
		var tableToExcel = (function() {
		 var uri = 'data:application/vnd.ms-excel;base64,'
		   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
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
