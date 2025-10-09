//var datatable;
$(document).ready(function() {
	load_expense_datatable();
	
	// validate vendor add form on keyup and submit
	$("#expense_add").validate({
		rules: {
			expense_date: {
				required: true			
			},
			expense_name: {
				required: true
			},
			expense_amount: {
				required: true,
				number:true
			}
		},
		messages: {
			expense_date: {
				required: "Please Select Date"
			},
			expense_name: {
				required: "Select Expense Name"
			},
			expense_amount: {
				number:"Enter Only number "
			}
		}
	});
	
});

$(".btn_close").click(function() {
	$("label.error").hide();
});
$("#expense_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#expense_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/expense_detail/',
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
				toastr.success("EXPENSE ADDED SUCCESSFULLY", "SUCCESS");	
				window.location=root_domain+'expense_detail';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("EXPENSE ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
			}
			else if(responsevalue.trim() == '0')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(responsevalue.trim() == 'update')
			{	
				toastr.success("EXPENSE UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+'expense_detail';		
			}
			$('#expense_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function edit_expense(expense_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/expense_detail/',
		data: { mode : "preedit", expense_id : expense_id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#edit_id").val(expense_id);
			$("#expense_name").select2("val",obj.expense_name);
			$("#expense_complain").select2("val",obj.expense_complain);
			$("#expense_amount").val(obj.expense_amount);
			var nowDate = new Date(obj.expense_date);
			var date=("0" + nowDate.getDate()).slice(-2);
			var month=("0" + (nowDate.getMonth() + 1)).slice(-2);
			//alert(unformatedDate);
			$("#expense_date").val(date +"-"+ month + '-'+ nowDate.getFullYear());
			$("#mode").val('edit');
			Unloading();
		}
	});	
}

function delete_expense(id) {
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/expense_detail/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("EXPENSE DELETE SUCCESSFULLY", "SUCCESS");
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

function load_expense_datatable(){
	datatable = $("#expense-table").dataTable({
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
		"sAjaxSource": root_domain+'app/expense_detail/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}   