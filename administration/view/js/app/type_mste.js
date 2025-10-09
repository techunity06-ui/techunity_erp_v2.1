$(document).ready(function() {
	load_impregnation_msteter_datatable();	
	// validate vendor add form on keyup and submit
	$("#type_add").validate({
		rules: {
			branch_id: {
				required: true
			},
			type_desc: {
				required: true
			},
			code: {
				required: true
			}
		},
		messages: {
			branch_id: {
				required: "Enter Branch Name"			
			},
			type_desc: {
				required: "Please Enter Type Description"
			},
			code: {
				required: "Please Enter Type Code"
			},
		}
	}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
	e_branch_id: {
			required: true
		},
	edit_type_desc: {
			required: true
		},
	edit_code: {
			required: true
		},
	},
	messages: {
		e_branch_id: {
			required: "Enter Branch Name"			
		},
		edit_type_desc: {
			required: "Please Enter Type Description"	
		},
		edit_code: {
			required: "Please Enter Type Code"
		},
	}
});		

});
$("#type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var type_desc=$("#type_desc").val();
	var branch_id=$("#abranch_id").val();
	var code=$("#code").val();
	var form_data = {
		type_desc: type_desc,
		code: code,
		branch_id: branch_id,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/type_mste/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				$('#p_product').select2("val","");
				Unloading();
				load_impregnation_msteter_datatable();
			}
			if(msg.trim() == '2') {				
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_expense_head_modal").modal("hide");
				$('#expense_head_id').append('<option value='+resp.g_id+'>'+resp.g_name+'</option>'); 
				$('#expense_head_id').select2("val",resp.g_id);
				$("#expense_head_id").trigger('change'); 
				Unloading();
				load_impregnation_msteter_datatable();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#type_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
$("#FormEditunit").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditunit").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		impregnation_name: $("#edit_type_desc").val(),
		code: $("#edit_code").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/type_mste/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				load_impregnation_msteter_datatable();
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

function delete_reload()
{
	load_impregnation_msteter_datatable();
}

function delete_parameter(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/type_mste/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_parameter(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/type_mste/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.type_id);
			$("#edit_type_desc").val(obj.type_name);
			$("#edit_code").val(obj.code);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}

function load_impregnation_msteter_datatable(){
	var branch_id = $('#branch_id').val();
	
	datatable = $("#dynamic-table").dataTable({
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
			"sAjaxSource": root_domain+administration_domain+'app/type_mste/',
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
	// validate the comment form when it is submitted        
}