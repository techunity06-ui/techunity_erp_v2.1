$(document).ready(function() {
	load_exp_head_datatable();
	// validate vendor add form on keyup and submit
	$("#expense_head_add").validate({
		rules: {
			expense_head_name: {
				required: true
			}
		},
		messages: {
			expense_head_name: {
				required: "Enter Expense Head Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditExpHead").validate({
		rules: {
			edit_expense_head_name: {
				required: true
			}
		},
		messages: {
			edit_expense_head_name: {
				required: "Enter Expense Head Name"			
			}
		}
	});		
	
});
$("#expense_head_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#expense_head_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		expense_head_name: $("#expense_head_name").val(),
		expense_head_model: $("#expense_head_model").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/expense_head_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("EXPENSE HEAD ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_exp_head_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("EXPENSE HEAD ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_expense_head_modal").modal("hide");
				$('#expense_head_id').append('<option value='+resp.expense_head_id+'>'+resp.expense_head_name+'</option>'); 
				$('#expense_head_id').select2("val",resp.expense_head_id);
				$("#expense_head_id").trigger('change'); 
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#expense_head_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditExpHead").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditExpHead").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		expense_head_name: $("#edit_expense_head_name").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/expense_head_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("EXPENSE HEAD UPDATED SUCCESSFULLY", "SUCCESS");
				load_exp_head_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditExpHead").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_expense_head(expense_head_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/expense_head_mst/',
			data: { mode : "delete", expense_head_id : expense_head_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("EXPENSE HEAD DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_exp_head_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_expense_head(expense_head_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/expense_head_mst/',
		data: { mode : "preedit", expense_head_id : expense_head_id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response); 
			$("#ModalEditExpHead").modal("show");
			$("#edit_id").val(expense_head_id);				
			$("#edit_expense_head_name").val(obj.expense_head_name);
			Unloading();
		}
	});	
}
function load_exp_head_datatable(){
	datatable = $("#expense-head-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/expense_head_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}