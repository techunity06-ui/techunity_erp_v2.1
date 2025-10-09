//var datatable;
$(document).ready(function() {
	load_datatable();
});

function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}	
function load_datatable()
{	
	var branch_id=$('#branch_id').val();

	datatable = $("#dynamic-table").dataTable({
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
	"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
	"iDisplayLength": 10,
	"sAjaxSource": root_domain+production_domain+'app/request_jobwork_material/',
	"fnServerParams": function ( aoData ) {
		aoData.push( 
			{ "name": "mode", "value": "fetch" }
			);
	},
	"fnDrawCallback": function( oSettings ) {
		$('.ttip, [data-toggle="tooltip"]').tooltip();
	}
}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function request_material(job_work_id) 
{
	// show materia listing  later : now direct added
	// $('#store_accept_modal').modal('show');
Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_jobwork_material/',
		data: { 
			mode : "request_jobwork_material",
			job_work_id:job_work_id,
		},
		success: function(response)
		{
			if(response == '1') {
				toastr.success("JOBWORK REQUEST SUCCESSFULLY", "SUCCESS");
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
			$('#outside_jobwork_release_modal').modal('hide');
			load_datatable();
			Unloading();

		}
	});
}


function show_request_material_data(job_work_id){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/request_jobwork_material/',
		data: { mode : "store_request_using_model",job_work_id:job_work_id},
		success: function(response)
		{
			//alert(response);
			$('#store_request_data').html(response);
			$('#outside_jobwork_release_modal').modal('show');
			Unloading();
		}
	});
}
	

	