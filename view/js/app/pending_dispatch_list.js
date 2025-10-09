$(document).ready(function() {
	load_dispatch_datatable();
}); 

function load_dispatch_datatable(){

	//var status=$('input[name=approved_status]:Checked').val();
	var date=$('#rep_date').val();
	
	$("#dispatch-list-datatable").dataTable({
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
		"sAjaxSource": root_domain+'app/pending_dispatch_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch" }, {"name": "date", "value": date } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function load_pend_disp(){
	Loading();
	
	var log_user_id=$('#log_user_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/pending_dispatch_list/',
		data: { mode : "load_pend_disp", log_user_id:log_user_id },
		success: function(resnse)
		{
			//console.log(resnse);
			var resp = JSON.parse(resnse);
			$('#pend_dispatch_tbody').html(resp.resp_html);
			Unloading();					
		}
	});
}