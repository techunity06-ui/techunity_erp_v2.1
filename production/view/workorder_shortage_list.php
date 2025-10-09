	<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	
	$form="Work Order Shortage List";
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
	    DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_VIEW,DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_CREATE,DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_UPDATE,DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_DELETE
	]);

	// if(!in_array(DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_VIEW,DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_CREATE,DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_UPDATE,DASHBOARD_WORKORDER_SHORTAGE_LIST_SLUG_DELETE,$bulkAccessArray)){
 //        header("Location: ".DOMAIN."permission_access");
 //    }
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>WORK ORDER LIST</title>
		<?php include_once($include.'include_css_file.php');?>
		<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
		<style type="text/css">
				 #process_left,#process_right{
   margin: 5px;
    border: 1px solid #cccccc;
    list-style: none;
    padding-left: 0;
    height: 200px;
    overflow: auto;
    /* width: 250px; */
    border-radius: 5px;
  }
.mb-5{
	margin-bottom: 5px;
}
  ul li{
    cursor: pointer;
    padding: 5px 10px;
  }


  .selected{
    background-color: blue;
    color: white;
     margin: 2px;
  }

  .bigBtn{
    height: 50px;
    width: 55px;
    margin-top: 35px;
    margin-left: -5px;
    font-size: 20px;
    font-weight: 900;
  }
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
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
								<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'load_po_datatable()'); ?>	
										</div>	
								<div class='col-lg-4 col-md-8 col-xs-12'>
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
								<!-- <div class="col-lg-8 col-md-6 col-xs-12">
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
								</div> -->
                        	
								<span class="tools pull-right respadr_15">
									<a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	
									<a href="<?=ROOT.PRODUCTION_ROOT.'work_order_add'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
								</span>
								<div class="col-md-12"	style="height:10px;" ></div>
							</header>	
							<div class="panel-body">
								<div class="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
										<tr>
											<th>Sr No.</th>
											<th>Product Name</th>
											<th>BOM Version</th>								
											<th>QTY</th>
											<th>Current Stock</th>
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
		<?php include_once($include.'include_js_file.php');?>   
		<?php include_once($include1.'shortage_stock_allocate_modal.php');?>
		<?php include_once($include1.'shortage_jobcard_model.php');?>
		<?php include_once($include1.'shortage_indent_modal.php');?>
		<script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/workorder_shortage_list.js"></script>
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
