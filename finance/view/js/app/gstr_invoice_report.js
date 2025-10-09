$(document).ready(function() {
	load_gstr_invoice_datatable();
		
});
function reload_data()
{
	load_gstr_invoice_datatable();
	
}

function load_gstr_invoice_datatable(){
	
	var date=$("#rep_date").val();

	$("#gstr-invoice-report-table").dataTable({
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
		"sAjaxSource": root_domain+finance_root_domain+'app/gstr_invoice_report/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"},{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$("#gstr-invoice-report-table").parent("div").addClass("tbl-overflow-row");
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function exportCsv() {
	var date=$("#rep_date").val();
	var url = root_domain +'generate_export?mode=gstr1_format_report&date=' + encodeURIComponent(date);
	window.location.href = url;
}