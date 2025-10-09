//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_appraisal_template_add").validate({
	rules: {
		appraisal_template_title: {
			required: true			
		},
		status: {
			required: true			
		}
	},
	messages: {
		appraisal_template_title: {
			required: "Enter Appraisal Template"
		},
		status: {
			required: "Select Status"			
		}
	}
}); 
});
$("#hrms_appraisal_template_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_appraisal_template_add").valid()) {
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
		url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
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
				toastr.success("HRMS APPRAISAL TEMPLATE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_appraisal_template_list';
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
			else if(arr.msg == '3')
			{
				toastr.warning("Sum of points for all goals should be 100. It is "+arr.count, "ERROR")
				Unloading();				
			}
			else if(arr.msg == 'update')
			{	
				toastr.success("HRMS APPRAISAL TEMPLATE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_appraisal_template_list';
			}
			$('#hrms_appraisal_template_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_hrms_appraisal_template(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS APPRAISAL TEMPLATE DELETE SUCCESSFULLY", "SUCCESS");
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
function add_appraisal_goals_field()
{
	if($("#key_resource_planning_name").val()==="")
	{		
		toastr.warning("Enter Key Resource Planning Name", "ERROR")
		return false;
	}
	if($("#key_resource_planning_weightage").val()==="")
	{		
		toastr.warning("Enter Key Resource Planning Weightage", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
			data: { 
				mode : "fieldappraisaltemplateadd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				key_resource_planning_name:$("#key_resource_planning_name").val(),
				key_resource_planning_weightage:$("#key_resource_planning_weightage").val()
			},
			success: function(response)
			{
				$("#key_resource_planning_name").val("");
				$("#key_resource_planning_weightage").val("");
				$('#addappraisalgoalsrow').val('Add');
				Unloading();
				show_appraisal_goals_data();
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_appraisal_template/',
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

function show_appraisal_goals_data()
{
	var est_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
		data: { mode : "load_tempoutward",est_id:est_id},
		success: function(data){
			$('#hrms_appraisal_goals_data').html(data);				
			Unloading();
		}		
	});
}
function edit_appraisal_goals_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#key_resource_planning_name").val(data.key_resource_planning_name);
				$("#key_resource_planning_weightage").val(data.key_resource_planning_weightage);
				$("#edit_id").val(id);
				$('#addappraisalgoalsrow').val('Update');
				Unloading();
			}
		});
}
function delete_appraisal_goals_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
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
					show_employee_onboarding_data();
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
		url: root_domain + hrms_domain + 'app/hrms_appraisal_template/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS APPRAISAL TEMPLATE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}