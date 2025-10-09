//var datatable;
$(document).ready(function() {
	load_datatable();
	show_data();	
	// validate vendor add form on keyup and submit
	$("#hrms_comp_leave_request_add").validate({
		rules: {
			employee_id: {
				required: true			
			},
			work_from_date: {
				required: true			
			},
			work_end_date: {
				required: true			
			},
			leave_request_reason: {
				required: true			
			},
			status:{
				required : true	
			}
		},
		messages: {
			employee_id: {
				required: "Select Employee"
			},
			work_from_date: {
				required: "Select Work From Date"			
			},
			work_end_date: {
				required: "Select Work End Date"			
			},
			leave_request_reason: {
				required: "Enter Leave Request Reason"			
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
});
$("#hrms_comp_leave_request_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_comp_leave_request_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_comp_leave_request/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("HRMS COMPENSATORY LEAVE REQUEST ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_comp_leave_request_list';
			}else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(arr.msg == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#save').prop("disabled",false);
			$('#hrms_comp_leave_request_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});		
});

function delete_hrms_comp_leave_request(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_comp_leave_request/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == "1") {
					toastr.success("HRMS COMPENSATORY LEAVE REQUEST DELETE SUCCESSFULLY", "SUCCESS");
					load_datatable();
					Unloading();
				}else if(arr.msg == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function reload_data()
{
	load_datatable();
}	
function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	
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
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_comp_leave_request/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function show_data()
{
	var eid = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_comp_leave_request/',
		data: { mode : "load_tempoutward", po_id:eid },
		success: function(resp){
			//console.log(resp);
			$('#sale_productdata').html(resp);				
			Unloading();
		}	
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_comp_leave_request/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS COMPENSATORY LEAVE REQUEST CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}