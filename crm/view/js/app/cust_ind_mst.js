$(document).ready(function() {
	load_cust_ind_datatable();
	// validate the comment form when it is submitted        
// validate vendor add form on keyup and submit
$("#cust_ind_mst_add").validate({
	rules: {
		ci_name: {
			required: true
		},
	},
	messages: {
		ci_name: {
			required: "Enter Industry Name"			
		},
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditcust_ind").validate({
	rules: {
	edit_ci_name: {
			required: true
		}
	},
	messages: {
		edit_ci_name: {
			required: "Enter Industry Name"			
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
	
	var ci_name=$("#ci_name").val();
	var branch_id=$("#abranch_id").val();
	
	var form_data = {
		ci_name: ci_name,
		mode:"Add",
		cust_ind_model:$("#cust_ind_model").val(),
		branch_id: branch_id,
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/cust_ind_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp=JSON.parse(response);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("PARTY INDUSTRY ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_cust_ind_datatable();
			}
			else if(response.trim() == '2') {
				toastr.success("PARTY INDUSTRY ADDED SUCCESSFULLY", "SUCCESS");
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
		e_ci_name: $("#e_ci_name").val(),
		e_branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/cust_ind_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("PARTY INDUSTRY UPDATED SUCCESSFULLY", "SUCCESS");
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
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/cust_ind_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("PARTY INDUSTRY DELETED SUCCESSFULLY", "SUCCESS");
						load_cust_ind_datatable();
					}
					else if(response.trim() == "-1") {
						toastr.error("USED PARTY INDUSTRY CAN'T BE DELETED !!!", "WARNING"); 
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
		url: root_domain + crm_domain +'app/cust_ind_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.ci_id);
			$("#e_ci_name").val(obj.ci_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_cust_ind_datatable(){
	var branch_id = $('#branch_id').val();
	
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
		"sAjaxSource": root_domain + crm_domain +'app/cust_ind_mst/',
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