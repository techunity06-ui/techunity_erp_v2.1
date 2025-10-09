$(document).ready(function() {
	load_currency_datatable();
	// validate vendor add form on keyup and submit
	$("#Size_add").validate({
		rules: {
			size_name: {
				required: true
			}
		},
		messages: {
			size_name: {
				required: "Enter Size Name"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditCurrency").validate({
		rules: {
			edit_size_name: {
				required: true
			}		
		},
		messages: {
			edit_size_name: {
				required: "Enter Size Name"
			}
		}
	});		
	
});
$("#Size_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#Size_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		size_name: $("#size_name").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/size_mst/',
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
		size_name: $("#edit_size_name").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/size_mst/',
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
			url: root_domain+administration_domain+'app/size_mst/',
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
		url: root_domain+administration_domain+'app/size_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
		
			$("#edit_size_name").val(obj.size_name);
			Unloading();
		}
	});	
}
function load_currency_datatable(){
	var branch_id = $('#branch_id').val();
	datatable = $("#Size-table").dataTable({
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
		"sAjaxSource": root_domain+administration_domain+'app/size_mst/',
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