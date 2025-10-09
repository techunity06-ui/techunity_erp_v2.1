<?php 
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
$frmdt=date('d-m-Y');
$todt=date('d-m-Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>TRANSACTION DASHBOARD</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once('../include/include_top_menu.php');?>
		<?php include_once('../include/left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<?php 
				if(!empty($_SESSION['company_id']))
				{
					include_once('../include/transaction_dashboard.php');
				}
				?>
			</section>
		</section>
		<?php include_once('../include/footer.php');?>
	</section>
	<?php include_once('../include/include_js_file.php');?>   
		<!--<script src="<?=ROOT?>js/app/todo_mst.js"></script>
			<script src="<?=ROOT?>js/app/complaint.js?<?=time()?>"></script>-->
			<script>
				$(document).ready(function() {
					load_trans_datatable();
				}); 
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
				$(".select2").select2({
					width: '100%'
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
				function load_trans_datatable(){
					var start_date = $('#start_date').val();
					var end_date = $('#end_date').val();
					var cust_id = $('#cust_id').val();
					var type = $('#type').val();

					$("#transaction-table").dataTable({
						"bStateSave": true,
						"fixedHeader": true,
						"bAutoWidth" : false,
						"bFilter" : true,
						"bSort" : true,
						"bProcessing": true,
						"bDestroy": true,
						"bServerSide" : true,
						"oLanguage": {
							"sLengthMenu": "_MENU_",
							"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
							"sEmptyTable": "NO DATA ADDED YET !"
						},
						"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
						"iDisplayLength": 10,
						"sAjaxSource": root_domain + 'app/transaction_dashboard/',
						"fnServerParams": function ( aoData ) {
							aoData.push( {"name": "mode", "value": "fetch"}, 
								{"name": "cust_id", "value": cust_id},
								{"name": "type", "value": type},
								{"name": "start_date", "value": start_date},
								{"name": "end_date", "value": end_date});
						},
						"fnDrawCallback": function( oSettings ) {
							$('.ttip, [data-toggle="tooltip"]').tooltip();
						}
					}).fnSetFilteringDelay();

					//Search input style
					$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
					$('.dataTables_length select').addClass('form-control');
				}
				function task_done(trn_dashbord_user_id){
					$.ajax({
						type: "POST",
						url: root_domain+'app/transaction_dashboard/',
						data: { mode : "task_done",  trn_dashbord_user_id : trn_dashbord_user_id },
						success: function(response)
						{
							console.log(response);
							if(response.trim() == "1") {
								toastr.success("TASK DONE", "SUCCESS");
								load_trans_datatable();
								Unloading();
							}
							else if(response.trim() == "0") {
								toastr.warning("SOMETHING WRONG", "WARNING");
							}							
						}
					});	
				}
				function print_cust_label(){
					Loading(true);
					var trn_dashbord_user_id = $("#transaction-table input:checkbox:checked").map(function(){
						return $(this).val();
					}).toArray();
					if(trn_dashbord_user_id!="")
					{

						$.ajax({
							type: "POST",
							url: root_domain + 'app/transaction_dashboard/',
							data: { mode : "print_cust_label",  trn_dashbord_user_id:trn_dashbord_user_id},
							success: function(response)
							{
								toastr.success("TASK DONE SUCCESSFULLY", "SUCCESS");
								load_trans_datatable();
								// $("#bs-user_allocate").modal("hide");
								Unloading();							
							}
						});

					}
					else 
					{
						toastr.warning("SELECT DATA", "ERROR");
					}
					Unloading();
				}
			</script>
		</body>
		</html>
