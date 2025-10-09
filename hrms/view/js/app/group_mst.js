var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/groupmst/',
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
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#group_add").validate({
	rules: {
		g_name: {
			required: true
		},
		g_parent: {
			required: true
		},
		
	},
	messages: {
		g_name: {
			required: "Enter Sub Category Name"			
		},
		g_parent: {
			required: "Please Select Category "
		},
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditunit").validate({
	rules: {
	edit_unit_name: {
			required: true
		}
	},
	messages: {
		edit_unit_name: {
			required: "Enter Unit Name"			
		}
	}
});		

});
$("#group_add").on('submit',function(e) {
	//alert('hiii');
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#group_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var g_name=$("#g_name").val();
	var g_parent=$("#g_parent").val();
	var g_opening=$("#g_opening").val();
	var g_status=$("#g_status").val();
	var g_form=$("#g_form").val();
	
	//alert($("#mode").val());
	var form_data = {
		g_name: g_name,
		g_parent: g_parent,
		g_opening: g_opening,
		g_status: g_status,
		g_form: g_form,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/groupmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			//alert(resp);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("EMPLOYEE GROUP ADDED SUCCESSFULLY", "SUCCESS");
				get_group_dropdown('g_parent');
				Unloading();
				datatable.fnReloadAjax();
			}
			if(msg.trim() == '2') {				
				toastr.success("EMPLOYEE GROUP ADDED SUCCESSFULLY", "SUCCESS");
				//get_group_dropdown('g_parent');
				$("#add_expense_head_modal").modal("hide");
				$('#expense_head_id').append('<option value='+resp.g_id+'>'+resp.g_name+'</option>'); 
				$('#expense_head_id').select2("val",resp.g_id);
				$("#expense_head_id").trigger('change'); 
				Unloading();
				datatable.fnReloadAjax();
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
			$('#group_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
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
		e_g_name: $("#e_g_name").val(),
		e_g_parent: $("#e_g_parent").val(),
		e_g_opening: $("#e_g_opening").val(),
		e_g_status: $("#e_g_status").val(),
		e_g_form: $("#e_g_form").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/groupmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("EMPLOYEE GROUP UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
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
	datatable.fnReloadAjax();
}
function delete_category(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/groupmst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("EMPLOYEE GROUP DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_group(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/groupmst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(obj.g_id);
			$("#edit_pid").val(obj.g_pid);
			get_group_dropdown('e_g_parent');
			$("#e_g_name").val(obj.g_name);
			$("#e_g_opening").val(obj.g_open_balance);
			$("#e_g_status").select2('val',obj.g_status);
			$("#e_g_form").val(obj.form_id);
			Unloading();
		}
	});	
}

function get_group_dropdown(sel_id)
{
	//alert(sel_id);
	var id = $('#edit_pid').val();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/groupmst/',
		data: { mode : "get_group_dropdown_data",id:id },
		success: function(response)
		{
			$('#'+sel_id).html(response);
			Unloading();
		}
	});	
}

function get_form_type(gid,text_id)
{
	//alert(gid);
	
	Loading(true);
	
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/groupmst/',
		data: { mode : "get_form_type",gid:gid },
		success: function(response)
		{
			$('#'+text_id).val(response);
			Unloading();
		}
	});	
	
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/groupmst/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("EMPLOYEE GROUP CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}