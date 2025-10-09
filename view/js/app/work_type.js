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
					"sEmptyTable": "NO BANK ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/work_type/',
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
$("#work").validate({
	rules: {
		work_type: {
			required: true
			
		}
	},
	messages: {
		work_type: {
			required: "Enter bank Name"
			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#Formwork").validate({
	rules: {
		edit_work_type: {
			required: true
			
		}		

	},
	messages: {
		edit_work_type: {
			required: "Enter bank Name"
		
		}
	}
});		

});
$("#work").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#work").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var token=  $("#token").val();		
	var form_data = {
		work_type: $("#work_type").val(),
		token:token,
		mode:$("#mode").val(),
		model:$("#model").val(),
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/work_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("WORK TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
				
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("WORK TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-work-modal").modal("hide");
				$('#description').append('<option value='+data.work_type_id+'>'+data.work_type+'</option>');
				$('#description').val(data.work_type_id);
				$("#description").trigger('change')
				$('#work').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$("#model_addwork").modal("hide");
				$('#work').trigger('reset');
				Unloading();				
			}
			$('#work').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditwork").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditwork").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		work_type: $("#edit_work_type").val(),
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/work_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			
			if(response.trim() == '1') {
				toastr.success("EORK TYPE UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditwork").modal("hide");					
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
				url: root_domain+'app/work_type/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("WORK TYPE DELETE SUCCESSFULLY", "SUCCESS");
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
			url: root_domain+'app/work_type/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditwork").modal("show");
				$("#edit_id").val(id);				
				$("#edit_work_type").val(obj.work_type);
				Unloading();
			}
		});	
	}