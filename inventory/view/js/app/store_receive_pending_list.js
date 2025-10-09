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
	var release_status=$('input[name=release_status]:Checked').val();
	var date=$('#rep_date').val();
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
			"sAjaxSource": root_domain+inventory_domain+'app/store_receive_pending_list/',
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
function change_stock_status (stock_receive_id,ref_name) 
{
	$('#store_aprv_hist_modal').modal('show');
	$('#stock_receive_id').val(stock_receive_id);	
	$('#ref_name').val(ref_name);
	load_hist_datatable();
	load_release_dtl();
}
function load_hist_datatable(){
	var release_id = $('#ref_ord_id').val();
	
	$("#grn-history-datatable").dataTable({
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
		"sAjaxSource": root_domain+inventory_domain+'app/store_receive_pending_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_hist_datatable" }, { "name": "release_id", "value": release_id }  );
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
function add_apprv_hist(){	
	var form_data = {
		mode:"add_apprv_hist",
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		stock_receive_id:$('#stock_receive_id').val(),
		ref_name:$('#ref_name').val()
	};	
	
	//alert($('#ref_name').val());
	//alert($('#stock_receive_id').val());
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list/',
		data: form_data,
		success: function(response)
		{
			
			//return false;
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_hist_datatable();
			load_datatable();
			Unloading();
		}
	});	
}
function load_release_dtl(){
	var release_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+inventory_domain+'app/store_receive_pending_list/',
		data: { mode : "load_release_dtl", release_id:release_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_comp_div_sec').html(resp.mod_comp_div_sec);
		}		 
	});
}