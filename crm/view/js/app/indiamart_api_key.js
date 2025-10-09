$(document).ready(function() {
	load_cust_ind_datatable();
	// validate the comment form when it is submitted        

	// validate vendor add form on keyup and submit
$("#cust_ind_mst_add").validate({
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
$("#FormEditcust_ind").validate({
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
$("#cust_ind_mst_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#cust_ind_mst_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var mobile_no=$("#mobile_no").val();
	var api_key=$("#api_key").val();
	var source_id=$("#source_id").val();
	
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
		url: root_domain + crm_domain + 'app/indiamart_api_key/',
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
				load_cust_ind_datatable();
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
			$('#cust_ind_mst_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditcust_ind").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditcust_ind").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		mobile_no: $("#edit_mobile_no").val(),
		api_key: $("#edit_api_key").val(),
		source_id: $("#edit_source_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/indiamart_api_key/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_cust_ind_datatable();
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

function delete_cust_ind(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/indiamart_api_key/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
						load_cust_ind_datatable();
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
function edit_cust_ind(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/indiamart_api_key/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.i_id);
			$("#edit_mobile_no").val(obj.mobile_no);
			$("#edit_api_key").val(obj.api_key);
			$('#edit_source_id').select2("val",obj.source_id);
			Unloading();
		}
	});	
}
function load_cust_ind_datatable(){
	$("#cust-ind-datatable").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/indiamart_api_key/',
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