$(document).ready(function() {
	load_quotation_print_block_setup_datatable();       
	show_data();
// validate vendor add form on keyup and submit
$("#quotation_print_block_setup_add").validate({
	rules: {
		priority: {
			required: true
		},
	},
	messages: {
		priority: {
			required: "Enter Block Name"			
		},
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditquotation_print_block_setup").validate({
	rules: {
		e_priority: {
			required: true
		}
	},
	messages: {
		e_priority: {
			required: "Enter Block Name"			
		}
	}
});		

});
$("#quotation_print_block_setup_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#quotation_print_block_setup_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		quotation_print_block_id: $("#quotation_print_block_id").val(),
		priority: $("#priority").val(),
		mode:"Add",
		quotation_print_block_setup_add : $("#quotation_print_block_setup_add").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain +'app/quotation_print_block_setup/',
		type: "POST",
		data: form_data,
		success: function(responses)
		{
			//console.log(response);
			var resp=JSON.parse(responses);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("QUOTATION PRINT BLOCK ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_quotation_print_block_setup_datatable();
				show_data();
				$("#priority").val("");
				$("#quotation_print_block_id").select2("val","");
			}
			else if(response.trim() == '2') {
				toastr.success("QUOTATION PRINT BLOCK ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-group-modal").modal("hide");
				//$('#product_group').append('<option value='+resp.quotation_print_setup_id+'>'+resp.block_name+'</option>');	
				$('#block_name').val(resp.block_name);
				//$("#product_group").trigger('change');
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
			$('#quotation_print_block_setup_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditquotation_print_block_setup").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditquotation_print_block_setup").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_quotation_print_block_id: $("#e_quotation_print_block_id").val(),
		e_priority: $("#e_priority").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + 'app/quotation_print_block_setup/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("QUOTATION PRINT BLOCK UPDATED SUCCESSFULLY", "SUCCESS");
				load_quotation_print_block_setup_datatable();
				show_data();
				$("#e_priority").val("");
				$("#e_quotation_print_block_id").select2("val","");
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
			$("#ModalEditquotation_print_block_setup").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_quotation_print_block_setup(id) 
{
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + 'app/quotation_print_block_setup/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				
				if(response.trim() == "1") {
					toastr.success("QUOTATION PRINT BLOCK DELETE SUCCESSFULLY", "SUCCESS");
					load_quotation_print_block_setup_datatable();
				}
				else if(response.trim() == "-1") {
					toastr.error("USED QUOTATION PRINT BLOCK CAN'T BE DELETED !!!", "WARNING"); 
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function edit_quotation_print_block_setup(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + 'app/quotation_print_block_setup/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditquotation_print_block_setup").modal("show");
			$("#edit_id").val(obj.quotation_print_setup_id);
			// CKEDITOR.instances['e_block_formate'].setData(obj.block_formate);
			//$("#e_block_formate").val(obj.block_formate);
			$("#e_priority").val(obj.priority);
			$("#e_quotation_print_block_id").select2("val", obj.quotation_print_block_id);
			Unloading();
		}
	});	
}
function load_quotation_print_block_setup_datatable(){
	$("#quotation_print_block_setup-datatable").dataTable({
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
		"sAjaxSource": root_domain + 'app/quotation_print_block_setup/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" }
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
function show_data(){
	var form_data = {
		quotation_print_block_id: $("#quotation_print_block_id").val(),
		priority: $("#priority").val(),
		mode:'show_data',
		is_ajax: 1
	};
	$.ajax({
		cache:false,
		url: root_domain + 'app/quotation_print_block_setup/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			$("#show_print_formate").html(response);					
		},
	});
}