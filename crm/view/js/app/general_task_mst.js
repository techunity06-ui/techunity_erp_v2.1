$(document).ready(function() {
	load_general_task_mst_datatable();       

// validate vendor add form on keyup and submit
$("#general_task_mst_add").validate({
	rules: {
		general_task_name: {
			required: true
		},
	},
	messages: {
		general_task_name: {
			required: "Enter General Task Name"			
		},
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditSourceMst").validate({
	rules: {
	edit_general_task_name: {
			required: true
		}
	},
	messages: {
		edit_general_task_name: {
			required: "Enter General Task Name"			
		}
	}
});		

});
$("#general_task_mst_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#general_task_mst_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		general_task_name: $("#general_task_name").val(),
		mode:"Add",
		general_task_mst_model:$("#general_task_mst_model").val(),
		branch_id: $("#abranch_id").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/general_task_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp=JSON.parse(response);
			var response = resp.resp;
			if(response.trim() == '1') {				
				toastr.success("GENERAL TASK LIST ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_general_task_mst_datatable();
			}
			else if(response.trim() == '2') {
				toastr.success("GENERAL TASK LIST ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-product-group-modal").modal("hide");
				$('#product_group').append('<option value='+resp.rb_id+'>'+resp.rb_name+'</option>');	
				$('#product_group').select2("val",resp.rb_id);
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
			$('#general_task_mst_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditSourceMst").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditSourceMst").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_rb_name: $("#e_rb_name").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/general_task_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			
			if(response.trim() == '1') {
				toastr.success("GENERAL TASK LIST UPDATED SUCCESSFULLY", "SUCCESS");
				load_general_task_mst_datatable();
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
			$("#ModalEditSourceMst").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_general_task_mst(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/general_task_mst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("GENERAL TASK LIST DELETE SUCCESSFULLY", "SUCCESS");
						load_general_task_mst_datatable();
					}
					else if(response.trim() == "-1") {
						toastr.error("USED GENERAL TASK LIST CAN'T BE DELETED !!!", "WARNING"); 
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}	
					Unloading();						
				}
			});	
		}
	
}
function edit_general_task_mst(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/general_task_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditSourceMst").modal("show");
			$("#edit_id").val(obj.gt_id);
			$("#e_rb_name").val(obj.general_task_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_general_task_mst_datatable(){
	var branch_id = $('#branch_id').val();

	$("#source-mst-datatable").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/general_task_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
						 { "name": "branch_id", "value": branch_id } 
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