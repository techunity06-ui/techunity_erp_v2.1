//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#payroll_emp_benefit_claim_add").validate({
	rules: {
		employee_id: {
			required: true			
		},
		claim_date: {
			required: true			
		},
		claim_benefit_for: {
			required: true			
		},
		claim_amount: {
			required: true			
		},
	},
	messages: {
		employee_id: {
			required: "Select Employee"
		},
		claim_date: {
			required: "Select Claim Date"		
		},
		claim_benefit_for: {
			required: "Select Claim Benefit For"		
		},
		claim_amount: {
			required: "Enter Claim Amount"		
		},
	}
}); 
});
$("#payroll_emp_benefit_claim_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_emp_benefit_claim_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);
	form_data.append('file', $('#claim_attachment').prop('files')[0]);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_claim/',
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
				toastr.success("PAYROLL EMPLOYEE BENEFIT CLAIM ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_emp_benefit_claim_list';
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
				toastr.success("PAYROLL EMPLOYEE BENEFIT CLAIM UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'payroll_emp_benefit_claim_list';
			}
			$('#payroll_emp_benefit_claim_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_payroll_emp_benefit_claim(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/payroll_emp_benefit_claim/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PAYROLL EMPLOYEE BENEFIT CLAIM DELETE SUCCESSFULLY", "SUCCESS");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_emp_benefit_claim/',
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

function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_claim/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL EMPLOYEE BENEFIT CLAIM STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable(); 
		}
	});
	Unloading();
}

function getEarningComponentData(){
	var component_id = $("#claim_benefit_for").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_emp_benefit_claim/',
		data: { mode : "get_earning_component_data",component_id:component_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			if(data.is_fexible_benefit_flag == 'Yes'){
				$("#maximum_display_amount_eligible").val(data.max_benefit_amount_yearly);
			}else{
				$("#maximum_display_amount_eligible").val('0.00');
			}				
			Unloading();
		}		
	});
}