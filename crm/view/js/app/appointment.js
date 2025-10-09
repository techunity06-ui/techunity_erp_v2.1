//var datatable;
$(document).ready(function() {
	load_appointment_datatable();
        var task_status = $('#task_status').val();
        validate_close_remark(task_status);
	// validate vendor add form on keyup and submit
	$("#appointment_add").validate({
		rules: {
			task_location: {
				required: true			
			},
			appointment_start_time: {
				required: true			
			},
			appointment_end_time: {
				required: true			
			},
			appointment_subject: {
				required: true			
			}
		},
		messages: {
			task_location: {
				required: "Enter Task Location"
			},
			appointment_start_time: {
				required: "Choose Start Time"
			},
			appointment_end_time: {
				required: "Choose End Time"
			},
			appointment_subject: {
				required: "Enter Subject"
			}
		}
	}); 
}); 

$("#appointment_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#appointment_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/appointment/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("APPOINTMENT ADDED SUCCESSFULLY", "SUCCESS");
				if($('#back_link').val()){
					window.location=($('#back_link').val());
				}
				else{
					window.location=root_domain + crm_domain + 'appointment_list';
				}
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("APPOINTMENT UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location=root_domain + crm_domain +'appointment_list';	
			}
			Unloading();
			$('#appointment_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function load_appointment_datatable(){
	var task_status=$('input[name=task_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	
	datatable = $("#appointment-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain +'app/appointment/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch"},
			  {"name": "date", "value": date},
			  {"name": "task_status", "value": task_status },
			  {"name": "branch_id", "value": branch_id}
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

function delete_appointment(task_id) 
{
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain + 'app/appointment/',
			data: { mode : "delete",  task_id : task_id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("APPOINTMENT DELETE SUCCESSFULLY", "SUCCESS");
					load_appointment_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	} 
}

function get_rel_task_divs(task_rel_id){
	if(task_rel_id=='3'){//Person
		$('#person_rel_div').show();
		$('#gen_rel_div').hide();
		$('#company_rel_div').hide();
		$('#inq_rel_div').hide();
	}
	else if(task_rel_id=='4'){//Company
		$('#company_rel_div').show();
		$('#person_rel_div').hide();
		$('#gen_rel_div').hide();
		$('#inq_rel_div').hide();
	}
	else if(task_rel_id=='5'){//Inquiry
		$('#inq_rel_div').show();
		$('#company_rel_div').hide();
		$('#person_rel_div').hide();
		$('#gen_rel_div').hide();
	}
	else{
		$('#gen_rel_div').show();
		$('#person_rel_div').hide();
		$('#company_rel_div').hide();
		$('#inq_rel_div').hide();
	}
}
function preview_rel_types() 
{
	var task_rel_id=$('#task_rel_id').val();
	var c_con_id=$('#c_con_id').val();
	var cust_id=$('#cust_id').val();
	var inquiry_id=$('#inquiry_id').val();
	if(task_rel_id){
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/task/',
			data: { mode : "preview_rel_types", task_rel_id:task_rel_id, c_con_id:c_con_id, cust_id:cust_id, inquiry_id:inquiry_id },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#preview_rel_details_modal').modal('show');
				$('#preview_rel_details_div').html(resp.html_resp);
				Unloading();
			}
		});
	}
}

function load_pend_appointment(){
	var fil_due_date = $('#from_date').val();
	var log_user_id=$('#log_user_id').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+ crm_domain + 'app/appointment/',
		data: { mode : "load_pend_appointment", log_user_id:log_user_id, fil_due_date:fil_due_date },
		success: function(resnse)
		{
			//console.log(resnse);
			var resp = JSON.parse(resnse);
			$('#pend_apt_tbl').html(resp.resp_html);
			$('.ttip, [data-toggle="tooltip"]').tooltip();
			Unloading();					
		}
	});
}
function validate_close_remark(task_status){
    if(task_status === '1'){
        $('#closed_remark_div').show();
        $('#appointment_add').validate({
                rules: {
                        closed_reason: {
                                required: true			
                        }
                },
                messages: {
                        closed_reason: {
                                required: "Write Closing Remark."
                        }
                }
        });
    } else {
        $('#closed_remark_div').hide();
    }
} 