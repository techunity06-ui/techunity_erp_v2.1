var datatable;
$(document).ready(function() {
		// validate vendor add form on keyup and submit
		$("#menu_master_access_add").validate({
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
					"sEmptyTable": "NO MENU ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/menu_master_access/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch"},{ "name": "parent_id", "value": $('#parent_id').val()}  );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted        
});
$("#menu_master_access_add").on('submit',function(e) {
	var slug_name = [];
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#menu_master_access_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var parent_id= $("#parent_id").val();
	
	var str = $("#menu_master_access_add").serializeArray();
	$.ajax({
		cache:false,
		url: root_domain+'app/menu_master_access/',
		type: "POST",
		data: str,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {

				toastr.success("MENU MASTER ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + 'menu_master_access_list/'+parent_id;

			}
			else if(arr.msg == '2') {
				toastr.success("MENU MASTER UPDATED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + 'menu_master_access_list/'+parent_id;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.warning("CURRENT SLUG NAME ALREADY EXISTS", "ERROR")
				Unloading();				
			}
			$('#menu_master_access_add').trigger('reset');	
			$('#parent_id').val(parent_id);
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
	var r= confirm("Are you sure want to delete?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/menu_master_access/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("MENU MASTER DELETE SUCCESSFULLY", "SUCCESS");
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
function pid_test(pid,pname)
{
	var ppid=$('#parent_id').val()
	var per_name = $('#pname').html()
	$('#parent_id').val(pid);
	$('#return').val(ppid);
	$('#pname').html(pname);
	$('#ppname').val(per_name);
	$("a.update_link").attr("href", root_domain+"menu_master_access_add/"+pid);
	
	datatable.fnReloadAjax();
}

function pid_home(pid)
{
	
	//alert("hr");
	$('#parent_id').val(pid);
	$('#return').val(0);
	$('#pname').html('');
	datatable.fnReloadAjax();
}

function pid_return(pid)
{
	var ppname=$('#ppname').val();
	
	$('#parent_id').val(pid);
	$('#pname').html('');
	$('#pname').html(ppname);
	datatable.fnReloadAjax();
}

function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/menu_master_access/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("MENU MASTER STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}

function open_cron_modal(id,parent_id){
	$("#menu_cron_modal").modal('show');
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + 'app/menu_master_access/',
		data: { mode : "view_crone", eid : id , parent_id:parent_id },
		success: function(response)
		{
			$("#source_code").html(response);
		}
	});
	Unloading();
}