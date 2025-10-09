$(document).ready(function() {
	load_exp_datatable();
	// validate vendor add form on keyup and submit
	$("#expense_add").validate({
		rules: {
			expense_head_id: {
				required: true
			},
			expense_name: {
				required: true
			}
		},
		messages: {
			expense_head_id: {
				required: "Choose Expense Head"			
			},
			expense_name: {
				required: "Enter Expense Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditExp").validate({
		rules: {
			edit_expense_head_id: {
				required: true
			},
			edit_expense_name: {
				required: true
			}
		},
		messages: {
			edit_expense_head_id: {
				required: "Choose Expense Head"		
			},
			edit_expense_name: {
				required: "Enter Expense Name"			
			}
		}
	});		
	
});
$("#expense_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#expense_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		expense_head_id: $("#expense_head_id").val(),
		expense_name: $("#expense_name").val(),
		expense_model: $("#expense_model").val(),
		ledger_name: $("#ledger_name").val(),
		exp_opn_balance: $("#exp_opn_balance").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/expense_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("LEDGER ADDED SUCCESSFULLY", "SUCCESS");
				$("#expense_head_id").select2("val","");
				$("#expense_name").val('');
				$("#exp_opn_balance").val('');
				
				$("#expense_form").addClass("ledger_forms");
				
				$('#ledger_name').val('');
				$('#ledger_grp').select2('val','');
				
				
				Unloading();
				//load_exp_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("EXPENSE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_expense_modal").modal("hide");
				$('#expense_id').append('<option value='+resp.expense_id+'>'+resp.expense_name+'</option>'); 
				$('#expense_id').select2("val",resp.expense_id);
				$("#expense_id").trigger('change'); 
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
			$('#expense_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditExp").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditExp").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		expense_head_id: $("#edit_expense_head_id").val(), 
		expense_name: $("#edit_expense_name").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/expense_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("EXPENSE UPDATED SUCCESSFULLY", "SUCCESS");
				load_exp_datatable();
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
			$("#ModalEditExp").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_expense(expense_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/expense_mst/',
			data: { mode : "delete", expense_id : expense_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("EXPENSE DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_exp_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_expense(expense_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/expense_mst/',
		data: { mode : "preedit", expense_id : expense_id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditExp").modal("show");
			$("#edit_id").val(expense_id);
			$("#edit_expense_head_id").select2("val",obj.expense_head_id);
			$("#edit_expense_name").val(obj.expense_name);
			Unloading();
		}
	});	
}
function load_exp_datatable(){
	datatable = $("#expense-table").dataTable({
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
		"sAjaxSource": root_domain+'app/expense_mst/',
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