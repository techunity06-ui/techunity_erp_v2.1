$(document).ready(function() {
	load_currency_datatable();
	
	// validate vendor add form on keyup and submit
	$("#currency_add").validate({
		rules: {
			currency_name: {
				required: true,
				minlength: 3
			},
			currency_code: {
				required: true
			},
			currency_symbol: {
				required: true
			},
			currency_in_word: {
				required: true
			},
			currency_in_word_end: {
				required: true
			},
			currency_rate: {
				number:true,
				required:true
			}
		},
		messages: {
			currency_name: {
				required: "Enter Currency Name",
				minlength: "Your Currency Name must consist of at least 3 characters"
			},
			currency_code: {
				required: "Enter Currency Code"
			},
			currency_symbol: {
				required: "Enter Currency Symbol"
			},
			currency_in_word: {
				required: "Enter Currency In Word"
			},
			currency_in_word_end: {
				required: "Enter Currency In Word"
			},
			currency_rate: {
				required: "Enter Currency Rate"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditCurrency").validate({
		rules: {
			edit_currency_name: {
				required: true,
				minlength: 3
			},
			edit_currency_code: {
				required: true
			},
			edit_currency_symbol: {
				required: true
			},
			edit_currency_in_word: {
				required: true
			},
			edit_currency_in_word_end: {
				required: true
			},
			edit_currency_rate: {
				number:true,
				required:true
			}	
		},
		messages: {
			edit_currency_name: {
				required: "Enter Currency Name",
				minlength: "Your Currency Name must consist of at least 3 characters"
			},
			edit_currency_code: {
				required: "Enter Currency Rate",
			},
			edit_currency_symbol: {
				required: "Enter Currency Symbol",
			},
			edit_currency_in_word: {
				required: "Enter Currency In Word",
			},
			edit_currency_in_word_end: {
				required: "Enter Currency In Word",
			},
			edit_currency_rate: {
				required: "Enter Currency Rate",
			}		
		}
	});		
	
});
$("#currency_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#currency_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		currency_name: $("#currency_name").val(),
		currency_code: $("#currency_code").val(),
		currency_symbol: $("#currency_symbol").val(),
		currency_in_word: $("#currency_in_word").val(),
		currency_in_word_end: $("#currency_in_word_end").val(),
		currency_rate: $("#currency_rate").val(),
		//branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/currencynewmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("CURRENCY ADDED SUCCESSFULLY", "SUCCESS")
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
			$('#currency_add').trigger('reset');	
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
		currency_name: $("#edit_currency_name").val(),
		currency_code: $("#edit_currency_code").val(),
		currency_symbol: $("#edit_currency_symbol").val(),
		currency_in_word: $("#edit_currency_in_word").val(),
		currency_in_word_end: $("#edit_currency_in_word_end").val(),
		currency_rate: $("#edit_currency_rate").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/currencynewmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("CURRENCY UPDATED SUCCESSFULLY", "SUCCESS");
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
function delete_currency(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/currencynewmst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{		
				if(response.trim() == "1") {
					toastr.success("CURRENCY DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain+'app/currencynewmst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
		
			$("#edit_currency_name").val(obj.currency_name);
			$("#edit_currency_code").val(obj.currency_code);
			$("#edit_currency_symbol").val(obj.currency_symbol);
			$("#edit_currency_in_word").val(obj.currency_in_word);
			$("#edit_currency_in_word_end").val(obj.currency_in_word_end);
			$("#edit_currency_rate").val(obj.currency_rate);
			//$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_currency_datatable(){
	//var branch_id = $('#branch_id').val();

	datatable = $("#currency-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO CURRENCY ADDED YET !",
		},
		"aLengthMenu": [[10, 30, 50, -1], [10, 30, 50, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/currencynewmst/',
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