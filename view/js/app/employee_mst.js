//var datatable;
$(document).ready(function() {
	load_emp_datatable();

	// validate vendor add form on keyup and submit
	$("#employee_add").validate({
		rules: {
			employee_name: {
				required: true			
			}, 
			stateid: {
				required: true
			},
			cityid: {
				required: true
			},
			emp_mobile: {
				number:true,
				maxlength:10,
				minlength:10
			},
			emp_email:{
				required:true,
				email:true
			},
			emp_password:{
				required:true,
				minlength:5
			},
			e_type:{
				required:true,
			}
		},
		messages: {
			employee_name: {
				required: "Enter Employee Name"
			}, 
			stateid: {
				required: "State must be select"
			},
			cityid: {
				required: "City must be select"
			},
			emp_mobile: {
				number:"Enter Only number ",
				maxlength:"Mobile No. Should consist only 10 digits",
				minlength:"Mobile No. Should consist at least 10 digits"
			},
			emp_email:{
				required: "Enter Email",
				email:"Enter Valid Email"
			},
			emp_password:{
				required: "Enter Password",
				minlength:"Enter more than 5 Character"
			}
		}
	});
	
});

$(".btn_close").click(function() {
	$("label.error").hide();
});
$("#employee_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#employee_add").valid()) {
		return false;
	}
	else if(parseInt($('#opening_balance').val())>0 && $('#balance_typeid').val() =="" ) {
		toastr.warning("Select Debit / Credit Option", "WARNING");
		return false;
	}

	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/employee_mst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var data = JSON.parse(response);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				Unloading();
				toastr.success("EMPLOYEE ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'employee_list';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("EMPLOYEE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_employee_modal").modal("hide");
				$('#employee_id').append('<option value='+data.employee_id+'>'+data.employee_name+'</option>');
				$("#employee_id").trigger('change');
				$('#employee_id').select2("val",data.employee_id);
				$("#employee_id").trigger('change');
				$('#employee_add').trigger('reset');
				Unloading();
			}
			else if(responsevalue.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$("#add_employee_modal").modal("hide");
				$('#employee_add').trigger('reset');
				Unloading();				
			}
			else if(responsevalue.trim() == 'update') {	
				toastr.success("EMPLOYEE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'employee_list';		
			}
			$('#employee_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function delete_emp(id) {
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/employee_mst/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("EMPLOYEE DELETE SUCCESSFULLY", "SUCCESS");
					load_emp_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function load_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_mst/',
		data: { mode : "load_state",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function add_state()
{
	if($("#countryid").val()=='') {
		toastr.warning("Please Select the Country", "WARNING");
	}
	else{
		$("#bs-example-modal-state").modal("show");
		$("#countryid").val($("#countryid").val());
	}
}
function load_city(parentid,control,val1) {	
	$.ajax({
		type: "POST",
		url: root_domain+'app/employee_mst/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	}); 
}
function add_city() {
	if($("#stateid").val()=='') {
		toastr.warning("Please Select the State", "WARNING");
	}
	else{
		$("#bs-example-modal-city").modal("show");
		$("#state_id").val($("#stateid").val());
	}
}
function load_emp_datatable(){
	
	var type=$('#type').val();
	
	datatable = $("#employee-table").dataTable({
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
		"sAjaxSource": root_domain+'app/employee_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{"name":"type","value":type});
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}   

function generate_daily_log_report(){
	
	var rep_date=$('#rep_date').val();
	var type=$('#type').val();
	//alert(type);
	datatable = $("#dynamic-table").dataTable({
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
		"sAjaxSource": root_domain+'app/employee_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "generate_daily_log_report" },{ "name": "rep_date", "value": rep_date },{ "name": "type", "value": type } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}   