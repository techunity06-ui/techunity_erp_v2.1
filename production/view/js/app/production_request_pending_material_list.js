$(document).ready(function() {
	load_datatable();
});

function load_datatable()
{
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
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+production_domain+'app/production_request_pending_material_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
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


function change_request_approval_status(store_request_id){
	$('#preview_store_request_approval_hist_modal').modal('show');
	$('#apprv_store_request_id').html(store_request_id);
	$('#store_request_id').val(store_request_id);
	load_request_hist_datatable();
	load_request_dtl();
}
function load_request_hist_datatable(){
	var store_request_id = $('#store_request_id').val();
	
	$("#order-po-history-datatable").dataTable({
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
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain+production_domain+'app/production_request_pending_material_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_request_hist_datatable" }, { "name": "store_request_id", "value": store_request_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
	
}
function add_request_apprv_hist(){
	
	var form_data = {
		mode:"add_po_apprv_hist",
		approve_status:$('#request_approve_status').val(),
		approve_remark:$('#request_approve_remark').val(),
		purchase_order_id:$('#store_request_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_request_pending_material_list/',
		data: form_data,
		success: function(response)
		{
			$('#request_approve_status').select2("val","0");
			$('#request_approve_remark').val("");
			load_request_hist_datatable();
			//load_order_confirm_datatable();
			load_datatable();
			Unloading();
		}
	});	
}
function load_request_dtl(){
	var store_request_id = $('#store_request_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/production_request_pending_material_list/',
		data: { mode : "load_request_dtl", store_request_id:store_request_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}