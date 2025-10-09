$(document).ready(function() {
	load_qc_parameter_datatable();	
	// validate vendor add form on keyup and submit
	$("#parameter_add").validate({
		rules: {
			p_product: {
				required: true
			},
			p_name: {
				required: true
			},
			p_value: {
				required: true
			}
		},
		messages: {
			p_product: {
				required: "Enter Product Name"			
			},
			p_name: {
				required: "Please Enter Parameter Name "
			},
			p_value: {
				required: "Please Enter Parameter Value "
			}
		}
	}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
	e_p_name: {
			required: true
		}
	},
	messages: {
		e_p_name: {
			required: "Please Enter Parameter Name"			
		}
	}
});		

});
$("#parameter_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#parameter_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var p_product=$("#p_product").val();
	var p_name=$("#p_name").val();
	var p_value=$("#p_value").val();
	var branch_id=$("#abranch_id").val();
	var form_data = {
		p_product: p_product,
		p_name: p_name,
		p_value: p_value,
		branch_id: branch_id,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/qc_param/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("QC PARAMETER ADDED SUCCESSFULLY", "SUCCESS");
				$('#p_product').select2("val","");
				Unloading();
				load_qc_parameter_datatable();
			}
			if(msg.trim() == '2') {				
				toastr.success("QC PARAMETER ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_expense_head_modal").modal("hide");
				$('#expense_head_id').append('<option value='+resp.g_id+'>'+resp.g_name+'</option>'); 
				$('#expense_head_id').select2("val",resp.g_id);
				$("#expense_head_id").trigger('change'); 
				Unloading();
				load_qc_parameter_datatable();
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
			$('#parameter_add').trigger('reset');
			$('#abranch_id').select2('val', '1000');
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
		e_p_product: $("#e_p_product").val(),
		e_p_name: $("#e_p_name").val(),
		e_p_value: $("#e_p_value").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/qc_param/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("QC PARAMETER UPDATED SUCCESSFULLY", "SUCCESS");
				load_qc_parameter_datatable();
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
	load_qc_parameter_datatable();
}

function delete_parameter(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/qc_param/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					var resp = JSON.parse(response);
					if(resp.msg == "-1") {
						swal("CURRENT RECORD ALREADY USED BELOW MODULES", ""+resp.table+"", "warning");
	         		    load_qc_parameter_datatable();
						Unloading();
					}else if(resp.msg == "1") {
						toastr.success("QC PARAMETER DELETE SUCCESSFULLY", "SUCCESS");
						delete_reload();
						Unloading();
					}else if(resp.msg == "0") { 
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
		url: root_domain+administration_domain+'app/qc_param/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");

			$("#edit_id").val(obj.p_id);
			get_product_dropdown(obj.p_product);
			$("#e_p_name").val(obj.p_name);
			$("#e_p_value").val(obj.p_value);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditunit").valid()
			Unloading();
		}
	});	
}

function get_product_dropdown(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/qc_param/',
		data: { mode : "get_all_product",id:id },
		success: function(response)
		{
			$('#e_p_product').html(response);
			$('#e_p_product').select2("val",id);
			Unloading();
		}
	});	
}

function load_qc_parameter_datatable(){
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
			"sAjaxSource": root_domain+administration_domain+'app/qc_param/',
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