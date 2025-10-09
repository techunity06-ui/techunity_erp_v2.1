//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#payroll_income_tax_slab_add").validate({
	rules: {
		income_tax_slab_name: {
			required: true			
		},
	},
	messages: {
		income_tax_slab_name: {
			required: "Enter Income Tax Slab Name"
		},
	}
}); 
});
$("#payroll_income_tax_slab_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_income_tax_slab_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
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
				toastr.success("PAYROLL INCOME TAX SLAB ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_income_tax_slab_list';
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
				toastr.success("PAYROLL INCOME TAX SLAB UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'payroll_income_tax_slab_list';
			}
			$('#payroll_income_tax_slab_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_payroll_income_tax_slab(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("PAYROLL INCOME TAX SLAB DELETE SUCCESSFULLY", "SUCCESS");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
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
	if($("#taxable_from_amount").val()==="")
	{		
		toastr.warning("Enter Taxable From Amount", "ERROR")
		return false;
	}
	if($("#taxable_to_amount").val()==="")
	{		
		toastr.warning("Enter Taxable To Amount", "ERROR")
		return false;
	}
	if($("#taxable_percent_deduction").val()==="")
	{		
		toastr.warning("Enter Taxable Percent Deduction", "ERROR")
		return false;
	}
	if($("#taxable_condition").val()==="")
	{		
		toastr.warning("Enter Taxable Condition", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
			data: { 
				mode : "fieldadd",
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				taxable_from_amount:$("#taxable_from_amount").val(),
				taxable_to_amount:$("#taxable_to_amount").val(),
				taxable_percent_deduction:$("#taxable_percent_deduction").val(),
				taxable_condition:$("#taxable_condition").val()
			},
			success: function(response)
			{
				$("#taxable_from_amount").val("");
				$("#taxable_to_amount").val("");
				$("#taxable_percent_deduction").val("");
				$("#taxable_condition").val("");
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
		url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
		data: { mode : "load_tempoutward",tax_id:tax_id},
		success: function(data){
			$('#payroll_taxable_salary_slabs').html(data);				
			Unloading();
		}		
	});
}

function edit_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#taxable_from_amount").val(data.taxable_from_amount);
				$("#taxable_to_amount").val(data.taxable_to_amount);
				$("#taxable_percent_deduction").val(data.taxable_percent_deduction);
				$("#taxable_condition").val(data.taxable_condition);
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
			url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
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
		url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL INCOME TAX SLAB STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable(); 
		}
	});
	Unloading();
}

function add_other_field(){
	if($("#taxes_and_charges_description").val() === null)
	{		
		toastr.warning("Enter Taxes And Charges Description", "ERROR")
		return false;
	}
	if($("#taxes_and_charges_percent").val() === null)
	{		
		toastr.warning("Enter Taxes And Charges Percent", "ERROR")
		return false;
	}
	if($("#taxes_and_charges_min_taxable_income").val() === null)
	{		
		toastr.warning("Enter Taxes And Charges Min Taxable Income", "ERROR")
		return false;
	}
	if($("#taxes_and_charges_max_taxable_income").val() === null)
	{		
		toastr.warning("Enter Taxes And Charges Max Taxable Income", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
			data: { 
				mode : "fieldotheradd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(), 
				taxes_and_charges_description:$("#taxes_and_charges_description").val(),
				taxes_and_charges_percent:$("#taxes_and_charges_percent").val(),
				taxes_and_charges_min_taxable_income:$("#taxes_and_charges_min_taxable_income").val(),
				taxes_and_charges_max_taxable_income:$("#taxes_and_charges_max_taxable_income").val()
			},
			success: function(response)
			{
				$("#taxes_and_charges_description").val('');
				$("#taxes_and_charges_percent").val('');
				$("#taxes_and_charges_min_taxable_income").val('');
				$("#taxes_and_charges_max_taxable_income").val('');
				$('#addotherrow').val('Add');
				Unloading();
				show_other_data();
			}
	});
}

function show_other_data()
{
	var oth_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
		data: { mode : "load_othertempoutward",oth_id:oth_id},
		success: function(data){
			$('#payroll_other_taxes_and_charges').html(data);				
			Unloading();
		}		
	});
}

function edit_other_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
			data: { mode : "preotheredit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#taxes_and_charges_description").val(data.taxes_and_charges_description);
				$("#taxes_and_charges_percent").val(data.taxes_and_charges_percent);
				$("#taxes_and_charges_min_taxable_income").val(data.taxes_and_charges_min_taxable_income);
				$("#taxes_and_charges_max_taxable_income").val(data.taxes_and_charges_max_taxable_income);
				$("#edit_id").val(id)
				$('#addotherrow').val('Update');
				Unloading();
			}
		});
}

function delete_other_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_income_tax_slab/',
			data: { mode : "delete_other_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_block_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

