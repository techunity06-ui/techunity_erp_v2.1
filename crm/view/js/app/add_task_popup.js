//var datatable;
$(document).ready(function() {
	// validate vendor add form on keyup and submit
	$("#task_add").validate({
		rules: {
			task_due_date: {
				required: true			
			},
                        task_type_id: {
				required: true			
			}
		},
		messages: {
			task_due_date: {
				required: "Choose Next Follow up Date"
			},
                        task_type_id: {
				required: "Choose Task Type"
			}
		}
	}); 
});

function show_close_reason(opp_id){
    if(opp_id === '13'){
            $(".lost_reasons").show();
            $('#task_add').validate({
                    rules: {
                            reasonid: {
                                    required: true			
                            },
                            lost_reason: {
                                    required: true
                            }
                    },
                    messages: {
                            reasonid: {
                                    required: "Choose Reason"
                            },
                            lost_reason: {
                                    required: "Write Reason to close."
                            }
                    }
            });
    } else {
        $(".lost_reasons").hide();
    }
}

function add_reason_div(){
        var counter = $("#counter").val();
        $.ajax({
                type: "POST",
                url: root_domain + crm_domain + 'app/task/',
                data: { mode : "add_lost_reason", counter: counter },
                success: function(response)
                {
                    var resp=JSON.parse(response);
                    //$(".add_remove_reason").removeClass('fa-plus');
                    //$(".add_remove_reason").addClass('fa-minus');
                    $(".lost_reasons:last").after(resp.html);
                    $("#reason_id"+counter).select2({width: '100%'});
                    counter++;
                    $("#counter").val(counter);
                }
        });	
}

function remove_reason_div(obj){
    $(obj).closest( ".lost_reasons" ).remove();
}

$("#task_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#task_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain+'app/add_task_popup/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("TASK ADDED SUCCESSFULLY", "SUCCESS");
                                $("#add_task_modal .close").click();
                                load_inquiry_datatable();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function validateDueDate(task_type, due_date){
    if(task_type === '16'){
        $("#task_due_date").val('');
        $("#task_add").validate({
		rules: {
			task_type_id: {
				required: true			
			}
		},
		messages: {
			task_type_id: {
				required: "Choose Task Type"
			}
		}
	});
    } else {
        $("#task_due_date").val(due_date);
    }
}

// Add Appointment
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

function load_opp_stage_prob(opp_id){
	if(opp_id){
                var task_type = $("#task_type_id").val();
                if(task_type === '16' && opp_id === '13'){
                    $("#task_due_date").val('');
                } 
                
            	$.ajax({
			type: "POST",
			url: root_domain  + crm_domain +  'app/opp_mst/',
			data: { mode:"load_opp_stage_prob", opp_id:opp_id },
			success: function(response)
			{
				//console.log(response);
				var resp=jQuery.parseJSON(response);
				$('#stage_prob').val(resp.opp_probability);
			}
		});                
	}
}

$("#appointment_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#appointment_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain+'app/add_task_popup/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("APPOINTMENT ADDED SUCCESSFULLY", "SUCCESS");
                                $("#add_task_modal .close").click();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

// function to change inquiry stage
function change_inquiry_stage(opp_id){
	if(opp_id){
            if(opp_id === '12'){
                    var inquiry_id = $("#eid").val();
                    var has_quot = has_quotation(inquiry_id);
                    if(has_quot === false){
                        if(confirm("Inquiry has no Quotation. Are you sure, you want to Won ?")){
                            load_opp_stage_prob(opp_id);
                        } else {
                            if(inquiry_id){ 
                                $.ajax({
                                    type: "POST",
                                    url: root_domain + crm_domain + 'app/inquiry/',
                                    data: { mode:"load_inquiry_data", inquiry_id : inquiry_id },
                                    success: function(response)
                                    {
                                            var resp=jQuery.parseJSON(response);
                                            $('#opp_id').select2("val",resp.opp_id);
                                            $('#stage_prob').val(resp.stage_prob);
                                            validate_close_reason(opp_id);
                                    }
                                });
                            } else {
                                $('#opp_id').val(5);
                                $('#stage_prob').val(10);
                            }
                        }
                    }
            }
            else {
                load_opp_stage_prob(opp_id);
            }
        }
        
}

// load stage probability
function load_opp_stage_prob(opp_id){
    $.ajax({
        type: "POST",
        url: root_domain + crm_domain + 'app/opp_mst/',
        data: { mode:"load_opp_stage_prob", opp_id:opp_id },
        success: function(response)
        {
                var resp=jQuery.parseJSON(response);
                $('#stage_prob').val(resp.opp_probability);
                validate_close_reason(opp_id);
        }
    });
}

// checks if inquiry has quotation or not.
function has_quotation(inquiry_id){
    var has_quot = false;
    if(inquiry_id){
        $.ajax({
                type: "POST",
                async: false,
                url: root_domain + crm_domain + 'app/inquiry/',
                data: { mode:"has_quotation", inquiry_id : inquiry_id },
                success: function(response)
                {
                        if(response > 0){
                            has_quot = true;
                        } else {
                            has_quot = false;
                        }
                }
        });
    } else {
        has_quot = false;
    }
    
    return has_quot;
}
function check_assign_user(task_type_id){
    if(task_type_id === '15' || task_type_id === '20'){
        $('#assign_user_ids').removeAttr('multiple');
        $('#assign_user_ids').select2({width: '100%'});
    } else {
        $('#assign_user_ids').attr('multiple','true');
        $('#assign_user_ids').select2({width: '100%'});
    }
}
