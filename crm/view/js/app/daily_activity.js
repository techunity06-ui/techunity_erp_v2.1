$(document).ready(function() {      
	load_daily_activity_datatable();

	// validate vendor add form on keyup and submit
	$("#daily_activity_add").validate({
		rules: {
			branch_id: {
				required: true
			},
			daily_update_date: {
				required: true
			},
			description: {
				required: true
			}
		},
		messages: {
			branch_id: {
				required: "Please Select Branch"			
			},
			daily_update_date: {
				required: "Enter Daily Activity Date"	
			},
			description: {
				required: "Enter Daily Activity Description"	
			}
		}
	}); 
		
});
$("#daily_activity_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#daily_activity_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	var token	=  $("#token").val();	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/daily_activity/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(responsevalue)
		{
			if(responsevalue.trim() == '1') {
				
				toastr.success("DAILY ACTIVITY LOG ADDED SUCCESSFULLY", "SUCCESS")
				$('#daily_activity_add').trigger('reset');			
				Unloading();
				window.location=root_domain + crm_domain +'daily_activity_list';
			}
			if(responsevalue.trim() == 'update') {
				
				toastr.success("DAILY ACTIVITY LOG UPDATED SUCCESSFULLY", "SUCCESS")
				Unloading();
				window.location=root_domain + crm_domain +'daily_activity_list';
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("something wrong", "ERROR")
				$('#daily_activity_add').trigger('reset');	
				Unloading();
			}
			else if(responsevalue.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#daily_activity_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == '3') {
				toastr.success("DAILY ACTIVITY LOG ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
			}
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditDailyActivity").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditDailyActivity").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		e_branch_id: $("#e_branch_id").val(),
		e_daily_activity_date: $("#e_daily_activity_date").val(),
		e_description: $("#e_description").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/daily_activity/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("DAILY ACTIVITY LOG UPDATED SUCCESSFULLY", "SUCCESS");
				load_terms_mst_datatable();
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
			$("#ModalEditDailyActivity").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_reload()
{
	load_daily_activity_datatable();
}
function delete_data(id) 
{
	var r= confirm(" Are you sure want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain +'app/daily_activity/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("DAILY ACTIVITY LOG DELETED SUCCESSFULLY", "SUCCESS");
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
function edit_data(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/daily_activity/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditDailyActivity").modal("show");
			$("#edit_id").val(obj.id);
			$("#e_branch_id").val(obj.branch_id);
			$("#e_daily_activity_date").val(obj.daily_activity_date);
			$("#e_description").val(obj.description);
			Unloading();
		}
	});	
}

function load_daily_activity_datatable(){
		var branch_id = $("#branch_id").val();
		var user_id = $("#user_id").val();
		$("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + crm_domain +'app/daily_activity/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },
					{ "name": "branch_id", "value": branch_id },
					{ "name": "user_id", "value": user_id });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
