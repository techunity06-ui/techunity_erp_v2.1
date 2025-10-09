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
					"sEmptyTable": "NO MENU ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/menumst/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch"},{ "name": "pid", "value": $('#pid').val()}  );
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
$("#menu_add").validate({
	rules: {
		menu_name: {
			required: true,
			minlength: 3
		}
	},
	messages: {
		menu_name: {
			required: "Enter Menu Name",
			minlength: "Your Menu Name must consist of at least 3 characters"
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditMenu").validate({
	rules: {
		menu_name: {
			required: true,
			minlength: 3
		}		

	},
	messages: {
		menu_name: {
			required: "Enter Menu Name",
			minlength: "Your Menu Name must consist of at least 3 characters"
		}
	}
});		

});
$("#menu_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#menu_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var pid= $("#pid").val();
	var form_data = {
		menu_name: $("#menu_name").val(),
		order: $("#order").val(),
		page_name: $("#page_name").val(),
		fa_icon: $("#fa_icon").val(),
		incl_per: $("input[name='incl_per']:checked").val(),
		report_status: $("input[name='report_status']:checked").val(),
		pid:pid,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	$.ajax({
		cache:false,
		url: root_domain+'app/menumst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			response=response.trim();
			if(response == '1') {
				toastr.success("Menu ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
				$('#pid').val(pid);
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#menu_add').trigger('reset');	
			$('#pid').val(pid);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditMenu").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditMenu").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		menu_name: $("#edit_menu_name").val(),
		order: $("#edit_order").val(),
		page_name: $("#edit_page_name").val(),
		fa_icon: $("#edit_fa_icon").val(),
		incl_per: $("input[name='edit_incl_per']:checked").val(),
		report_status: $("input[name='edit_report_status']:checked").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/menumst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			response=response.trim();
			if(response == '1') {
				toastr.success("Menu UPDATED SUCCESSFULLY", "SUCCESS");
				
				datatable.fnReloadAjax();
				Unloading();	
				
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response == '-1')
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
function delete_menu(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/menumst/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("Menu DELETE SUCCESSFULLY", "SUCCESS");
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
			url: root_domain+'app/menumst/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_menu_name").val(obj.menu_name);
				$("#edit_order").val(obj.menuorder);
				$("#edit_page_name").val(obj.page_name);
				$("#edit_fa_icon").val(obj.fa_icon);
				if(obj.incl_per=='1'){
					$('#edit_incl_per').prop('checked',true);
				}
				else{
					$('#edit_incl_per').prop('checked',false);
				}
				if(obj.report_status=='1'){
					$('#edit_report_status').prop('checked',true);
				}
				else{
					$('#edit_report_status').prop('checked',false);
				}
				Unloading();
			}
		});	
}

function pid_test(pid,pname)
{
	var ppid=$('#pid').val()
	var per_name = $('#pname').html()
	$('#pid').val(pid);
	$('#return').val(ppid);
	$('#pname').html(pname);
	$('#ppname').val(per_name);
	
	datatable.fnReloadAjax();
}

function pid_home(pid)
{
	
	//alert("hr");
	$('#pid').val(pid);
	$('#return').val(0);
	$('#pname').html('');
	datatable.fnReloadAjax();
}

function pid_return(pid)
{
	var ppname=$('#ppname').val();
	
	$('#pid').val(pid);
	$('#pname').html('');
	$('#pname').html(ppname);
	datatable.fnReloadAjax();
}