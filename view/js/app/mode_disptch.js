var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO Mode Of Dispatch ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/mode_disptch/',
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
$("#dispatch").validate({
	rules: {
		mode_dispatch: {
			required: true
		}
	},
	messages: {
		mode_dispatch: {
			required: "Enter Mode Of dispatch"
			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditdispatch").validate({
	rules: {
		edit_mode_dispatch: {
			required: true
		}		

	},
	messages: {
		edit_mode_dispatch: {
			required: "Enter Mode Of dispatch"
		}
	}
});		

});
$("#dispatch").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#dispatch").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();		
	var form_data = {
		mode_dispatch: $("#mode_dispatch").val(),
		token:token,
		mode:$("#mode").val(),
		model:$("#model").val(),
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/mode_disptch/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("MODE OF DISPATCH ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
				
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("MODE OF DISPATCH ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-dispatch-modal").modal("hide");
				$('#dispatch_doc_no').append('<option value='+data.mode_dis_id+'>'+data.mode_dispatch+'</option>');
				$('#dispatch_doc_no').val(data.mode_dis_id);
				$("#dispatch_doc_no").trigger('change')
				$('#dispatch').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#model_addbank").modal("hide");
				$('#bank_add').trigger('reset');
				Unloading();				
			}
			$('#dispatch').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditdispatch").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditdispatch").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		mode_dispatch: $("#edit_mode_dispatch").val(),
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/mode_disptch/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			
			if(response.trim() == '1') {
				toastr.success("MODE OF DISPATCH UPDATED SUCCESSFULLY", "SUCCESS");
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
function delete_bank(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/mode_disptch/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("MODE OF DISPATCH DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_test(id)
{
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/mode_disptch/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_mode_dispatch").val(obj.mode_dispatch);
				Unloading();
			}
		});	
	}