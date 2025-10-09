$(document).ready(function() {
	load_india_mart_api();
	// validate the comment form when it is submitted        

	// validate vendor add form on keyup and submit
$("#india_mart_add").validate({
	rules: {
		mobile_no: {
			required: true
		},
		api_key: {
			required: true
		},
		source_id: {
			required: true
		}
	},
	messages: {
		mobile_no: {
			required: "Enter Mobile No"			
		},
		api_key: {
			required: "Enter API Key"			
		},
		source_id: {
			required: "Enter Source Id"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#india_mart_edit").validate({
	rules: {
		edit_mobile_no: {
			required: true
		},
		edit_api_key: {
			required: true
		},
		edit_source_id: {
			required: true
		}
	},
	messages: {
		edit_mobile_no: {
			required: "Enter Mobile No"			
		},
		edit_api_key: {
			required: "Enter API Key"			
		},
		edit_source_id: {
			required: "Enter Source Id"			
		}
	}
});		

});
function add_india_mart() {
	
	var mobile_no=$("#mobile_no").val();
	var api_key=$("#api_key").val();
	var source_id=$("#source_indiamart").val();
	
	if(!mobile_no){		
		toastr.warning("Enter Mobile No", "ERROR");
		$("#mobile_no").focus();
		return false;
	}
	
	if(!api_key){		
		toastr.warning("Enter Api Key", "ERROR");
		$("#api_key").focus();
		return false;
	}
	
	if(!source_id){		
		toastr.warning("Choose Source", "ERROR");
		$("#source_indiamart").select2('focus');
		return false;
	}
	
	var form_data = {
		mobile_no: mobile_no,
		api_key: api_key,
		source_id: source_id,
		mode:"Add",
		//cust_ind_model:$("#cust_ind_model").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/indiamart_api_key/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp=JSON.parse(response);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("API Configuration SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_india_mart_api();
				$("#mobile_no").val("");
				$("#api_key").val("");
				$("#source_indiamart").select2("val","");
			}
			else if(response.trim() == '2') {
				toastr.success("INDUSTRY ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-group-modal").modal("hide");
				$('#product_group').append('<option value='+resp.ci_id+'>'+resp.ci_name+'</option>');	
				$('#product_group').select2("val",resp.ci_id);
				$("#product_group").trigger('change');
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
		},
	});
}
//var editReq = null;
function india_mart_update() {
			
	var form_data = {
		eid :$("#edit_indiamart").val(),
		mobile_no: $("#edit_mobile_no").val(),
		api_key: $("#edit_api_key").val(),
		source_id: $("#edit_source_indiamart").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/indiamart_api_key/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_india_mart_api();
				$("#edit_indiamart").val("");
				$("#edit_mobile_no").val("");
				$("#edit_api_key").val("");
				$("#edit_source_indiamart").select2("val","");
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
			$("#ModalEditIndiamart").modal("hide");					
		},
	});	
}

function delete_indiamart(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/indiamart_api_key/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
						load_india_mart_api();
					}
					else if(response.trim() == "-1") {
						toastr.error("USED INDUSTRY CAN'T BE DELETED !!!", "WARNING"); 
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();						
				}
			});	
		}
	
}
function edit_indiamart(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/indiamart_api_key/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditIndiamart").modal("show");
			$("#edit_indiamart").val(obj.i_id);
			$("#edit_mobile_no").val(obj.mobile_no);
			$("#edit_api_key").val(obj.api_key);
			$('#edit_source_indiamart').select2("val",obj.source_id);
			Unloading();
		}
	});	
}
function load_india_mart_api(){
	$("#india_mart_ta").dataTable({
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
		"sAjaxSource": root_domain+administration_domain+'app/indiamart_api_key/',
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