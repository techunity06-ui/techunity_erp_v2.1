//var datatable;
$(document).ready(function() {
	load_datatable();
	show_data();	
	// validate vendor add form on keyup and submit
	$("#hrms_attendance_add").validate({
		rules: {
			employee_id: {
				required: true			
			},
			attendance_date: {
				required: true			
			},
			attendance_status: {
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
			attendance_date: {
				required: "Select Attendance Date"
			},
			attendance_status: {
				required: "Select Attendance Date"
			},
			status: {
				required: "Select Status"
			}
		}
	});

	$("#upload_attendance").validate({
		rules: {
			excel_file: {
				required: true			
			}
		},
		messages: {
			excel_file: {
				required: "Select CSV File"			
			}
		}
	});

	$("#download_attendance").validate({
		rules: {
			from_date: {
				required: true			
			},
			to_date: {
				required: true			
			}
		}
	});
});
$("#hrms_attendance_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_attendance_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_attendance/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("HRMS ATTENDANCE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_attendance_list';
			}else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(arr.msg == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#save').prop("disabled",false);
			$('#hrms_attendance_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});		
});

$("#upload_attendance").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#upload_attendance").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var token	=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_attendance/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resp)
		{	
			console.log('resp',resp);
			var data = JSON.parse(resp);
			var response=data.res;
			Unloading();
			if(response == '1') {
				$('#msg').html('<span style="color:green">Data Checked Successfully, Submit Again to Upload.</span>');
				$('#mode').val('import_data');
			}
			else if(response == '-1')
			{
				toastr.info("SELECT WRONG FILE", "INFO")
				$('#upload_attendance').trigger('reset');
				Unloading();				
			}
			else if(response == '0')
			{
				$('#msg').html('<span style="color:red"> Columns Does Not Match, Please Check With demo File</span>');
				$('#upload_attendance').trigger('reset');
				Unloading();				
			}
			else if(response == '3')
			{
				$('#msg').html('<span style="color:red"> Column Name Does Not Match, Please Check With demo File</span>');
				$('#upload_attendance').trigger('reset');
				Unloading();				
			}
			else if(response == '4')
			{
				toastr.success("DATA IMPORTED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_upload_attendance_list';		
				$('#upload_attendance').trigger('reset');
				Unloading();				
			}
			else if(response == '5')
			{
				$('#msg').html('<span style="color:red"> '+data.msg+'</span>');
				$('#upload_attendance').trigger('reset');
				$('#mode').val('check_data');
				// show_importedcust_data();
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	Unloading();
});

$("#download_attendance").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#download_attendance").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var token = $("#token").val();
	var from_date = $('#from_date').val();
	var to_date = $('#to_date').val();
	window.location=root_domain + hrms_domain + 'export/attendance?from_date='+from_date+'&to_date='+to_date;
	Unloading();
});

function delete_attendance(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_attendance/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == "1") {
					toastr.success("HRMS ATTENDANCE DELETE SUCCESSFULLY", "SUCCESS");
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
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_attendance/',
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
		url: root_domain + hrms_domain + 'app/hrms_attendance/',
		data: { mode : "load_tempoutward", ha_id:eid },
		success: function(resp){
			$('#sale_attendancedata').html(resp);				
			Unloading();
		}	
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_attendance/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS ATTENDANCE CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}