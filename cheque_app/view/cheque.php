<?php
	session_start();
	include("../../config/config.php");
	include("../../include/common_functions/common_functions.php");
	include("../../config/session.php");	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Cheque Manager";
		include("../common/head.php");
		
	?>
</head>
<body>
<div id="cl-wrapper" class="sb-collapsed">
	<?php
		include("../common/menu.php");
	?>
	<div class="container-fluid" id="pcont">
		<?php
			include("../common/header.php");
		?>
    
		<div class="cl-mcont">	
			<?php
				include("../common/user_steps.php");
			?>
			
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li class="active"><?php echo $TITLE; ?></li>
				</ol>
			</div>		
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="pull-left">
						<!--<a href="<?php echo DOMAIN_CHEQUE.'new-cheque'; ?>"><button class="btn btn-success"><i class="fa fa-print"></i> New Cheque</button></a>-->
						<!--<a href="<?php echo DOMAIN_CHEQUE.'designs'; ?>"><button class="btn btn-primary"><i class="fa fa-th-large"></i> Design Cheque</button></a>-->
					</div>
					<div class="pull-right">
						<a href="<?php DOMAIN_CHEQUE; ?>export/cheques?_print" target="_blank">
							<button type="button" class="btn btn-default"><i class="fa fa-print"></i> Print Report</button>
						</a>
						<button onClick="tableToExcel('dynamic-table', 'Cheque');" type="button" class="btn btn-default"><i class="fa fa-bars"></i> Export CSV</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="header">							
							<h3><i class="fa fa-eye"></i> Filter</h3>
						</div>
						<div class="content">
							<form role="form"> 
								<div class="form-group">
									<label>Date Range :</label>
									<input type="text" style="width:100%" name="date_range" id="date_range" class="form-control" value="" placeholder="SELECT DATERANGE" tabindex="1" />
								</div>
								
								<div class="form-group">
									<label>Account :</label>
									<select id="ch_acc" name="ch_acc" class="select2" tabindex="1" required>
										<option value="-9" selected>NOT CONSIDERED</option>
										<?=getaccount($dbcon,$rel['acc_id'],'acc_id>0')?>
									</select>
								</div>
								
								<div class="form-group">
									<label>Payee :</label>
									<select id="ch_payee" name="ch_payee" class="select2" tabindex="2">
										<option value="-9" selected>NOT CONSIDERED</option>
									<?php
										
											$query = $dbcon->query("SELECT l_id,l_name FROM tbl_ledger  WHERE l_status !=2 AND user_id = '$_SESSION[user_id]' ORDER BY l_name");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['l_id'].'" data-name="'.$r['l_name'].'" >'.$r['l_name'].' - '.$r['city_name'].'</option>';
											}
										?>
									</select>
								</div>
								<div class="form-group">
									<label>Amount :</label>
									<div class="input-group">
										<input type="text" id="ch_amount" name="ch_amount" class="form-control" placeholder="Amount" />
										<span class="input-group-btn">
											<button class="btn btn-primary" id="sub_amount" name="sub_amount" type="button">Submit</button>
										</span>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
					<div class="block-flat">
						<div class="header">							
							<h3><i class="fa fa-clipboard"></i> All Cheques</h3>
						</div>
						<div class="content">
							<div class="table-responsive">
								<table class="no-border red datatable" id="dynamic-table">
									<thead class="no-border">
										<tr>
											<th>SR</th>
											<th>DATE</th>
											<th>CHEQUE NO.</th>
											<th>PAYEE</th>
											<th>ACCOUNT</th>
											<th>AMOUNT (<i class="fa fa-inr">)</th>
											<th>&nbsp;</th>
										</tr>
									</thead>
									<tfoot>
									<tr style="color:#f16e3f">
										<th colspan="5"  style="text-align:right">Total:</th>
										<th style="padding-left:15px;"></th>
									</tr>
									</tfoot>
									<tbody class="no-border-x">
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>			
		</div>
		
		
	</div> 
