//var datatable;
$(document).ready(function() {
	load_pend_task_report();
});	

function load_pend_task_report(){
	var rep_date = $('#rep_date').val();
	var stage_id = $('#stage_id').val();
	var user_id = $('#user_id').val();
	var cust_id = $('#cust_id').val();
	var state_id = $('#state_id').val();
	var city_id = $('#city_id').val();
	var t_id = $('#t_id').val();
	var fil_task_type_id = $('#fil_task_type_id').val();
	datatable = $("#dynamic-tables").dataTable({
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
		"sAjaxSource": root_domain + crm_domain + 'app/report_pending_task/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_report" },
				{ "name": "rep_date", "value": rep_date },
				{ "name": "stage_id", "value": stage_id },
				{ "name": "user_id", "value": user_id },
				{ "name": "state_id", "value": state_id },
				{ "name": "city_id", "value": city_id },
				{ "name": "cust_id", "value": cust_id },
				{ "name": "t_id", "value": t_id },
				{ "name": "fil_task_type_id", "value": fil_task_type_id });
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
	var stage_id = $('#stage_id').val();
	var user_id = $('#user_id').val();
	var cust_id = $('#cust_id').val();
	var state_id = $('#state_id').val();
	var city_id = $('#city_id').val();
	var t_id = $('#t_id').val();
	var fil_task_type_id = $('#fil_task_type_id').val();

	var url = root_domain +'generate_export?mode=pending_task&rep_date=' + encodeURIComponent(rep_date) + '&stage_id=' + encodeURIComponent(stage_id) + "&user_id=" + encodeURIComponent(user_id) + "&cust_id=" + encodeURIComponent(cust_id) + "&state_id=" + encodeURIComponent(state_id) + "&city_id=" + encodeURIComponent(city_id) + "&t_id=" + encodeURIComponent(t_id) + "&fil_task_type_id=" + encodeURIComponent(fil_task_type_id);
	window.location.href = url;
}