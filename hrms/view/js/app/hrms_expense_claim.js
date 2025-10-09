//var datatable;
$(document).ready(function() {
		load_datatable(); 
// validate vendor add form on keyup and submit
$("#hrms_expense_claim_add").validate({
	rules: {
		employee_id: {
			required: true			
		},
		expense_approver_id: {
			required: true			
		},
		posting_date: {
			required: true			
		},
		payable_account_id: {
			required: true			
		},
	},
	messages: {
		employee_id: {
			required: "Select Employee Name"
		},
		expense_approver_id: {
			required: "Select Employee Approver"
		},
		posting_date: {
			required: "Select Posting Date"		
		},
		payable_account_id: {
			required: "Select Payable Account"		
		},
	}
}); 
});
$("#hrms_expense_claim_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_expense_claim_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
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
				toastr.success("HRMS EXPENSE CLAIM ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_expense_claim_list';
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
				toastr.success("HRMS EXPENSE CLAIM UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_expense_claim_list';
			}
			$('#hrms_expense_claim_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_expense_claim/',
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
function delete_hrms_expense_claim(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					console.log(response)
					if(response.trim() == "1") {
						toastr.success("HRMS EXPENSE CLAIM DELETE SUCCESSFULLY", "SUCCESS");
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
function load_leaveblockdetail(val) {
	if(val!=0)
	{
		$('#addproduct').hide();
	}
	else
	{
		$('#addproduct').show();
	}
	var cust_id = $('#cust_id').val();
	if(cust_id==''){
		toastr.warning("Please Select Customer First","ERROR");
		$('#cust_id').select2('focus');
		return false;
	}
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_block/',
			data: { mode : "load_productdata",eid :val, cust_id:cust_id },
			success: function(response)
			{
				console.log(response);
				var obj =jQuery.parseJSON(response)
				$('#product_hsn_code').val(obj.product_hsn);
				$('#formulaid').val(obj.fom_id);
				$('#product_rate').val(obj.product_sale_rate);
				$('#unit_id').select2("val",obj.product_base_unit);
			}
		});
}
function show_expense_data()
{
	var ex_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
		data: { mode : "load_expense",ex_id:ex_id},
		success: function(data){
			$('#hrms_expensedata').html(data);				
			Unloading();
		}		
	});
}
function show_expense_taxes_charges_data()
{
	var ex_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
		data: { mode : "load_expense_taxes_charges",ex_id:ex_id},
		success: function(data){
			$('#hrms_expensetaxandchargesdata').html(data);				
			Unloading();
		}		
	});
}
function show_expense_advance_payment_data()
{
	var ex_id=$("#eid").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
		data: { mode : "load_expense_advance_payment",ex_id:ex_id},
		success: function(data){
			$('#hrms_advancepaymentdata').html(data);				
			Unloading();
		}		
	});
}
function add_expense_field()
{
	if($("#expense_date").val()==="")
	{		
		toastr.warning("Select Expense Date", "ERROR")
		return false;
	}
	if($("#expense_claim_type_id").val()==="")
	{		
		toastr.warning("Enter Expense Claim Type", "ERROR")
		return false;
	}
	if($("#expense_description").val()==="")
	{		
		toastr.warning("Enter Expense Description", "ERROR")
		return false;
	}
	if($("#expense_amount").val()==="")
	{		
		toastr.warning("Enter Expense Amount", "ERROR")
		return false;
	}
	if($("#expense_sanctioned_amount").val()==="")
	{		
		toastr.warning("Enter Expense Sanctioned Amount", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { 
				mode : "fieldexpenseadd",
				eid : $("#eid").val(),
				edit_id:$("#edit_id").val(),
				expense_date:$("#expense_date").val(),
				expense_claim_type_id:$("#expense_claim_type_id").val(),
				expense_description:$("#expense_description").val(),
				expense_amount:$("#expense_amount").val(),
				expense_sanctioned_amount:$("#expense_sanctioned_amount").val()
			},
			success: function(response)
			{
				$("#expense_date").val("");
				$("#expense_claim_type_id").select2("val","");
				$("#expense_description").val("");
				$("#expense_amount").val("");
				$("#expense_sanctioned_amount").val("");
				$('#addexpenserow').val('Add');
				Unloading();
				show_expense_data();
			}
		});
}
function edit_expense_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { mode : "pre_expense_edit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#expense_date").val(data.expense_date);
				$("#expense_claim_type_id").select2("val",data.expense_claim_type_id);
				$("#expense_description").val(data.expense_description);
				$("#expense_amount").val(data.expense_amount);
				$("#expense_sanctioned_amount").val(data.expense_sanctioned_amount);
				$("#edit_id").val(id);
				$('#addexpenserow').val('Update');
				Unloading();
			}
		});
}
function delete_expense_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { mode : "delete_expense_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_expense_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function add_expense_tax_charge_field(){
	if($("#account_head_id").val() === null)
	{		
		toastr.warning("Select Account Head", "ERROR")
		return false;
	}
	if($("#exp_tax_rate").val() === null)
	{		
		toastr.warning("Enter Employee Tax Rate", "ERROR")
		return false;
	}
	if($("#exp_tax_amount").val() === null)
	{		
		toastr.warning("Enter Employee Tax Amount", "ERROR")
		return false;
	}
	if($("#exp_tax_total").val() === null)
	{		
		toastr.warning("Enter Employee Tax Total", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { 
				mode : "fieldexpensetaxchargeadd",
				eid : $("#eid").val(),
				edit_id:$("#edit_id").val(),
				account_head_id:$("#account_head_id").val(),
				exp_tax_rate:$("#exp_tax_rate").val(),
				exp_tax_amount:$("#exp_tax_amount").val(),
				exp_tax_total:$("#exp_tax_total").val()
			},
			success: function(response)
			{
				$("#account_head_id").select2('val','');
				$('#exp_tax_rate').val('');
				$('#exp_tax_amount').val('');
				$('#exp_tax_total').val('');
				$('#total_tax_charges_amount').val('');
				$('#expense_grand_total').val('');
				$("#expensetaxandcharge").css("display","none");
				$('#addexpensetaxchargerow').val('Add');
				Unloading();
				show_expense_taxes_charges_data();
			}
	});
}
function edit_expense_tax_charge_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { mode : "pre_expense_tax_charge_edit", id:id },
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#account_head_id").select2("val",data.account_head_id);
				$("#exp_tax_rate").val(data.exp_tax_rate);
				$("#exp_tax_amount").val(data.exp_tax_amount);
				$("#exp_tax_total").val(data.exp_tax_total);
				$("#edit_id").val(id);
				$('#addexpensetaxchargerow').val('Update');
				Unloading();
			}
		});
}
function delete_expense_tax_charge_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { mode : "delete_expense_tax_charge_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_expense_taxes_charges_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function empAdvanceEmployee(){
	var empAdv = $("#emp_advance_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
		data: { mode : "load_employee_advance", id:empAdv },
		success: function(response)
		{
			var data = jQuery.parseJSON(response);
			$("#advance_posting_date").val(data.posting_date);
			$("#advance_paid_amount").val(data.advance_amount);
			$("#unclaim_amount").val(data.pending_amount);
			$("#allocated_amount").val(data.claimed_amount);
			Unloading();
		}
	});
}
function add_advance_payment_field(){
	if($("#emp_advance_id").val() === null)
	{		
		toastr.warning("Select Employee Advance", "ERROR")
		return false;
	}
	if($("#advance_posting_date").val() === null)
	{		
		toastr.warning("Enter Advance Posting Date", "ERROR")
		return false;
	}
	if($("#advance_paid_amount").val() === null)
	{		
		toastr.warning("Enter Advance Paid Amount", "ERROR")
		return false;
	}
	if($("#unclaim_amount").val() === null)
	{		
		toastr.warning("Enter Unclaim Amount", "ERROR")
		return false;
	}
	if($("#allocated_amount").val() === null)
	{		
		toastr.warning("Enter Allocated Amount", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { 
				mode : "fieldadvancepaymentadd",
				eid : $("#eid").val(),
				edit_id:$("#edit_id").val(),
				emp_advance_id:$("#emp_advance_id").val(),
				advance_posting_date:$("#advance_posting_date").val(),
				advance_paid_amount:$("#advance_paid_amount").val(),
				unclaim_amount:$("#unclaim_amount").val(),
				allocated_amount:$("#allocated_amount").val()
			},
			success: function(response)
			{
				$("#emp_advance_id").select2('val','');
				$('#advance_posting_date').val('');
				$('#advance_paid_amount').val('');
				$('#unclaim_amount').val('');
				$('#allocated_amount').val('');
				Unloading();
				show_expense_advance_payment_data();
			}
	});
}
function edit_expense_advance_payment_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { mode : "pre_advance_payment_edit", id:id },
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#emp_advance_id").select2("val",data.emp_advance_id);
				$("#advance_posting_date").val(data.advance_posting_date);
				$("#advance_paid_amount").val(data.advance_paid_amount);
				$("#unclaim_amount").val(data.unclaim_amount);
				$("#allocated_amount").val(data.allocated_amount);
				$("#edit_id").val(id);
				$('#addadvancepaymentrow').val('Update');
				Unloading();
			}
		});
}
function delete_expense_advance_payment_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
			data: { mode : "delete_advance_payment_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_expense_advance_payment_data();
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
		url: root_domain + hrms_domain + 'app/hrms_expense_claim/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS EXPENSE CLAIM STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}