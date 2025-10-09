var datatable;
$(document).ready(function() {
load_datatable();
		
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#todo_add").validate({
	rules: {
		date: {
			required: true
		},
		task_detail: {
			required: true
		}	
	},
	messages: {
		date: {
			required: "Enter Date"
		},
		task_detail: {
			required: "Enter task"			
		}
	}
}); 

// validate vendor edit form on keyup and submit
$("#FormEdittodotask").validate({
	rules: {
		edit_date: {
			required: true
		},
		edit_task_detail: {
			required: true			
		}
		

	},
	messages: {
		edit_date: {
			required: "Enter Date"
		},		
		edit_task_detail: {
			required: "Select Task"
		}
	}
});		

});
function reload_data()
{
	//datatable.fnReloadAjax();
	load_datatable();
}
function load_datatable()
{
	var date=$('#rep_date').val();
	var status_id=$('#status_id').val();
	datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bDestroy": true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO TASK ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 100,
			"sAjaxSource": root_domain+'app/todomst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "status_id", "value": status_id },{ "name": "date", "value": date } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
$("#todo_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#todo_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var date= $("#date").val();	
	var form_data = {
		date: date,
		task_detail: $("#task_detail").val(),		
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/todomst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);			
			if(response.trim() == '1') {
				toastr.success("TASK ADDED SUCCESSFULLY", "SUCCESS")
				$("#add_todotask").modal("hide");
				datatable.fnReloadAjax();
				Unloading();
				show_todolist();
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
			$('#todo_add').trigger('reset');	
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEdittodotask").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEdittodotask").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		date: $("#edit_date").val(),
		task_detail: $("#edit_task_detail").val(),		
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/todomst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			
			if(response.trim() == '1') {
				toastr.success("TASK UPDATED SUCCESSFULLY", "SUCCESS");
				$("#show_todotask").modal("show");
				datatable.fnReloadAjax();
				show_todolist();
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
			$("#ModalEdittodolist").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_catalog(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/todomst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
						show_todolist();
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
			url: root_domain+'app/todomst/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#show_todotask").modal("hide");
				$("#ModalEdittodolist").modal("show");
				$("#edit_id").val(id);								
				$("#edit_date").val(obj.date);
				$("#edit_task_detail").val(obj.task_detail);				
				Unloading();
			}
		});	
}
function change_status(id,todo_status) 
{
	var r= confirm(" Are you want to Change Task Status ?");

		if(r) {
			Loading();
			$.ajax({
				type: "POST",
				url: root_domain+'app/todomst/',
				data: { mode : "change_status", eid : id, todo_status:todo_status },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("TASK SUCCESSFULLY COMPLETED", "SUCCESS");
						Unloading();
						show_todolist();
						datatable.fnReloadAjax();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function show_todolist()
{
 
  $.ajax({
	type: "POST",
	url: root_domain+'app/todomst/',
	data: { mode : "gettodolist"},
	success: function(response){
				//console.log(response);
				$('#todolist').html(response);
				 $('.tooltips').tooltip();

	}
	});
}  