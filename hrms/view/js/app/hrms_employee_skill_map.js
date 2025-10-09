//var datatable;
$(document).ready(function() {
load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_employee_skill_map_add").validate({
	rules: {
		employee_id: {
			required: true			
		},
	},
	messages: {
		employee_id: {
			required: "Select Employee"
		},
	}
}); 
});
$("#hrms_employee_skill_map_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_employee_skill_map_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
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
				toastr.success("HRMS EMPLOYEE SKILL MAP ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_employee_skill_map_list';
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
				toastr.success("HRMS EMPLOYEE SKILL MAP UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee_skill_map_list';
			}
			$('#hrms_employee_skill_map_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_hrms_employee_skill_map(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS EMPLOYEE SKILL MAP DELETE SUCCESSFULLY", "SUCCESS");
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
function add_skill_field()
{
	if($("#skill_id").val()==="")
	{		
		toastr.warning("Select Skill", "ERROR")
		return false;
	}
	if($("#proficiency").val()==="")
	{		
		toastr.warning("Enter Proficiency", "ERROR")
		return false;
	}
	if($("#evaluation_date").val()==="")
	{		
		toastr.warning("Enter Evaluation Date", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
			data: {
				mode : "fieldskilladd",
				eid : $("#eid").val(),
				edit_id:$("#edit_id").val(),
				skill_id:$("#skill_id").val(),
				proficiency:$("#proficiency").val(),
				evaluation_date:$("#evaluation_date").val()
			},
			success: function(response)
			{
				$("#skill_id").val("");
				$("#proficiency").val("");
				$("#evaluation_date").val("");
				$('#addskillrow').val('Add');
				Unloading();
				show_employee_skill_data();
			}
		});
}

function add_training_field(){
	if($("#training_id").val() === null)
	{		
		toastr.warning("Select Training Name", "ERROR")
		return false;
	}
	if($("#training_date").val() === null)
	{		
		toastr.warning("Select Training Date", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
			data: { 
				mode : "fieldtrainingadd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(), 
				training_id:$("#training_id").val(),
				training_date:$("#training_date").val()
			},
			success: function(response)
			{
				$("#training_id").select2('val','');
				$("#training_date").val('');
				$('#addtrainingrow').val('Add');
				Unloading();
				show_employee_training_data();
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
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

function show_employee_skill_data()
{
	var ekd_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
		data: { mode : "load_skillstempoutward",ekd_id:ekd_id},
		success: function(data){
			$('#hrms_employee_skills_data').html(data);				
			Unloading();
		}		
	});
}
function show_employee_training_data()
{
	var ekd_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
		data: { mode : "load_trainingtempoutward",ekd_id:ekd_id},
		success: function(data){
			$('#hrms_employee_training_data').html(data);				
			Unloading();
		}		
	});
}
function edit_skill_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
			data: { mode : "preskilledit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#skill_id").select2('val',data.skill_id);
				$("#proficiency").val(data.proficiency);
				$("#evaluation_date").val(data.evaluation_date);
				$("#edit_id").val(id);
				$('#addskillrow').val('Update');
				Unloading();
			}
		});
}
function edit_training_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
			data: { mode : "pretrainingedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#training_id").select2("val",data.training_id);
				$("#training_date").val(data.training_date);
				$("#edit_id").val(id);
				$('#addtrainingrow').val('Update');
				Unloading();
			}
		});
}
function delete_skill_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
			data: { mode : "delete_skill_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_employee_skill_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function delete_training_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
			data: { mode : "delete_training_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_employee_training_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function getEmployeeDesignation(){
	var emp_skill_id = $("#employee_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
		data: { mode : "get_employee_skill_map",emp_skill_id:emp_skill_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			$("#designation_id").select2("val",data.designation_id);
			$("#designation_hidden_id").val(data.designation_id);				
			Unloading();
		}		
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_skill_map/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS EMPLOYEE SKILL MAP STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}