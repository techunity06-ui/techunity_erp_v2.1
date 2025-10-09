$(document).ready(function() {
	load_product_type_datatable();
	// validate vendor add form on keyup and submit
	$("#product_type_add").validate({
		rules: {
			product_type_name: {
				required: true,
			}
		},
		messages: {
			product_type_name: {
				required: "Enter Product Type Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditproduct_type").validate({
		rules: {
			edit_product_type_name: {
				required: true,
			}
		},
		messages: {
			edit_product_type_name: {
				required: "Enter Product Type Name"			
			}
		}
	});		
	
});
$("#product_type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		product_type_name: $("#product_type_name").val(),
		process_required: $("#process_required").val(),
		pr_code_series: $("#pr_code_series").val(),
		pr_code_short: $("#pr_code_short").val(),
		product_type_model: $("#product_type_model").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/product_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("PRODUCT TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_product_type_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("PRODUCT TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_product_type_modal").modal("hide");
				$('#product_type_id').append('<option value='+resp.product_type_id+'>'+resp.product_type_name+'</option>'); 
				$('#product_type_id').select2("val",resp.product_type_id);
				$("#product_type_id").trigger('change'); 
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
			$('#product_type_add').trigger('reset'); 	
			$('#abranch_id').select2('val', '1000');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditproduct_type").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditproduct_type").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		product_type_name: $("#edit_product_type_name").val(),
		process_required: $("#edit_process_required").val(),
		pr_code_series: $("#edit_pr_code_series").val(),
		pr_code_short: $("#edit_pr_code_short").val(),
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/product_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			if(response.trim() == '1') {
				toastr.success("PRODCUT TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				load_product_type_datatable();
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
			$("#ModalEditproduct_type").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_product_type(product_type_id) 
{
	var r= confirm(" Are you want to delete ?");	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/product_type_mst/',
			data: { mode : "delete", product_type_id : product_type_id },
			success: function(response)
			{
				var resp = JSON.parse(response);
				if(resp.msg == "-1") {
					swal("CURRENT RECORD ALREADY USED BELOW MODULES", ""+resp.table+"", "warning");
         		    load_product_type_datatable();
					Unloading();
				}else if(resp.msg == "1") {
					toastr.success("PRODUCT TYPE DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_product_type_datatable();
					Unloading();
				}else if(resp.msg == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_product_type(product_type_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/product_type_mst/',
		data: { mode : "preedit", product_type_id : product_type_id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			//alert(obj.process_required);
			$("#ModalEditproduct_type").modal("show");
			$("#edit_id").val(product_type_id);				
			$("#edit_product_type_name").val(obj.product_type_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#edit_process_required").select2("val", obj.process_required);
			$("#edit_pr_code_short").val(obj.pr_code_short);
			$("#edit_pr_code_series").val(obj.pr_code_series);

			$("#FormEditproduct_type").valid();
			Unloading();
		}
	});	
}
function load_product_type_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#product_type-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bprocess_typeing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"s	_typeing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/product_type_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
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