</div>
  
	<?php include("../common/js.php"); ?>
	<script type="text/javascript" src="js/bootstrap.daterangepicker/moment.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.daterangepicker/daterangepicker.js"></script>
	<script type="text/javascript">
		var cTable; var range = -9;
		$(document).ready(function(){
			$('#date_range').daterangepicker();
			//Load Invoices
			fetch_cheques();
			
			$('#date_range').on('apply.daterangepicker', function(ev, picker) {
				range = picker.startDate.format('YYYY-MM-DD')+','+picker.endDate.format('YYYY-MM-DD');
				cTable.fnClearTable();
				fetch_cheques();
			});
			$('#date_range').on('cancel.daterangepicker', function(ev, picker) {
				$('#date_range').val('');
				range = -9;
				cTable.fnClearTable();
				fetch_cheques();
			});
			
			$('#sub_amount').on('click', function(e) {
				e.preventDefault();
				cTable.fnClearTable();
				fetch_cheques();
			});
			
			$("#ch_payee").change(function(e) {
				e.preventDefault();
				cTable.fnClearTable();
				fetch_cheques();
			});
			$("#ch_acc").change(function(e) {
				e.preventDefault();
				cTable.fnClearTable();
				fetch_cheques();
			});
			
			$("#FormVoucher").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					$("#ModalVoucher").modal("hide");
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/voucher/',
						data: {type : "generate", id : $("#v_chid").val(), tds : $("#v_tds").val(), rec_name : $("#v_rec_name").val(), rec_mobno : $("#v_rec_mobno").val()},
						success: function(response)
						{
							Unloading();
							response=response.trim();
							console.log(response);
							var obj = jQuery.parseJSON( response );
							if (obj.response == "1") {
								bootbox.confirm("Voucher Generated Successfully. Do You want to print it ?",function(e) {
									if (e) {
										window.open(root_domain+'voucher/'+obj.id);
									}
									else {
										cTable.fnReloadAjax();
									}
								})
								
							}
							else {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 2000,
								});
							}
							
						}
					});
				}
			})
			
		});
		
		function fetch_cheques() {
			var ch_amnt = $("#ch_amount").val();
			if (ch_amnt == null || ch_amnt == "") {
				ch_amnt = -9; //not considered
			}
			console.log("Loading Cheques");
			cTable = $(".datatable").dataTable({
				"bDestroy": true,
				"bSort" : false,
				"bAutoWidth" : false,
				"bFilter" : true,
				"bProcessing": true,
				"bServerSide": true,
				"aoColumns": [
					null,
					null,
					null,
					null,
					null,
					null,
					{"sWidth": "20%"}
				],
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO CHEQUES ADDED YET !",
				},
				"aLengthMenu": [[10, 20, 30, 50,-1], [10, 20, 30, 50,'All']],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/cheque/index',
				"fnServerParams": function ( aoData ) {
					aoData.push(
						{ "name": "type", "value": "fetch" },
						{ "name": "date", "value": range },
						{ "name": "payee", "value": $("#ch_payee").val() },
						{ "name": "account", "value": $("#ch_acc").val() },
						{ "name": "amount", "value": ch_amnt }
					);
				},
				"fnDrawCallback": function( oSettings ) {
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				},
				"fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
			
			var iPageMarket = 0;
			for ( var i=0 ; i<aaData.length ; i++ )
			{
				iPageMarket += aaData[ aiDisplay[i] ][7]*1;
			}
			iPageMarket=getindian_rupee(iPageMarket);
			var nCells = nRow.getElementsByTagName('th');
			nCells[1].innerHTML = (iPageMarket );
						
			}
			}).fnSetFilteringDelay();
			
			//Search input style
			$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
			$('.dataTables_length select').addClass('form-control');
		}
		function getindian_rupee(x)
		{
			x=x.toString();
			var lastThree = x.substring(x.length-3);
			var otherNumbers = x.substring(0,x.length-3);
			if(otherNumbers != '')
				lastThree = ',' + lastThree;
			var res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;			
			return res;
		}
		function cancel_cheque(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					var form_data = {
						type: "cancelcheque",
						id : id
					};
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/cheque/',
						data: form_data,
						success: function(response)
						{
							response=response.trim();
							Unloading();
							if (response == "1") {
								$.gritter.add({
									text: 'CHEQUE CANCELLED SUCCESSFULLY !',
									class_name: 'success',
									time: 1500,
								});
								cTable.fnReloadAjax();
							}
							else {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 2000,
								});
								console.log(response);
							}
							
						}
					});
				}
			});
		}
		var _v = null;
		
		function print_voucher(id) {
			Loading(true);
			if (_v != null) {
				_v.abort();
			}
			_v = $.ajax({
				type: "POST",
				cache:false,
				url: root_domain+'app/voucher/',
				data: {type : "init_voucher", id : id},
				success: function(response)
				{
					console.log(response);
					Unloading();
					var obj = jQuery.parseJSON( response );
					$("#v_chdate").html(obj.cheque_date);
					$("#v_chnum").html(obj.cheque_number);
					$("#v_chpayee").html(obj.vender_name);
					$("#v_chacc").html(obj.acc_name+' - '+obj.bank_name+' ('+obj.acc_number+')');
					$("#v_chamnt").html(ToInNumber(parseFloat(obj.cheque_amount).toFixed(2)));
					$("#v_amnt").val(obj.cheque_amount);
					$("#v_oamount").val(ToInNumber(parseFloat(obj.cheque_amount).toFixed(2)));
					$("#ModalVoucher").modal("show");
					$("#v_chid").val(id);
					if (obj.mode == "0") {
						$("#v_tds").val(obj.v_tds).attr("disabled","disabled");
						$("#v_rec_name").val(obj.v_rec_name).attr("disabled","disabled");
						$("#v_rec_mobno").val(obj.v_rec_mobno).attr("disabled","disabled");
						$("#v_print").removeClass("hidden").attr("data-url",obj.v_id);
						$("#v_gen").addClass("hidden");
					}
					else {
						$("#v_tds").val("").removeAttr("disabled");
						$("#v_gen").removeClass("hidden");
						$("#v_print").addClass("hidden");
					}
					CalcTDS();
				}
			});
			
			
		}
		
		function CalcTDS() {
			var amnt = Number($("#v_amnt").val());
			var r_tds = 100 - Number($("#v_tds").val());
			var oamnt = (100 * amnt) / r_tds;
			$("#v_oamount").val(ToInNumber(parseFloat(oamnt).toFixed(2)));
		}
		
		function open_print_voucher() {
			var url = $("#v_print").attr("data-url");
			window.open(root_domain+"voucher/"+url);
		}
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
	
