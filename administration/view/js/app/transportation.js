$(document).ready(function() {
	load_transportation_datatable();
	// validate vendor add form on keyup and submit
	$("#transportation_add").validate({
		rules: {
			transportation_name: {
				required: true,
				
			},
			
		},
		messages: {
			transportation_name: {
				required: "Enter Transportation Name"
				 },	
           
		}
	}); 
	$("#trans_add_f").validate({
		rules: {
			
			transportation_address: { // Add validation for the textarea field
					required: true,
			},
		},
		messages: {
			
            transportation_address: {
                required: "Enter Transportation Address"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEdittransportation").validate({
		rules: {
			edit_transportation_name: {
				required: true,
			}
		},
		messages: {
			edit_transportation_name: {
				required: "Enter Transportation Name"			
			}
		}
	});		
	
});
$("#transportation_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#transportation_add").valid()) {
		return false;
	}	
		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		transportation_name: $("#transportation_name").val(),
		transportation_gst_number: $("#transportation_gst_number").val(),
		transportation_email_id: $("#transportation_email_id").val(),
		transportation_branch: $("#transportation_branch").val(),
		transportation_phone_num: $("#transportation_phone_num").val(),
		transportation_model: $("#transportation_model").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/transportation/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("TRANSPORTATION ADDED SUCCESSFULLY", "SUCCESS")
				$("#abranch_id").select2("val",1000);
				//$("#abranch_id").val("10000");
				Unloading();
				load_transportation_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("TRANSPORTATION ADDED SUCCESSFULLY", "SUCCESS");
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
			$('#transportation_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEdittransportation").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEdittransportation").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		transportation_name: $("#edit_transportation_name").val(),
		transportation_gst_number: $("#edit_transportation_gst_number").val(), 
		transportation_email_id: $("#edit_transportation_email_id").val(), 
		transportation_branch: $("#edit_transportation_branch").val(), 
		transportation_phone_num: $("#edit_transportation_phone_num").val(), 
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/transportation/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("TRANSPORTATION UPDATED SUCCESSFULLY", "SUCCESS");
				load_transportation_datatable();
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
			$("#ModalEdittransportation").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_transportation(transportation_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/transportation/',
			data: { mode : "delete", transportation_id : transportation_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("TRANSPORTATION DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_transportation_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_transportation(transportation_id)
{
	$("#FormEdittransportation").valid();		
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/transportation/',
		data: { mode : "preedit", transportation_id : transportation_id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEdittransportation").modal("show");
			$("#edit_id").val(transportation_id);				
			$("#edit_transportation_name").val(obj.transportation_name);
			$("#edit_transportation_gst_number").val(obj.transportation_gst_number);
			$("#edit_transportation_email_id").val(obj.transportation_email_id);
			$("#edit_transportation_branch").val(obj.transportation_branch);
			$("#edit_transportation_phone_num").val(obj.transportation_phone_num);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_transportation_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#transportation-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bprocess_typeing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sprocess_typeing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/transportation/',
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
function add_address(transportation_id,address_id)
{
	$("#model_add_transport_address").modal("show");
	$("#trans_id").val(transportation_id);
	$("#trans_add_id").val(address_id);
	load_transportation_add_datatable();
	// Loading(true);
	// editReq = $.ajax({
	// 	type: "POST",
	// 	url: root_domain+administration_domain+'app/transportation/',
	// 	data: { mode : "preedit", transportation_id : transportation_id },
	// 	success: function(response)
	// 	{
	// 		console.log(response);
	// 		var obj = jQuery.parseJSON(response);
			
							
	// 		Unloading();
	// 	}
	// });	
}
// function add_address_db()
// {
// 	// alert("hr");
// 	//$("#model_add_transport_address").modal("show");
// 	//$("#tranc_id").val(transportation_id);
// 	var address_id=$("#trans_add_id").val();
// 	var address=$("#transportation_address").val();
// 	var transport_id=$("#trans_id").val();
// 	 Loading(true);
// 	 editReq = $.ajax({
// 	 	type: "POST",
// 	 	url: root_domain+administration_domain+'app/transportation/',
// 	 	data: { mode : "add_address_db", address_id : address_id,address:address,transport_id:transport_id },
// 	 	data: { mode : "add_address_db", address_id : address_id,address:address,transport_id:transport_id },
// 	 	success: function(response)
// 	 	{
// 			if(response.trim() == "1") {
// 				toastr.success("SUCCESSFULLY", "SUCCESS"); 	
// 				load_transportation_add_datatable();
// 				Unloading();
// 			}
// 			else if(response.trim() == "0") { 
// 				toastr.warning("SOMETHING WRONG", "WARNING");
// 			}						
			
// 			$("#trans_add_id").val("");		
// 			$("#transportation_address").val("");		
// 	 		Unloading();
// 	 	}
// 	 });	
// }
function add_address_db() {
    var address = $("#transportation_address").val().trim(); // Get the value of transportation_address and 
	if (!$("#trans_add_f").valid()) {
		return false;
	}
  
    var address_id = $("#trans_add_id").val();
    var transport_id = $("#trans_id").val();

    Loading(true);
    editReq = $.ajax({
        type: "POST",
        url: root_domain + administration_domain + 'app/transportation/',
        data: {
            mode: "add_address_db",
            address_id: address_id,
            address: address,
            transport_id: transport_id
        },
        success: function(response) {
            if (response.trim() == "1") {
                toastr.success("Successfully added transportation address", "SUCCESS");
                load_transportation_add_datatable();
                $("#trans_add_id").val("");
                $("#transportation_address").val("");
            } else {
                toastr.warning("Something went wrong", "WARNING");
            }
            Unloading();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
            toastr.error("Error occurred during AJAX request", "ERROR");
            Unloading();
        }
    });
}

function load_transportation_add_datatable(){
	var trans_id = $('#trans_id').val();

	datatable = $("#transportation_add-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bprocess_typeing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sprocess_typeing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/transportation/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch_add" },
				{"name": "trans_id", "value": trans_id }
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
function delete_transportation_add(transportation_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/transportation/',
			data: { mode : "delete_add", transportation_id : transportation_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_transportation_add_datatable()
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_transportation_add(transportation_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/transportation/',
		data: { mode : "preedit_add", transportation_id : transportation_id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#trans_id").val(transportation_id);				
			$("#transportation_address").val(obj.transportation_address);
			Unloading();
		}
	});	
}