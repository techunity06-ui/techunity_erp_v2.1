$(document).ready(function() {
	load_income_datatable();
	// validate vendor add form on keyup and submit
	$("#income_add").validate({
		rules: {
			
			income_name: {
				required: true
			},
			income_head_id:{
				
				required:true
			}
		},
		messages: {
			
			income_name: {
				required: "Enter Income Name"			
			},
			income_head_id:{
				
				required:"Enter Group Name"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditExp").validate({
		rules: {
			edit_income_name: {
				required: true
			}
		},
		messages: {
			
			edit_income_name: {
				required: "Enter Income Name"			
			}
		}
	});		
	
});
$("#income_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#income_name").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		income_name: $("#income_name").val(),
		income_head_id: $("#income_head_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/income_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("INCOME ADDED SUCCESSFULLY", "SUCCESS");
				//load_income_datatable();
				
				$("#income_head_id").select2("val","");
				$("#income_name").val('');
				$("#inc_opn_balance").val('');
				
				$("#income_form").addClass("ledger_forms");
				
				$('#ledger_name').val('');
				$('#ledger_grp').select2('val','');
				
				Unloading();
			}
			else if(msg.trim() == '2') {
				toastr.success("INCOME ADDED SUCCESSFULLY", "SUCCESS");
				
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
			$("#income_head_id").select2("val","");
			$('#income_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditExp").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditExp").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		income_name: $("#edit_income_name").val(), 
		income_head_id: $("#edit_income_head_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/income_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("EXPENSE UPDATED SUCCESSFULLY", "SUCCESS");
				load_income_datatable();
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
			$("#ModalEditExp").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
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
	
	//alert($("#mode").val());
	var form_data = {
		g_name: g_name,
		g_parent: g_parent,
		g_opening: g_opening,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/groupmst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			//alert(resp);
			var msg= resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("Group ADDED SUCCESSFULLY", "SUCCESS");
				get_group_dropdown('g_parent');
				Unloading();
				datatable.fnReloadAjax();
			}
			if(msg.trim() == '2') {				
				toastr.success("Group ADDED SUCCESSFULLY", "SUCCESS");
				//get_group_dropdown('g_parent');
				$("#add_income_head_modal").modal("hide");
				$('#income_head_id').append('<option value='+resp.g_id+'>'+resp.g_name+'</option>'); 
				$('#income_head_id').select2("val",resp.g_id);
				$("#income_head_id").trigger('change'); 
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


function delete_expense(income_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/income_mst/',
			data: { mode : "delete", inc_id : income_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("INCOME DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_income_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_income(income_id)
{
	//alert(income_id);
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/income_mst/',
		data: { mode : "preedit", inc_id : income_id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditExp").modal("show");
			$("#edit_id").val(obj.inc_id);
			$("#edit_income_head_id").select2("val",obj.inc_group);
			$("#edit_income_name").val(obj.inc_name);
			Unloading();
		}
	});	
}
function load_income_datatable(){
	datatable = $("#income-table").dataTable({
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
		"sAjaxSource": root_domain+'app/income_mst/',
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