<!-- Modal -->
<div class="modal colored-header" id="ModalVoucher" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Print Voucher</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
				<table width="100%" class="table table-bordered">
					<tr>
						<td>Date:</td>
						<td><span id="v_chdate"></span></td>
						<td style="text-align: right;">Cheque Number:</td>
						<td><span id="v_chnum"></span></td>
					</tr>
					<tr>
						<td colspan="2">Payee:</td>
						<td colspan="2"><span id="v_chpayee"></span></td>
					</tr>
					<tr>
						<td colspan="2">Account:</td>
						<td colspan="2"><span id="v_chacc"></span></td>
					</tr>
					<tr>
						<td colspan="2">Cheque Amount:</td>
						<td colspan="2"><span id="v_chamnt"></span></td>
					</tr>
				</table>
			<form id="FormVoucher" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">TDS (%)</label>
					<input type="hidden" id="v_chid" value="" />
					<input type="hidden" id="v_amnt" value="" />
					<input type="text" id="v_tds" class="form-control parsley-validated" parsley-trigger="change" value="0" onKeyUp="CalcTDS()" required>
				</div>
				<div class="form-group">
					<label class="control-label">Original Amount</label>
					<input type="text" id="v_oamount" class="form-control parsley-validated" parsley-trigger="change" value="" disabled="disabled" value="0" required>
				</div>
				<div class="form-group">
					<label class="control-label">Receiver Name</label>					
					<input type="text" id="v_rec_name" class="form-control" value=""  >
				</div>
				<div class="form-group">
					<label class="control-label">Receiver Contact NO.</label>					
					<input type="text" id="v_rec_mobno" class="form-control" value=""  >
				</div>
			</div>
			<div class="modal-footer">
				<!--<input type="hidden" name="edit_token" id="v_token" value="<?php echo $token; ?>" />-->
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button id="v_gen" type="submit" class="btn btn-success btn-flat">Generate</button>
				<button id="v_print" type="button" class="btn btn-info btn-flat hidden" onClick="open_print_voucher()" data-url="">Print</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</body>
</html>
