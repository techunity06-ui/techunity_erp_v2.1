//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_daily_work_summary_group_add").validate({
	rules: {
		daily_work_summary_group_name: {
			required: true			
		},
		send_email_timing: {
			required: true			
		},
		holiday_list_id: {
			required: true			
		},
		reminder_subject: {
			required: true			
		},
		reminder_message: {
			required: true			
		},
		status: {
			required: true			
		}
	},
	messages: {
		daily_work_summary_group_name: {
			required: "Enter Daily Work Summary Group Name"
		},
		send_email_timing: {
			required: "Select Send Email Timining"
		},
		holiday_list_id: {
			required: "Select Holiday List"		
		},
		reminder_subject: {
			required: "Enter Reminder Subject"	
		},
		reminder_message: {
			required: "Enter Reminder Message"			
		},
		status: {
			required: "Select Status"			
		}
	}
}); 
});
$("#hrms_daily_work_summary_group_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_daily_work_summary_group_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("HRMS DAILY WORK SUMMARY GROUP ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_daily_work_summary_group_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("HRMS DAILY WORK SUMMARY GROUP UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_daily_work_summary_group_list';
			}
			$('#hrms_daily_work_summary_group_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_hrms_daily_work_summary_group(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS DAILY WORK SUMMARY GROUP DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
}
function add_hrms_daily_work_summary_group_field()
{
	if($("#employee_user_id").val()==="")
	{		
		toastr.warning("Select Employee Name", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
			data: { 
				mode : "fielddailyworksummaryadd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				employee_user_id:$("#employee_user_id").val()
			},
			success: function(response)
			{
				$("#employee_user_id").select2("val","");
				$('#adddailyworksummaryrow').val('Add');
				Unloading();
				show_daily_work_summary_group_data();
			}
		});
}
function reload_data()
{
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	var type=$('#type_id').val();
	datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
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
			"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },{ "name": "date", "value": date } );
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}

function show_daily_work_summary_group_data()
{
	var est_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
		data: { mode : "load_tempoutward",est_id:est_id},
		success: function(data){
			$('#hrms_hrms_daily_work_summary_group_data').html(data);				
			Unloading();
		}		
	});
}
function edit_daily_work_summary_group_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#employee_user_id").select2("val",data.employee_user_id);
				$("#edit_id").val(id);
				$('#adddailyworksummaryrow').val('Update');
				Unloading();
			}
		});
}
function delete_daily_work_summary_group_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
			data: { 
				mode : "delete_data",
				eid : id
			},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_daily_work_summary_group_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_daily_work_summary_group/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS DAILY WORK SUMMARY GROUP CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}