$(document).ready(function() {
	load_pending_invoice_datatable();
}); 

function load_pending_invoice_datatable(){
	//var status=$('input[name=approved_status]:Checked').val();
	var date=$('#rep_date').val();
        var branch_id = $('#branch_id').val();
	var mode_type=$('input[name="mode_type"]:checked').val();//fetch/fetch_service
	
	$("#pending-invoice-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+finance_root_domain+'app/pending_invoice_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": mode_type }, {"name": "date", "value": date },{ "name": "branch_id", "value": branch_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
