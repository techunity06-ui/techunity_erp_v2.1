//var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/accountmst/',
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
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#bank_account_add").validate({
	rules: {
		bank_id:{
			required: true		
		},
		branch_name: {
			required: true			
		},
		cityid:{
			required: true		
		},
		account_name: {
			required: true			
		},
		account_number: {
			required: true			
		}
	},
	messages: {
		bank_id: {
			required: "Choose Bank"				
		},
		branch_name: {
			required: "Enter Branch Name"				
		},
		cityid:{
			required: "Choose City"		
		},
		account_name: {
			required: "Enter Account Holder Name"
		},
		account_number: {
			required: "Enter Account Number"
		}
	}
}); 
});
$("#bank_account_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#bank_account_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/accountmst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			var data = JSON.parse(response);
			var response=data.res;
			if(response.trim() == '1') {
				Unloading();
				toastr.success("ACCOUNT ADDED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
			}
			if(response.trim() == '2') {
				Unloading();
				toastr.success("ACCOUNT ADDED SUCCESSFULLY", "SUCCESS");
				$("#model_addaccount").modal("hide");
				$('#pur_acc_id').append('<option value='+data.id+'>'+data.name+'</option>');
				$('#pur_acc_id').val(data.id);
				$("#pur_acc_id").trigger('change')
				$('#bank_account_add').trigger('reset');
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			
			$('#bank_account_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
$("#FormEditAccount").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditAccount").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/accountmst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);			
			if(response.trim() == '2') {
				Unloading();
				toastr.success("ACCOUNT UPDATED SUCCESSFULLY", "SUCCESS");
				
				datatable.fnReloadAjax();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditAccount").modal("hide");
			$('#FormEditAccount').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_accountmst(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/accountmst/',
				data: { mode : "delete",  eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("ACCOUNT DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
						Unloading();
					}
					else if(response == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_test(id)
{
		Loading(true);
		editReq = $.ajax({
			type: "POST",
			url: root_domain+'app/accountmst/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				//console.log(response);
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);				
				$("#edit_bankid").select2("val",obj.bankid);
				$("#edit_cityid").select2("val",obj.cityid);
				$("#edit_branch_name").val(obj.branch_name);	
				$("#edit_acc_name").val(obj.acc_name);
				$("#edit_acc_number").val(obj.acc_number);
				$("#edit_acc_chequeno").val(obj.acc_chequeno);	
				$("#edit_acc_chequeleft").val(obj.acc_chequeleft);
				$("#edit_opn_balance").val(obj.opn_balance);
				Unloading();
			}
		});	
}