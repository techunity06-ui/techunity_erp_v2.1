<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
$form="Quotation Detail Report";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$start=date('1-m-Y');
$end=date("d-m-Y");
$countryid = '101';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$form?></title>
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<script>
/*function generate_report(){
	var date=$('#rep_date').val();
	var t_id=$('#t_id').val();
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/report_inq_bydate/',
		data: { mode : "generate_report", date:date, t_id:t_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
		}
	});	
}*/
</script>
<section id="main-content">
	
	<section class="wrapper">
		
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
<!--						<span class="tools pull-right">
							<a href="<?php //=ROOT.'report_list'?>"><button type="button" class="btn btn-info"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> Report List</button></a>	
						</span>-->
						
						<h3 style=""><?=$form?> </h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="<?=ROOT.CRM_ROOT.'crm_report_list'?>"> CRM Report List</a></li>
							<li><?=$form?></li>
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
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Choose Date</label>
								<div class="col-md-7">
									<div class="input-group date form_datetime-component">
										<input type="hidden" id="from_date" value="<?=$start?>">
										<input type="hidden" id="to_date" value="<?=$end?>">
										<input type="text" id="rep_date" onChange="generate_chart_report();" class="form-control datepikerdemo" value="">
										<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
										</span>
									</div>
								</div>
							</div>
						</div>
						
						<span class="tools pull-right"> 
							<a href="javascript:;" onClick="tableToExcel('adv-table', '<?=$form?>')" ><button class="btn btn-primary btn-flat" >Export Excel</button></a>	
						</span> 
						<div class="clearfix"></div>
						<div class="col-md-12" style="padding-top:10px;"></div>
						<!-- <div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Territory</label>
								<div class="col-md-7">
									<select class="select2" id="t_id" name="t_id" onChange="generate_chart_report()">
										<option value="">Choose Territory</option>
										<?=get_all_territory($dbcon,"");?>
									</select>
								</div>
							</div>
						</div> -->

						<div class="col-md-5">
							<div class="form-group">
								<label class="col-md-4 control-label">Company * </label>
                                <div class="col-md-7"> 
                                    <select class="select2" id="cust_id" name="cust_id" onchange="generate_chart_report()">
                                        <?= getcustomer($dbcon,$cust_id) ?>
                                    </select>
                                </div>
							</div> 
						</div>

						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Product Category</label>
								<div class="col-md-7">
									<select class="select2" name="product_category" id="product_category" onchange="generate_chart_report();">
                                        <?= get_all_category($dbcon, $rel['product_category']); ?>
                                    </select>
								</div>
							</div>
						</div>

						<div class="clearfix"></div>
						<div class="col-md-12" style="padding-top:10px;"></div>

						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Product</label>
								<div class="col-md-7">
									<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="generate_chart_report();"/>
									<!-- <select class="select2" id="product_id" name="product_id[]" onchange="generate_chart_report()" placeholder="Choose Products" multiple="multiple">
										<?php //=getproduct($dbcon,"");?>
									</select> -->
								</div>
							</div>
						</div>

						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Quotation Type</label>
								<div class="col-md-7">
									<select class="select2" id="quot_type" name="quot_type" onChange="generate_chart_report()">
										<option value="">Choose Quotation Type</option>
										<option value="0">Domestic</option>
										<option value="1">Export</option>
									</select>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-12" style="padding-top:10px;"></div>

						<div class="col-md-5">
                            <div class="form-group">
                                <label class="col-md-4 control-label" >Quotation Status</label>
                                <div class="col-md-7"> 
                                    <select class="select2" id="approve_status" name="approve_status" onChange="generate_chart_report();">
                                       	<option value="">Choose Quotation Status</option>
										<option value="0">Pending</option>
										<option value="1">Approved</option>
										<option value="2">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Quotation No</label>
								<div class="col-md-7">
									<select class="select2" id="quotation_id" name="quotation_id" onChange="generate_chart_report()">
										<option value="">Choose Quotation</option>
										<?=get_quotation_no($dbcon,"");?>
									</select>
								</div>
							</div>
						</div>

						<div class="clearfix"></div>
						<div class="col-md-12" style="padding-top:10px;"></div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Branch</label>
								<div class="col-md-7">
									<select class="select2" id="branch_id" name="branch_id" onChange="generate_chart_report()">
										<?=get_branch($dbcon,"");?>
									</select>
								</div>
							</div>
						</div>

						
						<!-- <div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Industry Type</label>
								<div class="col-md-7">
									<select class="select2" id="industry_type" name="industry_type" onChange="generate_chart_report()">
										<option value="">--Select Party Industry--</option>
										<?=get_customer_industries($dbcon,'');?>
									</select>
								</div>
							</div>
						</div> -->

						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Country</label>
								<div class="col-md-7">
									<select class="select2" name="c_add_country" id="c_add_country" onChange="load_state(this.value,'c_add_state','');generate_chart_report()">
										<?=get_country($dbcon,$countryid)?>				
									</select>
								</div>
							</div>
						</div>
						
						<div class="clearfix"></div>
						<div class="col-md-12" style="padding-top:10px;"></div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">State</label>
								<div class="col-md-7">
									<select class="select2" name="c_add_state" id="c_add_state" onChange="load_city(this.value,'c_add_city','');generate_chart_report()">
										<option value="">Select State</option>	
										<?php //=getstate($dbcon,$rel['stateid'])?>				
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">City</label>
								<div class="col-md-7">
									<select class="select2" name="c_add_city" id="c_add_city" onchange="generate_chart_report()">
										<option value="">Select City</option>	
									</select>
								</div>
							</div>
						</div>

						<div class="clearfix"></div>
						<div class="col-md-12" style="padding-top:10px;"></div>
						<div class="col-md-5">
							<div class="form-group">
								<label for="user_id" class="col-md-4 control-label">User</label>
								<div class="col-md-7">
									<select class="select2" name="user_id" id="user_id" onchange="generate_chart_report()">
										<?=get_users_typewise($dbcon, '', '')?>
									</select>
								</div>
							</div>
						</div>

						<div class="clearfix"></div>
						
					</header>	
					<div class="clearfix"></div>
					<div class="panel-body">
						<!-- <div class="row">
							<div class="col-md-12 margin_row">
								<div class="col-md-2">
									<button class="btn btn-dark btn-flat" onClick="clear_lead_by_source_report();" style="margin-right:20px;"><i class="fa fa-remove"></i> Clear Chart</button>
								</div>
								<div class="col-md-8">
									<div id="report_inq_bydate" style="width: 900px; height: 400px;"></div>
								</div>
								<div class="col-md-2">
								</div>
							</div>
						</div> -->
						<div class="adv-table" id="adv-table" style="overflow-x: scroll;">
							
						</div>
					</div>
				</section>
			</div>
		</div>	
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php
include_once('../../include/footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<?php include_once('../../include/include_report_js_file.php');?>  
<script src="<?=ROOT.CRM_ROOT?>js/app/quotation_report.js?<?=time()?>"></script>  
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
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		}
	}, cb);
	$('.date-set').click(function(){
		$('.datepikerdemo').trigger('click')
	});
</script>
<script type="text/javascript">
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

<?php 
	echo "<script>load_state(".$countryid.",'c_add_state',".$stateid.")</script>";
?>
</body>
</html>
