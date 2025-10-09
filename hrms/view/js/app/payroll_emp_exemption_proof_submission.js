//var datatable;
$(document).ready(function() {
	load_datatable(); 
// validate vendor add form on keyup and submit
$("#payroll_emp_exemption_proof_submission_add").validate({
	rules: {
		employee_id: {
			required: true			
		},
		payroll_period_id: {
			required: true			
		},
	},
	messages: {
		employee_id: {
			required: "Select Employee"
		},
		payroll_period_id: {
			required: "Select Payroll Period"		
		},
	}
}); 
});
$("#payroll_emp_exemption_proof_submission_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_emp_exemption_proof_submission_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);
	form_data.append('file', $('#attachments').prop('files')[0]);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
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
				toastr.success("PAYROLL EMPLOYEE EXEMPTION PROOF SUBMISSION ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_emp_exemption_proof_submission_list';
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
				toastr.success("PAYROLL EMPLOYEE EXEMPTION PROOF SUBMISSION UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'payroll_emp_exemption_proof_submission_list';
			}
			$('#payroll_emp_exemption_proof_submission_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_payroll_emp_exemption_proof_submission(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PAYROLL EMPLOYEE EXEMPTION PROOF SUBMISSION DELETE SUCCESSFULLY", "SUCCESS");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
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

function add_field()
{
	if($("#exemption_subcategory").val()==="")
	{		
		toastr.warning("Select Exemption Sub Category", "ERROR")
		return false;
	}
	if($("#type_of_proof").val()==="")
	{		
		toastr.warning("Enter Type Of Proof", "ERROR")
		return false;
	}
	if($("#actual_amount").val()==="")
	{		
		toastr.warning("Enter Actual Amount", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
			data: { 
				mode : "fieldadd",
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				exemption_subcategory:$("#exemption_subcategory").val(),
				exemption_category:$("#exemption_category").val(),
				maximum_exemption_amount:$("#maximum_exemption_amount").val(),
				type_of_proof:$("#type_of_proof").val(),
				actual_amount:$("#actual_amount").val()
			},
			success: function(response)
			{
				$("#exemption_subcategory").select2("val","");
				$("#exemption_display_category").select2("val","");
				$("#exemption_category").val("");
				$("#maximum_display_exemption_amount").val("");
				$("#maximum_exemption_amount").val("");
				$("#type_of_proof").val("");
				$("#actual_amount").val("");
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
}

function show_data()
{
	var tax_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
		data: { mode : "load_tempoutward",tax_id:tax_id},
		success: function(data){
			$('#payroll_employee_proof_submission').html(data);				
			Unloading();
		}		
	});
}

function edit_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#exemption_subcategory").select2('val',data.exemption_subcategory);
				$("#exemption_display_category").select2('val',data.subcategory_id);
				$("#exemption_category").val(data.exemption_category);
				$("#maximum_display_exemption_amount").val(data.maximum_exemption_amount);
				$("#maximum_exemption_amount").val(data.maximum_exemption_amount);
				$("#type_of_proof").val(data.type_of_proof);
				$("#actual_amount").val(data.actual_amount);
				$("#edit_id").val(id);
				$('#addrow').val('Update');
				Unloading();
			}
		});
}

function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
			data: { mode : "delete_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
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
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL EMPLOYEE EXEMPTION PROOF SUBMISSION STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable(); 
		}
	});
	Unloading();
}

function getCategoryData(){
	var sub_cat_id = $("#exemption_subcategory").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_proof_submission/',
		data: { mode : "get_category_data",sub_cat_id:sub_cat_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			$("#exemption_display_category").select2("val",data.parent_data_id);
			$("#exemption_category").val(data.parent_category_name);
			$("#maximum_display_exemption_amount").val(data.max_exemption_amount);
			$("#maximum_exemption_amount").val(data.max_exemption_amount);
			$("#total_exemption_amount").val(data.max_exemption_amount);
			Unloading();
		}		
	});
}