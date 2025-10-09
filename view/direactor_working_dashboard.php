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
	<title>DIRECTOR DASHBOARD</title>
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
					include_once('../include/direactor_working_dashboard.php');
				}
				?>
			</section>
		</section>
		<?php include_once('../include/show_date_model.php');?>
		<?php include_once('../include/footer.php');?>
	</section>
	<?php include_once('../include/include_js_file.php');?>   
	<!--<script src="<?=ROOT?>js/app/todo_mst.js"></script>
		<script src="<?=ROOT?>js/app/complaint.js?<?=time()?>"></script>-->
		<script>
			$(document).ready(function() {
				load_notes();
				load_count_data();
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
			$("#direactor_working_dashboard").on('submit',function(e) {	
				var form = this;
				e.preventDefault();
				e.stopPropagation();	
				if (!$("#direactor_working_dashboard").valid()) {
					return false;
				} 

				form.submitted = true;	
				Loading(true);	
				$(this).attr("disabled","disabled");		
				$('#save').prop('disabled', true);
				var form_data=new FormData(this);	

				//Hide Form Submit Alert
				// setFormSubmitting();

				$.ajax({
					cache:false,
					url: root_domain + 'app/direactor_working_dashboard/',
					type: "POST",
					data: form_data,
					contentType: false,
					processData:false,
					success: function(response)
					{
						var arr = jQuery.parseJSON(response);			
						if(arr.msg == '1') {
							toastr.success("TASK ADDED SUCCESSFULLY", "SUCCESS");
							window.location=root_domain + 'direactor_working_dashboard';
						}
						else if(arr.msg == '0') {
							toastr.warning("SOMETHING WRONG", "ERROR");
						}
						else if(arr.msg == '-1') {
							toastr.info("ALREADY EXISTS", "INFO");
						}
						else if(arr.msg == 'update') {	
							toastr.success("TASK UPDATED SUCCESSFULLY", "SUCCESS");		
							window.location = root_domain + 'direactor_working_dashboard';	
						}
						Unloading();
						$('#direactor_working_dashboard').trigger('reset');	
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				});

			});
			function addnext() {
				var count = $('#count').val();
				var b = parseInt(count) + 1;
				var btn = document.createElement("div");
				btn.className += 'webflow-style-input';
				btn.innerHTML='<input class="" type="text" name="description" id="description'+count+'" placeholder="Write Your Note" onkeyup="add_notes(\'description'+count+'\',\'notes_id'+count+'\')"></input><input type="hidden" name="notes_id" id="notes_id'+count+'" value="note_add"><input type="hidden" name="mode" id="mode" value="notes_add"><button type="submit" onClick="opendatemodel(\'notes_id'+count+'\',\'\',\'\')"><i class="fa fa-circle"></i></button>';
				document.getElementById("showhtml").appendChild(btn);
				$('#count').val(b);
			}
			function getcheck(id,sid) {
				$('#'+id).css('display','block');
				$('#'+sid).css('display','none');
			} 
			function add_notes(descriptions, notes_ids){
				var description = $('#'+descriptions).val();
				var notes_id = $('#'+notes_ids).val();
				var mode = $('#mode').val();

				$.ajax({
					url: root_domain + 'app/direactor_working_dashboard/',
					type: "POST",
					data: { notes_id : notes_id, description : description, mode : mode},
					success: function(response)
					{
						var arr = jQuery.parseJSON(response);
						if(arr.msg != "0"){			
							$('#'+notes_ids).val(arr.msg);
						}
						Unloading();
						// $('#direactor_working_dashboard').trigger('reset');	
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				});
			}
			function load_notes(){
				var start_date = $('#start_date').val();

				$.ajax({
					url: root_domain + 'app/direactor_working_dashboard/',
					type: "POST",
					data: { start_date : start_date, mode : "load_notes"},
					success: function(response)
					{
						$("#showhtml").empty();
						$("#showhtml").append(response);
						Unloading();
						// $('#direactor_working_dashboard').trigger('reset');	
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				});
			}
			function opendatemodel(notes_id, id, status){
				if(status=="1"){
					toastr.success("TASK DONE", "SUCCESS");
				}else if(status=="3"){
					$('#show_date_models').modal('show');
					$('#notes_id').val(id);
					$('#notes_status').val(status);
					$('#assign').css('display','none');
					$('#done').css('display','none');
					$('#cancel').css('display','none');
				} else{
					$('#show_date_models').modal('show');
					$('#notes_id').val(id);
					$('#notes_status').val(status);
				}
			}
			function assign_date(status){
				var notes_date = $('#notes_date').val();
				var notes_id = $('#notes_id').val();
				var mode = "assign_date";

				$.ajax({
					url: root_domain + 'app/direactor_working_dashboard/',
					type: "POST",
					data: { notes_date : notes_date, mode : mode, notes_id : notes_id, status : status},
					success: function(response)
					{
						
						var arr = jQuery.parseJSON(response);
						if(arr.msg != "0"){	
							$('#show_date_models').modal('hide');
							load_notes();
						}
						Unloading();
						// $('#direactor_working_dashboard').trigger('reset');	
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				});
			}
			function load_count_data() {
				$.ajax({
					url: root_domain + 'app/direactor_working_dashboard/',
					type: "POST",
					data: { mode : "getdata"},
					success: function(response)
					{
						var data = JSON.parse(response);
						$('#pocount').html(data.purchse_order_pending_approval);
						$('#poamount').html('₹ '+data.po_pending_amount);
						$('#poficount').html(data.po_finance_aprooval);
						$('#pofiamount').html('₹ '+data.po_finance_amount);
						$('#socount').html(data.so_aprooval);
						$('#soamount').html('₹ '+data.so_pending_amount);
						$('#oacount').html(data.order_accept_aprooval);
						$('#oaamount').html('₹ '+data.order_accept_pending_amount);
						$('#quotcount').html(data.quotation_pending_count);
						$('#quotamount').html('₹ '+data.quotation_pending_amount);
						$('#indentcount').html(data.pending_indent);
						$('#indentamount').html(''+data.pending_indent_amt);
						$('#invoicecount').html(data.invoice_count);
						$('#invoiceamount').html('₹ '+data.invoice_amount);
						Unloading();	
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log(textStatus, errorThrown);
					}
				});
			}
		</script>
	</body>
	</html>
