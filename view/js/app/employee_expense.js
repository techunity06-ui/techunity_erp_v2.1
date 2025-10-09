//var datatable;
$(document).ready(function() {
	load_expense_datatable();
	//$("#emp_id").select2({disabled:readonly});
	
	
$("#expense_change_status").validate({
	rules: {
		emp_status: {
			required: true			
		},
		
	},
	messages: {
		emp_status: {
			required: "Select Status"
		},
			
	}
}); 

$("#comp_id").keyup(function(){
		$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "search_complain_no", keyword : $(this).val() },
		beforeSend: function(){
			Loading();
		},
		success: function(data){
			Unloading();
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#comp_id").css("background","#FFF");
		}
		});
	});

});

function select_data_search(val) 
{
	//alert(val);
	$("#comp_id").val(val);
	$("#suggesstion-box").hide();
}

$(".btn_close").click(function() {
	$("label.error").hide();
});

function load_expense_datatable(){
	
	var emp_name=$('#emp_name').val();
	var emp_status=$('#emp_status').val();
	//alert(emp_status);
	datatable = $("#expense-table").dataTable({
		//Amish Soni 04-09-2020
		"bStateSave": true,
		
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 30, 50, 250], [10, 30, 50, 250]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/employee_expense/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } , {"name": "emp_name", "value": emp_name} , {"name": "emp_status", "value": emp_status}  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}   

function approveData(expense_id) 
{
	var r= confirm(" Are you want to Approve ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/employee_expense/',
			data: { mode : "approve", expense_id : expense_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("EXPENSE Appproved SUCCESSFULLY", "SUCCESS"); 	
					load_expense_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function DisApproveData(expense_id) 
{
	//alert(expense_id);
	var r= confirm(" Are you want to Dis Approve ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/employee_expense/',
			data: { mode : "dis_approve", expense_id : expense_id },
			success: function(response)
			{
				
				if(response.trim() == "1") {
					toastr.success("EXPENSE STATUS CHANGED SUCCESSFULLY", "SUCCESS"); 	
					load_expense_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}



function generate_report() 
{
	
	var date=$("#rep_date").val();
	var emp_id=$("#emp_id").val();
	
//	alert(date);
//	alert(emp_id);
	
	if(emp_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report", date : date,emp_id:emp_id},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	}
}


function generate_report_complain() 
{
	// alert('hello');
	var comp_id=$("#comp_id").val();
	
//	alert(date);
//	alert(emp_id);
	
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_complain",comp_id:comp_id},
		success: function(response)
		{
			// alert(response);
			// console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	
}


function generate_report_product_service() 
{
	
	var date=$("#rep_date").val();
	var cust_id=$("#cust_id").val();
	var product_id=$("#product_id").val();
	
	// alert(date);
	// alert(emp_id);
	
	if(cust_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_product_service",date:date,cust_id:cust_id,product_id:product_id},
		success: function(response)
		{
			// alert(response);
			// console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	}
}

function generate_report_emp_per() 
{
	
	var emp_id=$("#emp_id").val();
	
//	alert(date);
//	alert(emp_id);
	
	if(emp_id!="")
	{
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_emp_per",emp_id:emp_id},
		success: function(response)
		{
			// alert(response);
			// console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	}
}

function generate_report_frequent() 
{
	
	var date=$("#rep_date").val();
	
//	alert(date);
//	alert(emp_id);
	
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_frequent",date:date},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
	
}

function generate_report_comp_history()
{
	var date=$("#rep_date").val();
	
//	alert(date);
//	alert(emp_id);
	
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_comp_history",date:date},
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
										
		}
	});	
}

$("#expense_change_status").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#expense_change_status").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/employee_expense/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			//alert(arr.msg);
			if(arr.msg == '1') {
				
				toastr.success("STATUS CHANGED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'employee_expense';
				Unloading();
				//location.reload();
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
				toastr.success("EXPENSE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				if ($("#save_new").val() == '1')
				{	
					window.location=root_domain+'expense-entry';
				}
				else
				{
					window.location=root_domain+'expense_detail';
				}
			}
				
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function generate_report_expense() 
{
	
	var date=$("#rep_date").val();
	var emp_id=$("#emp_id").val();
	var exp_id=$("#exp_id").val();
	var comp_id=$("#comp_id").val();
	//alert(date);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_expense",date:date,emp_id:emp_id,exp_id:exp_id,comp_id:comp_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}


function generate_report_emp_ledger() 
{
	
	var date=$("#rep_date").val();
	var emp_id=$("#emp_id").val();
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_expense/',
		data: { mode : "generate_report_emp_ledger",date:date,emp_id:emp_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}
function delete_expense(ex_id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/employee_expense/',
				data: { mode : "delete_expense",  ex_id : ex_id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("EXPENSE DELETED SUCCESSFULLY", "SUCCESS");
						load_expense_datatable();
					}
					else if(response == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}
					Unloading();							
				}
			});	
		}
	
}