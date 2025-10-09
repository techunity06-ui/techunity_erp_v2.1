//var datatable;
$(document).ready(function() {
	load_pend_task();
});	

function load_pend_task(){
	var rep_date = $('#rep_date').val();
	var user_id = $('#user_id').val();
	datatable = $("#dynamic-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"bStateSave": true,
		"fnStateSave": function (oSettings, oData) {
			localStorage.setItem('offersDataTables', JSON.stringify(oData));
		},
		"fnStateLoad": function (oSettings) {
			return JSON.parse(localStorage.getItem('offersDataTables'));
		},
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/report_delay_followup/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report" },
				{ "name": "rep_date", "value": rep_date },
				{ "name": "user_id", "value": user_id });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function exportCsv() {
	var rep_date = $('#rep_date').val();
	var user_id = $('#user_id').val();

	var url = root_domain +'generate_export?mode=delay_followup&rep_date=' + encodeURIComponent(rep_date) + '&user_id=' + encodeURIComponent(user_id);
	window.location.href = url;
}