$(document).ready(function() {
	load_model_datatable();
	// validate vendor add form on keyup and submit
	$("#model_add").validate({
		rules: {
			product_id: {
				required: true
			},
			model_name: {
				required: true
			}
		},
		messages: {
			product_id: {
				required: "Choose Product"			
			},
			model_name: {
				required: "Enter Model Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditModel").validate({
		rules: {
			edit_product_id: {
				required: true
			},
			edit_model_name: {
				required: true
			}
		},
		messages: {
			edit_product_id: {
				required: "Choose Product"			
			},
			edit_model_name: {
				required: "Enter Model Name"			
			}
		}
	});		
	
});
$("#model_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#model_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		product_id: $("#product_id").val(),
		model_name: $("#model_name").val(),
		model_desc: $("#model_desc").val(),
		model_model: $("#model_model").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/model_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("MODEL ADDED SUCCESSFULLY", "SUCCESS");
				$('#product_id').select2("val","");
				Unloading();
				load_model_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("MODEL ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_zone_modal").modal("hide");
				$('#model_id').append('<option value='+resp.model_id+'>'+resp.model_name+'</option>'); 
				$('#model_id').select2("val",resp.model_id);
				$("#model_id").trigger('change'); 
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			$('#model_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditModel").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditModel").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		product_id: $("#edit_product_id").val(), 
		model_name: $("#edit_model_name").val(), 
		model_desc: $("#edit_model_desc").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/model_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("MODEL UPDATED SUCCESSFULLY", "SUCCESS");
				load_model_datatable();
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
			$("#ModalEditModel").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_model(model_id) 
{
	var r= confirm(" Are you want to delete ?"); 
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/model_mst/',
			data: { mode : "delete", model_id : model_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("MODEL DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_model_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function edit_model(model_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/model_mst/',
		data: { mode : "preedit", model_id : model_id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response); 
			$("#ModalEditModel").modal("show");
			$("#edit_id").val(model_id);
			$("#edit_product_id").select2("val",obj.product_id);
			$("#edit_model_name").val(obj.model_name);
			$("#edit_model_desc").val(obj.model_desc);
			Unloading();
		}
	});	
}
function load_model_datatable(){
	datatable = $("#model-table").dataTable({
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
		"sAjaxSource": root_domain+'app/model_mst/',
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
function alloc_req_pro(model_id){ 
	if(model_id) { 
		$("#allocate_req_product").modal("show");
		$("#model_id").val(model_id);		 
		show_req_pro(); 
	}	
} 
function add_req_field() {
	var model_id=$("#model_id").val();
	if($("#req_product_id").val()==""){
		toastr.warning("Select Product Name", "ERROR");
		return false;
	}
	if($("#req_product_qty").val()==""){
		toastr.warning("Enter Qty", "ERROR");
		return false;
	}
	Loading();	
	$.ajax({
		type: "POST",
		url: root_domain+'app/model_mst/',
		data: { mode : "add_req_field",edit_id:$("#edit_id1").val(),req_product_id:$("#req_product_id").val(),req_product_qty:$("#req_product_qty").val(),model_id:$("#model_id").val() },
		//contentType: false,
		//  processData:false,
		success: function(response)
		{
			console.log(response);
			$("#req_product_id").select2("val","");
			$("#req_product_qty").val(""); 
			$("#edit_id1").val('');
			$("#addrow").val("Add");
			Unloading(); 
			show_req_pro(); 
		}
	});
}
function show_req_pro(){
	var model_id=$("#model_id").val(); 
	$("#req-pro-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bDestroy": true,
		"bProcessing": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100,"All"]],
		"iDisplayLength": 20,
		"sAjaxSource": root_domain+'app/model_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "show_req_pro" },{ "name": "model_id", "value": model_id } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function edit_reqpro(model_pro_alloc_mst_id) {
	//alert(id);
	Loading(true);
	 $.ajax({
		type: "POST",
		url: root_domain+'app/model_mst/',
		data: { mode : "preedit_reqpro", model_pro_alloc_mst_id : model_pro_alloc_mst_id },
		//contentType: false,
		//processData:false,
		success: function(resnse)
		{
			//console.log(resnse);
			var data = jQuery.parseJSON(resnse); 
			$("#req_product_id").select2("val",data.req_product_id);
			$("#req_product_qty").val(data.req_product_qty);
			$("#edit_id1").val(model_pro_alloc_mst_id);
			$("#addrow").val("Update"); 
			Unloading();
		}
	});	 
}
function delete_req_product(model_pro_alloc_mst_id)  {
	var r= confirm(" Are you want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/model_mst/',
			data: { mode : "delete_req_product",  model_pro_alloc_mst_id : model_pro_alloc_mst_id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("PRODUCT ALLOCATION DELETED SUCCESSFULLY", "SUCCESS");
					show_req_pro();
					Unloading();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}