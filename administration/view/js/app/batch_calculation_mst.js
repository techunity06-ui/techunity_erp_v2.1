$(document).ready(function() {
	load_currency_datatable();
	// validate vendor add form on keyup and submit
	$("#balty_add").validate({
		rules: {
			size_id: {
				required: true
			},
			batch_size_id: {
				required: true
			},batch_qty: {
				required: true
			}
		},
		messages: {
			size_id: {
				required: "Select size"
			},
			batch_size_id: {
				required: "Select Batch Size"
			},
			batch_qty: {
				required: "Enter Batch Qty"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditCurrency").validate({
		rules: {
			edit_size_id: {
				required: true
			},
			edit_batch_size_id: {
				required: true
			},
			edit_batch_qty: {
				required: true
			}		
		},
		messages: {
			edit_size_id: {
				required: "Select size"
			},
			edit_batch_size_id: {
				required: "Select Batch Size"
			},
			edit_batch_qty: {
				required: "Enter Batch Qty"
			}
		}
	});		
	
});
$("#balty_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#balty_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		size_id: $("#size_id").val(),
		batch_size_id: $("#batch_size_id").val(),
		batch_qty: $("#batch_qty").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/batch_calculation_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_currency_datatable();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#balty_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditCurrency").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditCurrency").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		size_id: $("#edit_size_id").val(),
		batch_size_id: $("#edit_batch_size_id").val(),
		batch_qty: $("#edit_batch_qty").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/batch_calculation_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_currency_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_balty(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/batch_calculation_mst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{		
				if(response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
					load_currency_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function edit_test(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/batch_calculation_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
		
			$("#edit_size_id").select2("val",obj.size_id);
			$("#edit_batch_size_id").select2("val",obj.batch_size_id);
			$("#edit_batch_qty").val(obj.batch_size);
			Unloading();
		}
	});	
}
function load_currency_datatable(){
	var branch_id = $('#branch_id').val();
	datatable = $("#balty-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO ADDED YET !",
		},
		"aLengthMenu": [[10, 30, 50, -1], [10, 30, 50, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/batch_calculation_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" },
				{"name": "branch_id", "value": branch_id }
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